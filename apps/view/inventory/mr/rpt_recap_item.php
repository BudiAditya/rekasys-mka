<?php
if ($output == "xls") {
	require_once(LIBRARY . "PHPExcel.php");
	include("rpt_recap_item.excel.php");
} else {
	include("rpt_recap_item.web.php");
}
// End of File: rpt_recap_item.php
