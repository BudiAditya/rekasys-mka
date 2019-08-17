<?php
// Just bootstrap
switch ($output) {
	case "xls":
	case "xlsx":
		require_once(LIBRARY . "PHPExcel.php");
		include("rekap_piutang.excel.php");
		break;
	default:
		include("rekap_piutang.web.php");
		break;
}

// EoF: rekap_piutang.php