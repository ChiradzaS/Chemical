<?php
namespace App\Barcode;

require_once 'C:\xampp\htdocs\LaravelCRUD\app\Library\Barcode\php-barcode.php';

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\JobCard;
use Illuminate\Support\Facades\View;

//Create unique prefix numbers to add to each table.
//user barcode 12
//jobCard barcode 13


class Barcode
{

 public static function uniqidReal($prefix = 11, $lenght = 13) {
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    $genStr = substr(bin2hex($bytes), 0, $lenght);
    
    return $prefix.$genStr;
}
}
?>
