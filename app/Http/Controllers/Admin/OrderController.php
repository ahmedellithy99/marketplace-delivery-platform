<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderAcceptRequest;
use App\Http\Requests\Admin\OrderAssignDeliveryRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\Admin\OrderService;
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
            'groupedItems' => $this->orderService->getOrderGroupedByStore($order),
            'deliveryPersonnel' => User::where('role', 'delivery')
                ->withCount(['deliveries as active_deliveries_count' => function ($q) {
                    $q->whereNull('delivered_at');
                }])
                ->get(['id', 'name', 'phone']),
        ]);
    }

    /**
     * Accept a pending order with delivery fee.
     */
    public function accept(OrderAcceptRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->acceptOrder($order, (float) $request->validated('delivery_fee'));

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'تم قبول الطلب بنجاح.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->orderService->cancelOrder($order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'تم إلغاء الطلب بنجاح.');
    }

    /**
     * Assign a delivery man to an order.
     */
    public function assignDelivery(OrderAssignDeliveryRequest $request, Order $order): RedirectResponse
    {
        $deliveryMan = User::findOrFail($request->validated('delivery_man_id'));

        $this->orderService->assignDelivery($order, $deliveryMan, $request->user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'تم تعيين مندوب التوصيل بنجاح.');
    }

    /**
     * Cancel and archive an order (no hard delete — preserves records).
     */
    public function destroy(Order $order): RedirectResponse
    {
        // Cancel the order instead of deleting
        $order->update(['status' => 'cancelled']);

        // Remove delivery assignment if exists
        if ($order->delivery) {
            $order->delivery()->delete();
        }

        return redirect()->route('admin.orders.index')
            ->with('success', 'تم إلغاء الطلب بنجاح.');
    }
}
