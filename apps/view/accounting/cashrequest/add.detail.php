<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Entry Cash Request (NPKP) Step 2</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		optgroup {
			text-align: center;
		}
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var counter = 0, divDetails, template;
		var details = eval(<?php print(json_encode($details)); ?>);

		$(document).ready(function () {
			divDetails = $("#details");
			template = $("#template").removeAttr("id");

			if (details.length == 0) {
				AddTemplate();
			} else {
				for (var i = 0; i < details.length; i++) {
					AddTemplate(details[i]);
				}
			}
			MakeSureVisible();
		});

		function AddTemplate(data) {
			// Buat Template dan register event handlernya
			var cloned = template.clone();
			cloned.find("#btnDelete").click(function (e) {
				cloned.slideUp("fast", function () {
					cloned.parent().remove();
					MakeSureVisible();
				});
			});
			cloned.find("#btnAdd").click(function (e) {
				$(this).hide();
				AddTemplate();
			});

			// Untuk Set data dan id digabung disini
			FixDivId(cloned, data);

			// Everything is GREEN
			divDetails.append($("<li></li>").append(cloned));
			cloned.slideDown();

			return cloned;
		}

		function MakeSureVisible() {
			if (divDetails.find("li").length == 0) {
				AddTemplate();
			} else {
				divDetails.find("#btnAdd:last").show();
			}
		}

		function FixDivId(div, data) {
            var temp;
			var haveData;
			if (data == undefined) {
				haveData = false;
			} else {
				haveData = true;
				div.find("#btnAdd").hide();
			}

			div.find("#lblAccount").attr("for", "Account_" + counter);
			div.find("#Account").attr("id", "Account_" + counter).val(haveData ? data.AccountId : null);
			div.find("#lblNote").attr("for", "Note_" + counter);
			div.find("#Note").attr("id", "Note_" + counter).val(haveData ? data.Note : null);

            div.find("#lblAmount").attr("for", "Amount_" + counter);
			temp = div.find("#Amount").attr("id", "Amount_" + counter).val(haveData ? data.Amount : null);
            temp.autoNumeric();
            if (haveData) {
                temp.autoNumericSet(data.Amount);
            }

			counter++;
		}
	</script>
</head>

<body>
<?php /** @var $company Company */ /** @var $cashRequest CashRequest */ /** @var $category CashRequestCategory */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Entry Cash Request (NPKP) Step 2 - Data Detail</span></legend>

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td class="bold right">NPKP No. :</td>
			<td><?php print($cashRequest->DocumentNo); ?></td>
		</tr>
		<tr>
			<td class="bold right">NPKP Date :</td>
			<td><?php print($cashRequest->FormatDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Project :</td>
			<td><?php printf("%s - %s", $category->Code, $category->Name); ?></td>
		</tr>
		<tr>
			<td class="bold right">NPKP Purpose :</td>
			<td><?php print($cashRequest->Objective); ?></td>
		</tr>
		<tr>
			<td class="bold right">Description :</td>
			<td><?php print(str_replace("\n", "<br />", $cashRequest->Note)); ?></td>
		</tr>
		<tr>
			<td class="bold right">Request Date :</td>
			<td><?php print($cashRequest->FormatEtaDate()); ?></td>
		</tr>
	</table>

	<br />

	<form action="<?php print($helper->site_url("accounting.cashrequest/add?step=detail")); ?>" method="post">
		<input type="hidden" id="dummy" name="dummy" value="dummy" />

		<div id="container" style="min-width: 600px; display: inline-block; border: solid #000000 1px; padding: 5px;">
			<div class="bold center subTitle">
				Cash Request (NPKP) Detail
			</div><br />

			<ol id="details" style="padding-left: 25px;"></ol>

			<br />

			<div id="navigation" class="center">
				<button type="button" onclick="window.location='<?php print($helper->site_url("accounting.cashrequest/add?step=master")); ?>';">&lt; Previous</button>
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
			<td class="right"><label id="lblAccount" for="Account">Post Account :</label></td>
			<td colspan="2"><select id="Account" name="Account[]" style="width: 400px" required>
				<option value="">-- PILIH AKUN --</option>
				<?php
				foreach ($accounts as $account) {
					printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
				}
				?>
			</select></td>
		</tr>
		<tr>
			<td class="right"><label id="lblNote" for="Note">Cash Purpose :</label></td>
			<td colspan="2"><input type="text" id="Note" name="Note[]" style="width: 400px" required></td>
		</tr>
		<tr>
			<td class="right"><label id="lblAmount" for="Amount">Amount :</label></td>
			<td><input class="right bold" type="text" id="Amount" name="Amount[]" value="0" required/></td>
			<td class="right">
				<button type="button" id="btnDelete">Delete</button>
				&nbsp;&nbsp;
				<button type="button" id="btnAdd">Add Detail</button>
			</td>
		</tr>
	</table>
</div>

</body>
</html>
