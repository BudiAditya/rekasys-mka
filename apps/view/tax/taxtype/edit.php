<!DOCTYPE html>
<?php /** @var  $taxType TaxType */  ?>

<html>
<head>
	<title>Rekasys - Edit Tax Type</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var elements = ["TaxCode", "TaxType","TaxMode","TaxRate","IsDeductable","TempAccId","PostAccId","Update"];
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
	<legend class="bold">Edit Tax Type</legend>
	<form id="frm" action="<?php print($helper->site_url("tax.taxtype/edit/".$taxType->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Perusahaan :</td>
				<td colspan="5"><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td align="right">Kode Pajak :</td>
				<td colspan="5"><input type="text" name="TaxCode" id="TaxCode" size="10" maxlength="10" value="<?php print($taxType->TaxCode); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
			</tr>
			<tr>
				<td align="right">Nama Pajak :</td>
				<td colspan="5"><input type="text" name="TaxType" id="TaxType" size="50" maxlength="50" value="<?php print($taxType->TaxType); ?>" required/></td>
			</tr>
            <tr>
                <td align="right">Jenis Pajak :</td>
                <td><select name="TaxMode" id="TaxMode" required>
                        <option value="1" <?php print($taxType->TaxMode == 1 ? 'selected="selected"' : '');?>> 1 - Masukan </option>
                        <option value="2" <?php print($taxType->TaxMode == 2 ? 'selected="selected"' : '');?>> 2 - Keluaran </option>
                    </select>
                </td>
                <td align="right">Tarif Pajak :</td>
                <td><input type="text" class="bold right" name="TaxRate" id="TaxRate" size="5" maxlength="5" value="<?php print($taxType->TaxRate); ?>" required/>%</td>
                <td align="right">Pengurang DPP? :</td>
                <td><select name="IsDeductable" id="IsDeductable" required>
                        <option value="0" <?php print($taxType->IsDeductable == 0 ? 'selected="selected"' : '');?>> 0 - Tidak </option>
                        <option value="1" <?php print($taxType->IsDeductable == 1 ? 'selected="selected"' : '');?>> 1 - Ya </option>
                    </select>
                </td>
            </tr>
			<tr>
				<td align="right">Temp Account :</td>
				<td colspan="5"><select name="TempAccId" id="TempAccId" style="width:450px;">
					<?php
					foreach ($accounts as $account) {
						if ($account->Id == $taxType->TempAccId) {
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
                <td align="right">Post Account :</td>
                <td colspan="5"><select name="PostAccId" id="PostAccId" style="width:450px;">
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $taxType->PostAccId) {
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
				<td colspan="5">
					<button id="Update" type="submit">UPDATE</button>
					&nbsp;
					<a href="<?php print($helper->site_url("tax.taxtype")); ?>">Tax Type List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>

</html>
