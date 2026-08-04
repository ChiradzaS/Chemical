<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChemicalJobCardItem extends Model
{
    protected $table = 'chemical_job_card_items';

    protected $fillable = [
        'jobCardId',
        'processId',
        'processName',
        'productId',
        'quantity',
        'outstanding',
        'unitId',
        'barcode',
        'stateId',
    ];

    public function jobCard()
    {
        return $this->belongsTo(ChemicalJobCard::class, 'jobCardId');
    }
}