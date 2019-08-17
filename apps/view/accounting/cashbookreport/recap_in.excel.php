<?php

$phpExcel = new PHPExcel();
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("Rekap Cash-Bank In Per Akun")->setCompany("Rekasys Corporation");

$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekap Cash-Bank In Per Akun");

$sheet->setCellValue("A1", sprintf("%s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:F1");
$sheet->getStyle("A1")->applyFromArray(array(
    "font" => array("bold" => true, "size" => 20)
));
$sheet->setCellValue("A2", "Rekap Cash/Bank In Per Akun");
$sheet->mergeCells("A2:F2");
$sheet->setCellValue("A3", sprintf("Periode: %s %s", $monthNames[$month], $year));
$sheet->mergeCells("A3:F3");
$sheet->getStyle("A2:A3")->applyFromArray(array(
    "font" => array("size" => 14)
));

// Column Header
$sheet->setCellValue("A5", "No. Akun");
$sheet->setCellValue("B5", "Nama Akun");
$sheet->setCellValue("C5", sprintf("Mutasi %s %s", $monthNames[$month], $year));
$sheet->setCellValue("E5", sprintf("Jumlah s/d. %s %s", $monthNames[$month], $year));
$sheet->setCellValue("C6", "Debet");
$sheet->setCellValue("D6", "Kredit");
$sheet->setCellValue("E6", "Debet");
$sheet->setCellValue("F6", "Kredit");
$sheet->mergeCells("A5:A6");
$sheet->mergeCells("B5:B6");
$sheet->mergeCells("C5:D5");
$sheet->mergeCells("E5:F5");
$sheet->getStyle("A5:F6")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,"vertical" => PHPExcel_Style_Alignment::VERTICAL_CENTER),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_DOUBLE),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));
for ($i = 0; $i < 6; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Tulis Data...
$sumDebit = 0;
$sumCredit = 0;
$sumAllDebit = 0;
$sumAllCredit = 0;
$brs = 6;
while($row = $report->FetchAssoc()) {
    $brs++;
    $sumDebit += $row["total_debit"];
    $sumCredit += $row["total_credit"];
    $sumAllDebit += $row["total_debit"] + $row["total_debit_prev"];
    $sumAllCredit += $row["total_credit"] + $row["total_credit_prev"];
    $sheet->setCellValue("A$brs",$row["acc_no"]);
    $sheet->setCellValue("B$brs",$row["acc_name"]);
    $sheet->setCellValue("C$brs",$row["total_debit"]);
    $sheet->setCellValue("D$brs",$row["total_credit"]);
    $sheet->setCellValue("E$brs",$row["total_debit"] + $row["total_debit_prev"]);
    $sheet->setCellValue("F$brs",$row["total_credit"] + $row["total_credit_prev"]);
}
// Grand Total
$brs++;
$sheet->setCellValue("A$brs", "GRAND TOTAL: ");
$sheet->mergeCells("A$brs:B$brs");
$sheet->setCellValue("C$brs", "=SUM(C6:C" . ($brs - 1) . ")");
$sheet->setCellValue("D$brs", "=SUM(D6:D" . ($brs - 1) . ")");
$sheet->setCellValue("E$brs", "=SUM(E6:E" . ($brs - 1) . ")");
$sheet->setCellValue("F$brs", "=SUM(F6:F" . ($brs - 1) . ")");
$sheet->getStyle("A$brs:F$brs")->applyFromArray(array(
    "font" => array("bold" => true),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));

// Border Styling
$sheet->getStyle("A5:A$brs")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A5:A$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B5:B$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C5:C$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D5:D$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E5:E$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F5:F$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C5:F5")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C6:F$brs")->getNumberFormat()->setFormatCode('#,##0.00');

// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);

// Sent header
header('Content-Type: application/vnd.ms-excel');
header(sprintf('Content-Disposition: attachment;filename="rekap-cashbank-in-%s %s.xls"', $monthNames[$month], $year));
header('Cache-Control: max-age=0');

// Write to php output
$writer = new PHPExcel_Writer_Excel5($phpExcel);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// EoF: bkk_bkm.excel.php
