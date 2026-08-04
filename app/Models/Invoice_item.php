<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice_item extends Model
{
    protected $fillable = [
        'productId'  ,
        'unitId'     ,
        'quantity'   ,
        'vatAmnt'    ,
        'Discount'   ,
        'price'      ,
        'totalPrice' ,
        'invoicesId' ,
        'stateId'  
    ];
}
