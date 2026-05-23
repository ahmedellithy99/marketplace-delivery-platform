<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['pricing'];

    /**
     * Cached pricing result.
     */
    protected ?array $cachedPricing = null;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Pricing Accessor ──────────────────────────────────────────────

    /**
     * Compute pricing with discount for this variant (cached per request).
     */
    public function getPricingAttribute(): array
    {
        if ($this->cachedPricing !== null) {
            return $this->cachedPricing;
        }

        // Use already-loaded parent product to avoid N+1
        if ($this->relationLoaded('product') && $this->product) {
            $product = $this->product;
        } else {
            $product = Product::with('discounts')->find($this->product_id);
        }

        if (!$product) {
            $this->cachedPricing = ['unit_price' => (float) $this->price, 'effective_price' => (float) $this->price, 'discount_amount' => 0, 'has_discount' => false, 'discount_label' => null, 'total' => (float) $this->price];
            return $this->cachedPricing;
        }

        if (!$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        if (!$this->relationLoaded('discounts')) {
            $this->load('discounts');
        }

        $pricingService = app(\App\Services\PricingService::class);
        $result = $pricingService->calculate($product, $this);
        $this->cachedPricing = $result->toArray();
        return $this->cachedPricing;
    }

    // ─── Relationships ─────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discounts(): MorphToMany
    {
        return $this->morphToMany(Discount::class, 'discountable');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }
}
