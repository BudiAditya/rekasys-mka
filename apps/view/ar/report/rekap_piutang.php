<?php
// Just bootstrap
switch ($output) {
	case "xls":
	case "xlsx":
		require_once(LIBRARY . "PHPExcel.php");
		include("rekap_piutang.excel.php");
		break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");
		include("rekap_piutang.pdf.php");
		break;
	default:
		include("rekap_piutang.web.php");
		break;
}


// End of file: rekap_piutang.php
