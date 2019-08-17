<?php
/** @var CashRequest[] $report */
// #REGION - styling
$bold = array("font" => array("bold" => true));
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$top = array("alignment" => array("vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$Rp-421]* #,##0.00_);_([$Rp-421]* (#,##0.00);_([$Rp-421]* "-"??_);_(@_)'));
//var_dump(array_merge($center, $allBorders));exit();
// #END REGION

$phpExcel = new PHPExcel();
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Excel MetaData
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("Print Voucher")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("NPKP");
// OK mari kita bikin ini cuma bisa di read-only
$password = "" . time();
//$sheet->getProtection()->setSheet(true);
//$sheet->getProtection()->setPassword($password);

// Default Styling
$phpExcel->getDefaultStyle()->getFont()->setName("Tahoma");
$phpExcel->getDefaultStyle()->getFont()->setSize(10);

// OK kita bikin semua kolomnya memiliki size = 1
for ($i = 0; $i < 100; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setWidth(1.7);
}
// FORCE Custom Margin for continous form
$sheet->getPageMargins()->setTop(0)
	->setRight(0.2)
	->setBottom(0)
	->setLeft(0.2)
	->setHeader(0)
	->setFooter(0);

foreach ($report as $i => $npkp) {
	$row = ($i * 61) + 1;

	$sheet->setCellValue("A$row", "NOTA PERMINTAAN KAS PROYEK");
	$sheet->getStyle("A$row")->applyFromArray(array_merge($bold, $center));
	$sheet->getStyle("A$row")->getFont()->setSize(16);
	$sheet->mergeCells("A$row:BG" . ($row + 1));

	$row += 2;
	$sheet->setCellValue("A$row", $npkp->DocumentNo);
	$sheet->getStyle("A$row")->getFont()->setSize(14);
	$sheet->getStyle("A$row")->applyFromArray($center);
	$sheet->mergeCells("A$row:BG$row");

	$row += 2;
	$sheet->setCellValue("A$row", "Kepada Yth:");
	$sheet->getStyle("A$row")->getFont()->setSize(12);

	$row++;
	$sheet->setCellValue("A$row", "Direktur ".$company->CompanyName);
	$sheet->getStyle("A$row")->getFont()->setSize(12);

	$row++;
	$sheet->setCellValue("A$row", "up. Kepala Bagian Keuangan dan Akuntansi");
	$sheet->getStyle("A$row")->getFont()->setSize(12);

	$row += 3;
	$amount = 0;
	foreach ($npkp->Details as $detail) {
		$amount += $detail->Amount;
	}
	$sheet->setCellValue("A$row", sprintf("Untuk pembiayaan: %s mohon kiranya disediakan uang sebanyak Rp. %s (terbilang: %s rupiah) dengan rencana penggunaan seperti berikut:", $npkp->Objective, number_format($amount, 2, ",", "."), terbilang($amount)));
	$sheet->mergeCells("A$row:BG" . ($row + 1));
	$sheet->getStyle("A$row")->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$row += 3;

	// Header Kolom
	$firstRow = $row;
	$sheet->setCellValue("A$row", "No");
	$sheet->mergeCells("A$row:B$row");
	$sheet->getStyle("A$row")->applyFromArray(array_merge($bold, $center));

	$sheet->setCellValue("C$row", "Kode Perk");
	$sheet->mergeCells("C$row:J$row");
	$sheet->getStyle("C$row")->applyFromArray(array_merge($bold, $center));

	$sheet->setCellValue("K$row", "Rencana Penggunaan Uang");
	$sheet->mergeCells("K$row:AU$row");
	$sheet->getStyle("K$row")->applyFromArray(array_merge($bold, $center));

	$sheet->setCellValue("AV$row", "Jumlah");
	$sheet->mergeCells("AV$row:BG$row");
	$sheet->getStyle("AV$row")->applyFromArray(array_merge($bold, $center));
	$sheet->getStyle("A$row:BG$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:BG$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	foreach ($npkp->Details as $j => $detail) {
		$row++;
		// Cek apakah detail melebihi space yang disiapkan / tidak
		$approx = ceil(strlen($detail->Note) / 70);
		$row2 = $row + $approx - 1;

		$sheet->setCellValue("A$row", $j + 1);
		$sheet->mergeCells("A$row:B$row2");

		$sheet->setCellValue("C$row", $detail->AccountNo);
		$sheet->mergeCells("C$row:J$row2");

		$sheet->setCellValue("K$row", $detail->Note);
		$sheet->getStyle("K$row")->getAlignment()->setWrapText(true);
		$sheet->mergeCells("K$row:AU$row2");

		$sheet->setCellValue("AV$row", $detail->Amount);
		$sheet->mergeCells("AV$row:BG$row2");
		$sheet->getStyle("AV$row")->applyFromArray(array_merge($right, $idrFormat));

		$sheet->getStyle("A$row:BG$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

		// Hmmm silly hack
		$row = $row2;
	}

	$row = $firstRow + 24;
	// Bikin garis table
	$sheet->getStyle("A$firstRow:A$row")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("B$firstRow:B$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("J$firstRow:J$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AU$firstRow:AU$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("BG$firstRow:BG$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$row++;
	$sheet->setCellValue("A$row", "TOTAL : ");
	$sheet->mergeCells("A$row:AU$row");
	$sheet->getStyle("A$row")->applyFromArray(array_merge($bold, $right));
	$sheet->setCellValue("AV$row", $amount);
	$sheet->mergeCells("AV$row:BG$row");
	$sheet->getStyle("AV$row")->applyFromArray(array_merge($bold, $right, $idrFormat));
	$sheet->getStyle("A$row:BG$row")->applyFromArray($allBorders);

	$row += 2;
	$sheet->setCellValue("A$row", "Mohon kiranya uang tersebut dapat diterima selambat-lambatnya tanggal: " . long_date($npkp->FormatEtaDate(SQL_DATEONLY)));

	$row++;
	$sheet->setCellValue("A$row", "Manado, " . long_date($npkp->FormatDate(SQL_DATEONLY)));
	$sheet->getStyle("A$row")->applyFromArray($right);
	$sheet->mergeCells("A$row:BG$row");

	$row += 2;
	$sheet->setCellValue("C$row", "Disetujui Oleh,");
	$sheet->setCellValue("AX$row", "Disiapkan Oleh,");

	$row += 3;
	$sheet->getStyle("A$row:L$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AV$row:BG$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$row++;
	$sheet->setCellValue("A$row", "Koordinator Pelaksana");
	$sheet->mergeCells("A$row:L$row");
	$sheet->getStyle("A$row")->applyFromArray($center);
	$sheet->setCellValue("AV$row", "Administrasi");
	$sheet->mergeCells("AV$row:BG$row");
	$sheet->getStyle("AV$row")->applyFromArray($center);

	$row += 2;
	$sheet->setCellValue("C$row", "Disetujui Oleh,");
	$sheet->setCellValue("AX$row", "Diverifikasi Oleh,");

	$row += 3;
	$sheet->getStyle("A$row:L$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AV$row:BG$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$row++;
	$sheet->setCellValue("A$row", "Direktur");
	$sheet->mergeCells("A$row:L$row");
	$sheet->getStyle("A$row")->applyFromArray($center);
	$sheet->setCellValue("AV$row", "Kabag. Keuangan");
	$sheet->mergeCells("AV$row:BG$row");
	$sheet->getStyle("AV$row")->applyFromArray($center);
}

// Headers
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="print-npkp.xls"');
header('Cache-Control: max-age=0');

// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);

$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// EoF: doc_print.excel.php