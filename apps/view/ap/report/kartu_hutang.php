<?php
// Just bootstrap
switch ($output) {
	case "xls":
	case "xlsx":
		require_once(LIBRARY . "PHPExcel.php");
		include("kartu_hutang.excel.php");
		break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");
		include("kartu_hutang.pdf.php");
		break;
	default:
		include("kartu_hutang.web.php");
		break;
}


// End of file: kartu_hutang.php
