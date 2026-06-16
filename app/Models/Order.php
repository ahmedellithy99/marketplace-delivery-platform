<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Order extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'delivery_address',
        'latitude',
        'longitude',
        'notes',
        'subtotal',
        'delivery_fee_min',
        'delivery_fee_max',
        'delivery_fee',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'subtotal' => 'decimal:2',
            'delivery_fee_min' => 'decimal:2',
            'delivery_fee_max' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    // ─── Multi-Store Grouping ──────────────────────────────────────────

    /**
     * Get order items grouped by store with store details.
     *
     * Returns a collection keyed by store_id, each containing:
     * - store_id, store_name, store_address: store info for pickup
     * - items: the order items belonging to that store
     *
     * Requires items.store relationship to be loaded.
     */
    public function getItemsGroupedByStore(): Collection
    {
        $this->loadMissing('items.store');

        return $this->items->groupBy('store_id')->map(function (Collection $items, int $storeId) {
            $store = $items->first()->store;

            return [
                'store_id' => $storeId,
                'store_name' => $store?->name,
                'store_address' => $store?->address,
                'store_phone' => $store?->phone,
                'items' => $items,
            ];
        })->values();
    }
}
