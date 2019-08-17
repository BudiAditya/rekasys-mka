<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Edit A/R Invoice Type</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var elements = ["InvoicePrefix", "InvoiceType", "InvoiceTypeDescs", "RevAccId"];
			BatchFocusRegister(elements);
			RegisterFormSubmitByEnter("RevAccId", "frm");
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
	<legend class="bold">Edit A/R Invoice Type</legend>
	<form id="frm" action="<?php print($helper->site_url("common.arinvoicetype/edit/".$arinvoicetype->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td align="right">Prefix :</td>
				<td><input type="text" name="InvoicePrefix" id="InvoicePrefix" size="5" maxlength="2" value="<?php print($arinvoicetype->InvoicePrefix); ?>" required/></td>
			</tr>
            <tr>
                <td align="right">Invoice Type :</td>
                <td><input type="text" name="InvoiceType" id="InvoiceType" size="20" maxlength="50" value="<?php print($arinvoicetype->InvoiceType); ?>" required/></td>
            </tr>
			<tr>
				<td align="right">Description :</td>
				<td><input type="text" name="InvoiceTypeDescs" id="InvoiceTypeDescs" size="30" maxlength="50" value="<?php print($arinvoicetype->InvoiceTypeDescs); ?>" required/></td>
			</tr>
			<tr>
				<td align="right">Tax Scheme :</td>
				<td><select name="TaxSchemeId" id="TaxSchemeId" required>
					<?php
                    /** @var $taxscheme TaxRate[] */
                    foreach ($taxscheme as $tax) {
						if ($tax->Id == $arinvoicetype->TaxSchemeId) {
							printf('<option value="%s" selected="selected">%s - %s</option>', $tax->Id, $tax->TaxSchCd, $tax->TaxSchDesc);
						} else {
							printf('<option value="%s">%s - %s</option>', $tax->Id, $tax->TaxSchCd, $tax->TaxSchDesc);
						}
					}
					?>
				</select>
				</td>
			</tr>
            <tr>
                <td align="right">Revenue Account :</td>
                <td><select name="RevAccId" id="RevAccId" required>
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $arinvoicetype->RevAccId) {
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
					<button type="submit">Update</button>
					&nbsp;
					<a href="<?php print($helper->site_url("common.arinvoicetype")); ?>">A/R Invoice Type List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>

</html>
