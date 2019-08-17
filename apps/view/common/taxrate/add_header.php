<!DOCTYPE HTML>
<html>
<head>
	<title>Entry Skema Pajak Step 1</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
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
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Entry Skema Pajak Step 1 - Data Header</span></legend>
	<form action="<?php print($helper->site_url("common.taxrate/add_header")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td>Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td><label for="TaxSchCd">Kode:</label></td>
				<td><input type="text" name="TaxSchCd" id="TaxSchCd" maxlength="2" size="3" value="<?php print($taxRate->TaxSchCd); ?>" /></td>
			</tr>
			<tr>
				<td><label for="TaxSchDesc">Skema Pajak:</label></td>
				<td><input type="text" name="TaxSchDesc" id="TaxSchDesc" maxlength="50" size="50" value="<?php print($taxRate->TaxSchDesc); ?>" /></td>
			</tr>
            <tr>
                <td><label for="TaxMode">Jenis Pajak:</label></td>
                <td><select name="TaxMode" id="TaxMode">
                    <option value="0"></option>
                    <option value="1" <?php if($taxRate->TaxMode == 1){print("selected='selected'");}?>>Masukan</option>
                    <option value="2" <?php if($taxRate->TaxMode == 2){print("selected='selected'");}?>>Keluaran</option>
                </select>
                </td>
            </tr>
			<tr>
				<td><label for="InclExcl">Termasuk:</label></td>
				<td><input type="checkbox" id="InclExcl" name="InclExcl" <?php print($taxRate->InclExcl == 1 ? 'checked="checked"' : ''); ?>/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Berikutnya &gt;</button>
					<a href="<?php print($helper->site_url("common.taxrate")) ?>">Daftar Skema Pajak</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
