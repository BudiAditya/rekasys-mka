<!DOCTYPE HTML>
<html>
<?php
/** @var StockOpname $stockOpname */ /** @var Warehouse $warehouse */
?>
<head>
	<title>Rekasys - Entry Stock Opname Step 3</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

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
	<legend><span class="bold">Entry Stock Opname Step 3 - Konfirmasi</span></legend>

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
	<div id="container" style="min-width: 600px; display: inline-block; padding: 5px;">
		<div class="bold center subTitle">
			Daftar Barang Stock Opname<br />
		</div><br />
		<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0 auto; min-width: 600px;">
			<tr class="bold center">
				<td width="20px;">No.</td>
				<td>Nama Barang</td>
				<td colspan="2">Jumlah SO</td>
				<td colspan="2">Stock Computed</td>
				<td colspan="2">Selisih</td>
				<td>Harga Satuan</td>
			</tr>
			<?php
			// Hwee karena idx item nya
			$counter = 0;
			foreach ($stockOpname->Details as $idx => $detail) {
				$className = $counter % 2 == 0 ? "oddRow" : "evenRow";
				$counter++;
				?>
				<tr class="<?php print($className); ?>">
					<td class="right"><?php print($counter); ?>.</td>
					<td><?php printf("%s - %s", $detail->ItemCode, $detail->ItemName); ?></td>
					<td class="right" style="border-right: none;"><?php print(number_format($detail->QtySo, 2)); ?></td>
					<td><?php print($detail->UomCd); ?></td>
					<td class="right" style="border-right: none;"><?php print(number_format($detail->QtyComputed, 2)); ?></td>
					<td><?php print($detail->UomCd); ?></td>
					<td class="right" style="border-right: none;"><?php print(number_format($detail->QtySo - $detail->QtyComputed, 2)); ?></td>
					<td><?php print($detail->UomCd); ?></td>
					<td class="right"><?php print(number_format($detail->Price)); ?></td>
				</tr>
				<?php } ?>
		</table><br />

		<form action="<?php print($helper->site_url("inventory.so/add?step=confirm")); ?>" method="post" class="center">
			<input type="hidden" name="confirmed" value="1" />
			<button type="button" onclick="window.location='<?php print($helper->site_url("inventory.so/add?step=detail")); ?>';">&lt; Sebelumnya</button>
			&nbsp;&nbsp;&nbsp;
			<button type="submit">Simpan</button>
		</form>
	</div>

</fieldset>

</body>
</html>
