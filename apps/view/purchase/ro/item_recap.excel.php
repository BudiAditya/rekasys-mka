<?php
$objPHPExcel = new PHPExcel();

$sheet = $objPHPExcel->setActiveSheetIndex(0);

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)),);

//set alignment
$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$horRight = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
));

$sheet->mergeCellsByColumnAndRow(0, 1, 0 + 5, 1);
$sheet->getStyle('A1')->getFont()->setSize(22);
$sheet->setCellValue('A1', "REKAPITULASI BARANG PO");

$sheet->mergeCellsByColumnAndRow(0, 2, 0 + 1, 2);
$sheet->getStyle('A2')->applyFromArray($horRight);
$sheet->setCellValue('A2', "Tanggal : ");
$sheet->setCellValue('C2', date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));

$sheet->mergeCellsByColumnAndRow(0, 3, 0 + 1, 3);
$sheet->getStyle('A3')->applyFromArray($horRight);
$sheet->setCellValue('A3', "Status : ");
$sheet->setCellValue('C3', $statusName->ShortDesc);

// Setting Auto Width
for ($i = 0; $i < 5; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$sheet->getStyle('A5:F5')->applyFromArray($border);
$sheet->getStyle('A5:F5')->applyFromArray($horCenter);
$sheet->getStyle('A5:F5')->getFont()->setBold(true);
$sheet->setCellValue('A5', 'No');
$sheet->setCellValue('B5', 'Kode Barang');
$sheet->setCellValue('C5', 'Nama Barang');
$sheet->setCellValue('D5', 'Jumlah');
$sheet->setCellValue('E5', 'Satuan');
$sheet->setCellValue('F5', 'Harga');

$row = 5;
$i = 0;
$total = 0;
while($rs = $report->fetch_assoc()) {
    $row++;
    $i++;

    $total = $total + $rs["total"];

    $line1 = 'A'.$row;
    $line2 = 'F'.$row;
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);

    $sheet->getStyle('A'.$row)->applyFromArray($horCenter);
    $sheet->setCellValue('A'.$row, $i);
    $sheet->setCellValue('B'.$row, $rs["item_code"]);
    $sheet->setCellValue('C'.$row, $rs["item_name"]);
    $sheet->setCellValue('D'.$row, $rs["jumlah"]);
    $sheet->setCellValue('E'.$row, $rs["uom_cd"]);
    $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
    $sheet->setCellValue('F'.$row, $rs["total"]);
}

$row += 1;
$sheet->mergeCellsByColumnAndRow(0, $row, 0 + 4, $row);

$line3 = 'A'.$row;
$line4 = 'F'.$row;
$sheet->getStyle("$line3:$line4")->applyFromArray($border);
$sheet->getStyle('A'.$row)->getFont()->setBold(true);
$sheet->getStyle('A'.$row)->applyFromArray($horRight);
$sheet->setCellValue('A'.$row, "TOTAL");

$temp = $row - 1;
$token = $report->num_rows();
$sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
$sheet->setCellValue('F'.$row, $token == 0 ? 0 :"=SUM(F6:F$temp)");

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekap Barang PO.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
