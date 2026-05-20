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

        // 3. Category-level discounts (skip if not loaded to avoid N+1)
        if ($product->relationLoaded('category') && $product->category) {
            $categoryDiscounts = $product->category->discounts()->active()->get();
            foreach ($categoryDiscounts as $discount) {
                $savings = $discount->calculateAmount($unitPrice);
                if ($savings > $bestSavings) {
                    $bestSavings = $savings;
                    $bestDiscount = $discount;
                }
            }
        }

        // 4. Store-level discounts (skip if not loaded to avoid N+1)
        if ($product->relationLoaded('store') && $product->store) {
            $storeDiscounts = $product->store->discounts()->active()->get();
            foreach ($storeDiscounts as $discount) {
                $savings = $discount->calculateAmount($unitPrice);
                if ($savings > $bestSavings) {
                    $bestSavings = $savings;
                    $bestDiscount = $discount;
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
}
