<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Queries\Admin\OrderQuery;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderQuery $orders) {}

    public function index(Request $request): View
    {
        return view('pages.admin.orders', $this->orderResults($this->orderFilters($request)));
    }

    private function orderFilters(Request $request): array
    {
        return Validator::make($request->query(), [
            'status' => ['nullable', Rule::in(array_keys(OrderService::STATUS_LABELS))],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ])->validate();
    }

    private function orderResults(array $data): array
    {
        $filters = ['q' => $data['q'] ?? null, 'status' => $data['status'] ?? null];
        $orders = $this->orders->paginate($data);
        $orders->withPath(route('admin.orders.index'))->appends($filters);

        return [
            'orders' => $orders,
            'orderListQuery' => [...$filters, 'page' => $orders->currentPage() > 1 ? $orders->currentPage() : null],
            'statusLabels' => OrderService::STATUS_LABELS,
            'statusTransitions' => OrderService::ADMIN_TRANSITIONS,
        ];
    }

    public function update(Request $request, Order $order, OrderService $service): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(OrderService::STATUS_LABELS))]]);
        $filters = $request->expectsJson() ? $this->orderFilters($request) : null;
        $service->transition($order, $data['status']);

        $message = 'Đã cập nhật đơn hàng.';
        if ($filters !== null) {
            $results = $this->orderResults($filters);

            return response()->json([
                'message' => $message,
                'html' => view('pages.admin.order-results', $results)->render(),
                'total' => $results['orders']->total(),
                'url' => route('admin.orders.index', $results['orderListQuery']),
            ]);
        }

        return back()->with('status', $message);
    }
}
