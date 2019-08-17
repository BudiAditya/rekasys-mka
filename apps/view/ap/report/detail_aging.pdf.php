<?php
/** @var $creditorId null|int */ /** @var $creditors Creditor[] */ /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */

$pdf = new TabularPdf("L");
$columns = array("No.", "No. Dokumen", "Tgl. Dokumen", "Nilai Dokumen", "1 - 30 hari", "31 - 60 hari", "61 - 90 hari", "91 - 120 hari", "121 - 150 hari", "> 150 hari", "Total");
$widths = array(7, 0, 25, 28, 28, 28, 28, 28, 28, 28, 28);
$defBorder = array("RBL", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB", "RB");
$defAlignment = array("R", "L", "L", "R", "R", "R", "R", "R", "R", "R", "R");

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
$merge8Cell = $widths[3] + $widths[4] + $widths[5] + $widths[6] + $widths[7] + $widths[8] + $widths[9] + $widths[10];

// Bikin Header
$pdf->SetFont("Arial", "", 16);
$pdf->Cell($totalWidth, 8, sprintf("Detail Aging Hutang Supplier Company : %s - %s", $company->EntityCd, $company->CompanyName), null, 1, "C");
$pdf->SetFont("Arial", "", 12);
$pdf->Cell($totalWidth, 6, "Per Tanggal: " . date(HUMAN_DATE, $date), null, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 9);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C", "C", "C", "C"));

// Tulis Data
$pdf->SetFont("Arial", "", 9);
$counter = 0;
$sums = array(
	"dokumen" => 0
	, "hutang_1" => 0
	, "hutang_2" => 0
	, "hutang_3" => 0
	, "hutang_4" => 0
	, "hutang_5" => 0
	, "hutang_6" => 0
	, "total" => 0
);
$prevCreditorId = null;
while ($row = $report->FetchAssoc()) {
	$counter++;
	$amount = $row["sum_amount"];

	$sums["dokumen"] += $amount;
	$age = $row["age"];
	$date = strtotime($row["doc_date"]);

	// Reset variable
	$hutang1 = 0;
	$hutang2 = 0;
	$hutang3 = 0;
	$hutang4 = 0;
	$hutang5 = 0;
	$hutang6 = 0;
	$hutang = $amount - $row["sum_paid"];

	if ($age <= 0) {
		// Nothing to do... data ini di skip tapi masih ditampilkan walau 0 semua
	} else if ($age <= 30) {
		$hutang1 = $hutang;
		$sums["hutang_1"] += $hutang;
		$sums["total"] += $hutang;
	} else if ($age <= 60) {
		$hutang2 = $hutang;
		$sums["hutang_2"] += $hutang;
		$sums["total"] += $hutang;
	} else if ($age <= 90) {
		$hutang3 = $hutang;
		$sums["hutang_3"] += $hutang;
		$sums["total"] += $hutang;
	} else if ($age <= 120) {
		$hutang4 = $hutang;
		$sums["hutang_4"] += $hutang;
		$sums["total"] += $hutang;
	} else if ($age <= 150) {
		$hutang5 = $hutang;
		$sums["hutang_5"] += $hutang;
		$sums["total"] += $hutang;
	} else {
		$hutang6 = $hutang;
		$sums["hutang_6"] += $hutang;
		$sums["total"] += $hutang;
	}

	// Header untuk debtor..
	if ($prevCreditorId != $row["supplier_id"]) {
		$pdf->SetFont("Arial", "B", 9);
		// Counter nomor ketika ganti debtor ter-reset
		$counter = 1;
		$prevCreditorId = $row["supplier_id"];
		$pdf->Cell($merge3Cell, 6, "Kode Supplier: " . $row["creditor_cd"], "RBL", 0, "R");
		$pdf->Cell($merge8Cell, 6, $row["creditor_name"], "RB", 1);
		$pdf->SetFont("Arial", "", 9);
	}

	// Buff data
	$data = array();
	$data[] = $counter . ".";
	$data[] = $row["doc_no"];
	$data[] = date(HUMAN_DATE, $date);
	$data[] = number_format($amount, 2);
	$data[] = number_format($hutang1, 2);
	$data[] = number_format($hutang2, 2);
	$data[] = number_format($hutang3, 2);
	$data[] = number_format($hutang4, 2);
	$data[] = number_format($hutang5, 2);
	$data[] = number_format($hutang6, 2);
	$data[] = number_format($hutang1 + $hutang2 + $hutang3 + $hutang4 + $hutang5 + $hutang6, 2);
	// Flush to PDF
	$pdf->RowData($data, 6);
}
// Sums
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($merge3Cell, 6, "TOTAL : ", $defBorder[0], 0, "R");
$pdf->Cell($widths[3], 6, number_format($sums["dokumen"], 2), $defBorder[3], 0, "R");
$pdf->Cell($widths[4], 6, number_format($sums["hutang_1"], 2), $defBorder[4], 0, "R");
$pdf->Cell($widths[5], 6, number_format($sums["hutang_2"], 2), $defBorder[5], 0, "R");
$pdf->Cell($widths[6], 6, number_format($sums["hutang_3"], 2), $defBorder[6], 0, "R");
$pdf->Cell($widths[7], 6, number_format($sums["hutang_4"], 2), $defBorder[7], 0, "R");
$pdf->Cell($widths[8], 6, number_format($sums["hutang_5"], 2), $defBorder[8], 0, "R");
$pdf->Cell($widths[9], 6, number_format($sums["hutang_6"], 2), $defBorder[9], 0, "R");
$pdf->Cell($widths[10], 6, number_format($sums["total"], 2), $defBorder[10], 0, "R");

// Send to browser...
$pdf->Output(sprintf("detail-aging_%s.pdf", $company->EntityCd), "D");


// End of file: detail_aging.pdf.php
