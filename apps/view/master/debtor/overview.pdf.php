<?php
$pdf = new TabularPdf("L", "mm", "F4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Company", "Kode", "Jenis", "Nama Creditor", "Alamat", "Core Business"),
    array(11, 15, 20, 20, 75, 90, 90)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DATA CREDITOR',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(30, 7, 'Jenis Creditor', 0, 0, 'L');
$pdf->Cell(5, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $typeDesc, 0, 0, 'L');
$pdf->Ln();

$pdf->SetFont('Arial','B',10);
//set header table format (jika yg default tidak dipake)
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->fetch_assoc()) {
    $i++;

    $pdf->RowData(array($i, $rs["entity"], $rs["creditor_cd"], $rs["type_desc"], $rs["creditor_name"], $rs["address1"], $rs["core_business"]), 5, null, 0,
                  array("C", "L", "L", "L", "L", "L", "L"));
}
$pdf->Output("Rekap data creditor.pdf" ,"D");