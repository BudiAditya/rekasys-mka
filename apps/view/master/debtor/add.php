<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Add New Debtor/Customer</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var elements = ["DebtorTypeId", "DebtorName","DebtorCd", "Address1", "Address2", "Address3", "PostCd", "TelNo", "HandPhone","FaxNo", "EmailAdd","Npwp", "ContactPerson", "Position", "WebSite", "Remark"];
			BatchFocusRegister(elements);

            $("#DebtorCd").focus();

            $("#DebtorName").change(function () {
                var url = "<?php print($helper->site_url("master.debtor/autodebtorcd/")); ?>" + this.value;
                $.get(url, function (data) {
                    $("#DebtorCd").val(data);
                });
            });

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
	<legend class="bold">Add New Debtor/Customer</legend>
	<form id="frm" action="<?php print($helper->site_url("master.debtor/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName) ?></td>
				<td colspan="6">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="DebtorTypeId">Debtor Type :</label></td>
                <td><select id="DebtorTypeId" name="DebtorTypeId" required>
                    <?php
                    foreach ($debtorTypes as $debtorType) {
                        if ($debtorType->Id == $debtor->DebtorTypeId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $debtorType->Id, $debtorType->DebtorTypeCd, $debtorType->DebtorTypeDesc);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $debtorType->Id, $debtorType->DebtorTypeCd, $debtorType->DebtorTypeDesc);
                        }
                    }
                    ?>
                </select></td>
				<td colspan="6">&nbsp;</td>
			</tr>
            <tr>
                <td class="right"><label for="DebtorName">Debtor Name :</label></td>
                <td><input  type="text" id="DebtorName" name="DebtorName" size="50" maxlength="50" value="<?php print($debtor->DebtorName);?>" onkeyup="this.value = this.value.toUpperCase();" required></td>
                <td class="right"><label for="DebtorCd">Debtor Code :</label></td>
                <td><input  type="text" id="DebtorCd" name="DebtorCd" size="20" maxlength="20" value="<?php print($debtor->DebtorCd);?>" placeholder="AUTO" readonly></td>
				<td colspan="4">&nbsp;</td>
            </tr>
			<tr>
				<td class="right"><label for="CoreBusiness">Core Business :</label></td>
				<td colspan="3"><input type="text" id="CoreBusiness" name="CoreBusiness" size="100" value="<?php print($debtor->CoreBusiness); ?>" onkeyup="this.value = this.value.toUpperCase();"></td>
				<td class="right"><label for="BankAccount">Bank :</label></td>
				<td colspan="3"><input type="text" id="BankAccount" name="BankAccount" size="60" value="<?php print($debtor->BankAccount); ?>"></td>
			</tr>
            <tr>
                <td class="right"><label for="Address1">Address 1 :</label></td>
                <td colspan="3"><input  type="text" id="Address1" name="Address1" size="100" maxlength="150" value="<?php print($debtor->Address1);?>" required></td>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="Address2">Address 2 :</label></td>
                <td colspan="3"><input  type="text" id="Address2" name="Address2" size="100" maxlength="150" value="<?php print($debtor->Address2);?>"></td>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="Address3">City :</label></td>
                <td><input  type="text" id="Address3" name="Address3" size="50" maxlength="50" value="<?php print($debtor->Address3);?>" required></td>
                <td class="right"><label for="PostCd">Post Code :</label></td>
                <td><input  type="text" id="PostCd" name="PostCd" size="20" maxlength="15" value="<?php print($debtor->PostalCode);?>"></td>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="TelNo">No. Telephone :</label></td>
                <td><input  type="text" id="TelNo" name="TelNo" size="50" maxlength="50" value="<?php print($debtor->PhoneNo);?>" required></td>
                <td class="right"><label for="HandPhone">Mobile Phone :</label></td>
                <td><input  type="text" id="HandPhone" name="HandPhone" size="20" maxlength="20" value="<?php print($debtor->HandPhone);?>"></td>
                <td class="right"><label for="FaxNo">Facsimile :</label></td>
                <td><input  type="text" id="FaxNo" name="FaxNo" size="20" maxlength="20" value="<?php print($debtor->FaxNo);?>"></td>
                <td class="right"><label for="EmailAdd">Email :</label></td>
                <td><input  type="text" id="EmailAdd" name="EmailAdd" size="20" maxlength="50" value="<?php print($debtor->EmailAddress);?>"></td>
            </tr>
            <tr>
                <td class="right"><label for="Npwp">NPWP :</label></td>
                <td><input  type="text" id="Npwp" name="Npwp" size="50" maxlength="50" value="<?php print($debtor->Npwp);?>"></td>
                <td class="right"><label for="ContactPerson">Contact Person :</label></td>
                <td><input  type="text" id="ContactPerson" name="ContactPerson" size="20" maxlength="20" value="<?php print($debtor->ContactPerson);?>" required></td>
                <td class="right"><label for="Position">Position :</label></td>
                <td><input  type="text" id="Position" name="Position" size="20" maxlength="20" value="<?php print($debtor->Position);?>"></td>
                <td class="right"><label for="WebSite">Website :</label></td>
                <td><input  type="text" id="WebSite" name="WebSite" size="20" maxlength="50" value="<?php print($debtor->WebSite);?>"></td>
            </tr>
            <tr>
                <td class="right"><label for="Remark">Notes :</label></td>
                <td colspan="3">
					<textarea rows="3" cols="40" id="Remark" name="Remark"><?php print($debtor->Remark);?></textarea>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button type="submit">Save</button>
                    <a href="<?php print($helper->site_url("master.debtor")); ?>">Debtor/Customer List</a>
                </td>
				<td colspan="6">&nbsp;</td>
            </tr>
		</table>
	</form>
</fieldset>
</body>
</html>
