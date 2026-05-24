<?php

namespace App\Services\Public;

use App\Filters\Public\ProductFilter;
use App\Filters\Public\StoreFilter;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreType;
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
    /**
     * Get featured products for the home page (highest discount value first).
     */
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        $productIds = \DB::table('products')
            ->join('discountables', function ($join) {
                $join->on('products.id', '=', 'discountables.discountable_id')
                    ->where('discountables.discountable_type', '=', 'App\\Models\\Product');
            })
            ->join('discounts', function ($join) {
                $join->on('discountables.discount_id', '=', 'discounts.id')
                    ->where('discounts.is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('discounts.starts_at')->orWhere('discounts.starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('discounts.ends_at')->orWhere('discounts.ends_at', '>=', now());
                    });
            })
            ->where('products.is_available', true)
            ->whereNull('products.deleted_at')
            ->orderByDesc('discounts.value')
            ->limit($limit)
            ->pluck('products.id');

        return Product::with(['store.discounts', 'category.discounts', 'media', 'variants.discounts', 'discounts'])
            ->whereIn('id', $productIds)
            ->get()
            ->sortByDesc(fn ($p) => $p->discounts->max('value'))
            ->values();
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
            ->with(['category', 'media', 'variants', 'discounts'])
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
        return Product::with(['store.discounts', 'category.discounts', 'media', 'variants.discounts', 'variants.product', 'discounts'])
            ->filter(new ProductFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get active store types for filter dropdowns.
     */
    public function getStoreTypeOptions(): Collection
    {
        return StoreType::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get category options for filter dropdowns.
     */
    public function getCategoryOptions(): Collection
    {
        return Category::orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get store show page data: store details, filtered/sorted products, and categories.
     *
     * @return array{store: Store, products: LengthAwarePaginator, categories: Collection}
     */
    public function getStoreShowData(Store $store, Request $request): array
    {
        $store->load(['storeType', 'media']);

        // Get categories for this store's products
        $categories = Category::whereHas('products', function ($q) use ($store) {
            $q->where('store_id', $store->id)->where('is_available', true);
        })->orderBy('name')->get(['id', 'name']);

        // Get filtered products
        $query = $store->products()
            ->where('is_available', true)
            ->with(['category', 'media', 'variants.discounts', 'discounts', 'store.discounts', 'category.discounts'])
            ->when($request->get('category'), fn ($q, $cat) => $q->where('category_id', $cat))
            ->when($request->get('search'), fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->when($request->get('on_discount'), function ($q) {
                $q->whereHas('discounts', function ($dq) {
                    $dq->where('is_active', true)
                        ->where(function ($q2) {
                            $q2->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q2) {
                            $q2->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                        });
                });
            });

        // Sort
        $sort = $request->get('sort', '');
        if ($sort) {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $column = ltrim($sort, '-');
            if ($column === 'base_price') {
                $query->orderByRaw("COALESCE(products.base_price, (SELECT MIN(pv.price) FROM product_variants pv WHERE pv.product_id = products.id)) {$direction}");
            } elseif (in_array($column, ['name', 'created_at'])) {
                $query->orderBy($column, $direction);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(20)->appends($request->query());

        return [
            'store' => $store,
            'products' => $products,
            'categories' => $categories,
        ];
    }
}
