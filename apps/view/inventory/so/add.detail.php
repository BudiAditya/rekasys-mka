<!DOCTYPE HTML>
<html>
<?php
/** @var StockOpname $stockOpname */ /** @var Item[] $items */ /** @var Warehouse $projects */
/** @var array $detailsJson */ /** @var array $autoCompleteJson */ /** @var array $details */
?>
<head>
	<title>Rekasys - Entry Stock Opname Step 2</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		.ui-autocomplete {
			max-height: 150px;
			overflow-y: auto;
			/* prevent horizontal scrollbar */
			overflow-x: hidden;
			/* add padding to account for vertical scrollbar */
			padding-right: 20px;
		}

		/**
		IE 6 doesn't support max-height
		we use height instead, but this forces the menu to always be this tall
		*/
		* html .ui-autocomplete {
			height: 150px;
		}

		.linkDelete {
			color: blue;
			cursor: pointer;
			text-decoration: underline;
		}
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var divDetails, txtItem, txtQty, lblUom, txtPrice, btnAdd, lblNote, template;
		var autoCompleteJson = eval(<?php print(json_encode($autoCompleteJson)); ?>);
		var detailsJson = eval(<?php print(json_encode($detailsJson)); ?>);
		var selectedItem = null;
		var soItems = new Array();

		$(document).ready(function () {
			divDetails = $("#details");
			txtItem = document.getElementById("txtItem");
			txtQty = document.getElementById("txtQty");
            $("#txtQty").autoNumeric();
			$("#txtPrice").autoNumeric();
			lblUom = $("#lblUom");
			txtPrice = document.getElementById("txtPrice");
			btnAdd = document.getElementById("btnAdd");
			lblNote = $("#Note");
			template = $("#template").removeAttr("id");

			// Disable enter on txt input barang dan qty karena didalam form.... grr....
			$("#txtItem").keydown(function (e) {
				if (e.keyCode == 13) {
					setTimeout(function() {
						txtQty.focus();
					}, 100);
				}
			});
			$("#txtQty").keydown(function (e) {
				if (e.keyCode == 13) {
					txtPrice.focus();
				}
			});
			$("#txtPrice").keydown(function (e) {
				if (e.keyCode == 13) {
					btnAdd.focus();
				}
			});

			$("#txtItem").autocomplete({
				minLength : 2, source : autoCompleteJson, search : function (event, ui) {
					selectedItem = null;
					lblNote.html("-- SILAHKAN PILIH BARANG DAHULU --");
				}, select : function (event, ui) {
					selectedItem = ui.item;
					lblUom.html(selectedItem.uom);
					txtQty.value = "";
					txtQty.focus();
					lblNote.html("BARANG YANG DIPILIH : " + selectedItem.label);
				}
			});

			$("#btnAdd").click(function (e) {
				btnAdd_Click(this, e);
			});

			$("#lblDelete").click(function (e) {
				var tr = $(this).parents("tr");
				var id = tr.find("#ItemId").val();
				var code = tr.find("#Code").val();
				var name = tr.find("#Name").val();
				var message = "Apakah anda mau menghapus data: " + code + " - " + name + " ?";

				if (confirm(message)) {
					var idx = soItems.indexOf(id);
					soItems.splice(idx, 1);
					tr.remove();
					ReIndex();
				}
			});

			// OK semua sudah beres ? Mari kita restore state-nya... (harus sesudah event registration)
			for (var i = 0; i < detailsJson.length; i++) {
				AddDetail(detailsJson[i].ItemId, detailsJson[i].ItemCode, detailsJson[i].ItemName, detailsJson[i].QtySo, detailsJson[i].UomCd, detailsJson[i].Price);
			}
			ReIndex();
		});

		function AddDetail(itemId, code, name, qty, uom, price) {
			soItems.push(itemId);

			var cloned = template.clone(true);
			cloned.find("#Item").val(itemId);
			cloned.find("#Code").val(code);
			cloned.find("#Name").val(name);
			cloned.find("#Qty").val(qty);
			cloned.find("#Uom").val(uom);
			cloned.find("#Price").val(price);

			cloned.find("#colCode").html(code);
			cloned.find("#colName").html(name);
			cloned.find("#colQty").html(qty);
			cloned.find("#colUom").html(uom);
			cloned.find("#colPrice").html(price);

			divDetails.append(cloned);
		}
		function btnAdd_Click(sender, e) {
			if (selectedItem == null) {
				alert("Maaf anda belum memilih barang yang akan di SO");
				txtItem.focus();
				return;
			}
			if (txtQty.value == "") {
				txtQty.focus();
				alert("Harap mengisi qty SO terlebih dahulu !");
				return;
			}
			var qty = parseFloat(txtQty.value.replace(/,/gi, ""));
			if (qty < 0) {
				txtQty.select();
				txtQty.focus();
				alert("Maaf qty SO tidak dapat < 0");
				return;
			}
			// Validasi agar tidak ada data yang sama di entry
			if (soItems.indexOf(selectedItem.id) != -1) {
				alert("Maaf barang yang mau anda proses SO sudah ada pada data diatas !");
				txtItem.select();
				txtItem.focus();
				return;
			}
			AddDetail(selectedItem.id, selectedItem.code, selectedItem.name, qty, selectedItem.uom, txtPrice.value);
			ReIndex();
			window.scrollTo(window.scrollX, window.scrollY + 30);

			txtItem.value = "";
			txtQty.value = "";
			txtPrice.value = "0";
			lblNote.html("-- SILAHKAN PILIH BARANG DAHULU --");
			txtItem.focus();
		}

		function ReIndex() {
			divDetails.find(".number").each(function (idx, ele) {
				$(ele).html(idx + 1);
			});
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
	<legend><span class="bold">Entry Stock Opname Step 2 - Data Detail</span></legend>

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td class="bold right">No. Dokumen:</td>
			<td><?php print($stockOpname->DocumentNo); ?></td>
		</tr>
		<tr>
			<td class="bold right">Tgl. Dokumen:</td>
			<td><?php print($stockOpname->FormatDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Gudang:</td>
			<td><?php printf("%s - %s", $warehouse->Code, $warehouse->Name); ?></php></td>
		</tr>
		<tr>
			<td class="bold right">Keterangan Stock Opname:</td>
			<td><?php print(str_replace("\n", "<br />", $stockOpname->Note)); ?></td>
		</tr>
	</table>

	<br />


	<div id="container" style="min-width: 600px; display: inline-block; border: solid #000000 1px; padding: 5px; margin: 0 auto;">

		<div class="bold center subTitle">
			Daftar Barang Stock Opname
		</div>
		<br />

		<div class="center" style="margin: 0 auto">
			<label for="txtItem">Kode / Nama Barang : </label>
			<input type="text" id="txtItem" size="60" />
			<label for="txtQty">Jumlah SO : </label>
			<input type="text" id="txtQty" class="right" size="8" />
			<label id="lblUom" class="left" style="display: inline-block; width: 30px;"></label>
			<label for="txtPrice">Harga Satuan : </label>
			<input type="text" id="txtPrice" class="right" size="12" value="0">
			<button type="button" id="btnAdd">Tambah</button>
			<br />
			<label id="Note">-- SILAHKAN PILIH BARANG DAHULU --</label><br />
			NOTE: Jika harga satuan adalah nol maka akan menggunakan harga pembelian terakhir (Jika terjadi kelebihan barang)
		</div>
		<br />

		<form action="<?php print($helper->site_url("inventory.so/add?step=detail")); ?>" method="post">
			<input type="hidden" id="dummy" name="dummy" value="dummy" />

			<table id="details" cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto">
				<tr class="center bold">
					<td>No.</td>
					<td>Kode</td>
					<td>Nama</td>
					<td colspan="2">Qty SO</td>
					<td>Harga</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<br />

			<div id="navigation" class="center">
				<button type="button" onclick="window.location='<?php print($helper->site_url("inventory.so/add?step=master")); ?>';">&lt; Sebelumnya</button>
				&nbsp;&nbsp;&nbsp;
				<button type="submit">Berikutnya &gt;</button>
			</div>
		</form>
		<br />
	</div>
</fieldset>

<table style="display: none;">
	<tr id="template">
		<td>
			<label class="number"></label>
			<input type="hidden" id="Id" name="Id[]" />
			<input type="hidden" id="Item" name="Item[]" />
			<input type="hidden" id="Code" name="Code[]" />
			<input type="hidden" id="Name" name="Name[]" />
			<input type="hidden" id="Qty" name="Qty[]" />
			<input type="hidden" id="Uom" name="Uom[]" />
			<input type="hidden" id="Price" name="Price[]" />
		</td>
		<td>
			<div id="colCode"></div>
		</td>
		<td>
			<div id="colName"></div>
		</td>
		<td>
			<div id="colQty" class="right"></div>
		</td>
		<td>
			<div id="colUom"></div>
		</td>
		<td>
			<div id="colPrice" class="right"></div>
		</td>
		<td><label id="lblDelete" class="linkDelete">Hapus</label></td>
	</tr>
</table>

</body>
</html>
