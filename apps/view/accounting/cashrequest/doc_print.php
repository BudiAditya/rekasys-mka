<?php
/** @var CashRequest[] $report */ /** @var string $output */
switch ($output) {
	case "xls":
		require_once(LIBRARY . "PHPExcel.php");
		include("doc_print.excel.php");
		break;
	default:
		require_once(LIBRARY . "tabular_pdf.php");
		include("doc_print.pdf.php");
		break;
}