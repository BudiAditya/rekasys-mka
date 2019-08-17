<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Cash Request (NPKP) Entry Step 1</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#Date").customDatePicker();
			$("#Eta").customDatePicker();
		});
	</script>
</head>

<body>
<?php /** @var $company Company */ /** @var $cashRequest CashRequest */ /** @var $categories CashRequestCategory[] */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Cash Request (NPKP) Entry Step 1 - Data Master</span></legend>
	<form action="<?php print($helper->site_url("accounting.cashrequest/add?step=master")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td class="right"><label for="DocumentNo">NPKP No. :</label></td>
				<td><input type="text" name="DocumentNo" id="DocumentNo" value="<?php print($cashRequest->DocumentNo); ?>" readonly="readonly" /></td>
			</tr>
			<tr>
				<td class="right"><label for="CategoryId">Project :</label></td>
				<td><select id="CategoryId" name="CategoryId">
					<?php
					foreach ($categories as $category) {
						if ($category->Id == $cashRequest->CategoryId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $category->Id, $category->Code, $category->Name);
						} else {
							printf('<option value="%d">%s - %s</option>', $category->Id, $category->Code, $category->Name);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="Date">NPKP Date :</label></td>
				<td><input type="text" id="Date" name="Date" value="<?php print($cashRequest->FormatDate(JS_DATE)); ?>" size="12"></td>
			</tr>
			<tr>
				<td class="right"><label for="Objective">NPKP Purpose :</label></td>
				<td><input type="text" id="Objective" name="Objective" value="<?php print($cashRequest->Objective); ?>" size="50" required></td>
			</tr>
			<tr>
				<td class="right"><label for="Note">Description :</label></td>
				<td><textarea rows="3" cols="60" id="Note" name="Note"><?php print($cashRequest->Note); ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><label for="Date">Request Date :</label></td>
				<td><input type="text" id="Eta" name="Eta" value="<?php print($cashRequest->FormatEtaDate(JS_DATE)); ?>" size="12"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Next &gt;</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("accounting.cashrequest")); ?>">NPKP List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
