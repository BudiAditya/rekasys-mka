<?php
$objPHPExcel = new PHPExcel();

$sheet = $objPHPExcel->setActiveSheetIndex(0);

$sheet->getProtection()->setSheet(true);

$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

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


for ($i = 0; $i < 9; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 1;
foreach ($report as $idx => $rs){
    $logo = 'public/images/company/'.$company->FileLogo;
    $row = 1 + ($idx * 40);
    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setPath($logo);
    $objDrawing->setResizeProportional(false);
    $objDrawing->setWidth(65);
    $objDrawing->setHeight(45);
    $objDrawing->setCoordinates('A'.$row);
    $objDrawing->setOffsetY(8);
    $objDrawing->setWorksheet($sheet);

    $line1 = 'H' . $row;
    $line2 = 'J' . $row;
    $sheet->mergeCellsByColumnAndRow(7, $row, 7 + 2, $row);
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);
    $sheet->getStyle("$line1:$line2")->applyFromArray($horCenter);
    $sheet->getStyle('H' . $row)->getFont()->setSize(12);
    $sheet->getStyle('H' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('H'. $row, $rs->ProjectCd.' - '.$rs->ProjectName);

    $row++;
    $sheet->mergeCellsByColumnAndRow(3, $row, 3 + 1, $row);
    $sheet->getStyle('D' . $row)->applyFromArray($horCenter);
    $sheet->getStyle('D' . $row)->getFont()->setSize(12);
    $sheet->getStyle('D' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('D'. $row, "PURCHASE REQUEST");

    $sheet->getStyle('H' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('H' . $row, "No :");
    $sheet->setCellValue('I'. $row, $rs->DocumentNo);

    $row++;
    $sheet->getStyle('H' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('H' . $row, "Date :");
    $sheet->setCellValue('I'. $row, long_date(date('Y-m-d', $rs->Date)));

    $row++;
    $line3 = 'A' . ($row + 1);
    $line4 = 'J' . ($row + 1);
    $sheet->getStyle("$line3:$line4")->getFont()->setBold(true);
    $sheet->getStyle("$line3:$line4")->applyFromArray($border);
    $sheet->getStyle("$line3:$line4")->applyFromArray($hoverCenter);
    $sheet->getStyle("$line3:$line4")->getAlignment()->setWrapText(true);

    $sheet->setCellValue('A' . ($row + 1), "No.");
    $sheet->setCellValue('B' . ($row + 1), "Code");
    $sheet->setCellValue('C' . ($row + 1), "Items");
    $sheet->setCellValue('D' . ($row + 1), "Qty");
    $sheet->setCellValue('E' . ($row + 1), "Items Description");
    $sheet->setCellValue('F' . ($row + 1), "Supplier 1");
    $sheet->setCellValue('G' . ($row + 1), "Supplier 2");
    $sheet->setCellValue('H' . ($row + 1), "Supplier 3");
    $sheet->setCellValue('I' . ($row + 1), "Vendor Selected");
    $sheet->setCellValue('J' . ($row + 1), "Total Cost");

    $i = 0;
    $sumTotal = 0;
    foreach ($rs->Details as $result){
        $i++;

        switch($result->SelectedSupplier){
            case "-1";
                $vendor = "-";
                $total = $result->Qty * 0;
                break;

            case "1";
                $vendor = "Supplier 1";
                $total = $result->Qty * $result->Price1;
                break;

            case "2";
                $vendor = "Supplier 2";
                $total = $result->Qty * $result->Price2;
                break;

            case "3";
                $vendor = "Supplier 3";
                $total = $result->Qty * $result->Price3;
                break;
        }

        $row++;
        $line5 = 'A' . ($row + 1);
        $line6 = 'J' . ($row + 1);
        $sheet->getStyle("$line5:$line6")->applyFromArray($border);

        $sheet->getStyle('A' . ($row + 1))->applyFromArray($horCenter);
        $sheet->setCellValue('A' . ($row + 1), $i);
        $sheet->setCellValue('B' . ($row + 1), $result->ItemCode);
        $sheet->setCellValue('C' . ($row + 1), $result->ItemName);
        $sheet->setCellValue('D' . ($row + 1), $result->Qty . " " . $result->UomCd);
        $sheet->setCellValue('E' . ($row + 1), $result->ItemDescription);
        $sheet->getStyle('F'.($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('F' . ($row + 1), $result->Price1);
        $sheet->getStyle('G'.($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('G' . ($row + 1), $result->Price2);
        $sheet->getStyle('H'.($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('H' . ($row + 1), $result->Price3);
        $sheet->setCellValue('I' . ($row + 1), $vendor);
        $sheet->getStyle('J'.($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('J' . ($row + 1), $total);

        $sumTotal =  $sumTotal + $total;
    }

    $row++;
    $sheet->getStyle('I' . ($row + 1))->applyFromArray($border);
    $sheet->getStyle('I' . ($row + 1))->applyFromArray($horCenter);
    $sheet->getStyle('I' . ($row + 1))->getFont()->setBold(true);
    $sheet->setCellValue('I' . ($row + 1), "TOTAL");
    $sheet->getStyle('J' . ($row + 1))->applyFromArray($border);
    $sheet->getStyle('J'. ($row + 1))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
    $sheet->setCellValue('J'. ($row + 1), $sumTotal);

    $row++;
    $sheet->getStyle('A' . ($row + 2))->getFont()->setItalic(true);
    $sheet->setCellValue('A' . ($row + 2), "REASON : " . "$rs->Note");

    $row++;
    $sheet->mergeCellsByColumnAndRow(2, $row + 3, 2 + 1, $row + 3);
    $sheet->getStyle('C' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('C' . ($row + 3), "Created by :");
    $sheet->mergeCellsByColumnAndRow(6, $row + 3, 6 + 1, $row + 3);
    $sheet->getStyle('G' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('G' . ($row + 3), "Approved by :");

    $row++;
    $sheet->mergeCellsByColumnAndRow(2, $row + 5, 2 + 1, $row + 5);
    $sheet->getStyle('C' . ($row + 5))->applyFromArray($horCenter);
    $line7 = 'C' . ($row + 5);
    $line8 = 'D' . ($row + 5);
    $sheet->getStyle("$line7:$line8")->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $sheet->setCellValue('C' . ($row + 5), $rs->CreatedUser->UserName);

    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "";
    $sheet->mergeCellsByColumnAndRow(6, $row + 5, 6 + 1, $row + 5);
    $sheet->getStyle('G' . ($row + 5))->applyFromArray($horCenter);
    $line9 = 'G' . ($row + 5);
    $line10 = 'H' . ($row + 5);
    $sheet->getStyle("$line9:$line10")->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $sheet->setCellValue('G' . ($row + 5),  $approvedUser);
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Nota PR.xls"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;