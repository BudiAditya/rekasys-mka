<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Edit Stock Opname Step 1</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#Date").customDatePicker();
			$("#Eta").customDatePicker();
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
	<legend><span class="bold">Edit Stock Opname Step 1 - Data Master</span></legend>
	<form action="<?php print($helper->site_url("inventory.so/edit/" . $stockOpname->Id . "?step=master")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td class="right"><label for="DocumentNo">No. Dokumen:</label></td>
				<td><input type="text" name="DocumentNo" id="DocumentNo" value="<?php print($stockOpname->DocumentNo); ?>" readonly="readonly" /></td>
			</tr>
			<tr>
				<td class="right"><label for="Date">Tanggal:</label></td>
				<td><input type="text" id="Date" name="Date" value="<?php print($stockOpname->FormatDate(JS_DATE)); ?>" size="12"></td>
			</tr>
			<tr>
				<td class="right"><label for="Warehouse">Gudang :</label></td>
				<td><select id="Warehouse" name="Warehouse">
					<option value="">-- PILIH GUDANG --</option>
					<?php
					foreach ($warehouses as $warehouse) {
						if ($warehouse->Id == $stockOpname->WarehouseId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $warehouse->Id, $warehouse->Code, $warehouse->Name);
						} else {
							printf('<option value="%d">%s - %s</option>', $warehouse->Id, $warehouse->Code, $warehouse->Name);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="Note">Keterangan:</label></td>
				<td><textarea rows="3" cols="60" id="Note" name="Note"><?php print($stockOpname->Note); ?></textarea></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Berikutnya &gt;</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("inventory.so")); ?>">Daftar Stock Opname</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
