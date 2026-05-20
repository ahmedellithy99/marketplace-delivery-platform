<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Discount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // ─── Scopes ────────────────────────────────────────────────────────

    /**
     * Only active discounts within their valid date range.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    // ─── Polymorphic Relationships ─────────────────────────────────────

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'discountable');
    }

    public function variants(): MorphToMany
    {
        return $this->morphedByMany(ProductVariant::class, 'discountable');
    }

    public function stores(): MorphToMany
    {
        return $this->morphedByMany(Store::class, 'discountable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'discountable');
    }

    // ─── Helpers ───────────────────────────────────────────────────────

    /**
     * Calculate the discount amount for a given price.
     */
    public function calculateAmount(float $price): float
    {
        return match ($this->type) {
            'percentage' => round($price * ($this->value / 100), 2),
            'fixed' => min($this->value, $price), // Can't discount more than the price
            default => 0,
        };
    }
}
