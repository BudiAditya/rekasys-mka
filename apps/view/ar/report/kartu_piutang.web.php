<!DOCTYPE HTML>
<html>
<?php
/** @var $start int */ /** @var $end int */ /** @var $debtorId int */ /** @var $debtors Debtor[] */ /** @var $report null|ReaderBase */ /** @var $saldoAwal float */
?>
<head>
	<title>Rekasys - Report Kartu Piutang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<style type="text/css">
		.colCode { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colText { display: inline-block; width: 310px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.blue { color: blue; }
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/select2.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#start").customDatePicker();
			$("#end").customDatePicker();
			$("#debtor").select2({
				placeholderOption: "first",
				allowClear: false,
				minimumInputLength: 2,
				formatResult: formatOptionList,
				formatSelection: formatOptionResult
			});
		});

		function formatOptionList(state) {
			if (state.id == "") {
				return "-- PILIH DEBTOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode">' + originalOption.data("code") + '</div><div class="colText">' + originalOption.data("name") + '</div>';
		}

		function formatOptionResult(state) {
			if (state.id == "") {
				return "-- PILIH DEBTOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode bold blue">' + originalOption.data("code") + '</div><div class="colText bold blue">' + originalOption.data("name") + '</div>';
		}
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
	<legend><span class="bold">Report Kartu Piutang</span></legend>

	<form action="<?php print($helper->site_url("ar.report/kartu_piutang")); ?>" method="get">
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
				<td class="bold right"><label for="debtor">Debtor :</label></td>
				<td><select id="debtor" name="debtor" style="width: 450px;">
					<option value="">-- PILIH DEBTOR --</option>
					<?php
					$selectedDebtor = null;
					foreach ($debtors as $debtor) {
						if ($debtor->Id == $debtorId) {
							$selectedDebtor = $debtor;
							printf('<option value="%d" selected="selected" data-code="%s" data-name="%s">%s - %s</option>', $debtor->Id, $debtor->DebtorCd, $debtor->DebtorName, $debtor->DebtorCd, $debtor->DebtorName);
						} else {
							printf('<option value="%d" data-code="%s" data-name="%s">%s - %s</option>', $debtor->Id, $debtor->DebtorCd, $debtor->DebtorName, $debtor->DebtorCd, $debtor->DebtorName);
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
		Kartu Piutang: <?php printf("%s - %s", $selectedDebtor->DebtorCd, $selectedDebtor->DebtorName); ?>
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
		<tr class="bold right">
			<td colspan="4">Saldo Awal per tanggal <?php print(date(HUMAN_DATE, $start)); ?> : </td>
			<td><?php print($saldoAwal > 0 ? number_format($saldoAwal, 2) : "&nbsp;"); ?></td>
			<td><?php print($saldoAwal < 0 ? number_format($saldoAwal * -1, 2) : "&nbsp;"); ?></td>
			<td><?php print(number_format($saldoAwal, 2)); ?></td>
		</tr>
		<?php
		$counter = 0;
		$saldo = $saldoAwal;
		$prevDate = null;
		$sums = array(
			"debit" => 0
			, "credit" => 0
		);
		while ($row = $report->FetchAssoc()) {
			$date = strtotime($row["voucher_date"]);
			$debit = $row["debet"];
			$credit = $row["kredit"];
			if ($debit + $credit == 0) {
				continue;
			}
			$counter++;

			$sums["debit"] += $debit;
			$sums["credit"] += $credit;
			if ($prevDate != $date) {
				$prevDate = $date;
			} else {
				$date = null;
			}
			$saldo += $debit - $credit;
		?>
		<tr>
			<td><?php print($counter); ?></td>
			<td><?php print($date == null ? "&nbsp;" : date(HUMAN_DATE, $date)); ?></td>
			<td><a href="<?php print($helper->site_url("accounting.voucher/view/" . $row["id"])); ?>"><?php print($row["doc_no"]); ?></a></td>
			<td><?php print($row["note"]); ?></td>
			<td class="right"><?php print($debit != 0 ? number_format($debit, 2) : "&nbsp;"); ?></td>
			<td class="right"><?php print($credit != 0 ? number_format($credit, 2) : "&nbsp;"); ?></td>
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
