<?php
/** @var $obal OpeningBalance */ /** @var $obalTransaction array */
/** @var $accountId int */ /** @var $accounts Coa[] */ /** @var $start int */ /** @var $end int */ /** @var $status string */ /** @var $report ReaderBase */
/** @var $output string */ /** @var $orientation int */ /** @var $company Company */

// OK mari kita buat PDF nya (selectedAccountnya... harus dicari manual)
/** @var $selectedAccount Coa|null */
$selectedAccount = null;
foreach ($accounts as $account) {
    if ($account->Id == $accountId) {
        $selectedAccount = $account;
        break;
    }
}
$phpExcel = new PHPExcel();
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("BKK - BKM")->setCompany("Rekasys Corporation");

$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Daftar BKK - BKM");

$sheet->setCellValue("A1", sprintf("%s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:I1");
$sheet->getStyle("A1")->applyFromArray(array(
    "font" => array("bold" => true, "size" => 20)
));
$sheet->setCellValue("A2", "Daftar BKK - BKM");
$sheet->mergeCells("A2:I2");
$sheet->setCellValue("A3", sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));
$sheet->mergeCells("A3:I3");
$sheet->setCellValue("A4", sprintf("Akun: %s - %s", $selectedAccount->AccNo, $selectedAccount->AccName));
$sheet->mergeCells("A4:I4");
$sheet->getStyle("A2:A4")->applyFromArray(array(
    "font" => array("size" => 14)
));

// Column Header
$sheet->setCellValue("A6", "Tgl.");
$sheet->setCellValue("B6", "No. Voucher");
$sheet->setCellValue("C6", "Kontra Pos");
$sheet->setCellValue("D6", "Uraian");
$sheet->setCellValue("E6", "Project");
$sheet->setCellValue("F6", "Dept");
$sheet->setCellValue("G6", "Debet");
$sheet->setCellValue("H6", "Kredit");
$sheet->setCellValue("I6", "Saldo");
$sheet->getStyle("A6:I6")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));
for ($i = 0; $i < 9; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Tulis Data...

$prevDate = null;
$prevVoucherNo = null;

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;

$counter = 0;
$subTotalDebit = 0;
$subTotalCredit = 0;
$totalDebit = 0;
$totalCredit = 0;
$saldo = $obal == null ? 0 : $obalTransaction["saldo"];

// Saldo awal
$sheet->setCellValue("D7", "Saldo Awal " . date(HUMAN_DATE, $start));
$sheet->setCellValue("G7", $selectedAccount->DcSaldo == "D" ? $obalTransaction["saldo"] :0);
$sheet->setCellValue("H7", $selectedAccount->DcSaldo == "K" ? $obalTransaction["saldo"] :0);
$brs = 7;
foreach ($report as $master) {
    foreach ($master["details"] as $row) {
        $counter++;
        $brs++;
        if ($prevDate != $row["voucher_date"]) {
            if ($counter > 1) {
                // Sudah pernah ada data yang ditulis
                $totalDebit += $subTotalDebit;
                $totalCredit += $subTotalCredit;

                $sheet->setCellValue("A$brs", date("d F", $prevDate));

            }

            $prevDate = $row["voucher_date"];
            $flagDate = true;

            $subTotalDebit = 0;
            $subTotalCredit = 0;
        } else {
            $flagDate = false;
        }

        if ($prevVoucherNo != $row["doc_no"]) {
            $prevVoucherNo = $row["doc_no"];
            $flagVoucherNo = true;
        } else {
            $flagVoucherNo = false;
        }

        $subTotalDebit += $row["debit"];
        $subTotalCredit += $row["credit"];
        if ($selectedAccount->DcSaldo == "D") {
            $saldo += ($row["debit"] - $row["credit"]);
        } else {
            $saldo += ($row["credit"] - $row["debit"]);
        }

        $sheet->setCellValue("A$brs",$flagDate ? date("d", $prevDate) : "");
        $sheet->setCellValue("B$brs", $flagVoucherNo ? $prevVoucherNo : "");
        $sheet->setCellValue("C$brs", $row["opposite_no"]);
        $sheet->setCellValue("D$brs", $row["note"]);
        $sheet->setCellValue("E$brs", $row["project_cd"]);
        $sheet->setCellValue("F$brs", $row["dept_code"]);
        $sheet->setCellValue("G$brs", $row["debit"]);
        $sheet->setCellValue("H$brs", $row["credit"]);
        $sheet->setCellValue("I$brs", $saldo);
    }
}

// Grand Total
$brs++;
$sheet->setCellValue("A$brs", "GRAND TOTAL: ");
$sheet->mergeCells("A$brs:F$brs");
$sheet->setCellValue("G$brs", "=SUM(G7:G" . ($brs - 1) . ")");
$sheet->setCellValue("H$brs", "=SUM(H7:H" . ($brs - 1) . ")");
$sheet->getStyle("A$brs:I$brs")->applyFromArray(array(
    "font" => array("bold" => true),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));

// Saldo Akhir
$brs++;
$sheet->setCellValue("A$brs", "SALDO AKHIR: ");
$sheet->mergeCells("A$brs:F$brs");
$sheet->setCellValue("G$brs", $selectedAccount->DcSaldo == "DK" ? "=G" . ($brs - 1) . "-H" . ($brs - 1) : "");
$sheet->setCellValue("H$brs", $selectedAccount->DcSaldo == "KD" ? "=H" . ($brs - 1) . "-G" . ($brs - 1) : "");
$sheet->getStyle("A$brs:I$brs")->applyFromArray(array(
    "font" => array("bold" => true),
    "borders" => array(
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));

// Border Styling
$sheet->getStyle("A6:A$brs")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A6:A$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B6:B$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C6:C$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D6:D$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E6:E$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F6:F$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G6:G$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H6:H$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("I6:I$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G6:G$brs")->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle("H6:H$brs")->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle("I6:I$brs")->getNumberFormat()->setFormatCode('#,##0.00');

// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);

// Sent header
header('Content-Type: application/vnd.ms-excel');
header(sprintf('Content-Disposition: attachment;filename="bkk-bkm-%s.xls"', $selectedAccount->AccName));
header('Cache-Control: max-age=0');

// Write to php output
$writer = new PHPExcel_Writer_Excel5($phpExcel);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// EoF: bkk_bkm.excel.php
