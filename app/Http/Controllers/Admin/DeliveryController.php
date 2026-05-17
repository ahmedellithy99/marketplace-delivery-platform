<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    public function index(Request $request): Response
    {
        $deliveries = Delivery::with(['order', 'deliveryMan'])
            ->orderByDesc('assigned_at')
            ->paginate(15)
            ->appends($request->query());

        return Inertia::render('Admin/Deliveries/Index', [
            'deliveries' => $deliveries,
        ]);
    }
}
