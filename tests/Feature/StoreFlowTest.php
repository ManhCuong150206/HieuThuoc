<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreFlowTest extends TestCase
{
    private function product(array $overrides = []): Product
    {
        return Product::create(array_replace([
            'category_id' => 1, 'name' => 'Sản phẩm kiểm thử', 'price' => 50000,
            'quantity' => 10, 'is_active' => true, 'brand' => 'ThuongHieu',
        ], $overrides));
    }

    private function customer(): User
    {
        $user = User::factory()->create(['address' => '12 Đường A, Hà Nội']);
        $this->actingAs($user->account);

        return $user;
    }

    private function payload(): array
    {
        return [
            'recipient_name' => 'Người nhận thử', 'recipient_phone' => '0912345678',
            'shipping_address' => '34 Đường B, Hà Nội', 'payment_method' => 'COD',
            'terms' => 1, 'checkout_token' => session('checkout_token'),
        ];
    }

    private function prepare(User $user, Product $product, int $quantity = 2): array
    {
        $this->actingAs($user->account)->post(route('cart.store', $product), ['quantity' => $quantity])->assertSessionHasNoErrors();
        $this->get(route('checkout.index'))->assertOk();

        return $this->payload();
    }

    public function test_guests_must_login_for_all_shopping_writes_and_checkout(): void
    {
        $product = $this->product();
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertRedirect(route('login'));
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), [])->assertRedirect(route('login'));
        $this->post(route('favorites.store', $product))->assertRedirect(route('login'));
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cart_add_merge_update_delete_and_clear_are_persistent(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('cart.store', $product), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->post(route('cart.store', $product), ['quantity' => 3])->assertSessionHasNoErrors();
        $item = $user->cartItems()->sole();
        $this->assertSame(5, $item->quantity);
        $this->assertDatabaseCount('cart_items', 1);
        $this->put(route('cart.update', $item), ['quantity' => 4])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4, 'user_id' => $user->id]);
        $this->get(route('cart.index'))->assertOk()->assertSee('200.000đ')->assertSee('Tiến hành đặt hàng');
        $this->delete(route('cart.destroy', $item))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('cart_items', 0);
        $this->post(route('cart.store', $product), ['quantity' => 1, 'buy_now' => 1])->assertRedirect(route('checkout.index'));
        $this->delete(route('cart.clear'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_invalid_or_unavailable_products_do_not_change_the_cart(): void
    {
        $this->customer();
        foreach ([['quantity' => 0], ['price' => 0], ['is_active' => false], ['expiry_date' => today()->subDay()]] as $data) {
            $product = $this->product($data);
            $this->post(route('cart.store', $product), ['quantity' => 1])->assertSessionHasErrors('cart');
        }
        $product = $this->product();
        $this->post(route('cart.store', $product), ['quantity' => 11])->assertSessionHasErrors('cart');
        $this->post(route('cart.store', $product), ['quantity' => -1])->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_cannot_edit_another_customers_cart(): void
    {
        $other = User::factory()->create();
        $product = $this->product();
        $item = $other->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);
        $this->customer();
        $this->put(route('cart.update', $item), ['quantity' => 1])->assertNotFound();
        $this->delete(route('cart.destroy', $item))->assertNotFound();
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 2]);
    }

    public function test_cash_checkout_saves_order_items_payment_and_stock_atomically(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $data = $this->prepare($user, $product, 3);
        $this->post(route('checkout.store'), $data + ['total_amount' => 1, 'user_id' => 999, 'unit_price' => 1])
            ->assertSessionHasNoErrors()->assertRedirect(route('checkout.success', Order::first()));
        $order = Order::with('items', 'payments')->sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('CONFIRMED', $order->status);
        $this->assertSame('COD', $order->payment_method);
        $this->assertSame(150000, $order->subtotal);
        $this->assertSame(175000, $order->total_amount);
        $this->assertSame(50000, $order->items->sole()->unit_price);
        $this->assertSame('SUCCESS', $order->payments->sole()->status);
        $this->assertSame(today()->toDateString(), $order->payments->sole()->paid_at->toDateString());
        $this->assertSame(7, $product->fresh()->quantity);
        $this->assertDatabaseCount('cart_items', 0);
        $this->get(route('checkout.success', $order))->assertOk()->assertSee($order->order_code)->assertSee('Thành công')->assertSee('34 Đường B');
        $this->get(route('account.index'))->assertOk()->assertSee($order->order_code)->assertSee('Đã xác nhận');
    }

    public function test_repeated_checkout_request_creates_one_order_only(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $data = $this->prepare($user, $product);
        $this->post(route('checkout.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('checkout.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(8, $product->fresh()->quantity);
    }

    public function test_stock_change_or_invalid_payment_rolls_back_without_erasing_cart(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $data = $this->prepare($user, $product);
        $this->post(route('checkout.store'), array_replace($data, ['payment_method' => 'QR']))->assertSessionHasErrors('payment_method');
        $this->post(route('checkout.store'), array_replace($data, ['checkout_token' => str_repeat('x', 48)]))->assertStatus(419);
        $product->update(['quantity' => 1]);
        $this->post(route('checkout.store'), $data)->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(1, $product->fresh()->quantity);
    }

    public function test_active_promotion_is_calculated_on_server_and_expired_one_is_rejected(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $promotion = Promotion::create(['name' => 'Giảm 10%', 'discount_percent' => 10, 'starts_at' => today()->subDay(), 'ends_at' => today()->addDay(), 'is_active' => true]);
        $data = $this->prepare($user, $product);
        $promotion->update(['is_active' => false]);
        $this->post(route('checkout.store'), $data + ['promotion_id' => $promotion->id])->assertSessionHasErrors('promotion_id');
        $this->assertDatabaseCount('orders', 0);
        $promotion->update(['is_active' => true]);
        $this->post(route('checkout.store'), $data + ['promotion_id' => $promotion->id, 'discount_amount' => 99999])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['promotion_id' => $promotion->id, 'subtotal' => 100000, 'discount_amount' => 10000, 'shipping_fee' => 25000, 'total_amount' => 115000]);
    }

    public function test_checkout_is_private_and_cancellation_restores_stock_once(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('checkout.store'), $this->prepare($user, $product))->assertSessionHasNoErrors();
        $order = Order::sole();
        $other = User::factory()->create();
        $this->actingAs($other->account)->get(route('checkout.success', $order))->assertNotFound();
        $this->post(route('orders.cancel', $order))->assertNotFound();
        $this->post(route('orders.reorder', $order))->assertNotFound();
        $this->actingAs($user->account)->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertSame(10, $product->fresh()->quantity);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'CANCELLED']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'FAILED', 'paid_at' => null]);
    }

    public function test_admin_can_complete_an_order_but_cannot_skip_or_reverse_shipping_states(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('checkout.store'), $this->prepare($user, $product));
        $order = Order::sole();
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'COMPLETED'])->assertSessionHasErrors('order');
        foreach (['PROCESSING', 'SHIPPING', 'DELIVERED', 'COMPLETED'] as $status) {
            $this->put(route('admin.orders.update', $order), ['status' => $status])->assertSessionHasNoErrors();
        }
        $this->assertSame('COMPLETED', $order->fresh()->status);
        $this->actingAs($user->account)->post(route('orders.cancel', $order))->assertSessionHasErrors('order');
        $this->assertSame(8, $product->fresh()->quantity);
        $this->post(route('orders.reorder', $order))->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_favorites_are_persistent_unique_and_scoped_to_customer(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('favorites.store', $product))->assertSessionHasNoErrors();
        $this->post(route('favorites.store', $product))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('favorites', 1);
        $this->get(route('favorites.index'))->assertOk()->assertSee($product->name);
        $other = User::factory()->create();
        $this->actingAs($other->account)->delete(route('favorites.destroy', $product));
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
        $this->actingAs($user->account)->delete(route('favorites.destroy', $product));
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_reviews_require_completed_purchase_and_admin_approval(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('checkout.store'), $this->prepare($user, $product));
        $order = Order::sole();
        $item = $order->items()->sole();
        $data = ['rating' => 5, 'comment' => 'Đánh giá thật từ khách'];
        $this->post(route('reviews.store', $item), $data)->assertSessionHasErrors('review');
        $other = User::factory()->create();
        $this->actingAs($other->account)->post(route('reviews.store', $item), $data)->assertNotFound();
        $order->update(['status' => 'COMPLETED']);
        $this->actingAs($user->account)->post(route('reviews.store', $item), $data)->assertSessionHasNoErrors();
        $this->post(route('reviews.store', $item), $data)->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 1);
        $this->get(route('products.show', $product))->assertDontSee($data['comment']);
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin)->put(route('admin.reviews.update', Review::sole()), ['status' => 'VISIBLE'])->assertSessionHasNoErrors();
        $this->get(route('products.show', $product))->assertOk()->assertSee($data['comment']);
    }

    public function test_search_filters_database_products_by_name_or_brand(): void
    {
        $product = $this->product(['name' => 'TimThay123']);
        $this->product(['name' => 'Không phù hợp', 'brand' => 'Khac']);
        $this->get(route('products.index', ['q' => 'TimThay123']))->assertOk()->assertSee($product->name)->assertDontSee('Không phù hợp');
        $this->get(route('products.index', ['q' => 'ThuongHieu']))->assertViewHas('products', fn ($products) => $products->total() === 1);
    }

    public function test_admin_forms_crud_stock_prices_categories_promotions_and_posts_work(): void
    {
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin);
        foreach (['products', 'categories', 'promotions', 'posts'] as $resource) {
            $this->get(route('admin.resources.index', $resource))->assertOk();
            $this->get(route('admin.resources.create', $resource))->assertOk();
        }
        foreach (['admin.dashboard', 'admin.orders.index', 'admin.customers.index', 'admin.reviews.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $product = $this->product();
        $data = $product->only(['category_id', 'name', 'price', 'quantity', 'is_active']);
        $data['price'] = 65000;
        $data['quantity'] = 20;
        $this->put(route('admin.resources.update', ['resource' => 'products', 'record' => $product->id]), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'price' => 65000, 'quantity' => 20]);
        $this->delete(route('admin.resources.destroy', ['resource' => 'categories', 'record' => 1]))->assertSessionHasErrors('category');
        $this->post(route('admin.resources.store', 'categories'), ['name' => 'Danh mục mới'])->assertSessionHasNoErrors();
        $this->post(route('admin.resources.store', 'promotions'), ['name' => 'Ưu đãi mới', 'discount_percent' => 20, 'starts_at' => '2026-11-01', 'ends_at' => '2026-11-30', 'is_active' => 1])->assertSessionHasNoErrors();
        $this->post(route('admin.resources.store', 'posts'), ['title' => 'Bài thử', 'content' => 'Nội dung từ database', 'status' => 'PUBLISHED', 'published_at' => today()->toDateString()])->assertSessionHasNoErrors();
        $post = Post::sole();
        $this->get(route('posts.index'))->assertOk()->assertSee($post->title);
        $this->get(route('posts.show', $post))->assertOk()->assertSee($post->content);
        $this->get('/')->assertSee($post->title);
        $this->delete(route('admin.resources.destroy', ['resource' => 'posts', 'record' => $post->id]))->assertSessionHasNoErrors();
        $this->get(route('posts.show', $post))->assertNotFound();
        $this->delete(route('admin.resources.destroy', ['resource' => 'products', 'record' => $product->id]))->assertSessionHasNoErrors();
        $this->assertFalse($product->fresh()->is_active);
        $this->get(route('admin.resources.edit', ['resource' => 'products', 'record' => $product->id]))->assertOk();
        $this->get(route('admin.report'))->assertOk()->assertDownload();
    }

    public function test_customer_cannot_use_admin_writes_and_admin_can_lock_customer(): void
    {
        $user = $this->customer();
        $this->post(route('admin.resources.store', 'categories'), ['name' => 'Forbidden'])->assertForbidden();
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin)->put(route('admin.customers.update', $user->account), ['is_active' => 0])->assertSessionHasNoErrors();
        $this->actingAs($user->account->fresh())->get(route('account.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_customer_can_change_password_with_current_password_verification(): void
    {
        $user = $this->customer();
        $this->put(route('account.password'), ['current_password' => 'wrong', 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'])->assertSessionHasErrors('current_password');
        $this->put(route('account.password'), ['current_password' => 'password', 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newPassword123', $user->account->fresh()->password));
    }
}
