<!DOCTYPE HTML>
<html>
<?php
/** @var $start int */ /** @var $end int */ /** @var $showNo bool */ /** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */
?>
<head>
	<title>Rekasys - Report Cash/Bank Out</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#Start").customDatePicker();
			$("#End").customDatePicker();
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
	<legend><span class="bold">Report Cash/Bank Out</span></legend>
	<div class="center">
		<form action="<?php print($helper->site_url("accounting.cashbookreport/rpt_out")); ?>" method="GET">
			<label for="Start">Periode : </label>
			<input type="text" id="Start" name="start" value="<?php print(date(JS_DATE, $start)) ?>" />
			<label for="End"> s.d. </label>
			<input type="text" id="End" name="end" value="<?php print(date(JS_DATE, $end)) ?>" />
			<label for="Output">Output : </label>
			<select id="Output" name="output">
				<option value="web" <?php print($output == "web" ? 'selected="selected"' : '') ?>>Web Browser</option>
				<option value="pdf" <?php print($output == "pdf" ? 'selected="selected"' : '') ?>>PDF</option>
                <option value="xls" <?php print($output == "xls" ? 'selected="selected"' : '') ?>>Excel</option>
			</select>

			<button type="submit">Generate</button><br /><br />
			<input type="checkbox" id="ShowNo" name="showNo" value="1" <?php print($showNo ? 'checked="checked"' : '') ?> />
			<label for="ShowNo">Tambilkan No Akun bukan nama akun.</label>
		</form>
	</div>
</fieldset>


<!-- REGION: LAPORAN -->
<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="title bold">
		<?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?><br />
	</div>
	<div class="subTitle">
		JURNAL BUKTI CASH/BANK OUT<br />
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr class="bold center">
			<td rowspan="2" class="bN bE bS bW">Tgl</td>
			<td rowspan="2" class="bN bE bS">No. Voucher</td>
			<td rowspan="2" class="bN bE bS">Company</td>
			<td rowspan="2" class="bN bE bS">Uraian</td>
			<td colspan="2" class="bN bE bS">Debet</td>
			<td colspan="2" class="bN bE bS">Kredit</td>
		</tr>
		<tr class="bold center">
			<td class="bE bS">Akun</td>
			<td class="bE bS">Jumlah</td>
			<td class="bE bS">Akun</td>
			<td class="bE bS">Jumlah</td>
		</tr>
		<?php
		$counter = 0;
		$prevDate = null;
		$prevVoucherNo = null;
		$prevSbu = null;

		$flagDate = true;
		$flagVoucherNo = true;
		$flagSbu = true;
		while ($row = $report->FetchAssoc()) {
			// Convert datetime jadi native format
			$row["voucher_date"] = strtotime($row["voucher_date"]);
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			if ($prevDate != $row["voucher_date"]) {
				$prevDate = $row["voucher_date"];
				$flagDate = true;
			} else {
				$flagDate = false;
			}

			if ($prevVoucherNo != $row["doc_no"]) {
				$prevVoucherNo = $row["doc_no"];
				$flagVoucherNo = true;
			} else {
				$flagVoucherNo = false;
			}

			if ($prevSbu != $row["entity_cd"]) {
				$prevSbu = $row["entity_cd"];
				$flagSbu = true;
			} else {
				$flagSbu = false;
			}

			if ($flagVoucherNo) {
				$link = sprintf('<a href="%s">%s</a>', $helper->site_url("accounting.voucher/view/" . $row["id"]), $prevVoucherNo);
			} else {
				$link = "&nbsp;";
			}
		?>
		<tr class="<?php print($className); ?>">
			<td class="bE bS bW"><?php print($flagDate ? date("d", $prevDate) : "&nbsp;"); ?></td>
			<td class="bE bS"><?php print($link); ?></td>
			<td class="bE bS"><?php print($flagSbu ? $prevSbu : "&nbsp;"); ?></td>
			<td class="bE bS"><?php print($row["note"]); ?></td>
			<td class="bE bS"><?php print($showNo ? $row["acc_no_debit"] : $row["acc_debit"]); ?></td>
			<td class="bE bS right"><?php print(number_format($row["amount"], 2)); ?></td>
			<td class="bE bS"><?php print($showNo ? $row["acc_no_credit"] : $row["acc_credit"]); ?></td>
			<td class="bE bS right"><?php print(number_format($row["amount"], 2)); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


</body>
</html>
