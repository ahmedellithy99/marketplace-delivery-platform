<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class StaffService
{
    /**
     * Get staff members (admins + customer_service) with optional search.
     */
    public function getStaff(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::whereIn('role', ['admin', 'customer_service']);

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
     * Create a new staff member (admin or customer_service).
     */
    public function createStaff(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    /**
     * Update a staff member.
     */
    public function updateStaff(User $user, array $data): User
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
     * Soft delete a staff member.
     */
    public function deleteStaff(User $user): void
    {
        $user->delete();
    }

    /**
     * Update the user's password after verifying the current one.
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
