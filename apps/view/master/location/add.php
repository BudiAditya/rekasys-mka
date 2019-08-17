<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Tambah Data Informasi Lokasi</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["LocationCd", "LocationName"];
			BatchFocusRegister(elements);
		});
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
	<legend><b>Tambah Data Lokasi</b></legend>
	<form id="frm" action="<?php print($helper->site_url("master.location/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Kode</td>
				<td><input type="text" name="LocationCd" size="4" maxlength="3" class="text2" id="LocationCd" value="<?php print($location->LocationCd); ?>" /></td>
			</tr>
			<tr>
				<td>Nama</td>
				<td><input type="text" name="LocationName" size="50" maxlength="150" class="text2" id="LocationName" value="<?php print($location->LocationName); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("master.location")); ?>" class="button">Daftar Lokasi</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
