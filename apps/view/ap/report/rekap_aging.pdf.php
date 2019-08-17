<?php
/** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */

$pdf = new TabularPdf("L");
$columns = array("No.", "Kode Debtor", "Nama Debtor", "1 - 30 hari", "31 - 60 hari", "61 - 90 hari", "91 - 120 hari", "121 - 150 hari", "> 150 hari");
$widths = array(10, 20, 0, 30, 30, 30, 30, 30, 30);
$defBorder = array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB");
$defAlignment = array("R", "L", "L", "R", "R", "R", "R", "R", "R");

// Setting default PDF
$pdf->SetFont("Arial", "", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetColumns($columns, $widths);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments($defAlignment);
$pdf->SetDefaultBorders($defBorder);

$pdf->Open();
$pdf->AddPage();
$widths = $pdf->GetWidths();	// Ambil kembali ukuran kolom yang sudah dihitung ulang....
$totalWidth = array_sum($widths);
$merge3Cell = $widths[0] + $widths[1] + $widths[2];

// Bikin Header
$pdf->SetFont("Arial", "", 16);
$pdf->Cell($totalWidth, 8, sprintf("Rekap Aging Hutang Supplier Company : %s - %s", $company->EntityCd, $company->CompanyName), null, 1, "C");
$pdf->SetFont("Arial", "", 12);
$pdf->Cell($totalWidth, 6, "Per Tanggal: " . date(HUMAN_DATE, $date), null, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 9);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C", "C"));

// Tulis Data
$pdf->SetFont("Arial", "", 9);
$counter = 0;
$sums = array(
	"hutang_1" => 0
	, "hutang_2" => 0
	, "hutang_3" => 0
	, "hutang_4" => 0
	, "hutang_5" => 0
	, "hutang_6" => 0
);
while ($row = $report->FetchAssoc()) {
	$counter++;
	$sums["hutang_1"] += $row["sum_hutang_1"];
	$sums["hutang_2"] += $row["sum_hutang_2"];
	$sums["hutang_3"] += $row["sum_hutang_3"];
	$sums["hutang_4"] += $row["sum_hutang_4"];
	$sums["hutang_5"] += $row["sum_hutang_5"];
	$sums["hutang_6"] += $row["sum_hutang_6"];

	// Buff data
	$data = array();
	$data[] = $counter . ".";
	$data[] = $row["creditor_cd"];
	$data[] = $row["creditor_name"];
	$data[] = number_format($row["sum_hutang_1"], 2);
	$data[] = number_format($row["sum_hutang_2"], 2);
	$data[] = number_format($row["sum_hutang_3"], 2);
	$data[] = number_format($row["sum_hutang_4"], 2);
	$data[] = number_format($row["sum_hutang_5"], 2);
	$data[] = number_format($row["sum_hutang_6"], 2);
	// Flush to PDF
	$pdf->RowData($data, 6);
}
// Sums
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($merge3Cell, 6, "TOTAL : ", $defBorder[0], 0, "R");
$pdf->Cell($widths[3], 6, number_format($sums["hutang_1"], 2), $defBorder[3], 0, "R");
$pdf->Cell($widths[4], 6, number_format($sums["hutang_2"], 2), $defBorder[4], 0, "R");
$pdf->Cell($widths[5], 6, number_format($sums["hutang_3"], 2), $defBorder[5], 0, "R");
$pdf->Cell($widths[6], 6, number_format($sums["hutang_4"], 2), $defBorder[6], 0, "R");
$pdf->Cell($widths[7], 6, number_format($sums["hutang_5"], 2), $defBorder[7], 0, "R");
$pdf->Cell($widths[8], 6, number_format($sums["hutang_6"], 2), $defBorder[8], 0, "R");

// Send to browser...
$pdf->Output(sprintf("rekap-aging_%s.pdf", $company->EntityCd), "D");


// End of file: rekap_aging.pdf.php
