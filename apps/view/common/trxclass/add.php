<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Add New Transaction Class</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["TrxClassCd", "TrxClassDesc"];
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
	<legend><b>Add New Transaction Class</b></legend>
	<form id="frm" action="<?php print($helper->site_url("common.trxclass/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td align="right">Class Code :</td>
				<td><input type="text" name="TrxClassCd" id="TrxClassCd" size="3" maxlength="1" value="<?php print($trxclass->TrxClassCd); ?>" /></td>
			</tr>
			<tr>
				<td align="right">Class Description :</td>
				<td colspan="3"><input type="text" name="TrxClassDesc" id="TrxClassDesc" size="50" maxlength="10" value="<?php print($trxclass->TrxClassDesc); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.trxclass")); ?>" class="button">Trx Class List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
