<?php
$pdf = new TabularPdf("P", "mm", "F4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Kode Barang", "Nama Barang", "Jumlah", "Satuan", "Harga"),
    array(11, 25, 45, 15, 15, 35)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI BARANG GN',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(25, 7, 'Tanggal', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate), 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(25, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $statusName->ShortDesc, 0, 0, 'L');
$pdf->Ln();

//header table
$pdf->SetFont('Arial','B',10);
//set header table format (jika yg default tidak dipake)
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$i = 0;
$total = 0;
while($rs = $report->fetch_assoc()) {
    $i++;
    $total = $total + $rs["total"];

    $pdf->RowData(array($i, $rs["item_code"], $rs["item_name"], $rs["jumlah"], $rs["uom_cd"], "Rp . " . number_format($rs["total"], 0,",",".")),
        5, null, 0,  array("C", "L", "L", "R", "L", "R"));
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell(111, 5, "TOTAL", "LRB", 0, "R");
$pdf->SetFont('Arial','',10);
$pdf->Cell(35, 5, "Rp . " . number_format($total, 0,",","."), "LRB", 0, "R");
$pdf->Output("Rekap barang GN.pdf", "D");