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

    public function test_ajax_favorite_is_saved_once_and_returns_saved_state(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->actingAs($user->account);
        foreach ([1, 2] as $attempt) {
            $this->postJson(route('favorites.store', $product))->assertOk()
                ->assertJsonPath('saved', true)->assertJsonPath('product_id', $product->id)
                ->assertSessionMissing('status');
        }
        $this->assertDatabaseCount('favorites', 1);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
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

    public function test_ajax_guest_gets_unauthorized_json_for_both_actions(): void
    {
        $product = $this->product();
        $this->postJson(route('cart.store', $product), ['quantity' => 1])->assertUnauthorized();
        $this->postJson(route('favorites.store', $product))->assertUnauthorized();
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('favorites', 0);
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
        $this->get(route('products.index'))->assertOk()->assertSee('data-ajax-action="cart"', false);
        $this->get(route('products.show', $product))->assertOk()
            ->assertSee('data-ajax-action="cart"', false)
            ->assertSee('data-ajax-action="favorite"', false)
            ->assertSee('name="buy_now" value="1"', false)
            ->assertSee('data-cart-count', false);
    }
}
