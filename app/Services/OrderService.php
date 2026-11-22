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

    public function __construct(private CartService $cart) {}

    public function place(Account $account, array $data, string $token): Order
    {
        return DB::transaction(function () use ($account, $data, $token): Order {
            $profile = $this->cart->profile($account, true);
            $code = 'AT'.strtoupper(substr(hash('sha256', $token), 0, 24));
            $existing = $profile->orders()->where('order_code', $code)->first();
            if ($existing) {
                return $existing;
            }
            $items = $profile->cartItems()->orderBy('product_id')->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }
            $products = Product::whereIn('id', $items->pluck('product_id'))->orderBy('id')
                ->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product) {
                    throw ValidationException::withMessages(['cart' => 'Có sản phẩm không còn tồn tại.']);
                }
                $this->cart->ensureAvailable($product, $item->quantity);
                $subtotal += (int) round($product->price) * $item->quantity;
            }
            if ($subtotal > 2000000000 - self::SHIPPING_FEE) {
                throw ValidationException::withMessages(['cart' => 'Giá trị đơn hàng vượt giới hạn cho phép.']);
            }
            $promotion = null;
            if (! empty($data['promotion_id'])) {
                $promotion = Promotion::whereKey($data['promotion_id'])->where('is_active', true)
                    ->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today())
                    ->lockForUpdate()->first();
                if (! $promotion || $promotion->discount_percent < 0 || $promotion->discount_percent > 100) {
                    throw ValidationException::withMessages(['promotion_id' => 'Ưu đãi không còn hiệu lực.']);
                }
            }
            $discount = (int) floor($subtotal * ($promotion?->discount_percent ?? 0) / 100);
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
                    'unit_price' => (int) round($product->price), 'quantity' => $item->quantity,
                ]);
                $product->decrement('quantity', $item->quantity);
            }
            $order->payments()->create([
                'method' => 'COD', 'amount' => $order->total_amount, 'status' => 'SUCCESS',
                'created_at' => today(), 'paid_at' => today(),
            ]);
            $profile->cartItems()->delete();

            return $order;
        }, 3);
    }

    public function transition(Order $order, string $status, bool $customer = false): void
    {
        DB::transaction(function () use ($order, $status, $customer): void {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $allowed = [
                'PENDING' => ['CONFIRMED', 'CANCELLED'],
                'CONFIRMED' => ['PROCESSING', 'SHIPPING', 'CANCELLED'],
                'PROCESSING' => ['SHIPPING', 'CANCELLED'],
                'SHIPPING' => ['DELIVERED'], 'DELIVERED' => ['COMPLETED'],
            ];
            if ($status === $order->status && $status === 'CANCELLED') {
                return;
            }
            if (($customer && $status !== 'CANCELLED') || ! in_array($status, $allowed[$order->status] ?? [], true)) {
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
