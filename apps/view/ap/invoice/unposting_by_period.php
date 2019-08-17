<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Un-Posting Invoice Supplier By Periode</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#start").customDatePicker();
			$("#end").customDatePicker();
		});
	</script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Un-Posting Invoice Supplier By Periode</span></legend>

	<form action="<?php print($helper->site_url("ap.invoice/unposting_by_period")); ?>" method="get">
		<div class="subTitle center">
			Membatalkan Invoice Supplier yang Sudah Terposting Sebagai Voucher
		</div><br />
		<div class="center">
			<label for="start">Periode : </label>
			<input type="text" id="start" name="start" value="<?php print(date(JS_DATE, $start)); ?>" size="12" />
			<label for="end"> s.d. </label>
			<input type="text" id="end" name="end" value="<?php print(date(JS_DATE, $end)); ?>" size="12" />
			<br />
			<button type="submit">UN - Posting</button>
			<br /><br />
			Proses ini akan membatalkan semua Invoice Supplier yang terposting dan mengembalikan statusnya menjadi DRAFT.<br />Untuk Invoice Supplier lainnya tidak berpengaruh.
		</div>
	</form>
</fieldset>

</body>
</html>
