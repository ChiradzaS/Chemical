<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChemicalJobCard extends Model
{
    protected $table = 'chemical_job_cards';

    protected $fillable = [
        'customerId',
        'productId',
        'quantity',
        'batchCount',
        'unitId',
        'containerSizeId',
        'colourId',
        'viscosityId',
        'activeIngredientId',
        'fragranceId',
        'bottleTypeId',
        'weightPerUnit',
        'totalUnits',
        'barcode',
        'notes',
        'startDate',
        'stateId',
    ];

    public function items()
    {
        return $this->hasMany(ChemicalJobCardItem::class, 'jobCardId');
    }
}