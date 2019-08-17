<?php /** @var $company Company */ /** @var $monthNames string[] */  /** @var $month int */ /** @var $year int */ /** @var $report null|ReaderBase */

$phpExcel = new PHPExcel();
$headers = array(
	'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="worksheet-balance.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Excel MetaData
$phpExcel->getProperties()->setCreator("PMS System (c) Wayan Budiasa 2013")->setTitle("Worksheet Balance")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Trial Balance");

//Bikin Header
$sheet->setCellValue("A1","WORKSHEET BALANCE - ".$company->CompanyName);
$sheet->mergeCells("A1:R1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", sprintf("%s : %s %s",$rekapMethod ? "Bulan" : "S/d. Bulan ", $monthNames[$month], $year));
$sheet->mergeCells("A2:R2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));
for ($i = 0; $i < 18; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Bikin Kolom
$sheet->setCellValue("A4", "Kode Perkiraan");
$sheet->setCellValue("B4", "Nama Perkiraan");
$sheet->setCellValue("C4", "Opening Balance");
$sheet->setCellValue("E4", "Mutasi Kas (BKK/BKM)");
$sheet->setCellValue("G4", "Accrual (BJK/BPK)");
$sheet->setCellValue("I4", "After Accrual");
$sheet->setCellValue("K4", "Adjustment (BPB/BMM)");
$sheet->setCellValue("M4", "After Adjusment)");
$sheet->setCellValue("O4", "Profit/Loss");
$sheet->setCellValue("Q4", "Balance Statement");
$sheet->setCellValue("C5", "Debet");
$sheet->setCellValue("D5", "Kredit");
$sheet->setCellValue("E5", "Debet");
$sheet->setCellValue("F5", "Kredit");
$sheet->setCellValue("G5", "Debet");
$sheet->setCellValue("H5", "Kredit");
$sheet->setCellValue("I5", "Debet");
$sheet->setCellValue("J5", "Kredit");
$sheet->setCellValue("K5", "Debet");
$sheet->setCellValue("L5", "Kredit");
$sheet->setCellValue("M5", "Debet");
$sheet->setCellValue("N5", "Kredit");
$sheet->setCellValue("O5", "Debet");
$sheet->setCellValue("P5", "Kredit");
$sheet->setCellValue("Q5", "Debet");
$sheet->setCellValue("R5", "Kredit");
$sheet->mergeCells("A4:A5");
$sheet->mergeCells("B4:B5");
$sheet->mergeCells("C4:D4");
$sheet->mergeCells("E4:F4");
$sheet->mergeCells("G4:H4");
$sheet->mergeCells("I4:J4");
$sheet->mergeCells("K4:L4");
$sheet->mergeCells("M4:N4");
$sheet->mergeCells("O4:P4");
$sheet->mergeCells("Q4:R4");
$sheet->getStyle("A4:R5")->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	),
	"borders" => array("allborders" => array(
		"style" => PHPExcel_Style_Border::BORDER_THIN
	))
));
// warna background
$sheet->getStyle("A4:R5")->applyFromArray(array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFFFFF00')
    )
));
// Tulis Data
$line = 5;
while ($row = $report->FetchAssoc()) {
    $oblDebit = 0;
    $oblCredit = 0;
    $mtcDebit = 0;
    $mtcCredit = 0;
    $acrDebit = 0;
    $acrCredit = 0;
    $adjDebit = 0;
    $adjCredit = 0;
    if($isIncOb == 1){
        $oblDebit = ($row["total_debit_prev"] + $row["bal_debit_amt"]);
        $oblCredit = ($row["total_credit_prev"] + $row["bal_credit_amt"]);
    }
    $mtcDebit = $row["mtc_debit"];
    $acrDebit = $row["acr_debit"];
    $adjDebit = $row["adj_debit"];
    $mtcCredit = $row["mtc_credit"];
    $acrCredit = $row["acr_credit"];
    $adjCredit = $row["adj_credit"];
    if($oblDebit+$oblCredit+$mtcDebit+$mtcCredit+$acrDebit+$acrCredit+$adjDebit+$adjCredit <> 0){
        $line++;
        $sheet->setCellValue("A$line", $row["acc_no"]);
        $sheet->setCellValue("B$line", $row["acc_name"]);
        $sheet->setCellValue("C$line", $oblDebit);
        $sheet->setCellValue("D$line", $oblCredit);
        $sheet->setCellValue("E$line", $mtcDebit);
        $sheet->setCellValue("F$line", $mtcCredit);
        $sheet->setCellValue("G$line", $acrDebit);
        $sheet->setCellValue("H$line", $acrCredit);
        $sheet->setCellValue("I$line", "=C".$line."+E".$line."+G".$line);
        $sheet->setCellValue("J$line", "=D".$line."+F".$line."+H".$line);
        $sheet->setCellValue("K$line", $adjDebit);
        $sheet->setCellValue("L$line", $adjCredit);
        $sheet->setCellValue("M$line", "=I".$line."+K".$line);
        $sheet->setCellValue("N$line", "=J".$line."+L".$line);
        // hitung profit-loss
        if(left($row["acc_no"],1) > 3){
            if($row["dc_saldo"] == "D"){
                $sheet->setCellValue("O$line", "=M".$line."-N".$line);
                $sheet->setCellValue("P$line", "0");
            }elseif($row["dc_saldo"] == "K"){
                $sheet->setCellValue("O$line", "0");
                $sheet->setCellValue("P$line", "=N".$line."-M".$line);
            }
        }else{
            if($row["dc_saldo"] == "D"){
                $sheet->setCellValue("Q$line", "=M".$line."-N".$line);
                $sheet->setCellValue("R$line", "0");
            }elseif($row["dc_saldo"] == "K"){
                $sheet->setCellValue("Q$line", "0");
                $sheet->setCellValue("R$line", "=N".$line."-M".$line);
            }
        }
    }
}
// bottom description
$line++;
$sheet->setCellValue("C$line", "Opening Balance");
$sheet->setCellValue("E$line", "Mutasi Kas (BKK/BKM)");
$sheet->setCellValue("G$line", "Accrual (BJK/BPK)");
$sheet->setCellValue("I$line", "After Accrual");
$sheet->setCellValue("K$line", "Adjustment (BPB/BMM)");
$sheet->setCellValue("M$line", "After Adjusment)");
$sheet->setCellValue("O$line", "Profit/Loss");
$sheet->setCellValue("Q$line", "Balance Statement");
$sheet->mergeCells("A$line:B$line");
$sheet->mergeCells("C$line:D$line");
$sheet->mergeCells("E$line:F$line");
$sheet->mergeCells("G$line:H$line");
$sheet->mergeCells("I$line:J$line");
$sheet->mergeCells("K$line:L$line");
$sheet->mergeCells("M$line:N$line");
$sheet->mergeCells("O$line:P$line");
$sheet->mergeCells("Q$line:R$line");
$sheet->getStyle("A$line:R$line")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array(
        "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ),
    "borders" => array("allborders" => array(
        "style" => PHPExcel_Style_Border::BORDER_THIN
    ))
));
// SUM
$line++;
$flagCyclic = ($row == 6);
$sheet->setCellValue("A$line", "GRAND TOTAL : ");
$sheet->mergeCells("A$line:B$line");
$sheet->setCellValue("C$line", $flagCyclic ? "0" : "=SUM(C6:C".($line-2).")");
$sheet->setCellValue("D$line", $flagCyclic ? "0" : "=SUM(D6:D".($line-2).")");
$sheet->setCellValue("E$line", $flagCyclic ? "0" : "=SUM(E6:E".($line-2).")");
$sheet->setCellValue("F$line", $flagCyclic ? "0" : "=SUM(F6:F".($line-2).")");
$sheet->setCellValue("G$line", $flagCyclic ? "0" : "=SUM(G6:G".($line-2).")");
$sheet->setCellValue("H$line", $flagCyclic ? "0" : "=SUM(H6:H".($line-2).")");
$sheet->setCellValue("I$line", $flagCyclic ? "0" : "=SUM(I6:I".($line-2).")");
$sheet->setCellValue("J$line", $flagCyclic ? "0" : "=SUM(J6:J".($line-2).")");
$sheet->setCellValue("K$line", $flagCyclic ? "0" : "=SUM(K6:K".($line-2).")");
$sheet->setCellValue("L$line", $flagCyclic ? "0" : "=SUM(L6:L".($line-2).")");
$sheet->setCellValue("M$line", $flagCyclic ? "0" : "=SUM(M6:M".($line-2).")");
$sheet->setCellValue("N$line", $flagCyclic ? "0" : "=SUM(N6:N".($line-2).")");
$sheet->setCellValue("O$line", $flagCyclic ? "0" : "=SUM(O6:O".($line-2).")");
$sheet->setCellValue("P$line", $flagCyclic ? "0" : "=SUM(P6:P".($line-2).")");
$sheet->setCellValue("Q$line", $flagCyclic ? "0" : "=SUM(Q6:Q".($line-2).")");
$sheet->setCellValue("R$line", $flagCyclic ? "0" : "=SUM(R6:R".($line-2).")");
//// warna background
$lbf = $line-1;
$sheet->getStyle("A$lbf:R$line")->applyFromArray(array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFFFFF00')
    )
));
$sheet->getStyle("A$line:R$line")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A$line:R$line")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$line++;
$lbf = $line-1;
$sheet->setCellValue("C$line", "=C".$lbf."-D".$lbf);
$sheet->setCellValue("E$line", "=E".$lbf."-F".$lbf);
$sheet->setCellValue("G$line", "=G".$lbf."-H".$lbf);
$sheet->setCellValue("I$line", "=I".$lbf."-J".$lbf);
$sheet->setCellValue("K$line", "=K".$lbf."-L".$lbf);
$sheet->setCellValue("M$line", "=M".$lbf."-N".$lbf);
$sheet->setCellValue("O$line", "=IF(O$lbf>P$lbf,0,P$lbf-O$lbf)");
$sheet->setCellValue("P$line", "=IF(O$lbf>P$lbf,O$lbf-P$lbf,0)");
$sheet->setCellValue("Q$line", "=IF(R$lbf>Q$lbf,R$lbf-Q$lbf,0)");
$sheet->setCellValue("R$line", "=IF(R$lbf>Q$lbf,0,Q$lbf-R$lbf)");
$sheet->mergeCells("A$line:B$line");
$sheet->mergeCells("C$line:D$line");
$sheet->mergeCells("E$line:F$line");
$sheet->mergeCells("G$line:H$line");
$sheet->mergeCells("I$line:J$line");
$sheet->mergeCells("K$line:L$line");
$sheet->mergeCells("M$line:N$line");
$sheet->getStyle("A$line:R$line")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array(
        "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ),
    "borders" => array("allborders" => array(
        "style" => PHPExcel_Style_Border::BORDER_THIN
    ))
));


//// Styling
$sheet->getStyle("C6:R$line")->getNumberFormat()->setFormatCode("#,##0.00");
$sheet->getStyle("A6:A$line")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A6:A$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B6:B$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C6:C$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D6:D$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E6:E$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F6:F$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G6:G$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H6:H$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("I6:I$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("J6:J$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("K6:K$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("L6:L$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("M6:M$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("N6:N$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("O6:O$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("P6:P$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("Q6:Q$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("R6:R$line")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
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