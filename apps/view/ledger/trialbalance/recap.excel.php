<?php /** @var $company Company */ /** @var $monthNames string[] */  /** @var $month int */ /** @var $year int */ /** @var $report null|ReaderBase */

$phpExcel = new PHPExcel();
$headers = array(
	'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="trial-balance.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Excel MetaData
$phpExcel->getProperties()->setCreator("Rekasys System (c) Wayan Budiasa 2018")->setTitle("Trial Balance")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Trial Balance");

//Bikin Header
$sheet->setCellValue("A1","TRIAL BALANCE - ".$company->CompanyName);
$sheet->mergeCells("A1:H1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", sprintf("S/D. Bulan : %s %s", $monthNames[$month], $year));
$sheet->mergeCells("A2:H2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));
for ($i = 0; $i < 8; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Bikin Kolom
$sheet->setCellValue("A4", "Kode Perkiraan");
$sheet->setCellValue("B4", "Nama Perkiraan");
$sheet->setCellValue("C4", "Opening Balance");
$sheet->setCellValue("E4", sprintf("Mutasi %s %s", $monthNames[$month], $year));
$sheet->setCellValue("G4", "Closing Balance");
$sheet->setCellValue("C5", "Debet");
$sheet->setCellValue("D5", "Kredit");
$sheet->setCellValue("E5", "Debet");
$sheet->setCellValue("F5", "Kredit");
$sheet->setCellValue("G5", "Debet");
$sheet->setCellValue("H5", "Kredit");
$sheet->mergeCells("A4:A5");
$sheet->mergeCells("B4:B5");
$sheet->mergeCells("C4:D4");
$sheet->mergeCells("E4:F4");
$sheet->mergeCells("G4:H4");
$sheet->getStyle("A4:H5")->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	),
	"borders" => array("allborders" => array(
		"style" => PHPExcel_Style_Border::BORDER_THIN
	))
));
// warna background
$sheet->getStyle("A4:H5")->applyFromArray(array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFFFFF00')
    )
));
// Tulis Data
// disimpan ke array dulu
$data = array();
while ($row = $report->FetchAssoc()) {
    $data[] = $row;
}
$sumDebitTrx = 0;
$sumCreditTrx = 0;
$sumDebitObl = 0;
$sumCreditObl = 0;
$sumDebitCbl = 0;
$sumCreditCbl = 0;
$line = 5;
foreach ($data as $row) {
    $oblDebit = 0;
    $oblCredit = 0;
    $cblDebit = 0;
    $cblCredit = 0;
    $trxDebit = 0;
    $trxCredit = 0;
    $className = "";

    if ($row["acc_level"] == 0) {
        $className = "level0";
        foreach ($data as $temp) {
            if ($temp["level0"] == $row["id"]) {
                if ($row["dc_saldo"] == 'D') {
                    $oblDebit += ($temp["bal_debit_amt"] + $temp["total_debit_prev"]) - $temp["total_credit_prev"];
                }else {
                    $oblCredit += ($temp["bal_credit_amt"] + $temp["total_credit_prev"]) - $temp["total_debit_prev"];
                }
                $trxDebit += $temp["total_debit"];
                $trxCredit += $temp["total_credit"];
            }
        }
    } else if ($row["acc_level"] == 1) {
        $className = "level1";
        foreach ($data as $temp) {
            if ($temp["level1"] == $row["id"]) {
                if ($row["dc_saldo"] == 'D') {
                    $oblDebit += ($temp["bal_debit_amt"] + $temp["total_debit_prev"]) - $temp["total_credit_prev"];
                }else {
                    $oblCredit += ($temp["bal_credit_amt"] + $temp["total_credit_prev"]) - $temp["total_debit_prev"];
                }
                $trxDebit += $temp["total_debit"];
                $trxCredit += $temp["total_credit"];
            }
        }
    } else if ($row["acc_level"] == 2) {
        $className = "level2";
        foreach ($data as $temp) {
            if ($temp["level2"] == $row["id"]) {
                if ($row["dc_saldo"] == 'D') {
                    $oblDebit += ($temp["bal_debit_amt"] + $temp["total_debit_prev"]) - $temp["total_credit_prev"];
                }else {
                    $oblCredit += ($temp["bal_credit_amt"] + $temp["total_credit_prev"]) - $temp["total_debit_prev"];
                }
                $trxDebit += $temp["total_debit"];
                $trxCredit += $temp["total_credit"];
            }
        }
    } else {
        $className = "level3";
        if ($row["dc_saldo"] == 'D') {
            $oblDebit = ($row["bal_debit_amt"] + $row["total_debit_prev"]) - $row["total_credit_prev"];
        }else{
            $oblCredit = ($row["bal_credit_amt"] + $row["total_credit_prev"]) - $row["total_debit_prev"];
        }
        $trxDebit += $row["total_debit"];
        $trxCredit += $row["total_credit"];
        $sumDebitTrx += $trxDebit;
        $sumCreditTrx += $trxCredit;
        if($isIncOb == 1){
            $sumDebitObl += $oblDebit;
            $sumCreditObl += $oblCredit;
            if ($row["dc_saldo"] == 'D') {
                $sumDebitCbl += $trxDebit + $oblDebit - $trxCredit;
            }else{
                $sumCreditCbl += $trxCredit + $oblCredit - $trxDebit;
            }
        }else{
            if ($row["dc_saldo"] == 'D') {
                $sumDebitCbl += $trxDebit - $trxCredit;
            }else{
                $sumCreditCbl += $trxCredit - $trxDebit;
            }
        }
    }
    if($isIncOb){
        $cblDebit = $oblDebit + $trxDebit;
        $cblCredit = $oblCredit + $trxCredit;
    }else{
        $cblDebit = $trxDebit;
        $cblCredit = $trxCredit;
    }
    if(($isIncHd == 0 && $row["acc_level"]< 3) || ($cblDebit == 0 && $cblCredit == 0)){
        // jangan tampilkan
    }else{
        $line++;
        $sheet->setCellValue("A$line", $row["acc_no"]);
        $sheet->setCellValue("B$line", $row["acc_name"]);
        $sheet->setCellValue("C$line", $isIncOb ? $oblDebit : 0);
        $sheet->setCellValue("D$line", $isIncOb ? $oblCredit : 0);
        $sheet->setCellValue("E$line", $trxDebit);
        $sheet->setCellValue("F$line", $trxCredit);
        $sheet->setCellValue("G$line", $isIncOb ? $oblDebit + $trxDebit : $trxDebit);
        $sheet->setCellValue("H$line", $isIncOb ? $oblCredit + $trxCredit : $trxCredit);
        if($row["acc_level"] == 2){
           // warna background
            $sheet->getStyle("A$line:H$line")->applyFromArray(array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FF99CCFF')
                )
            ));
        }elseif($row["acc_level"] == 1){
            // warna background
            $sheet->getStyle("A$line:H$line")->applyFromArray(array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FF6682FF')
                )
            ));
        }elseif($row["acc_level"] ==0){
            // warna background
            $sheet->getStyle("A$line:H$line")->applyFromArray(array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FF0080FF')
                )
            ));
        }else{
            // warna background
            $sheet->getStyle("A$line:H$line")->applyFromArray(array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FFCCE5FF')
                )
            ));
        }
    }
}
// SUM
$line++;
$flagCyclic = ($row == 6);
$sheet->setCellValue("A$line", "GRAND TOTAL : ");
$sheet->mergeCells("A$line:B$line");
if($isIncHd){
    $sheet->setCellValue("C$line", $flagCyclic ? "0" : $sumDebitObl);
    $sheet->setCellValue("D$line", $flagCyclic ? "0" : $sumCreditObl);
    $sheet->setCellValue("E$line", $flagCyclic ? "0" : $sumDebitTrx);
    $sheet->setCellValue("F$line", $flagCyclic ? "0" : $sumCreditTrx);
    $sheet->setCellValue("G$line", $flagCyclic ? "0" : $sumDebitCbl);
    $sheet->setCellValue("H$line", $flagCyclic ? "0" : $sumCreditCbl);
}else{
    $sheet->setCellValue("C$line", $flagCyclic ? "0" : "=SUM(C6:C".($line-1).")");
    $sheet->setCellValue("D$line", $flagCyclic ? "0" : "=SUM(D6:D".($line-1).")");
    $sheet->setCellValue("E$line", $flagCyclic ? "0" : "=SUM(E6:E".($line-1).")");
    $sheet->setCellValue("F$line", $flagCyclic ? "0" : "=SUM(F6:F".($line-1).")");
    $sheet->setCellValue("G$line", $flagCyclic ? "0" : "=SUM(G6:G".($line-1).")");
    $sheet->setCellValue("H$line", $flagCyclic ? "0" : "=SUM(H6:H".($line-1).")");
}

// warna background
$sheet->getStyle("A$line:H$line")->applyFromArray(array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFFFFF00')
    )
));

$sheet->getStyle("A$line:H$line")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A$line:H$line")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// Styling
$sheet->getStyle("C6:H$line")->getNumberFormat()->setFormatCode("#,##0.00");
$sheet->getStyle("A6:A$line")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A6:A$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B6:B$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C6:C$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D6:D$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E6:E$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F6:F$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G6:G$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H6:H$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);

// Flush to client
foreach ($headers as $header) {
	header($header);
}
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// EoF: recap.excel.php