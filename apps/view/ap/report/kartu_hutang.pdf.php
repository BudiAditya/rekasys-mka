<?php
/** @var $start int */ /** @var $end int */ /** @var $supplierId int */ /** @var $suppliers Creditor[] */ /** @var $report null|ReaderBase */ /** @var $saldoAwal float */
foreach ($suppliers as $supplier) {
	if ($supplier->Id == $supplierId) {
		$selectedSupplier = $supplier;
		break;
	}
}

$pdf = new TabularPdf("L");
$columns = array("No.", "Tanggal", "No. Dokumen", "Keterangan", "Debit", "Kredit", "Saldo");
$widths = array(7, 22, 33, 0, 30, 30, 30);
$defBorder = array("RBL", "RB", "RB", "RB", "RB", "RB", "RB");
$defAlignment = array("R", "L", "L", "L", "R", "R", "R");

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
$merge4Cell = $widths[0] + $widths[1] + $widths[2] + $widths[3];

// Bikin Header
$pdf->SetFont("Arial", "", 16);
$pdf->Cell($totalWidth, 8, sprintf("Kartu Hutang: %s - %s", $selectedSupplier->CreditorCd, $selectedSupplier->CreditorName), null, 1, "C");
$pdf->SetFont("Arial", "", 12);
$pdf->Cell($totalWidth, 6, sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)), null, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 9);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C"));

// Tulis Data
// Saldo Awal
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($merge4Cell, 6, "Saldo Awal per tanggal " . date(HUMAN_DATE, $start) . ": ", $defBorder[0], 0, "R");
$pdf->Cell($widths[4], 6, $saldoAwal < 0 ? number_format($saldoAwal * -1, 2) : "", $defBorder[4], 0, "R");
$pdf->Cell($widths[5], 6, $saldoAwal > 0 ? number_format($saldoAwal, 2) : "", $defBorder[5], 0, "R");
$pdf->Cell($widths[6], 6, number_format($saldoAwal, 2), $defBorder[6], 1, "R");
// Data transaksi
$pdf->SetFont("Arial", "", 9);
$counter = 0;
$saldo = $saldoAwal;
$prevDate = null;
$sums = array(
	"debit" => 0
	, "credit" => 0
);
while ($row = $report->FetchAssoc()) {
	$date = strtotime($row["voucher_date"]);
	$debit = $row["debet"];
	$credit = $row["kredit"];
	if ($debit + $credit == 0) {
		continue;
	}
	$counter++;

	$sums["debit"] += $debit;
	$sums["credit"] += $credit;
	if ($prevDate != $date) {
		$prevDate = $date;
	} else {
		$date = null;
	}
	$saldo += $credit - $debit;

	$data = array(
		$counter . ".",
		$date == null ? "" : date(HUMAN_DATE, $date),
		$row["doc_no"],
		$row["note"],
		$debit != 0 ? number_format($debit, 2) : "",
		$credit != 0 ? number_format($credit, 2) : "",
		number_format($saldo, 2));
	$pdf->RowData($data, 6);
}
// Sums
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($merge4Cell, 6, "TOTAL : ", $defBorder[0], 0, "R");
$pdf->Cell($widths[4], 6, number_format($sums["debit"], 2), $defBorder[4], 0, "R");
$pdf->Cell($widths[5], 6, number_format($sums["credit"], 2), $defBorder[5], 0, "R");
$pdf->Cell($widths[6], 6, number_format($saldo, 2), $defBorder[6], 1, "R");

// Send to browser...
$pdf->Output(sprintf("kartu-hutang_%s.pdf", $selectedSupplier->CreditorCd), "D");

// End of file: kartu_hutang.pdf.php
