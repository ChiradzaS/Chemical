<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllocationProduction extends Model
{
    protected $table = 'allocation_productions';

    protected $fillable = [
        'userId',
        'machineId',
        'shiftId',
    ];
}