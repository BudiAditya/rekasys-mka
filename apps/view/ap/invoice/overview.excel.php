<?php
/** @var $output string */
/** @var $creditors Creditor[] */ /** @var $credt Creditor */ /** @var $creditorId int */
/** @var $docTypes DocType[] */ /** @var $doc DocType */ /** @var $docTypeId int */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.ap.invoice.xls");
$sheet = $excelTemplate->getActiveSheet();

//set border
$border = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,)));

//set alignment
$horCenter = array('alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
));

$creditorName = $credt != null ? $credt->CreditorName : "SEMUA CREDITOR";
$docDesc = $doc != null ? $doc->DocTypeDesc : "SEMUA JENIS TAGIHAN";
if ($status == -1) {
    $statusName = "SEMUA STATUS";
} elseif ($status == 0) {
    $statusName = "UNPOSTED";
} else {
    $statusName = "POSTED";
}

$sheet->setCellValue('C2', date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));
$sheet->setCellValue('C3', $creditorName);
$sheet->setCellValue('C4', $docDesc);
$sheet->setCellValue('C5', $statusName);

// Setting Auto Width
for ($i = 0; $i < 11; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 7;
$docNo = null;
$baseAmount = 0;
$taxAmount = 0;
$deductAmount = 0;
$i = 1;
while($rs = $report->FetchAssoc()) {
    if ($rs["doc_no"] != $docNo){
        $entity = $rs["entity_cd"];
        $doc = $rs["doc_no"];
        $date = date('d M Y', strtotime($rs["doc_date"]));
		$reff = $rs["reference_no"];
        $namaCreditor = $rs["creditor_name"];
        $docNo = $rs["doc_no"];
        $counter = $i++;
    } else {
        $entity = "";
        $doc = "";
        $date = "";
		$reff = "";
        $namaCreditor = "";
        $counter = "";
    }
    $baseAmount += $rs["dpp"];
    $taxAmount += $rs["tax"];
    $deductAmount += $rs["deduction"];

    $row++;

    $line1 = 'A'.$row;
    $line2 = 'J'.$row;
    $sheet->getStyle("$line1:$line2")->applyFromArray($border);

    $sheet->getStyle('A'.$row)->applyFromArray($horCenter);
    $sheet->setCellValue('A'.$row, $counter);
    $sheet->setCellValue('B'.$row, $entity);
    $sheet->setCellValue('C'.$row, $doc);
    $sheet->setCellValue('D'.$row, $date);
	$sheet->setCellValue('E'.$row, $reff);
    $sheet->setCellValue('F'.$row, $namaCreditor);
    $sheet->setCellValue('G'.$row, $rs["description"]);
    $sheet->setCellValue('H'.$row, $rs["dpp"]);
    $sheet->setCellValue('I'.$row, $rs["tax"]);
    $sheet->setCellValue('J'.$row, $rs["deduction"]);
	$sheet->setCellValue("K$row", "=H$row + I$row - J$row");
}

// Bikin SUM
$row++;
$flagCyclic = ($row == 8);
$sheet->mergeCells("A$row:G$row");
$sheet->setCellValue("A$row", "TOTAL :");
$sheet->setCellValue("H$row", $flagCyclic ? "0" : "=SUM(H8:H".($row-1).")");
$sheet->setCellValue("I$row", $flagCyclic ? "0" : "=SUM(I8:I".($row-1).")");
$sheet->setCellValue("J$row", $flagCyclic ? "0" : "=SUM(J8:J".($row-1).")");
$sheet->setCellValue("K$row", $flagCyclic ? "0" : "=SUM(K8:K".($row-1).")");
$sheet->getStyle("A$row:K$row")->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
	'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
));

$sheet->getStyle("H8:K$row")->getNumberFormat()->setFormatCode('#,##0.00');

// Reset Pointer
$sheet->getStyle("A1");

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap_dokumen_ap_invoice.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;