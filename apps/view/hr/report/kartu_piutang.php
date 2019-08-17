<?php
// Just bootstrap
switch ($output) {
	case "xls":
	case "xlsx":
		require_once(LIBRARY . "PHPExcel.php");
		include("kartu_piutang.excel.php");
		break;
	default:
		include("kartu_piutang.web.php");
		break;
}


// EoF: kartu_piutang.php