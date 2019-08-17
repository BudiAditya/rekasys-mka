<?php
if ($output == "xls") {
    require_once(LIBRARY . "PHPExcel.php");
    require("item_recap.excel.php");
} elseif ($output == "pdf") {
    require_once(LIBRARY . 'tabular_pdf.php');
    define('FPDF_FONTPATH','font/');
    require("item_recap.pdf.php");
} else {
    require("item_recap.web.php");
}