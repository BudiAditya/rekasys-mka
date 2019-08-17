<?php
/** @var $output string */
/** @var $creditors Creditor[] */ /** @var $credt Creditor */ /** @var $creditorId int */
/** @var $docTypes DocType[] */ /** @var $doc DocType */ /** @var $docTypeId int */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */

$pdf = new TabularPdf("L", "mm", "A4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetDefaultAlignments(array("C", "L", "L", "L", "L", "L", "R", "R", "R", "R"));
$pdf->SetMargins(5, 10);
$pdf->SetColumns(
    array("No", "Company", "No.Invoice", "Reference", "Creditor", "Deskripsi","DPP", "PPN", "PPh", "Total"),
    array(9, 13, 33, 25, 45, 0, 30, 25, 25, 30)
);

$widths = $pdf->GetColumnWidths();
$widthForTotal = $widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4] + $widths[5];

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
$pdf->Cell(35, 7, 'Creditor', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$creditorName = $credt != null ? $credt->CreditorName : "SEMUA CREDITOR";
$pdf->Cell(100, 7, $creditorName, 0, 0, 'L');
$pdf->Ln();

$docDesc = $doc != null ? $doc->Description : "SEMUA JENIS TAGIHAN";
$pdf->Cell(35, 7, 'Jenis Tagihan', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'L');
$pdf->Cell(100, 7, $docDesc, 0, 0, 'L');
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
$pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C", "C", "C"));

$pdf->SetFont('Arial','',10);

$docNo = null;
$baseAmount = 0;
$taxAmount = 0;
$deductAmount = 0;
$i = 1;
while($rs = $report->FetchAssoc()) {
    if ($rs["doc_no"] != $docNo){
        $entity = $rs["entity_cd"];
        $doc = $rs["doc_no"] . "\n" . date('d M Y', strtotime($rs["doc_date"]));
		$reff = $rs["reference_no"];
        $namaCreditor = $rs["creditor_name"];
        $docNo = $rs["doc_no"];
        $counter = $i++;
    } else {
        $entity = "";
        $doc = "";
		$reff = "";
        $namaCreditor = "";
        $counter = "";
    }

    $baseAmount += $rs["dpp"];
    $taxAmount += $rs["tax"];
    $deductAmount += $rs["deduction"];

	$pdf->RowData(array(
		$counter,
		$entity,
		$doc,
		$reff,
		$namaCreditor,
		$rs["description"],
		number_format($rs["dpp"], 2, ",", "."),
		number_format($rs["tax"], 2, ",", "."),
		number_format($rs["deduction"], 2, ",", "."),
		number_format($rs["dpp"] + $rs["tax"] - $rs["deduction"], 2, ",", ".")
	), 5);
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell($widthForTotal, 5, "TOTAL", "RBL", 0, "R");
$pdf->Cell($widths[6], 5, number_format($baseAmount, 2,",","."), "RBL", 0, "R");
$pdf->Cell($widths[7], 5, number_format($taxAmount, 2,",","."), "RBL", 0, "R");
$pdf->Cell($widths[8], 5, number_format($deductAmount, 2,",","."), "RBL", 0, "R");
$pdf->Cell($widths[9], 5, number_format($baseAmount + $taxAmount - $deductAmount, 2,",","."), "RBL", 0, "R");

$pdf->Output("Rekap dokumen ap invoice.pdf", "D");