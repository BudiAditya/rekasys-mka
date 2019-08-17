<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Rekap Aging Hutang Supplier</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#date").customDatePicker();
		});
	</script>
</head>

<body>
<?php /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */  ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Rekap Aging Hutang Suplier</span></legend>

	<form action="<?php print($helper->site_url("ap.report/rekap_aging")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="date">Per Tanggal :</label></td>
				<td>
					<input type="text" id="date" name="date" value="<?php print(date(JS_DATE, $date)); ?>" size="12" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="output">Format Report :</label></td>
				<td><select id="output" name="output">
					<option value="web">Web Browser</option>
					<option value="pdf">PDF Format (*.pdf)</option>
					<option value="xls">Excel 2003 Format (*.xls)</option>
					<option value="xlsx">Excel 2007 Format (*.xlsx)</option>
				</select></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Submit</button></td>
			</tr>
		</table>
	</form>
</fieldset>

<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="center bold title">
		Rekap Aging Hutang Suplier Company : <?php printf('%s - %s', $company->EntityCd, $company->CompanyName); ?>
	</div>
	<div class="center bold subTitle">
		Per Tanggal: <?php print(date(HUMAN_DATE, $date)); ?>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
		<tr class="bold center">
			<td class="bN bE bS bW">No.</td>
			<td class="bN bE bS">Kode Supplier</td>
			<td class="bN bE bS">Nama Supplier</td>
			<td class="bN bE bS">1 - 30 hari</td>
			<td class="bN bE bS">31 - 60 hari</td>
			<td class="bN bE bS">61 - 90 hari</td>
			<td class="bN bE bS">91 - 120 hari</td>
			<td class="bN bE bS">121 - 150 hari</td>
			<td class="bN bE bS">&gt; 150 hari</td>
			<td class="bN bE bS">Detail</td>
		</tr>
		<?php
		$counter = 0;
		$sums = array(
			"hutang_1" => 0
			, "hutang_2" => 0
			, "hutang_3" => 0
			, "hutang_4" => 0
			, "hutang_5" => 0
			, "hutang_6" => 0
		);
		while ($row = $report->FetchAssoc()) {
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			$sums["hutang_1"] += $row["sum_hutang_1"];
			$sums["hutang_2"] += $row["sum_hutang_2"];
			$sums["hutang_3"] += $row["sum_hutang_3"];
			$sums["hutang_4"] += $row["sum_hutang_4"];
			$sums["hutang_5"] += $row["sum_hutang_5"];
			$sums["hutang_6"] += $row["sum_hutang_6"];

			$link = $helper->site_url(sprintf("ap.report/detail_aging?date=%s&creditor=%d&output=web", date(SQL_DATEONLY, $date), $row["id"]));
		?>
		<tr class="<?php print($className); ?>">
			<td class="right bE bS bW"><?php print($counter); ?>.</td>
			<td class="bE bS"><?php print($row["creditor_cd"]); ?></td>
			<td class="bE bS"><?php print($row["creditor_name"]); ?></td>
			<td class="right bE bS"><?php print(number_format($row["sum_hutang_1"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($row["sum_hutang_2"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($row["sum_hutang_3"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($row["sum_hutang_4"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($row["sum_hutang_5"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($row["sum_hutang_6"], 2)); ?></td>
			<td class="center bE bS"><a href="<?php print($link); ?>">Detail</a></td>
		</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="3" class="right bE bS bW">GRAND TOTAL : </td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_1"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_2"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_3"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_4"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_5"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_6"], 2)); ?></td>
			<td>&nbsp;</td>
		</tr>
	</table>

</div>
<?php } ?>

</body>
</html>
