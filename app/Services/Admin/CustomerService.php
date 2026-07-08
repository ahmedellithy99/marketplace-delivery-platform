<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getCustomers(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('role', 'customer');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->withCount('orders')
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    public function getCustomerOrders(User $customer, Request $request): LengthAwarePaginator
    {
        return $customer->orders()
            ->latest()
            ->paginate(15)
            ->appends($request->query());
    }

    public function deleteCustomer(User $customer): void
    {
        $customer->delete();
    }
}
