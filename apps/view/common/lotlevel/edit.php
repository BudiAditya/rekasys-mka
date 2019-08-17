<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Edit Data Lantai</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["LevelCd", "LevelName", "LevelSeq"];
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
	<legend class="bold">Ubah Data Lantai</legend>
	<form id="frm" action="<?php print($helper->site_url("common.lotlevel/edit/" . $lotLevel->Id)); ?>" method="post">
		<input type="hidden" id="LevelId" name="LevelId" value="<?php print($lotLevel->Id); ?>"/>
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Company</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td><label for="LevelCd">Kode</label></td>
				<td><input type="text" name="LevelCd" id="LevelCd" maxlength="5" size="5" value="<?php print($lotLevel->LevelCd); ?>" /></td>
			</tr>
			<tr>
				<td><label for="LevelName">Nama Lantai</label></td>
				<td><input type="text" name="LevelName" id="LevelName" maxlength="50" size="50" value="<?php print($lotLevel->LevelName); ?>" /></td>
			</tr>
			<tr>
				<td><label for="LevelSeq">Urutan</label></td>
				<td><input type="text" name="LevelSeq" id="LevelSeq" maxlength="5" size="5" value="<?php print($lotLevel->LevelSeq); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.lotlevel")); ?>" class="button">Daftar Lantai</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
