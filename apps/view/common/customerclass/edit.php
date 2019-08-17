<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Edit Data Customer Class</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["ClassCd", "ClassDesc"];
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
	<legend><b>Edit Data Customer Class</b></legend>
	<form id="frm" action="<?php print($helper->site_url("common.customerclass/edit")); ?>" method="post">
		<input type="hidden" name="Id" id="Id" value="<?php print($customerclass->Id);?>"/>
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Kode</td>
				<td><input type="text" name="ClassCd" size="5" maxlength="5" class="text2" id="ClassCd" value="<?php print($customerclass->ClassCd); ?>" /></td>
			</tr>
			<tr>
				<td>Nama</td>
				<td><input type="text" name="ClassDesc" size="50" maxlength="150" class="text2" id="ClassDesc" value="<?php print($customerclass->ClassDesc); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.customerclass")); ?>" class="button">Daftar Customer Class</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
