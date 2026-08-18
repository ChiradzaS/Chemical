<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialTrans extends Model
{
    use HasFactory;

    protected $table = 'raw_material_trans';

    /* Why the stock moved. These are the SAME numbers stocks_trans uses for the
       same document, so one production run shows as 105 on both ledgers — the
       finished goods going in and the raw materials coming out. */
    public const RECEIPT     = 103;  // delivery in from a supplier
    public const RETURN_OUT  = 104;  // sent back to the supplier
    public const ISSUE       = 105;  // consumed by a production item
    public const ADJUSTMENT  = 111;  // stock count correction
    public const WRITE_OFF   = 112;  // spillage, expiry, damage

    protected $fillable = [
        'raw_material_id',
        'supplier_id',
        'doc_type',
        'doc_no',
        'qty_in',
        'qty_out',
        'balance_after',
        'unit_cost',
        'trans_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'raw_material_id' => 'integer',
        'supplier_id'     => 'integer',
        'doc_type'        => 'integer',
        'qty_in'          => 'decimal:2',
        'qty_out'         => 'decimal:2',
        'balance_after'   => 'decimal:2',
        'unit_cost'       => 'decimal:4',
        'trans_date'      => 'date:Y-m-d',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}