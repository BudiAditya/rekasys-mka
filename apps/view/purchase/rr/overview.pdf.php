<?php
$pdf = new TabularPdf("P", "mm", "F4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Company", "No.Dokumen", "Tgl.MR", "Status", "Tgl.Update"),
    array(11, 15, 50, 30, 30, 30)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN PR',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(25, 7, 'Tanggal', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate), 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(25, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $statusName, 0, 0, 'L');
$pdf->Ln();

$pdf->SetFont('Arial','B',10);
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->fetch_assoc()) {
    $i++;
    $updated = $rs["update_time"] != null ? date('d M Y', strtotime($rs["update_time"])) : "";

    $pdf->RowData(array($i, $rs["entity"], $rs["doc_no"], date('d M Y', strtotime($rs["pr_date"])), $rs["status_name"], $updated),
                  5, null, 0, array("C", "L", "L", "L", "L", "L"));
}

$pdf->Output("Rekap dokumen PR.pdf", "D");