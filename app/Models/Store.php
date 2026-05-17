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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Store extends Model implements HasMedia
{
    use HasFactory, HasArabicSlug, SoftDeletes, InteractsWithMedia, Filterable;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
        ];
    }

    // ─── Spatie MediaLibrary ───────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->acceptsFile(function (\Spatie\MediaLibrary\MediaCollections\File $file) {
                return $file->size <= 2 * 1024 * 1024; // 2MB max
            });

        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->acceptsFile(function (\Spatie\MediaLibrary\MediaCollections\File $file) {
                return $file->size <= 5 * 1024 * 1024; // 5MB max
            });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);
    }

    // ─── Relationships ─────────────────────────────────────────────────

    public function storeType(): BelongsTo
    {
        return $this->belongsTo(StoreType::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
