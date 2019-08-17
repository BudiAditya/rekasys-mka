<!DOCTYPE html>
<?php
/** @var $creditor Creditor */
?>
<html>
<head>
	<title>Rekasys - View Vendor</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend class="bold">View Vendor</legend>
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName) ?></td>
				<td colspan="6">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="CreditorTypeId">Vendor Type :</label></td>
                <td><?php printf("%s - %s", $creditor->CreditorTypeCd, $creditorType->CreditorTypeDesc) ?></td>
				<td colspan="6">&nbsp;</td>
			</tr>
            <tr>
                <td class="right"><label for="CreditorName">Vendor Name :</label></td>
                <td><input  type="text" id="CreditorName" name="CreditorName" size="50" maxlength="50" value="<?php print($creditor->CreditorName);?>" readonly></td>
                <td class="right"><label for="CreditorCd">Vendor Code :</label></td>
                <td><input  type="text" id="CreditorCd" name="CreditorCd" size="20" maxlength="20" value="<?php print($creditor->CreditorCd);?>" placeholder="AUTO" readonly></td>
				<td colspan="4">&nbsp;</td>
            </tr>
			<tr>
				<td class="right"><label for="CoreBusiness">Core Business :</label></td>
				<td colspan="3"><input type="text" id="CoreBusiness" name="CoreBusiness" size="100" value="<?php print($creditor->CoreBusiness); ?>" readonly></td>
				<td class="right"><label for="BankAccount">Bank :</label></td>
				<td colspan="3"><input type="text" id="BankAccount" name="BankAccount" size="60" value="<?php print($creditor->BankAccount); ?>" readonly></td>
			</tr>
            <tr>
                <td class="right"><label for="Address1">Address 1 :</label></td>
                <td colspan="3"><input  type="text" id="Address1" name="Address1" size="100" maxlength="150" value="<?php print($creditor->Address1);?>" readonly></td>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="Address2">Address 2 :</label></td>
                <td colspan="3"><input  type="text" id="Address2" name="Address2" size="100" maxlength="150" value="<?php print($creditor->Address2);?>" readonly></td>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="Address3">City :</label></td>
                <td><input  type="text" id="Address3" name="Address3" size="50" maxlength="50" value="<?php print($creditor->Address3);?>"  readonly></td>
                <td class="right"><label for="PostCd">Post Code :</label></td>
                <td><input  type="text" id="PostCd" name="PostCd" size="20" maxlength="15" value="<?php print($creditor->PostalCode);?>" readonly></td>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td class="right"><label for="TelNo">No. Telephone :</label></td>
                <td><input  type="text" id="TelNo" name="TelNo" size="50" maxlength="50" value="<?php print($creditor->PhoneNo);?>"  readonly></td>
                <td class="right"><label for="HandPhone">Mobile Phone :</label></td>
                <td><input  type="text" id="HandPhone" name="HandPhone" size="20" maxlength="20" value="<?php print($creditor->HandPhone);?>" readonly></td>
                <td class="right"><label for="FaxNo">Facsimile :</label></td>
                <td><input  type="text" id="FaxNo" name="FaxNo" size="20" maxlength="20" value="<?php print($creditor->FaxNo);?>" readonly></td>
                <td class="right"><label for="EmailAdd">Email :</label></td>
                <td><input  type="text" id="EmailAdd" name="EmailAdd" size="20" maxlength="50" value="<?php print($creditor->EmailAddress);?>" readonly></td>
            </tr>
            <tr>
                <td class="right"><label for="Npwp">NPWP :</label></td>
                <td><input  type="text" id="Npwp" name="Npwp" size="50" maxlength="50" value="<?php print($creditor->Npwp);?>" readonly></td>
                <td class="right"><label for="ContactPerson">Contact Person :</label></td>
                <td><input  type="text" id="ContactPerson" name="ContactPerson" size="20" maxlength="20" value="<?php print($creditor->ContactPerson);?>" readonly></td>
                <td class="right"><label for="Position">Position :</label></td>
                <td><input  type="text" id="Position" name="Position" size="20" maxlength="20" value="<?php print($creditor->Position);?>" readonly></td>
                <td class="right"><label for="WebSite">Website :</label></td>
                <td><input  type="text" id="WebSite" name="WebSite" size="20" maxlength="50" value="<?php print($creditor->WebSite);?>" readonly></td>
            </tr>
            <tr>
                <td class="right"><label for="Remark">Notes :</label></td>
                <td colspan="3">
					<textarea rows="3" cols="40" id="Remark" name="Remark" readonly><?php print($creditor->Remark);?></textarea>
				<td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <a href="<?php print($helper->site_url("master.creditor")); ?>">Vendor List</a>
                </td>
				<td colspan="6">&nbsp;</td>
            </tr>
		</table>
</fieldset>
</body>
</html>
