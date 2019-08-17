<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $voucher Voucher */
?>
<head>
	<title>Rekasys - Edit Voucher Step 1</title>
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
	<legend><span class="bold">Edit Voucher Step 1 - Data Master</span></legend>
	<form action="<?php print($helper->site_url("accounting.voucher/edit_master/" . $voucher->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td class="right">Voucher Type :</td>
				<td>
					<input type="hidden" name="DocumentType" value="<?php print($voucher->DocumentTypeId); ?>" />
					<?php printf("%s - %s", $docType->DocCode, $docType->Description); ?>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="DocumentNo">Voucher No. :</label></td>
				<td><input type="text" name="DocumentNo" id="DocumentNo" value="<?php print($voucher->DocumentNo); ?>" readonly="readonly" /></td>
			</tr>
			<tr>
				<td class="right"><label for="Date">Date :</label></td>
				<td><input type="text" id="Date" name="Date" value="<?php print($voucher->FormatDate(JS_DATE)); ?>" size="12"></td>
			</tr>
			<tr>
				<td class="right"><label for="Note">Notes :</label></td>
				<td><textarea rows="3" cols="60" id="Note" name="Note"><?php print($voucher->Note); ?></textarea></td>
			</tr>
            <tr>
                <td class="right"><label for="RStatus">Report Status :</label></td>
                <td><select id="RStatus" name="RStatus" required>
                        <option value="1" <?php print($voucher->RStatus == 1 ? 'selected="selected"' : ''); ?>>1 - Normal</option>
                        <option value="2" <?php print($voucher->RStatus == 2 ? 'selected="selected"' : ''); ?>>2 - Advanced</option>
                    </select></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Next &gt;</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("accounting.voucher")); ?>">Voucher List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
