<?php
/**
 * This one is obsoleted
 */
$pdf = new TabularPdf("P", "mm", "A4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetMargins(5, 10);
$pdf->SetColumns(
    array("", "", "", "", "", "", ""),
    array(9, 13, 35, 25, 0, 25,35)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB"));
$widths = $pdf->GetColumnWidths();

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN AP PAYMENT',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(25, 7, 'Tanggal', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate), 0, 0, 'L');
$pdf->Ln();

$pdf->Cell(25, 7, 'Creditor', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$creditorName = $credt != null ? $credt->CreditorName: "SEMUA CREDITOR";
$pdf->Cell(100, 7, $creditorName, 0, 0, 'L');
$pdf->Ln();

$pdf->Cell(25, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$statusName = $codeName != null ? $codeName->ShortDesc : "SEMUA STATUS";
$pdf->Cell(100, 7, $statusName, 0, 0, 'L');
$pdf->Ln();

//header table
$pdf->SetFont('Arial','B',10);

$pdf->Cell(9, 10, 'No.', 'TRBL', 0, 'C');
$pdf->Cell(13, 10, 'Company', 'TBR', 0, 'C');
$pdf->Cell(35, 10, 'No.Dokumen', 'TBR', 0, 'C');
$pdf->Cell(25, 10, 'Status', 'TBR', 0, 'C');
$pdf->Cell($widths[4], 10, 'Creditor', 'TBR', 0, 'C');
$pdf->Cell(25, 10, 'Tgl.Dokumen', 'TBR', 0, 'C');
$pdf->Cell(35, 10, 'Jumlah Pembayaran', 'TBR', 0, 'C');
$pdf->Ln();
$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->fetch_assoc()) {
    $i++;

    $pdf->RowData(array($i, $rs["entity_cd"], $rs["doc_no"], $rs["short_desc"], $rs["creditor_name"], date('d M Y', strtotime($rs["doc_date"])),
        "Rp. " . number_format($rs["amount"], 2,",",".")),
        5, null, 0,  array("C", "L", "L", "L", "L", "R"));
}
$pdf->Output("Rekap dokumen AP Payment.pdf", "D");