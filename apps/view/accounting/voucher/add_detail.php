<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $docType DocType */ /** @var $accounts Coa[] */ /** @var $departments Department[] */ /** @var $activitys Activity[] */
/** @var $debtors Debtor[] */ /** @var $creditors Creditor[] */ /** @var $employees Employee[] */ /** @var $projects Project[] */
/** @var $voucher Voucher */ /** @var $details array */ /** @var $units Units[] */
?>
<head>
	<title>Rekasys - Entry Voucher Step 2</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		.colCode { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colText { display: inline-block; width: 260px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.blue { color: blue; }
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/select2.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/accounting.voucher.VcDetail.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var divDetails, template;
		var details = eval(<?php print(json_encode($details)); ?>);
		var activitys = eval(<?php print(json_encode($activitys)); ?>);

		$(document).ready(function () {
			divDetails = $("#details");
			template = $("#template").removeAttr("id");
			$("#btnAdd").click(function() { AddTemplate(); });

			if (details.length == 0) {
				AddTemplate();
			} else {
				for (var i = 0; i < details.length; i++) {
					AddTemplate(details[i]);
				}
			}
		});

		function AddTemplate(data) {
			var vcDetail = new VcDetail(template.clone(), data);
			var li = $("<li></li>").append(vcDetail.DivContainer);
			divDetails.append(li);

			if (data == undefined) {
				vcDetail.DivContainer.hide().slideDown();
			}
		}
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
	<legend><span class="bold">Entry Voucher Step 2 - Data Detail</span></legend>

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td class="bold right">Voucher Type :</td>
			<td><?php printf("%s - %s", $docType->DocCode, $docType->Description); ?></td>
		</tr>
		<tr>
			<td class="bold right">Voucher No. :</td>
			<td><?php print($voucher->DocumentNo); ?></td>
		</tr>
		<tr>
			<td class="bold right">Voucher Source :</td>
			<td><?php print($voucher->VoucherSource); ?></td>
		</tr>
		<tr>
			<td class="bold right">Voucher Date :</td>
			<td><?php print($voucher->FormatDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Notes :</td>
			<td><?php print(str_replace("\n", "<br />", $voucher->Note)); ?></td>
		</tr>
        <tr>
            <td class="bold right">Report Status :</td>
            <td><?php printf("%d - %s", $voucher->RStatus, $voucher->RStatus == 1 ? 'Normal' : 'Advanced'); ?></td>
        </tr>
	</table>

	<br />

	<form action="<?php print($helper->site_url("accounting.voucher/add_detail")); ?>" method="post">
		<input type="hidden" id="dummy" name="dummy" value="dummy" />

		<div id="container" style="min-width: 600px; display: inline-block; border: solid #000000 1px; padding: 5px;">
			<div class="bold center subTitle">Voucher Detail</div><br />

			<ol id="details" style="padding-left: 25px;"></ol>

			<br />

			<div id="navigation" class="center">
				<button type="button" onclick="window.location='<?php print($helper->site_url("accounting.voucher/add_master")); ?>';">&lt; Previous</button>
				&nbsp;&nbsp;&nbsp;
				<button type="button" id="btnAdd">Add Detail</button>
				&nbsp;&nbsp;&nbsp;
				<button type="submit">Next &gt;</button>
			</div>
		</div>
	</form>

</fieldset>

<?php
$buff = '<option value="">-- PILIH AKUN --</option> ';
foreach ($accounts as $account) {
	$buff .= sprintf('<option value="%d" data-code="%s" data-text="%s">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName, $account->AccNo, $account->AccName);
}
?>
<div id="template" style="display: none; padding: 3px 3px; margin-bottom: 5px; border-bottom: solid #000000 1px;">
	<input type="hidden" id="Id" name="Id[]" value="" />

	<table cellpadding="0" cellspacing="0" class="smallTablePadding">
		<tr>
			<td class="right"><label id="lblDebit" for="Debit">Debit Acc :</label></td>
			<td><select id="Debit" name="Debit[]" style="width: 400px;">
				<?php print($buff); ?>
			</select></td>
			<td class="right"><label id="lblCredit" for="Credit">Credit Acc :</label></td>
			<td colspan="2"><select id="Credit" name="Credit[]" style="width: 400px;">
				<?php print($buff); ?>
			</select></td>
		</tr>
        <tr>
            <td class="right"><label id="lblProject" for="Project">Project :</label></td>
            <td><select id="Project" name="Project[]" style="width: 300px">
                    <option value="">-- PILIH PROYEK JIKA ADA --</option>
                    <?php
                    foreach ($projects as $project) {
                        printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                    }
                    ?>
                </select>
            </td>
			<td class="right"><label id="lblActivity" for="Activity">Activity :</label></td>
			<td colspan="2"><select id="Activity" name="Activity[]" style="width: 300px">
                    <option value="">-- PILIH ACTIVITY --</option>
                    <?php
                    foreach ($activitys as $activity) {
                        printf('<option value="%d">%s - %s</option>', $activity->Id, $activity->ActCode, $activity->ActName);
                    }
                    ?>
			</select></td>
		</tr>
        <tr>
            <td class="right"><label id="lblDepartment" for="Department">Dept :</label></td>
            <td><select id="Department" name="Department[]" style="width: 300px">
                    <option value="">-- PILIH DEPARTEMEN --</option>
                    <?php
                    foreach ($departments as $deparment) {
                        printf('<option value="%d">%s - %s</option>', $deparment->Id, $deparment->DeptCode, $deparment->DeptName);
                    }
                    ?>
                </select>
            </td>
            <td class="right"><label id="lblUnit" for="Unit">Unit :</label></td>
            <td colspan="2"><select id="Unit" name="Unit[]" style="width: 300px">
                    <option value="">-- PILIH UNIT --</option>
                    <?php
                    foreach ($units as $unit) {
                        printf('<option value="%d">%s - %s</option>', $unit->Id, $unit->UnitCode, $unit->UnitName);
                    }
                    ?>
                </select>
            </td>
        </tr>
		<tr>
			<td class="right"><label id="lblDebtor" for="Debtor">Debtor :</label></td>
			<td><select id="Debtor" name="Debtor[]" style="width: 300px">
				<option value="">-- PILIH DEBTOR --</option>
				<?php
				foreach ($debtors as $debtor) {
					printf('<option value="%d">[%s] %s - %s</option>', $debtor->Id, $debtor->EntityCd, $debtor->DebtorCd, $debtor->DebtorName);
				}
				?>
			</select></td>
			<td class="right"><label id="lblCreditor" for="Creditor">Creditor :</label></td>
			<td colspan="2"><select id="Creditor" name="Creditor[]" style="width: 300px">
				<option value="">-- PILIH KREDITOR --</option>
				<?php
				foreach ($creditors as $creditor) {
					printf('<option value="%d">[%s] %s - %s</option>', $creditor->Id, $creditor->EntityCd, $creditor->CreditorCd, $creditor->CreditorName);
				}
				?>
			</select></td>
		</tr>
		<tr>
			<td class="right"><label id="lblEmployee" for="Employee">Employee :</label></td>
			<td colspan="3">
				<select id="Employee" name="Employee[]" style="width: 300px">
					<option value="">-- PILIH KARYAWAN --</option>
					<?php
					foreach ($employees as $employee) {
						printf('<option value="%d">%s - %s</option>', $employee->Id, $employee->Nik, $employee->Nama);
					}
					?>
				</select>
			</td>
		</tr>
        <tr>
            <td class="right"><label id="lblNote" for="Note">Description :</label></td>
            <td><input type="text" id="Note" name="Note[]"  style="width: 400px"/></td>
            <td class="right"><label id="lblAmount" for="Amount">Amount :</label></td>
            <td><input id="Amount" class="right bold" name="Amount[]" value="0" /></td>
			<td class="right"><button type="button" id="btnDelete">Delete</button></td>
		</tr>
	</table>
</div>

</body>
</html>
