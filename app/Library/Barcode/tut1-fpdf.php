<?php
require_once 'php-barcode.php';

require_once 'sample-fpdf.php';

require_once 'fpdf181.php';

// -------------------------------------------------- //
//                  PROPERTIES
// -------------------------------------------------- //

$fontSize = 10;

$marge = 10; // between barcode and hri in pixel

$x = 150; // barcode center

$y = 50; // barcode center

$height = 35; // barcode height in 1D ; module size in 2D

$width = 1.5; // barcode height in 1D ; not use in 2D

$angle = 0; // rotation in degrees

$code = '2234567890121'; // barcode, of course ; )

//$type = 'ean13';
$type = 'code128';
$black = '000000'; // color in hexa

// -------------------------------------------------- //
//            ALLOCATE FPDF RESSOURCE
// -------------------------------------------------- //

$pdf = new eFPDF( 'P', 'pt' );

$pdf->AddPage();

$data = $pdf->digit_to_fpdf_renderer( $pdf, $black, $x, $y, $angle, $type, array( 'code' => $code ), $width, $height );

// -------------------------------------------------- //
//                      HRI
// -------------------------------------------------- //

$pdf->SetFont( 'Arial', '', $fontSize );

$pdf->SetTextColor( 0, 0, 0 );

$len = $pdf->GetStringWidth( $data[ 'hri' ] );

Barcode::rotate( - $len / 2, ( $data[ 'height' ] / 2 ) + $fontSize + $marge, $angle, $xt, $yt );

$pdf->TextWithRotation( $x + $xt, $y + $yt, $data[ 'hri' ], $angle );

// header( 'Content-Type:text/plain' );

// echo $pdf->Output( 'TEST.PDF.pdf', 'S' );

$pdf->Output( 'TEST.PDF.' . sprintf( '%04d', rand( 1, 1000 ) ) . '.pdf', 'I' );

?>