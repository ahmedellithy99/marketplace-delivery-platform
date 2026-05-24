<?php

namespace App\Services\Admin;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DeliveryService
{
    /**
     * Get paginated deliveries and delivery men earnings for a given month.
     *
     * @return array{deliveries: LengthAwarePaginator, currentMonth: string, deliveryMenEarnings: Collection}
     */
    public function getDeliveries(Request $request): array
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

        $deliveryMenEarnings = User::where('role', 'delivery')
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

        return [
            'deliveries' => $deliveries,
            'currentMonth' => $month,
            'deliveryMenEarnings' => $deliveryMenEarnings,
        ];
    }

    /**
     * Get a delivery man's profile with deliveries and stats for a given month.
     *
     * @return array{deliveryMan: array, deliveries: LengthAwarePaginator, currentMonth: string, stats: array}
     */
    public function getDeliveryManProfile(User $user, Request $request): array
    {
        $month = $request->get('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        $deliveries = Delivery::with(['order'])
            ->where('delivery_man_id', $user->id)
            ->whereYear('assigned_at', $year)
            ->whereMonth('assigned_at', $monthNum)
            ->orderByDesc('assigned_at')
            ->paginate(20)
            ->appends($request->query());

        $monthCompleted = Delivery::where('delivery_man_id', $user->id)
            ->whereYear('assigned_at', $year)
            ->whereMonth('assigned_at', $monthNum)
            ->whereNotNull('delivered_at')
            ->count();

        $monthFees = Delivery::where('delivery_man_id', $user->id)
            ->whereYear('assigned_at', $year)
            ->whereMonth('assigned_at', $monthNum)
            ->whereNotNull('delivered_at')
            ->join('orders', 'deliveries.order_id', '=', 'orders.id')
            ->sum('orders.delivery_fee');

        $totalCompleted = Delivery::where('delivery_man_id', $user->id)
            ->whereNotNull('delivered_at')
            ->count();

        $totalFees = Delivery::where('delivery_man_id', $user->id)
            ->whereNotNull('delivered_at')
            ->join('orders', 'deliveries.order_id', '=', 'orders.id')
            ->sum('orders.delivery_fee');

        $activeCount = Delivery::where('delivery_man_id', $user->id)
            ->whereNull('delivered_at')
            ->count();

        return [
            'deliveryMan' => $user->only(['id', 'name', 'phone', 'email', 'created_at']),
            'deliveries' => $deliveries,
            'currentMonth' => $month,
            'stats' => [
                'month_completed' => $monthCompleted,
                'month_fees' => round((float) $monthFees, 2),
                'total_completed' => $totalCompleted,
                'total_fees' => round((float) $totalFees, 2),
                'active_count' => $activeCount,
            ],
        ];
    }
}
