<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    /**
     * Get discounts with pagination.
     */
    public function getDiscounts(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Discount::latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single discount with its targets.
     */
    public function getDiscount(Discount $discount): Discount
    {
        return $discount->load(['products', 'variants.product', 'stores', 'categories']);
    }

    /**
     * Create a new discount and attach targets.
     */
    public function createDiscount(array $data): Discount
    {
        return DB::transaction(function () use ($data) {
            $discount = Discount::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'scope' => $data['scope'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'usage_limit' => $data['usage_limit'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->attachTargets($discount, $data);

            return $discount;
        });
    }

    /**
     * Update an existing discount.
     */
    public function updateDiscount(Discount $discount, array $data): Discount
    {
        return DB::transaction(function () use ($discount, $data) {
            $discount->update([
                'name' => $data['name'] ?? $discount->name,
                'type' => $data['type'] ?? $discount->type,
                'value' => $data['value'] ?? $discount->value,
                'scope' => $data['scope'] ?? $discount->scope,
                'starts_at' => $data['starts_at'] ?? $discount->starts_at,
                'ends_at' => $data['ends_at'] ?? $discount->ends_at,
                'usage_limit' => $data['usage_limit'] ?? $discount->usage_limit,
                'is_active' => $data['is_active'] ?? $discount->is_active,
            ]);

            // Re-attach targets if provided
            if (isset($data['target_ids'])) {
                $this->detachAllTargets($discount);
                $this->attachTargets($discount, $data);
            }

            return $discount->refresh();
        });
    }

    /**
     * Delete a discount.
     */
    public function deleteDiscount(Discount $discount): void
    {
        $discount->delete();
    }

    /**
     * Toggle discount active status.
     */
    public function toggleActive(Discount $discount): Discount
    {
        $discount->update(['is_active' => !$discount->is_active]);
        return $discount->refresh();
    }

    /**
     * Get options for target selection based on scope.
     */
    public function getTargetOptions(string $scope): array
    {
        return match ($scope) {
            'product' => Product::select('id', 'name')->orderBy('name')->get()->toArray(),
            'variant' => ProductVariant::with('product:id,name')->select('id', 'product_id', 'name', 'price')->get()->toArray(),
            'store' => Store::select('id', 'name')->orderBy('name')->get()->toArray(),
            'category' => Category::select('id', 'name')->orderBy('name')->get()->toArray(),
            default => [],
        };
    }

    /**
     * Attach targets to a discount based on scope.
     */
    protected function attachTargets(Discount $discount, array $data): void
    {
        $targetIds = $data['target_ids'] ?? [];
        if (empty($targetIds)) return;

        match ($discount->scope) {
            'product' => $discount->products()->attach($targetIds),
            'variant' => $discount->variants()->attach($targetIds),
            'store' => $discount->stores()->attach($targetIds),
            'category' => $discount->categories()->attach($targetIds),
        };
    }

    /**
     * Detach all targets from a discount.
     */
    protected function detachAllTargets(Discount $discount): void
    {
        match ($discount->scope) {
            'product' => $discount->products()->detach(),
            'variant' => $discount->variants()->detach(),
            'store' => $discount->stores()->detach(),
            'category' => $discount->categories()->detach(),
        };
    }
}
