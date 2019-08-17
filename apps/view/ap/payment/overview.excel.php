<?php
/** @var Creditor[] $creditors */ /** @var int $creditorId */ /** @var StatusCode[] $codes */ /** @var int $status */ /** @var ReaderBase $report */
/** @var int $startDate */ /** @var int $endDate */ /** @var string $output */
$selectedCreditor = null;
foreach($creditors as $creditor){
	if ($creditor->Id == $creditorId) {
		$selectedCreditor = $creditor;
		break;
	}
}
$selectedStatus = null;
foreach ($codes as $code) {
	if ($code->Code == $status) {
		$selectedStatus = $code;
		break;
	}
}
$creditorName = $selectedCreditor != null ? $selectedCreditor->CreditorName: "SEMUA CREDITOR";
$statusName = $selectedStatus != null ? $selectedStatus->ShortDesc : "SEMUA STATUS";

$reader = new PHPExcel_Reader_Excel5();

$excelTemplate = $reader->load(APPS . "templates/rekap.dokumen.ap.payment.xls");
$sheet = $excelTemplate->getActiveSheet();

$sheet->setCellValue('A2', "Tanggal : " . date(HUMAN_DATE, $startDate) . " s/d " . date(HUMAN_DATE, $endDate));
$sheet->setCellValue('A3', "Kreditor : " . $creditorName);
$sheet->setCellValue('A4', "Status : " . $statusName);

// Setting Auto Width
for ($i = 0; $i < 9; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

$row = 6;
$no = 0;
$prevId = null;
while($data = $report->FetchAssoc()) {
	$row++;
	if ($prevId != $data["id"]) {
		$no++;
		$prevId = $row["id"];

		$sheet->setCellValue('A'.$row, $no);
		$sheet->setCellValue('B'.$row, $data["entity_cd"]);
		$sheet->setCellValue('C'.$row, $data["doc_no"]);
		$sheet->setCellValue('D'.$row, date('d M Y', strtotime($data["doc_date"])));
		$sheet->setCellValue('E'.$row, $data["short_desc"]);
		$sheet->setCellValue('F'.$row, $data["creditor_name"]);
	}

	$sheet->setCellValue("G$row", $data["paid_doc_no"]);
	$sheet->setCellValue("H$row", $data["paid_note"]);
	$sheet->setCellValue("I$row", $data["amount"]);
}

$row++;
$flagCyclic = ($row == 7);
$sheet->setCellValue("A$row", "GRAND TOTAL : ");
$sheet->mergeCells("A$row:H$row");
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue("I$row", $flagCyclic ? 0 : "=SUM(I7:I" . ($row - 1) . ")");

// Styling
$sheet->getStyle("I7:I$row")->getNumberFormat()->setFormatCode('#,##.00');
$sheet->getStyle("A7:I$row")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekap-dokumen-pv.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excelTemplate, 'Excel5');
$objWriter->save('php://output');
exit;