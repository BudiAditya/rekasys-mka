<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php
/** @var $start int */ /** @var $end int */
/** @var $employeeId int */ /** @var $employees Employee[] */
/** @var $report null|ReaderBase */ /** @var $saldoAwal float */
?>
<head>
	<title>Mega PMS - Report Kartu Piutang Karyawan</title>
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
	<legend><span class="bold">Report Kartu Piutang Karyawan</span></legend>

	<form action="<?php print($helper->site_url("hr.report/kartu_piutang")); ?>" method="get">
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
				<td class="bold right"><label for="debtor">Karyawan :</label></td>
				<td><select id="employee" name="employee">
						<?php
						$selectedEmployee = null;
						foreach ($employees as $employee) {
							if ($employee->Id == $employeeId) {
								$selectedEmployee = $employee;
								printf('<option value="%d" selected="selected">%s</option>', $employee->Id, $employee->Name);
							} else {
								printf('<option value="%d">%s</option>', $employee->Id, $employee->Name);
							}
						}
						?>
					</select></td>
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
		Kartu Piutang: <?php printf("%s - %s", $selectedEmployee->Name, $selectedEmployee->Nik); ?>
	</div>
	<div class="center bold subTitle">
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0 auto;">
		<tr class="bold center">
			<td>No.</td>
			<td>Tgl. Dokumen</td>
			<td>No. Dokumen</td>
			<td>Keterangan</td>
			<td>Debet</td>
			<td>Kredit</td>
			<td>Saldo</td>
		</tr>
		<tr class="bold">
			<td colspan="4" class="right">Saldo Awal per tanggal <?php print(date(HUMAN_DATE, $start)); ?> : </td>
			<td class="right"><?php print($saldoAwal > 0 ? number_format($saldoAwal, 2) : "&nbsp;"); ?></td>
			<td class="right"><?php print($saldoAwal < 0 ? number_format($saldoAwal * -1, 2) : "&nbsp;"); ?></td>
			<td class="right"><?php print(number_format($saldoAwal, 2)); ?></td>
		</tr>
		<?php
		$counter = 0;
		$saldo = $saldoAwal;
		$prevDate = null;
		$sums = array(
			"debit" => 0,
			"credit" => 0
		);
		// Ganti logic karena doc_type tidak bisa jadi pegangan untuk penentu debet atau kredit
		// Ada dokumen AJ yang menggunakan akun piutang karyawan
		// Akun Piutang Karyawan = 110.04.02.00 (ID = 78)
		while ($row = $report->FetchAssoc()) {
			$counter++;
			$date = strtotime($row["voucher_date"]);
			if ($prevDate != $date) {
				$prevDate = $date;
			} else {
				$date = null;
			}
			if ($row["acc_debit_id"] == 78) {
				$debit = number_format($row["amount"], 2);
				$credit = "&nbsp;";
				$saldo += $row["amount"];
				$sums["debit"] += $row["amount"];
			} else if ($row["acc_credit_id"] == 78) {
				$debit = "&nbsp;";
				$credit = number_format($row["amount"], 2);
				$saldo -= $row["amount"];
				$sums["credit"] += $row["amount"];
			} else {
				$link = "#";
				$debit = "&nbsp;";
				$credit = "&nbsp;";
			}
			?>
			<tr>
				<td><?php print($counter); ?></td>
				<td><?php print($date == null ? "&nbsp;" : date(HUMAN_DATE, $date)); ?></td>
				<td>
					<a href="<?php print($helper->site_url("accounting.voucher/view/" . $row["id"])); ?>"><?php print($row["doc_no"]); ?></a>
				</td>
				<td><?php print($row["note"]); ?></td>
				<td class="right"><?php print($debit); ?></td>
				<td class="right"><?php print($credit); ?></td>
				<td class="right"><?php print(number_format($saldo, 2)); ?></td>
			</tr>
		<?php } ?>
		<tr class="bold right">
			<td colspan="4">TOTAL : </td>
			<td><?php print(number_format($sums["debit"], 2)); ?></td>
			<td><?php print(number_format($sums["credit"], 2)); ?></td>
			<td><?php print(number_format($saldo, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>

</body>
</html>
