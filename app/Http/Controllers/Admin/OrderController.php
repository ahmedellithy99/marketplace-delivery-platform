<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderAcceptRequest;
use App\Http\Requests\Admin\OrderAssignDeliveryRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of orders with filters.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $this->orderService->getAdminOrders($request),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', [
            'order' => $this->orderService->getOrder($order),
        ]);
    }

    /**
     * Accept a pending order with delivery fee.
     */
    public function accept(OrderAcceptRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->acceptOrder($order, (float) $request->validated('delivery_fee'));

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order accepted successfully.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->orderService->cancelOrder($order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Assign a delivery man to an order.
     */
    public function assignDelivery(OrderAssignDeliveryRequest $request, Order $order): RedirectResponse
    {
        $deliveryMan = User::findOrFail($request->validated('delivery_man_id'));

        $this->orderService->assignDelivery($order, $deliveryMan, $request->user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Delivery man assigned successfully.');
    }
}
