<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [


        'docId'         ,
        'customerId'    ,
        'unitId'        ,
        'qnt'           ,
        'addressId'     ,
        'vehicleReg'    ,
        'driver'        ,
        'invoiceNo'     , 
        'stateId'       ,
        'productId'     ,
        'uniqu'         

    ];
}
