<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceiptItem extends Model
{
    protected $table = 'stock_receipt_items';

    public $timestamps = false;

    protected $fillable = [
        'stock_receipt_id',
        'raw_material_id',
        'qty',
        'unit_cost',
    ];

    protected $casts = [
        'stock_receipt_id' => 'integer',
        'raw_material_id'  => 'integer',
        'qty'              => 'integer',
        'unit_cost'        => 'decimal:4',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class, 'stock_receipt_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->qty * (float) $this->unit_cost;
    }
}