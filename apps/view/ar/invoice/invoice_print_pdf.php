<?php
/** @var $invoice Invoice */ /** @var $sales Karyawan[] */
if (strlen($invoice->ExSoNo) > 2){
    $esn = ' - S/O No: '.$invoice->ExSoNo;
}else{
    $esn = '';
}
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
$fontFamily = "helvetica";
$widths = $pdf->GetWidths();

//$logo = 'public/images/company/mtc.jpg';
$y = 0;
$y1 = 0;
//if ($idx % 2 == 0) {
//	$y = 0;
$pdf->AddPage();
//} else {
//	$y = 140;
//}
$pdf->SetY($y);
$pdf->SetFont("helvetica", "B", 11);
$pdf->Cell(1,5,$invoice->CompanyName, 0, 0, "L");
$pdf->SetFont("helvetica", "", 11);
$pdf->Cell($pdf->GetPaperWidth(), 5, "I N V O I C E", 0, 0, "C");
$pdf->Ln(7);
$pdf->SetFont("helvetica", "", 10);
$pdf->Cell(20, 5, "Nomor", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
//$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell(4, 5, $invoice->InvoiceNo, 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->SetFont("helvetica", "", 10);
$pdf->Cell(20, 5, "Customer", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
//$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell(4, 5, $invoice->CustomerName, 0, 0, "L");
$pdf->Ln(5);
$pdf->SetFont("helvetica", "", 10);
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
$pdf->Cell(4, 5, $invoice->SalesName.' - '.$invoice->CabangCode.$esn, 0, 0, "L");
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
$pdf->Line($x,$y+5,$x+196,$y+5);
$pdf->SetLineWidth(0.2);
$pdf->Line($x,$y+11,$x+196,$y+11);
//garis vertical header
$pdf->Line($x,$y+5,$x,$y+81);
//qty
$pdf->Line($x+20,$y+5,$x+20,$y+75);
//uraian
$pdf->Line($x+120,$y+5,$x+120,$y+105);
//harga
$pdf->Line($x+141,$y+5,$x+141,$y+75);
//discount
$pdf->Line($x+168,$y+5,$x+168,$y+105);
//subtotal
$pdf->Line($x+196,$y+5,$x+196,$y+105);
//garis datar footer 1
$pdf->Line($x,$y+75,$x+196,$y+75);
//garis datar footer 2
$pdf->Line($x,$y+81,$x+196,$y+81);
//garis datar footer 3
$pdf->Line($x+120,$y+87,$x+196,$y+87);
//garis datar footer 4
$pdf->Line($x+120,$y+93,$x+196,$y+93);
//garis datar footer 5
$pdf->Line($x+120,$y+99,$x+196,$y+99);
//garis datar footer 5
$pdf->Line($x+120,$y+105,$x+196,$y+105);
$pdf->Ln(6);
//header barang
$pdf->Cell(17,5,"  QTY",0,0,"C");
$pdf->Cell(90,5,"   KODE DAN NAMA BARANG",0,0,"C");
$pdf->Cell(48,5,"HARGA",0,0,"C");
$pdf->Cell(2,5," DISCOUNT",0,0,"C");
$pdf->Cell(47,5,"    J U M L A H",0,0,"C");
$y = $pdf->GetY();
//footer barang
$pdf->SetFont("helvetica", "", 9);
$pdf->Ln(70);
$pdf->Cell(167,5,"Sub Total",0,0,"R");
$pdf->Cell(30,5,number_format($invoice->BaseAmount,0,',','.'),0,0,"R");
$y1 = $pdf->GetY();
$pdf->Ln(6);
if ($invoice->Disc1Pct > 0){
	$pdf->Cell(167,5,"Discount (".number_format($invoice->Disc1Pct,1,',','.').' %)',0,0,"R");
	$pdf->Cell(30,5,'-'.number_format($invoice->Disc1Amount,0,',','.'),0,0,"R");
}else{
	$pdf->Cell(167,5,"Discount (0 %)",0,0,"R");
	$pdf->Cell(30,5,0,0,0,"R");
}

$pdf->Ln(6);
$pdf->Cell(167,5,"Pajak (".$invoice->TaxPct.' %)',0,0,"R");
if($invoice->TaxAmount > 0){
	$pdf->Cell(30,5,'+'.number_format($invoice->TaxAmount,0,',','.'),0,0,"R");
}else{
	$pdf->Cell(30,5,number_format($invoice->TaxAmount,0,',','.'),0,0,"R");

}
$pdf->Ln(6);
$pdf->Cell(167,5,$invoice->OtherCosts,0,0,"R");
if($invoice->OtherCostsAmount > 0){
	$pdf->Cell(30,5,'+'.number_format($invoice->OtherCostsAmount,0,',','.'),0,0,"R");
}else{
	$pdf->Cell(30,5,number_format($invoice->OtherCostsAmount,0,',','.'),0,0,"R");
}

$pdf->Ln(6);
$pdf->Cell(167,5,"Grand Total",0,0,"R");
$pdf->Cell(30,5,number_format($invoice->TotalAmount,0,',','.'),0,0,"R");
//detail barang
$pdf->SetX(16);
$pdf->SetY($y);
$qJenis = 0;
$qTotal = 0;
foreach($invoice->Details as $idx => $detail) {
	$pdf->Ln(5);
	if (right($detail->Qty,3) == '.00') {
        $pdf->Cell(21, 5, number_format($detail->Qty, 0) . ' ' . left(strtolower($detail->SatBesar) . '   ', 3), 0, 0, "R");
    }else{
        $pdf->Cell(21, 5, number_format($detail->Qty, 2) . ' ' . left(strtolower($detail->SatBesar) . '   ', 3), 0, 0, "R");
    }
    if (($detail->ItemNote == '') || ($detail->ItemNote == null)) {
        $pdf->Cell(94, 5, $detail->ItemCode . ' - ' . $detail->ItemDescs, 0, 0, "L");
    } else {
        $pdf->Cell(94, 5, $detail->ItemCode . ' - ' . $detail->ItemDescs . ' ' . $detail->ItemNote, 0, 0, "L");
    }
	$pdf->Cell(27,5,number_format($detail->Price,0,',','.'),0,0,"R");
	if ($detail->DiscAmount > 0){
		$pdf->Cell(28,5,number_format($detail->DiscAmount,0,',','.').' ('.$detail->DiscFormula.'%)',0,0,"R");
	}else{
		$pdf->Cell(28,5,' ',0,0,"R");
	}
	if ($detail->IsFree == 0){
		$pdf->Cell(27,5,number_format($detail->SubTotal,0,',','.'),0,0,"R");
	}else{
		$pdf->Cell(27,5,'*Free/Bonus*',0,0,"R");
	}
	$qJenis++;
	$qTotal+= $detail->Qty;
}
$pdf->SetXY(6,$y1);
$pdf->SetFont("helvetica", "", 9);
$pdf->Cell(5,5,'Total: '.$qTotal.' satuan *'.$qJenis.' macam*',0,0,"L");
$pdf->SetX(25);
$pdf->Write(20,'Diterima Oleh,');
$pdf->SetX(80);
$pdf->Write(20,'Hormat Kami,');
$pdf->SetY($pdf->GetY()+17);
$pdf->SetX(25);
$pdf->Write(20,'_________________');
$pdf->SetX(80);
$pdf->Write(20,'_________________');
$pdf->Ln(15);
$pdf->SetFont("helvetica", "i", 7);
$pdf->Cell(5,5,'Admin: '.$invoice->AdminName.' - Printed by: '.$userName.'  - Time: '.date('d-m-Y h:i:s'),0,0,"L");
