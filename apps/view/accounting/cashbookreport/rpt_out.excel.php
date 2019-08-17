<?php
/** @var $start int */ /** @var $end int */ /** @var $showNo bool */ /** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */
$phpExcel = new PHPExcel();
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("Report Cash-Bank Out")->setCompany("Rekasys Corporation");

$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Report Cash-Bank Out");

$sheet->setCellValue("A1", sprintf("%s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:H1");
$sheet->getStyle("A1")->applyFromArray(array(
    "font" => array("bold" => true, "size" => 20)
));
$sheet->setCellValue("A2", "Jurnal Cash-Bank Out");
$sheet->mergeCells("A2:H2");
$sheet->setCellValue("A3", sprintf("Periode : %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));
$sheet->mergeCells("A3:H3");
$sheet->getStyle("A2:A3")->applyFromArray(array(
    "font" => array("size" => 14)
));

// Column Header
$sheet->setCellValue("A5", "Tgl.");
$sheet->setCellValue("B5", "No. Voucher");
$sheet->setCellValue("C5", "Company");
$sheet->setCellValue("D5", "Uraian");
$sheet->setCellValue("E5", "Debet");
$sheet->setCellValue("G5", "Kredit");
$sheet->setCellValue("E6", "Akun");
$sheet->setCellValue("F6", "Jumlah");
$sheet->setCellValue("G6", "Akun");
$sheet->setCellValue("H6", "Jumlah");
$sheet->mergeCells("A5:A6");
$sheet->mergeCells("B5:B6");
$sheet->mergeCells("C5:C6");
$sheet->mergeCells("D5:D6");
$sheet->mergeCells("E5:F5");
$sheet->mergeCells("G5:H5");
$sheet->getStyle("A5:H6")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,"vertical" => PHPExcel_Style_Alignment::VERTICAL_CENTER),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_DOUBLE),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));
for ($i = 0; $i < 10; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Tulis Data...
$counter = 0;
$prevDate = null;
$prevVoucherNo = null;
$prevSbu = null;
$brs = 6;
$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;
while ($row = $report->FetchAssoc()) {
    // Convert datetime jadi native format
    $row["voucher_date"] = strtotime($row["voucher_date"]);
    $counter++;
    $className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
    if ($prevDate != $row["voucher_date"]) {
        $prevDate = $row["voucher_date"];
        $flagDate = true;
    } else {
        $flagDate = false;
    }

    if ($prevVoucherNo != $row["doc_no"]) {
        $prevVoucherNo = $row["doc_no"];
        $flagVoucherNo = true;
    } else {
        $flagVoucherNo = false;
    }

    if ($prevSbu != $row["entity_cd"]) {
        $prevSbu = $row["entity_cd"];
        $flagSbu = true;
    } else {
        $flagSbu = false;
    }
    $brs++;
    $sheet->setCellValue("A$brs", $flagDate ? date("d", $prevDate) : "");
    $sheet->setCellValue("B$brs", $prevVoucherNo);
    $sheet->setCellValue("C$brs", $prevSbu);
    $sheet->setCellValue("D$brs", $row["note"]);
    $sheet->setCellValue("E$brs", $showNo ? $row["acc_no_debit"] : $row["acc_debit"]);
    $sheet->setCellValue("F$brs", $row["amount"]);
    $sheet->setCellValue("G$brs", $showNo ? $row["acc_no_credit"] : $row["acc_credit"]);
    $sheet->setCellValue("H$brs", $row["amount"], 2);
}
// Grand Total
$brs++;
$sheet->setCellValue("A$brs", "GRAND TOTAL: ");
$sheet->mergeCells("A$brs:D$brs");
$sheet->setCellValue("F$brs", "=SUM(F6:F" . ($brs - 1) . ")");
$sheet->setCellValue("H$brs", "=SUM(H6:H" . ($brs - 1) . ")");
$sheet->getStyle("A$brs:H$brs")->applyFromArray(array(
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
$sheet->getStyle("G5:G$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H5:H$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E5:H5")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F6:F$brs")->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle("H6:H$brs")->getNumberFormat()->setFormatCode('#,##0.00');
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);

// Sent header
header('Content-Type: application/vnd.ms-excel');
header(sprintf('Content-Disposition: attachment;filename="jurnal-cashbank-out.xls"'));
header('Cache-Control: max-age=0');

// Write to php output
$writer = new PHPExcel_Writer_Excel5($phpExcel);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// EoF: rpt_in.excel.php
