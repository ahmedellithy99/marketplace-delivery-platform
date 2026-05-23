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
        $month = $request->get('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $deliveries = Delivery::with(['order', 'deliveryMan'])
            ->whereYear('assigned_at', $year)
            ->whereMonth('assigned_at', $monthNum)
            ->orderByDesc('assigned_at')
            ->paginate(15)
            ->appends($request->query());

        // Delivery men earnings for the selected month
        $deliveryMenEarnings = \App\Models\User::where('role', 'delivery')
            ->withCount(['deliveries as month_deliveries_count' => function ($q) use ($year, $monthNum) {
                $q->whereYear('assigned_at', $year)
                  ->whereMonth('assigned_at', $monthNum)
                  ->whereNotNull('delivered_at');
            }])
            ->get()
            ->map(function ($user) use ($year, $monthNum) {
                $totalFees = Delivery::where('delivery_man_id', $user->id)
                    ->whereYear('assigned_at', $year)
                    ->whereMonth('assigned_at', $monthNum)
                    ->whereNotNull('delivered_at')
                    ->join('orders', 'deliveries.order_id', '=', 'orders.id')
                    ->sum('orders.delivery_fee');

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'deliveries_count' => $user->month_deliveries_count,
                    'total_fees' => round((float) $totalFees, 2),
                ];
            })
            ->sortByDesc('total_fees')
            ->values();

        return Inertia::render('Admin/Deliveries/Index', [
            'deliveries' => $deliveries,
            'currentMonth' => $month,
            'deliveryMenEarnings' => $deliveryMenEarnings,
        ]);
    }
}
