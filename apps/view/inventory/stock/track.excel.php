<?php
$reader = new PHPExcel_Reader_Excel5(); // Report Template
$phpExcel = $reader->load(APPS . "templates/inventory.stock.track.xls");

switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($phpExcel);
		$filename = sprintf("track_%s.xlsx", $item->ItemCode);
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($phpExcel);
		$filename = sprintf("track_%s.xls", $item->ItemCode);
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$phpExcel->getProperties()->setCreator("Reka System (c)")->setTitle("Tracking Barang")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();

$selectedProject = null;
foreach ($projects as $project) {
	if ($project->Id == $projectId) {
		$selectedProject = $project;
		break;
	}
}
$sheet->setCellValue("A1", sprintf("Tracking Barang: %s - %s", $item->ItemCode, $item->ItemName));
$sheet->setCellValue("A2", $selectedProject == null ? 'Gudang: SEMUA GUDANG' : "Gudang: " . $selectedProject->Code . " - " . $selectedProject->Name);
$sheet->setCellValue("A3", sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $startDate), date(HUMAN_DATE, $endDate)));

// Report Start
$sheet->setCellValue($saldoAwal >= 0 ? "E7" : "F7", abs($saldoAwal));
$sheet->setCellValue("G7", $saldoAwal);

$currentRow = 7;
foreach ($histories as $stock) {
	$currentRow++;

	$isSo = in_array($stock->StockTypeCode, array(2, 102));
	// HACK agar klo SO (-) maka qty menjadi negatif (harus jalan terakhir karena script diatas untuk qty jika type > 100 akan dikurangi)
	if ($stock->StockTypeCode == 102) {
		$stock->Qty *= -1;
	}

	$sheet->setCellValue("A" . $currentRow, $currentRow - 7);
	$sheet->setCellValue("B" . $currentRow, $stock->DocumentNo);
	$sheet->setCellValue("C" . $currentRow, $stock->DocumentType);
	$sheet->setCellValue("D" . $currentRow, $stock->FormatDocumentDate());
	$sheet->setCellValue("E" . $currentRow, !$isSo && $stock->StockTypeCode < 100 ? $stock->Qty : null);
	$sheet->setCellValue("F" . $currentRow, !$isSo && $stock->StockTypeCode > 100 ? $stock->Qty : null);
	$sheet->setCellValue("G" . $currentRow, $isSo ? $stock->Qty : null);
	$formula = "=H" . ($currentRow - 1) . " + E" . $currentRow . " - F" . $currentRow . " + G" . $currentRow;
	$sheet->setCellValue("H" . $currentRow, $formula);
}

$currentRow++;
$sheet->setCellValue("A". $currentRow, "SALDO AKHIR");
$range = sprintf("A%d:C%d", $currentRow, $currentRow);
$sheet->mergeCells($range);
$sheet->getStyle("A" . $currentRow)->applyFromArray(array(
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
	)
	, "font" => array(
		"bold" => true
	)
));
$sheet->setCellValue("D" . $currentRow, date(HUMAN_DATE, $endDate));
$sheet->setCellValue("E" . $currentRow, sprintf("=SUM(E7:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, sprintf("=SUM(F7:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, sprintf("=SUM(G7:G%d)", $currentRow - 1));
$sheet->setCellValue("H" . $currentRow, "=H" . ($currentRow - 1));

// Basic Formatting
$range = "E7:H" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array("numberformat" => array("code" => '0.00')));
$range = "A5:H" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"borders" => array(
		"allborders" => array(
			"style" => PHPExcel_Style_Border::BORDER_THIN
			, "color" => array("argb" => "FF000000")
		)
	)
));

// Hmm Reset Pointer
$sheet->getStyle("A7");

// Flush to client
foreach ($headers as $header) {
	header($header);
}
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
exit();

// End of File: track.excel.php
