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
$excel->getProperties()->setCreator("Reka System (c)")->setTitle("Rekap Aging Piutang Debtor")->setCompany("Rekasys Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Rekap Aging Piutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Rekap Aging Piutang Company : %s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:J1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", "Per Tanggal: " . date(HUMAN_DATE, $date));
$sheet->mergeCells("A2:J2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));

$sheet->setCellValue("A4", "No.");
$sheet->setCellValue("B4", "Kode Debtor");
$sheet->setCellValue("C4", "Nama Debtor");
$sheet->setCellValue("D4", "Merk Dagang");
$sheet->setCellValue("E4", "1 - 30 hari");
$sheet->setCellValue("F4", "31 - 60 hari");
$sheet->setCellValue("G4", "61 - 90 hari");
$sheet->setCellValue("H4", "91 - 120 hari");
$sheet->setCellValue("I4", "121 - 150 hari");
$sheet->setCellValue("J4", "> 150 hari");
$sheet->getStyle("A4:J4")->applyFromArray(array(
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
	$sheet->setCellValue("B" . $currentRow, $row["debtor_cd"]);
	$sheet->setCellValue("C" . $currentRow, $row["debtor_name"]);
	$sheet->setCellValue("D" . $currentRow, $row["trade_name"]);
	$sheet->setCellValue("E" . $currentRow, $row["sum_piutang_1"] == null ? 0 : $row["sum_piutang_1"]);
	$sheet->setCellValue("F" . $currentRow, $row["sum_piutang_2"] == null ? 0 : $row["sum_piutang_2"]);
	$sheet->setCellValue("G" . $currentRow, $row["sum_piutang_3"] == null ? 0 : $row["sum_piutang_3"]);
	$sheet->setCellValue("H" . $currentRow, $row["sum_piutang_4"] == null ? 0 : $row["sum_piutang_4"]);
	$sheet->setCellValue("I" . $currentRow, $row["sum_piutang_5"] == null ? 0 : $row["sum_piutang_5"]);
	$sheet->setCellValue("J" . $currentRow, $row["sum_piutang_6"] == null ? 0 : $row["sum_piutang_6"]);
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
$sheet->setCellValue("E" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(E5:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(F5:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(G5:G%d)", $currentRow - 1));
$sheet->setCellValue("H" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(H5:H%d)", $currentRow - 1));
$sheet->setCellValue("I" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(I5:I%d)", $currentRow - 1));
$sheet->setCellValue("J" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(J5:J%d)", $currentRow - 1));

// Styling
// Numeric Format: #,##0.00
$range = "E5:J" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"numberformat" => array("code" => "#,##0.00")
));
// Borders
$range = "A4:J" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"borders" => array(
		"allborders" => array(
			"style" => PHPExcel_Style_Border::BORDER_THIN
			, "color" => array("argb" => "FF000000")
		)
	)
));
// Auto Widths
for ($i = 0; $i <= 10; $i++) {
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
