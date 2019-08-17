<!DOCTYPE html>
<html>
<?php
/** @var $accounts  */ /** @var $ */ /** @var $ */
?>
<head>
	<title>Rekasys - Add New Debtor Type</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var elements = ["DebtorTypeCd", "DebtorTypeDesc", "AccCtl"];
			BatchFocusRegister(elements);
			RegisterFormSubmitByEnter("AccCtl", "frm");
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
	<legend class="bold">Add New Debtor Type</legend>
	<form id="frm" action="<?php print($helper->site_url("common.debtortype/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td align="right">Code :</td>
				<td><input type="text" name="DebtorTypeCd" id="DebtorTypeCd" size="10" maxlength="5" value="<?php print($debtorType->DebtorTypeCd); ?>" required/></td>
			</tr>
			<tr>
				<td align="right">Debtor Type :</td>
				<td><input type="text" name="DebtorTypeDesc" id="DebtorTypeDesc" size="30" maxlength="50" value="<?php print($debtorType->DebtorTypeDesc); ?>" required/></td>
			</tr>
			<tr>
				<td align="right">Receivables Account :</td>
				<td><select name="AccCtl" id="AccCtl" required>
					<?php
					foreach ($accounts as $account) {
						if ($account->Id == $debtorType->AccControlId) {
							printf('<option value="%s" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%s">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					&nbsp;
					<a href="<?php print($helper->site_url("common.debtortype")); ?>">Debtor Type List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>

</html>
