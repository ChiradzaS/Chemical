<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    protected $table = 'company_info';

    protected $fillable = [
        'name',
        'trading_name',
        'vat_number',
        'reg_number',
        'tel_number',
        'email',
        'web_address',
        'suburb',
        'shop_no',
        'physical_address',
        'city',
        'country',
        'receipt_comment',
    ];
}