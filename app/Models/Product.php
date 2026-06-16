<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasArabicSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, HasArabicSlug, SoftDeletes, InteractsWithMedia, Filterable;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'type',
        'base_price',
        'measurement_unit',
        'min_quantity',
        'max_quantity',
        'quantity_step',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'min_quantity' => 'decimal:3',
            'max_quantity' => 'decimal:3',
            'quantity_step' => 'decimal:3',
            'is_available' => 'boolean',
        ];
    }

    // ─── Type Helpers ──────────────────────────────────────────────────

    public function isSimple(): bool
    {
        return $this->type === 'simple';
    }

    public function isVariant(): bool
    {
        return $this->type === 'variant';
    }

    public function isMeasured(): bool
    {
        return $this->type === 'measured';
    }

    // ─── Spatie MediaLibrary ───────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->acceptsFile(function (\Spatie\MediaLibrary\MediaCollections\File $file) {
                return $file->size <= 5 * 1024 * 1024;
            });
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)->height(200)->format('webp')->quality(80)->sharpen(10);

        $this->addMediaConversion('optimized')
            ->width(800)->height(800)->format('webp')->quality(85)->nonQueued();
    }

    // ─── Relationships ─────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function discounts(): MorphToMany
    {
        return $this->morphToMany(Discount::class, 'discountable');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
