<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\Delivery\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {}

    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $deliveries = $this->deliveryService->getDeliveries($user, $request);

        return Inertia::render('Delivery/Assignments/Index', [
            'deliveries' => $deliveries,
        ]);
    }

    public function show(Delivery $delivery): Response
    {
        $delivery = $this->deliveryService->getDelivery($delivery);

        return Inertia::render('Delivery/Assignments/Show', [
            'delivery' => $delivery,
        ]);
    }

    public function markPreparing(Delivery $delivery): RedirectResponse
    {
        $this->deliveryService->markPreparing($delivery);

        return redirect()->back()->with('success', 'تم تحديث الحالة: جاري التحضير.');
    }

    public function markPickedUp(Delivery $delivery): RedirectResponse
    {
        $this->deliveryService->markPickedUp($delivery);

        return redirect()->back()->with('success', 'تم تحديث الحالة: تم الاستلام.');
    }

    public function markDelivered(Delivery $delivery): RedirectResponse
    {
        $this->deliveryService->markDelivered($delivery);

        return redirect()->back()->with('success', 'تم تحديث الحالة: تم التوصيل.');
    }
}
