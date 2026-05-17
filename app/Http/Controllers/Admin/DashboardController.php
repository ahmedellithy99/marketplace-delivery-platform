<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the admin dashboard with statistics.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', $this->dashboardService->getDashboardStats());
    }
}
