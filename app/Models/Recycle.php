<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recycle extends Model
{
    protected $fillable = [
        'operator', 'kilos', 'machineId', 'shiftId', 'materialTypeId', 'code'
    ];
}
