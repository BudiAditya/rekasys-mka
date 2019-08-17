<!DOCTYPE html>
<?php
/** @var $asset Asset */
?>
<html>
<head>
	<title>Rekasys - Asset Depreciation History</title>
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
	<legend><span class="bold">Depreciation Process</span></legend>
	<form action="<?php print($helper->site_url("asset.depreciation/process/" . $asset->Id)) ?>" method="get">
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
			<tr>
				<td class="right"><label for="month">Depreciation Period :</label></td>
				<td>
					<select id="month" name="month">
						<option value="1">Januari</option>
						<option value="2">Februari</option>
						<option value="3">Maret</option>
						<option value="4">April</option>
						<option value="5">Mei</option>
						<option value="6">Juni</option>
						<option value="7">Juli</option>
						<option value="8">Agustus</option>
						<option value="9">September</option>
						<option value="10">Oktober</option>
						<option value="11">November</option>
						<option value="12">Desember</option>
					</select>
					<label for="year"> Year: </label>
					<select id="year" name="year">
						<?php
						for ($i = (int)date("Y"); $i >= $startYear; $i--) {
							printf('<option value="%d">%d</option>', $i, $i);
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Process</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("asset.asset")); ?>">Asset List</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset><br />

<div class="container">
	<div class="title">Asset Depreciation History</div>

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0;">
		<tr class="bold">
			<th>No.</th>
			<th>Depr Date</th>
			<th>Method</th>
			<th>Rate</th>
			<th>Depreciation</th>
			<th>Book Value</th>
			<th>View</th>
		</tr>
		<?php
        $balance = round($asset->Qty * $asset->Price,2);
		foreach ($histories as $idx => $history) {
			$balance-= $history->Amount;
		?>
		<tr>
			<td><?php print($idx + 1); ?></td>
			<td><?php print($history->FormatDepreciationDate()); ?></td>
			<td><?php print($history->GetDepreciationMethod()); ?></td>
			<td><?php print(number_format($history->Percentage, 1)); ?>%</td>
			<td class="right"><?php print(number_format($history->Amount, 2)); ?></td>
			<td class="right"><?php print(number_format($balance, 2)); ?></td>
			<td class="center"><a href="<?php print($helper->site_url("asset.depreciation/detail/" . $history->Id)); ?>">Detail</a></td>
		</tr>
		<?php } ?>
	</table>
</div>

</body>
</html>
