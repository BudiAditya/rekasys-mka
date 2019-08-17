<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Entry Adjustment Stock Step 2</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var counter = 0, divDetails, template;
		var details = eval(<?php print(json_encode($details)); ?>);
		var itemCodes = eval(<?php print(json_encode($itemCodes)); ?>);
		var items = eval(<?php print(json_encode($items)); ?>);

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
			var ddlItems = null;

			ddlItems = cloned.find("#InvId").change(function(e) {
				var data = itemCodes[this.value];
				if (data == undefined) {
					return;
				}
				var tokens = data.split("|");
				cloned.find("#Codes").val(data);
				cloned.find("#lblUnit").text(tokens[2]);

			}).change();
			cloned.find("#Category").change(function(e) {
				if (this.value == "") {
					ddlItems.html('<option value="">-- MOHON PILIH KATEGORI DAHULU --</option>');
				} else {
					ddlItems.html(items[this.value]);
				}
			});
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

			// Kalau ada data yang dikirim maka kita set juga datanya
			if (data != undefined) {
				cloned.find("#btnAdd").hide();
				SetDetailData(cloned, data);
			}
			// Pembuatan DatePicker harus sesudah perubahan ID element karena ada proses yang binding ke ID...
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

		function SetDetailData(div, data) {
			// Untuk ComboBox event nya tidak otomatis ter-invoke jadi manual invoke
			var temp = itemCodes[data.ItemId];
			if (temp != undefined) {
				var tokens = temp.split("|");		// Untuk dapat category Idnya kita harus refer ke barangnya
				div.find("#Category").val(tokens[3]).change();
			}
			div.find("#InvId").val(data.ItemId).change();
			div.find("#Qty").val(data.Qty);
			div.find("#Desc").val(data.Note);
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

			div.find("#lblInvId").attr("for", "InvId_" + counter);
			div.find("#InvId").attr("id", "InvId_" + counter);

            div.find("#lblQty").attr("for", "Qty_" + counter);
            temp = div.find("#Qty").attr("id", "Qty_" + counter);
            temp.autoNumeric();
            if (haveData) {
                temp.autoNumericSet(data.Qty);
            }

			div.find("#lblDesc").attr("for", "Desc_" + counter);
			div.find("#Desc").attr("id", "Desc_" + counter);
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
	<legend><span class="bold">Entry Adjustment Stock Step 2 - Data Detail</span></legend>

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td class="bold right">No. Dokumen:</td>
			<td><?php print($adjustment->DocumentNo); ?></td>
		</tr>
		<tr>
			<td class="bold right">Jenis Dokumen</td>
			<td><?php print($adjustment->GetDocumentType()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Tgl. Dokumen:</td>
			<td><?php print($adjustment->FormatDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Gudang:</td>
			<td><?php printf("%s - %s", $warehouse->Code, $warehouse->Name); ?></php></td>
		</tr>
		<tr>
			<td class="bold right">Keterangan Adjustment Stock:</td>
			<td><?php print(str_replace("\n", "<br />", $adjustment->Note)); ?></td>
		</tr>
	</table>

	<br />

	<form action="<?php print($helper->site_url("inventory.adjustment/add?step=detail")); ?>" method="post">
		<input type="hidden" id="dummy" name="dummy" value="dummy" />

		<div id="container" style="min-width: 600px; display: inline-block; border: solid #000000 1px; padding: 5px;">
			<div class="bold center subTitle">
				Daftar Barang Adjustment Stock
			</div><br />

			<ol id="details" style="padding-left: 25px;"></ol>

			<br />

			<div id="navigation" class="center">
				<button type="button" onclick="window.location='<?php print($helper->site_url("inventory.adjustment/add?step=master")); ?>';">&lt; Sebelumnya</button>
				&nbsp;&nbsp;&nbsp;
				<button type="submit">Berikutnya &gt;</button>
			</div>
		</div>
	</form>

</fieldset>

<div id="template" style="display: none; padding: 3px 3px; margin-bottom: 5px; border-bottom: solid #000000 1px;">
	<input type="hidden" id="Codes" name="Codes[]" />
	<input type="hidden" id="Id" name="Id[]" />

	<table cellpadding="0" cellspacing="0" class="smallTablePadding">
		<tr>
			<td class="right"><label id="lblCategory" for="Category">Kategori Barang :</label></td>
			<td colspan="3"><select id="Category" name="Category[]">
				<option value="">-- PILIH KATEGORI --</option>
				<?php
				foreach ($itemCategories as $category) {
					printf('<option value="%d">%s - %s</option>', $category->Id, $category->Code, $category->Description);
				}
				?>
			</select></td>
		</tr>
		<tr>
			<td class="right"><label id="lblInvId" for="InvId">Nama Barang:</label></td>
			<td colspan="3"><select id="InvId" name="InvId[]">
				<option value="">-- MOHON PILIH KATEGORI DAHULU --</option>
			</select></td>
		</tr>
		<tr>
			<td class="right"><label id="lblQty" for="Qty">Jumlah:</label></td>
			<td>
				<input type="text" id="Qty" name="Qty[]" size="3" />
				<label id="lblUnit" style="display: inline-block; width: 80px;"></label>
			</td>
		</tr>
		<tr>
			<td class="right"><label id="lblDesc" for="Desc">Item Description:</label></td>
			<td colspan="3"><textarea rows="3" cols="60" id="Desc" name="Note[]"></textarea></td>
		</tr>
		<tr>
			<td colspan="4" class="right">
				<button type="button" id="btnDelete">Delete</button>
				&nbsp;&nbsp;
				<button type="button" id="btnAdd">Tambah</button>
			</td>
		</tr>
	</table>
</div>

</body>
</html>
