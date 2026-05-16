<?php

namespace App\Http\Controllers;

use App\Services\Public\StoreService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Home', [
            'featuredStores' => $this->storeService->getFeaturedStores(),
            'featuredProducts' => $this->storeService->getFeaturedProducts(),
        ]);
    }
}
