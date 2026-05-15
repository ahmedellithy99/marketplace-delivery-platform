<?php

namespace App\Models;

use App\Traits\HasArabicSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreType extends Model
{
    use HasFactory, HasArabicSlug;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}
