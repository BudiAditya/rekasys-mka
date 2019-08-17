<?php
if ($output == "xls") {
    require_once(LIBRARY . "PHPExcel.php");
    include("items_xls.php");
} else{
    require_once(LIBRARY . "tabular_pdf.php");
    include("items_pdf.php");
}