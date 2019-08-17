<?php
// Just bootstrap
switch ($output) {
	case "xls":
	case "xlsx":
		require_once(LIBRARY . "PHPExcel.php");
		include("detail_aging.excel.php");
		break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");
		include("detail_aging.pdf.php");
		break;
	default:
		include("detail_aging.web.php");
		break;
}



// End of file: detail_aging.php
