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
$phpExcel = $reader->load(APPS . "templates/upload-assets.xls");
$filename = "template-assetlist-upload.xls";
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, sprintf('Content-Disposition: attachment;filename="%s"', $filename)
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Tulis data Asset Category
$sheet = $phpExcel->setActiveSheetIndexByName("Asset Category List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $acats AssetCategory[]*/
foreach ($acats as $acategory) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $acategory->Id);
    $sheet->setCellValueExplicit("B" . $brs, $acategory->Code,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $acategory->Name);
    $sheet->setCellValue("D" . $brs, $acategory->GetDepreciationMethod());
    $sheet->setCellValue("E" . $brs, $acategory->MaxAge);
    $sheet->setCellValue("F" . $brs, $acategory->DepreciationPercentage);
}

// Hmm Reset Pointer
$sheet = $phpExcel->setActiveSheetIndexByName("Asset List");
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
