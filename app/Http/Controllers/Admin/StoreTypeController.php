<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTypeStoreRequest;
use App\Http\Requests\Admin\StoreTypeUpdateRequest;
use App\Models\StoreType;
use App\Services\Admin\StoreTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreTypeController extends Controller
{
    public function __construct(
        protected StoreTypeService $storeTypeService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/StoreTypes/Index', [
            'storeTypes' => $this->storeTypeService->getStoreTypes($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/StoreTypes/Create');
    }

    public function store(StoreTypeStoreRequest $request): RedirectResponse
    {
        $this->storeTypeService->createStoreType($request->validated());

        return redirect()->route('admin.store-types.index')
            ->with('success', 'تم إضافة نوع المتجر بنجاح.');
    }

    public function edit(StoreType $storeType): Response
    {
        return Inertia::render('Admin/StoreTypes/Edit', [
            'storeType' => $storeType,
        ]);
    }

    public function update(StoreTypeUpdateRequest $request, StoreType $storeType): RedirectResponse
    {
        $this->storeTypeService->updateStoreType($storeType, $request->validated());

        return redirect()->route('admin.store-types.index')
            ->with('success', 'تم تحديث نوع المتجر بنجاح.');
    }

    public function destroy(StoreType $storeType): RedirectResponse
    {
        $this->storeTypeService->deleteStoreType($storeType);

        return redirect()->route('admin.store-types.index')
            ->with('success', 'تم حذف نوع المتجر بنجاح.');
    }

    public function toggleActive(StoreType $storeType): RedirectResponse
    {
        $this->storeTypeService->toggleActive($storeType);

        return redirect()->back()
            ->with('success', 'تم تحديث حالة نوع المتجر.');
    }
}
