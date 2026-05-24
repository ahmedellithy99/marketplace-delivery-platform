<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class DeliveryManService
{
    /**
     * Get delivery men with optional search filtering and pagination.
     */
    public function getDeliveryMen(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('role', 'delivery');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Create a new delivery man.
     */
    public function createDeliveryMan(array $data): User
    {
        $data['role'] = 'delivery';
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    /**
     * Update an existing delivery man.
     */
    public function updateDeliveryMan(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->refresh();

        return $user;
    }

    /**
     * Soft delete a delivery man.
     */
    public function deleteDeliveryMan(User $user): void
    {
        $user->delete();
    }
}
