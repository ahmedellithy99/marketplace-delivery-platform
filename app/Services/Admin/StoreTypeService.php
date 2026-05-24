<?php

namespace App\Services\Admin;

use App\Models\StoreType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreTypeService
{
    /**
     * Get store types with optional search, paginated.
     */
    public function getStoreTypes(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = StoreType::withCount('stores');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Create a new store type.
     */
    public function createStoreType(array $data): StoreType
    {
        return StoreType::create($data);
    }

    /**
     * Update an existing store type.
     */
    public function updateStoreType(StoreType $storeType, array $data): StoreType
    {
        $storeType->update($data);
        $storeType->refresh();

        return $storeType;
    }

    /**
     * Delete a store type (only if no stores use it).
     */
    public function deleteStoreType(StoreType $storeType): void
    {
        if ($storeType->stores()->count() > 0) {
            abort(403, 'لا يمكن حذف هذا النوع لأنه مرتبط بمتاجر.');
        }

        $storeType->delete();
    }

    /**
     * Toggle the is_active status of a store type.
     */
    public function toggleActive(StoreType $storeType): StoreType
    {
        $storeType->update([
            'is_active' => !$storeType->is_active,
        ]);

        $storeType->refresh();

        return $storeType;
    }
}
