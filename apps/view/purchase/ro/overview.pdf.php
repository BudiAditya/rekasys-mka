<?php
$pdf = new TabularPdf("P", "mm", "F4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Company", "Supplier", "No. Dokumen", "Tgl. PO", "Tgl. Prakiran", "Status", "PPN ?" ,"Included ?"),
    array(11, 15, 30, 35, 25, 30, 25, 15, 20)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN PO',0,0,'L');
$pdf->Ln();
$pdf->SetFont('Arial','', 12);
$pdf->Ln();
$pdf->Cell(25, 7, 'Tanggal', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate), 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(25, 7, 'Supplier', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $supplierName, 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(25, 7, 'Status', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $statusName, 0, 0, 'L');
$pdf->Ln();

//header table
$pdf->SetFont('Arial','B',10);
//set header table format (jika yg default tidak dipake)
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->fetch_assoc()) {
    $i++;

    $ppn = $rs["is_vat"] == 1 ? "Ya" : "Tidak";
    $inc = $rs["is_inc_vat"] == 1 ? "Ya" : "Tidak";

    $pdf->RowData(array($i, $rs["entity"], $rs["supplier"], $rs["doc_no"], date('d M Y', strtotime($rs["po_date"])), date('d M Y', strtotime($rs["expected_date"])),
                 $rs["status_name"], $ppn, $inc), 5, null, 0,  array("C", "L", "L", "L", "L", "L", "L", "L", "L", "L"));
}
$pdf->Output("Rekap dokumen PO.pdf", "D");