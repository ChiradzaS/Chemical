<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaItem extends Model
{
    protected $table = 'formula_items';

    protected $fillable = [
        'formula_id',
        'raw_material_id',
        'material_type',
        'percentage',
        'quantity',
        'uom',
        'entry_mode',
        'is_balance',
        'sequence',
        'instruction',
    ];

    protected $casts = [
        'percentage' => 'float',
        'quantity'   => 'float',
        'is_balance' => 'boolean',
        'sequence'   => 'integer',
    ];

    protected $attributes = [
        'percentage' => 0,
        'quantity'   => 0,
        'uom'        => 'kg',
        'entry_mode' => 'percent',
        'is_balance' => 0,
        'sequence'   => 1,
    ];

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'formula_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}