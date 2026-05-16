<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\StoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Products/Index', [
            'products' => $this->storeService->getProducts($request),
            'filters' => $request->only(['search', 'category', 'store', 'price_min', 'price_max', 'sort']),
        ]);
    }
}
