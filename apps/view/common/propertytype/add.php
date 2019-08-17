<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Tambah Data Jenis Property</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["PropertyCd", "PropertyName"];
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

<br/>
<fieldset>
	<legend class="bold">Tambah Data Property</legend>
	<form id="frm" action="<?php print($helper->site_url("common.propertytype/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Company</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td><label for="PropertyCd">Kode</label></td>
				<td><input type="text" name="PropertyCd" id="PropertyCd" maxlength="5" size="5" value="<?php print($propertyType->PropertyCd); ?>" /></td>
			</tr>
			<tr>
				<td><label for="PropertyName">Nama Property</label></td>
				<td><input type="text" name="PropertyName" id="PropertyName" maxlength="50" size="50" value="<?php print($propertyType->PropertyName); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.propertytype")); ?>" class="button">Daftar Jenis Property</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
