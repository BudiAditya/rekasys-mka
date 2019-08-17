<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Ganti Password</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["Old","New","Retype","Submit"];
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
	<legend><span class="bold">Ganti Password Sendiri</span></legend>

	<form action="<?php print($helper->site_url("main/change_password")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right"><label for="Old">Password Lama :</label></td>
				<td><input type="password" id="Old" name="Old" /></td>
			</tr>
			<tr>
				<td class="right"><label for="New">Password Baru :</label></td>
				<td><input type="password" id="New" name="New" /></td>
			</tr>
			<tr>
				<td class="right"><label for="Retype">Ulangi Password Baru :</label></td>
				<td><input type="password" id="Retype" name="Retype"></td>
			</tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button type="submit" id="Submit">Ganti Password</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
