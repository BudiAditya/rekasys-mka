<!DOCTYPE html>
<?php
/** @var $asset Asset */
/** @var $categorys AssetCategory[] */
?>
<html>
<head>
	<title>Rekasys - View Asset</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
          $("#PurchaseDate").customDatePicker();
          $("#LastDep").customDatePicker();
          $("#Qty").autoNumeric();
          $("#Price").autoNumeric();
          $("#AssetValue").autoNumeric();
          $("#DepAccumulate").autoNumeric();
          $("#Residu").autoNumeric();

          //initialize
          var dta = '<?php print($dcat);?>';
          var dtx = dta.split('|');
          $("#CategoryId").val(dtx[0]);
          $("#DepMethod").val(dtx[1]);
          $("#UsefulLife").val(dtx[2]);
          $("#DepRate").val(dtx[3]);
          hitung();
        });

        function hitung() {
            var dpr = Number($("#DepRate").val());
            var dpa = Number(removeComma($("#DepAccumulate").val()));
            var qty = Number(removeComma($("#Qty").val()));
            var prc = Number(removeComma($("#Price").val()));
            var asv = qty * prc;
            var dpy = 0;
            var dpm = 0;
            var rsd = 0;

            if (asv > 0 && dpr > 0) {
                dpy = Math.round(asv * (dpr / 100), 2);
                dpm = Math.round(dpy/12,2);
                rsd = asv - dpa;
            }

            $("#AssetValue").val(addComma(asv));
            //$("#DepPerYear").val(addComma(dpy));
            //$("#DepPerMonth").val(addComma(dpm));
            $("#Residu").val(addComma(rsd));
        }

        function removeComma(str){
            return str.replace(/,/g, '').trim();
        }

        function addComma(str){
            return str.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?><br />

<fieldset>
	<legend class="bold">View Asset</legend>
    <table cellpadding="2" cellspacing="1">
        <tr>
            <td class="right"><label for="xCategoryId">Asset Category :</label></td>
            <td colspan="3"><select id="xCategoryId" name="xCategoryId" disabled style="width:200px">
                    <option value=""></option>
                    <?php
                    $data = null;
                    foreach ($categorys as $category) {
                        $data = $category->Id.'|'.$category->GetDepreciationMethod().'|'.$category->MaxAge.'|'.$category->DepreciationPercentage;
                        if ($asset->CategoryId == $category->Id) {
                            printf('<option value="%s" selected="selected">%s - %s</option>', $data, $category->Code, $category->Name);
                        } else {
                            printf('<option value="%s">%s - %s</option>', $data, $category->Code, $category->Name);
                        }
                    }
                    ?>
                </select>
                <input type="hidden" name="CategoryId" id="CategoryId" value="<?php print($asset->CategoryId);?>">
            </td>
        </tr>
        <tr>
            <td class="right"><label for="PurchaseDate">Purchase Date :</label></td>
            <td><input type="text" id="PurchaseDate" name="PurchaseDate" style="width: 100px" value="<?php print($asset->FormatPurchaseDate(JS_DATE)); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right"><label for="AssetCode">Asset Code :</label></td>
            <td><input type="text" id="AssetCode" name="AssetCode" style="width: 100px" value="<?php print($asset->AssetCode); ?>" onkeyup="this.value = this.value.toUpperCase();" readonly/></td>
        </tr>
        <tr>
            <td class="right"><label for="AssetName">Asset Name :</label></td>
            <td colspan="3"><input type="text" id="AssetName" name="AssetName" style="width: 300px" value="<?php print($asset->AssetName); ?>" readonly onkeyup="this.value = this.value.toUpperCase();"/></td>
        </tr>
        <tr>
            <td class="right"><label for="Qty">Asset Qty :</label></td>
            <td><input type="text" class="right" id="Qty" name="Qty" style="width: 100px" value="<?php print($asset->Qty);?>" readonly/></td>
            <td class="right"><label for="Price">Unit Price :</label></td>
            <td><input type="text" class="right" id="Price" name="Price" style="width: 100px" value="<?php print($asset->Price);?>" readonly/></td>
            <td class="right"><label for="AssetValue">Asset Value :</label></td>
            <td><input type="text" class="right" id="AssetValue" name="AssetValue" style="width: 100px" value="<?php print($asset->Qty * $asset->Price);?>" readonly/></td>
        </tr>
        <tr>
            <td class="right"><label for="DepMethod">Dep Method :</label></td>
            <td><input type="text" class="bold" id="DepMethod" name="DepMethod" style="width: 100px" value="" disabled/></td>
            <td class="right"><label for="UsefulLife">Useful Life :</label></td>
            <td><input type="text" class="right bold" id="UsefulLife" name="UsefulLife" style="width: 30px" value="0" disabled/>&nbsp;Year(s)</td>
            <td class="right"><label for="DepRate">Dep Rate :</label></td>
            <td><input type="text" class="right bold" id="DepRate" name="DepRate" style="width: 30px" value="0" disabled/>&nbsp;% per Year</td>
        </tr>
        <!--
        <tr>
            <td class="right"><label for="DepPerYear">Dep Per Year :</label></td>
            <td><input type="text" class="right" id="DepPerYear" name="DepPerYear" style="width: 100px" value="0" disabled/></td>
            <td class="right"><label for="DepPerMonth">Dep Per Month :</label></td>
            <td><input type="text" class="right" id="DepPerMonth" name="DepPerMonth" style="width: 100px" value="0" disabled/></td>
        </tr>
        -->
        <tr>
            <td class="right"><label for="LastDep">Last Depreciation :</label></td>
            <td><input type="text" id="LastDep" name="LastDep" style="width: 100px" value="<?php print($asset->FormatLastDep(JS_DATE)); ?>" readonly/></td>
            <td class="right"><label for="DepAccumulate">Dep Accumulate :</label></td>
            <td><input type="text" class="right" id="DepAccumulate" name="DepAccumulate" style="width: 100px" value="<?php print($asset->DepAccumulate); ?>" readonly/></td>
            <td class="right"><label for="Residu">Book Value :</label></td>
            <td><input type="text" class="right" id="Residu" name="Residu" style="width: 100px" value="0" disabled/></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">
                <a href="<?php print($helper->site_url("asset.asset")); ?>">Asset List</a>
            </td>
        </tr>
    </table>
</fieldset>

</body>
</html>
