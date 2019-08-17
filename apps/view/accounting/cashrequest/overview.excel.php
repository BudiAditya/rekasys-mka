<?php
/** @var StatusCode[] $statuses */ /** @var int $statusCode */ /** @var ReaderBase $report */ /** @var string $output */ /** @var int $start */ /** @var int $end */
$statusName = "-- SEMUA STATUS --";
foreach ($statuses as $row) {
	if ($row->Code == $statusCode) {
		$statusName = $row->ShortDesc;
		break;
	}
}

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.npkp.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$sheet->setCellValue('C2', $statusName);

// Setting Auto Width
for ($i = 0; $i < 8; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 4;
$no = 0;
while($rs = $report->FetchAssoc()) {
    $row++;
    $no++;

    $sheet->getStyle('A'.$row)->applyFromArray($horCenter);
    $sheet->setCellValue('A'.$row, $no);
    $sheet->setCellValue('B'.$row, $rs["entity"]);
    $sheet->setCellValue('C'.$row, $rs["doc_no"]);
    $sheet->setCellValue('D'.$row, $rs["objective"]);
    $sheet->setCellValue('E'.$row, date('d M Y', strtotime($rs["cash_request_date"])));
    $sheet->setCellValue('F'.$row, date('d M Y', strtotime($rs["eta_date"])));
    $sheet->setCellValue('G'.$row, $rs["jumlah"]);
    $sheet->setCellValue('H'.$row, $rs["status_name"]);
}

$sheet->getStyle("G5:G$row")->getNumberFormat()->setFormatCode('_([$Rp-421]* #,##0.00_);_([$Rp-421]* (#,##0.00);_([$Rp-421]* "-"??_);_(@_)');
$sheet->getStyle("A4:H$row")->applyFromArray($border);

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap_dokumen_npkp.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;