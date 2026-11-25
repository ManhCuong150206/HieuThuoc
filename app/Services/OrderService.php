<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const SHIPPING_FEE = 25000;

    public const STATUS_LABELS = [
        'PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận',
        'PROCESSING' => 'Đang xử lý', 'SHIPPING' => 'Đang giao',
        'DELIVERED' => 'Đã giao', 'COMPLETED' => 'Hoàn thành', 'CANCELLED' => 'Đã hủy',
    ];

    public const ADMIN_TRANSITIONS = [
        'PENDING' => ['CONFIRMED', 'CANCELLED'],
        'CONFIRMED' => ['SHIPPING', 'CANCELLED'],
        'PROCESSING' => ['SHIPPING', 'CANCELLED'],
        'SHIPPING' => ['COMPLETED'],
        'DELIVERED' => ['COMPLETED'],
    ];

    private const CUSTOMER_TRANSITIONS = [
        'PENDING' => ['CANCELLED'],
        'CONFIRMED' => ['CANCELLED'],
        'PROCESSING' => ['CANCELLED'],
        'SHIPPING' => ['DELIVERED'],
    ];

    public function __construct(private CartService $cart, private OrderMailService $mail) {}

    public function place(Account $account, array $data, string $token, array $itemIds): Order
    {
        $order = DB::transaction(function () use ($account, $data, $token, $itemIds): Order {
            $profile = $this->cart->profile($account, true);
            $code = 'AT'.strtoupper(substr(hash('sha256', $token), 0, 24));
            $existing = $profile->orders()->where('order_code', $code)->first();
            if ($existing) {
                return $existing;
            }
            $items = $profile->cartItems()->whereKey($itemIds)->orderBy('product_id')->lockForUpdate()->get();
            if ($items->isEmpty() || $items->count() !== count($itemIds)) {
                throw ValidationException::withMessages(['cart' => 'Sản phẩm đã chọn thay đổi. Vui lòng kiểm tra lại giỏ hàng.']);
            }
            $products = Product::whereIn('id', $items->pluck('product_id'))->orderBy('id')
                ->lockForUpdate()->get()->keyBy('id');
            $percentages = Promotion::productOffers()->whereIntegerInRaw('product_promotion.product_id', $products->modelKeys())
                ->orderBy('promotions.id')->orderBy('product_promotion.product_id')->lockForUpdate()
                ->get(['product_promotion.product_id', 'promotions.discount_percent'])
                ->groupBy('product_id')->map->max('discount_percent');
            $unitPrices = [];
            $subtotal = 0;
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product) {
                    throw ValidationException::withMessages(['cart' => 'Có sản phẩm không còn tồn tại.']);
                }
                $this->cart->ensureAvailable($product, $item->quantity);
                $price = Product::discountedUnitPrice($product->regularUnitPrice(), (int) $percentages->get($product->id));
                $unitPrices[$product->id] = $price;
                $subtotal += $price * $item->quantity;
            }
            if ($subtotal > 2000000000 - self::SHIPPING_FEE) {
                throw ValidationException::withMessages(['cart' => 'Giá trị đơn hàng vượt giới hạn cho phép.']);
            }
            $promotion = null;
            $discount = 0;
            if (! empty($data['promotion_id'])) {
                $promotion = Promotion::availableAtCheckout()->whereKey($data['promotion_id'])
                    ->lockForUpdate()->first();
                if (! $promotion) {
                    throw ValidationException::withMessages(['promotion_id' => 'Ưu đãi không còn hiệu lực.']);
                }
                if ($promotion->applies_to_all_products) {
                    $discount = $promotion->discountForSubtotal($subtotal);
                } elseif (! $promotion->products()->whereIntegerInRaw('products.id', $products->modelKeys())
                    ->lockForUpdate()->first(['products.id'])) {
                    throw ValidationException::withMessages(['promotion_id' => 'Ưu đãi không áp dụng cho các sản phẩm đã chọn.']);
                }
            }
            $order = $profile->orders()->create([
                'order_code' => $code, 'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'], 'shipping_address' => $data['shipping_address'],
                'subtotal' => $subtotal, 'promotion_id' => $promotion?->id, 'discount_amount' => $discount,
                'shipping_fee' => self::SHIPPING_FEE, 'total_amount' => $subtotal - $discount + self::SHIPPING_FEE,
                'payment_method' => 'COD', 'status' => 'CONFIRMED', 'created_at' => today(),
            ]);
            foreach ($items as $item) {
                $product = $products[$item->product_id];
                $order->items()->create([
                    'product_id' => $product->id, 'product_name' => $product->name,
                    'unit_price' => $unitPrices[$product->id], 'quantity' => $item->quantity,
                ]);
                $product->decrement('quantity', $item->quantity);
            }
            $order->payments()->create([
                'method' => 'COD', 'amount' => $order->total_amount, 'status' => 'SUCCESS',
                'created_at' => today(), 'paid_at' => today(),
            ]);
            $profile->cartItems()->whereKey($itemIds)->delete();

            return $order;
        }, 3);

        if ($order->wasRecentlyCreated) {
            $order->getConnection()->afterCommit(fn () => $this->mail->send($order, 'CONFIRMED'));
        }

        return $order;
    }

    public function transition(Order $order, string $status, bool $customer = false): void
    {
        DB::transaction(function () use ($order, $status, $customer): void {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $transitions = $customer ? self::CUSTOMER_TRANSITIONS : self::ADMIN_TRANSITIONS;
            if (($status === 'CANCELLED' && $order->status === 'CANCELLED')
                || ($customer && $status === 'DELIVERED' && in_array($order->status, ['DELIVERED', 'COMPLETED'], true))) {
                return;
            }
            if (! in_array($status, $transitions[$order->status] ?? [], true)) {
                throw ValidationException::withMessages(['order' => 'Không thể thay đổi trạng thái đơn hàng ở bước này.']);
            }
            if ($status === 'CANCELLED') {
                foreach ($order->items()->orderBy('product_id')->get() as $item) {
                    Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail()->increment('quantity', $item->quantity);
                }
                $order->payments()->update(['status' => 'FAILED', 'paid_at' => null]);
            }
            $order->update(['status' => $status]);
        }, 3);
    }

    public function reorder(Account $account, Order $order): void
    {
        DB::transaction(function () use ($account, $order): void {
            $profile = $this->cart->profile($account, true);
            foreach ($order->items()->orderBy('product_id')->get() as $item) {
                $this->cart->add($account, $item->product_id, $item->quantity);
            }
        }, 3);
    }
}
