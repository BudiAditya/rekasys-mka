<!DOCTYPE html>
<html>
<?php
/** @var Hr\OpeningBalance $obal */ /** @var Employee $employee */
?>
<head>
	<title>Mega PMS - Edit Saldo Awal Piutang Karyawan</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#DebitAmount").autoNumeric();
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
	<legend><span class="bold">Edit Saldo Awal Piutang Karyawan</span></legend>

	<form action="<?php print($helper->site_url("hr.obal/edit/" . $obal->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right">Employee Name :</td>
				<td><?php printf('%s - %s', $employee->Nik,$employee->Nama); ?></td>
			</tr>
			<tr>
				<td class="bold right"><label for="DebitAmount">Opening Balance :</label></td>
				<td><input type="text" class="right bold" id="DebitAmount" name="DebitAmount" value="<?php print(number_format($obal->DebitAmount, 2)); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Submit</button></td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
