<?php
/** @var $output string */
/** @var $debtors Debtor[] */ /** @var $debt Debtor */ /** @var $debtorId int */
/** @var $billTypes BillType[] */ /** @var $bill BillType */ /** @var $billTypeId int */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */

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