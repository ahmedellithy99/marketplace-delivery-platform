<?php

namespace App\Services;

use App\DTOs\PriceResult;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;

class PricingService
{
    /**
     * Calculate the effective price for a product (or variant) with quantity.
     */
    public function calculate(Product $product, ?ProductVariant $variant = null, float $quantity = 1): PriceResult
    {
        // Step 1: Determine unit price based on product type
        $unitPrice = $this->getUnitPrice($product, $variant);

        // Step 2: Find the best applicable discount
        $discount = $this->findBestDiscount($product, $variant);

        // Step 3: Calculate discount amount
        $discountAmount = $discount ? $discount->calculateAmount($unitPrice) : 0;
        $effectivePrice = max(0, $unitPrice - $discountAmount);

        // Step 4: Calculate total (effective price × quantity)
        $total = round($effectivePrice * $quantity, 2);

        return new PriceResult(
            unitPrice: $unitPrice,
            discountAmount: $discountAmount,
            effectivePrice: $effectivePrice,
            total: $total,
            discount: $discount,
        );
    }

    /**
     * Get the unit price based on product type.
     */
    protected function getUnitPrice(Product $product, ?ProductVariant $variant): float
    {
        if ($product->isVariant() && $variant) {
            return (float) $variant->price;
        }

        // Simple or Measured — use base_price
        return (float) $product->base_price;
    }

    /**
     * Find the best (highest savings) active discount for a product/variant.
     *
     * Priority: variant → product → category → store
     */
    protected function findBestDiscount(Product $product, ?ProductVariant $variant): ?Discount
    {
        $unitPrice = $this->getUnitPrice($product, $variant);
        $bestDiscount = null;
        $bestSavings = 0;

        $now = now();

        // Helper to check if a discount is currently active
        $isActive = function (Discount $d) use ($now) {
            if (!$d->is_active) return false;
            if ($d->starts_at && $d->starts_at > $now) return false;
            if ($d->ends_at && $d->ends_at < $now) return false;
            return true;
        };

        // 1. Variant-level discounts (most specific)
        if ($variant && $variant->relationLoaded('discounts')) {
            foreach ($variant->discounts as $discount) {
                if (!$isActive($discount)) continue;
                $savings = $discount->calculateAmount($unitPrice);
                if ($savings > $bestSavings) {
                    $bestSavings = $savings;
                    $bestDiscount = $discount;
                }
            }
        }

        // 2. Product-level discounts
        if ($product->relationLoaded('discounts')) {
            foreach ($product->discounts as $discount) {
                if (!$isActive($discount)) continue;
                $savings = $discount->calculateAmount($unitPrice);
                if ($savings > $bestSavings) {
                    $bestSavings = $savings;
                    $bestDiscount = $discount;
                }
            }
        } else {
            $productDiscounts = $product->discounts()->active()->get();
            foreach ($productDiscounts as $discount) {
                $savings = $discount->calculateAmount($unitPrice);
                if ($savings > $bestSavings) {
                    $bestSavings = $savings;
                    $bestDiscount = $discount;
                }
            }
        }

        // 3. Category-level discounts (only if category AND its discounts are loaded)
        if ($product->relationLoaded('category') && $product->category) {
            if ($product->category->relationLoaded('discounts')) {
                foreach ($product->category->discounts as $discount) {
                    if (!$isActive($discount)) continue;
                    $savings = $discount->calculateAmount($unitPrice);
                    if ($savings > $bestSavings) {
                        $bestSavings = $savings;
                        $bestDiscount = $discount;
                    }
                }
            }
        }

        // 4. Store-level discounts (only if store AND its discounts are loaded)
        if ($product->relationLoaded('store') && $product->store) {
            if ($product->store->relationLoaded('discounts')) {
                foreach ($product->store->discounts as $discount) {
                    if (!$isActive($discount)) continue;
                    $savings = $discount->calculateAmount($unitPrice);
                    if ($savings > $bestSavings) {
                        $bestSavings = $savings;
                        $bestDiscount = $discount;
                    }
                }
            }
        }

        return $bestDiscount;
    }

    /**
     * Get pricing info for display (used by controllers to pass to frontend).
     */
    public function getPricingForDisplay(Product $product, ?ProductVariant $variant = null): array
    {
        $result = $this->calculate($product, $variant);
        return $result->toArray();
    }

    /**
     * Compute and set the 'pricing' attribute on a Product model.
     */
    public function loadProductPricing(Product $product): void
    {
        if ($product->isVariant()) {
            $variant = $product->relationLoaded('variants')
                ? ($product->variants->firstWhere('is_default', true) ?? $product->variants->first())
                : $product->variants()->where('is_default', true)->first() ?? $product->variants()->first();

            if (!$variant) {
                $product->setAttribute('pricing', [
                    'unit_price' => 0,
                    'discount_amount' => 0,
                    'effective_price' => 0,
                    'total' => 0,
                    'has_discount' => false,
                    'discount_label' => null,
                    'starts_from' => true,
                ]);
                return;
            }

            $result = $this->calculate($product, $variant);
            $arr = $result->toArray();
            $arr['starts_from'] = $product->relationLoaded('variants') ? $product->variants->count() > 1 : true;
            $product->setAttribute('pricing', $arr);
            return;
        }

        $result = $this->calculate($product);
        $arr = $result->toArray();
        $arr['starts_from'] = false;
        $product->setAttribute('pricing', $arr);
    }

    /**
     * Compute and set the 'pricing' attribute on a ProductVariant model.
     */
    public function loadVariantPricing(ProductVariant $variant): void
    {
        if ($variant->relationLoaded('product') && $variant->product) {
            $product = $variant->product;
        } else {
            $variant->setAttribute('pricing', [
                'unit_price' => (float) $variant->price,
                'effective_price' => (float) $variant->price,
                'discount_amount' => 0,
                'has_discount' => false,
                'discount_label' => null,
                'total' => (float) $variant->price,
            ]);
            return;
        }

        if (!$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        $result = $this->calculate($product, $variant);
        $variant->setAttribute('pricing', $result->toArray());
    }

    /**
     * Compute and set 'pricing' on a collection of products and their variants.
     */
    public function loadCollectionPricing(iterable $products): void
    {
        foreach ($products as $product) {
            if ($product instanceof Product) {
                $this->loadProductPricing($product);
                if ($product->relationLoaded('variants')) {
                    foreach ($product->variants as $variant) {
                        $this->loadVariantPricing($variant);
                    }
                }
            }
        }
    }
}
