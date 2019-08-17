<!DOCTYPE html>
<?php /** @var  $unitcomp UnitComp */?>
<html>
<head>
	<title>Rekasys - Add New Unit Component</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["CompCode", "CompName" ,"Save"];
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
	<legend><b>Add New Unit Component</b></legend>
	<form id="frm" action="<?php print($helper->site_url("master.unitcomp/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td class="bold right">Company :</td>
                <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
                <td class="bold right">Comp Code :</td>
				<td><input type="text" class="text2" name="CompCode" id="CompCode" maxlength="3" size="3" value="<?php print($unitcomp->CompCode); ?>" onkeyup="this.value = this.value.toUpperCase();" required/>&nbsp;<sub>001 ~ 999</sub></td>
			</tr>
			<tr>
                <td class="bold right">Comp Name :</td>
				<td><input type="text" class="text2" name="CompName" id="CompName" maxlength="150" size="50" value="<?php print($unitcomp->CompName); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
			</tr>
            <tr>
                <td class="bold right">Unit Model :</td>
                <td><input type="text" class="text2" name="CompModel" id="CompModel" maxlength="50" size="30" value="<?php print($unitcomp->CompModel); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button id="Save" type="submit">Save</button>
					<a href="<?php print($helper->site_url("master.unitcomp")); ?>">Unit Component List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
