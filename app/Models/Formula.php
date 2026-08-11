<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formula extends Model
{
    protected $table = 'formulas';

    protected $fillable = [
        'code',
        'name',
        'chemical_type',
        'base_batch_qty',
        'status',
        'notes',
        'created_by',
    ];

    // float, not 'decimal:n' — the decimal cast returns a string and
    // breaks every percentage/quantity calculation downstream
    protected $casts = [
        'base_batch_qty' => 'float',
    ];

    protected $attributes = [
        'base_batch_qty' => 1000,
        'status'         => 'draft',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FormulaItem::class, 'formula_id')->orderBy('sequence');
    }

    /** Many products can share one formula. */
    public function products(): HasMany
    {
        return $this->hasMany(ChemicalProduct::class, 'formula_id');
    }
}