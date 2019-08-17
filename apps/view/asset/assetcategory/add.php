<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Add New Asset Category</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?><br />

<fieldset>
	<legend class="bold">Add New Asset Category</legend>
	<form action="<?php print($helper->site_url("asset.assetcategory/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td class="right"><label for="Code">Category Code :</label></td>
				<td><input type="text" id="Code" name="Code" size="10" value="<?php print($category->Code); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
			</tr>
            <tr>
                <td class="right"><label for="Name">Category Name :</label></td>
                <td><input type="text" id="Name" name="Name" size="30" value="<?php print($category->Name); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
			<tr>
				<td class="right"><label for="ActAccId">Asset Account:</label></td>
				<td><select id="ActAccId" name="ActAccId" required>
                        <option value=""></option>
					<?php
					foreach ($accounts as $account) {
						if ($category->AssetAccountId == $account->Id) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="DepAccId">Depreciation Account :</label></td>
				<td><select id="DepAccId" name="DepAccId" required>
                        <option value=""></option>
					<?php
					foreach ($accounts as $account) {
						if ($category->DepreciationAccountId == $account->Id) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="CosAccId">Cost Account :</label></td>
				<td><select id="CosAccId" name="CosAccId" required>
                        <option value=""></option>
					<?php
					foreach ($accounts as $account) {
						if ($category->CostAccountId == $account->Id) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="RevAccId">Revenue Account :</label></td>
				<td><select id="RevAccId" name="RevAccId">
                        <option value=""></option>
					<?php
					foreach ($accounts as $account) {
						if ($category->RevenueAccountId == $account->Id) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="DepMethod">Depreciation Method :</label></td>
				<td><select id="DepMethod" name="DepMethod" required>
					<option value="1" <?php print($category->DepreciationMethodId == 1 ? 'selected="selected"' : ''); ?>>Straight Line</option>
					<option value="2" <?php print($category->DepreciationMethodId == 2 ? 'selected="selected"' : ''); ?>>Double Declining</option>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="MaxAge">Useful Life :</label></td>
				<td><input type="text" id="MaxAge" name="MaxAge" value="<?php print($category->MaxAge); ?>" size="3" required/>&nbsp;Year(s)</td>
			</tr>
			<tr>
				<td class="right"><label for="DepPercentage">Depreciation Rate :</label></td>
				<td><input type="text" id="DepPercentage" name="DepPercentage" value="<?php print($category->DepreciationPercentage); ?>" size="3" required/>%</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("asset.assetcategory")); ?>">Asset Category List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
