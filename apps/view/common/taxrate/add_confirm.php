<!DOCTYPE HTML>
<html>
<head>
	<title>Entry Skema Pajak Step 3</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
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
	<legend><span class="bold">Entry Skema Pajak Step 3 - Konfirmasi</span></legend>

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
			<td class="bold"><?php print($taxRate->InclExcl == 1 ? "Iya" : "Tidak"); ?></td>
		</tr>
	</table>

	<br />
	<div class="subTitle bold">Detail Skema Pajak</div>
	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bN bE bS bW">No.</td>
			<td class="bN bE bS">Kode</td>
			<td class="bN bE bS">Jenis Pajak</td>
			<td class="bN bE bS">Tarif (%)</td>
			<td class="bN bE bS">No. Akun</td>
			<td class="bN bE bS">Akun Balik</td>
			<td class="bN bE bS">Pengurang</td>
		</tr>
		<?php foreach ($taxRate->TaxRateDetails as $idx => $detail) { ?>
		<tr>
			<td class="bW bE bS right"><?php print($idx + 1); ?>.</td>
			<td class="bE bS"><?php print($detail->TaxCd); ?></td>
			<td class="bE bS"><?php print($detail->TaxType); ?></td>
			<td class="bE bS right"><?php print($detail->TaxTarif); ?>%</td>
			<td class="bE bS"><?php print($detail->AccNo); ?></td>
			<td class="bE bS"><?php print($detail->ReversalAccNo); ?></td>
			<td class="bE bS"><?php print($detail->Deductable == 1 ? "Iya" : "Tidak"); ?></td>
		</tr>
		<?php } ?>
		<tr class="center">
			<td colspan="7">
				<form action="<?php print($helper->site_url("common.taxrate/add_confirm")); ?>" method="post">
					<input type="hidden" name="confirmed" value="1" />
					<button type="button" onclick="window.location='<?php print($helper->site_url("common.taxrate/add_detail")); ?>';">&lt; Sebelumnya</button>
					&nbsp;&nbsp;&nbsp;
					<button type="submit">Simpan</button>
				</form>
			</td>
		</tr>
	</table>
</fieldset>

</body>
</html>
