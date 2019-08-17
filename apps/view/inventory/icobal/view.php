<!DOCTYPE HTML>
<?php
/** @var $icobal IcObal */
/** @var $items Item[] */
/** @var $projects Project[] */
?>
<html>
<head>
	<title>Rekasys - View Inventory Opening</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <style scoped>
        .f1{
            width:200px;
        }
        #fd{
            margin:0;
            padding:5px 10px;
        }
        .ftitle{
            font-size:14px;
            font-weight:bold;
            padding:5px 0;
            margin-bottom:10px;
            bpurchase-bottom:1px solid #ccc;
        }
        .fitem{
            margin-bottom:5px;
        }
        .fitem label{
            display:inline-block;
            width:100px;
        }
        .numberbox .textbox-text{
            text-align: right;
            color: blue;
        }
    </style>

    <script type="text/javascript">

        $(document).ready(function() {

                //var addmaster = ["CabangId", "GrnDate", "ReceiptDate", "SupplierId", "SalesName", "GrnDescs", "PaymentType", "CreditTerms", "btSubmit", "btKembali"];
                //BatchFocusRegister(addmaster);

                $('#ItemId').combogrid({
                    panelWidth: 800,
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
                    }
                });
        });
    </script>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<div id="p" class="easyui-panel" title="View Inventory Opening" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
            <tr>
                <td class="right">As Per Date :</td>
                <td><input type="text" class="easyui-textbox" id="OpnDate" name="OpnDate" style="width: 100px;text-align: right;"  value="<?php print($icobal->FormatOpnDate(SQL_DATEONLY)); ?>" required/></td>
            </tr>
			<tr>
				<td class="right">Project/Warehouse :</td>
				<td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" required>
                        <?php
                        if (count($projects) > 1){
                            print("<option value=''>-- PILIH PROJECT --</option>");
                            foreach ($projects as $project) {
                                if ($project->Id == $icobal->ProjectId) {
                                    printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                                } else {
                                    printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                                }
                            }
                        }else {
                            foreach ($projects as $project) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }
                        }
                        ?>
                    </select>
                </td>
			</tr>
            <tr>
                <td class="right">Item Code :</td>
                <td><input class="easyui-combogrid" id="ItemId" name="ItemId" style="width: 250px" value="<?php print($icobal->ItemId); ?>" required/></td>
            </tr>
			<tr>
				<td class="right"><label for="Qty">Qty :</label></td>
                <td><input type="number" class="easyui-textbox" id="Qty" name="Qty" style="width: 100px;text-align: right;"  value="<?php print($icobal->Qty); ?>" required/></td>
			</tr>
            <tr>
                <td class="right"><label for="Price">Price :</label></td>
                <td><input type="number" class="easyui-textbox" id="Price" name="Price" style="width: 100px;text-align: right;"  value="<?php print($icobal->Price); ?>"/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<a href="<?php print($helper->site_url("inventory.icobal")); ?>">Inventory Opening List</a>
				</td>
			</tr>
		</table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2019 PT. Reka Sistem Teknologi
</div>
</body>
</html>
