<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Rekasys - Detail Data Penyusutan Asset</title>
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
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
    <legend><span class="bold">Edit Asset Depreciation</span></legend>
    <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0;">
        <tr>
            <td class="right">Asset Code :</td>
            <td class="bold"><?php print($asset->AssetCode); ?></td>
        </tr>
        <tr>
            <td class="right">Asset Name :</td>
            <td class="bold"><?php printf("%s", $asset->AssetName);?></td>
        </tr>
        <tr>
            <td class="right">Purchase Date :</td>
            <td class="bold"><?php print($asset->FormatPurchaseDate()); ?></td>
        </tr>
        <tr>
            <td class="right">Qty x Price :</td>
            <td class="bold"><?php printf("%s x Rp. %s",number_format($asset->Qty, 0),number_format($asset->Price,2)); ?></td>
        </tr>
        <tr>
            <td class="right">Asset Value :</td>
            <td class="bold">Rp. <?php print(number_format($asset->Qty * $asset->Price, 2)); ?></td>
        </tr>
        <tr>
            <td class="right">Opening Depreciation Date :</td>
            <td class="bold"><?php print($asset->FormatInitLastDep()); ?></td>
        </tr>
        <tr>
            <td class="right">Opening Depreciation Accumulate :</td>
            <td class="bold">Rp. <?php print(number_format($asset->InitDepAccumulate, 2)); ?></td>
        </tr>
        <tr>
            <td class="right">Opening Book Value :</td>
            <td class="bold">Rp. <?php print(number_format(($asset->Qty * $asset->Price) - $asset->InitDepAccumulate, 2)); ?></td>
        </tr>
        <tr>
            <td class="right">Depreciation Method :</td>
            <td class="bold"><?php print($assetCategory->GetDepreciationMethod()); ?></td>
        </tr>
    </table>
    <hr>
	<div>
		<form action="<?php print($helper->site_url("asset.depreciation/edit/" . $depreciation->Id)); ?>" method="post">
			Asset Depreciation per Period <?php printf("%s %d", $monthNames[$month], $year); ?> Rp.
            &nbsp;
			<input type="text" class="bold right" id="Amount" name="Amount" size="15" value="<?php print($depreciation->Amount); ?>" />
            &nbsp;
			<button type="submit">Update</button>
            &nbsp;
            <a href="<?php print($helper->site_url("asset.depreciation/history/" . $asset->Id)); ?>">Depreciation History</a>
            &nbsp;
            <a href="<?php print($helper->site_url("asset.asset")); ?>">Asset List</a>
		</form>
	</div>
</fieldset>

</body>
</html>
