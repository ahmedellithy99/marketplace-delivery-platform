<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Public\StoreService;
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
        return Inertia::render('Stores/Index', [
            'stores' => $this->storeService->getStores($request),
            'filters' => $request->only(['search', 'type', 'sort']),
            'storeTypes' => \App\Models\StoreType::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Store $store, Request $request): Response
    {
        $store->load(['storeType', 'media']);

        // Get categories for this store's products
        $categories = \App\Models\Category::whereHas('products', function ($q) use ($store) {
            $q->where('store_id', $store->id)->where('is_available', true);
        })->orderBy('name')->get(['id', 'name']);

        // Get filtered products
        $products = $store->products()
            ->where('is_available', true)
            ->with(['category', 'media', 'variants.discounts', 'discounts'])
            ->when($request->get('category'), fn ($q, $cat) => $q->where('category_id', $cat))
            ->when($request->get('search'), fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return Inertia::render('Stores/Show', [
            'store' => $store,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }
}
