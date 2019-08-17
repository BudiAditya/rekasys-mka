<?php
/** @var $start int */ /** @var $end int */ /** @var $company Company */ /** @var $report null|ReaderBase */

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("rekap-hutang_%s.xlsx", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("rekap-hutang_%s.xls", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$excel->getProperties()->setCreator("Reka System (c)")->setTitle("Rekap Hutang Supplier")->setCompany("Rekasys Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Rekap Hutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Rekap Piutang: %s - %s", $company->EntityCd, $company->CompanyName));
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
$sheet->setCellValue("B4", "Kode Supplier");
$sheet->setCellValue("C4", "Nama Supplier");
$sheet->setCellValue("D4", "Saldo Awal");
$sheet->setCellValue("E4", "Debit");
$sheet->setCellValue("F4", "Kredit");
$sheet->setCellValue("G4", "Sisa");
$sheet->getStyle("A4:H4")->applyFromArray(array(
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
	$saldoAwal = $row["saldo_kredit"] - $row["saldo_debet"] + $row["prev_kredit"] - $row["prev_debet"];
	$debet = $row["current_debet"];
	$kredit = $row["current_kredit"];

	$sheet->setCellValue("A" . $currentRow, $counter . ".");
	$sheet->setCellValue("B" . $currentRow, $row["creditor_cd"]);
	$sheet->setCellValue("C" . $currentRow, $row["creditor_name"]);
	$sheet->setCellValue("D" . $currentRow, $saldoAwal);
	$sheet->setCellValue("E" . $currentRow, $debet == null ? 0 : $debet);
	$sheet->setCellValue("F" . $currentRow, $kredit == null ? 0 : $kredit);
	$formula = sprintf("=D%d-E%d+F%d", $currentRow, $currentRow, $currentRow);
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
$flagCyclic = $currentRow == 5;
$sheet->setCellValue("D" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(D5:D%d)", $currentRow - 1));
$sheet->setCellValue("E" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(E5:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(F5:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(G5:G%d)", $currentRow - 1));

// Styling
// Numeric Format: #,##0.00
$range = "D5:G" . $currentRow;
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
for ($i = 0; $i <= 6; $i++) {
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

// End of file: rekap_hutang.excel.php
