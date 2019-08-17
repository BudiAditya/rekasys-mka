<?php
/** @var $month int */ /** @var $year int */ /** @var $docTypes DocType[] */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */ /** @var $projectId int */ /** @var $projectList Project[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */ /** @var $monthNames array */

$columns = array("No. Akun", "Nama Akun", "Debet", "Kredit", "Debet", "Kredit");
$widths = array(25, 0, 28, 28, 33, 33);
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

// Buat PDF nya
$pdf = new AccountingJournalRecapPdf($orientation);

// Setting PDF
$pdf->AliasNbPages();
$pdf->SetFont("Arial", "B", 9);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetMargins(5, 5);
// Custom method from TabularPdf
switch ($status) {
	case 1:
		$suffix = implode(", ", $buff) . " (BELUM APPROVED)";
		break;
	case 2:
		$suffix = implode(", ", $buff) . " (SUDAH APPROVED)";
		break;
	case 3:
		$suffix = implode(", ", $buff) . " (VERIFIED)";
		break;
	case 4:
		$suffix = implode(", ", $buff) . " (POSTED)";
		break;
	default:
		$suffix = implode(", ", $buff) . " (SEMUA)";
		break;
}
$pdf->SetHeaderData($company, $monthNames[$month], $year, "Rekap Jurnal: " . $suffix,$selectedProject);
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

$pdf->Output("rekap_jurnal_" . implode(", ", $buff) . ".pdf", "D");

// EoF: recap.pdf.php