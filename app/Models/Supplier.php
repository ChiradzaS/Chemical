<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Materials this supplier quotes, with their own price on the pivot. */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'raw_material_supplier')
            ->withPivot(['supplier_part_code', 'cost_per_kg', 'is_preferred', 'last_quoted_at'])
            ->withTimestamps();
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(StockReceipt::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}