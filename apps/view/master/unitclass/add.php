<!DOCTYPE html>
<?php /** @var  $unitclass UnitClass */?>
<html>
<head>
	<title>Rekasys - Add New Unit Class</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["ClassCode", "ClassName" ,"Save"];
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
	<legend><b>Add New Unit Class</b></legend>
	<form id="frm" action="<?php print($helper->site_url("master.unitclass/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td class="bold right">Company :</td>
                <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
                <td class="bold right">Class Code :</td>
				<td><input type="text" class="text2" name="ClassCode" id="ClassCode" maxlength="2" size="3" value="<?php print($unitclass->ClassCode); ?>" onkeyup="this.value = this.value.toUpperCase();" required/>&nbsp;<sub>01 ~ 99</sub></td>
			</tr>
			<tr>
                <td class="bold right">Class Name :</td>
				<td><input type="text" class="text2" name="ClassName" id="ClassName" maxlength="50" size="30" value="<?php print($unitclass->ClassName); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
			</tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button id="Save" type="submit">Save</button>
					<a href="<?php print($helper->site_url("master.unitclass")); ?>">Unit Class List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
