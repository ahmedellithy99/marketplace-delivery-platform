<?php

namespace App\Http\Controllers;

use App\Services\PublicStoreService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected PublicStoreService $publicStoreService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Home', [
            'featuredStores' => $this->publicStoreService->getFeaturedStores(),
            'featuredProducts' => $this->publicStoreService->getFeaturedProducts(),
        ]);
    }
}
