<!DOCTYPE HTML>
<html>
<?php
/** @var $accountId int */ /** @var $accounts array */ /** @var $start int */ /** @var $end int */ /** @var $openingBalance null|OpeningBalance */
/** @var $transaction null|array */ /** @var $report null|ReaderBase */ /** @var $output string */ /** @var $company Company */
$haveData = $openingBalance != null;
?>
<head>
	<title>Rekasys - Report Cash Flow</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#Start").customDatePicker().datepicker("show");
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
	<legend><span class="bold">Report Cash Flow - Detail</span></legend>
	<div class="center">
		<form action="<?php print($helper->site_url("accounting.cashflow/rpt_detail")); ?>" method="GET">
			<label for="Account">Pilih Akun : </label>
			<select id="Account" name="account">
				<option value="">-- PILIH AKUN --</option>
				<?php
				/** @var $selectedAccount Coa */
				$selectedAccount = null;
				foreach ($accounts as $row) {
					$account = $row["Parent"];
					printf('<optgroup label="%s - %s"></optgroup>', $account->AccNo, $account->AccName);
					/** @var $account Coa */
					foreach ($row["SubAccounts"] as $account) {
						if ($account->Id == $accountId) {
							$selectedAccount = $account;
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
				}
				?>
			</select>
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

			<button type="submit">Generate</button>
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
		CASH FLOW DETAIL<br />
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?><br />
		Akun: <?php printf("%s - %s", $selectedAccount->AccNo, $selectedAccount->AccName); ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr class="bold center">
			<td class="bN bE bS bW">Tgl</td>
			<td class="bN bE bS">No. Voucher</td>
			<td class="bN bE bS">Uraian</td>
			<td class="bN bE bS">Project</td>
            <td class="bN bE bS">Dept</td>
            <td class="bN bE bS">Act</td>
            <td class="bN bE bS">Unit</td>
			<td class="bN bE bS">Debet</td>
			<td class="bN bE bS">Kredit</td>
			<td class="bN bE bS">Saldo</td>
		</tr>
		<tr>
			<td class="bE bS bW"><?php print(date("d", $start)); ?></td>
			<td class="bE bS">&nbsp;</td>
			<td class="bE bS">Saldo Awal <?php print(date(HUMAN_DATE, $start)); ?></td>
			<td class="bE bS">&nbsp;</td>
            <td class="bE bS">&nbsp;</td>
            <td class="bE bS">&nbsp;</td>
            <td class="bE bS">&nbsp;</td>
			<td class="bE bS right"><?php print(number_format(($haveData && $openingBalance->AccountDcSaldo == "D") ? $transaction["saldo"] : 0, 2)) ?></td>
			<td class="bE bS right"><?php print(number_format(($haveData && $openingBalance->AccountDcSaldo == "K") ? $transaction["saldo"] : 0, 2)) ?></td>
			<td class="bE bS right"><?php print(number_format($haveData ? $transaction["saldo"] : 0, 2)) ?></td>
		</tr>
		<?php
		$counter = 0;
		$prevDate = null;
		$prevVoucherNo = null;

		$flagDate = true;
		$flagVoucherNo = true;
		$flagSbu = true;
		$saldo = $haveData ? $transaction["saldo"] : 0;

		$subTotalDebit = 0;
		$subTotalCredit = 0;
		$totalDebit = 0;
		$totalCredit = 0;
		while ($row = $report->FetchAssoc()) {
			// Convert datetime jadi native format
			$row["voucher_date"] = strtotime($row["voucher_date"]);
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			if ($prevDate != $row["voucher_date"]) {
				if ($prevDate != null) {
					// OK sudah ganti baris kita harus bikin subTotal dahulu
					printf('<tr class="bold right"><td colspan="7" class="bE bS bW">Sub Total %s:</td><td class="bE bS">%s</td><td class="bE bS">%s</td><td class="bE bS">&nbsp;</td></tr>', date(HUMAN_DATE, $prevDate), number_format($subTotalDebit, 2), number_format($subTotalCredit, 2));
					$totalDebit += $subTotalDebit;
					$totalCredit += $subTotalCredit;
					$subTotalDebit = 0;
					$subTotalCredit = 0;
				}
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

			$debit = $row["acc_debit_id"] == $accountId ? $row["amount"] : 0;
			$credit = $row["acc_credit_id"] == $accountId ? $row["amount"] : 0;
			$saldo = $saldo + $debit - $credit;
			$subTotalDebit += $debit;
			$subTotalCredit += $credit;
			?>
			<tr class="<?php print($className); ?>">
				<td class="bE bS bW"><?php print($flagDate ? date("d", $prevDate) : "&nbsp;"); ?></td>
				<td class="bE bS"><?php print($flagVoucherNo ? $prevVoucherNo : "&nbsp;"); ?></td>
				<td class="bE bS"><?php print($row["note"]); ?></td>
				<td class="bE bS"><?php print($row["project_cd"]); ?></td>
                <td class="bE bS"><?php print($row["dept_code"]); ?></td>
                <td class="bE bS"><?php print($row["act_code"]); ?></td>
                <td class="bE bS"><?php print($row["unit_code"]); ?></td>
				<td class="bE bS right"><?php print(number_format($debit, 2)); ?></td>
				<td class="bE bS right"><?php print(number_format($credit, 2)); ?></td>
				<td class="bE bS right"><?php print(number_format($saldo, 2)); ?></td>
			</tr>
		<?php
		}
		// Baris terakhir yang terlupakan
		if ($prevDate != null) {
			// OK sudah ganti baris kita harus bikin subTotal dahulu
			printf('<tr class="bold right"><td colspan="7" class="bE bS bW">Sub Total %s:</td><td class="bE bS">%s</td><td class="bE bS">%s</td><td class="bE bS">&nbsp;</td></tr>', date(HUMAN_DATE, $prevDate), number_format($subTotalDebit, 2), number_format($subTotalCredit, 2));
			$totalDebit += $subTotalDebit;
			$totalCredit += $subTotalCredit;
			$subTotalDebit = 0;
			$subTotalCredit = 0;
		}
		?>
		<tr class="bold right">
			<td colspan="7" class="bE bS bW">GRAND TOTAL :</td>
			<td class="bE bS"><?php print(number_format($totalDebit, 2)); ?></td>
			<td class="bE bS"><?php print(number_format($totalCredit, 2)); ?></td>
			<td class="bE bS"><?php print(number_format($saldo, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


</body>
</html>
