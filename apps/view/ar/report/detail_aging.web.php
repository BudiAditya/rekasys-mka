<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Detail Aging Piutang Debtor</title>
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
<?php /** @var $debtorId null|int */ /** @var $debtors Debtor[] */ /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */  ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Detail Aging Piutang Debtor</span></legend>

	<form action="<?php print($helper->site_url("ar.report/detail_aging")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="date">Per Tanggal :</label></td>
				<td>
					<input type="text" id="date" name="date" value="<?php print(date(JS_DATE, $date)); ?>" size="12" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="debtor">Debtor :</label></td>
				<td><select id="debtor" name="debtor">
					<option value="">-- SEMUA DEBTOR --</option>
					<?php
					$selectedDebtor = null;
					foreach ($debtors as $debtor) {
						if ($debtor->Id == $debtorId) {
							$selectedDebtor = $debtor;
							printf('<option value="%d" selected="selected">%s - %s</option>', $debtor->Id, $debtor->DebtorCd, $debtor->DebtorName);
						} else {
							printf('<option value="%d">%s - %s</option>', $debtor->Id, $debtor->DebtorCd, $debtor->DebtorName);
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
		Detail Aging Piutang Debtor Company : <?php printf('%s - %s', $company->EntityCd, $company->CompanyName); ?>
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
			<td class="bN bE bS">Nilai Dokumen</td>
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
			, "piutang_1" => 0
			, "piutang_2" => 0
			, "piutang_3" => 0
			, "piutang_4" => 0
			, "piutang_5" => 0
			, "piutang_6" => 0
			, "total" => 0
		);
		$prevDebtorId = null;
		while ($row = $report->FetchAssoc()) {
			$counter++;
			$amount = $row["total_amount"];
			$sums["dokumen"] += $amount;
			$age = $row["age"];
			$date = strtotime($row["invoice_date"]);

			// Reset variable
			$piutang1 = 0;
			$piutang2 = 0;
			$piutang3 = 0;
			$piutang4 = 0;
			$piutang5 = 0;
			$piutang6 = 0;
			$piutang = $amount - $row["sum_paid"];

			if ($age <= 0) {
				// Nothing to do... data ini di skip tapi masih ditampilkan walau 0 semua
			} else if ($age <= 30) {
				$piutang1 = $piutang;
				$sums["piutang_1"] += $piutang;
				$sums["total"] += $piutang;
			} else if ($age <= 60) {
				$piutang2 = $piutang;
				$sums["piutang_2"] += $piutang;
				$sums["total"] += $piutang;
			} else if ($age <= 90) {
				$piutang3 = $piutang;
				$sums["piutang_3"] += $piutang;
				$sums["total"] += $piutang;
			} else if ($age <= 120) {
				$piutang4 = $piutang;
				$sums["piutang_4"] += $piutang;
				$sums["total"] += $piutang;
			} else if ($age <= 150) {
				$piutang5 = $piutang;
				$sums["piutang_5"] += $piutang;
				$sums["total"] += $piutang;
			} else {
				$piutang6 = $piutang;
				$sums["piutang_6"] += $piutang;
				$sums["total"] += $piutang;
			}

			// Header untuk debtor..
			if ($prevDebtorId != $row["debtor_id"]) {
				// Counter nomor ketika ganti debtor ter-reset
				$counter = 1;
				$prevDebtorId = $row["debtor_id"];
				printf("<tr class='bold'><td class='right bE bS bW' colspan='3'>Kode Debtor: %s</td><td class='bE bS' colspan='8'>%s</td></tr>\n", $row["debtor_cd"], $row["debtor_name"]);
			}
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
		?>
		<tr class="<?php print($className); ?>">
			<td class="right bE bS bW"><?php print($counter); ?>.</td>
			<td class="bE bS"><a href="<?php print($helper->site_url("ar.invoice/view/" . $row["id"])); ?>"><?php print($row["invoice_no"]); ?></a></td>
			<td class="bE bS"><?php print(date(HUMAN_DATE, $date)); ?></td>
			<td class="right bE bS"><?php print(number_format($amount, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang1, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang2, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang3, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang4, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang5, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang6, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($piutang1 + $piutang2 + $piutang3 + $piutang4 + $piutang5 + $piutang6, 2)); ?></td>
		</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="3" class="right bE bS bW">GRAND TOTAL : </td>
			<td class="right bE bS"><?php print(number_format($sums["dokumen"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["piutang_1"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["piutang_2"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["piutang_3"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["piutang_4"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["piutang_5"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["piutang_6"], 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($sums["total"], 2)); ?></td>
		</tr>
	</table>

</div>
	<?php } ?>

</body>
</html>
