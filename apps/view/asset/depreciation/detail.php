<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Rekasys - Asset Depreciation Detail</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#linkDelete").click(function() {
				return confirm("Apakah anda yakin mau menghapus data berikut ?");
			});
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
	<legend><span class="bold">Asset Depreciation Detail</span></legend>
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
	</table> <br />

	<?php
	$balance = 	$asset->Qty * $asset->Price;
    ?>
	<table cellspacing="0" cellpadding="0" class="tablePadding tableBorder" style="margin: 0;">
		<tr class="center bold">
			<th>Description</th>
            <th>Depreciation</th>
			<th>Book Value</th>
		</tr>
		<tr>
			<td>Depreciation Until Desember <?php print($year - 1); ?></td>
			<td class="bold right"><?php print(number_format($totalPrevYear, 2)); ?></td>
			<td class="bold right"><?php $balance -= $totalPrevYear;print(number_format($balance, 2)); ?></td>
		</tr>
		<?php if ($month > 1) { ?>
		<tr>
			<td><?php printf("Depreciation 1 Januari %d s.d. %s %d", $year, $monthNames[$month - 1], $year) ?></td>
			<td class="bold right"><?php print(number_format($totalRunningYear, 2)); ?></td>
			<td class="bold right"><?php $balance -= $totalRunningYear;print(number_format($balance, 2)); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td><?php printf("Depreciation %s %d", $monthNames[$month], $year); ?></td>
			<td class="bold right"><?php print(number_format($depreciation->Amount, 2)); ?></td>
			<td class="bold right"><?php $balance -= $depreciation->Amount;print(number_format($balance, 2)); ?></td>
		</tr>
	</table>
    <br />
	<div>
		<a href="<?php print($helper->site_url("asset.depreciation/history/" . $asset->Id)); ?>">Depreciation History</a>
		&nbsp;&nbsp;&nbsp;
		<a href="<?php print($helper->site_url("asset.depreciation/edit/" . $depreciation->Id)); ?>">Edit Depreciation Data</a>
		&nbsp;&nbsp;&nbsp;
		<a id="linkDelete" href="<?php print($helper->site_url("asset.depreciation/delete/" . $depreciation->Id)); ?>">Delete Depreciation Data</a>
	</div>
</fieldset>

</body>
</html>
