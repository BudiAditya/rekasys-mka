<!DOCTYPE html>
<?php
/** @var $unittype UnitType[]*/
/** @var $unitbrand UnitBrand[]*/
/** @var $unitclass UnitClass[]*/
/** @var $units Units */
/** @var $assets Asset[]*/
?>
<html>
<head>
	<title>Rekasys - Add New Unit</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			//var elements = ["TypeCode", "UnitName" ,"Save"];
			//BatchFocusRegister(elements);
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
	<legend><b>Add New Unit</b></legend>
	<form id="frm" action="<?php print($helper->site_url("master.units/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td class="bold right">Company :</td>
                <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
            <tr>
                <td class="bold right">Asset Code :</td>
                <td><select name="AssetId" id="AssetId" style="width: 150px;">
                        <option value=""></option>
                        <?php
                        foreach ($assets as $asset){
                            if ($units->AssetId == $asset->Id) {
                                printf("<option value='%d' selected='selected'> %s - %s </option>", $asset->Id, $asset->AssetCode, $asset->AssetName);
                            }else{
                                printf("<option value='%d'> %s - %s </option>", $asset->Id, $asset->AssetCode, $asset->AssetName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right">Type :</td>
                <td><select name="TypeCode" id="TypeCode" style="width: 150px;" required>
                        <option value=""></option>
                        <?php
                        foreach ($unittype as $type){
                            if ($units->TypeCode == $type->TypeCode) {
                                printf("<option value='%s' selected='selected'> %s - %s </option>", $type->TypeCode, $type->TypeCode, $type->TypeDesc);
                            }else{
                                printf("<option value='%s'> %s - %s </option>", $type->TypeCode, $type->TypeCode,$type->TypeDesc);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right">Brand :</td>
                <td><select name="BrandCode" id="BrandCode" style="width: 150px;" required>
                        <option value=""></option>
                        <?php
                        foreach ($unitbrand as $brand){
                            if ($units->BrandCode == $brand->BrandCode) {
                                printf("<option value='%s' selected='selected'> %s - %s </option>", $brand->BrandCode, $brand->BrandCode, $brand->BrandName);
                            }else{
                                printf("<option value='%s'> %s - %s </option>", $brand->BrandCode, $brand->BrandCode,$brand->BrandName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right">Model :</td>
                <td><input type="text" class="text2" name="UnitModel" id="UnitModel" style="width: 150px;" value="<?php print($units->UnitModel); ?>" required/></td>
            </tr>
            <tr>
                <td class="bold right">Class :</td>
                <td><select name="ClassCode" id="ClassCode" style="width: 150px;">
                        <option value=""></option>
                        <?php
                        foreach ($unitclass as $class){
                            if ($units->ClassCode == $class->ClassCode) {
                                printf("<option value='%s' selected='selected'> %s - %s </option>", $class->ClassCode, $class->ClassCode, $class->ClassName);
                            }else{
                                printf("<option value='%s'> %s - %s </option>", $class->ClassCode, $class->ClassCode, $class->ClassName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right">Unit Code :</td>
                <td><input type="text" class="text2" name="UnitCode" id="UnitCode" maxlength="10" style="width: 150px;" value="<?php print($units->UnitCode); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
			<tr>
                <td class="bold right">Unit Name :</td>
				<td><input type="text" class="text2" name="UnitName" id="UnitName" style="width: 250px;" value="<?php print($units->UnitName); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
			</tr>
            <tr>
                <td class="bold right">Serial No :</td>
                <td><input type="text" class="text2" name="SnNo" id="SnNo" style="width: 150px;" value="<?php print($units->SnNo); ?>" onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td class="bold right">Prod Year :</td>
                <td><input type="text" class="text2" name="ProdYear" id="ProdYear" maxlength="4" size="4" value="<?php print($units->ProdYear); ?>"/></td>
            </tr>
            <tr>
                <td class="bold right">Machine No :</td>
                <td><input type="text" class="text2" name="NoMesin" id="NoMesin" size="20" value="<?php print($units->NoMesin); ?>" onkeyup="this.value = this.value.toUpperCase();" /></td>
            </tr>
            <tr>
                <td class="bold right">Chasis No :</td>
                <td><input type="text" class="text2" name="NoChasis" id="NoChasis" size="20" value="<?php print($units->NoChasis); ?>" onkeyup="this.value = this.value.toUpperCase();" /></td>
            </tr>
            <tr>
                <td class="bold right">KM Position :</td>
                <td><input type="number" class="text2" name="KmPosition" id="KmPosition" size="10" value="<?php print($units->KmPosition); ?>"/></td>
            </tr>
            <tr>
                <td class="bold right">HM Position :</td>
                <td><input type="number" class="text2" name="HmPosition" id="HmPosition" size="10" value="<?php print($units->HmPosition); ?>"/></td>
            </tr>
            <tr>
                <td class="bold right">Unit Status :</td>
                <td><select name="UnitStatus" id="UnitStatus" required>
                        <option value="1" <?php print($units->UnitStatus == 1 ? 'selected="selected"' : '') ?>> 1 - Active </option>
                        <option value="0" <?php print($units->UnitStatus == 0 ? 'selected="selected"' : '') ?>> 0 - InActive </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button id="Save" type="submit">Save</button>
					<a href="<?php print($helper->site_url("master.units")); ?>">Unit List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
