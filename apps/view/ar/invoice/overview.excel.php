<?php
/** @var $output string */
/** @var $debtors Debtor[] */ /** @var $debt Debtor */ /** @var $debtorId int */
/** @var $billTypes BillType[] */ /** @var $billTypeId int[] */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */ /** @var int $groupBy */ /** @var string $key */

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.invoice.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

// styling
$rightBold = array(
	'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
	'font' => array('bold' => true)
);

$debtorName = $debt != null ? $debt->DebtorName : "SEMUA DEBTOR";
$buff = array();
foreach($billTypes as $billType){
	if(in_array($billType->Id, $billTypeId)) {
		$buff[] = $billType->BillTypeDesc;
	}
}
$billDesc = count($buff) > 0 ? implode(", ", $buff) : "SEMUA JENIS TAGIHAN";
if ($status == -1) {
	$statusName = "SEMUA STATUS";
} elseif ($status == 0) {
	$statusName = "UNPOSTED";
} else {
	$statusName = "POSTED";
}

$sheet->setCellValue('A2', "Tanggal : " . date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));
$sheet->setCellValue('A3', "Debtor : " . $debtorName);
$sheet->setCellValue('A4', "Jenis Tagihan : " . $billDesc);
$sheet->setCellValue('A5', "Status : " . $statusName);

$row = 7;
$docNo = null;
$i = 1;
$prevKey = null;
$subTotals = array ("base" => 0, "tax" => 0, "deduction" => 0);
$totals = array ("base" => 0, "tax" => 0, "deduction" => 0);

while($rs = $report->FetchAssoc()) {
	if ($rs["doc_no"] != $docNo){
		$entity = $rs["entity_cd"];
		$doc = $rs["doc_no"];
		$docStatus = $rs["short_desc"];
		$date = date('d M Y', strtotime($rs["doc_date"]));
		$kodeDebtor = $rs["debtor_cd"];
		$namaDebtor = $rs["debtor_name"];
		$docNo = $rs["doc_no"];
		$counter = $i++;
	} else {
		$entity = "";
		$doc = "";
		$docStatus = "";
		$date = "";
		$kodeDebtor = "";
		$namaDebtor = "";
		$counter = "";
	}

	// Apakah kita harus proses grouping ?
	if ($groupBy != -1) {
		if ($prevKey != $rs[$key]) {
			// OK periksa apakah ada nilai sebelumnya atau tidak
			if ($prevKey != null) {
				$row++;
				$sheet->setCellValue("A$row", sprintf("SUB TOTAL %s : ", strtoupper($prevKey)));
				$sheet->mergeCells("A$row:J$row");
				$sheet->setCellValue("K$row", $subTotals["base"]);
				$sheet->setCellValue("L$row", $subTotals["tax"]);
				$sheet->setCellValue("M$row", $subTotals["deduction"]);
				$sheet->setCellValue("N$row", $subTotals["base"] + $subTotals["tax"]);
				$sheet->getStyle("A$row:N$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$sheet->getStyle("A$row:N$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$sheet->getStyle("A$row:N$row")->applyFromArray($rightBold);
			}

			$prevKey = $rs[$key];
			$subTotals = array ("base" => 0, "tax" => 0, "deduction" => 0);
		}

		// Untuk subtotal apabila di grouping
		$subTotals["base"] += $rs["base_amt"];
		$subTotals["tax"] += $rs["tax_amt"];
		$subTotals["deduction"] += $rs["deduction_amt"];
	}
	// Untuk Grand total
	$totals["base"] += $rs["base_amt"];
	$totals["tax"] += $rs["tax_amt"];
	$totals["deduction"] += $rs["deduction_amt"];

	$row++;

	$sheet->getStyle('A'.$row)->applyFromArray($horCenter);
	$sheet->setCellValue('A'.$row, $counter);
	$sheet->setCellValue('B'.$row, $entity);
	$sheet->setCellValue('C'.$row, $doc);
	$sheet->setCellValue('D'.$row, $docStatus);
	$sheet->setCellValue('E'.$row, $date);
	$sheet->setCellValue('F'.$row, $kodeDebtor);
	$sheet->setCellValue('G'.$row, $namaDebtor);
	$sheet->setCellValue('H'.$row, $rs["code"]);
	$sheet->setCellValue('I'.$row, $rs["trx_descs"]);
	$sheet->setCellValue('J'.$row, $rs["lot_no"]);
	$sheet->setCellValue('K'.$row, $rs["base_amt"]);
	$sheet->setCellValue('L'.$row, $rs["tax_amt"]);
	$sheet->setCellValue('M'.$row, $rs["deduction_amt"]);
	$sheet->setCellValue('N'.$row, $rs["base_amt"] + $rs["tax_amt"]);

	if (strlen($namaDebtor) >= 24 || strlen($rs["trx_descs"]) >= 35) {
		$sheet->getRowDimension($row)->setRowHeight(30);
	}
}

// Yang terakhir pasti terlupakan
if ($groupBy != -1 && $prevKey != null) {
	$row++;
	$sheet->setCellValue("A$row", sprintf("SUB TOTAL %s : ", strtoupper($prevKey)));
	$sheet->mergeCells("A$row:J$row");
	$sheet->setCellValue("K$row", $subTotals["base"]);
	$sheet->setCellValue("L$row", $subTotals["tax"]);
	$sheet->setCellValue("M$row", $subTotals["deduction"]);
	$sheet->setCellValue("N$row", $subTotals["base"] + $subTotals["tax"]);
	$sheet->getStyle("A$row:N$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:N$row")->applyFromArray($rightBold);
}

// Grand Total
$row++;
$sheet->setCellValue("A$row", sprintf("TOTAL : ", strtoupper($prevKey)));
$sheet->mergeCells("A$row:J$row");
$sheet->setCellValue("K$row", $totals["base"]);
$sheet->setCellValue("L$row", $totals["tax"]);
$sheet->setCellValue("M$row", $totals["deduction"]);
$sheet->setCellValue("N$row", $totals["base"] + $totals["tax"]);
$sheet->getStyle("A$row:N$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A$row:N$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A$row:N$row")->applyFromArray($rightBold);

$sheet->getStyle("K8:N$row")->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle("A7:A$row")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A7:A$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B7:B$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C7:C$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D7:D$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E7:E$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F7:F$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G7:G$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H7:H$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("I7:I$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("J7:J$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("K7:K$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("L7:L$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("M7:M$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("N7:N$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

$sheet->getStyle("G7:G$row")->getAlignment()->setWrapText(true);
$sheet->getStyle("I7:I$row")->getAlignment()->setWrapText(true);
$sheet->getStyle("A8:N$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

$sheet->setShowGridlines(false);
// Reset pointer
$sheet->getStyle("A1");

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap_dokumen_invoice.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;