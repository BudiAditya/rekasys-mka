<?php
/** @var $debtorId null|int */ /** @var $debtors Debtor[] */ /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */

// File ini akan pure membuat file excel... Tidak ada fragment HTML
$excel = new PHPExcel();
switch ($output) {
	case "xlsx":
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$filename = sprintf("detail-aging_%s.xlsx", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
	default:
		$writer = new PHPExcel_Writer_Excel5($excel);
		$filename = sprintf("detail-aging_%s.xls", $company->EntityCd);
		$headers[] = 'Content-Type: application/vnd.ms-excel';
		$headers[] = sprintf('Content-Disposition: attachment;filename="%s"', $filename);
		$headers[] = 'Cache-Control: max-age=0';
		break;
}

// Excel MetaData
$excel->getProperties()->setCreator("Reka System (c)")->setTitle("Detail Aging Piutang Debtor")->setCompany("Rekasys Corporation");
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Detail Aging Piutang");

// Bikin Header
$sheet->setCellValue("A1", sprintf("Detail Aging Piutang Company : %s - %s", $company->EntityCd, $company->CompanyName));
$sheet->mergeCells("A1:K1");
$sheet->getStyle("A1")->applyFromArray(array(
	"font" => array("size" => 20)
));
$sheet->setCellValue("A2", "Per Tanggal: " . date(HUMAN_DATE, $date));
$sheet->mergeCells("A2:K2");
$sheet->getStyle("A2")->applyFromArray(array(
	"font" => array("size" => 14)
));

$sheet->setCellValue("A4", "No.");
$sheet->setCellValue("B4", "No. Dokumen");
$sheet->setCellValue("C4", "Tgl. Dokumen");
$sheet->setCellValue("D4", "Nilai Dokumen");
$sheet->setCellValue("E4", "1 - 30 hari");
$sheet->setCellValue("F4", "31 - 60 hari");
$sheet->setCellValue("G4", "61 - 90 hari");
$sheet->setCellValue("H4", "91 - 120 hari");
$sheet->setCellValue("I4", "121 - 150 hari");
$sheet->setCellValue("J4", "> 150 hari");
$sheet->setCellValue("K4", "Total");
$sheet->getStyle("A4:K4")->applyFromArray(array(
	"font" => array("bold" => true)
	, "alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	)
));

// Tulis Data
$counter = 0;
$currentRow = 4;
$prevDebtorId = null;
while ($row = $report->FetchAssoc()) {
	$currentRow++;
	$counter++;
	if ($row["is_pkp"] == 1) {
		$amount = $row["sum_base"] + $row["sum_tax"] - $row["sum_deduction"];
	} else {
		$amount = $row["sum_base"] + $row["sum_tax"];
	}

	$age = $row["age"];
	$date = strtotime($row["doc_date"]);

	// Reset variable
	$piutang1 = 0;
	$piutang2 = 0;
	$piutang3 = 0;
	$piutang4 = 0;
	$piutang5 = 0;
	$piutang6 = 0;
	$piutang = $amount - $row["sum_paid"];

	if ($age <= 0) {
		// Nothing to do... data ini di skip tapi masih ditampilkan walau 0 semua
	} else if ($age <= 30) {
		$piutang1 = $piutang;
	} else if ($age <= 60) {
		$piutang2 = $piutang;
	} else if ($age <= 90) {
		$piutang3 = $piutang;
	} else if ($age <= 120) {
		$piutang4 = $piutang;
	} else if ($age <= 150) {
		$piutang5 = $piutang;
	} else {
		$piutang6 = $piutang;
	}

	// Header untuk debtor..
	if ($prevDebtorId != $row["debtor_id"]) {
		// Counter nomor ketika ganti debtor ter-reset
		$counter = 1;
		$prevDebtorId = $row["debtor_id"];
		$sheet->setCellValue("A" . $currentRow, "Kode Debtor: " . $row["debtor_cd"]);
		$sheet->mergeCells("A$currentRow:C$currentRow");
		$sheet->getStyle("A" . $currentRow)->applyFromArray(array(
			"font" => array("bold" => true)
			, "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
		));
		$sheet->setCellValue("D" . $currentRow, sprintf("%s (Merk Dagang: %s)", $row["debtor_name"], $row["trade_name"]));
		$sheet->mergeCells("D$currentRow:K$currentRow");
		$sheet->getStyle("D" . $currentRow)->applyFromArray(array(
			"font" => array("bold" => true)
		));

		$currentRow++;
	}

	// Buff data
	$data = array();
	$sheet->setCellValue("A" . $currentRow, $counter . ".");
	$sheet->setCellValue("B" . $currentRow, $row["doc_no"]);
	$sheet->setCellValue("C" . $currentRow, date(HUMAN_DATE, $date));
	$sheet->setCellValue("D" . $currentRow, $amount);
	$sheet->setCellValue("E" . $currentRow, $piutang1);
	$sheet->setCellValue("F" . $currentRow, $piutang2);
	$sheet->setCellValue("G" . $currentRow, $piutang3);
	$sheet->setCellValue("H" . $currentRow, $piutang4);
	$sheet->setCellValue("I" . $currentRow, $piutang5);
	$sheet->setCellValue("J" . $currentRow, $piutang6);
	$formula = sprintf("=SUM(E%d:J%d)", $currentRow, $currentRow);
	$sheet->setCellValue("K" . $currentRow, $formula);
}
// Sums
$currentRow++;
$sheet->setCellValue("A" . $currentRow, "TOTAL : ");
$sheet->mergeCells("A$currentRow:C$currentRow");
$sheet->getStyle("A" . $currentRow)->applyFromArray(array(
	"font" => array("bold" => true)
	, "alignment" => array(
		"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
	)
));
$flagCyclic = $currentRow == 5;
$sheet->setCellValue("D" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(D5:D%d)", $currentRow - 1));
$sheet->setCellValue("E" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(E5:E%d)", $currentRow - 1));
$sheet->setCellValue("F" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(F5:F%d)", $currentRow - 1));
$sheet->setCellValue("G" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(G5:G%d)", $currentRow - 1));
$sheet->setCellValue("H" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(H5:H%d)", $currentRow - 1));
$sheet->setCellValue("I" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(I5:I%d)", $currentRow - 1));
$sheet->setCellValue("J" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(J5:J%d)", $currentRow - 1));
$sheet->setCellValue("K" . $currentRow, $flagCyclic ? 0 : sprintf("=SUM(K5:K%d)", $currentRow - 1));


// Styling
// Numeric Format: #,##0.00
$range = "D5:K" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"numberformat" => array("code" => "#,##0.00")
));
// Borders
$range = "A4:K" . $currentRow;
$sheet->getStyle($range)->applyFromArray(array(
	"borders" => array(
		"allborders" => array(
			"style" => PHPExcel_Style_Border::BORDER_THIN
			, "color" => array("argb" => "FF000000")
		)
	)
));
// Auto Widths
for ($i = 0; $i <= 11; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Hmm Reset Pointer
$sheet->getStyle("A5");

// Flush to client
foreach ($headers as $header) {
	header($header);
}
$writer->save("php://output");

// Garbage Collector
$excel->disconnectWorksheets();
unset($excel);
ob_flush();
exit();


// End of file: detail_aging.excel.php
