<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Add New A/P Invoice Type</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var elements = ["InvoiceType", "InvoiceTypeDescs", "CtlAccId"];
			BatchFocusRegister(elements);
			RegisterFormSubmitByEnter("CtlAccId", "frm");
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
	<legend class="bold">Add New A/P Invoice Type</legend>
	<form id="frm" action="<?php print($helper->site_url("common.apinvoicetype/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
            <tr>
                <td align="right">Prefix :</td>
                <td><input type="text" name="InvoicePrefix" id="InvoicePrefix" size="5" maxlength="2" value="<?php print($apinvoicetype->InvoicePrefix); ?>" required/></td>
            </tr>
			<tr>
				<td align="right">Invoice Type :</td>
				<td><input type="text" name="InvoiceType" id="InvoiceType" size="20" maxlength="50" value="<?php print($apinvoicetype->InvoiceType); ?>" required/></td>
			</tr>
			<tr>
				<td align="right">Description :</td>
				<td><input type="text" name="InvoiceTypeDescs" id="InvoiceTypeDescs" size="30" maxlength="50" value="<?php print($apinvoicetype->InvoiceTypeDescs); ?>" required/></td>
			</tr>
            <tr>
                <td align="right">Tax Scheme :</td>
                <td><select name="TaxSchemeId" id="TaxSchemeId" required>
                        <?php
                        /** @var $taxscheme TaxRate[] */
                        foreach ($taxscheme as $tax) {
                            if ($tax->Id == $apinvoicetype->TaxSchemeId) {
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
				<td><select name="CtlAccId" id="CtlAccId" required>
                        <option value="0"></option>
					<?php
					foreach ($accounts as $account) {
						if ($account->Id == $apinvoicetype->CtlAccId) {
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
					<a href="<?php print($helper->site_url("common.apinvoicetype")); ?>">A/P Invoice Type List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>

</html>
