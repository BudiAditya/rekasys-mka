<?php
/** @var $start int */ /** @var $end int */
/** @var $employeeId int */ /** @var $employees Employee[] */
/** @var $report null|ReaderBase */ /** @var $saldoAwal float */
$selectedEmployee = null;
foreach ($employees as $employee) {
	if ($employee->Id == $employeeId) {
		$selectedEmployee = $employee;
	}
}

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();

// Excel MetaData
$excel->getProperties()->setCreator("Megamas System (c)")->setTitle("Kartu Piutang Karyawan")->setCompany("Megamas Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Kartu Piutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Kartu Piutang: %s - %s", $selectedEmployee->Name, $selectedEmployee->Nik));
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
	"font" => array("bold" => true),
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	)
));

// Tulis Data
// Saldo Awal
$sheet->setCellValue("A5", "Saldo Awal per tanggal " . date(HUMAN_DATE, $start) . ": ");
$sheet->mergeCells("A5:D5");
$sheet->getStyle("A5")->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array(
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
	"debit" => 0,
	"credit" => 0
);
while ($row = $report->FetchAssoc()) {
	$currentRow++;
	$counter++;
	$date = strtotime($row["voucher_date"]);
	if ($prevDate != $date) {
		$prevDate = $date;
	} else {
		$date = null;
	}
	switch ($row["doc_type_id"]) {
		case 2:		// BKK
			$debit = $row["amount"];
			$credit = "";
			$sums["debit"] += $row["amount"];
			break;
		case 3:		// BKM
			$debit = "";
			$credit = $row["amount"];
			$sums["credit"] += $row["amount"];
			break;
		default:
			$debit = "";
			$credit = "";
			break;
	}

	$sheet->setCellValue("A" . $currentRow, $counter);
	$sheet->setCellValue("B" . $currentRow, $date == null ? "" : date(HUMAN_DATE, $date));
	$sheet->setCellValue("C" . $currentRow, $row["doc_no"]);
	$sheet->setCellValue("D" . $currentRow, $row["note"]);
	$sheet->setCellValue("E" . $currentRow, $debit);
	$sheet->setCellValue("F" . $currentRow, $credit);
	$formula = sprintf("=G%d+E%d-F%d", $currentRow - 1, $currentRow, $currentRow);
	$sheet->setCellValue("G" . $currentRow, $formula);
}
// Sums
$currentRow++;
$sheet->setCellValue("A" . $currentRow, "TOTAL : ");
$sheet->mergeCells("A$currentRow:D$currentRow");
$sheet->getStyle("A" . $currentRow)->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array(
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
			"style" => PHPExcel_Style_Border::BORDER_THIN,
			"color" => array("argb" => "FF000000")
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
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("kartu-piutang_%s.xlsx", $selectedEmployee->Name);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header(sprintf('Content-Disposition: attachment;filename="%s"', $filename));
		header('Cache-Control: max-age=0');
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("kartu-piutang_%s.xls", $selectedEmployee->Name);
		header('Content-Type: application/vnd.ms-excel');
		header(sprintf('Content-Disposition: attachment;filename="%s"', $filename));
		header('Cache-Control: max-age=0');
		break;
}
$writer->save("php://output");

// Garbage Collector
$excel->disconnectWorksheets();
unset($excel);
ob_flush();
exit();

// EoF: kartu_piutang.excel.php