<?php

namespace App\Services\Admin;

use App\Enums\AdminResource;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ResourceService
{
    public function create(AdminResource $resource, array $data): void
    {
        if ($resource === AdminResource::Promotions) {
            DB::transaction(fn () => $this->savePromotion(new Promotion, $data), 3);

            return;
        }

        $class = $resource->model();
        $class::create($data);
    }

    public function update(AdminResource $resource, int $record, array $data): void
    {
        DB::transaction(function () use ($resource, $record, $data): void {
            $model = $resource->query()->lockForUpdate()->findOrFail($record);
            if ($model instanceof Promotion) {
                $this->savePromotion($model, $data);
            } else {
                $model->update($data);
            }
        }, 3);
    }

    public function bulkUpdateProducts(array $data, array $changes): int
    {
        if ($changes === []) {
            throw ValidationException::withMessages(['changes' => 'Nhập ít nhất một thay đổi.']);
        }

        $ids = $this->decodeProductIds($data['ids'], 'ids');
        $query = AdminResource::Products->query($data['scope'] === 'all' ? ($data['q'] ?? null) : null);
        if ($data['scope'] === 'all') {
            $query->whereIntegerNotInRaw('id', $ids);
        } else {
            $query->whereIntegerInRaw('id', $ids);
        }

        return DB::transaction(function () use ($query, $changes, $data): int {
            $selected = (clone $query)->orderBy('id')->lockForUpdate()->pluck('id');
            if ($selected->count() !== (int) $data['selected_count']) {
                throw ValidationException::withMessages(['ids' => 'Số sản phẩm được chọn đã thay đổi. Vui lòng bỏ chọn, chọn lại và thử lại.']);
            }

            foreach ($selected->chunk(500) as $chunk) {
                Product::whereIntegerInRaw('id', $chunk->all())->update($changes);
            }

            return $selected->count();
        }, 3);
    }

    public function deleteProduct(int $product): void
    {
        DB::transaction(function () use ($product): void {
            $model = Product::lockForUpdate()->findOrFail($product);
            if ($model->orderItems()->exists()) {
                throw ValidationException::withMessages([
                    'product' => 'Sản phẩm đã có trong đơn hàng. Bạn có thể dùng «Ngừng / ẩn».',
                ]);
            }

            $model->cartItems()->delete();
            DB::table('favorites')->where('product_id', $product)->delete();
            $model->delete();
        }, 3);
    }

    public function destroy(AdminResource $resource, int $record): void
    {
        $class = $resource->model();
        DB::transaction(function () use ($class, $record, $resource): void {
            $model = $class::lockForUpdate()->findOrFail($record);
            if ($resource === AdminResource::Products || $resource === AdminResource::Promotions) {
                $model->update(['is_active' => false]);
            } elseif ($resource === AdminResource::Posts) {
                $model->update(['status' => 'DRAFT']);
            } else {
                if ($model->products()->exists()) {
                    throw ValidationException::withMessages(['category' => 'Danh mục đang có sản phẩm, không thể xóa.']);
                }
                $model->delete();
            }
        });
    }

    public function saveProduct(array $data, ?int $record = null): ?string
    {
        $image = Arr::pull($data, 'image');
        $imagePath = $image ? $this->storeProductImage($image) : null;
        if ($imagePath !== null) {
            $data['image_url'] = $imagePath;
        }

        try {
            if ($record === null) {
                Product::create($data);
            } else {
                DB::transaction(function () use ($record, $data): void {
                    $product = Product::lockForUpdate()->findOrFail($record);
                    if ($product->hasUploadedImage() && ($data['image_url'] ?? null) === null) {
                        unset($data['image_url']);
                    }
                    $product->update($data);
                }, 3);
            }
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                File::delete(public_path($imagePath));
            }

            throw $exception;
        }

        return $imagePath;
    }

    private function storeProductImage(UploadedFile $image): string
    {
        $imagePath = 'images/product-'.$image->hashName();
        $destination = public_path($imagePath);
        try {
            File::ensureDirectoryExists(dirname($destination));
            if (! File::copy($image->getPathname(), $destination)) {
                throw new RuntimeException('Unable to save the product image.');
            }
        } catch (Throwable $exception) {
            File::delete($destination);
            report($exception);

            throw ValidationException::withMessages(['image' => 'Không thể lưu ảnh. Vui lòng tải lại ảnh.']);
        }

        return $imagePath;
    }

    private function savePromotion(Promotion $promotion, array $data): void
    {
        $ids = [];
        if (isset($data['product_ids'])) {
            $ids = $this->decodeProductIds(Arr::pull($data, 'product_ids'), 'product_ids');
            if ($ids === [] || Product::whereIntegerInRaw('id', $ids)->count() !== count($ids)) {
                throw ValidationException::withMessages(['product_ids' => 'Vui lòng chọn ít nhất một sản phẩm còn tồn tại.']);
            }
        }

        $promotion->fill($data)->save();
        if (array_key_exists('applies_to_all_products', $data)) {
            $promotion->products()->sync($ids);
        }
    }

    private function decodeProductIds(string $json, string $field): array
    {
        $ids = json_decode($json);
        if (! is_array($ids) || array_filter($ids, fn ($id) => ! is_int($id) || $id < 1)) {
            throw ValidationException::withMessages([$field => 'Danh sách sản phẩm đã chọn không hợp lệ.']);
        }

        return array_values(array_unique($ids));
    }
}
