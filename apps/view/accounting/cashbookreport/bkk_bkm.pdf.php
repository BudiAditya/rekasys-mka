<?php
/** @var $obal OpeningBalance */ /** @var $obalTransaction array */
/** @var $accountId int */ /** @var $accounts Coa[] */ /** @var $start int */ /** @var $end int */ /** @var $status string */ /** @var $report ReaderBase */
/** @var $output string */ /** @var $orientation int */ /** @var $company Company */

$columns = array("Tgl", "No. Voucher", "Kontra Pos", "Uraian", "Project", "Dept", "Debet", "Kredit", "Saldo");
$widths = array(8, 33, 22, 0, 10, 10, 30, 30, 30);

// OK mari kita buat PDF nya (selectedAccountnya... harus dicari manual)
/** @var $selectedAccount Coa|null */
$selectedAccount = null;
foreach ($accounts as $account) {
	if ($account->Id == $accountId) {
		$selectedAccount = $account;
		break;
	}
}
$pdf = new BkkBkmReportPdf($orientation, "mm", "f4");
$pdf->SetHeaderData($company, $selectedAccount, sprintf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetColumns($columns, $widths);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments(array("C", "L", "L", "L", "C", "C", "R", "R", "R"));
$pdf->SetDefaultBorders(array("RL", "R", "R", "R", "R", "R", "R", "R", "R"));

$pdf->Open();
$pdf->AddPage();

// Tulis Header...
$pdf->SetFont("Arial", "B", 10);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C", "C"));

// Tulis Data...
$pdf->SetFont("Arial", "", 9);
$prevDate = null;
$prevVoucherNo = null;

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;

$counter = 0;
$subTotalDebit = 0;
$subTotalCredit = 0;
$totalDebit = 0;
$totalCredit = 0;
$saldo = $obal == null ? 0 : $obalTransaction["saldo"];

$subTotalWidth = $widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4] + $widths[5];

// Tulis Saldo Awal
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($subTotalWidth, 5, "Saldo Awal per tgl. " . date(HUMAN_DATE, $start), "RBL", 0, "R");
if ($obal != null) {
	$pdf->Cell($widths[6], 5, $selectedAccount->DcSaldo == "D" ? number_format($saldo, 2) : "", "RB", 0, "R");
	$pdf->Cell($widths[7], 5, $selectedAccount->DcSaldo == "K" ? number_format($saldo, 2) : "", "RB", 0, "R");
} else {
	$pdf->Cell($widths[6], 5, "-", "RB", 0, "R");
	$pdf->Cell($widths[7], 5, "-", "RB", 0, "R");
}
$pdf->Cell($widths[7], 5, "", "RB");
$pdf->Ln();
$pdf->SetFont("Arial", "", 9);

foreach ($report as $master) {
	foreach ($master["details"] as $row) {
		$counter++;
		if ($prevDate != $row["voucher_date"]) {
			if ($counter > 1) {
				// Sudah pernah ada data yang ditulis
				$totalDebit += $subTotalDebit;
				$totalCredit += $subTotalCredit;

				$pdf->SetFont("Arial", "B", 9);
				$pdf->Cell($subTotalWidth, 5, "Sub Total " . date("d F", $prevDate) . " :", "TRBL", 0, "R");
				$pdf->Cell($widths[6], 5, number_format($subTotalDebit, 2), "TRB", 0, "R");
				$pdf->Cell($widths[7], 5, number_format($subTotalCredit, 2), "TRB", 0, "R");
				$pdf->Cell($widths[8], 5, number_format($saldo, 2), "TRB", 0, "R");
				$pdf->Ln();
				$pdf->SetFont("Arial", "", 9);
			}

			$prevDate = $row["voucher_date"];
			$flagDate = true;

			$subTotalDebit = 0;
			$subTotalCredit = 0;
		} else {
			$flagDate = false;
		}

		if ($prevVoucherNo != $row["doc_no"]) {
			$prevVoucherNo = $row["doc_no"];
			$flagVoucherNo = true;
		} else {
			$flagVoucherNo = false;
		}

		$subTotalDebit += $row["debit"];
		$subTotalCredit += $row["credit"];
		if ($selectedAccount->DcSaldo == "D") {
			$saldo += ($row["debit"] - $row["credit"]);
		} else {
			$saldo += ($row["credit"] - $row["debit"]);
		}

		$data = array();
		$data[] = $flagDate ? date("d", $prevDate) : "";
		$data[] = $flagVoucherNo ? $prevVoucherNo : "";
		$data[] = $row["opposite_no"];
		$data[] = $row["note"];
		$data[] = $row["project_cd"];
		$data[] = $row["dept_code"];
		$data[] = number_format($row["debit"], 2);
		$data[] = number_format($row["credit"], 2);
		$data[] = number_format($saldo, 2);
		$pdf->RowData($data, 5);
	}
}

// Sub Total yang terakhir yang terlupakan
if ($counter > 1) {
	// Sudah pernah ada data yang ditulis
	$totalDebit += $subTotalDebit;
	$totalCredit += $subTotalCredit;

	$pdf->SetFont("Arial", "B", 9);
	$pdf->Cell($subTotalWidth, 5, "Sub Total " . date("d F", $prevDate) . " :", "TRBL", 0, "R");
	$pdf->Cell($widths[6], 5, number_format($subTotalDebit, 2), "TRB", 0, "R");
	$pdf->Cell($widths[7], 5, number_format($subTotalCredit, 2), "TRB", 0, "R");
	$pdf->Cell($widths[8], 5, number_format($saldo, 2), "TRB", 0, "R");
	$pdf->Ln();
	$pdf->SetFont("Arial", "", 9);
}

// GRAND TOTAL
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($subTotalWidth, 5, "GRAND TOTAL :", "RBL", 0, "R");
$pdf->Cell($widths[6], 5, number_format($totalDebit, 2), "RB", 0, "R");
$pdf->Cell($widths[7], 5, number_format($totalCredit, 2), "RB", 0, "R");
$pdf->Cell($widths[8], 5, number_format($saldo, 2), "RB", 0, "R");

$pdf->Output(sprintf("bkk-bkm_%s (%s).pdf", $selectedAccount->AccName, $selectedAccount->AccNo), "D");

// End of File: bkk_bkm.pdf.php
