<?php
/** @var $accountId int */ /** @var $accounts array */ /** @var $start int */ /** @var $end int */ /** @var $openingBalance null|OpeningBalance */
/** @var $transaction null|array */ /** @var $report null|ReaderBase */ /** @var $output string */ /** @var $company Company */
$haveData = $openingBalance != null;

// OK mari kita buat PDF nya (selectedAccountnya... harus dicari manual)
/** @var $selectedAccount Coa */
$selectedAccount = null;
foreach ($accounts as $row) {
    /** @var $account Coa */
    foreach ($row["SubAccounts"] as $account) {
        if ($account->Id == $accountId) {
            $selectedAccount = $account;
            break;
        }
    }
}
$phpExcel = new PHPExcel();
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("CASH FLOW DETAIL")->setCompany("Rekasys Corporation");

$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("LAPORAN CASH FLOW DETAIL");

$sheet->setCellValue("A1", sprintf("%s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:I1");
$sheet->getStyle("A1")->applyFromArray(array(
    "font" => array("bold" => true, "size" => 20)
));
$sheet->setCellValue("A2", "LAPORAN CASH FLOW DETAIL");
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
$sheet->setCellValue("C6", "Uraian");
$sheet->setCellValue("D6", "Project");
$sheet->setCellValue("E6", "Dept");
$sheet->setCellValue("F6", "Debet");
$sheet->setCellValue("G6", "Kredit");
$sheet->setCellValue("H6", "Saldo");
$sheet->getStyle("A6:H6")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_DOUBLE),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));
for ($i = 0; $i < 8; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Tulis Data...
$counter = 0;
$prevDate = null;
$prevVoucherNo = null;

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;
$saldo = $haveData ? $transaction["saldo"] : 0;

$subTotalDebit = 0;
$subTotalCredit = 0;
$totalDebit = 0;
$totalCredit = 0;
// saldo awal tulis duluan
$sheet->setCellValue("A7",date("d", $start));
$sheet->setCellValue("C7","Saldo Awal ".date(HUMAN_DATE, $start));
$sheet->setCellValue("F7",($haveData && $openingBalance->AccountDcSaldo == "D") ? $transaction["saldo"] : 0);
$sheet->setCellValue("G7",($haveData && $openingBalance->AccountDcSaldo == "K") ? $transaction["saldo"] : 0);
$sheet->setCellValue("H7",$haveData ? $transaction["saldo"] : 0);
$brs = 7;
while ($row = $report->FetchAssoc()) {
    // Convert datetime jadi native format
    $row["voucher_date"] = strtotime($row["voucher_date"]);
    $counter++;
    $className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
    if ($prevDate != $row["voucher_date"]) {
        if ($prevDate != null) {
            // OK sudah ganti baris kita harus bikin subTotal dahulu
            $brs++;
            $sheet->mergeCells("A$brs:E$brs");
            $sheet->setCellValue("A$brs",sprintf("Sub Total %s :", date(HUMAN_DATE, $prevDate)));
            $sheet->setCellValue("F$brs",$subTotalDebit);
            $sheet->setCellValue("G$brs",$subTotalCredit);
            $sheet->getStyle("A$brs:H$brs")->applyFromArray(array(
                "font" => array("bold" => true),
                "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
                "borders" => array(
                    "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
                    "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
                )
            ));
            $totalDebit += $subTotalDebit;
            $totalCredit += $subTotalCredit;
            $subTotalDebit = 0;
            $subTotalCredit = 0;
        }
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
    $brs++;
    $debit = $row["acc_debit_id"] == $accountId ? $row["amount"] : 0;
    $credit = $row["acc_credit_id"] == $accountId ? $row["amount"] : 0;
    $saldo = $saldo + $debit - $credit;
    $subTotalDebit += $debit;
    $subTotalCredit += $credit;
    $sheet->setCellValue("A$brs",$flagDate ? date("d", $prevDate) : "");
    $sheet->setCellValue("B$brs",$flagVoucherNo ? $prevVoucherNo : "");
    $sheet->setCellValue("C$brs",$row["note"]);
    $sheet->setCellValue("D$brs",$row["project_cd"]);
    $sheet->setCellValue("E$brs",$row["dept_code"]);
    $sheet->setCellValue("F$brs",$debit);
    $sheet->setCellValue("G$brs",$credit);
    $sheet->setCellValue("H$brs",$saldo);
}
// Baris terakhir yang terlupakan
if ($prevDate != null) {
    // OK sudah ganti baris kita harus bikin subTotal dahulu
    $brs++;
    $sheet->mergeCells("A$brs:E$brs");
    $sheet->setCellValue("A$brs",sprintf("Sub Total %s :", date(HUMAN_DATE, $prevDate)));
    $sheet->setCellValue("F$brs",$subTotalDebit);
    $sheet->setCellValue("G$brs",$subTotalCredit);
    $sheet->getStyle("A$brs:H$brs")->applyFromArray(array(
        "font" => array("bold" => true),
        "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
        "borders" => array(
            "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
            "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
        )
    ));
    $totalDebit += $subTotalDebit;
    $totalCredit += $subTotalCredit;
    $subTotalDebit = 0;
    $subTotalCredit = 0;
}

// Grand Total
$brs++;
$sheet->mergeCells("A$brs:E$brs");
$sheet->setCellValue("A$brs","Grand Total ");
$sheet->setCellValue("F$brs",$totalDebit);
$sheet->setCellValue("G$brs",$totalCredit);
$sheet->getStyle("A$brs:H$brs")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
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
$sheet->getStyle("F7:H$brs")->getNumberFormat()->setFormatCode('#,##0.00');

// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);

// Sent header
header('Content-Type: application/vnd.ms-excel');
header(sprintf('Content-Disposition: attachment;filename="cash-flow-detail-%s.xls"', $selectedAccount->AccName));
header('Cache-Control: max-age=0');

// Write to php output
$writer = new PHPExcel_Writer_Excel5($phpExcel);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// EoF: bkk_bkm.excel.php
