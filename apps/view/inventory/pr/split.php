<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Split Purchase Request</title>
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
	<legend><span class="bold"><?php printf("Split Dokumen PR: %s (Status: %s)", $pr->DocumentNo, $pr->GetStatus()); ?></span></legend>

	<div class="center subTitle">
		Modul ini akan memecah dokumen PR menjadi 2 dokumen PR agar dokumen dengan barang yang urgent dapat segera di proses
	</div><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td colspan="2" class="bold center">Dokumen yang akan di split</td>
		</tr>
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td class="bold right">No. Dokumen:</td>
			<td><?php print($pr->DocumentNo); ?></td>
		</tr>
		<tr>
			<td class="bold right">Tgl. Dokumen:</td>
			<td><?php print($pr->FormatDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Status:</td>
			<td><?php print($pr->GetStatus()); ?></php></td>
		</tr>
		<tr>
			<td class="bold right">Keterangan MR:</td>
			<td><?php print(str_replace("\n", "<br />", $pr->Note)); ?></td>
		</tr>
	</table>

	<br />
	<form action="<?php print($helper->site_url("inventory.pr/split/" . $pr->Id)); ?>" method="POST">
		<input type="hidden" id="dummy" name="dummy" value="dummy" />

		<div id="container" style="min-width: 600px; display: inline-block; padding: 5px;">
			<div class="bold center subTitle">
				Pilih barang-barang yang akan di split<br />
				Berdasarkan MR: <?php print(count($pr->MrIds) > 0 ? implode(", ", $pr->MrCodes) : "[Tanpa MR]"); ?>
			</div>
			<br />

			<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; min-width: 600px;">
				<tr class="bold center">
					<td class="bN bE bS bW" width="20px;">No.</td>
					<td class="bN bE bS">Nama Barang</td>
					<td colspan="2" class="bN bE bS">Jumlah</td>
					<td class="bN bE bS" width="80px;">Tanggal</td>
				</tr>
				<?php
				foreach ($pr->Details as $idx => $detail) {
					// Index dimulai dari 0
					$className = $idx % 2 == 0 ? "oddRow" : "evenRow";
					?>
					<tr class="<?php print($className); ?>">
						<td class="bW bE right"><?php print($idx + 1); ?>.</td>
						<td class="">
							<input type="checkbox" id="id_<?php print($idx); ?>" name="id[]" value="<?php print($detail->Id); ?>" />
							<label for="id_<?php print($idx); ?>"><?php printf("%s - %s", $detail->ItemCode, $detail->ItemName); ?></label>
						</td>
						<td class="right" width="80px"><?php print(number_format($detail->Qty, 2)); ?></td>
						<td class="" width="60px"><?php print($detail->UomCd); ?></td>
						<td class="bE">&nbsp;</td>
					</tr>
					<tr class="<?php print($className); ?>">
						<td class="bE bW">&nbsp;</td>
						<td colspan="4" class="bE bS"><?php print(str_replace("\n", "<br />", $detail->ItemDescription)); ?></td>
					</tr>
					<?php
					$suffix = $detail->SelectedSupplier == 1 ? " bold" : "";
					$supplierName = $detail->SupplierId1 == null ? "-" : $suppliers[$detail->SupplierId1]->CreditorName;
					?>
					<tr class="<?php print($className . $suffix); ?>">
						<td class="bE bW">&nbsp;</td>
						<td class="">Supplier 1: <?php print($supplierName); ?></td>
						<td colspan="2" class="right">Hrg Satuan Rp. <?php print(number_format($detail->Price1)); ?></td>
						<td class="bE"><?php print($detail->FormatDate1()); ?></td>
					</tr>
					<?php
					$suffix = $detail->SelectedSupplier == 2 ? " bold" : "";
					$supplierName = $detail->SupplierId2 == null ? "-" : $suppliers[$detail->SupplierId2]->CreditorName;
					?>
					<tr class="<?php print($className . $suffix); ?>">
						<td class="bE bW">&nbsp;</td>
						<td class="">Supplier 2: <?php print($supplierName); ?></td>
						<td colspan="2" class="right">Hrg Satuan Rp. <?php print(number_format($detail->Price2)); ?></td>
						<td class="bE"><?php print($detail->FormatDate2()); ?></td>
					</tr>
					<?php
					$suffix = $detail->SelectedSupplier == 3 ? " bold" : "";
					$supplierName = $detail->SupplierId3 == null ? "-" : $suppliers[$detail->SupplierId3]->CreditorName;
					?>
					<tr class="<?php print($className . $suffix); ?>">
						<td class="bE bS bW">&nbsp;</td>
						<td class="bS">Supplier 3: <?php print($supplierName); ?></td>
						<td colspan="2" class="bS right">Hrg Satuan Rp. <?php print(number_format($detail->Price3)); ?></td>
						<td class="bE bS"><?php print($detail->FormatDate3()); ?></td>
					</tr>
					<?php } ?>
			</table>
			<br />

			<div class="center">
				<button type="submit">Submit</button>
				&nbsp;&nbsp;&nbsp;
				<a href="<?php print($helper->site_url("inventory.pr")); ?>">Daftar Dokumen PR</a>
			</div>
		</div>
	</form>


</fieldset>

</body>
</html>
