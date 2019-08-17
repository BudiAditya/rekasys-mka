<?php
/** @var $output string */ /** @var $report Voucher[] */
if ($output == "xls") {
	require_once(LIBRARY . "PHPExcel.php");
	require_once("print.xls.common.php");
	require_once("print.xls.special.php");

	$phpExcel = new PHPExcel();
	$headers = array(
		'Content-Type: application/vnd.ms-excel'
		, 'Content-Disposition: attachment;filename="print-voucher.xls"'
		, 'Cache-Control: max-age=0'
	);
	$writer = new PHPExcel_Writer_Excel5($phpExcel);

	// Excel MetaData
	$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("Print Voucher")->setCompany("Rekasys Corporation");
	$sheet = $phpExcel->getActiveSheet();
	$sheet->setTitle("Voucher");
	// OK mari kita bikin ini cuma bisa di read-only
	$password = "" . time();
	$sheet->getProtection()->setSheet(true);
	$sheet->getProtection()->setPassword($password);

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

	$counter = 0;
	foreach ($report as $idx => $rs){
		if ($rs->VoucherType->Id != 4) {
			$subPages = ceil(count($rs->Details) / 9);
			for ($i = 1; $i <= $subPages; $i++) {
				CreateXlsCommon($sheet, $rs, $counter, $i, $subPages);
				$counter++;
			}
		} else {
			$subPages = ceil(count($rs->Details) / 7);
			for ($i = 1; $i <= $subPages; $i++) {
				CreateXlsSpecial($sheet, $rs, $counter, $i, $subPages);
				$counter++;
			}
		}

		$output = $rs->VoucherType->VoucherCd;
	}

	// Hmm Reset Pointer
	$sheet->getStyle("A1");
	$sheet->setShowGridlines(false);

	// Flush to client
	foreach ($headers as $header) {
		header($header);
	}
	// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
	$writer->save("php://output");

	// Garbage Collector
	$phpExcel->disconnectWorksheets();
	unset($phpExcel);
	ob_flush();
} else {
	require_once(LIBRARY . "tabular_pdf.php");
	require_once("print.common.php");
	require_once("print.special.php");

	// array(612,396) => array(215.9, 139.7)

	$pdf = new TabularPdf("P", "mm", "letter");
	$pdf->SetAutoPageBreak(true, 5);
	$pdf->SetMargins(5, 5);

	$pdf->SetColumns(
		array("KODE", "DEPT", "URAIAN", "JUMLAH"),
		array(30, 20, 0, 40)
	);


	$pdf->Open();
	$pdf->AddFont("Tahoma");
	$pdf->AddFont("Tahoma", "B");

	$output = "Voucher";
	$counter = 0;
	foreach ($report as $idx => $rs){

		if ($rs->VoucherType->Id != 4) {
			$subPages = ceil(count($rs->Details) / 9);
			for ($i = 1; $i <= $subPages; $i++) {
				CreateCommon($pdf, $rs, $counter, $i, $subPages);
				$counter++;
			}
		} else {
			$subPages = ceil(count($rs->Details) / 7);
			for ($i = 1; $i <= $subPages; $i++) {
				CreateSpecial($pdf, $rs, $counter, $i, $subPages);
				$counter++;
			}
		}

		$output = $rs->VoucherType->VoucherCd;
	}

	$pdf->Output($output . ".pdf", "D");
}