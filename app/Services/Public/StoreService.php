<?php

namespace App\Services\Public;

use App\Filters\Public\ProductFilter;
use App\Filters\Public\StoreFilter;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StoreService
{
    /**
     * Get featured stores for the home page.
     */
    public function getFeaturedStores(int $limit = 8): Collection
    {
        return Store::with(['storeType', 'media'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get featured products for the home page.
     */
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::with(['store', 'category', 'media'])
            ->where('is_available', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get paginated stores with filtering.
     */
    public function getStores(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Store::with(['storeType', 'media'])
            ->filter(new StoreFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single store with its available products grouped by category.
     */
    public function getStoreDetails(Store $store): Store
    {
        $store->load(['storeType', 'media']);

        // Load available products grouped by category
        $products = $store->products()
            ->where('is_available', true)
            ->with(['category', 'media', 'variants'])
            ->get();

        $groupedProducts = $products->groupBy(fn (Product $product) => $product->category?->name ?? 'Uncategorized')
            ->map(fn (Collection $items, string $categoryName) => [
                'category' => $categoryName,
                'products' => $items->values(),
            ])
            ->values();

        $store->setAttribute('grouped_products', $groupedProducts);

        return $store;
    }

    /**
     * Get paginated products with filtering (public-facing, only available).
     */
    public function getProducts(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['store', 'category', 'media', 'variants'])
            ->filter(new ProductFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }
}
