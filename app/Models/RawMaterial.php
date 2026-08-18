<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    protected $table = 'raw_materials';

    protected $fillable = [
        'code',
        'name',
        'material_type',
        'uom',
        'cost_per_kg',
        'reorder_level',
        'allow_negative',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'cost_per_kg'    => 'decimal:4',
        'stock_on_hand'  => 'decimal:2',
        'reorder_level'  => 'decimal:2',
        'allow_negative' => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'raw_material_supplier')
            ->withPivot(['supplier_part_code', 'cost_per_kg', 'is_preferred', 'last_quoted_at'])
            ->withTimestamps();
    }

    public function receiptItems()
    {
        return $this->hasMany(StockReceiptItem::class);
    }
}