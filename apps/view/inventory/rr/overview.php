<?php
if ($output == "xls") {
    require_once(LIBRARY . "PHPExcel.php");
    require("overview.excel.php");
} elseif ($output == "pdf") {
    require_once(LIBRARY . 'tabular_pdf.php');
    define('FPDF_FONTPATH','font/');
    require("overview.pdf.php");
} else {
    require("overview.web.php");
}