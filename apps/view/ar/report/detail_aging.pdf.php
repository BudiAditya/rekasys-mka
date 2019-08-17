<?php
/** @var $debtorId null|int */ /** @var $debtors Debtor[] */ /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */

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
$pdf->Cell($totalWidth, 8, sprintf("Detail Aging Piutang Debtor Company : %s - %s", $company->EntityCd, $company->CompanyName), null, 1, "C");
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
	, "piutang_1" => 0
	, "piutang_2" => 0
	, "piutang_3" => 0
	, "piutang_4" => 0
	, "piutang_5" => 0
	, "piutang_6" => 0
	, "total" => 0
);
$prevDebtorId = null;
while ($row = $report->FetchAssoc()) {
	$counter++;
	if ($row["is_pkp"] == 1) {
		$amount = $row["sum_base"] + $row["sum_tax"] - $row["sum_deduction"];
	} else {
		$amount = $row["sum_base"] + $row["sum_tax"];
	}

	$sums["dokumen"] += $amount;
	$age = $row["age"];
	$date = strtotime($row["doc_date"]);

	// Reset variable
	$piutang1 = 0;
	$piutang2 = 0;
	$piutang3 = 0;
	$piutang4 = 0;
	$piutang5 = 0;
	$piutang6 = 0;
	$piutang = $amount - $row["sum_paid"];

	if ($age <= 0) {
		// Nothing to do... data ini di skip tapi masih ditampilkan walau 0 semua
	} else if ($age <= 30) {
		$piutang1 = $piutang;
		$sums["piutang_1"] += $piutang;
		$sums["total"] += $piutang;
	} else if ($age <= 60) {
		$piutang2 = $piutang;
		$sums["piutang_2"] += $piutang;
		$sums["total"] += $piutang;
	} else if ($age <= 90) {
		$piutang3 = $piutang;
		$sums["piutang_3"] += $piutang;
		$sums["total"] += $piutang;
	} else if ($age <= 120) {
		$piutang4 = $piutang;
		$sums["piutang_4"] += $piutang;
		$sums["total"] += $piutang;
	} else if ($age <= 150) {
		$piutang5 = $piutang;
		$sums["piutang_5"] += $piutang;
		$sums["total"] += $piutang;
	} else {
		$piutang6 = $piutang;
		$sums["piutang_6"] += $piutang;
		$sums["total"] += $piutang;
	}

	// Header untuk debtor..
	if ($prevDebtorId != $row["debtor_id"]) {
		$pdf->SetFont("Arial", "B", 9);
		// Counter nomor ketika ganti debtor ter-reset
		$counter = 1;
		$prevDebtorId = $row["debtor_id"];
		$pdf->Cell($merge3Cell, 6, "Kode Debtor: " . $row["debtor_cd"], "RBL", 0, "R");
		$pdf->Cell($merge8Cell, 6, sprintf("%s (Merk Dagang: %s)", $row["debtor_name"], $row["trade_name"]), "RB", 1);
		$pdf->SetFont("Arial", "", 9);
	}

	// Buff data
	$data = array();
	$data[] = $counter . ".";
	$data[] = $row["doc_no"];
	$data[] = date(HUMAN_DATE, $date);
	$data[] = number_format($amount, 2);
	$data[] = number_format($piutang1, 2);
	$data[] = number_format($piutang2, 2);
	$data[] = number_format($piutang3, 2);
	$data[] = number_format($piutang4, 2);
	$data[] = number_format($piutang5, 2);
	$data[] = number_format($piutang6, 2);
	$data[] = number_format($piutang1 + $piutang2 + $piutang3 + $piutang4 + $piutang5 + $piutang6, 2);
	// Flush to PDF
	$pdf->RowData($data, 6);
}
// Sums
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($merge3Cell, 6, "TOTAL : ", $defBorder[0], 0, "R");
$pdf->Cell($widths[3], 6, number_format($sums["dokumen"], 2), $defBorder[3], 0, "R");
$pdf->Cell($widths[4], 6, number_format($sums["piutang_1"], 2), $defBorder[4], 0, "R");
$pdf->Cell($widths[5], 6, number_format($sums["piutang_2"], 2), $defBorder[5], 0, "R");
$pdf->Cell($widths[6], 6, number_format($sums["piutang_3"], 2), $defBorder[6], 0, "R");
$pdf->Cell($widths[7], 6, number_format($sums["piutang_4"], 2), $defBorder[7], 0, "R");
$pdf->Cell($widths[8], 6, number_format($sums["piutang_5"], 2), $defBorder[8], 0, "R");
$pdf->Cell($widths[9], 6, number_format($sums["piutang_6"], 2), $defBorder[9], 0, "R");
$pdf->Cell($widths[10], 6, number_format($sums["total"], 2), $defBorder[10], 0, "R");

// Send to browser...
$pdf->Output(sprintf("detail-aging_%s.pdf", $company->EntityCd), "D");


// End of file: detail_aging.pdf.php
