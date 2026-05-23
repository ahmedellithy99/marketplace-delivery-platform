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
     * Compute pricing with discount for this variant.
     * Uses the parent product from the relation if loaded to avoid N+1.
     */
    public function getPricingAttribute(): array
    {
        // Use already-loaded parent product to avoid N+1
        if ($this->relationLoaded('product') && $this->product) {
            $product = $this->product;
        } else {
            // Fallback: load product with discounts (single query)
            $product = Product::with('discounts')->find($this->product_id);
        }

        if (!$product) {
            return ['unit_price' => (float) $this->price, 'effective_price' => (float) $this->price, 'discount_amount' => 0, 'has_discount' => false, 'discount_label' => null, 'total' => (float) $this->price];
        }

        // Ensure discounts are loaded on product
        if (!$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        // Load own discounts if not loaded
        if (!$this->relationLoaded('discounts')) {
            $this->load('discounts');
        }

        $pricingService = app(\App\Services\PricingService::class);
        $result = $pricingService->calculate($product, $this);
        return $result->toArray();
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
