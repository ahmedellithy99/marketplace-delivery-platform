<?php

namespace App\Services\Admin;

use App\Filters\Admin\ProductFilter;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        protected PricingService $pricingService
    ) {}
    /**
     * Get products with optional filtering and pagination.
     */
    public function getProducts(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $products = Product::with(['store', 'category', 'media', 'variants', 'discounts'])
            ->filter(new ProductFilter($request))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $this->pricingService->loadCollectionPricing($products);

        return $products;
    }

    /**
     * Get a single product with relationships.
     */
    public function getProduct(Product $product): Product
    {
        $product->load(['store', 'category', 'media', 'variants', 'discounts']);

        $this->pricingService->loadProductPricing($product);
        if ($product->relationLoaded('variants')) {
            foreach ($product->variants as $variant) {
                $this->pricingService->loadVariantPricing($variant);
            }
        }

        return $product;
    }

    /**
     * Get all stores for dropdown selection.
     */
    public function getStoreOptions()
    {
        return Store::all(['id', 'name']);
    }

    /**
     * Get all categories for dropdown selection.
     */
    public function getCategoryOptions()
    {
        return Category::all(['id', 'name']);
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product
    {
        $this->validateStoreExists($data['store_id']);
        $this->validateCategoryExists($data['category_id']);
        $this->validateProductData($data);

        return DB::transaction(function () use ($data) {
            $productData = collect($data)->except(['images', 'variants'])->toArray();
            $product = Product::create($productData);

            // Create variants if type is 'variant'
            if ($product->isVariant() && !empty($data['variants'])) {
                foreach ($data['variants'] as $index => $variantData) {
                    $product->variants()->create([
                        'name' => $variantData['name'],
                        'price' => $variantData['price'],
                        'is_default' => $index === 0, // First variant is default
                        'sort_order' => $index,
                    ]);
                }
            }

            return $product->load(['store', 'category', 'media', 'variants']);
        });
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        if (isset($data['store_id'])) {
            $this->validateStoreExists($data['store_id']);
        }
        if (isset($data['category_id'])) {
            $this->validateCategoryExists($data['category_id']);
        }
        $this->validateProductData($data, $product);

        DB::transaction(function () use ($product, $data) {
            $productData = collect($data)->except(['images', 'variants'])->toArray();
            $product->update($productData);
        });

        $product->refresh();
        return $product->load(['store', 'category', 'media', 'variants']);
    }

    /**
     * Delete a product (soft delete).
     */
    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    /**
     * Toggle product availability.
     */
    public function toggleAvailability(Product $product): Product
    {
        $product->update(['is_available' => !$product->is_available]);
        $product->refresh();
        return $product;
    }

    /**
     * Add a variant to a product.
     */
    public function addVariant(Product $product, array $data): ProductVariant
    {
        $maxSort = $product->variants()->max('sort_order') ?? -1;
        $data['sort_order'] = $maxSort + 1;

        if ($product->variants()->count() === 0) {
            $data['is_default'] = true;
        }

        return $product->variants()->create($data);
    }

    /**
     * Update an existing product variant.
     */
    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);
        $variant->refresh();
        return $variant;
    }

    /**
     * Remove a product variant.
     */
    public function removeVariant(ProductVariant $variant): void
    {
        $variant->delete();
    }

    /**
     * Validate product data based on type.
     */
    protected function validateProductData(array $data, ?Product $product = null): void
    {
        $type = $data['type'] ?? $product?->type ?? 'simple';

        if (in_array($type, ['simple', 'measured'])) {
            $basePrice = $data['base_price'] ?? $product?->base_price;
            if (empty($basePrice) || $basePrice <= 0) {
                throw ValidationException::withMessages([
                    'base_price' => ['Base price is required for simple and measured products.'],
                ]);
            }
        }

        if ($type === 'measured') {
            if (empty($data['measurement_unit']) && !$product?->measurement_unit) {
                throw ValidationException::withMessages([
                    'measurement_unit' => ['Measurement unit is required for measured products.'],
                ]);
            }
        }
    }

    protected function validateStoreExists(int $storeId): void
    {
        if (!Store::where('id', $storeId)->exists()) {
            throw ValidationException::withMessages([
                'store_id' => ['The selected store does not exist.'],
            ]);
        }
    }

    protected function validateCategoryExists(int $categoryId): void
    {
        if (!Category::where('id', $categoryId)->exists()) {
            throw ValidationException::withMessages([
                'category_id' => ['The selected category does not exist.'],
            ]);
        }
    }

    /**
     * Set a variant as the default for its product.
     */
    public function setDefaultVariant(Product $product, ProductVariant $variant): void
    {
        $product->variants()->update(['is_default' => false]);
        $variant->update(['is_default' => true]);
    }

    /**
     * Add a discount to a product.
     */
    public function addProductDiscount(Product $product, array $data): Discount
    {
        $discount = Discount::create([
            ...$data,
            'scope' => 'product',
            'is_active' => true,
        ]);

        $product->discounts()->attach($discount->id);

        return $discount;
    }

    /**
     * Remove a discount from a product.
     */
    public function removeProductDiscount(Product $product, Discount $discount): void
    {
        $product->discounts()->detach($discount->id);
        $discount->delete();
    }
}
