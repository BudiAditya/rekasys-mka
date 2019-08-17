<?php
/** @var $items Items[] */
$phpExcel = new PHPExcel();
$headers = array(
'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="print-daftar-barang.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Erasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Report")->setCompany("Erasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Daftar Item Barang");
//helper for styling
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$-421]* #,##0_);_([$-421]* (#,##0);_([$-421]* "-"??_);_(@_)'));
$row = 1;
$sheet->setCellValue("A$row",$company_name);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);
$row++;
$sheet->setCellValue("A$row","DAFTAR ITEM BARANG");
$row++;
$sheet->setCellValue("A$row","No.");
$sheet->setCellValue("B$row","Jenis");
$sheet->setCellValue("C$row","Divisi");
$sheet->setCellValue("D$row","Kelompok");
$sheet->setCellValue("E$row","Kode Barang");
$sheet->setCellValue("F$row","Nama Barang");
$sheet->setCellValue("G$row","Kemasan");
$sheet->setCellValue("H$row","Isi");
$sheet->setCellValue("I$row","Satuan");
$sheet->setCellValue("J$row","Supplier");
$sheet->setCellValue("K$row","Keterangan");
$sheet->setCellValue("L$row","Status");
$sheet->getStyle("A$row:L$row")->applyFromArray(array_merge($center, $allBorders));
$nmr = 0;
$str = $row;
if ($items != null){
    foreach ($items as $item){
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("B$row",$item->Bjenis);
        $sheet->setCellValue("C$row",$item->Bdivisi);
        $sheet->setCellValue("D$row",$item->Bgnama);
        $sheet->setCellValue("E$row",$item->Bkode);
        $sheet->setCellValue("F$row",$item->Bnama);
        $sheet->setCellValue("G$row",$item->Bsatbesar);
        $sheet->setCellValue("H$row",$item->Bisisatkecil);
        $sheet->setCellValue("I$row",$item->Bsatkecil);
        $sheet->setCellValue("J$row",$item->Bsnama);
        $sheet->setCellValue("K$row",$item->Bketerangan);
        if ($item->Bisaktif == 1) {
            $sheet->setCellValue("L$row", "Aktif");
        }else{
            $sheet->setCellValue("L$row", "Non-Aktif");
        }
        $sheet->getStyle("A$row:L$row")->applyFromArray(array_merge($allBorders));
    }
}
// Flush to client

foreach ($headers as $header) {
    header($header);
}
// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
$writer->save("php://output");
// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
