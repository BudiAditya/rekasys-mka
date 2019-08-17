<!DOCTYPE HTML>
<html>
<?php /** @var $year int */ /** @var $month int */ ?>
<head>
	<title>Rekasys - Set Periode Accounting</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>

<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br/>

<fieldset>
	<legend><span class="bold">Set Periode Akuntansi</span></legend>

	<form action="<?php print($helper->site_url("main/set_periode")); ?>" method="post">
		<div class="center">
			<label for="ddlMonth">Periode : </label>
			<select id="ddlMonth" name="month">
				<option value="1" <?php print($month == 1 ? 'selected="selected"' : ''); ?>>Januari</option>
				<option value="2" <?php print($month == 2 ? 'selected="selected"' : ''); ?>>Februari</option>
				<option value="3" <?php print($month == 3 ? 'selected="selected"' : ''); ?>>Maret</option>
				<option value="4" <?php print($month == 4 ? 'selected="selected"' : ''); ?>>April</option>
				<option value="5" <?php print($month == 5 ? 'selected="selected"' : ''); ?>>Mei</option>
				<option value="6" <?php print($month == 6 ? 'selected="selected"' : ''); ?>>Juni</option>
				<option value="7" <?php print($month == 7 ? 'selected="selected"' : ''); ?>>Juli</option>
				<option value="8" <?php print($month == 8 ? 'selected="selected"' : ''); ?>>Agustus</option>
				<option value="9" <?php print($month == 9 ? 'selected="selected"' : ''); ?>>September</option>
				<option value="10" <?php print($month == 10 ? 'selected="selected"' : ''); ?>>Oktober</option>
				<option value="11" <?php print($month == 11 ? 'selected="selected"' : ''); ?>>November</option>
				<option value="12" <?php print($month == 12 ? 'selected="selected"' : ''); ?>>Desember</option>
			</select>
			<label for="ddlYear">Tahun : </label>
			<select id="ddlYear" name="year">
				<?php
				for ($i = date("Y"); $i >= 2012; $i--) {
					if ($i == $year) {
						printf('<option value="%d" selected="selected">%d</option>', $i, $i);
					} else {
						printf('<option value="%d">%d</option>', $i, $i);
					}
				}
				?>
			</select>

			<button type="submit">Set Periode</button>
		</div>
	</form>
</fieldset>

</body>
</html>
