<!DOCTYPE html>
<?php /** @var  $activity Activity */ ?>
<html>
<head>
	<title>Rekasys - Edit Activity</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["ActCode", "ActName" ,"Update"];
			BatchFocusRegister(elem
            ents);
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
	<legend><b>Edit Activity</b></legend>
	<form id="frm" action="<?php print($helper->site_url("master.activity/edit/".$activity->Id)); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Company</td>
                <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td>Act Code</td>
				<td><input type="text" class="text2" name="ActCode" id="ActCode" maxlength="10" size="10" value="<?php print($activity->ActCode); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
			</tr>
			<tr>
				<td>Act Name</td>
				<td><input type="text" class="text2" name="ActName" id="ActName" maxlength="50" size="30" value="<?php print($activity->ActName); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
			</tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button id="Update" type="submit">Update</button>
					<a href="<?php print($helper->site_url("master.activity")); ?>">Activity List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
