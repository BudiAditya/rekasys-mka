<?php
/** @var $accountId int */ /** @var $accounts array */ /** @var $start int */ /** @var $end int */ /** @var $openingBalance null|OpeningBalance */
/** @var $transaction null|array */ /** @var $report null|ReaderBase */ /** @var $output string */ /** @var $company Company */
$haveData = $openingBalance != null;

$columns = array("Tgl", "No. Voucher", "Uraian", "Project", "Dept", "Debet", "Kredit", "Saldo");
$widths = array(8, 33, 0, 12, 12, 25, 25, 25);

// OK mari kita buat PDF nya (selectedAccountnya... harus dicari manual)
/** @var $selectedAccount Coa */
$selectedAccount = null;
foreach ($accounts as $row) {
	/** @var $account Coa */
	foreach ($row["SubAccounts"] as $account) {
		if ($account->Id == $accountId) {
			$selectedAccount = $account;
			break;
		}
	}
}
$pdf = new CashFlowReportPdf();
$pdf->SetHeaderData($company, $selectedAccount, sprintf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetColumns($columns, $widths);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments(array("C", "L", "L", "C", "C", "R", "R", "R"));
$pdf->SetDefaultBorders(array("RL", "R", "R", "R", "R", "R", "R", "R"));

$pdf->Open();
$pdf->AddPage();

// Tulis Header...
$pdf->SetFont("Arial", "B", 10);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C", "C"));

// Tulis Data...
$pdf->SetFont("Arial", "", 9);
$prevDate = null;
$prevVoucherNo = null;

// OK tulis saldo awal dahulu
$data = array();
$data[] = "01";
$data[] = "";
$data[] = "Saldo Awal " . date(HUMAN_DATE, $start);
$data[] = "";
$data[] = "";
$data[] = number_format(($haveData && $openingBalance->AccountDcSaldo == "D") ? $transaction["saldo"] : 0, 2);
$data[] = number_format(($haveData && $openingBalance->AccountDcSaldo == "K") ? $transaction["saldo"] : 0, 2);
$data[] = number_format($haveData ? $transaction["saldo"] : 0, 2);
$pdf->RowData($data, 5);

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;
$saldo = $transaction["saldo"];

$subTotalDebit = 0;
$subTotalCredit = 0;
$totalDebit = 0;
$totalCredit = 0;
$subTotalWidth = $widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4];
while ($row = $report->FetchAssoc()) {
	// Convert datetime jadi native format
	$row["voucher_date"] = strtotime($row["voucher_date"]);

	if ($prevDate != $row["voucher_date"]) {
		if ($prevDate != null) {
			// OK sudah ganti baris kita harus bikin subTotal dahulu
			$pdf->SetFont("Arial", "B", 9);
			$pdf->Cell($subTotalWidth, 5, "Sub Total " . date(HUMAN_DATE, $prevDate) . " :", "TRBL", 0, "R");
			$pdf->Cell($widths[5], 5, number_format($subTotalDebit, 2), "TRB", 0, "R");
			$pdf->Cell($widths[6], 5, number_format($subTotalCredit, 2), "TRB", 0, "R");
			$pdf->Cell($widths[7], 5, "", "TRB", 0, "R");
			$pdf->Ln();
			$pdf->SetFont("Arial", "", 9);
			$totalDebit += $subTotalDebit;
			$totalCredit += $subTotalCredit;
			$subTotalDebit = 0;
			$subTotalCredit = 0;
		}
		$prevDate = $row["voucher_date"];
		$flagDate = true;
	} else {
		$flagDate = false;
	}

	if ($prevVoucherNo != $row["doc_no"]) {
		$prevVoucherNo = $row["doc_no"];
		$flagVoucherNo = true;
	} else {
		$flagVoucherNo = false;
	}

	$debit = $row["acc_debit_id"] == $accountId ? $row["amount"] : 0;
	$credit = $row["acc_credit_id"] == $accountId ? $row["amount"] : 0;
	$saldo = $saldo + $debit - $credit;
	$subTotalDebit += $debit;
	$subTotalCredit += $credit;

	$data = array();
	$data[] = $flagDate ? date("d", $prevDate) : "";
	$data[] = $flagVoucherNo ? $prevVoucherNo : "";
	$data[] = $row["note"];
	$data[] = $row["project_cd"];
	$data[] = $row["dept_code"];
	$data[] = number_format($debit, 2);
	$data[] = number_format($credit, 2);
	$data[] = number_format($saldo, 2);
	$pdf->RowData($data, 5);
}

if ($prevDate != null) {
	// OK sudah ganti baris kita harus bikin subTotal dahulu
	$pdf->SetFont("Arial", "B", 9);
	$pdf->Cell($subTotalWidth, 5, "Sub Total " . date(HUMAN_DATE, $prevDate) . " :", "TRBL", 0, "R");
	$pdf->Cell($widths[5], 5, number_format($subTotalDebit, 2), "TRB", 0, "R");
	$pdf->Cell($widths[6], 5, number_format($subTotalCredit, 2), "TRB", 0, "R");
	$pdf->Cell($widths[7], 5, "", "TRB", 0, "R");
	$pdf->Ln();
	$pdf->SetFont("Arial", "", 9);
	$totalDebit += $subTotalDebit;
	$totalCredit += $subTotalCredit;
	$subTotalDebit = 0;
	$subTotalCredit = 0;
}

// GRAND TOTAL
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($subTotalWidth, 5, "GRAND TOTAL :", "RBL", 0, "R");
$pdf->Cell($widths[5], 5, number_format($totalDebit, 2), "RB", 0, "R");
$pdf->Cell($widths[6], 5, number_format($totalCredit, 2), "RB", 0, "R");
$pdf->Cell($widths[7], 5, number_format($saldo, 2), "RB", 0, "R");
$pdf->Ln();
$pdf->FontStyle = "";
$totalDebit += $subTotalDebit;
$totalCredit += $subTotalCredit;
$subTotalDebit = 0;
$subTotalCredit = 0;

$pdf->Output("laporan_cash_flow.pdf", "D");

// End of File: rpt_detail.pdf.php
