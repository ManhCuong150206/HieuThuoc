<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateProductsRequest;
use App\Http\Requests\Admin\SaveResourceRequest;
use App\Services\Admin\ResourceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ResourceService $resources) {}

    public function bulkUpdate(BulkUpdateProductsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $changes = array_filter($request->safe()->only(array_keys(SaveResourceRequest::PRODUCT_UPDATE_RULES)), fn ($value) => $value !== null);
        $count = $this->resources->bulkUpdateProducts($data, $changes);

        return redirect()->route('admin.resources.index', [
            'resource' => 'products', 'q' => $data['q'] ?? null, 'page' => $data['page'] > 1 ? $data['page'] : null,
        ])->with('status', 'Đã áp dụng thay đổi cho '.number_format($count, 0, ',', '.').' sản phẩm.')
            ->with('admin_products_bulk_saved', true);
    }

    public function destroy(Request $request, int $product): RedirectResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $this->resources->deleteProduct($product);

        return redirect()->route('admin.resources.index', ['resource' => 'products', 'q' => $data['q'] ?? null])
            ->with('status', 'Đã xóa sản phẩm.')
            ->with('admin_product_deleted', $product);
    }
}
