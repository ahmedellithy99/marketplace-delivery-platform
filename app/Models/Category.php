<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasArabicSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory, HasArabicSlug, SoftDeletes, InteractsWithMedia, Filterable;

    protected $guarded = [];

    // ─── Spatie MediaLibrary ───────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->acceptsFile(function (\Spatie\MediaLibrary\MediaCollections\File $file) {
                return $file->size <= 5 * 1024 * 1024; // 5MB max
            });
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(80)
            ->sharpen(10);

        $this->addMediaConversion('optimized')
            ->width(600)
            ->height(600)
            ->format('webp')
            ->quality(85)
            ->nonQueued();
    }

    // ─── Relationships ─────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function discounts(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Discount::class, 'discountable');
    }
}
