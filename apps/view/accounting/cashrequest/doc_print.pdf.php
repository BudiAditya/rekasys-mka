<?php
/** @var CashRequest[] $report */
//print(getcwd());
$pdf = new TabularPdf("P", "mm", "letter");
$pdf->SetAutoPageBreak(true,10);


$pdf->SetMargins(5, 5);
$pdf->SetColumns(
	array("No", "Kode Perk", "Rencana Penggunaan Uang", "Jumlah"),
	array(9, 28, 0, 35)
);
$widths = $pdf->GetWidths();
$pdf->SetDefaultAlignments(array("R", "L", "L", "R"));

$pdf->Open();
$pdf->AddFont("Tahoma");
$pdf->AddFont("Tahoma", "B");
$fontFamily = "Tahoma";

foreach($report as $idx => $rs) {
	$pdf->AddPage();

	$pdf->SetFont($fontFamily, "B", 17);
	$pdf->Cell(0, 8, "NOTA PERMINTAAN KAS PROYEK", 0, 0, "C");
	$pdf->Ln();

	$pdf->SetFont($fontFamily, "", 15);
	$pdf->Cell(0, 6, $rs->DocumentNo, 0, 0, "C");
	$pdf->Ln(12);

	$pdf->SetFont($fontFamily, "", 13);
	$pdf->Cell(0, 6, "Kepada Yth :", 0, 0, "L");
	$pdf->Ln();
	$pdf->Cell(0, 6, "Direktur ".$company->CompanyName, 0, 0, "L");
	$pdf->Ln();
	$pdf->Cell(205, 6, "up. Kepala Bagian Keuangan dan Akuntansi", 0, 0, "L");
	$pdf->Ln(10);

	$amount = 0;
	foreach ($rs->Details as $result) {
		$amount = $amount + $result->Amount;
	}

	$pdf->SetFont($fontFamily,'',11);
	$msg = sprintf("Untuk pembiayaan: %s mohon kiranya disediakan uang sebanyak Rp. %s (terbilang: %s rupiah) dengan rencana penggunaan seperti berikut:", $rs->Objective, number_format($amount, 2, ",", "."), terbilang($amount));
	$rowUsed = $pdf->DetectRowsUsed($msg, 0);
	$pdf->MultiCell(0, 6, $msg, 0, "J");
	if ($rowUsed == 2) {
		$pdf->Ln(10);
	} else {
		$pdf->Ln(5);
	}

	$pdf->SetFont($fontFamily, "B", 11);
	$pdf->RowHeader(5, array("TRBL", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C"));
	$pdf->SetFont($fontFamily, "", 11);

	// Ambil koordinat Y untuk bikin garis nantinya
	$x = $pdf->GetX();
	$y = $pdf->GetY();
	$i = 0;
	foreach ($rs->Details as $row) {
		$i++;
		$data = array();
		$data[] = $i . ".";
		$data[] = $row->AccountNo;
		$data[] = $row->Note;
		$data[] = number_format($row->Amount, 2, ",", ".");

		$pdf->RowData($data, 5);
	}

	// Bikin Garis untuk detail (asumsi total data adalah 9 baris dengan width 10 mm)
	$pdf->Line($x, $y, $x, $y + 100);
	$width = 0;
	for ($i = 0; $i < count($widths); $i++) {
		$width += $widths[$i];
		$pdf->Line($x + $width, $y, $x + $width, $y + 100);
	}
	$pdf->SetY($y + 100);

	$pdf->Cell($widths[0] + $widths[1] + $widths[2], 5, "Jumlah ", "TRBL", 0, "R");
	$pdf->Cell($widths[3], 5, number_format($amount,2,",","."), "TRB", 0, "R");
	$pdf->Ln(8);

	$pdf->SetFont($fontFamily, "", 11);
	$pdf->Cell(0, 5, "Mohon kiranya uang tersebut dapat diterima selambat-lambatnya tanggal: " . long_date($rs->FormatEtaDate(SQL_DATEONLY)), 0, 0, "L");
	$pdf->Ln(8);
	$pdf->Cell(0, 5, "Manado, " . long_date(date("Y-m-d", $rs->Date)), 0, 0, "R");
	$pdf->Ln(8);

	$pdf->SetX(10, true);
	$pdf->Cell(50, 5, "Disetujui oleh,", 0, 0, "C");
	$pdf->SetX(-60, true);
	$pdf->Cell(50, 5, "Disiapkan oleh,", 0, 0, "C");
	$pdf->Ln(20);

	$pdf->SetX(10, true);
	$pdf->Cell(50, 5, "Koordinator Pelaksana", "T", 0, "C");
	$pdf->SetX(-60, true);
	$pdf->Cell(50, 5, "Administrasi", "T", 0, "C");
	$pdf->Ln(10);

	$pdf->SetX(10, true);
	$pdf->Cell(50, 5, "Disetujui oleh,", 0, 0, "C");
	$pdf->SetX(-60, true);
	$pdf->Cell(50, 5, "Diverifikasi oleh,", 0, 0, "C");
	$pdf->Ln(20);

	$pdf->SetX(10, true);
	$pdf->Cell(50, 5, "Direktur", "T", 0, "C");
	$pdf->SetX(-60, true);
	$pdf->Cell(50, 5, "Kabag. Keu. dan Akuntansi", "T", 0, "C");
	$pdf->Ln();
}
$pdf->Output("print-npkp.pdf", "D");
// EoF: doc_print.pdf.php