<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\PublicStoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(
        protected PublicStoreService $publicStoreService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Stores/Index', [
            'stores' => $this->publicStoreService->getStores($request),
            'filters' => $request->only(['search', 'type', 'sort']),
        ]);
    }

    public function show(Store $store): Response
    {
        return Inertia::render('Stores/Show', [
            'store' => $this->publicStoreService->getStoreDetails($store),
        ]);
    }
}
