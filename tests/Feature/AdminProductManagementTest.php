<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    public function test_product_creation_uploads_a_public_image_and_keeps_it_after_editing(): void
    {
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']))->withoutVite();
        $publicPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'group10-'.Str::uuid();
        $this->app->usePublicPath($publicPath);

        $index = route('admin.resources.index', 'products');
        $this->get($index)->assertOk()->assertSee('id="resource-create-dialog"', false)
            ->assertSee('enctype="multipart/form-data"', false);
        $this->get(route('admin.resources.create', 'products'))->assertRedirect($index.'?them=1');

        $data = ['category_id' => 1, 'name' => 'Sản phẩm có ảnh', 'price' => 15000, 'quantity' => 20, 'is_active' => 1];
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j5ioAAAAASUVORK5CYII=');
        $files = [];

        try {
            $this->post($index, [...$data, 'image' => UploadedFile::fake()->createWithContent('sample.png', $png)])
                ->assertSessionHasNoErrors()->assertRedirect($index);

            $product = Product::sole();
            $files[] = public_path($product->image_url);
            $this->assertStringStartsWith('images/product-', $product->image_url);
            $this->assertSame($png, File::get(public_path($product->image_url)));
            $this->assertSame(asset($product->image_url), $product->imageSrc());
            $this->get($index)->assertOk()->assertSee('Xem ảnh vừa lưu')->assertSee($product->imageSrc());
            $this->get(route('products.show', $product))->assertOk()->assertSee($product->imageSrc());

            $this->put(route('admin.resources.update', ['resource' => 'products', 'record' => $product->id]), [
                ...$data, 'name' => 'Sản phẩm đã đổi tên', 'image_url' => '',
            ])->assertSessionHasNoErrors();
            $this->assertSame($product->image_url, $product->fresh()->image_url);

            $existing = Product::create([...$data, 'name' => 'Sản phẩm thiếu ảnh']);
            $edit = route('admin.resources.edit', ['resource' => 'products', 'record' => $existing->id]);
            $this->get($edit)->assertOk()->assertSee('enctype="multipart/form-data"', false)
                ->assertSee('type="file"', false);
            $response = $this->post(route('admin.resources.update', ['resource' => 'products', 'record' => $existing->id]), [
                ...$data, 'name' => $existing->name, '_method' => 'PUT',
                'image' => UploadedFile::fake()->createWithContent('sample.png', $png),
            ], ['Accept' => 'application/json'])->assertOk()
                ->assertJsonPath('url', $index)->assertJsonPath('message', 'Đã cập nhật sản phẩm và lưu ảnh.');

            $existing->refresh();
            $files[] = public_path($existing->image_url);
            $this->assertSame($png, File::get(public_path($existing->image_url)));
            $response->assertJsonPath('image_url', $existing->imageSrc());
            $this->get($edit)->assertSee($existing->imageSrc());
        } finally {
            File::delete($files);
            if (is_dir(public_path('images'))) {
                rmdir(public_path('images'));
            }
            if (is_dir($publicPath)) {
                rmdir($publicPath);
            }
        }
    }

    public function test_an_invalid_product_image_returns_to_the_catalog_and_reopens_the_form(): void
    {
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $index = route('admin.resources.index', ['resource' => 'products', 'q' => 'Kem', 'page' => 2]);
        $data = ['category_id' => 1, 'name' => 'Tên đã nhập', 'price' => 15000, 'quantity' => 20, 'is_active' => 1];
        $file = UploadedFile::fake()->createWithContent('sample.png', 'plain text');
        $image = new UploadedFile($file->getPathname(), 'sample.png', null, null, true);

        $this->post($index, [...$data, 'image' => $image])
            ->assertSessionHasErrorsIn('productCreate', ['image'])->assertRedirect($index);
        $this->assertDatabaseEmpty('products');
        $this->get($index)->assertOk()->assertSee('data-open-on-load', false)
            ->assertSee('value="Tên đã nhập"', false)->assertSee('Tệp đã chọn phải là ảnh.');

        $this->post($index, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['name' => 'Tên đã nhập', 'image_url' => null]);
    }

    public function test_creating_resources_by_ajax_returns_the_updated_first_page(): void
    {
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin);
        $product = $this->product('Sản phẩm áp dụng');
        $examples = [
            'categories' => ['name' => 'Danh mục AJAX'],
            'promotions' => [
                'name' => 'Ưu đãi AJAX', 'discount_percent' => 15, 'is_active' => 1,
                'starts_at' => today()->toDateString(), 'ends_at' => today()->addWeek()->toDateString(),
                'applies_to_all_products' => 0, 'product_ids' => json_encode([$product->id]),
            ],
            'posts' => ['title' => 'Bài viết AJAX', 'content' => 'Nội dung thử', 'status' => 'DRAFT'],
            'products' => ['name' => 'Sản phẩm AJAX', 'category_id' => 1, 'price' => 20000, 'quantity' => 5, 'is_active' => 1],
        ];

        foreach ($examples as $resource => $data) {
            $index = route('admin.resources.index', $resource);
            $filtered = route('admin.resources.index', ['resource' => $resource, 'q' => 'Không trùng', 'page' => 3]);
            $this->get($filtered)->assertOk()->assertSee('data-admin-create-form', false);
            $response = $this->postJson($filtered, $data)->assertCreated()->assertJsonPath('url', $index);
            $this->assertStringContainsString($data['name'] ?? $data['title'], $response->json('html'));
            $this->assertStringContainsString('data-admin-resource-results', $response->json('html'));
        }

        $this->assertSame([$product->id], Promotion::sole()->products()->pluck('products.id')->all());
        $this->assertDatabaseHas('posts', ['title' => 'Bài viết AJAX', 'author_account_id' => $admin->id]);
        $this->postJson(route('admin.resources.store', 'categories'), $examples['categories'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_editing_resources_by_ajax_loads_current_values_and_refreshes_the_filtered_list(): void
    {
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin);
        $category = Category::create(['name' => 'Mẫu danh mục']);
        $product = $this->product('Mẫu sản phẩm');
        $offer = [
            'name' => 'Mẫu khuyến mãi', 'discount_percent' => 15, 'is_active' => 1,
            'starts_at' => today()->toDateString(), 'ends_at' => today()->addWeek()->toDateString(),
            'applies_to_all_products' => false,
        ];
        $promotion = Promotion::create($offer);
        $promotion->products()->attach($product);
        $post = Post::create(['title' => 'Mẫu bài viết', 'content' => 'Nội dung', 'status' => 'DRAFT', 'author_account_id' => $admin->id]);
        $examples = [
            'categories' => [$category, ['name' => 'Mẫu danh mục đã sửa']],
            'products' => [$product, ['name' => 'Mẫu sản phẩm đã sửa', 'category_id' => 1, 'price' => 15000, 'quantity' => 17, 'is_active' => 1]],
            'promotions' => [$promotion, [...$offer, 'name' => 'Mẫu khuyến mãi đã sửa', 'product_ids' => json_encode([$product->id])]],
            'posts' => [$post, ['title' => 'Mẫu bài viết đã sửa', 'content' => 'Nội dung mới', 'status' => 'DRAFT']],
        ];

        foreach ($examples as $resource => [$record, $data]) {
            $parameters = ['resource' => $resource, 'record' => $record->id, 'q' => 'Mẫu'];
            $index = route('admin.resources.index', ['resource' => $resource, 'q' => 'Mẫu']);
            $update = route('admin.resources.update', $parameters);
            $this->get($index)->assertOk()->assertSee('data-admin-edit-open', false);
            $fields = $this->getJson(route('admin.resources.edit', $parameters))->assertOk()->assertJsonPath('url', $update)->json('html');
            $this->assertStringContainsString(e($record->name ?? $record->title), $fields);
            if ($resource === 'promotions') {
                $this->assertStringContainsString('value="'.$product->id.'" checked', $fields);
            }

            $response = $this->post($update, [...$data, '_method' => 'PUT'], ['Accept' => 'application/json'])
                ->assertOk()->assertJsonPath('url', $index)->assertJsonPath('message', 'Đã lưu thay đổi.');
            $this->assertStringContainsString($data['name'] ?? $data['title'], $response->json('html'));
        }

        $this->putJson(route('admin.resources.update', ['resource' => 'categories', 'record' => $category->id]), ['name' => Category::findOrFail(1)->name])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $response = $this->putJson(route('admin.resources.update', ['resource' => 'categories', 'record' => $category->id, 'q' => 'Mẫu', 'page' => 2]), ['name' => 'Danh mục ngoài bộ lọc'])
            ->assertOk()->assertJsonPath('url', route('admin.resources.index', ['resource' => 'categories', 'q' => 'Mẫu']));
        $this->assertStringContainsString('Chưa có dữ liệu phù hợp.', $response->json('html'));
    }

    public function test_admin_can_update_a_thousand_explicitly_selected_products(): void
    {
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $records = array_fill(0, 1001, ['category_id' => 1, 'name' => 'Sản phẩm thử', 'price' => 15000, 'quantity' => 20]);
        foreach (array_chunk($records, 100) as $chunk) {
            DB::table('products')->insert($chunk);
        }
        $ids = Product::orderBy('id')->limit(1000)->pluck('id')->all();

        $this->patch(route('admin.products.bulk-update'), [
            'scope' => 'selected', 'ids' => json_encode($ids), 'selected_count' => 1000,
            'page' => 2, 'q' => 'Sản phẩm', 'quantity' => 0, 'is_active' => '0', 'price' => '',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.resources.index', [
            'resource' => 'products', 'q' => 'Sản phẩm', 'page' => 2,
        ]));

        $this->assertSame(1000, Product::where('quantity', 0)->where('is_active', false)->where('price', 15000)->count());
        $this->assertSame(1, Product::where('quantity', 20)->where('is_active', true)->count());
    }

    public function test_admin_can_permanently_delete_a_product_without_orders(): void
    {
        $product = $this->product('Kem cần xóa');
        $other = $this->product('Kem giữ lại');
        $customer = User::factory()->create();
        $customer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);
        $customer->cartItems()->create(['product_id' => $other->id, 'quantity' => 2]);
        DB::table('favorites')->insert(['user_id' => $customer->id, 'product_id' => $product->id]);
        $promotion = Promotion::create([
            'name' => 'Ưu đãi theo sản phẩm', 'discount_percent' => 10, 'applies_to_all_products' => false,
            'starts_at' => today(), 'ends_at' => today()->addDay(),
        ]);
        $promotion->products()->attach([$product->id, $other->id]);

        $url = route('admin.products.destroy', $product);
        $index = route('admin.resources.index', ['resource' => 'products', 'q' => 'Kem']);
        $this->actingAs($customer->account)->delete($url)->assertForbidden();
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $this->get($index)->assertOk()->assertSee($url)->assertSee('Xóa vĩnh viễn «Kem cần xóa»?');
        $this->delete($url, ['q' => 'Kem'])->assertRedirect($index)->assertSessionHasNoErrors();
        $this->get($index)->assertOk()
            ->assertSee('data-state="success"', false)->assertSee('Đã xóa sản phẩm.')
            ->assertSee('data-deleted-product="'.$product->id.'"', false)->assertDontSee($product->name);

        $this->assertModelMissing($product);
        $this->assertSame($other->id, $customer->cartItems()->sole()->product_id);
        $this->assertDatabaseEmpty('favorites');
        $this->assertSame($other->id, $promotion->products()->sole()->id);
    }

    public function test_admin_cannot_permanently_delete_a_product_in_an_order(): void
    {
        $product = $this->product('Sản phẩm đã đặt');
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_code' => 'AT-DELETE-TEST', 'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone, 'shipping_address' => $customer->address,
            'subtotal' => 15000, 'total_amount' => 15000, 'payment_method' => 'COD', 'status' => 'CONFIRMED',
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => 15000, 'quantity' => 1,
        ]);

        $index = route('admin.resources.index', 'products');
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']))->from($index)
            ->delete(route('admin.products.destroy', $product))->assertRedirect($index)->assertSessionHasErrors('product');
        $this->get($index)->assertOk()->assertSee('data-state="error"', false)
            ->assertSee('Sản phẩm đã có trong đơn hàng.')->assertDontSee('feedback-message feedback-error', false);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => true]);
        $this->assertModelExists($item);
    }

    public function test_select_all_uses_the_search_and_excludes_unchecked_products(): void
    {
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $selected = $this->product('Kem thử A');
        $excluded = $this->product('Kem thử B');
        $other = $this->product('Dầu thử');
        DB::table('categories')->insert(['id' => 2, 'name' => 'Danh mục khác']);
        $data = [
            'scope' => 'all', 'ids' => json_encode([$excluded->id]), 'selected_count' => 1,
            'page' => 1, 'q' => 'Kem thử', 'price' => 21000, 'category_id' => 2,
        ];
        $url = route('admin.products.bulk-update');

        $this->patch($url, array_replace($data, ['selected_count' => 2]))->assertSessionHasErrors('ids');
        $this->patch($url, array_replace($data, ['price' => -1]))->assertSessionHasErrors('price');
        $this->assertSame(15000, (int) $selected->fresh()->price);
        $this->patch($url, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $selected->id, 'category_id' => 2, 'price' => 21000]);
        $this->assertSame(15000, (int) $excluded->fresh()->price);
        $this->assertSame(15000, (int) $other->fresh()->price);

        $this->actingAs(Account::factory()->create(['role' => 'CUSTOMER']))
            ->patch($url, $data)->assertForbidden();
    }

    public function test_admin_can_set_and_change_a_promotions_product_scope(): void
    {
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $first = $this->product('Sản phẩm A');
        $second = $this->product('Sản phẩm B');
        $data = [
            'name' => 'Ưu đãi thử', 'discount_percent' => 20, 'is_active' => '1',
            'starts_at' => today()->toDateString(), 'ends_at' => today()->addWeek()->toDateString(),
            'applies_to_all_products' => '0', 'product_ids' => json_encode([$first->id, $second->id]),
        ];
        $store = route('admin.resources.store', 'promotions');
        $this->post($store, array_replace($data, ['product_ids' => '[]']))->assertSessionHasErrors('product_ids');
        $this->post($store, array_replace($data, ['product_ids' => '[999]']))->assertSessionHasErrors('product_ids');
        $this->assertDatabaseEmpty('promotions');

        $this->post($store, $data)->assertSessionHasNoErrors();
        $promotion = Promotion::sole();
        $this->assertFalse($promotion->applies_to_all_products);
        $this->assertCount(2, $promotion->products);
        $this->get(route('admin.resources.index', 'promotions'))->assertOk()->assertSee('2 sản phẩm được áp dụng');
        $this->get(route('admin.resources.edit', ['resource' => 'promotions', 'record' => $promotion->id]))
            ->assertOk()->assertSee('Phạm vi khuyến mãi')->assertSee($first->name);

        $update = route('admin.resources.update', ['resource' => 'promotions', 'record' => $promotion->id]);
        $this->put($update, array_replace($data, ['product_ids' => json_encode([$second->id])]))->assertSessionHasNoErrors();
        $this->assertSame($second->id, $promotion->products()->sole()->id);
        $this->put($update, array_replace($data, ['applies_to_all_products' => '1']))->assertSessionHasNoErrors();
        $this->assertTrue($promotion->fresh()->applies_to_all_products);
        $this->assertDatabaseEmpty('product_promotion');
    }

    private function product(string $name): Product
    {
        return Product::create(['category_id' => 1, 'name' => $name, 'price' => 15000, 'quantity' => 20]);
    }
}
