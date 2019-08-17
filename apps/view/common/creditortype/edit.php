<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Edit Vendor Type</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var elements = ["CreditorTypeCd", "CreditorTypeDesc", "AccControlId","Update"];
			BatchFocusRegister(elements);
			RegisterFormSubmitByEnter("AccControlId", "frm");
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
	<legend class="bold">Edit Vendor Type</legend>
	<form id="frm" action="<?php print($helper->site_url("common.creditortype/edit/".$creditorType->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td align="right">Type Code :</td>
				<td><input type="text" name="CreditorTypeCd" id="CreditorTypeCd" size="10" maxlength="10" value="<?php print($creditorType->CreditorTypeCd); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
			</tr>
			<tr>
				<td align="right">Vendor Type :</td>
				<td><input type="text" name="CreditorTypeDesc" id="CreditorTypeDesc" size="50" maxlength="50" value="<?php print($creditorType->CreditorTypeDesc); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
			</tr>
            <tr>
                <td align="right">Liabilities Account :</td>
                <td><select name="AccControlId" id="AccControlId">
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $creditorType->AccControlId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button id="Update" type="submit">Update</button>
					&nbsp;
					<a href="<?php print($helper->site_url("common.creditortype")); ?>">Vendor Type List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>

</html>
