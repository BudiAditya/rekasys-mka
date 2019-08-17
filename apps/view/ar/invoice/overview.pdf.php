<?php
/** @var $output string */
/** @var $debtors Debtor[] */ /** @var $debt Debtor */ /** @var $debtorId int */
/** @var $billTypes BillType[] */ /** @var $billTypeId int[] */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */

$pdf = new TabularPdf("L", "mm", "A4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Company", "No.Invoice", "Tgl.Invoice", "Kode Debtor", "Nama Debtor", "TRX", "Deskripsi","DPP", "PPN", "PPh"),
    array(9, 12, 33, 23, 23, 0, 10, 40, 30, 25, 25)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 5);
$widths = $pdf->GetColumnWidths();
$widthForTotal = $widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4] + $widths[5] + $widths[6] + $widths[7];

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN INVOICE',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(35, 7, 'Tanggal', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate), 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(35, 7, 'Debtor', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$debtorName = $debt != null ? $debt->DebtorName : "SEMUA DEBTOR";
$pdf->Cell(100, 7, $debtorName, 0, 0, 'L');
$pdf->Ln();

$buff = array();
foreach($billTypes as $billType){
	if(in_array($billType->Id, $billTypeId)) {
		$buff[] = $billType->BillTypeDesc;
	}
}
$billDesc = count($buff) > 0 ? implode(", ", $buff) : "SEMUA JENIS TAGIHAN";
$pdf->Cell(35, 7, 'Jenis Tagihan', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $billDesc, 0, 0, 'L');
$pdf->Ln();

$pdf->Cell(35, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
if ($status == -1) {
    $statusName = "SEMUA STATUS";
} elseif ($status == 0) {
    $statusName = "UNPOSTED";
} else {
    $statusName = "POSTED";
}
$pdf->Cell(100, 7, $statusName, 0, 0, 'L');
$pdf->Ln();

//header table
$pdf->SetFont('Arial','B',10);
//set header table format (jika yg default tidak dipake)
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$docNo = null;
$baseAmount = 0;
$taxAmount = 0;
$deducAmount = 0;
$i = 1;
while($rs = $report->fetch_assoc()) {
    if ($rs["doc_no"] != $docNo){
        $entity = $rs["entity_cd"];
        $doc = $rs["doc_no"];
        $date = date('d M Y', strtotime($rs["doc_date"]));
		$kodeDebtor = $rs["debtor_cd"];
        $namaDebtor = $rs["debtor_name"];
        $docNo = $rs["doc_no"];
        $counter = $i++;
    } else {
        $entity = "";
        $doc = "";
        $date = "";
		$kodeDebtor = "";
        $namaDebtor = "";
        $counter = "";
    }

    $baseAmount += $rs["base_amt"];
    $taxAmount += $rs["tax_amt"];
    $deducAmount += $rs["deduction_amt"];

    $pdf->RowData(array($counter, $entity, $doc, $date, $kodeDebtor, $namaDebtor, $rs["code"], $rs["trx_descs"], number_format($rs["base_amt"], 2,",","."),
                  number_format($rs["tax_amt"], 2,",","."), number_format($rs["deduction_amt"], 2,",",".")),
                  5, null, 0,  array("C", "L", "L", "L", "L", "L", "L", "L", "R", "R", "R"));
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell($widthForTotal, 5, "TOTAL", "RBL", 0, "R");
$pdf->Cell($widths[8], 5, "Rp. " . number_format($baseAmount, 2,",","."), "RBL", 0, "R");
$pdf->Cell($widths[9], 5, "Rp. " . number_format($taxAmount, 2,",","."), "RBL", 0, "R");
$pdf->Cell($widths[10], 5, "Rp. " . number_format($deducAmount, 2,",","."), "RBL", 0, "R");

$pdf->Output("Rekap dokumen invoice.pdf", "D");