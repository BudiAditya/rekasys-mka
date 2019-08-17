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


for ($i = 0; $i < 6; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 1;
foreach ($report as $idx => $rs){
    $row = 1 + ($idx * 26);

    $line1 = 'F' . $row;
    $line2 = 'G' . $row;
    $sheet->mergeCellsByColumnAndRow(5, $row, 5 + 1, $row);
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);
    $sheet->getStyle("$line1:$line2")->applyFromArray($horCenter);
    $sheet->getStyle('F' . $row)->getFont()->setSize(12);
    $sheet->getStyle('F' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('F'. $row, "FINANCE & ACCOUNTING");

    $row++;
    $sheet->mergeCellsByColumnAndRow(2, $row, 2 + 1, $row);
    $sheet->getStyle('C' . $row)->applyFromArray($horCenter);
    $sheet->getStyle('C' . $row)->getFont()->setSize(12);
    $sheet->getStyle('C' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('C'. $row, "ITEM ISSUE");

    $sheet->getStyle('F' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('F' . $row, "No :");
    $sheet->setCellValue('G'. $row, $rs->DocumentNo);

    $row++;
    if($rs->StatusCode == 1) {
        $sheet->mergeCellsByColumnAndRow(2, $row, 2 + 1, $row);
        $sheet->getStyle('C' . $row)->applyFromArray($horCenter);
        $sheet->setCellValue('C'. $row, "(DRAFT)");
        $sheet->getStyle('F' . $row)->applyFromArray($horRight);
        $sheet->setCellValue('F' . $row, "Date :");
        $sheet->setCellValue('G'. $row, long_date(date('Y-m-d', $rs->Date)));
    } else {
        $sheet->getStyle('F' . $row)->applyFromArray($horRight);
        $sheet->setCellValue('F' . $row, "Date :");
        $sheet->setCellValue('G'. $row, long_date(date('Y-m-d', $rs->Date)));
    }

    $row++;
    $sheet->getStyle('F' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('F' . $row, "Departemen :");
    $sheet->setCellValue('G'. $row, $rs->Department->DeptCd);

    $row++;
    $line3 = 'A' . ($row + 1);
    $line4 = 'G' . ($row + 1);
    $sheet->getStyle("$line3:$line4")->getFont()->setBold(true);
    $sheet->getStyle("$line3:$line4")->applyFromArray($border);
    $sheet->getStyle("$line3:$line4")->applyFromArray($hoverCenter);
    $sheet->getStyle("$line3:$line4")->getAlignment()->setWrapText(true);

    $sheet->setCellValue('A' . ($row + 1), "No.");
    $sheet->setCellValue('B' . ($row + 1), "Code");
    $sheet->setCellValue('C' . ($row + 1), "Items");
    $sheet->setCellValue('D' . ($row + 1), "Item Description");
    $sheet->setCellValue('E' . ($row + 1), "Qty Issued");
    $sheet->setCellValue('F' . ($row + 1), "Unit Price");
    $sheet->setCellValue('G' . ($row + 1), "Total Amount");

    $i = 0;
    foreach ($rs->Details as $result){
        $i++;

        $unitPrice = $result->TotalCost / $result->Qty;

        $row++;
        $line5 = 'A' . ($row + 1);
        $line6 = 'G' . ($row + 1);
        $sheet->getStyle("$line5:$line6")->applyFromArray($border);

        $sheet->getStyle('A' . ($row + 1))->applyFromArray($horCenter);
        $sheet->setCellValue('A' . ($row + 1), $i);
        $sheet->setCellValue('B' . ($row + 1), $result->ItemCode);
        $sheet->setCellValue('C' . ($row + 1), $result->ItemName);
        $sheet->setCellValue('D' . ($row + 1), $result->ItemDescription);
        $sheet->setCellValue('E' . ($row + 1), $result->Qty . " " . $result->UomCd);
        $sheet->getStyle('F'.($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('F' . ($row + 1), $unitPrice);
        $sheet->getStyle('G'.($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('G' . ($row + 1), $result->TotalCost);
    }

    $row++;
    $sheet->getStyle('A' . ($row + 2))->getFont()->setItalic(true);
    $sheet->setCellValue('A' . ($row + 2), "REASON : " . "$rs->Note");

    $row++;
    $sheet->getStyle('B' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('B' . ($row + 3), "Issued by :");
    $sheet->getStyle('D' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('D' . ($row + 3), "Approved by :");
    $sheet->getStyle('F' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('F' . ($row + 3), "Received by :");

    $row++;

    $sheet->getStyle('B' . ($row + 5))->applyFromArray($horCenter);
    $sheet->getStyle('B' . ($row + 5))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $sheet->setCellValue('B' . ($row + 5), $rs->CreatedUser->UserName);

    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "";
    $sheet->getStyle('D' . ($row + 5))->applyFromArray($horCenter);
    $sheet->getStyle('D' . ($row + 5))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $sheet->setCellValue('D' . ($row + 5), $approvedUser);

    $sheet->getStyle('F' . ($row + 5))->applyFromArray($horCenter);
    $sheet->setCellValue('F' . ($row + 5), "_________________");
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Nota Item Issue.xls"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;