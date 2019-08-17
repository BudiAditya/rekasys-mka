<!DOCTYPE HTML>
<html>
<?php
/** @var Ar\OpeningBalance $obal */ /** @var Creditor $creditor */
?>
<head>
	<title>Rekasys - Edit Saldo Awal Hutang Creditor</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/	common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#DebitAmount").autoNumeric();
			$("#CreditAmount").autoNumeric();
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
	<legend><span class="bold">Edit A/P Opening Balance</span></legend>

	<form action="<?php print($helper->site_url("ap.obal/edit/" . $obal->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right">Creditor Name :</td>
				<td><?php printf('%s - %s', $creditor->CreditorCd, $creditor->CreditorName); ?></td>
			</tr>
			<tr>
				<td class="bold right"><label for="CreditAmount">Saldo Awal :</label></td>
				<td><input type="text" class="bold right" id="CreditAmount" name="CreditAmount" value="<?php print(number_format($obal->CreditAmount, 2)); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Update</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("ap.obal")); ?>">A/P Opening List</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
