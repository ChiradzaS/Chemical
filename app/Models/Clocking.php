<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clocking extends Model
{

    

    protected $fillable = [
        'name',
        'date',
        'day',
        'clockInTime',
        'clockOutTime',
        'shift',
        
    ];
}
