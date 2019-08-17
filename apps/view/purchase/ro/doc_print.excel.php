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


for ($i = 1; $i < 6; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 1;
foreach ($report as $idx => $rs){
    $row = 1 + ($idx * 27);

    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setPath('public/images/company/mka.jpeg');
    $objDrawing->setResizeProportional(false);
    $objDrawing->setWidth(65);
    $objDrawing->setHeight(45);
    $objDrawing->setCoordinates('A'.$row);
    $objDrawing->setOffsetY(8);
    $objDrawing->setWorksheet($sheet);

    $row++;
    $line1 = 'F' . $row;
    $line2 = 'G' . $row;
    $sheet->mergeCellsByColumnAndRow(5, $row, 5 + 1, $row);
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);
    $sheet->getStyle("$line1:$line2")->applyFromArray($horCenter);
    $sheet->getStyle('F' . $row)->getFont()->setSize(12);
    $sheet->getStyle('F' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('F'. $row, "FINANCE & ACCOUNTING");

    $row++;
    $sheet->getStyle('F' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('F' . $row, "No :");
    $sheet->setCellValue('G' . $row, $rs->DocumentNo);

    $row++;
    $sheet->getStyle('F' . $row)->applyFromArray($horRight);
    $sheet->setCellValue('F' . $row, "Date :");
    $sheet->setCellValue('G' . $row, long_date(date("Y-m-d", $rs->Date)));
    $sheet->setCellValue('A' . $row, "Jl. Pierre Tendean Boulevard Kawasan Rekasys Manado 95111");

    $row++;
    $sheet->setCellValue('A' . $row, "Telp. ( 0431 ) 879889  Fax ( 0431 ) 879823");

    $row++;
    $sheet->mergeCellsByColumnAndRow(0, ($row + 1), 0 + 6, ($row + 1));
    $sheet->getStyle('A' . ($row + 1))->applyFromArray($horCenter);
    $sheet->getStyle('A' . ($row + 1))->getFont()->setSize(12);
    $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
    $sheet->setCellValue('A'. ($row + 1), "PURCHASE ORDER");

    $row++;
    $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
    $sheet->setCellValue('A' . ($row + 1), "To :");
    $sheet->setCellValue('B' . ($row + 1), $rs->Supplier->CreditorName);
    $sheet->getStyle('F' . ($row + 1))->getFont()->setBold(true);
    $sheet->getStyle('F' . ($row + 1))->applyFromArray($horRight);
    $sheet->setCellValue('F' . ($row + 1), "NPWP :");
    $sheet->setCellValue('G' . ($row + 1), $rs->Supplier->Npwp);

    $row++;
    $sheet->getStyle('F' . ($row + 1))->getFont()->setBold(true);
    $sheet->getStyle('F' . ($row + 1))->applyFromArray($horRight);
    $sheet->setCellValue('F' . ($row + 1), "Telp :");
    //$sheet->setCellValue('G' . ($row + 1), $rs->Supplier->TelNo);

    $row++;
    $sheet->getStyle('F' . ($row + 1))->getFont()->setBold(true);
    $sheet->getStyle('F' . ($row + 1))->applyFromArray($horRight);
    $sheet->setCellValue('F' . ($row + 1), "Fax :");
    $sheet->setCellValue('G' . ($row + 1), $rs->Supplier->FaxNo);

    $row++;
    $line3 = 'A' . ($row + 2);
    $line4 = 'G' . ($row + 2);
    $sheet->getStyle("$line3:$line4")->getFont()->setBold(true);
    $sheet->getStyle("$line3:$line4")->applyFromArray($border);
    $sheet->getStyle("$line3:$line4")->applyFromArray($hoverCenter);

    $sheet->setCellValue('A' . ($row + 2), "No.");
    $sheet->setCellValue('B' . ($row + 2), "No.PR");
    $sheet->setCellValue('C' . ($row + 2), "Item Code");
    $sheet->setCellValue('D' . ($row + 2), "Item Description");
    $sheet->setCellValue('E' . ($row + 2), "Amount");
    $sheet->setCellValue('F' . ($row + 2), "Price");
    $sheet->setCellValue('G' . ($row + 2), "Total");

    $i = 0;
    $sumTotal = 0;
    foreach ($rs->Details as $result) {
        $i++;

        $price = $rs->IsVat == 1 && $rs->IsIncludeVat == 1 ? $result->Price / 1.1 : $result->Price;
        $total = $result->Qty * $price;

        $row++;
        $line1 = 'A' . ($row + 2);
        $line2 = 'G' . ($row + 2);
        $sheet->getStyle("$line1:$line2")->applyFromArray($border);

        $sheet->getStyle('A' . ($row + 2))->applyFromArray($horCenter);
        $sheet->setCellValue('A' . ($row + 2), $i);
        $sheet->setCellValue('B' . ($row + 2), $result->PrCode);
        $sheet->setCellValue('C' . ($row + 2), $result->ItemCode);
        $sheet->setCellValue('D' . ($row + 2), $result->ItemDescription);
        $sheet->setCellValue('E' . ($row + 2), $result->Qty . " " . $result->UomCd);
        $sheet->getStyle('F'.($row + 2))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('F' . ($row + 2), $price);
        $sheet->getStyle('G'.($row + 2))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
        $sheet->setCellValue('G' . ($row + 2), $total);

        $sumTotal =  $sumTotal + $total;
    }

    $ppn = $rs->IsVat == 1 ? $sumTotal * 0.1 : 0;
    $AmountPaid = $sumTotal + $ppn;

    $row++;
    $sheet->getStyle('F' . ($row + 2))->getFont()->setBold(true);
    $sheet->setCellValue('F' . ($row + 2), "TOTAL");
    $sheet->getStyle('G' . ($row + 2))->applyFromArray($border);
    $sheet->getStyle('G'. ($row + 2))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
    $sheet->setCellValue('G'. ($row + 2), $sumTotal);

    $row++;
    $sheet->getStyle('F' . ($row + 2))->getFont()->setBold(true);
    $sheet->setCellValue('F' . ($row + 2), "VAT (PPN)");
    $sheet->getStyle('G' . ($row + 2))->applyFromArray($border);
    $sheet->getStyle('G'. ($row + 2))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
    $sheet->setCellValue('G'. ($row + 2), $ppn);

    $row++;
    $sheet->getStyle('F' . ($row + 2))->getFont()->setBold(true);
    $sheet->setCellValue('F' . ($row + 2), "AMOUNT PAID");
    $sheet->getStyle('G' . ($row + 2))->applyFromArray($border);
    $sheet->getStyle('G'. ($row + 2))->getNumberFormat()->setFormatCode('[$Rp-421]#,###.00;([$Rp-421]#,###.00)');
    $sheet->setCellValue('G'. ($row + 2), $AmountPaid);

    $row++;
    $sheet->mergeCellsByColumnAndRow(0, $row + 2, 0 + 3, $row + 3);
    $sheet->getStyle('A' . ($row + 2))->applyFromArray($jusCenter);
    $sheet->getStyle('A' . ($row + 2))->getAlignment()->setWrapText(true);
    $sheet->setCellValue('A' . ($row + 2), "Remarks : " . "$rs->Note");

    $row++;
    $sheet->setCellValue("A" . ($row + 3), "Note :");
    $sheet->getStyle('E' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue("E" . ($row + 3), "Confirmed By :");
    $sheet->getStyle('F' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue("F" . ($row + 3), "Issued By :");
    $sheet->getStyle('G' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue("G" . ($row + 3), "Approved By :");

    $row++;
    $sheet->getStyle('A' . ($row + 3))->applyFromArray($horCenter);
    $sheet->setCellValue('A' . ($row + 3), "1.");

    $sheet->mergeCellsByColumnAndRow(1, $row + 3, 1 + 1, $row + 4);
    $sheet->getStyle('B' . ($row + 3))->applyFromArray($jusCenter);
    $sheet->setCellValue('B' . ($row + 3), "If The Quality and specification did not meet  the qualification,it will be returned of seller expenses");

    $row++;
    $sheet->getStyle('A' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue('A' . ($row + 4), "2.");
    $sheet->setCellValue('B' . ($row + 4), "Please attached the original Purchase Order (PO)");

    $row++;
    $sheet->setCellValue('A' . ($row + 4), "Condition :");

    $row++;
    $sheet->getStyle('A' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue('A' . ($row + 4), "1.");
    $sheet->setCellValue('B' . ($row + 4), "Delivery Date : " . long_date(date("Y-m-d", $rs->ExpectedDate)));

    $row++;
    $sheet->getStyle('A' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue('A' . ($row + 4), "2.");
    $sheet->setCellValue('B' . ($row + 4), "Term Of Payment : " . $rs->PaymentTerms);
    $sheet->getStyle('E' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue("E" . ($row + 4), $rs->Supplier->CreditorName);
    $sheet->getStyle('F' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue("F" . ($row + 4), $rs->CreatedUser->UserName);
    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "";
    $sheet->getStyle('G' . ($row + 4))->applyFromArray($horCenter);
    $sheet->setCellValue("G" . ($row + 4), $approvedUser);

    $row++;
    $sheet->getStyle('A'. ($row + 5))->getFont()->setBold(true);
    $sheet->setCellValue('A'. ($row + 5), "Original - Supplier   Copy 1 - Account Payable   Copy 2 - Purchasing");
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Nota PO.xls"');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;