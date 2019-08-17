<?php
// Ini file pure akan membuat file excel dan tidak ada HTML fragment
$reader = new PHPExcel_Reader_Excel5(); // Report Template
$phpExcel = $reader->load(APPS . "templates/inventory.stock.overview.xls");

switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($phpExcel);
		$filename = sprintf("stock_%s.xlsx", date(HUMAN_DATE, $date));
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($phpExcel);
		$filename = sprintf("stock_%s.xls", date(HUMAN_DATE, $date));
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$phpExcel->getProperties()->setCreator("Reka System (c)")->setTitle("Report Stock Gudang")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();

// Setting Auto Width
for ($i = 0; $i < 6; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$selectedProject = null;
foreach ($projects as $project) {
	if ($project->Id == $projectId) {
		$selectedProject = $project;
		break;
	}
}
$sheet->setCellValue("A1", "Report Stock Tgl: " . date(HUMAN_DATE, $date));
$sheet->setCellValue("A2", $selectedProject == null ? 'Gudang: SEMUA GUDANG' : "Gudang: " . $selectedProject->Code . " - " . $selectedProject->Name);

$currentRow = 4;
while($row = $report->fetch_assoc()) {
	$currentRow++;
	$qty = $row["qty_stock"];
	$minQty = $row["min_qty"];
	$maxQty = $row["max_qty"];

	if ($minQty != 0 && $qty <= $minQty) {
		$status = "STOCK KURANG DARI BATAS MINIMUM";
	} else if ($maxQty != 0 && $qty >= $maxQty) {
		$status = "STOCK MELEBIHI BATAS MAXIMUM";
	} else {
		$status = "OK";
	}

	if ($row["is_discontinue"] == 1) {
		$status .= " - BARANG DISCONTINUE !";
	}

	$sheet->setCellValue("A" . $currentRow, $currentRow - 4);
	$sheet->setCellValue("B" . $currentRow, $row["item_code"]);
	$sheet->setCellValue("C" . $currentRow, $row["item_name"]);
	$sheet->setCellValue("D" . $currentRow, $row["qty_stock"]);
	$sheet->setCellValue("E" . $currentRow, $row["uom_cd"]);
	$sheet->setCellValue("F" . $currentRow, $status);
}

// Basic Formatting
$range = "D5:D" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array("numberformat" => array("code" => '0.00')));
$range = "E4:E" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
										 "borders" => array(
											 "left" => array(
												 "style" => PHPExcel_Style_Border::BORDER_NONE
											 )
										 )
										 ));
$range = "A4:F" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
										 "borders" => array(
											 "allborders" => array(
												 "style" => PHPExcel_Style_Border::BORDER_THIN
												 , "color" => array("argb" => "FF000000")
											 )
										 )
										 ));

// Hmm Reset Pointer
$sheet->getStyle("A5");

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

// End of File: overview.excel.php
