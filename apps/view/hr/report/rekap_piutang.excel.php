<?php
/** @var $start int */ /** @var $end int */ /** @var $company Company */ /** @var $report null|ReaderBase */

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();

// Excel MetaData
$excel->getProperties()->setCreator("Megamas System (c)")->setTitle("Rekap Piutang Karyawan")->setCompany("Megamas Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Rekap Piutang Karyawan");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Rekap Piutang Karyawan: %s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:H1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));
$sheet->mergeCells("A2:H2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));

$sheet->setCellValue("A4", "No.");
$sheet->setCellValue("B4", "Nama Karyawan");
$sheet->setCellValue("C4", "NIK");
$sheet->setCellValue("D4", "Company");
$sheet->setCellValue("E4", "Saldo Awal");
$sheet->setCellValue("F4", "Debit");
$sheet->setCellValue("G4", "Kredit");
$sheet->setCellValue("H4", "Sisa");
$sheet->getStyle("A4:H4")->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	)
));

// Tulis Data
$counter = 0;
$currentRow = 4;
while ($row = $report->FetchAssoc()) {
	$currentRow++;
	$counter++;
	$saldoAwal = $row["opening_balance"] + $row["prev_debit"] - $row["prev_credit"];
	$debet = $row["current_debit"];
	$kredit = $row["current_credit"];

	// Buff data
	$data = array();
	$sheet->setCellValue("A" . $currentRow, $counter . ".");
	$sheet->setCellValue("B" . $currentRow, $row["e_name"]);
	$sheet->setCellValueExplicit("C" . $currentRow, $row["e_nik"], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValue("D" . $currentRow, $row["entity_cd"]);
	$sheet->setCellValue("E" . $currentRow, $saldoAwal);
	$sheet->setCellValue("F" . $currentRow, $debet);
	$sheet->setCellValue("G" . $currentRow, $kredit);
	$formula = sprintf("=E%d+F%d-G%d", $currentRow, $currentRow, $currentRow);
	$sheet->setCellValue("H" . $currentRow, $formula);
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
$flagCyclic = $currentRow == 5;
$sheet->setCellValue("E" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(E5:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(F5:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(G5:G%d)", $currentRow - 1));
$sheet->setCellValue("H" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(H5:H%d)", $currentRow - 1));

// Styling
// Numeric Format: #,##0.00
$range = "E5:H" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"numberformat" => array("code" => "#,##0.00")
));
// Borders
$sheet->getStyle("A4:H4")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A4:H4")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A4:A$currentRow")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A4:A$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B4:B$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C4:C$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D4:D$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E4:E$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F4:F$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G4:G$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H4:H$currentRow")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("a$currentRow:H$currentRow")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("a$currentRow:H$currentRow")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// Auto Widths
for ($i = 0; $i <= 8; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Hmm Reset Pointer
$sheet->getStyle("A5");
$sheet->setShowGridlines(false);

// Flush to client
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("rekap-piutang_%s.xlsx", $company->EntityCd);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header(sprintf('Content-Disposition: attachment;filename="%s"', $filename));
		header('Cache-Control: max-age=0');
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("rekap-piutang_%s.xls", $company->EntityCd);
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

// End of file: rekap_piutang.excel.php
