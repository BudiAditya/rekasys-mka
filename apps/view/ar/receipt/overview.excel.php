<?php
/** @var Debtor[] $debtors */ /** @var Debtor $debt */  /** @var int $debtorId */  /** @var StatusCode[] $codes */ /** @var StatusCode $codeName */  /** @var int $status */
/** @var ReaderBase $report */ /** @var int $startDate */ /** @var int $endDate */ /** @var string $output */

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.or.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$debtorName = $debt != null ? $debt->DebtorName: "SEMUA DEBTOR";
$sheet->setCellValue('C2', date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));
$sheet->setCellValue('C3', $debtorName);
$statusName = $codeName != null ? $codeName->ShortDesc : "SEMUA STATUS";
$sheet->setCellValue('C4', $statusName);

// Setting Auto Width
for ($i = 0; $i < 10; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 7;
$no = 0;
while($rs = $report->FetchAssoc()) {
	$row++;
	$no++;

	$sheet->setCellValue('A'.$row, $no);
	$sheet->setCellValue('B'.$row, $rs["entity_cd"]);
	$sheet->setCellValue('C'.$row, $rs["doc_no"]);
	$sheet->setCellValue('D'.$row, $rs["short_desc"]);
	$sheet->setCellValue('E'.$row, $rs["debtor_name"]);
	$sheet->setCellValue('F'.$row, $rs["pph_flag"] == 1 ? "Ya" : "-");
	$sheet->setCellValue('G'.$row, date('d M Y', strtotime($rs["doc_date"])));
	$sheet->setCellValue('H'.$row, $rs["trx_amt"]);
	$sheet->setCellValue('I'.$row, $rs["alloc_amt"]);
	$sheet->setCellValue('J'.$row, $rs["deduction_amt"]);
	$sheet->setCellValue('K'.$row, $rs["acc_no"]);
}
$row++;
$flagCyclic = ($row == 8);
$sheet->mergeCells("A$row:G$row");
$sheet->setCellValue("H$row", $flagCyclic ? "0" : "AHO");
$sheet->setCellValue("I$row", $flagCyclic ? "0" : "=SUM(I8:I" . ($row - 1) . ")");
$sheet->setCellValue("J$row", $flagCyclic ? "0" : "=SUM(J8:J" . ($row - 1) . ")");

$sheet->getStyle("A8:K$row")->applyFromArray($border);
$sheet->getStyle("A8:A$row")->applyFromArray($horCenter);
$sheet->getStyle("F8:F$row")->applyFromArray($horCenter);
$sheet->getStyle("H8:J$row")->getNumberFormat()->setFormatCode("#,##0.00");

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="REKAP DOKUMEN OFFICIAL RECEIPT.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;