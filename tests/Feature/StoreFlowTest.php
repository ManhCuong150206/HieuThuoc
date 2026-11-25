<?php

namespace Tests\Feature;

use App\Mail\OrderStatusMail;
use App\Models\Account;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
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
        $item = $user->cartItems()->where('product_id', $product->id)->sole();
        $this->post(route('checkout.select'), ['items' => [$item->id]])->assertRedirect(route('checkout.index'));
        $this->get(route('checkout.index'))->assertOk();

        return $this->payload();
    }

    private function assertAdminOrderOptions(Order $order, array $expected): void
    {
        $page = $this->get(route('admin.orders.index'))->assertOk()->getContent();
        $select = '<select id="order-status-'.$order->id.'" name="status">';
        preg_match('~'.preg_quote($select, '~').'(.*?)</select>~s', $page, $statusSelect);
        preg_match_all('~<option value="([A-Z]+)">~', $statusSelect[1] ?? '', $options);

        $this->assertSame($expected, $options[1]);
    }

    public function test_guests_must_login_for_all_shopping_writes_and_checkout(): void
    {
        $product = $this->product();
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertRedirect(route('login'));
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), [])->assertRedirect(route('login'));
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
        $this->get(route('account.orders'))->assertOk()->assertSee($order->order_code)->assertSee('Đã xác nhận');
    }

    public function test_checkout_purchases_selected_items_and_leaves_the_rest_in_the_cart(): void
    {
        $user = $this->customer();
        $selected = $this->product(['name' => 'Món được chọn']);
        $unselected = $this->product(['name' => 'Món để dành']);
        $this->post(route('cart.store', $unselected), ['quantity' => 3])->assertSessionHasNoErrors();
        $data = $this->prepare($user, $selected, 2);
        $this->get(route('checkout.index'))->assertSee('Món được chọn')->assertDontSee('Món để dành');
        $this->post(route('checkout.store'), $data)->assertSessionHasNoErrors();

        $order = Order::sole();
        $this->assertSame($selected->id, $order->items()->sole()->product_id);
        $this->assertSame(100000, $order->subtotal);
        $this->assertSame(8, $selected->fresh()->quantity);
        $this->assertSame(10, $unselected->fresh()->quantity);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $unselected->id, 'quantity' => 3]);
    }

    public function test_checkout_requires_a_selection_from_the_current_customers_cart(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $ownItem = $user->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);
        $other = User::factory()->create();
        $otherItem = $other->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);

        $this->get(route('checkout.index'))->assertRedirect(route('cart.index'))->assertSessionHasErrors('items');
        $this->from(route('cart.index'))->post(route('checkout.select'), [])->assertSessionHasErrors('items');
        $this->post(route('checkout.select'), ['items' => [$ownItem->id, $otherItem->id]])
            ->assertRedirect(route('cart.index'))->assertSessionHasErrors('items')
            ->assertSessionMissing('checkout_token');
    }

    public function test_buy_now_only_selects_the_requested_product(): void
    {
        $this->customer();
        $other = $this->product(['name' => 'Món đã có trong giỏ']);
        $requested = $this->product(['name' => 'Món mua ngay']);
        $this->post(route('cart.store', $other), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->post(route('cart.store', $requested), ['quantity' => 1, 'buy_now' => 1])
            ->assertRedirect(route('checkout.index'));
        $this->get(route('checkout.index'))->assertOk()->assertSee('Món mua ngay')->assertDontSee('Món đã có trong giỏ');
    }

    public function test_repeated_checkout_request_creates_one_order_only(): void
    {
        Mail::fake();
        $user = $this->customer();
        $product = $this->product();
        $data = $this->prepare($user, $product);
        $this->post(route('checkout.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('checkout.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(8, $product->fresh()->quantity);
        Mail::assertSent(OrderStatusMail::class, 1);
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

    public function test_scoped_promotions_show_and_charge_the_best_active_sale_price_automatically(): void
    {
        $user = $this->customer();
        $included = $this->product(['name' => 'Sản phẩm ưu đãi', 'price' => 50000]);
        $excluded = $this->product(['name' => 'Sản phẩm nguyên giá', 'price' => 30000]);
        $other = $this->product(['name' => 'Sản phẩm chưa chọn']);
        $promotion = Promotion::create([
            'name' => 'Ưu đãi theo sản phẩm', 'discount_percent' => 20,
            'starts_at' => today(), 'ends_at' => today(), 'is_active' => true,
            'applies_to_all_products' => false,
        ]);
        $promotion->products()->attach($included);
        $unavailablePromotion = $promotion->replicate()->fill(['name' => 'Ưu đãi ngoài giỏ']);
        $unavailablePromotion->save();
        $unavailablePromotion->products()->attach($other);
        foreach ([
            ['discount_percent' => 5],
            ['discount_percent' => 90, 'starts_at' => today()->subDays(2), 'ends_at' => today()->subDay()],
            ['discount_percent' => 90, 'is_active' => false],
            ['discount_percent' => 90, 'starts_at' => today()->addDay(), 'ends_at' => today()->addWeek()],
        ] as $changes) {
            $offer = $promotion->replicate()->fill($changes);
            $offer->save();
            $offer->products()->attach($included);
        }

        $this->get(route('products.show', $included))->assertOk()->assertSee('Giá gốc')->assertSee('50.000đ')->assertSee('40.000đ');
        $this->get(route('products.index', ['q' => $included->name, 'sort' => 'price_asc']))
            ->assertOk()->assertSee('Giá gốc')->assertSee('40.000đ');
        $this->get(route('products.show', $excluded))->assertOk()->assertSee('30.000đ');

        $this->post(route('cart.store', $included), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->post(route('cart.store', $excluded), ['quantity' => 1])->assertSessionHasNoErrors();
        $cartItem = $user->cartItems()->where('product_id', $included->id)->sole();
        $this->putJson(route('cart.update', $cartItem), ['quantity' => 2])
            ->assertOk()->assertJsonPath('cart_item.unit_price', 40000)->assertJsonPath('cart_item.regular_unit_price', 50000);
        $this->get(route('cart.index'))->assertOk()->assertSee('80.000đ');
        $this->post(route('checkout.select'), ['items' => $user->cartItems()->pluck('id')->all()])
            ->assertRedirect(route('checkout.index'));
        $this->get(route('checkout.index'))->assertOk()->assertSee('Đã giảm trên sản phẩm')
            ->assertSee('20.000đ')->assertSee('135.000đ')->assertDontSee($unavailablePromotion->name);

        $data = $this->payload();
        $this->post(route('checkout.store'), $data + ['promotion_id' => $unavailablePromotion->id])
            ->assertSessionHasErrors('promotion_id');
        $this->assertDatabaseEmpty('orders');
        $this->post(route('checkout.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'subtotal' => 110000, 'discount_amount' => 0, 'total_amount' => 135000,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $included->id, 'unit_price' => 40000]);
        $this->assertDatabaseHas('order_items', ['product_id' => $excluded->id, 'unit_price' => 30000]);
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
        Mail::fake();
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('checkout.store'), $this->prepare($user, $product));
        $order = Order::sole();
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $url = route('admin.orders.update', $order);
        $this->actingAs($admin);
        $this->assertSame('CONFIRMED', $order->status);
        $this->assertAdminOrderOptions($order, ['SHIPPING', 'CANCELLED']);
        $this->put($url, ['status' => 'PROCESSING'])->assertSessionHasErrors('order');
        $ordersUrl = route('admin.orders.index', ['q' => $order->order_code]);
        $this->from($ordersUrl)->put($url, ['status' => 'COMPLETED'])
            ->assertRedirect($ordersUrl)->assertSessionHasErrors('order');
        $this->get($ordersUrl)->assertOk()
            ->assertSee('data-state="error"', false)
            ->assertSee('Không thể thay đổi trạng thái đơn hàng ở bước này.')
            ->assertDontSee('feedback-message feedback-error', false);

        $this->from($ordersUrl)->put($url, ['status' => 'SHIPPING'])
            ->assertRedirect($ordersUrl)->assertSessionHasNoErrors();
        $this->get($ordersUrl)->assertOk()
            ->assertSee('data-state="success"', false)
            ->assertSee('Đã cập nhật đơn hàng.')
            ->assertDontSee('feedback-message auth-success', false);
        $this->assertSame('SHIPPING', $order->fresh()->status);
        Mail::assertSent(OrderStatusMail::class, 2);
        $this->assertAdminOrderOptions($order, ['COMPLETED']);
        $this->put($url, ['status' => 'DELIVERED'])->assertSessionHasErrors('order');
        $this->put($url, ['status' => 'CANCELLED'])->assertSessionHasErrors('order');
        $this->put($url, ['status' => 'COMPLETED'])->assertSessionHasNoErrors();
        $this->assertSame('COMPLETED', $order->fresh()->status);
        $this->assertAdminOrderOptions($order, []);
        $this->actingAs($user->account)->post(route('orders.cancel', $order))->assertSessionHasErrors('order');
        $this->post(route('orders.confirm-delivery', $order))->assertSessionHasNoErrors();
        $this->assertSame(8, $product->fresh()->quantity);
        $this->post(route('orders.reorder', $order))->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $mails = Mail::sent(OrderStatusMail::class);
        $this->assertSame([
            "Đặt hàng thành công - Đơn #{$order->order_code}",
            "Đơn hàng #{$order->order_code} đang trên đường tới bạn",
            "Giao hàng thành công - Đơn #{$order->order_code}",
        ], $mails->pluck('mailSubject')->all());
        $this->assertSame([
            'Bạn đã đặt hàng thành công. Chúng tôi sẽ sớm xử lý và giao đơn cho bạn.',
            'Đơn hàng của bạn đang trên đường tới bạn, vui lòng chú ý điện thoại.',
            'Đơn hàng đã được giao thành công. Cảm ơn bạn đã mua hàng.',
        ], $mails->pluck('statusMessage')->all());
        foreach ($mails as $mail) {
            $this->assertTrue($mail->hasTo($user->email));
            $this->assertTrue($mail->order->relationLoaded('items'));
            $this->assertTrue($mail->order->items->sole()->relationLoaded('product'));
            $mail->assertHasSubject($mail->mailSubject);
            $html = $mail->render();
            foreach ([$mail->statusMessage, $user->full_name, $product->name, 'Số lượng', '50.000đ', '100.000đ', '125.000đ', '34 Đường B', '0912345678'] as $content) {
                $this->assertStringContainsString(e($content), $html);
            }
            $this->assertStringContainsString('>2</td>', $html);
        }
    }

    public function test_admin_ajax_order_updates_return_current_status_and_preserve_the_list_filter(): void
    {
        Mail::fake();
        $this->post(route('checkout.store'), $this->prepare($this->customer(), $this->product()));
        $order = Order::sole();
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $search = ['q' => $order->order_code];
        $update = route('admin.orders.update', ['order' => $order, ...$search]);

        $this->putJson($update, ['status' => 'COMPLETED'])->assertUnprocessable()->assertJsonValidationErrors('order');
        $response = $this->post($update, ['_method' => 'PUT', 'status' => 'SHIPPING'], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('url', route('admin.orders.index', $search));
        $this->assertStringContainsString('data-order-status="SHIPPING"', $response->json('html'));
        $this->assertStringContainsString('value="COMPLETED"', $response->json('html'));

        $filter = [...$search, 'status' => 'SHIPPING'];
        $response = $this->putJson(route('admin.orders.update', ['order' => $order, ...$filter, 'page' => 2]), ['status' => 'COMPLETED'])
            ->assertOk()->assertJsonPath('total', 0)->assertJsonPath('url', route('admin.orders.index', $filter));
        $this->assertStringContainsString('Chưa có đơn hàng phù hợp.', $response->json('html'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'COMPLETED']);
    }

    public function test_only_the_owner_can_confirm_a_shipping_order_and_receive_one_delivery_mail(): void
    {
        Mail::fake();
        $user = $this->customer();
        $product = $this->product();
        $this->post(route('checkout.store'), $this->prepare($user, $product))->assertSessionHasNoErrors();
        $order = Order::sole();
        $url = route('orders.confirm-delivery', $order);
        $this->post($url)->assertSessionHasErrors('order');

        $order->update(['status' => 'SHIPPING']);
        $order->update(['status' => 'SHIPPING']);
        $order->update(['recipient_phone' => '']);
        Mail::assertSent(OrderStatusMail::class, 2);
        $this->get(route('account.orders'))->assertOk()->assertSee('Xác nhận đã nhận hàng');

        $other = User::factory()->create();
        $this->actingAs($other->account)->post($url)->assertNotFound();
        $this->actingAs($user->account)->post($url)->assertSessionHasNoErrors();
        $this->post($url)->assertSessionHasNoErrors();
        $this->assertSame('DELIVERED', $order->fresh()->status);
        Mail::assertSent(OrderStatusMail::class, 3);
        $deliveryMail = Mail::sent(OrderStatusMail::class)->last();
        $deliveryMail->assertHasSubject("Giao hàng thành công - Đơn #{$order->order_code}");
        $deliveryMail->assertDontSeeInHtml('Số điện thoại');

        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'COMPLETED'])->assertSessionHasNoErrors();
        $this->assertSame('COMPLETED', $order->fresh()->status);
        Mail::assertSent(OrderStatusMail::class, 3);
    }

    public function test_order_mails_skip_missing_email_and_rolled_back_status_updates(): void
    {
        Mail::fake();
        $user = $this->customer();
        $user->update(['email' => '']);
        $product = $this->product();
        $this->post(route('checkout.store'), $this->prepare($user, $product))->assertSessionHasNoErrors();
        $order = Order::sole();
        $order->update(['status' => 'SHIPPING']);
        Mail::assertNothingSent();

        $user->update(['email' => 'buyer@example.test']);
        DB::beginTransaction();
        try {
            $order->update(['status' => 'DELIVERED']);
            Mail::assertNothingSent();
        } finally {
            DB::rollBack();
        }
        $this->assertSame('SHIPPING', $order->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_checkout_and_status_update_succeed_when_mail_fails(): void
    {
        $user = $this->customer();
        $product = $this->product();
        Event::listen(MessageSending::class, static function (): never {
            throw new RuntimeException('SMTP unavailable');
        });
        Log::shouldReceive('error')->with('Không thể gửi email thông báo đơn hàng.', \Mockery::type('array'))->twice();

        $this->post(route('checkout.store'), $this->prepare($user, $product))->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertDatabaseCount('order_items', 1);
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'SHIPPING'])->assertSessionHasNoErrors();
        $this->assertSame('SHIPPING', $order->fresh()->status);
    }

    public function test_order_mail_retries_a_dropped_smtp_handshake_only(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $attempts = [];
        $sent = [];
        Event::listen(MessageSending::class, function (MessageSending $event) use (&$attempts): void {
            $status = $event->data['order']->status;
            $attempts[] = $status;
            $exception = new TransportException('Connection to SMTP has been closed unexpectedly.');
            $exception->appendDebug("[test] > EHLO localhost\n");

            if ($status === 'SHIPPING' && count(array_keys($attempts, 'SHIPPING', true)) === 1) {
                throw $exception;
            }
            if ($status === 'COMPLETED') {
                $exception->appendDebug("[test] > MAIL FROM:<sender@example.test>\n[test] > DATA\n");
                throw $exception;
            }
        });
        Event::listen(MessageSent::class, function (MessageSent $event) use (&$sent): void {
            $sent[] = $event->data['order']->status;
        });
        Log::shouldReceive('error')->with('Không thể gửi email thông báo đơn hàng.', \Mockery::on(
            fn (array $context) => $context['status'] === 'COMPLETED',
        ))->once();

        $this->post(route('checkout.store'), $this->prepare($user, $product))->assertSessionHasNoErrors();
        $order = Order::sole();
        $url = route('admin.orders.update', $order);
        $this->actingAs(Account::factory()->create(['role' => 'ADMIN']));
        $this->put($url, ['status' => 'SHIPPING'])->assertSessionHasNoErrors();
        $this->put($url, ['status' => 'COMPLETED'])->assertSessionHasNoErrors();

        $this->assertSame(['CONFIRMED', 'SHIPPING', 'SHIPPING', 'COMPLETED'], $attempts);
        $this->assertSame(['CONFIRMED', 'SHIPPING'], $sent);
        $this->assertSame('COMPLETED', $order->fresh()->status);
    }

    public function test_favorites_routes_have_been_removed(): void
    {
        $this->customer();
        $product = $this->product();
        $this->get('/yeu-thich')->assertNotFound();
        $this->post('/yeu-thich/'.$product->id)->assertNotFound();
        $this->delete('/yeu-thich/'.$product->id)->assertNotFound();
    }

    public function test_reviews_publish_after_completed_purchase_and_only_admin_can_delete(): void
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
        $this->actingAs($user->account)->postJson(route('reviews.store', $item), $data)
            ->assertCreated()->assertJsonPath('message', 'Đánh giá đã được hiển thị.');
        $this->post(route('reviews.store', $item), $data)->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 1);
        $review = Review::sole();
        $this->assertSame('VISIBLE', $review->status);
        $this->get(route('products.show', $product))->assertOk()->assertSee($data['comment'])->assertSee('5,0');
        $this->get(route('products.show', ['product' => $product, 'rating' => 4]))
            ->assertOk()->assertSee('Chưa có đánh giá 4 sao.')->assertDontSee($data['comment']);
        $review->update(['status' => 'PENDING']);
        $this->get(route('products.show', $product))->assertSee($data['comment']);
        $review->update(['status' => 'HIDDEN']);
        $this->get(route('products.show', $product))->assertDontSee($data['comment']);
        $this->delete(route('admin.reviews.destroy', $review))->assertForbidden();
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $review->update(['status' => 'VISIBLE']);
        $reviewUrl = route('products.show', ['product' => $product, 'review' => $review->id]).'#danh-gia-'.$review->id;
        $replyRoute = route('admin.reviews.reply', [$product, $review]);
        $this->put($replyRoute, ['admin_reply' => 'Không được lưu'])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertSee($reviewUrl);
        $this->get($reviewUrl)->assertOk()->assertSee($replyRoute);
        $this->put($replyRoute, ['admin_reply' => 'Cảm ơn bạn đã đánh giá.'])->assertRedirect($reviewUrl);
        $this->putJson($replyRoute, ['admin_reply' => 'Cảm ơn bạn đã đánh giá.'])
            ->assertOk()->assertJsonPath('reply', 'Cảm ơn bạn đã đánh giá.');
        $this->actingAs($user->account)->get($reviewUrl)->assertOk()
            ->assertSee('Cảm ơn bạn đã đánh giá.')->assertDontSee($replyRoute);
        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->get(route('products.show', $product))->assertOk()->assertDontSee($data['comment']);
    }

    public function test_search_filters_database_products_by_name_or_brand(): void
    {
        $product = $this->product(['name' => 'TimThay123']);
        $this->product(['name' => 'Không phù hợp', 'brand' => 'Khac']);
        $this->get(route('products.index', ['q' => 'TimThay123']))->assertOk()->assertSee($product->name)->assertDontSee('Không phù hợp');
        $this->get(route('products.index', ['q' => 'ThuongHieu']))->assertViewHas('products', fn ($products) => $products->total() === 1);
    }

    public function test_admin_dashboard_shows_daily_units_from_fulfilled_orders(): void
    {
        $this->travelTo(today()->setDate(2026, 11, 24));
        $user = $this->customer();
        $product = $this->product();
        $admin = Account::factory()->create(['role' => 'ADMIN']);

        foreach ([
            ['2026-11-17', 'COMPLETED', [20]],
            ['2026-11-18', 'COMPLETED', [2, 3]],
            ['2026-11-18', 'DELIVERED', [4]],
            ['2026-11-21', 'CANCELLED', [20]],
            ['2026-11-21', 'SHIPPING', [20]],
            ['2026-11-24', 'DELIVERED', [6]],
            ['2026-11-25', 'COMPLETED', [20]],
        ] as $index => [$date, $status, $quantities]) {
            $order = $user->orders()->create([
                'order_code' => 'SALES-'.$index, 'status' => $status, 'created_at' => $date,
                'recipient_name' => $user->full_name, 'recipient_phone' => $user->phone,
                'shipping_address' => $user->address, 'subtotal' => 10000, 'total_amount' => 10000,
                'payment_method' => 'COD',
            ]);
            foreach ($quantities as $quantity) {
                $order->items()->create([
                    'product_id' => $product->id, 'product_name' => $product->name,
                    'unit_price' => $product->price, 'quantity' => $quantity,
                ]);
            }
        }

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('dailySales', fn ($sales) => $sales->pluck('units')->all() === [9, 0, 0, 0, 0, 0, 6])
            ->assertViewHas('peakDailySales', 9)
            ->assertSeeInOrder(['Sản phẩm bán theo ngày', 'Đơn hàng gần đây'])
            ->assertDontSee('Tồn kho từ 5 sản phẩm trở xuống')
            ->assertDontSee('Hết hạn / hết hạn trong 30 ngày');
    }

    public function test_admin_forms_crud_stock_prices_categories_promotions_and_posts_work(): void
    {
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $this->actingAs($admin);
        foreach (['products', 'categories', 'promotions', 'posts'] as $resource) {
            $this->get(route('admin.resources.index', $resource))->assertOk();
            if ($resource !== 'products') {
                $this->get(route('admin.resources.create', $resource))->assertOk();
            }
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
