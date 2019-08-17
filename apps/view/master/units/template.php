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
$phpExcel = $reader->load(APPS . "templates/upload-units.xls");
$filename = "template-unitlist-upload.xls";
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, sprintf('Content-Disposition: attachment;filename="%s"', $filename)
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Tulis data Unit Type
$sheet = $phpExcel->setActiveSheetIndexByName("Type List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $utype UnitType[]*/
foreach ($utype as $type) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $type->Id);
    $sheet->setCellValueExplicit("B" . $brs, $type->TypeCode,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $type->TypeInitial);
    $sheet->setCellValue("D" . $brs, $type->TypeDesc);
}
// Tulis data Brand Unit
$sheet = $phpExcel->setActiveSheetIndexByName("Brand List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var $ubrand UnitBrand[] */
foreach ($ubrand as $brand) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $brand->Id);
    $sheet->setCellValueExplicit("B" . $brs, $brand->BrandCode,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $brand->BrandName);
}
// Tulis data Class Unit
$sheet = $phpExcel->setActiveSheetIndexByName("Class List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var $uclass UnitClass[] */
foreach ($uclass as $class) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $class->Id);
    $sheet->setCellValueExplicit("B" . $brs, $class->ClassCode,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $class->ClassName);
}

// Hmm Reset Pointer
$sheet = $phpExcel->setActiveSheetIndexByName("Unit List");
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
