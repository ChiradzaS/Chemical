<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceipt extends Model
{
    protected $table = 'stock_receipts';

    protected $fillable = [
        'supplier_id',
        'reference',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'supplier_id'   => 'integer',
        'received_date' => 'date:Y-m-d',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }

    /** What this whole delivery cost. */
    public function getTotalValueAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->qty * $i->unit_cost);
    }
}