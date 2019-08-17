<?php
// Ini file pure akan membuat file excel dan tidak ada HTML fragment
$reader = new PHPExcel_Reader_Excel5(); // Report Template
$phpExcel = $reader->load(APPS . "templates/inventory.mr.rpt_recap_item.xls");

switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($phpExcel);
		$filename = "rekap_item_mr.xlsx";
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($phpExcel);
		$filename = "rekap_item_mr.xls";
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$phpExcel->getProperties()->setCreator("Reka System (c)")->setTitle("Rekap Permintaan barang MR terhadap Stock Gudang")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();

// Setting Auto Width
for ($i = 0; $i < 9; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$sheet->setCellValue("A2", $warehouse == null ? 'Gudang: SEMUA GUDANG' : "Gudang: " . $warehouse->Code . " - " . $warehouse->Name);

$counter = 0;
$prevItemId = null;
$currentRow = 4;
while($row = $rs->fetch_assoc()) {
	$currentRow++;
	$docDate = strtotime($row["mr_date"]);
	if ($prevItemId != $row["item_id"]) {
		$prevItemId = $row["item_id"];
		$flagItem = true;
		$counter++;
	} else {
		$flagItem = false;
	}

	$stockQty = $row["qty_stock"];
	if ($stockQty == null) {
		$stock = "-";
	} else {
		$stock = number_format($stockQty, 2);
	}

	if ($flagItem) {
		// Hanya ditulis jk beda barang
		$sheet->setCellValue("A" . $currentRow, $counter);
		$sheet->setCellValue("B" . $currentRow, $row["item_name"]);
		$sheet->setCellValue("H" . $currentRow, $row["qty_stock"]);
		$sheet->setCellValue("I" . $currentRow, $row["stock_uom_cd"]);
	}

	$sheet->setCellValue("C" . $currentRow, $row["doc_no"]);
	$sheet->setCellValue("D" . $currentRow, date(HUMAN_DATE, $docDate));
	$sheet->setCellValue("E" . $currentRow, $row["app_qty"]);
	$sheet->setCellValue("F" . $currentRow, $row["uom_cd"]);
	$sheet->setCellValue("G" . $currentRow, $row["item_description"]);
}

// Basic Formatting
$range = "E5:E" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array("numberformat" => array("code" => '0.00')));
$range = "H5:H" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array("numberformat" => array("code" => '0.00')));
$range = "A4:I" . $currentRow;
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

// End of File: rpt_recap_item.excel.php
