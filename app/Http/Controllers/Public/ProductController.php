<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicStoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        protected PublicStoreService $publicStoreService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Products/Index', [
            'products' => $this->publicStoreService->getProducts($request),
            'filters' => $request->only(['search', 'category', 'store', 'price_min', 'price_max', 'sort']),
        ]);
    }
}
