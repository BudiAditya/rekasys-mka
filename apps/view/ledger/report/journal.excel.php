<?php
/** @var $start int */ /** @var $end int */ /** @var $docTypes DocType[] */ /** @var $showNo bool */ /** @var $showCol bool */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */
/** @var $projectList Project[] */ /** @var $projectId int */

$phpExcel = new PHPExcel();
$headers = array(
	'Content-Type: application/vnd.ms-excel'
	, 'Content-Disposition: attachment;filename="laporan-jurnal.xls"'
	, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Excel MetaData
$phpExcel->getProperties()->setCreator("Reka System (c) Wayan Budiasa")->setTitle("Report Jurnal")->setCompany("Rekasys Corporation");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Report Jurnal");

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
$sheet->setCellValue("A1", sprintf("Report Jurnal Company : %s - %s", $company->EntityCd, $company->CompanyName.$strProject));
$sheet->mergeCells("A1:H1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", $subTitle);
$sheet->mergeCells("A2:H2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));
$sheet->setCellValue("A3", sprintf("Periode: %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)));
$sheet->mergeCells("A3:H3");
$sheet->getStyle("A3")->applyFromArray(array(
	"font" => array("size" => 14)
));
for ($i = 0; $i < 13; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Bikin Kolom
$sheet->setCellValue("A5", "Tgl");
$sheet->setCellValue("B5", "No. Voucher");
$sheet->setCellValue("C5", "Company");
if ($showCol) {
	$sheet->setCellValue("D5", "Dept");
	$sheet->setCellValue("E5", "Div");
	$sheet->setCellValue("F5", "Project");
	$sheet->setCellValue("G5", "Debtor");
	$sheet->setCellValue("H5", "Kreditor");
	$sheet->setCellValue("I5", "Karyawan");

	$sheet->setCellValue("J5", "Uraian");
	$sheet->setCellValue("K5", "Debet");
	$sheet->setCellValue("M5", "Kredit");
	$sheet->setCellValue("K6", "Akun");
	$sheet->setCellValue("L6", "Jumlah");
	$sheet->setCellValue("M6", "Akun");
	$sheet->setCellValue("N6", "Jumlah");

	$sheet->mergeCells("A5:A6");
	$sheet->mergeCells("B5:B6");
	$sheet->mergeCells("C5:C6");
	$sheet->mergeCells("D5:D6");
	$sheet->mergeCells("E5:E6");
	$sheet->mergeCells("F5:F6");
	$sheet->mergeCells("G5:G6");
	$sheet->mergeCells("H5:H6");
	$sheet->mergeCells("I5:I6");
	$sheet->mergeCells("J5:J6");
	$sheet->mergeCells("K5:L5");
	$sheet->mergeCells("M5:N5");

	$range = "A5:N6";
} else {
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

	$range = "A5:H6";
}
$sheet->getStyle($range)->applyFromArray(array(
	"font" => array("bold" => true),
	"alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	),
	"borders" => array("allborders" => array(
		"style" => PHPExcel_Style_Border::BORDER_THIN
	))
));

// Tulis data
$row = 6;
$prevDate = null;
$prevVoucherNo = null;
$prevSbu = null;

$flagDate = true;
$flagVoucherNo = true;
$flagSbu = true;
$sums = 0;
while ($data = $report->FetchAssoc()) {
	$row++;
	// Convert datetime jadi native format
	$data["voucher_date"] = strtotime($data["voucher_date"]);
	if ($prevDate != $data["voucher_date"]) {
		$prevDate = $data["voucher_date"];
		$flagDate = true;
	} else {
		$flagDate = false;
	}

	if ($prevVoucherNo != $data["doc_no"]) {
		$prevVoucherNo = $data["doc_no"];
		$flagVoucherNo = true;
	} else {
		$flagVoucherNo = false;
	}

	if ($prevSbu != $data["entity_cd"]) {
		$prevSbu = $data["entity_cd"];
		$flagSbu = true;
	} else {
		$flagSbu = false;
	}

	$sums += $data["amount"];
	$sheet->setCellValue("A$row", $flagDate ? date("d", $prevDate) : "");
	$sheet->setCellValue("B$row", $flagVoucherNo ? $prevVoucherNo : "");
	$sheet->setCellValue("C$row", $flagSbu ? $prevSbu : "");
	if ($showCol) {
		$sheet->setCellValue("D$row", $data["dept_code"]);
		$sheet->setCellValue("E$row", $data["act_code"]);
		$sheet->setCellValue("F$row", $data["project_name"]);
		$sheet->setCellValue("G$row", $data["debtor_name"]);
		$sheet->setCellValue("H$row", $data["creditor_name"]);
		$sheet->setCellValue("I$row", $data["nama"]);

		$sheet->setCellValue("J$row", $data["note"]);
		$sheet->setCellValue("K$row", $showNo ? $data["acc_no_debit"] : $data["acc_debit"]);
		$sheet->setCellValue("L$row", $data["amount"]);
		$sheet->setCellValue("M$row", $showNo ? $data["acc_no_credit"] : $data["acccredit"]);
		$sheet->setCellValue("N$row", $data["amount"]);
	} else {
		$sheet->setCellValue("D$row", $data["note"]);
		$sheet->setCellValue("E$row", $showNo ? $data["acc_no_debit"] : $data["acc_debit"]);
		$sheet->setCellValue("F$row", $data["amount"]);
		$sheet->setCellValue("G$row", $showNo ? $data["acc_no_credit"] : $data["acccredit"]);
		$sheet->setCellValue("H$row", $data["amount"]);
	}
}

// SUM
$row++;
$flagCyclic = ($row == 7);
if ($showCol) {
	$sheet->setCellValue("A$row", "GRAND TOTAL : ");
	$sheet->mergeCells("A$row:J$row");
	$sheet->setCellValue("L$row", $flagCyclic ? "0" : "=SUM(L7:L".($row-1).")");
	$sheet->setCellValue("N$row", $flagCyclic ? "0" : "=SUM(N7:N".($row-1).")");
	$sheet->getStyle("A$row:N$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:N$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
} else {
	$sheet->setCellValue("A$row", "GRAND TOTAL : ");
	$sheet->mergeCells("A$row:D$row");
	$sheet->setCellValue("F$row", $flagCyclic ? "0" : "=SUM(F7:F".($row-1).")");
	$sheet->setCellValue("H$row", $flagCyclic ? "0" : "=SUM(H7:H".($row-1).")");
	$sheet->getStyle("A$row:H$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:H$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
}

// Styling
if ($showCol) {
	$sheet->getStyle("L7:L$row")->getNumberFormat()->setFormatCode("#,##0.00");
	$sheet->getStyle("N7:N$row")->getNumberFormat()->setFormatCode("#,##0.00");

	$sheet->getStyle("I7:I$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("J7:J$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("K7:K$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("L7:L$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("M7:M$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("N7:N$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
} else {
	$sheet->getStyle("F7:F$row")->getNumberFormat()->setFormatCode("#,##0.00");
	$sheet->getStyle("H7:H$row")->getNumberFormat()->setFormatCode("#,##0.00");
}
$sheet->getStyle("A7:A$row")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A7:A$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B7:B$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C7:C$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D7:D$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E7:E$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F7:F$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("G7:G$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("H7:H$row")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

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

// EoF: journal.excel.php