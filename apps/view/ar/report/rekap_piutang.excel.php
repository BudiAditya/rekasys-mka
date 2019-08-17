<?php
/** @var $start int */ /** @var $end int */ /** @var $company Company */ /** @var $report null|ReaderBase */

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("rekap-piutang_%s.xlsx", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("rekap-piutang_%s.xls", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$excel->getProperties()->setCreator("Reka System (c)")->setTitle("Rekap Piutang Debtor")->setCompany("Rekasys Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Rekap Piutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Rekap Piutang: %s - %s", $company->EntityCd, $company->CompanyName));
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
$sheet->setCellValue("B4", "Nama Customer");
$sheet->setCellValue("C4", "Kode Debtor");
$sheet->setCellValue("D4", "Nama Debtor");
$sheet->setCellValue("E4", "Saldo Awal");
$sheet->setCellValue("F4", "Debit");
$sheet->setCellValue("G4", "Kredit");
$sheet->setCellValue("H4", "Sisa");
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
	$saldoAwal = $row["saldo_debet"] - $row["saldo_kredit"] + $row["prev_debet"] - $row["prev_kredit"];
	$debet = $row["current_debet"];
	$kredit = $row["current_kredit"];

	// Buff data
	$sheet->setCellValue("A" . $currentRow, $counter . ".");
	$sheet->setCellValue("B" . $currentRow, $row["customer_name"]);
	$sheet->setCellValue("C" . $currentRow, $row["debtor_cd"]);
	$sheet->setCellValue("D" . $currentRow, $row["debtor_name"]);
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

// Styling
// Numeric Format: #,##0.00
$range = "E5:H" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"numberformat" => array("code" => "#,##0.00")
));
// Borders
$range = "A4:H" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"borders" => array(
		"allborders" => array(
			"style" => PHPExcel_Style_Border::BORDER_THIN
			, "color" => array("argb" => "FF000000")
		)
	)
));
// Auto Widths
for ($i = 0; $i <= 8; $i++) {
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

// End of file: rekap_piutang.excel.php
