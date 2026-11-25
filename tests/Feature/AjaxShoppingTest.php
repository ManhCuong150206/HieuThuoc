<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class AjaxShoppingTest extends TestCase
{
    private function product(array $overrides = []): Product
    {
        return Product::create(array_replace([
            'category_id' => 1, 'name' => 'Sản phẩm Ajax', 'price' => 50000,
            'quantity' => 10, 'is_active' => true,
        ], $overrides));
    }

    public function test_ajax_cart_addition_returns_updated_count_and_persists_without_redirect_or_flash(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = $this->product();
        $other->cartItems()->create(['product_id' => $product->id, 'quantity' => 9]);
        $this->actingAs($user->account)->withSession(['checkout_token' => 'old-token'])
            ->postJson(route('cart.store', $product), ['quantity' => 2])
            ->assertOk()->assertJsonPath('cart_quantity', 2)
            ->assertJsonPath('message', 'Đã thêm sản phẩm vào giỏ hàng.')
            ->assertSessionMissing('status')->assertSessionMissing('checkout_token');
        $this->postJson(route('cart.store', $product), ['quantity' => 3])
            ->assertOk()->assertJsonPath('cart_quantity', 5);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 5]);
        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_ajax_validation_errors_are_json_and_do_not_change_stock_or_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->actingAs($user->account)->postJson(route('cart.store', $product), ['quantity' => 11])
            ->assertUnprocessable()->assertJsonValidationErrors('cart');
        $this->postJson(route('cart.store', $product), ['quantity' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(10, $product->fresh()->quantity);
    }

    public function test_cart_forms_update_and_delete_using_json_without_redirects(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $item = $user->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);
        $other = $user->cartItems()->create(['product_id' => $this->product()->id, 'quantity' => 3]);
        $headers = ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];

        $this->actingAs($user->account)->withSession(['checkout_token' => 'old-token'])
            ->post(route('cart.update', $item), ['_method' => 'PUT', 'quantity' => 4], $headers)
            ->assertOk()->assertJsonPath('cart_quantity', 7)
            ->assertJsonPath('cart_item.id', $item->id)
            ->assertJsonPath('cart_item.quantity', 4)->assertJsonPath('cart_item.unit_price', 50000)
            ->assertSessionMissing('status')->assertSessionMissing('checkout_token');
        $this->post(route('cart.update', $item), ['_method' => 'PUT', 'quantity' => 11], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('cart');
        $this->assertSame(4, $item->fresh()->quantity);

        $this->post(route('cart.destroy', $item), ['_method' => 'DELETE'], $headers)
            ->assertOk()->assertJsonPath('removed_item_id', $item->id)->assertJsonPath('cart_quantity', 3);
        $this->assertModelExists($other);
        $this->post(route('cart.clear'), ['_method' => 'DELETE'], $headers)
            ->assertOk()->assertJsonPath('cart_cleared', true)->assertJsonPath('cart_quantity', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_ajax_guest_cannot_add_items_to_cart(): void
    {
        $product = $this->product();
        $this->postJson(route('cart.store', $product), ['quantity' => 1])->assertUnauthorized();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_locked_account_gets_json_instead_of_html_redirect(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $user->account->forceFill(['is_active' => false])->save();
        $this->actingAs($user->account)->postJson(route('cart.store', $product), ['quantity' => 1])
            ->assertUnauthorized()->assertJsonPath('message', 'Tài khoản đã bị khóa. Vui lòng đăng nhập lại.');
        $this->assertGuest();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_product_pages_enable_ajax_for_add_buttons_and_keep_buy_now(): void
    {
        $product = $this->product();
        $this->get(route('products.index'))->assertOk()
            ->assertSee('data-ajax-action="cart"', false)
            ->assertSee('name="buy_now" value="1"', false)
            ->assertSee('Thêm vào giỏ hàng');
        $this->get(route('products.show', $product))->assertOk()
            ->assertSee('data-ajax-action="cart"', false)
            ->assertDontSee('Lưu yêu thích')
            ->assertSee('name="buy_now" value="1"', false)
            ->assertSee('data-cart-count', false);
    }
}
