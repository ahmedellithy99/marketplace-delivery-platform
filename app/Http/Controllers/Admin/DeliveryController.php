<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\DeliveryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->deliveryService->getDeliveries($request);

        return Inertia::render('Admin/Deliveries/Index', $data);
    }

    /**
     * Show a delivery man's profile with all their deliveries and stats.
     */
    public function show(Request $request, User $user): Response
    {
        $data = $this->deliveryService->getDeliveryManProfile($user, $request);

        return Inertia::render('Admin/Deliveries/Show', $data);
    }
}
