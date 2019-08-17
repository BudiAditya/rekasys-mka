<?php
/** @var $start int */ /** @var $end int */ /** @var $company Company */ /** @var $report null|ReaderBase */

$pdf = new TabularPdf();
$columns = array("No.", "Kode Supplier", "Nama Supplier", "Saldo Awal", "Debet", "Kredit", "Sisa");
$widths = array(10, 25, 0, 25, 25, 25, 25);
$defBorder = array("RBL", "RB", "RB", "RB", "RB", "RB", "RB");
$defAlignment = array("R", "L", "L", "R", "R", "R", "R");

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
$pdf->Cell($totalWidth, 8, sprintf("Rekap Hutang Supplier: %s - %s", $company->EntityCd, $company->CompanyName), null, 1, "C");
$pdf->SetFont("Arial", "", 12);
$pdf->Cell($totalWidth, 6, sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)), null, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 9);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C"));

// Tulis Data
$pdf->SetFont("Arial", "", 9);
$counter = 0;
$sums = array(
	"saldoAwal" => 0
	, "debet" => 0
	, "kredit" => 0
);
while ($row = $report->FetchAssoc()) {
	$counter++;
	$saldoAwal = $row["saldo_kredit"] - $row["saldo_debet"] + $row["prev_kredit"] - $row["prev_debet"];
	$debet = $row["current_debet"];
	$kredit = $row["current_kredit"];

	$sums["saldoAwal"] += $saldoAwal;
	$sums["debet"] += $debet;
	$sums["kredit"] += $kredit;

	// Buff data
	$data = array();
	$data[] = $counter . ".";
	$data[] = $row["creditor_cd"];
	$data[] = $row["creditor_name"];
	$data[] = number_format($saldoAwal, 2);
	$data[] = number_format($debet, 2);
	$data[] = number_format($kredit, 2);
	$data[] = number_format($saldoAwal - $debet + $kredit, 2);
	// Flush to PDF
	$pdf->RowData($data, 6);
}
// Sums
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($merge3Cell, 6, "TOTAL : ", $defBorder[0], 0, "R");
$pdf->Cell($widths[3], 6, number_format($sums["saldoAwal"], 2), $defBorder[4], 0, "R");
$pdf->Cell($widths[4], 6, number_format($sums["debet"], 2), $defBorder[5], 0, "R");
$pdf->Cell($widths[5], 6, number_format($sums["kredit"], 2), $defBorder[5], 0, "R");
$pdf->Cell($widths[6], 6, number_format($sums["saldoAwal"] - $sums["debet"] + $sums["kredit"], 2), $defBorder[5], 0, "R");

// Send to browser...
$pdf->Output(sprintf("rekap-hutang_%s.pdf", $company->EntityCd), "D");


// End of file: rekap_hutang.pdf.php
