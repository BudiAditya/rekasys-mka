<?php

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.pr.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$sheet->setCellValue('C2', date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));
$sheet->setCellValue('C3', $statusName);

// Setting Auto Width
for ($i = 0; $i < 5; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 5;
$no = 0;
while($rs = $report->fetch_assoc()) {
    $row++;
    $no++;

    $updated = $rs["update_time"] != null ? date('d M Y', strtotime($rs["update_time"])) : "";

    $line1 = 'A'.$row;
    $line2 = 'F'.$row;
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);

    $sheet->getStyle('A'.$row)->applyFromArray($horCenter);
    $sheet->setCellValue('A'.$row, $no);
    $sheet->setCellValue('B'.$row, $rs["entity"]);
    $sheet->setCellValue('C'.$row, $rs["doc_no"]);
    $sheet->setCellValue('D'.$row, date('d M Y', strtotime($rs["pr_date"])));
    $sheet->setCellValue('E'.$row, $rs["status_name"]);
    $sheet->setCellValue('F'.$row, $updated);
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap_dokumen_pr.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;