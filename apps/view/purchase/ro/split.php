<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Split Purchase Order: <?php print($po->DocumentNo); ?></title>
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
	<legend><span class="bold">Split Purchase Order: <?php print($po->DocumentNo); ?></span></legend>

	<div class="center subTitle">
		Modul ini akan memecah dokumen PO menjadi 2 dokumen PO dengan supplier yang berbeda. PO yang baru akan otomatis link ke PO utama<br />
		Untuk perubahan harga harap menggunakan modul edit PO
	</div><br />

	<form action="<?php print($helper->site_url("purchase.po/split/" . $po->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="bold right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td class="bold right">Supplier Lama :</td>
				<td><?php printf("%s - %s", $supplier->CreditorCd, $supplier->CreditorName); ?></td>
			</tr>
			<tr>
				<td class="bold right"><label for="SupplierId">Supplier Baru:</label></td>
				<td><select id="SupplierId" name="SupplierId">
					<option value="">-- PILIH SUPPLIER --</option>
					<?php
					foreach ($suppliers as $supplier) {
						if ($supplier->Id == $po->SupplierId) {
							continue;	// Bypass supplier yang sama agar tidak terjadi keanehan split dengan supplier sama
						}
						if ($supplier->Id == $newSupplierId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
						} else {
							printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="bold right">No. Dokumen:</td>
				<td><?php print($po->DocumentNo); ?></td>
			</tr>
			<tr>
				<td class="bold right">Tgl. Dokumen:</td>
				<td>
					<?php print($po->FormatDate()); ?>
					&nbsp;&nbsp;&nbsp;
					Prakiraan tanggal Pengiriman:
					<?php print($po->FormatExpectedDate()); ?>
				</td>
			</tr>
			<tr>
				<td class="bold right">Status :</td>
				<td><?php print($po->GetStatus()); ?></td>
			</tr>
			<tr>
				<td class="bold right">PPN:</td>
				<td>
					<?php print($po->IsVat ? 'PO ini akan dikenakan PPN 10%' : 'PO ini <span class="bold">TIDAK</span> akan dikenakan PPN 10%'); ?>
					<?php if ($po->IsVat) { ?>
					<br />
					<?php print($po->IsIncludeVat ? 'Harga yang tertera sudah termasuk PPN 10%' : 'Harga yang tertera <span class="bold">BELUM</span> sudah termasuk PPN 10%'); ?>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td class="bold right">Keterangan PO:</td>
				<td><?php print(str_replace("\n", "<br />", $po->Note)); ?></td>
			</tr>
		</table>

		<br />
		<div id="container" style="min-width: 600px; display: inline-block; padding: 5px;">
			<div class="bold center subTitle">
				Daftar Barang Purchase Order<br />
				Berdasarkan PR: <?php print(count($po->PrIds) > 0 ? implode(", ", $po->PrCodes) : "[Tanpa PR]"); ?>
			</div><br />

			<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; min-width: 600px;">
				<tr class="bold center">
					<td class="bN bE bS bW" width="20px;">No.</td>
					<td class="bN bE bS">Nama Barang</td>
					<td colspan="2" class="bN bE bS">Jumlah</td>
					<td class="bN bE bS" width="120px;">Harga</td>
				</tr>
				<?php
				foreach ($po->Details as $idx => $detail) {
					// Index dimulai dari 0
					$className = $idx % 2 == 0 ? "oddRow" : "evenRow";
					?>
					<tr class="<?php print($className); ?>">
						<td class="bW bE right"><?php print($idx + 1); ?>.</td>
						<td>
							<input type="checkbox" id="id_<?php print($idx); ?>" name="id[]" value="<?php print($detail->Id); ?>" />
							<label for="id_<?php print($idx); ?>"><?php printf("%s - %s", $detail->ItemCode, $detail->ItemName); ?></label>
						</td>
						<td class="right" width="80px"><?php print(number_format($detail->Qty, 2)); ?></td>
						<td width="60px"><?php print($detail->UomCd); ?></td>
						<td class="bE right">Rp. <?php print(number_format($detail->Price)); ?></td>
					</tr>
					<tr class="<?php print($className); ?>">
						<td class="bE bS bW">&nbsp;</td>
						<td colspan="4" class="bE bS"><?php print(str_replace("\n", "<br />", $detail->ItemDescription)); ?></td>
					</tr>
					<?php } ?>
			</table><br />

			<div class="center">
				<button type="submit">Submit</button>
				&nbsp;&nbsp;&nbsp;
				<a href="<?php print($helper->site_url("purchase.po")); ?>">Daftar Dokumen PO</a>
			</div>
		</div>
	</form>

</fieldset>

</body>
</html>
