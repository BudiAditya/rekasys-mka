<?php
$columns = array("No. Akun", "Nama Akun", "Debet", "Kredit", "Debet", "Kredit");
$widths = array(25, 0, 30, 30, 30, 30);

// Buat PDF nya
$pdf = new CashbookRecapInReportPdf();

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "B", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetHeaderData($company, $monthNames[$month], $year);
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
$offsetX = $pdf->GetX();
$pdf->Cell($widths[2] + $widths[3], 6, sprintf("Mutasi %s %s", $monthNames[$month], $year), "TRB", 0, "C");
$pdf->Cell($widths[4] + $widths[5], 6, sprintf("Jumlah %s %s", $monthNames[$month], $year), "TRB", 0, "C");
$pdf->Ln();
$pdf->SetX($offsetX);
$pdf->Cell($widths[2], 6, $columns[2], "RB", 0, "C");
$pdf->Cell($widths[3], 6, $columns[3], "RB", 0, "C");
$pdf->Cell($widths[4], 6, $columns[4], "RB", 0, "C");
$pdf->Cell($widths[5], 6, $columns[5], "RB", 0, "C");
$pdf->Ln();

// OK mari kita tulis2 datanya
$pdf->SetFont("Arial", "", 9);
$sumDebit = 0;
$sumCredit = 0;
$sumAllDebit = 0;
$sumAllCredit = 0;
while($row = $report->FetchAssoc()) {
	$sumDebit += $row["total_debit"];
	$sumCredit += $row["total_credit"];
	$sumAllDebit += $row["total_debit"] + $row["total_debit_prev"];
	$sumAllCredit += $row["total_credit"] + $row["total_credit_prev"];

	$data = array();
	$data[] = $row["acc_no"];
	$data[] = $row["acc_name"];
	$data[] = number_format($row["total_debit"], 2);
	$data[] = number_format($row["total_credit"], 2);
	$data[] = number_format($row["total_debit"] + $row["total_debit_prev"], 2);
	$data[] = number_format($row["total_credit"] + $row["total_credit_prev"], 2);
	$pdf->RowData($data, 5);
}

// TOTAL
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($widths[0] + $widths[1], 5, "TOTAL : ", "TRBL", 0, "R");
$pdf->Cell($widths[2], 5, number_format($sumDebit, 2), "TRB", 0, "R");
$pdf->Cell($widths[3], 5, number_format($sumCredit, 2), "TRB", 0, "R");
$pdf->Cell($widths[4], 5, number_format($sumAllDebit, 2), "TRB", 0, "R");
$pdf->Cell($widths[5], 5, number_format($sumAllCredit, 2), "TRB", 0, "R");

$pdf->Output("rekap_bank_cash_in.pdf", "D");

// End of File: recap_in.pdf.php
