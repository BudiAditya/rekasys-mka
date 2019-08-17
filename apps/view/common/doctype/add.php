<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Tambah Data Dokumen</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["DocCode", "Description", "ModuleId", "AccVoucherId"];
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
	<legend><b>Tambah Data Dokumen</b></legend>
	<form id="frm" action="<?php print($helper->site_url("common.doctype/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Kode</td>
				<td><input type="text" class="text2" name="DocCode" id="DocCode" maxlength="2" size="4" value="<?php print($doctype->DocCode); ?>" /></td>
			</tr>
			<tr>
				<td>Nama Dokumen</td>
				<td><input type="text" class="text2" name="Description" id="Description" maxlength="50" size="50" value="<?php print($doctype->Description); ?>" /></td>
			</tr>
			<tr>
				<td>Module</td>
				<td><select name="ModuleId" class="text2" style="width:250px" id="ModuleId">
					<option value=""></option>
					<?php
					foreach ($modules as $mod) {
						if ($mod->Id == $doctype->ModuleId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $mod->Id, $mod->ModuleCd, $mod->ModuleName);
						} else {
							printf('<option value="%d">%s - %s</option>', $mod->Id, $mod->ModuleCd, $mod->ModuleName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td>Voucher</td>
				<td><select name="AccVoucherId" class="text2" style="width:250px" id="AccVoucherId">
					<option value=""></option>
					<?php
					foreach ($vouchers as $voc) {
						if ($voc->Id == $doctype->AccVoucherId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $voc->Id, $voc->VoucherCd, $voc->VoucherDesc);
						} else {
							printf('<option value="%d">%s - %s</option>', $voc->Id, $voc->VoucherCd, $voc->VoucherDesc);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.doctype")); ?>" class="button">Daftar Dokumen</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
