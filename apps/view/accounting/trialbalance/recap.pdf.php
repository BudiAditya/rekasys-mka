<?php
/** @var $company Company */ /** @var $monthNames string[] */ /** @var $parentAccounts Coa[] */ /** @var $parentId int */ /** @var $month int */ /** @var $year int */ /** @var $report null|ReaderBase */

$selectedAccount = null;
foreach ($parentAccounts as $account) {
	if ($account->Id == $parentId) {
		$selectedAccount = $account;
		break;
	}
}
$columns = array("No. Akun", "Nama Akun", "s.d. Bulan Lalu", "Debet", "Kredit", "s.d. Bulan Ini");
$widths = array(25, 0, 30, 30, 30, 30);

// Buat PDF nya
$pdf = new BukuTambahanRecapReportPdf();

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "B", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetHeaderData($company, $monthNames[$month], $year, $selectedAccount);
$pdf->SetColumns($columns, $widths);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments(array("C", "L", "R", "R", "R", "R"));
$pdf->SetDefaultBorders(array("RL", "R", "R", "R", "R", "R"));

// Begin new page
$pdf->Open();
$pdf->AddPage();

// Berhubung judul kolomnya ada yang merge 2 baris ga bisa curang deh...
$pdf->SetFont("Arial", "B", 10);
$pdf->Cell($widths[0], 12, $columns[0], "TRBL", 0, "C");
$pdf->Cell($widths[1], 12, $columns[1], "TRB", 0, "C");
$pdf->Cell($widths[2], 12, $columns[2], "TRB", 0, "C");
$offsetX = $pdf->GetX();
$pdf->Cell($widths[3] + $widths[4], 6, sprintf("Mutasi %s %s", $monthNames[$month], $year), "TRB", 0, "C");
$pdf->Cell($widths[5], 12, $columns[5], "TRB", 0, "C");
$pdf->Ln(6);
$pdf->SetX($offsetX);
$pdf->Cell($widths[3], 6, $columns[3], "RB", 0, "C");
$pdf->Cell($widths[4], 6, $columns[4], "RB", 0, "C");
$pdf->Ln();

// OK mari kita tulis2 datanya
$pdf->SetFont("Arial", "", 9);
$sumDebit = 0;
$sumCredit = 0;
$sumPrevSaldo = 0;
$sumSaldo = 0;
while($row = $report->FetchAssoc()) {
	$posisiSaldo = $row["posisi_saldo"];
	$sumDebit += $row["total_debit"];
	$sumCredit += $row["total_credit"];

	if ($posisiSaldo == "DK") {
		$prevSaldo = ($row["bal_debit_amt"] - $row["bal_credit_amt"]) + ($row["total_debit_prev"] - $row["total_credit_prev"]);
		$saldo = $row["total_debit"] - $row["total_credit"];
	} else  if($posisiSaldo == "KD") {
		$prevSaldo = ($row["bal_credit_amt"] - $row["bal_debit_amt"]) + ($row["total_credit_prev"] - $row["total_debit_prev"]);
		$saldo = $row["total_credit"] - $row["total_debit"];
	} else {
		throw new Exception("Invalid posisi_saldo! CODE: " . $posisiSaldo);
	}

	$sumPrevSaldo += $prevSaldo;
	$sumSaldo += $prevSaldo + $saldo;

	$data = array();
	$data[] = $row["acc_no"];
	$data[] = $row["acc_name"];
	$data[] = number_format($prevSaldo, 2);
	$data[] = number_format($row["total_debit"], 2);
	$data[] = number_format($row["total_credit"], 2);
	$data[] = number_format($prevSaldo + $saldo, 2);
	$pdf->RowData($data, 5);
}

// TOTAL
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($widths[0] + $widths[1], 5, "TOTAL : ", "TRBL", 0, "R");
$pdf->Cell($widths[2], 5, number_format($sumPrevSaldo, 2), "TRB", 0, "R");
$pdf->Cell($widths[3], 5, number_format($sumDebit, 2), "TRB", 0, "R");
$pdf->Cell($widths[4], 5, number_format($sumCredit, 2), "TRB", 0, "R");
$pdf->Cell($widths[5], 5, number_format($sumSaldo, 2), "TRB", 0, "R");

$pdf->Output(sprintf("buku_tambahan_%s (%s).pdf", $selectedAccount->AccName, $selectedAccount->AccNo), "D");

// End of File: recap_in.pdf.php
