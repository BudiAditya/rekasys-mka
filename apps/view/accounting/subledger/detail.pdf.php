<?php
/** @var $accountId int */ /** @var $accounts array */ /** @var $start int */ /** @var $end int */ /** @var $openingBalance null|OpeningBalance */
/** @var int $status */ /** @var string $statusName */ /** @var $projectList Project[] */ /** @var $projectId int */
/** @var $transaction null|array */ /** @var $report null|ReaderBase */ /** @var $output string */ /** @var $company Company */
$haveData = $openingBalance != null;

$columns = array("Tgl", "No. Voucher", "Uraian", "Company", "Dept", "Debet", "Kredit");
$widths = array(8, 33, 0, 12, 12, 30, 30);

// OK mari kita buat PDF nya (selectedAccountnya... harus dicari manual)
/** @var $selectedAccount Coa|null */
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
$selectedProject = null;
foreach ($projectList as $project) {
    if ($project->Id == $projectId) {
        $selectedProject = $project;
        break;
    }
}

$pdf = new BukuTambahanReportPdf();
$pdf->SetHeaderData($company, $selectedAccount, sprintf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)),$selectedProject, $statusName);

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetColumns($columns, $widths);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments(array("C", "L", "L", "C", "C", "R", "R"));
$pdf->SetDefaultBorders(array("RL", "R", "R", "R", "R", "R", "R"));

$pdf->Open();
$pdf->AddPage();

// Tulis Header...
$pdf->SetFont("Arial", "B", 10);
$pdf->RowHeader(6, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C"));

// Tulis Data...
$pdf->SetFont("Arial", "", 9);
$prevDate = null;
$prevVoucherNo = null;

// Saldo awal
$data = array();
$data[] = "01";
$data[] = "";
$data[] = "Saldo Awal " . date(HUMAN_DATE, $start);
$data[] = "";
$data[] = "";
$data[] = number_format(($haveData && $openingBalance->GetCoa()->DcSaldo == "D") ? $transaction["saldo"] : 0, 2);
$data[] = number_format(($haveData && $openingBalance->GetCoa()->DcSaldo == "K") ? $transaction["saldo"] : 0, 2);
$pdf->RowData($data, 5);

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;

$totalDebit = ($haveData && $openingBalance->GetCoa()->DcSaldo == "D") ? $transaction["saldo"] : 0;
$totalCredit = ($haveData && $openingBalance->GetCoa()->DcSaldo == "K") ? $transaction["saldo"] : 0;
$subTotalWidth = $widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4];
while ($row = $report->FetchAssoc()) {
	// Convert datetime jadi native format
	$row["voucher_date"] = strtotime($row["voucher_date"]);

	if ($prevDate != $row["voucher_date"]) {
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
	$totalDebit += $debit;
	$totalCredit += $credit;

	$data = array();
	$data[] = $flagDate ? date("d", $prevDate) : "";
	$data[] = $flagVoucherNo ? $prevVoucherNo : "";
	$data[] = $row["note"];
	$data[] = $row["entity_cd"];
	$data[] = $row["dept_code"];
	$data[] = number_format($debit, 2);
	$data[] = number_format($credit, 2);
	$pdf->RowData($data, 5);
}

// GRAND TOTAL
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($subTotalWidth, 5, "GRAND TOTAL :", "TRL", 0, "R");
$pdf->Cell($widths[5], 5, number_format($totalDebit, 2), "TR", 0, "R");
$pdf->Cell($widths[6], 5, number_format($totalCredit, 2), "TR", 0, "R");
$pdf->Ln();
$pdf->Cell($subTotalWidth, 5, "SALDO AKHIR :", "TRBL", 0, "R");
$pdf->Cell($widths[5], 5, $selectedAccount->DcSaldo == "D" ? number_format($totalDebit - $totalCredit, 2) : "", "TRB", 0, "R");
$pdf->Cell($widths[6], 5, $selectedAccount->DcSaldo == "K" ? number_format($totalCredit - $totalDebit, 2) : "", "TRB", 0, "R");

$pdf->Output(sprintf("buku-tambahan_%s (%s).pdf", $selectedAccount->AccName, $selectedAccount->AccNo), "D");

// End of File: detail.pdf.php
