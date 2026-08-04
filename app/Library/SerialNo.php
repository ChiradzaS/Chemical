<?php

namespace App\Library;
use DB;

class SerialNo 
{

    static function generateSerialNumber() {


        $timestamp = time();
        $serialNumber = 'SN' . date('mdHs', $timestamp);

        $existing = DB::table('productionitems')
        ->where('serialNo', $serialNumber)
        ->exists();

        if ($existing) {
            
            return self::generateSerialNumber();
          }




        return $serialNumber;
    }
    
}