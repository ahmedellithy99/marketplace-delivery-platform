<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\OrderStoreRequest;
use App\Models\Order;
use App\Services\Customer\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $order = $this->orderService->placeOrder($user, $request->validated());

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order placed successfully.');
    }

    public function show(Order $order): Response
    {
        $order = $this->orderService->getOrder($order);

        return Inertia::render('Customer/Orders/Show', [
            'order' => $order,
        ]);
    }
}
