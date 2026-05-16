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
        ]);
    }

    public function show(Store $store): Response
    {
        return Inertia::render('Stores/Show', [
            'store' => $this->storeService->getStoreDetails($store),
        ]);
    }
}
