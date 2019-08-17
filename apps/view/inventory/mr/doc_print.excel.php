<?php
$objPHPExcel = new PHPExcel();

$sheet = $objPHPExcel->setActiveSheetIndex(0);

$sheet->getProtection()->setSheet(true);

$sheet->getPageMargins()->setTop(0);
$sheet->getPageMargins()->setRight(0.2);
$sheet->getPageMargins()->setLeft(0.2);
$sheet->getPageMargins()->setBottom(0);

// Set alignments & borders
$hoverCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
));

$horRight = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
));

$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$jusCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
));

$border = array('borders' => array(
    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)
),);


for ($i = 0; $i < 5; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 1;
foreach ($report as $idx => $rs){
    $row = 1 + ($idx * 25);

    $line1 = 'E' . $row;
    $line2 = 'F' . $row;
    $sheet->mergeCellsByColumnAndRow(4, $row, 4 + 1, $row);
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);
    $sheet->getStyle("$line1:$line2")->applyFromArray($horCenter);
    $sheet->getStyle('E' . $row)->getFont()->setSize(12);
    $sheet->getStyle('E' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('E'. $row, "FINANCE & ACCOUNTING");

    $row++;
    $sheet->getStyle('C' . $row)->applyFromArray($horCenter);
    $sheet->getStyle('C' . $row)->getFont()->setSize(12);
    $sheet->getStyle('C' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('C'. $row, "MATERIAL REQUISITION");

    $sheet->getStyle('E' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('E' . $row, "No :");
    $sheet->setCellValue('F'. $row, $rs->DocumentNo);

    $row++;
    $sheet->getStyle('E' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('E' . $row, "Date :");
    $sheet->setCellValue('F'. $row, long_date(date('Y-m-d', $rs->Date)));

    $row++;
    $line3 = 'A' . ($row + 1);
    $line4 = 'F' . ($row + 1);
    $sheet->getStyle("$line3:$line4")->getFont()->setBold(true);
    $sheet->getStyle("$line3:$line4")->applyFromArray($border);
    $sheet->getStyle("$line3:$line4")->applyFromArray($hoverCenter);
    $sheet->getStyle("$line3:$line4")->getAlignment()->setWrapText(true);

    $sheet->setCellValue('A' . ($row + 1), "No.");
    $sheet->setCellValue('B' . ($row + 1), "Code");
    $sheet->setCellValue('C' . ($row + 1), "Items");
    $sheet->setCellValue('D' . ($row + 1), "Qty Requested");
    $sheet->setCellValue('E' . ($row + 1), "Qty Approved");
    $sheet->setCellValue('F' . ($row + 1), "Items Description");

    $i = 0;
    foreach ($rs->MrDetails as $result){
        $i++;

        $row++;
        $line5 = 'A' . ($row + 1);
        $line6 = 'F' . ($row + 1);
        $sheet->getStyle("$line5:$line6")->applyFromArray($border);

        $sheet->getStyle('A' . ($row + 1))->applyFromArray($horCenter);
        $sheet->setCellValue('A' . ($row + 1), $i);
        $sheet->setCellValue('B' . ($row + 1), $result->ItemCode);
        $sheet->setCellValue('C' . ($row + 1), $result->ItemName);
        $sheet->setCellValue('D' . ($row + 1), $result->RequestedQty . " " . $result->UomCd);
        $sheet->setCellValue('E' . ($row + 1), $result->ApprovedQty . " " . $result->UomCd);
        $sheet->setCellValue('F' . ($row + 1), $result->ItemDescription);


    }

    $row++;
    $sheet->getStyle('A' . ($row + 2))->getFont()->setItalic(true);
    $sheet->setCellValue('A' . ($row + 2), "REASON : " . "$rs->Note");

    $row++;
    $sheet->getStyle('C' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('C' . ($row + 3), "Requested by :");
    $sheet->getStyle('E' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('E' . ($row + 3), "Approved by :");

    $row++;

    $sheet->getStyle('C' . ($row + 5))->applyFromArray($horCenter);
    $sheet->getStyle('C' . ($row + 5))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $sheet->setCellValue('C' . ($row + 5), $rs->CreatedUser->UserName);

    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "";
    $sheet->getStyle('E' . ($row + 5))->applyFromArray($horCenter);
    $sheet->getStyle('E' . ($row + 5))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $sheet->setCellValue('E' . ($row + 5), $approvedUser);
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Nota MR.xls"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;