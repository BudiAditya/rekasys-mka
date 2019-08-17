<!DOCTYPE HTML>
<?php
/** @var $itemcategory ItemCategory */
/** @var $accounts Coa[] */
?>
<html>
<head>
	<title>Rekasys - Edit Items Category</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var elements = ["Code", "Description", "InvAccId","CostAccId","IsStock","Update"];
            BatchFocusRegister(elements);
            //RegisterFormSubmitByEnter("AccControlNo", "frm");
        });
    </script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?><br />

<fieldset>
	<legend class="bold">Edit Items Category</legend>
	<form action="<?php print($helper->site_url("inventory.itemcategory/edit/".$itemcategory->Id)); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td class="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td class="right"><label for="Code">Category Code :</label></td>
				<td><input type="text" id="Code" name="Code" size="8" value="<?php print($itemcategory->Code); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
			</tr>
			<tr>
				<td class="right"><label for="Description">Item Category :</label></td>
				<td><input type="text" id="Description" name="Description" size="50" value="<?php print($itemcategory->Description); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
			</tr>
			<tr>
				<td class="right"><label for="InvAccId">Inventory Account :</label></td>
				<td><select id="InvAccId" name="InvAccId">
					<option value="">-- N / A --</option>
					<?php
					foreach ($accounts as $account) {
						if ($itemcategory->InventoryAccountId == $account->Id) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="CostAccId">Cost Account :</label></td>
				<td><select id="CostAccId" name="CostAccId">
					<option value="">-- N / A --</option>
					<?php
					foreach ($accounts as $account) {
						if ($itemcategory->CostAccountId == $account->Id) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
                <td class="right"><label for="IsStock">Is Stock :</label></td>
				<td>
					<input type="checkbox" id="IsStock" name="IsStock" value="1" <?php print($itemcategory->IsStock ? 'checked="checked"' : ''); ?> />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button id="Update" type="submit">Update</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("inventory.itemcategory")); ?>">Items Category List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
