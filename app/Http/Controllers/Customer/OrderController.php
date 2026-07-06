<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\OrderStoreRequest;
use App\Models\Order;
use App\Services\Customer\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $orders = $this->orderService->getOrders($user, $request);

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
            'showAll' => $request->boolean('show_all'),
        ]);
    }

    public function store(OrderStoreRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        try {
            $order = $this->orderService->placeOrder($user, $request->validated());
        } catch (InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', 'السلة فارغة. يرجى إضافة منتجات قبل الطلب.');
        }

        Log::debug('Order placed - redirecting', [
            'order_id' => $order->id,
            'order_user_id' => $order->user_id,
            'auth_user_id' => $user?->id,
            'session_id' => $request->session()->getId(),
        ]);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'تم تقديم الطلب بنجاح.');
    }

    public function show(Request $request, Order $order): Response
    {
        $authUser = $request->user();
        Log::debug('Order show ownership check', [
            'order_id' => $order->id,
            'order_user_id' => $order->user_id,
            'auth_user_id' => $authUser?->id,
            'user_is_null' => $authUser === null,
            'session_id' => $request->session()->getId(),
            'intended_url' => session()->get('url.intended'),
            'previous_url' => url()->previous(),
        ]);

        // Verify ownership
        abort_unless((int) $order->user_id === (int) $authUser?->id, 403);

        $order = $this->orderService->getOrder($order);

        return Inertia::render('Customer/Orders/Show', [
            'order' => $order,
        ]);
    }
}
