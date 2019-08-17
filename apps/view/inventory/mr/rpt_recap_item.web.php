<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Rekap Barang MR terhadap Stock</title>
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
	<legend><span class="bold">MR - Stock Position Reca</span></legend>

	<div class="center subTitle bold">
		MR - Stock Position Recap
	</div>
    <br />

	<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
		<tr class="bold center">
			<td class="bN bE bS bW">No.</td>
			<td class="bN bE bS">MR Date</td>
			<td class="bN bE bS">MR Number</td>
			<td class="bN bE bS">Item Code</td>
            <td class="bN bE bS">Item Name</td>
            <td class="bN bE bS">Description</td>
			<td class="bN bE bS" colspan="2">Qty Request</td>
			<td class="bN bE bS" colspan="2">Stock On Hand</td>
		</tr>
		<?php
		$counter = 0;
		$prevItemId = null;
		while ($row = $rs->FetchAssoc()) {
			$docDate = strtotime($row["mr_date"]);
			if ($prevItemId != $row["item_id"]) {
				$prevItemId = $row["item_id"];
				$flagItem = true;
				$counter++;
			} else {
				$flagItem = false;
			}

			$className = $counter % 2 == 0 ? "evenRow" : "oddRow";
			$stockQty = $row["qty_stock"];
			if ($stockQty == null) {
				$stock = "-";
			} else {
				$stock = number_format($stockQty, 2);
			}
		?>
			<tr class="<?php print($className); ?>">
			<td class="bE bS bW right"><?php print($flagItem ? $counter : "&nbsp;"); ?></td>
            <td class="bE bS"><?php print(date(HUMAN_DATE, $docDate)); ?></td>
            <td class="bE bS"><?php print($row["doc_no"]); ?></td>
			<td class="bE bS"><?php print($flagItem ? $row["item_code"] : "&nbsp;"); ?></td>
            <td class="bE bS"><?php print($flagItem ? $row["item_name"] : "&nbsp;"); ?></td>
            <td class="bE bS"><?php print($row["item_description"]); ?></td>
			<td class="bE bS right"><?php print(number_format($row["app_qty"], 2)); ?></td>
			<td class="bE bS"><?php print($row["uom_cd"]); ?></td>
			<td class="bE bS right"><?php print($flagItem ? $stock : "&nbsp;"); ?></td>
			<td class="bE bS"><?php print($flagItem ? $row["stock_uom_cd"] : "&nbsp;"); ?></td>
		</tr>
		<?php } ?>
	</table><br /><br />
	<div class="center">
		<a href="<?php print($helper->site_url($excelLink)); ?>">Generate Data Diatas dalam format Excel</a><br /><br />
		<button type="button" onclick="history.back()">Kembali</button>
	</div>
</fieldset>

</body>
</html>
