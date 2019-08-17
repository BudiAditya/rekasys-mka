<?php
// Just bootstrap
$filename = dirname(__FILE__) .  "/add." . $step . ".php";
if (file_exists($filename)) {
	include($filename);
} else {
	throw new Exception("File path not found ! Please contact your system admin.");
}

// End of File: add.php
