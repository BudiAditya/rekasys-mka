<?php
/** @var $start int */ /** @var $end int */ /** @var $debtorId int */ /** @var $debtors Debtor[] */ /** @var $report null|ReaderBase */ /** @var $saldoAwal float */
foreach ($debtors as $debtor) {
	if ($debtor->Id == $debtorId) {
		$selectedDebtor = $debtor;
		break;
	}
}

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("kartu-piutang_%s.xlsx", $selectedDebtor->DebtorCd);
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("kartu-piutang_%s.xls", $selectedDebtor->DebtorCd);
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$excel->getProperties()->setCreator("Reka System (c)")->setTitle("Kartu Piutang Debtor")->setCompany("Rekasys Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Kartu Piutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Kartu Piutang: %s - %s", $selectedDebtor->DebtorCd, $selectedDebtor->DebtorName));
$sheet->mergeCells("A1:G1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));
$sheet->mergeCells("A2:G2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));

$sheet->setCellValue("A4", "No.");
$sheet->setCellValue("B4", "Tgl. Dokumen");
$sheet->setCellValue("C4", "No. Dokumen");
$sheet->setCellValue("D4", "Keterangan");
$sheet->setCellValue("E4", "Debit");
$sheet->setCellValue("F4", "Kredit");
$sheet->setCellValue("G4", "Saldo");
$sheet->getStyle("A4:G4")->applyFromArray(array(
	"font" => array("bold" => true)
	, "alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	)
));

// Tulis Data
// Saldo Awal
$sheet->setCellValue("A5", "Saldo Awal per tanggal " . date(HUMAN_DATE, $start) . ": ");
$sheet->mergeCells("A5:D5");
$sheet->getStyle("A5")->applyFromArray(array(
	"font" => array("bold" => true)
	, "alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
	)
));
$sheet->setCellValue("E5", $saldoAwal > 0 ? $saldoAwal : "");
$sheet->setCellValue("F5", $saldoAwal < 0 ? $saldoAwal * -1 : "");
$sheet->setCellValue("G5", "=E5-F5");
// Data Transaksi
$currentRow = 5;
$counter = 0;
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
	$currentRow++;
	$counter++;

	$sums["debit"] += $debit;
	$sums["credit"] += $credit;
	if ($prevDate != $date) {
		$prevDate = $date;
	} else {
		$date = null;
	}

	$sheet->setCellValue("A" . $currentRow, $counter);
	$sheet->setCellValue("B" . $currentRow, $date == null ? "" : date(HUMAN_DATE, $date));
	$sheet->setCellValue("C" . $currentRow, $row["doc_no"]);
	$sheet->setCellValue("D" . $currentRow, $row["description"]);
	$sheet->setCellValue("E" . $currentRow, $debit != 0 ? $debit : "");
	$sheet->setCellValue("F" . $currentRow, $credit != 0 ? $credit : "");
	$formula = sprintf("=G%d+E%d-F%d", $currentRow - 1, $currentRow, $currentRow);
	$sheet->setCellValue("G" . $currentRow, $formula);
}
// Sums
$currentRow++;
$sheet->setCellValue("A" . $currentRow, "TOTAL : ");
$sheet->mergeCells("A$currentRow:D$currentRow");
$sheet->getStyle("A" . $currentRow)->applyFromArray(array(
	"font" => array("bold" => true)
	, "alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
	)
));
$flagCyclic = $currentRow == 6;
$sheet->setCellValue("E" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(E5:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(F5:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, "=G" . ($currentRow - 1));

// Styling
// Numeric Format: #,##0.00
$range = "E5:G" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"numberformat" => array("code" => "#,##0.00")
));
// Borders
$range = "A4:G" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"borders" => array(
		"allborders" => array(
			"style" => PHPExcel_Style_Border::BORDER_THIN
			, "color" => array("argb" => "FF000000")
		)
	)
));
// Auto Widths
for ($i = 0; $i <= 7; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Hmm Reset Pointer
$sheet->getStyle("A5");

// Flush to client
foreach ($headers as $header) {
	header($header);
}
$writer->save("php://output");

// Garbage Collector
$excel->disconnectWorksheets();
unset($excel);
ob_flush();
exit();

// End of file: kartu_piutang.excel.php
