<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Eraditya Inc
 * Date: 26/01/15
 * Time: 16:12
 * To change this template use File | Settings | File Templates.
 */
require_once(LIBRARY . "PHPExcel.php");

// Ini file pure akan membuat file excel dan tidak ada HTML fragment
$reader = new PHPExcel_Reader_Excel5(); // Report Template
$phpExcel = $reader->load(APPS . "templates/upload-inventory-opening.xls");
$filename = "template-inventory-opening-upload.xls";
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, sprintf('Content-Disposition: attachment;filename="%s"', $filename)
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Tulis data Items
$sheet = $phpExcel->setActiveSheetIndexByName("Item List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $items Item[]*/
foreach ($items as $item) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $item->Id);
    $sheet->setCellValueExplicit("B" . $brs, $item->ItemCode,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $item->ItemName);
    $sheet->setCellValueExplicit("D" . $brs, $item->PartNo,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("E" . $brs, $item->UomCode);
    $sheet->setCellValue("F" . $brs, $item->UnitBrandName);
    $sheet->setCellValue("G" . $brs, $item->UnitTypeName);
}
// Tulis data Project
$sheet = $phpExcel->setActiveSheetIndexByName("Project List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $projects Project[]*/
foreach ($projects as $project) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $project->Id);
    $sheet->setCellValueExplicit("B" . $brs, $project->ProjectCd,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $project->ProjectName);
}

// Hmm Reset Pointer
$sheet = $phpExcel->setActiveSheetIndexByName("Opening Inventory");
$sheet->getStyle("A1");

// Flush to client
foreach ($headers as $header) {
    header($header);
}
// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
setcookie("startDownload", 1);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
exit();
