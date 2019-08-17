<?php
/** @var $start int */ /** @var $end int */ /** @var $docTypes DocType[] */ /** @var $showNo bool */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */ /** @var $projectId int */ /** @var $projectList Project[] */

$columns = array("Tgl", "No. Voucher", "Company", "Uraian", "Akun", "Jumlah", "Akun", "Jumlah");
$widths = array(8, 33, 12, 0, 22, 28, 22, 28);
$buff = array();
foreach ($docTypes as $docType) {
	if (in_array($docType->Id, $docIds)) {
		$buff[] = strtoupper($docType->DocCode);
	}
}
$selectedProject = null;
foreach ($projectList as $project) {
    if ($project->Id == $projectId) {
        $selectedProject = $project;
        break;
    }
}
// OK mari kita buat PDF nya
$pdf = new AccountingJournalPdf($orientation);
$pdf->SetCompany($company, $selectedProject);
switch ($status) {
	case 1:
		$pdf->SetSubTitle(implode(", ", $buff) . " (BELUM APPROVED)");
		break;
	case 2:
		$pdf->SetSubTitle(implode(", ", $buff) . " (SUDAH APPROVED)");
		break;
	case 3:
		$pdf->SetSubTitle(implode(", ", $buff) . " (VERIFIED)");
		break;
	case 4:
		$pdf->SetSubTitle(implode(", ", $buff) . " (POSTED)");
		break;
	default:
		$pdf->SetSubTitle(implode(", ", $buff) . " (SEMUA)");
		break;
}
$pdf->SetPeriod(sprintf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "B", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
$pdf->SetColumns($columns, $widths);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments(array("C", "L", "L", "L", "L", "R", "L", "R"));
$pdf->SetDefaultBorders(array("RL", "R", "R", "R", "R", "R", "R", "R"));

$pdf->Open();
$pdf->AddPage();

// Berhubung judul kolomnya ada yang merge 2 baris ga bisa curang deh...
$pdf->SetFont("Arial", "B", 10);
$pdf->Cell($widths[0], 12, $columns[0], "TRBL", 0, "C");
$pdf->Cell($widths[1], 12, $columns[1], "TRB", 0, "C");
$pdf->Cell($widths[2], 12, $columns[2], "TRB", 0, "C");
$pdf->Cell($widths[3], 12, $columns[3], "TRB", 0, "C");
$offsetX = $pdf->GetX();
$pdf->Cell($widths[4] + $widths[5], 6, "DEBET", "TRB", 0, "C");
$pdf->Cell($widths[6] + $widths[7], 6, "KREDIT", "TRB", 0, "C");
$pdf->Ln();
$pdf->SetX($offsetX);
$pdf->Cell($widths[4], 6, $columns[4], "RB", 0, "C");
$pdf->Cell($widths[5], 6, $columns[5], "RB", 0, "C");
$pdf->Cell($widths[6], 6, $columns[6], "RB", 0, "C");
$pdf->Cell($widths[7], 6, $columns[7], "RB", 0, "C");
$pdf->Ln();

$pdf->SetFont("Arial", "", 9);
$prevDate = null;
$prevVoucherNo = null;
$prevSbu = null;

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;
$sums = 0;
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

	if ($prevSbu != $row["entity_cd"]) {
		$prevSbu = $row["entity_cd"];
		$flagSbu = true;
	} else {
		$flagSbu = false;
	}

	$sums += $row["amount"];

	$data = array();
	$data[] = $flagDate ? date("d", $prevDate) : "";
	$data[] = $flagVoucherNo ? $prevVoucherNo : "";
	$data[] = $flagSbu ? $prevSbu : "";
	$data[] = $row["note"];
	$data[] = $showNo ? $row["acc_no_debit"] : $row["acc_debit"];
	$data[] = number_format($row["amount"], 2);
	$data[] = $showNo ? $row["acc_no_credit"] : $row["acc_credit"];
	$data[] = number_format($row["amount"], 2);

	$pdf->RowData($data, 5);
}

// Bikin garis tutup
$width = array_sum($widths);
$pdf->Cell($width, 0, "", "B");
$pdf->Ln();

// Bikin total
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell($widths[0] + $widths[1] + $widths[2] + $widths[3], 5, "GRAND TOTAL :", "RBL", 0, "R");
$pdf->Cell($widths[4], 5, "", "RB");
$pdf->Cell($widths[5], 5, number_format($sums, 2), "RB", 0, "R");
$pdf->Cell($widths[6], 5, "", "RB");
$pdf->Cell($widths[7], 5, number_format($sums, 2), "RB", 0, "R");

$pdf->Output("laporan_jurnal_" . implode(", ", $buff) . ".pdf", "D");
// EoF: journal.pdf.php