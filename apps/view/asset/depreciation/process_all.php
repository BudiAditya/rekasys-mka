<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Depreciation Process</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
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
	<legend><span class="bold">Depreciation Process</span></legend>

	<div>
		Modul ini akan secara otomatis menghitung penyusutan asset-asset perusahaan.
        <br />
        Anda cukup memilih periode yang akan dilakukan proses penyusutan.
		<br />
        <br />
		<form action="<?php print($helper->site_url("asset.depreciation/process_all")); ?>" method="post">
			Periode :
			<select id="month" name="month">
				<option value="1">Januari</option>
				<option value="2">Februari</option>
				<option value="3">Maret</option>
				<option value="4">April</option>
				<option value="5">Mei</option>
				<option value="6">Juni</option>
				<option value="7">Juli</option>
				<option value="8">Agustus</option>
				<option value="9">September</option>
				<option value="10">Oktober</option>
				<option value="11">November</option>
				<option value="12">Desember</option>
			</select>
			<label for="year"> tahun: </label>
			<select id="year" name="year">
				<?php
				for ($i = (int)date("Y"); $i >= $startYear; $i--) {
					printf('<option value="%d">%d</option>', $i, $i);
				}
				?>
			</select>
			<button type="submit">Proses</button>
		</form>
	</div>


</fieldset>

</body>
</html>
