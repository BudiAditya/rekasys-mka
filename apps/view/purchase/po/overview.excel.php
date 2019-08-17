<?php

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.po.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$sheet->setCellValue('C2', date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));
$sheet->setCellValue('C3', $supplierName);
$sheet->setCellValue('C4', $statusName);

// Setting Auto Width
for ($i = 0; $i < 6; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 6;
$no = 0;
while($rs = $report->fetch_assoc()) {
    $row++;
    $no++;

    $ppn = $rs["is_vat"] == 1 ? "Ya" : "Tidak";
    $inc = $rs["is_inc_vat"] == 1 ? "Ya" : "Tidak";

    $line1 = 'A'.$row;
    $line2 = 'I'.$row;
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);

    $sheet->getStyle('A'.$row)->applyFromArray($horCenter);
    $sheet->setCellValue('A'.$row, $no);
    $sheet->setCellValue('B'.$row, $rs["entity"]);
    $sheet->setCellValue('C'.$row, $rs["supplier"]);
    $sheet->setCellValue('D'.$row, $rs["doc_no"]);
    $sheet->setCellValue('E'.$row, date('d M Y', strtotime($rs["po_date"])));
    $sheet->setCellValue('F'.$row, date('d M Y', strtotime($rs["expected_date"])));
    $sheet->setCellValue('G'.$row, $rs["status_name"]);
    $sheet->setCellValue('H'.$row, $ppn);
    $sheet->setCellValue('I'.$row, $inc);
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap_dokumen_po.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;