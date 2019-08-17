<?php

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.data.creditor.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$sheet->setCellValue('C2', $typeDesc);

// Setting Auto Width
for ($i = 0; $i < 6; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 4;
$no = 0;
while($rs = $report->fetch_assoc()) {
    $row++;
    $no++;

    $line1 = 'A'.$row;
    $line2 = 'G'.$row;
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);

    $sheet->getStyle('A'.$row)->applyFromArray($horCenter);
    $sheet->setCellValue('A'.$row, $no);
    $sheet->setCellValue('B'.$row, $rs["entity"]);
    $sheet->setCellValue('C'.$row, $rs["creditor_cd"]);
    $sheet->setCellValue('D'.$row, $rs["type_desc"]);
    $sheet->setCellValue('E'.$row, $rs["creditor_name"]);
    $sheet->setCellValue('F'.$row, $rs["address1"] . " " . $rs["address2"] . " " . $rs["address3"]);
    $sheet->setCellValue('G'.$row, $rs["core_business"]);
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap_data_creditor.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;