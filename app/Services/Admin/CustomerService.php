<?php

namespace App\Services\Admin;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function getTrashedCustomers(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('role', 'customer')->onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->withCount('orders')
            ->orderByDesc('deleted_at')
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

    public function restoreCustomer(int $customerId): void
    {
        $customer = User::where('role', 'customer')->onlyTrashed()->findOrFail($customerId);
        $customer->restore();
    }

    public function forceDeleteCustomer(int $customerId): void
    {
        $customer = User::where('role', 'customer')->onlyTrashed()->findOrFail($customerId);

        DB::transaction(function () use ($customer) {
            Cart::where('user_id', $customer->id)->forceDelete();
            Order::where('user_id', $customer->id)->forceDelete();
            $customer->forceDelete();
        });
    }
}
