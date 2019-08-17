<?php
$pdf = new TabularPdf("L", "mm", "F4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Company", "Supplier", "No. Dokumen", "Tgl. GN", "Status", "PPN ?", "Included ?", "Pembayaran", "Terms", "Lokasi Gudang"),
    array(11, 15, 50, 35, 30, 28, 20, 25, 25, 20, 50)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','B',22);
$pdf->Cell(290,7,'REKAPITULASI DOKUMEN GN',0,0,'L');
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
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null,
               array("C", "C", "C", "C", "C", "C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$i = 0;
while($rs = $report->fetch_assoc()) {
    $i++;

    $ppn = $rs["is_vat"] == 1 ? "Ya" : "Tidak";
    $inc = $rs["is_inc_vat"] == 1 ? "Ya" : "Tidak";
    $pay = $rs["pay_mode"] == 1 ? "CASH" : "KREDIT";

    $pdf->RowData(array($i, $rs["entity"], $rs["supplier"], $rs["doc_no"], date('d M Y', strtotime($rs["gn_date"])), $rs["status_name"],
                        $ppn, $inc, $pay, $rs["credit_terms"] . " hari", $rs["warehouse"]), 5, null, 0,  array("C", "L", "L", "L", "L", "L", "L", "L", "L", "L", "L"));
}
$pdf->Output("Rekap dokumen GN.pdf", "D");