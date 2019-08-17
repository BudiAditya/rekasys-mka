<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Detail Aging Hutang Supplier</title>
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
<?php /** @var $creditorId null|int */ /** @var $creditors Creditor[] */ /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */  ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Detail Aging Hutang Supplier</span></legend>

	<form action="<?php print($helper->site_url("ap.report/detail_aging")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="date">Per Tanggal :</label></td>
				<td>
					<input type="text" id="date" name="date" value="<?php print(date(JS_DATE, $date)); ?>" size="12" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="creditor">Kreditor :</label></td>
				<td><select id="creditor" name="creditor">
					<option value="">-- SEMUA KREDITOR --</option>
					<?php
					$selectedCreditor = null;
					foreach ($creditors as $creditor) {
						if ($creditor->Id == $creditorId) {
							$selectedCreditor = $creditor;
							printf('<option value="%d" selected="selected">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName);
						} else {
							printf('<option value="%d">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName);
						}
					}
					?>
				</select></td>
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
		Detail Aging Hutang Supplier Company : <?php printf('%s - %s', $company->EntityCd, $company->CompanyName); ?>
	</div>
	<div class="center bold subTitle">
		Per Tanggal: <?php print(date(HUMAN_DATE, $date)); ?>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
		<tr class="bold center">
			<td class="bN bE bS bW">No.</td>
			<td class="bN bE bS">No. Bukti</td>
			<td class="bN bE bS">Tanggal</td>
			<td class="bN bE bS">Nilai Invoice</td>
			<td class="bN bE bS">1 - 30 hari</td>
			<td class="bN bE bS">31 - 60 hari</td>
			<td class="bN bE bS">61 - 90 hari</td>
			<td class="bN bE bS">91 - 120 hari</td>
			<td class="bN bE bS">121 - 150 hari</td>
			<td class="bN bE bS">&gt; 150 hari</td>
			<td class="bN bE bS">Total</td>
		</tr>
		<?php
		$counter = 0;
		$sums = array(
			"dokumen" => 0
			, "hutang_1" => 0
			, "hutang_2" => 0
			, "hutang_3" => 0
			, "hutang_4" => 0
			, "hutang_5" => 0
			, "hutang_6" => 0
			, "total" => 0
		);
		$prevCreditorId = null;
		while ($row = $report->FetchAssoc()) {
			$counter++;
			$amount = $row["sum_amount"];

			$sums["dokumen"] += $amount;
			$age = $row["age"];
			$date = strtotime($row["doc_date"]);

			// Reset variable
			$hutang1 = 0;
			$hutang2 = 0;
			$hutang3 = 0;
			$hutang4 = 0;
			$hutang5 = 0;
			$hutang6 = 0;
			$hutang = $amount - $row["sum_paid"];

			if ($age <= 0) {
				// Nothing to do... data ini di skip tapi masih ditampilkan walau 0 semua
			} else if ($age <= 30) {
				$hutang1 = $hutang;
				$sums["hutang_1"] += $hutang;
				$sums["total"] += $hutang;
			} else if ($age <= 60) {
				$hutang2 = $hutang;
				$sums["hutang_2"] += $hutang;
				$sums["total"] += $hutang;
			} else if ($age <= 90) {
				$hutang3 = $hutang;
				$sums["hutang_3"] += $hutang;
				$sums["total"] += $hutang;
			} else if ($age <= 120) {
				$hutang4 = $hutang;
				$sums["hutang_4"] += $hutang;
				$sums["total"] += $hutang;
			} else if ($age <= 150) {
				$hutang5 = $hutang;
				$sums["hutang_5"] += $hutang;
				$sums["total"] += $hutang;
			} else {
				$hutang6 = $hutang;
				$sums["hutang_6"] += $hutang;
				$sums["total"] += $hutang;
			}

			// Header untuk debtor..
			if ($prevCreditorId != $row["supplier_id"]) {
				// Counter nomor ketika ganti supplier ter-reset
				$counter = 1;
				$prevCreditorId = $row["supplier_id"];
				printf("<tr class='bold'><td class='right bE bS bW' colspan='3'>Kode Creditor: %s</td><td class='bE bS' colspan='8'>%s</td></tr>\n", $row["creditor_cd"], $row["creditor_name"]);
			}
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";

			if ($row["source"] == "IV") {
				$link = $helper->site_url("ap.invoice/view/" . $row["id"]);
			} else if ($row["source"] == "GN") {
				$link = $helper->site_url("inventory.gn/view/" . $row["id"]);
			} else {
				$link = "#";
			}
		?>
		<tr class="<?php print($className); ?>">
			<td class="right bE bS bW"><?php print($counter); ?>.</td>
			<td class="bE bS"><a href="<?php print($link); ?>"><?php print($row["doc_no"]); ?></a></td>
			<td class="bE bS"><?php print(date(HUMAN_DATE, $date)); ?></td>
			<td class="right bE bS"><?php print(number_format($amount, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang1, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang2, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang3, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang4, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang5, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang6, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($hutang1 + $hutang2 + $hutang3 + $hutang4 + $hutang5 + $hutang6, 2)); ?></td>
		</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="3" class="right bE bS bW">GRAND TOTAL : </td>
			<td class="right bE bS"><?php print(number_format($sums["dokumen"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_1"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_2"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_3"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_4"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_5"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["hutang_6"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["total"], 2)); ?></td>
		</tr>
	</table>

</div>
	<?php } ?>

</body>
</html>
