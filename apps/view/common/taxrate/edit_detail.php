<!DOCTYPE HTML>
<html>
<head>
	<title>Edit Skema Pajak Step 2</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var divDetails, template, counter = 0;
		var taxRateDetails = eval(<?php print(json_encode($details)) ?>);

		$(document).ready(function() {
			divDetails = $("#details");
			template = $("#template").removeAttr("id");

			// Rebuilt details
			if (taxRateDetails.length == 0) {
				AddTemplate();
			} else {
				for (var i = 0; i < taxRateDetails.length; i++) {
					AddTemplate(taxRateDetails[i]);
				}
			}
			MakeSureVisible();
		});

		function AddTemplate(data) {
			var cloned = template.clone();
			cloned.show();
			cloned.find("#btnDelete").click(function(e) {
				cloned.slideUp("fast", function () {
					cloned.parent().remove();
					MakeSureVisible();
				});
			});
			cloned.find("#btnAdd").click(function (e) {
				$(this).hide();
				AddTemplate();
			});
			FixDivId(cloned, data);

			divDetails.append($("<li></li>").append(cloned));
			cloned.slideDown();

			// Return template so we can edit data...
			return cloned;
		}

		function MakeSureVisible() {
			if (divDetails.find("li").length == 0) {
				AddTemplate();
			} else {
				divDetails.find("#btnAdd:last").show();
			}
		}

		function FixDivId(div, taxDataDetail) {
			var haveData;
			if (taxDataDetail == undefined) {
				haveData = false;
			} else {
				haveData = true;
				div.find("#btnAdd").hide();

				if (taxDataDetail.Id != "") {
					div.find("#Id").val(taxDataDetail.Id);
					div.find("#btnDelete").hide();
					div.find("#markDelete").show().val(taxDataDetail.Id);
					div.find("#lblMarkDelete").show();
					if (taxDataDetail.MarkedForDeletion) {
						div.find("#markDelete").attr("checked", "checked");
					}
				}
			}

			div.find("#lblTaxCd").attr("for", "TaxCd_" + counter);
			div.find("#TaxCd").attr("id", "TaxCd_" + counter).val(haveData ? taxDataDetail.TaxCd : null);
			div.find("#TaxType").attr("for", "TaxType_" + counter);
			div.find("#TaxType").attr("id", "TaxType_" + counter).val(haveData ? taxDataDetail.TaxType : null);
			div.find("#TaxTarif").attr("for", "TaxTarif_" + counter);
			div.find("#TaxTarif").attr("id", "TaxTarif_" + counter).val(haveData ? taxDataDetail.TaxTarif : null);
			div.find("#AccId").attr("for", "AccId_" + counter);
			div.find("#AccId").attr("id", "AccId_" + counter).val(haveData ? taxDataDetail.AccNoId : null);
			div.find("#ReversalAccId").attr("for", "ReversalAccId_" + counter);
			div.find("#ReversalAccId").attr("id", "ReversalAccId_" + counter).val(haveData ? taxDataDetail.ReversalAccNoId : null);
			if (haveData && taxDataDetail.Deductable == 1) {
				div.find("#Deductable").val("1");
			}

			counter++;
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
	<legend><span class="bold">Edit Skema Pajak Step 2 - Detail Skema Pajak</span></legend>

	<div class="subTitle bold">Header Skema Pajak</div>
	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td>Company :</td>
			<td class="bold"><?php printf('%s - %s', $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td>Kode:</td>
			<td class="bold"><?php print($taxRate->TaxSchCd); ?></td>
		</tr>
		<tr>
			<td>Skema Pajak:</td>
			<td class="bold"><?php print($taxRate->TaxSchDesc); ?></td>
		</tr>
		<tr>
			<td>Termasuk:</td>
			<td class="bold"><?php print($taxRate->InclExcl == "1" ? "Iya" : "Tidak"); ?></td>
		</tr>
	</table>

	<br />

	<form id="frmDetail" action="<?php print($helper->site_url("common.taxrate/edit_detail/" . $taxRate->Id)); ?>" method="post">

		<div id="container" style="min-width: 600px; display: inline-block; border: solid #000000 1px; padding: 5px;">
			<div class="subTitle bold center">Detail Skema Pajak</div>

			<ol id="details" style="padding-left: 25px;"></ol>

			<div id="navigation" class="center">
				<button type="button" onclick="window.location='<?php print($helper->site_url("common.taxrate/edit_header/" . $taxRate->Id)); ?>';">&lt; Sebelumnya</button>
				&nbsp;&nbsp;&nbsp;
				<button type="submit">Berikutnya &gt;</button>
			</div>
		</div>
	</form>

</fieldset>

<?php
$options = '<option value="">-</option>';
foreach ($accounts as $account) {
	$options .= sprintf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
}
?>
<div id="template" style="display: none; padding: 3px 3px; margin-bottom: 5px; border-bottom: solid #000000 1px;">
	<input type="hidden" id="Id" name="Id[]" value="" />

	<table cellpadding="0" cellspacing="0" class="smallTablePadding">
		<tr>
			<td><label id="lblTaxCd" for="TaxCd">Kode</label></td>
			<td><input type="text" id="TaxCd" name="TaxCd[]" maxlength="2" size="3"/></td>
			<td><label id="lblTaxType" for="TaxType">Jenis Pajak</label></td>
			<td><input type="text" id="TaxType" name="TaxType[]" maxlength="30" size="30"/></td>
			<td><label id="lblTaxTarif" for="TaxTarif">Tarif (%)</label></td>
			<td><input type="text" id="TaxTarif" name="TaxTarif[]" maxlength="5" size="5"/></td>
		</tr>
		<tr>
			<td><label id="lblAccId" for="AccId">No. Akun</label></td>
			<td colspan="5"><select id="AccId" name="AccId[]"><?php print($options); ?></select></td>
		</tr>
		<tr>
			<td><label id="lblReversalAccId" for="ReversalAccId">No. Akun Pembalik</label></td>
			<td colspan="5"><select id="ReversalAccId" name="ReversalAccId[]"><?php print($options); ?></select></td>
		</tr>
		<tr>
			<td><label id="lblDeducatble" for="Deductable">Pengurang DPP</label></td>
			<td><select id="Deductable" name="Deductable[]">
				<option value="0">Tidak</option>
				<option value="1">Iya</option>
			</select></td>
		</tr>
		<tr>
			<td colspan="6" class="right">
				<input type="checkbox" id="markDelete" name="markDelete[]" style="display: none;" />
				<label id="lblMarkDelete" for="markDelete" style="display: none;">Hapus Detail</label>
				<button type="button" id="btnDelete">Delete</button>
				&nbsp;&nbsp;
				<button type="button" id="btnAdd">Tambah</button>
			</td>
		</tr>
	</table>
</div>

</body>
</html>
