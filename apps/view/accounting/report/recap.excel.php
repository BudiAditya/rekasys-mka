<?php
/** @var $month int */ /** @var $year int */ /** @var $docTypes DocType[] */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */ /** @var $monthNames array */
/** @var $projectList Project[] */ /** @var $projectId int */

$phpExcel = new PHPExcel();
$headers = array(
	'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="rekap-jurnal.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Excel MetaData
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("Rekap Jurnal")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekap Jurnal");

// Bikin Header
$buff = array();
foreach ($docTypes as $docType) {
	if (in_array($docType->Id, $docIds)) {
		$buff[] = strtoupper($docType->DocCode);
	}
}
switch ($status) {
	case 1:
		$subTitle = "JURNAL: " . implode(", ", $buff) . " status: BELUM APPROVED";
		break;
	case 2:
		$subTitle = "JURNAL: " . implode(", ", $buff) . " status: SUDAH APPROVED";
		break;
	case 3:
		$subTitle = "JURNAL: " . implode(", ", $buff) . " status: VERIFIED";
		break;
	case 4:
		$subTitle = "JURNAL: " . implode(", ", $buff) . " status: POSTED";
		break;
	default:
		$subTitle = "JURNAL: " . implode(", ", $buff) . " status: SEMUA";
		break;
}
$selectedProject = null;
$strProject = null;
foreach ($projectList as $project) {
    if ($project->Id == $projectId) {
        $selectedProject = $project;
        $strProject = " (Proyek: ".$project->ProjectCd." - ".$project->ProjectName.")";
        break;
    }
}
$sheet->setCellValue("A1", sprintf("Rekap Jurnal Company : %s - %s", $company->EntityCd, $company->CompanyName.$strProject));
$sheet->mergeCells("A1:F1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", $subTitle);
$sheet->mergeCells("A2:F2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));
$sheet->setCellValue("A3", sprintf("Periode: %s %s", $monthNames[$month], $year));
$sheet->mergeCells("A3:F3");
$sheet->getStyle("A3")->applyFromArray(array(
	"font" => array("size" => 14)
));
for ($i = 0; $i < 8; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Bikin Kolom
$sheet->setCellValue("A5", "No. Akun");
$sheet->setCellValue("B5", "Nama Akun");
$sheet->setCellValue("C5", sprintf("Mutasi %s %s", $monthNames[$month], $year));
$sheet->setCellValue("E5", sprintf("Jumlah s.d. %s %s", $monthNames[$month], $year));
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
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	),
	"borders" => array("allborders" => array(
		"style" => PHPExcel_Style_Border::BORDER_THIN
	))
));

// Tulis Data
$row = 6;
while($data = $report->FetchAssoc()) {
	$row++;

	$sheet->setCellValue("A$row", $data["acc_no"]);
	$sheet->setCellValue("B$row", $data["acc_name"]);
	$sheet->setCellValue("C$row", $data["total_debit"]);
	$sheet->setCellValue("D$row", $data["total_credit"]);
	$sheet->setCellValue("E$row", $data["total_debit"] + $data["total_debit_prev"]);
	$sheet->setCellValue("F$row", $data["total_credit"] + $data["total_credit_prev"]);
}

// SUM
$row++;
$flagCyclic = ($row == 7);
$sheet->setCellValue("A$row", "GRAND TOTAL : ");
$sheet->mergeCells("A$row:B$row");
$sheet->setCellValue("D$row", $flagCyclic ? "0" : "=SUM(D7:D".($row-1).")");
$sheet->setCellValue("F$row", $flagCyclic ? "0" : "=SUM(F7:F".($row-1).")");
$sheet->getStyle("A$row:F$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A$row:F$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// Styling
$sheet->getStyle("C7:F$row")->getNumberFormat()->setFormatCode("#,##0.00");
$sheet->getStyle("A7:A$row")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A7:A$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B7:B$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C7:C$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D7:D$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E7:E$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F7:F$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

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