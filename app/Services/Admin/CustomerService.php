<?php

namespace App\Services\Admin;

use App\Filters\Admin\CustomerFilter;
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
        return User::where('role', 'customer')
            ->filter(new CustomerFilter($request))
            ->withCount('orders')
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    public function getTrashedCustomers(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return User::where('role', 'customer')
            ->onlyTrashed()
            ->filter(new CustomerFilter($request))
            ->withCount('orders')
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
