<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php
/** @var $start int */ /** @var $end int */ /** @var $company Company */ /** @var $report null|ReaderBase */
?>
<head>
	<title>Mega PMS - Rekap Piutang Karyawan</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#start").customDatePicker();
			$("#end").customDatePicker();
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
	<legend><span class="bold">Rekap Piutang Karyawan</span></legend>

	<form action="<?php print($helper->site_url("hr.report/rekap_piutang")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="start">Periode :</label></td>
				<td>
					<input type="text" id="start" name="start" value="<?php print(date(JS_DATE, $start)); ?>" size="12" />
					<label for="end" class="bold"> s.d. </label>
					<input type="text" id="end" name="end" value="<?php print(date(JS_DATE, $end)); ?>" size="12" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="output">Format Report :</label></td>
				<td><select id="output" name="output">
						<option value="web">Web Browser</option>
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
		Rekap Piutang Karyawan: <?php printf('%s - %s', $company->EntityCd, $company->CompanyName); ?>
	</div>
	<div class="center bold subTitle">
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorderSlim" style="margin: 0 auto;">
		<tr class="bold center">
			<td>No.</td>
			<td>Nama Karyawan</td>
			<td>NIK</td>
			<td>Company</td>
			<td>Saldo Awal</td>
			<td>Debet</td>
			<td>Kredit</td>
			<td>Sisa</td>
			<td>Detail</td>
		</tr>
		<?php
		$counter = 0;
		$sums = array(
			"saldoAwal" => 0,
			"debet" => 0,
			"kredit" => 0
		);
		$startDate = date(SQL_DATEONLY, $start);
		$endDate = date(SQL_DATEONLY, $end);

		while ($row = $report->FetchAssoc()) {
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			$saldoAwal = $row["opening_balance"] + $row["prev_debit"] - $row["prev_credit"];
			$debet = $row["current_debit"];
			$kredit = $row["current_credit"];

			$sums["saldoAwal"] += $saldoAwal;
			$sums["debet"] += $debet;
			$sums["kredit"] += $kredit;

			$link = $helper->site_url(sprintf("hr.report/kartu_piutang?employee=%d&start=%s&end=%s&output=web", $row["e_id"], $startDate, $endDate));
			?>
			<tr class="<?php print($className); ?>">
				<td class="right"><?php print($counter); ?>.</td>
				<td><?php print($row["e_name"]); ?></td>
				<td><?php print($row["e_nik"]); ?></td>
				<td><?php print($row["entity_cd"]); ?></td>
				<td class="right"><?php print(number_format($saldoAwal, 2)); ?></td>
				<td class="right"><?php print(number_format($debet, 2)); ?></td>
				<td class="right"><?php print(number_format($kredit, 2)); ?></td>
				<td class="right"><?php print(number_format($saldoAwal + $debet - $kredit, 2)); ?></td>
				<td class="center"><a href="<?php print($link); ?>">Kartu Piutang</a></td>
			</tr>
		<?php } ?>
		<tr class="bold right forceN forceS">
			<td colspan="4">TOTAL :</td>
			<td><?php print(number_format($sums["saldoAwal"], 2)); ?></td>
			<td><?php print(number_format($sums["debet"], 2)); ?></td>
			<td><?php print(number_format($sums["kredit"], 2)); ?></td>
			<td><?php print(number_format($sums["saldoAwal"] + $sums["debet"] - $sums["kredit"], 2)); ?></td>
			<td>&nbsp;</td>
		</tr>
	</table>

</div>
<?php } ?>

</body>
</html>
