<!DOCTYPE html>
<html>
<?php
/** @var Hr\OpeningBalance $obal */ /** @var Employee[] $employees */
?>
<head>
	<title>Rekasys - Entry Employee Loan Balance</title>
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
	<legend><span class="bold">Entry Employee Loan Balance</span></legend>

	<form action="<?php print($helper->site_url("hr.obal/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="EmployeeId">Employee Name :</label></td>
				<td>
					<select id="EmployeeId" name="EmployeeId">
						<option value="">-- PILIH KARYAWAN --</option>
						<?php
						foreach ($employees as $employee) {
							if ($obal->EmployeeId == $employee->Id) {
								printf('<option value="%d" selected="selected">%s - %s</option>', $employee->Id, $employee->Nik, $employee->Nama);
							} else {
								printf('<option value="%d">%s - %s</option>', $employee->Id, $employee->Nik, $employee->Nama);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="DebitAmount">Opening Balance :</label></td>
				<td><input class="bold right" type="text" id="DebitAmount" name="DebitAmount" value="<?php print(number_format($obal->DebitAmount, 2)); ?>" /></td>
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
