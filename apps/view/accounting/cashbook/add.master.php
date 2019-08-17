<!DOCTYPE HTML>
<?php /** @var $lockDocId null|int */ /** @var $title string */ /** @var $controller string */ /** @var $company Company */ /** @var $voucher Voucher */ ?>
<html>
<head>
	<title>Rekasys - Entry Voucher <?php print($title); ?> Step 1</title>
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
	<legend><span class="bold">Entry Voucher <?php print($title); ?> Step 1 - Data Master</span></legend>
	<form action="<?php print($helper->site_url("$controller/add?which=master")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<?php if ($lockDocId == null) { ?>
			<tr>
				<td class="right"><label for="DocumentType">Voucher Type :</label></td>
				<td><select id="DocumentType" name="DocumentType">
					<option value="3" <?php print($voucher->DocumentTypeId == 3 ? 'selected="selected"' : ''); ?>><?php print($title); ?> MASUK</option>
					<option value="2" <?php print($voucher->DocumentTypeId == 2 ? 'selected="selected"' : ''); ?>><?php print($title); ?> KELUAR</option>
				</select></td>
			</tr>
			<?php } else { ?>
			<tr>
				<td class="right">Voucher Type :</td>
				<td><?php print($title); ?></td>
			</tr>
			<?php } ?>
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
				<td>&nbsp;</td>
				<td>
					<button type="submit">Next &gt;</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url($controller)); ?>">Voucher <?php print($title); ?> List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
