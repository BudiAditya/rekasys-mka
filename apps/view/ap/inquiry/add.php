<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Entry Items Price</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function(e) {
			$("#ValidStart").customDatePicker();
			$("#ValidEnd").customDatePicker();
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
	<legend><span class="bold">Entry Items Price</span></legend>

	<form action="<?php print($helper->site_url("ap.inquiry/add")) ?>" method="POST">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right"><label for="SupplierId">Supplier :</label></td>
				<td><select id="SupplierId" name="SupplierId">
					<option value="">-- PILIH SUPPLIER --</option>
					<?php
					foreach ($suppliers as $supplier) {
						if ($supplier->Id == $inquiry->SupplierId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
						} else {
							printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="ItemId">Item Name :</label></td>
				<td><select id="ItemId" name="ItemId">
					<option value="">-- PILIH ITEM --</option>
					<?php
					foreach ($items as $item) {
						if ($item->Id == $inquiry->ItemId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $item->Id, $item->ItemCode, $item->ItemName);
						} else {
							printf('<option value="%d">%s - %s</option>', $item->Id, $item->ItemCode, $item->ItemName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="Price">Price :</label></td>
				<td><input type="text" class="right" id="Price" name="Price" value="<?php print(number_format($inquiry->Price)); ?>" size="12"></td>
			</tr>
			<tr>
				<td class="right"><label for="ValidStart">Valid Start Date :</label></td>
				<td><input type="text" id="ValidStart" name="ValidStart" value="<?php print($inquiry->FormatValidStart(JS_DATE)); ?>" size="12" /></td>
			</tr>
			<tr>
				<td class="right"><label for="ValidEnd">End Valid Date :</label></td>
				<td><input type="text" id="ValidEnd" name="ValidEnd" value="<?php print($inquiry->FormatValidEnd(JS_DATE)); ?>" size="12" /></td>
			</tr>
            <tr>
                <td class="right"><label for="ReffNo">Refference No. :</label></td>
                <td><input type="text" id="ReffNo" name="ReffNo" value="<?php print($inquiry->ReffNo); ?>" size="30" /></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("ap.inquiry")); ?>">Items Price List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
