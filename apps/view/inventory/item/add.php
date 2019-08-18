<!DOCTYPE HTML>
<?php
/** @var $item Item */
?>
<html>
<head>
	<title>Rekasys - Add New Item Spare Parts</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript">
        $(document).ready(function(){
            //var elements = ["CategoryId", "Code", "PartNo","Barcode","Name","Note","UnitTypeCode","AssetCategoryId","MaxQty","MinQty","Uom","Obsolete","Save"];
            //BatchFocusRegister(elements);
            $("#MaxQty").autoNumeric({ mDec: 0 });
            $("#MinQty").autoNumeric({ mDec: 0 });

            $('#aItemSearch').combogrid({
                panelWidth:800,
                url: "<?php print($helper->site_url("inventory.item/getjson_items"));?>",
                idField: 'id',
                textField: 'item_code',
                mode: 'remote',
                fitColumns: true,
                columns: [[
                    {field: 'item_code', title: 'Item Code', width: 100},
                    {field: 'part_no', title: 'Part Number', width: 150},
                    {field: 'item_name', title: 'Item Name', width: 300},
                    {field: 'uom_cd', title: 'UOM', width: 50},
                    {field: 'brand_name', title: 'Brand', width: 100},
                    {field: 'type_desc', title: 'Type', width: 100}
                ]],
                onSelect: function (index, row) {
                    var iti = row.id;
                    console.log(iti);
                    var itc = row.item_code;
                    console.log(itc);
                    var ptn = row.part_no;
                    console.log(ptn);
                    var itn = row.item_name;
                    console.log(itn);
                    var ucd = row.uom_cd;
                    console.log(ucd);
                    var qcl = Number($("#Qclass").val());
                    $("#IcxCode").val(itc);
                    $("#IcxCode1").val(itc);
                    if (qcl == 3) {
                        $("#Code").val(itc + 'R');
                        $("#PartNo").val(ptn);
                    }
                    $("#Name").val(itn);
                    $("#PartNo").focus();
                    $('#dlg').dialog('close');
                }
            });

            $("#Qclass").change(function() {
                $("#Code").val('');
                $("#IcxCode").val('');
                $("#IcxCode1").val('');
                if (this.value > 1){
                    $("#IcxCode1").prop('disabled', false);
                    $("#IcxCode1").attr('required',true);
                }else{
                    $("#IcxCode1").attr('required',false);
                    $("#IcxCode1").prop('disabled', true);
                }
            });

            $("#IcxCode1").change(function(){
                var icd = this.value;
                var qcl = Number($("#Qclass").val());
                if (qcl == 3){
                    $("#Code").val(icd + 'R');
                    $("#IcxCode").val(icd);
                }else if (qcl == 2){
                    $("#Code").val('');
                    $("#IcxCode").val(icd);
                }else{
                    $("#Code").val('');
                    $("#IcxCode").val('');
                }
            });
        });

        function searchItem(){
            var qcl = Number($("#Qclass").val());
            var ttl = null;
            if (qcl > 1) {
                if (qcl == 2) {
                    ttl = 'Search Interchange Item';
                } else if (qcl == 3) {
                    ttl = 'Search Repaired Item'
                }
                $('#dlg').dialog('open').dialog('setTitle', ttl);
                $('#fm').form('clear');
                $('#aMode').val('N');
                $('#aItemSearch').focus();
            }
        }

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
	<legend class="bold">Add New Item & Spare Parts</legend>

	<form action="<?php print($helper->site_url("inventory.item/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
            <tr>
                <td class="right"><label for="CategoryId">Item Category:</label></td>
                <td><select id="CategoryId" name="CategoryId" required style="width:200px;">
                        <option value="">-- PILIH KATEGORI --</option>
                        <?php
                        foreach ($categories as $category) {
                            if ($category->Id == $item->CategoryId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $category->Id, $category->Code, $category->Description);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $category->Id, $category->Code, $category->Description);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="right"><label for="UnitTypeCode">Unit Brand :</label></td>
                <td><select id="UnitBrandCode" name="UnitBrandCode" style="width:200px;" required>
                        <option value="">-- PILIH BRAND UNIT --</option>
                        <?php
                        /** @var $ubrand UnitBrand[] */
                        foreach ($ubrand as $brand) {
                            if ($brand->BrandCode == $item->UnitBrandCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $brand->BrandCode, $brand->BrandCode, $brand->BrandName);
                            } else {
                                printf('<option value="%s">%s - %s</option>', $brand->BrandCode, $brand->BrandCode, $brand->BrandName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="right"><label for="UnitTypeCode">Unit Type :</label></td>
                <td><select id="UnitTypeCode" name="UnitTypeCode" style="width:200px;" required>
                        <option value="">-- PILIH TYPE UNIT --</option>
                        <?php
                        /** @var $utype UnitType[] */
                        foreach ($utype as $type) {
                            if ($type->TypeCode == $item->UnitTypeCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $type->TypeCode, $type->TypeCode, $type->TypeDesc);
                            } else {
                                printf('<option value="%s">%s - %s</option>', $type->TypeCode, $type->TypeCode, $type->TypeDesc);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="right"><label for="UnitCompCode">Unit Component :</label></td>
                <td><select id="UnitCompCode" name="UnitCompCode" style="width:300px;">
                        <option value="">-- PILIH COMPONENT --</option>
                        <?php
                        /** @var $ucomp UnitComp[] */
                        foreach ($ucomp as $comp) {
                            if ($comp->CompCode == $item->UnitCompCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $comp->CompCode, $comp->CompCode, $comp->CompName);
                            } else {
                                printf('<option value="%s">%s - %s</option>', $comp->CompCode, $comp->CompCode, $comp->CompName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="right"><label for="Qclass">Quality Class :</label></td>
                <td><select id="Qclass" name="Qclass">
                        <option value="1" <?php print($item->Qclass == 1 ? 'selected="selected"' : '');?>>1 - Genuine</option>
                        <option value="2" <?php print($item->Qclass == 2 ? 'selected="selected"' : '');?>>2 - Interchange</option>
                        <option value="3" <?php print($item->Qclass == 3 ? 'selected="selected"' : '');?>>3 - Repaired</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="IcxCode1">Interchange Of :</label></td>
                <td><select id="IcxCode1" name="IcxCode1" style="width: 300px" disabled>
                        <option value=""></option>
                        <?php
                        /** @var $items Item[] */
                        foreach ($items as $itm){
                            if ($itm->ItemCode == $item->IcxCode) {
                                printf("<option value='%s' selected='selected'>%s - %s %s</option>", $itm->ItemCode, $itm->ItemCode, $itm->ItemName, strlen($itm->PartNo) > 2 ? '(' . $itm->PartNo . ')' : '');
                            }else{
                                printf("<option value='%s'>%s - %s %s</option>",$itm->ItemCode,$itm->ItemCode,$itm->ItemName,strlen($itm->PartNo) > 2 ? '('.$itm->PartNo.')' : '');
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" name="IcxCode" id="IcxCode" value="<?php print($item->IcxCode); ?>"/>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="searchItem();" style="width: 70px;height: 20px;">Search</a>
                </td>
            </tr>
			<tr>
				<td class="right"><label for="Code">Item Code :</label></td>
				<td><input type="text" id="Code" name="Code" maxlength="12" style="width:200px;" value="<?php print($item->ItemCode); ?>" readonly placeholder="[AUTO]"/>&nbsp;<sub>* max 12 digit</sub></td>
			</tr>
            <tr>
                <td class="right"><label for="Name">Item Name :</label></td>
                <td><input type="text" id="Name" name="Name" style="width:300px;" value="<?php print($item->ItemName); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td class="right"><label for="PartNo">Part Number :</label></td>
                <td><input type="text" id="PartNo" name="PartNo" style="width:200px;" value="<?php print($item->PartNo); ?>" onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td class="right"><label for="Barcode">Bar Code :</label></td>
                <td><input type="text" id="Barcode" name="Barcode" style="width:200px;" value="<?php print($item->Barcode); ?>" onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td class="right"><label for="SnNo">Serial Number :</label></td>
                <td><input type="text" id="SnNo" name="SnNo" style="width:200px;" value="<?php print($item->SnNo); ?>" onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
			<tr>
				<td class="right"><label for="Uom">Stock UOM :</label></td>
				<td><select id="Uom" name="Uom" style="width:200px;" required>
                    <option value=""></option>
					<?php
					foreach ($measurements as $measurement) {
						if ($measurement->UomCd == $item->UomCode) {
							printf('<option value="%s" selected="selected">%s - %s</option>', $measurement->UomCd, $measurement->UomCd, $measurement->UomDesc);
						} else {
							printf('<option value="%s">%s - %s</option>', $measurement->UomCd, $measurement->UomCd, $measurement->UomDesc);
						}
					}
					?>
				    </select>
                </td>
			</tr>
            <tr>
                <td class="right"><label for="LUom">Purchase UOM :</label></td>
                <td><select id="LUom" name="LUom" style="width:200px;">
                        <option value=""></option>
                        <?php
                        foreach ($measurements as $measurement) {
                            if ($measurement->UomCd == $item->UomCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $measurement->UomCd, $measurement->UomCd, $measurement->UomDesc);
                            } else {
                                printf('<option value="%s">%s - %s</option>', $measurement->UomCd, $measurement->UomCd, $measurement->UomDesc);
                            }
                        }
                        ?>
                    </select>
                    <label for="UomConversion">Conversion :</label>
                    <input type="text" class="right" id="UomConversion" name="UomConversion" size="7" value="<?php print($item->UomConversion); ?>"/>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="MaxQty">Stock Min :</label></td>
                <td><input type="text" class="right" id="MinQty" name="MinQty" size="7" value="<?php print($item->MinQty); ?>" />
                    <label for="MinQty">Max :</label>
                    <input type="text" class="right" id="MaxQty" name="MaxQty" size="7" value="<?php print($item->MaxQty); ?>" />
                </td>
            </tr>
            <tr>
                <td class="right"><label for="Note">Notes :</label></td>
                <td><textarea cols="40" rows="2" id="Note" name="Note" style="width:300px;"><?php print($item->Note); ?></textarea></td>
            </tr>
            <tr>
                <td class="right"><label for="OtherItemCode">Other Item Code :</label></td>
                <td><input type="text" id="OtherItemCode" name="OtherItemCode" maxlength="12" style="width:200px;" value="<?php print($item->OtherItemCode); ?>" onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td class="right"><label for="OtherItemName">Other Item Name :</label></td>
                <td><input type="text" id="OtherItemName" name="OtherItemName" style="width:300px;" value="<?php print($item->OtherItemName); ?>" onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td class="right"><label for="AddNotes">Description :</label></td>
                <td><input type="text" id="AddNotes" name="AddNotes" style="width:300px;" value="<?php print($item->AddNotes); ?>"/></td>
            </tr>
            <tr>
                <td class="right"><label for="StockLocationId">Stock Location :</label></td>
                <td><select id="StockLocationId" name="StockLocationId" style="width:200px;">
                        <option value="0"></option>
                        <?php
                        /** @var $stocklocation StockLocation[] */
                        foreach ($stocklocation as $location) {
                            if ($location->Id == $item->StockLocationId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $location->Id, $location->BinCode, $location->LocName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $location->Id, $location->BinCode, $location->LocName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="Obsolete">Non-Aktif :</label></td>
                <td>
                    <input type="checkbox" id="Obsolete" name="Obsolete" value="1" <?php print($item->IsDiscontinued ? 'checked="checked"' : ''); ?> />
                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button id="Save" type="submit">Save</button>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($helper->site_url("inventory.item")); ?>">Item & Part List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- Modal search item -->
<div id="dlg" class="easyui-dialog" style="width:620px;height:80px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
     <input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:590px"/>
</div>
</body>
</html>
