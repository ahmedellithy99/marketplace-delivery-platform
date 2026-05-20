<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasArabicSlug;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * The accessors to append to the model's array form.
     */
    protected $appends = ['is_open'];

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
                return $file->size <= 5 * 1024 * 1024; // 5MB max
            });

        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->acceptsFile(function (\Spatie\MediaLibrary\MediaCollections\File $file) {
                return $file->size <= 10 * 1024 * 1024; // 10MB max
            });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(80)
            ->sharpen(10);

        $this->addMediaConversion('optimized')
            ->width(800)
            ->height(600)
            ->format('webp')
            ->quality(85)
            ->nonQueued();
    }

    // ─── Accessors ───────────────────────────────────────────────────────

    /**
     * Determine if the store is currently open based on operating hours.
     */
    protected function isOpen(): Attribute
    {
        return Attribute::get(function (): bool {
            if (!$this->opening_time || !$this->closing_time) {
                return false;
            }

            $now = Carbon::now()->format('H:i');

            $opening = $this->opening_time instanceof \DateTimeInterface
                ? $this->opening_time->format('H:i')
                : (string) $this->opening_time;

            $closing = $this->closing_time instanceof \DateTimeInterface
                ? $this->closing_time->format('H:i')
                : (string) $this->closing_time;

            return $now >= $opening && $now <= $closing;
        });
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

    public function discounts(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Discount::class, 'discountable');
    }
}
