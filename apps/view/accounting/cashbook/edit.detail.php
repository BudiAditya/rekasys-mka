<!DOCTYPE HTML>
<html>
<?php
/** @var $lockDocId null|int */ /** @var $title string */ /** @var $controller string */ /** @var $company Company */ /** @var $voucher Voucher */
/** @var $docType DocType */ /** @var $departments Department[] */ /** @var $divisions array */ /** @var $debtors Debtor[] */ /** @var $creditors Creditor[] */ /** @var $employees Employee[] */ /** @var $projects Project[] */
/** @var $transTypes TrxType[] */ /** @var $banks Bank[] */ /** @var $details array */ /** @var $jsTransTypes array */
?>
<head>
	<title>Rekasys - Edit Voucher <?php print($title); ?> Step 2</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		optgroup {
			text-align: center;
		}
		.colDebit, .colCredit { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colDesc { display: inline-block; width: 450px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.colSbu { display: inline-block; width: 60px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colCode { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; }
		.colNama { display: inline-block; width: 330px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; white-space: nowrap; }
		.colNote { display: inline-block; width: 150px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.blue { color: blue; }
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/select2.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/accounting.cashbook.CbDetail.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var divDetails, template;
		var details = eval(<?php print(json_encode($details)); ?>);
		var transTypes = eval(<?php print(json_encode($jsTransTypes)); ?>);

		$(document).ready(function () {
			divDetails = $("#details");
			template = $("#template").removeAttr("id");
			$("#btnAdd").click(function (e) { AddTemplate(); });

			if (details.length == 0) {
				AddTemplate();
			} else {
				for (var i = 0; i < details.length; i++) {
					AddTemplate(details[i]);
				}
			}
		});

		function AddTemplate(data) {
			var cbDetail = new CbDetail(template.clone(), data);
			var li = $("<li></li>").append(cbDetail.DivContainer);
			divDetails.append(li);

			if (data == undefined) {
				cbDetail.DivContainer.hide().slideDown();
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
	<legend><span class="bold">Edit Voucher <?php print($title); ?> Step 2 - Data Detail</span></legend>

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
            <td class="bold right">Document No. :</td>
            <td><?php print($voucher->DocumentNo); ?></td>
        </tr>
        <tr>
            <td class="bold right">Date :</td>
            <td><?php print($voucher->FormatDate()); ?></td>
        </tr>
        <tr>
            <td class="bold right">Notes :</td>
            <td><?php print(str_replace("\n", "<br />", $voucher->Note)); ?></td>
        </tr>
    </table>

	<br />

	<form action="<?php print($helper->site_url("$controller/edit/" . $voucher->Id . "?which=detail")); ?>" method="post">
		<input type="hidden" id="dummy" name="dummy" value="dummy" />

		<div id="container" style="min-width: 600px; display: inline-block; border: solid #000000 1px; padding: 5px;">
			<div class="bold center subTitle">
				Detail Voucher <?php print($title); ?> Masuk
			</div><br />

			<ol id="details" style="padding-left: 25px;"></ol>

			<br />

			<div id="navigation" class="center">
				<button type="button" onclick="window.location='<?php print($helper->site_url("$controller/edit/" . $voucher->Id . "?which=master")); ?>';">&lt; Previous</button>
				&nbsp;&nbsp;&nbsp;
				<button type="button" id="btnAdd">Add Detail</button>
				&nbsp;&nbsp;&nbsp;
				<button type="submit">Next &gt;</button>
			</div>
		</div>
	</form>

</fieldset>

<div id="template" style="display: none; padding: 3px 3px; margin-bottom: 5px; border-bottom: solid #000000 1px;">
	<input type="hidden" id="Id" name="Id[]" value="" />

    <table cellpadding="1" cellspacing="1" class="smallTablePadding">
        <tr>
            <td class="right"><label id="lblTrxType" for="TrxType">Transaction :</label></td>
            <td colspan="3"><select id="TrxType" name="TrxType[]" style="width: 700px;">
                    <option value="">-- Transaction Type --</option>
                    <?php
                    foreach ($transTypes as $transType) {
                        printf('<option value="%d">%s</option>', $transType->Id, $transType->GetName());
                    }
                    ?>
                </select></td>
            <td class="right"><label id="lblBank" for="Bank" style="display: none;">Cash/Bank :</label></td>
            <td><select id="Bank" name="Bank[]" style="display: none;">
                    <option value="">-- Cash/Bank --</option>
                    <?php
                    foreach ($banks as $bank) {
                        printf('<option value="%d">%s</option>', $bank->Id, $bank->Name);
                    }
                    ?>
                </select></td>
        </tr>
        <tr>
            <td class="right"><label id="lblProject" for="Project">Project :</label></td>
            <td><select id="Project" name="Project[]" style="width:300px">
                    <option value="">-- PILIH PROYEK JIKA ADA --</option>
                    <?php
                    foreach ($projects as $project) {
                        printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                    }
                    ?>
                </select>
            </td>
            <td class="right"><label id="lblActivity" for="Activity">Activity :</label></td>
            <td><select id="Activity" name="Activity[]" style="width:300px">
                    <option value="">-- PILIH AKTIFITAS --</option>
                    <?php
                    foreach ($activitys as $activity) {
                        printf('<option value="%d">%s - %s</option>', $activity->Id, $activity->ActCode, $activity->ActName);
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right"><label id="lblDepartment" for="Department">Department :</label></td>
            <td><select id="Department" name="Department[]" style="width:300px">
                    <option value="">-- PILIH DEPARTEMEN --</option>
                    <?php
                    foreach ($departments as $deparment) {
                        printf('<option value="%d">%s - %s</option>', $deparment->Id, $deparment->DeptCode, $deparment->DeptName);
                    }
                    ?>
                </select>
            </td>
            <td class="right"><label id="lblUnit" for="Unit">Unit :</label></td>
            <td><select id="Unit" name="Unit[]" style="width:300px">
                    <option value="0">-- PILIH UNIT --</option>
                    <?php
                    foreach ($units as $unit) {
                        printf('<option value="%d">%s - %s</option>', $unit->Id, $unit->UnitCode, $unit->UnitName);
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php if ($voucher->DocumentTypeId == 3) { ?>
            <tr>
                <td class="right"><label id="lblDebtor" for="Debtor">Debtor :</label></td>
                <td><select id="Debtor" name="Debtor[]" style="width: 300px;">
                        <option value="">-- PILIH DEBTOR --</option>
                        <?php
                        foreach ($debtors as $debtor) {
                            printf('<option value="%d" data-sbu="%s" data-code="%s" data-nama="%s" data-note="%s">[%s] %s - %s (%s)</option>', $debtor->Id, $debtor->EntityCd, $debtor->DebtorCd, $debtor->DebtorName, $debtor->CoreBusiness, $debtor->EntityCd, $debtor->DebtorCd, $debtor->DebtorName, $debtor->CoreBusiness);
                        }
                        ?>
                    </select>
                    <input type="hidden" name="Creditor[]" value="" />
                </td>
                <td class="right"><label id="lblEmployee" for="Employee">Employee :</label></td>
                <td>
                    <select id="Employee" name="Employee[]" style="width: 300px;">
                        <option value="">-- PILIH KARYAWAN --</option>
                        <?php
                        foreach ($employees as $employee) {
                            printf('<option value="%d" data-nik="%s" data-nama="%s">%s - %s</option>', $employee->Id, $employee->Nik, $employee->Name, $employee->Nik, $employee->Name);
                        }
                        ?>
                    </select>
                </td>
            </tr>
        <?php } else if ($voucher->DocumentTypeId == 2) { ?>
            <tr>
                <td class="right"><label id="lblCreditor" for="Creditor">Creditor :</label></td>
                <td><select id="Creditor" name="Creditor[]" style="width: 300px;">
                        <option value="">-- PILIH KREDITOR --</option>
                        <?php
                        foreach ($creditors as $creditor) {
                            printf('<option value="%d" data-sbu="%s" data-code="%s" data-nama="%s">[%s] %s - %s</option>', $creditor->Id, $creditor->EntityCd, $creditor->CreditorCd, $creditor->CreditorName, $creditor->EntityCd, $creditor->CreditorCd, $creditor->CreditorName);
                        }
                        ?>
                    </select>
                    <input type="hidden" name="Debtor[]" value="" />
                </td>
                <td class="right"><label id="lblEmployee" for="Employee">Employee :</label></td>
                <td>
                    <select id="Employee" name="Employee[]" style="width: 300px;">
                        <option value="">-- PILIH KARYAWAN --</option>
                        <?php
                        foreach ($employees as $employee) {
                            printf('<option value="%d" data-nik="%s" data-nama="%s">%s - %s</option>', $employee->Id, $employee->Nik, $employee->Nama, $employee->Nik, $employee->Nama);
                        }
                        ?>
                    </select>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan="4">
                    -- INVALID Document Type --
                    <input type="hidden" name="Debtor[]" value="" />
                    <input type="hidden" name="Creditor[]" value="" />
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td class="right"><label id="lblNote" for="Note">Description :</label></td>
            <td><input type="text" id="Note" name="Note[]" style="width: 300px" /></td>
            <td class="right"><label id="lblAmount" for="Amount">Amount :</label></td>
            <td><input class="right bold" id="Amount" name="Amount[]" value="0" style="width: 100px;"/></td>
            <td colspan="2" class="right">
                <input type="checkbox" id="markDelete" name="markDelete[]" style="display: none;" />
                <label id="lblMarkDelete" for="markDelete" style="display: none;">Delete Detail</label>
                <button type="button" id="btnDelete">Delete</button>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
