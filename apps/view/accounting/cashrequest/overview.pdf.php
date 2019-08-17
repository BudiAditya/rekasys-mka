<?php
/** @var StatusCode[] $statuses */ /** @var int $statusCode */ /** @var ReaderBase $report */ /** @var string $output */ /** @var int $start */ /** @var int $end */
$statusName = "-- SEMUA STATUS --";
foreach ($statuses as $row) {
	if ($row->Code == $statusCode) {
		$statusName = $row->ShortDesc;
		break;
	}
}

$pdf = new TabularPdf("L", "mm", "letter");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Company", "No.Dokumen", "Tujuan NPKP", "Tgl. NPKP", "Prakiran Terima", "Jumlah", "Status"),
    array(11, 15, 40, 0, 23, 30, 30, 25)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN NPKP',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Cell(13, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $statusName, 0, 0, 'L');
$pdf->Ln();

$pdf->SetFont('Arial','B',10);
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->FetchAssoc()) {
    $i++;

    $pdf->RowData(array($i, $rs["entity"], $rs["doc_no"], $rs["objective"], date('d M Y', strtotime($rs["cash_request_date"])), date('d M Y', strtotime($rs["eta_date"])), number_format($rs["jumlah"], 0),
            $rs["status_name"]),5, null, 0, array("C", "L", "L", "L", "L", "L", "R", "L", "L"));
}

$pdf->Output("Rekap dokumen NPKP.pdf", "D");