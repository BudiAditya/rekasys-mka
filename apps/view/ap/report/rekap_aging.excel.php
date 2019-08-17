<?php
/** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("rekap-aging_%s.xlsx", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("rekap-aging_%s.xls", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$excel->getProperties()->setCreator("Reka System (c)")->setTitle("Rekap Aging Hutang Supplier")->setCompany("Rekasys Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Rekap Aging Hutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Rekap Aging Hutang Company : %s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:I1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", "Per Tanggal: " . date(HUMAN_DATE, $date));
$sheet->mergeCells("A2:I2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));

$sheet->setCellValue("A4", "No.");
$sheet->setCellValue("B4", "Kode Supplier");
$sheet->setCellValue("C4", "Nama Supplier");
$sheet->setCellValue("D4", "1 - 30 hari");
$sheet->setCellValue("E4", "31 - 60 hari");
$sheet->setCellValue("F4", "61 - 90 hari");
$sheet->setCellValue("G4", "91 - 120 hari");
$sheet->setCellValue("H4", "121 - 150 hari");
$sheet->setCellValue("I4", "> 150 hari");
$sheet->getStyle("A4:I4")->applyFromArray(array(
	"font" => array("bold" => true)
	, "alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	)
));

// Tulis Data
$counter = 0;
$currentRow = 4;
while ($row = $report->FetchAssoc()) {
	$currentRow++;
	$counter++;

	// Buff data
	$data = array();
	$sheet->setCellValue("A" . $currentRow, $counter . ".");
	$sheet->setCellValue("B" . $currentRow, $row["creditor_cd"]);
	$sheet->setCellValue("C" . $currentRow, $row["creditor_name"]);
	$sheet->setCellValue("D" . $currentRow, $row["sum_hutang_1"] == null ? 0 : $row["sum_hutang_1"]);
	$sheet->setCellValue("E" . $currentRow, $row["sum_hutang_2"] == null ? 0 : $row["sum_hutang_2"]);
	$sheet->setCellValue("F" . $currentRow, $row["sum_hutang_3"] == null ? 0 : $row["sum_hutang_3"]);
	$sheet->setCellValue("G" . $currentRow, $row["sum_hutang_4"] == null ? 0 : $row["sum_hutang_4"]);
	$sheet->setCellValue("H" . $currentRow, $row["sum_hutang_5"] == null ? 0 : $row["sum_hutang_5"]);
	$sheet->setCellValue("I" . $currentRow, $row["sum_hutang_6"] == null ? 0 : $row["sum_hutang_6"]);
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
$flagCyclic = $currentRow == 5;
$sheet->setCellValue("D" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(D5:D%d)", $currentRow - 1));
$sheet->setCellValue("E" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(E5:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(F5:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(G5:G%d)", $currentRow - 1));
$sheet->setCellValue("H" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(H5:H%d)", $currentRow - 1));
$sheet->setCellValue("I" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(I5:I%d)", $currentRow - 1));

// Styling
// Numeric Format: #,##0.00
$range = "D5:I" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"numberformat" => array("code" => "#,##0.00")
));
// Borders
$range = "A4:I" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"borders" => array(
		"allborders" => array(
			"style" => PHPExcel_Style_Border::BORDER_THIN
			, "color" => array("argb" => "FF000000")
		)
	)
));
// Auto Widths
for ($i = 0; $i <= 9; $i++) {
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

// End of file: rekap_aging.excel.php
