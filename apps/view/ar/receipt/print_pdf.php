<?php
require_once(LIBRARY . "tabular_pdf.php");
// array(612,396) => array(215.9, 139.7)
/** @var $invoice Invoice */ /** @var $sales Karyawan[] */
$pdf = new TabularPdf("P", "mm", "a5l");
$pdf->SetAutoPageBreak(true, 5);
$pdf->SetMargins(5, 5);
$widths = $pdf->GetColumnWidths();
$sumWidths = array_sum($widths);

$pdf->Open();
$pdf->AddFont("Tahoma");
$pdf->AddFont("Tahoma", "B");
$pdf->AddPage();
$output = $invoice->InvoiceNo;
$logo = 'smk-logo.jpg';
//$pdf->Image($logo,15,1,25,18);
//$pdf->SetLineWidth(0.4);
//$pdf->Line(15,20,200,20);
$pdf->SetFont("Tahoma", "U", 11);
$pdf->Cell(1,5,$invoice->CompanyName, 0, 0, "L");
$pdf->SetFont("Tahoma", "", 11);
$pdf->Cell($pdf->GetPaperWidth(), 5, "FAKTUR PENJUALAN", 0, 0, "C");
$pdf->Ln(7);
$pdf->SetFont("Tahoma", "", 10);
$pdf->Cell(20, 5, "Nomor", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->SetFont("Tahoma", "B", 10);
$pdf->Cell(4, 5, $invoice->InvoiceNo, 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->SetFont("Tahoma", "", 10);
$pdf->Cell(20, 5, "Customer", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->SetFont("Tahoma", "B", 10);
$pdf->Cell(4, 5, $invoice->CustomerName, 0, 0, "L");
$pdf->Ln(5);
$pdf->SetFont("Tahoma", "", 10);
$pdf->Cell(20, 5, "Tanggal", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $invoice->FormatInvoiceDate(JS_DATE), 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->Cell(20, 5, "Alamat", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $invoice->CustomerAddress, 0, 0, "L");
$pdf->Ln(5);
$pdf->Cell(20, 5, "Salesman", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $invoice->SalesName, 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->Cell(20, 5, "JTP", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
if($invoice->CreditTerms > 0){
    $pdf->Cell(4, 5, $invoice->FormatDueDate(JS_DATE).' ('.$invoice->CreditTerms.' hari)', 0, 0, "L");
}else{
    $pdf->Cell(4, 5, 'Cash', 0, 0, "L");
}
$pdf->Ln(2);
$x = 6;
$y = $pdf->GetY();
//garis datar header
$pdf->SetLineWidth(0.4);
$pdf->Line($x,$y+5,$x+195,$y+5);
$pdf->SetLineWidth(0.2);
$pdf->Line($x,$y+11,$x+195,$y+11);
//haris vertical header
$pdf->Line($x,$y+5,$x,$y+81);
//qty
$pdf->Line($x+20,$y+5,$x+20,$y+75);
//uraian
$pdf->Line($x+115,$y+5,$x+115,$y+105);
//harga
$pdf->Line($x+135,$y+5,$x+135,$y+75);
//discount
$pdf->Line($x+167,$y+5,$x+167,$y+105);
//subtotal
$pdf->Line($x+195,$y+5,$x+195,$y+105);
//garis datar footer 1
$pdf->Line($x,$y+75,$x+195,$y+75);
//garis datar footer 2
$pdf->Line($x,$y+81,$x+195,$y+81);
//garis datar footer 3
$pdf->Line($x+115,$y+87,$x+195,$y+87);
//garis datar footer 4
$pdf->Line($x+115,$y+93,$x+195,$y+93);
//garis datar footer 5
$pdf->Line($x+115,$y+99,$x+195,$y+99);
//garis datar footer 5
$pdf->Line($x+115,$y+105,$x+195,$y+105);
$pdf->Ln(6);
//header barang
$pdf->Cell(17,5,"QTY",0,0,"C");
$pdf->Cell(85,5,"KODE DAN NAMA BARANG",0,0,"C");
$pdf->Cell(48,5,"HARGA",0,0,"C");
$pdf->Cell(1,5,"DISKON",0,0,"C");
$pdf->Cell(55,5,"J U M L A H",0,0,"C");
//footer barang
$pdf->Ln(70);
$pdf->Cell(167,5,"Sub Total",0,0,"R");
$pdf->Cell(28,5,number_format($invoice->BaseAmount,0,',','.'),0,0,"R");
$pdf->Ln(6);
if ($invoice->Disc1Pct > 0){
    $pdf->Cell(167,5,"Discount (".number_format($invoice->Disc1Pct,1,',','.').' %)',0,0,"R");
    $pdf->Cell(28,5,'-'.number_format($invoice->Disc1Amount,0,',','.'),0,0,"R");
}else{
    $pdf->Cell(167,5,"Discount (0 %)",0,0,"R");
    $pdf->Cell(28,5,0,0,0,"R");
}

$pdf->Ln(6);
$pdf->Cell(167,5,"Pajak (".$invoice->TaxPct.' %)',0,0,"R");
if($invoice->TaxAmount > 0){
    $pdf->Cell(28,5,'+'.number_format($invoice->TaxAmount,0,',','.'),0,0,"R");
}else{
    $pdf->Cell(28,5,number_format($invoice->TaxAmount,0,',','.'),0,0,"R");

}
$pdf->Ln(6);
$pdf->Cell(167,5,$invoice->OtherCosts,0,0,"R");
if($invoice->OtherCostsAmount > 0){
    $pdf->Cell(28,5,'+'.number_format($invoice->OtherCostsAmount,0,',','.'),0,0,"R");
}else{
    $pdf->Cell(28,5,number_format($invoice->OtherCostsAmount,0,',','.'),0,0,"R");
}

$pdf->Ln(6);
$pdf->Cell(167,5,"Grand Total",0,0,"R");
$pdf->Cell(28,5,number_format($invoice->TotalAmount,0,',','.'),0,0,"R");
//detail barang
$pdf->SetX(16);
$pdf->SetY(31);
$pdf->SetFont("Tahoma", "", 9);
$qJenis = 0;
$qTotal = 0;
foreach($invoice->Details as $idx => $detail) {
    $pdf->Ln(5);
    $pdf->Cell(22,5,$detail->Qty.' '.strtolower($detail->SatBesar).' ',0,0,"R");
    $pdf->Cell(100,5,$detail->ItemCode.' - '.$detail->ItemDescs,0,0,"L");
    $pdf->Cell(13,5,number_format($detail->Price,0,',','.'),0,0,"R");
    if ($detail->DiscAmount > 0){
        $pdf->Cell(32,5,number_format($detail->DiscAmount,0,',','.').' ('.number_format($detail->DiscFormula,1,',','.').'%)',0,0,"R");
    }else{
        $pdf->Cell(32,5,' ',0,0,"R");
    }
    $pdf->Cell(28,5,number_format($detail->SubTotal,0,',','.'),0,0,"R");
    $qJenis++;
    $qTotal+= $detail->Qty;
}
$pdf->SetXY(6,100);
$pdf->SetFont("Tahoma", "", 9);
$pdf->Cell(5,5,'Total: '.$qTotal.' satuan *'.$qJenis.' jenis*',0,0,"L");
$pdf->SetX(25);
$pdf->Write(20,'Diterima Oleh,');
$pdf->SetX(80);
$pdf->Write(20,'Hormat Kami,');
$pdf->SetY($pdf->GetY()+17);
$pdf->SetX(25);
$pdf->Write(20,'_________________');
$pdf->SetX(80);
$pdf->Write(20,'_________________');
$pdf->Ln(13);
$pdf->SetFont("Times", "i", 7);
$pdf->Cell(195,5,'Printed by: '.$userName.' time: '.date('d-m-Y h:ia'),0,0,"R");
//print to file
$pdf->Output($output.".pdf", "D");