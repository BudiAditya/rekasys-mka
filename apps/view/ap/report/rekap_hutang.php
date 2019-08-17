<?php
// Just bootstrap
switch ($output) {
	case "xls":
	case "xlsx":
		require_once(LIBRARY . "PHPExcel.php");
		include("rekap_hutang.excel.php");
		break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");
		include("rekap_hutang.pdf.php");
		break;
	default:
		include("rekap_hutang.web.php");
		break;
}


// End of file: rekap_hutang.php
