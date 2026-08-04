<?php

namespace App\Http\Controllers;

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Auth;

class PdfController extends Controller
{
	protected $fpdf;
 
    public function __construct()
    {
        $this->fpdf = new Fpdf;
    }

    public function index(Request $request) 
    {
        $jobCardId = $request->get('jobCardId');
    	$this->fpdf->SetFont('Helvetica', 'B', 8);
        $this->fpdf->AddPage("L",'A4','0');
        $this->fpdf->Text(10, 10, "Companies : ".$jobCardId);      
        $this->fpdf->Text(15, 15, "Products");
    
         
        $this->fpdf->Output();

        exit;
    }
}