<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Stock Opname: <?php print($stockOpname->DocumentNo); ?></title>
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
	<legend><span class="bold">Stock Opname: <?php print($stockOpname->DocumentNo); ?></span></legend>

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
			<td class="bold right">Status :</td>
			<td><?php print($stockOpname->GetStatus()); ?></td>
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
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; min-width: 600px;">
			<tr class="bold center">
				<td class="bN bE bS bW" width="20px;">No.</td>
				<td class="bN bE bS">Nama Barang</td>
				<td colspan="2" class="bN bE bS">Jumlah SO</td>
				<td colspan="2" class="bN bE bS">Stock Computed</td>
				<td colspan="2" class="bN bE bS">Selisih</td>
				<td class="bN bE bS">Harga</td>
				<td class="bN bE bS">Total Biaya</td>
			</tr>
			<?php
			// Hwee karena idx item nya
			$counter = 0;
			foreach ($stockOpname->Details as $idx => $detail) {
				$className = $counter % 2 == 0 ? "oddRow" : "evenRow";
				$counter++;
				$qty = abs($detail->QtySo - $detail->QtyComputed);
				?>
				<tr class="<?php print($className); ?>">
					<td class="bW bE bS right"><?php print($counter); ?>.</td>
					<td class="bE bS"><?php printf("%s - %s", $detail->ItemCode, $detail->ItemName); ?></td>
					<td class="bS right"><?php print(number_format($detail->QtySo, 2)); ?></td>
					<td class="bE bS"><?php print($detail->UomCd); ?></td>
					<td class="bS right"><?php print(number_format($detail->QtyComputed, 2)); ?></td>
					<td class="bE bS"><?php print($detail->UomCd); ?></td>
					<td class="bS right"><?php print(number_format($detail->QtySo - $detail->QtyComputed, 2)); ?></td>
					<td class="bE bS"><?php print($detail->UomCd); ?></td>
					<td class="bE bS right"><?php print($detail->TotalCost == null ? "-" : number_format($detail->TotalCost / $qty)); ?></td>
					<td class="bE bS right"><?php print($detail->TotalCost == null ? "-" : number_format($detail->TotalCost)); ?></td>
				</tr>
				<?php } ?>
		</table><br />

		<div class="center">
			<a href="<?php print($helper->site_url("inventory.so")); ?>">Daftar Dokumen Stock Opname</a>
		</div>
	</div>

</fieldset>

</body>
</html>
