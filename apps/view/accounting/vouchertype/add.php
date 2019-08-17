<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Tambah Data Accounting Voucher Master</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["VoucherCd", "VoucherDesc"];
			BatchFocusRegister(elements);
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
	<legend><b>Tambah Data Jenis Accounting Voucher</b></legend>
	<form id="frm" action="<?php print($helper->site_url("common.vouchertype/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Kode</td>
				<td><input type="text" name="VoucherCd" size="3" maxlength="3" class="text2" id="VoucherCd" value="<?php print($voucher->VoucherCd); ?>" /></td>
			</tr>
			<tr>
				<td>Jenis Voucher</td>
				<td colspan="2">
					<input type="text" name="VoucherDesc" size="50" maxlength="150" class="text2" id="VoucherDesc" value="<?php print($voucher->VoucherDesc); ?>" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.vouchertype")); ?>" class="button">Daftar Jenis Accounting Voucher</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
