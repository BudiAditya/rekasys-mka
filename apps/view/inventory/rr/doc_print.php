<?php
if ($output == "xls") {
    require_once(LIBRARY . "PHPExcel.php");
    require("doc_print.excel.php");
} else {
    require_once(LIBRARY . 'tabular_pdf.php');
    define('FPDF_FONTPATH','font/');
    require("doc_print.pdf.php");
}