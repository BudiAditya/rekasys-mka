<?php
if ($output == "xls" || $output == "xlsx") {
	require_once(LIBRARY . "PHPExcel.php");
	require("track.excel.php");
} else {
	require("track.web.php");
}

// End of File: track.php
