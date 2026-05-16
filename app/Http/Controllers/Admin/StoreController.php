<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStoreRequest;
use App\Http\Requests\Admin\StoreUpdateRequest;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Stores/Index', [
            'stores' => $this->storeService->getStores($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Stores/Create', [
            'storeTypes' => $this->storeService->getStoreTypeOptions(),
        ]);
    }

    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $this->storeService->createStore(
            $request->validated(),
            $request->file('logo'),
            $request->file('cover')
        );

        return redirect()->route('super_admin.stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function edit(Store $store): Response
    {
        return Inertia::render('Admin/Stores/Edit', [
            'store' => $this->storeService->getStore($store),
            'storeTypes' => $this->storeService->getStoreTypeOptions(),
        ]);
    }

    public function update(StoreUpdateRequest $request, Store $store): RedirectResponse
    {
        $this->storeService->updateStore(
            $store,
            $request->validated(),
            $request->file('logo'),
            $request->file('cover')
        );

        return redirect()->route('super_admin.stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $this->storeService->deleteStore($store);

        return redirect()->route('super_admin.stores.index')
            ->with('success', 'Store deleted successfully.');
    }
}
