<?php

namespace App\Services\Public;

use App\Filters\Public\ProductFilter;
use App\Filters\Public\StoreFilter;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
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
            ->get()
            ->map(fn (Store $store) => $this->appendStoreStatus($store));
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
        $paginator = Store::with(['storeType', 'media'])
            ->filter(new StoreFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->getCollection()->transform(
            fn (Store $store) => $this->appendStoreStatus($store)
        );

        return $paginator;
    }

    /**
     * Get a single store with its available products grouped by category.
     */
    public function getStoreDetails(Store $store): Store
    {
        $store->load(['storeType', 'media']);

        $store->setAttribute('is_open', $this->isStoreOpen($store));

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

    /**
     * Determine if a store is currently open based on operating hours.
     */
    public function isStoreOpen(Store $store): bool
    {
        $now = Carbon::now()->format('H:i');

        $openingTime = $store->opening_time instanceof \DateTimeInterface
            ? $store->opening_time->format('H:i')
            : (string) $store->opening_time;

        $closingTime = $store->closing_time instanceof \DateTimeInterface
            ? $store->closing_time->format('H:i')
            : (string) $store->closing_time;

        return $now >= $openingTime && $now <= $closingTime;
    }

    /**
     * Append the is_open status to a store instance.
     */
    protected function appendStoreStatus(Store $store): Store
    {
        $store->setAttribute('is_open', $this->isStoreOpen($store));

        return $store;
    }
}
