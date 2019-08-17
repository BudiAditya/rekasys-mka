<?php
if ($OutPut == 2) {
	require_once(LIBRARY . "PHPExcel.php");
	require("report.excel.php");
} else {
	require("report.web.php");
}

// End of File: overview.php
