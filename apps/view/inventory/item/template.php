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
$phpExcel = $reader->load(APPS . "templates/upload-items.xls");
$filename = "template-itemlist-upload.xls";
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, sprintf('Content-Disposition: attachment;filename="%s"', $filename)
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);

// Tulis data Jenis Item
$sheet = $phpExcel->setActiveSheetIndexByName("Item Category List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $icategory ItemCategory[]*/
foreach ($icategory as $bcategory) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $bcategory->Id);
    $sheet->setCellValueExplicit("B" . $brs, $bcategory->Code,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $bcategory->Description);
}
// Tulis data Brand Unit
$sheet = $phpExcel->setActiveSheetIndexByName("Unit Brand List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $ibrand UnitBrand[]*/
foreach ($ibrand as $brand) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $brand->Id);
    $sheet->setCellValueExplicit("B" . $brs, $brand->BrandCode,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $brand->BrandName);
}
// Tulis data Type Unit
$sheet = $phpExcel->setActiveSheetIndexByName("Unit Type List");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var  $itype UnitType[]*/
foreach ($itype as $type) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $type->Id);
    $sheet->setCellValueExplicit("B" . $brs, $type->TypeCode,PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue("C" . $brs, $type->TypeDesc);
}
// Tulis data Satuan Barang
$sheet = $phpExcel->setActiveSheetIndexByName("Unit of Measure");
$sheet->getColumnDimension("B")->setAutoSize(true);
$brs = 3;
/** @var $isatuan UomMaster[] */
foreach ($isatuan as $satuan) {
    $brs++;
    $sheet->setCellValue("A" . $brs, $satuan->Id);
    $sheet->setCellValue("B" . $brs, $satuan->UomCd);
    $sheet->setCellValue("C" . $brs, $satuan->UomDesc);
}

// Hmm Reset Pointer
$sheet = $phpExcel->setActiveSheetIndexByName("Items List");
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
