<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryManStoreRequest;
use App\Http\Requests\Admin\DeliveryManUpdateRequest;
use App\Models\User;
use App\Services\Admin\DeliveryManService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryManController extends Controller
{
    public function __construct(
        protected DeliveryManService $deliveryManService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/DeliveryMen/Index', [
            'deliveryMen' => $this->deliveryManService->getDeliveryMen($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/DeliveryMen/Create');
    }

    public function store(DeliveryManStoreRequest $request): RedirectResponse
    {
        $this->deliveryManService->createDeliveryMan($request->validated());

        return redirect()->route('admin.delivery-men.index')
            ->with('success', 'تم إضافة المندوب بنجاح.');
    }

    public function edit(User $delivery_man): Response
    {
        return Inertia::render('Admin/DeliveryMen/Edit', [
            'deliveryMan' => $delivery_man,
        ]);
    }

    public function update(DeliveryManUpdateRequest $request, User $delivery_man): RedirectResponse
    {
        $this->deliveryManService->updateDeliveryMan($delivery_man, $request->validated());

        return redirect()->route('admin.delivery-men.index')
            ->with('success', 'تم تحديث بيانات المندوب بنجاح.');
    }

    public function destroy(User $delivery_man): RedirectResponse
    {
        $this->deliveryManService->deleteDeliveryMan($delivery_man);

        return redirect()->route('admin.delivery-men.index')
            ->with('success', 'تم حذف المندوب بنجاح.');
    }
}
