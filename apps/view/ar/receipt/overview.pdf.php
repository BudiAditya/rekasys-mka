<?php
/** @var Debtor[] $debtors */ /** @var Debtor $debt */  /** @var int $debtorId */  /** @var StatusCode[] $codes */ /** @var StatusCode $codeName */  /** @var int $status */
/** @var ReaderBase $report */ /** @var int $startDate */ /** @var int $endDate */ /** @var string $output */

$pdf = new TabularPdf("L", "mm", "A4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetMargins(5, 5);
$pdf->SetColumns(
    array("No.", "Company", "Dokumen", "Status", "Debtor", "PPh ?", "Tanggal", "Jumlah", "Alokasi", "PPh", "Akun"),
    array(9, 13, 35, 25, 0, 10, 25, 25, 35, 35, 35)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetDefaultAlignments(array("C", "L", "L", "L", "L", "C", "L", "L", "R", "R", "R"));

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN OFFICIAL RECEIPT',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(25, 7, 'Tanggal', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate), 0, 0, 'L');
$pdf->Ln();

$pdf->Cell(25, 7, 'Debtor', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$debtorName = $debt != null ? $debt->DebtorName: "SEMUA DEBTOR";
$pdf->Cell(100, 7, $debtorName, 0, 0, 'L');
$pdf->Ln();

$pdf->Cell(25, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$statusName = $codeName != null ? $codeName->ShortDesc : "SEMUA STATUS";
$pdf->Cell(100, 7, $statusName, 0, 0, 'L');
$pdf->Ln();

//header table
$pdf->SetFont('Arial','B',10);

//$pdf->Cell(9, 10, 'No.', 'TRBL', 0, 'C');
//$pdf->Cell(13, 10, 'Company', 'TBR', 0, 'C');
//$pdf->Cell(35, 10, 'No.Dokumen', 'TBR', 0, 'C');
//$pdf->Cell(25, 10, 'Status', 'TBR', 0, 'C');
//$pdf->Cell(40, 10, 'Debtor', 'TBR', 0, 'C');
//$pdf->MultiCell(10, 5, "Flag PPh", 'TBR', 'C');
//$pdf->SetXY(137, 45);
//$pdf->Cell(25, 10, 'Tgl.Dokumen', 'TBR', 0, 'C');
//$pdf->Cell(25, 10, 'Tgl.Transaksi', 'TBR', 0, 'C');
//$pdf->Cell(35, 10, 'Jumlah OR', 'TBR', 0, 'C');
//$pdf->Cell(35, 10, 'Jumlah Alokasi', 'TBR', 0, 'C');
//$pdf->SetXY(257, 45);
//$pdf->MultiCell(35, 5, "Jumlah Pengurangan", 'TBR', 'C');
$pdf->RowHeader(10, array("TRBL", "TRB", "TRB", "TRB", "TRB", "RB", "RB", "RB", "RB", "RB", "RB"));


$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->FetchAssoc()) {
    $i++;

	$data = array();
	$data[] = $i;
	$data[] = $rs["entity_cd"];
	$data[] = $rs["doc_no"];
	$data[] = $rs["short_desc"];
	$data[] = $rs["debtor_name"];
	$data[] = $rs["pph_flag"] == 1 ? "Ya" : "Tidak";
	$data[] = $rs["doc_date"];
	$data[] = number_format($rs["trx_amt"], 2,",",".");
	$data[] = number_format($rs["alloc_amt"], 2,",",".");
	$data[] = number_format($rs["deduction_amt"], 2,",",".");
	$data[] = $rs["acc_no"];

    $pdf->RowData($data, 5);
}
$pdf->Output("Rekap dokumen official receipt.pdf", "D");