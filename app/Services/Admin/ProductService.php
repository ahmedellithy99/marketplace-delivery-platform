<?php

namespace App\Services\Admin;

use App\Filters\Admin\ProductFilter;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Get products with optional filtering and pagination.
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
     * Get a single product with relationships.
     */
    public function getProduct(Product $product): Product
    {
        return $product->load(['store', 'category', 'media', 'variants']);
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
     *
     * @param array $data
     * @param array<UploadedFile> $images
     * @return Product
     * @throws ValidationException
     */
    public function createProduct(array $data, array $images = []): Product
    {
        $this->validateStoreExists($data['store_id']);
        $this->validateCategoryExists($data['category_id']);

        if (isset($data['discounted_price']) && $data['discounted_price'] !== null) {
            $this->validateDiscountedPrice($data['discounted_price'], $data['price']);
        }

        $product = DB::transaction(function () use ($data) {
            $productData = collect($data)->except(['images'])->toArray();
            return Product::create($productData);
        });

        foreach ($images as $image) {
            $product->addMedia($image)->toMediaCollection('images');
        }

        return $product->load(['store', 'category', 'media', 'variants']);
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @param array<UploadedFile> $images
     * @return Product
     * @throws ValidationException
     */
    public function updateProduct(Product $product, array $data, array $images = []): Product
    {
        $price = $data['price'] ?? $product->price;
        $discountedPrice = array_key_exists('discounted_price', $data)
            ? $data['discounted_price']
            : $product->discounted_price;

        if ($discountedPrice !== null) {
            $this->validateDiscountedPrice($discountedPrice, $price);
        }

        if (isset($data['store_id'])) {
            $this->validateStoreExists($data['store_id']);
        }

        if (isset($data['category_id'])) {
            $this->validateCategoryExists($data['category_id']);
        }

        DB::transaction(function () use ($product, $data) {
            $productData = collect($data)->except(['images'])->toArray();
            $product->update($productData);
        });

        foreach ($images as $image) {
            $product->addMedia($image)->toMediaCollection('images');
        }

        $product->refresh();
        $product->load(['store', 'category', 'media', 'variants']);

        return $product;
    }

    /**
     * Delete a product (soft delete).
     */
    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    /**
     * Toggle product availability (is_available).
     */
    public function toggleAvailability(Product $product): Product
    {
        $product->update([
            'is_available' => !$product->is_available,
        ]);

        $product->refresh();

        return $product;
    }

    /**
     * Add a variant to a product.
     */
    public function addVariant(Product $product, array $data): ProductVariant
    {
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
     * Validate that the store exists and is not soft-deleted.
     */
    protected function validateStoreExists(int $storeId): void
    {
        if (!Store::where('id', $storeId)->exists()) {
            throw ValidationException::withMessages([
                'store_id' => ['The selected store does not exist.'],
            ]);
        }
    }

    /**
     * Validate that the category exists and is not soft-deleted.
     */
    protected function validateCategoryExists(int $categoryId): void
    {
        if (!Category::where('id', $categoryId)->exists()) {
            throw ValidationException::withMessages([
                'category_id' => ['The selected category does not exist.'],
            ]);
        }
    }

    /**
     * Validate that discounted_price is strictly less than price.
     */
    protected function validateDiscountedPrice(float $discountedPrice, float $price): void
    {
        if ($discountedPrice >= $price) {
            throw ValidationException::withMessages([
                'discounted_price' => ['The discounted price must be less than the original price.'],
            ]);
        }
    }
}
