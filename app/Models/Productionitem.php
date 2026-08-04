<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productionitem extends Model
{
    protected $fillable = [
        'productionId',
        'jobcarditemId',
        'other',
        'productId',
        'userId',
        'qnt',
        'unitId',
        'processId',
        'machineId',
        'tms',
        'employeeId',
        'shiftId',
        'weight',
        'wpProduct',
        'weightState',
        'tempId',
        'serialNo',
        'unique_code',
        'rollId',
        'dateCreated'
    ];

 
}
