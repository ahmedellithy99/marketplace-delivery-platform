<?php

namespace App\Services\Admin;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;

class DashboardService
{
    /**
     * Get all dashboard statistics for the admin overview.
     *
     * @return array{
     *     pending_orders_count: int,
     *     active_deliveries_count: int,
     *     stores_count: int,
     *     customers_count: int,
     *     delivery_personnel_count: int,
     *     recent_orders: \Illuminate\Database\Eloquent\Collection
     * }
     */
    public function getDashboardStats(): array
    {
        return [
            'pending_orders_count' => Order::where('status', 'pending')->count(),
            'active_deliveries_count' => Delivery::whereNull('delivered_at')->count(),
            'stores_count' => Store::count(),
            'customers_count' => User::where('role', 'customer')->count(),
            'delivery_personnel_count' => User::where('role', 'delivery')->count(),
            'recent_orders' => Order::with('user')->latest()->take(10)->get(),
        ];
    }
}
