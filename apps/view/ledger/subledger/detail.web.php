<!DOCTYPE HTML>
<html>
<?php
/** @var $accountId int */ /** @var $accounts array */ /** @var $start int */ /** @var $end int */ /** @var $openingBalance null|OpeningBalance */
/** @var int $status */ /** @var string $statusName */ /** @var $projectId int */ /** @var $projectList Project[] */
/** @var $transaction null|array */ /** @var $report null|ReaderBase */ /** @var $output string */ /** @var $company Company */
$haveData = $openingBalance != null;
?>
<head>
	<title>Rekasys - Report Detail Sub Ledger</title>
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
	<legend><span class="bold">Report Detail Sub Ledger</span></legend>

	<form action="<?php print($helper->site_url("accounting.subledger/detail")); ?>" method="GET">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="right"><label for="Account">Account : </label></td>
				<td colspan="7">
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
				</td>
			</tr>
            <tr>
                <td class="right"><label for="Project">Project : </label></td>
                <td>
                    <select id="Project" name="projectId">
                        <option value="0">-- Not Filtered --</option>
                        <?php
                        $selectedProject = null;
                        foreach ($projectList as $project) {
                            if($project->Id == $projectId){
                                $selectedProject = $project;
                                printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }else{
                                printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="right"><label for="DeptId">Dept : </label></td>
                <td>
                    <select id="DeptId" name="deptId" style="width:150px">
                        <option value="0">-- Not Filtered --</option>
                        <?php
                        /** @var $deptList Department[] */
                        $selectedDept = null;
                        foreach ($deptList as $dept) {
                            if($dept->Id == $deptId){
                                $selectedDept = $dept;
                                printf('<option value="%d" selected="selected">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                            }else{
                                printf('<option value="%d">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="right"><label for="ActId">Activity : </label></td>
                <td>
                    <select id="ActId" name="actId" style="width:150px">
                        <option value="0">-- Not Filtered --</option>
                        <?php
                        /** @var $actList Activity[] */
                        $selectedAct = null;
                        foreach ($actList as $act) {
                            if($act->Id == $actId){
                                $selectedAct = $act;
                                printf('<option value="%d" selected="selected">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                            }else{
                                printf('<option value="%d">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="right"><label for="UnitId">Unit : </label></td>
                <td>
                    <select id="UnitId" name="unitId" style="width:150px">
                        <option value="0">-- Not Filtered --</option>
                        <?php
                        /** @var $unitList Units[] */
                        $selectedUnit = null;
                        foreach ($unitList as $unit) {
                            if($unit->Id == $unitId){
                                $selectedUnit = $unit;
                                printf('<option value="%d" selected="selected">%s - %s</option>', $unit->Id, $unit->UnitCode, $unit->UnitName);
                            }else{
                                printf('<option value="%d">%s - %s</option>', $unit->Id, $unit->UnitCode, $unit->UnitName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="right"><label for="Start">Period : </label></td>
				<td colspan="3">
					<input type="text" id="Start" name="start" size="12" value="<?php print(date(JS_DATE, $start)) ?>" />
					<label for="End"> to </label>
					<input type="text" id="End" name="end" size="12" value="<?php print(date(JS_DATE, $end)) ?>" />
				</td>
				<td class="right"><label for="DocStatus">Doc Status :</label></td>
				<td>
					<select id="DocStatus" name="status">
						<option value="-1" <?php print($status == -1 ? 'selected="selected"' : ''); ?>>SEMUA DOKUMEN</option>
						<option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>BELUM APPROVED</option>
						<option value="2" <?php print($status == 2 ? 'selected="selected"' : ''); ?>>SUDAH APPROVED</option>
						<option value="3" <?php print($status == 3 ? 'selected="selected"' : ''); ?>>VERIFIED</option>
						<option value="4" <?php print($status == 4 ? 'selected="selected"' : ''); ?>>POSTED</option>
					</select>
				</td>
				<td class="right"><label for="Output">Output : </label></td>
				<td>
					<select id="Output" name="output">
						<option value="web" <?php print($output == "web" ? 'selected="selected"' : '') ?>>Web Browser</option>
						<option value="pdf" <?php print($output == "pdf" ? 'selected="selected"' : '') ?>>PDF</option>
						<option value="xls" <?php print($output == "xls" ? 'selected="selected"' : '') ?>>Excel</option>
					</select>
                    &nbsp;
				    <button type="submit">Generate</button>
                </td>
			</tr>
		</table>
	</form>

</fieldset>


<!-- REGION: LAPORAN -->
<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="title bold">
		<?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?><br />
	</div>
	<div class="subTitle">
		Sub Ledger Detail<br />
		<?php printf("Period : %s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?><br />
		<?php printf("Account : %s - %s (Status: %s)", $selectedAccount->AccNo, $selectedAccount->AccName, $statusName); ?>
        <?php
        if($selectedProject != null){printf('<br>Project : %s - %s', $selectedProject->ProjectCd, $selectedProject->ProjectName);}
        if($selectedDept != null){printf('<br>Department : %s - %s', $selectedDept->DeptCode, $selectedDept->DeptName);}
        if($selectedAct!= null){printf('<br>Activity : %s - %s', $selectedAct->ActCode, $selectedAct->ActName);}
        if($selectedUnit!= null){printf('<br>Unit : %s - %s', $selectedUnit->UnitCode, $selectedUnit->UnitName);}
        ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
		<tr class="bold center">
			<td>Trx Date</td>
			<td>Doc Number</td>
			<td>Description</td>
			<td>Debit</td>
			<td>Credit</td>
		</tr>
		<tr>
			<td><?php print(date("d-m-Y", $start)); ?></td>
			<td>&nbsp;</td>
			<td>Opening Balance <?php print(date(HUMAN_DATE, $start)); ?></td>
			<td class="right"><?php print(number_format(($haveData && $openingBalance->GetCoa()->DcSaldo == "D") ? $transaction["saldo"] : 0, 2)) ?></td>
			<td class="right"><?php print(number_format(($haveData && $openingBalance->GetCoa()->DcSaldo == "K") ? $transaction["saldo"] : 0, 2)) ?></td>
		</tr>
		<?php
		$counter = 0;
		$prevDate = null;
		$prevVoucherNo = null;

		$flagDate = true;
		$flagVoucherNo = true;

		$totalDebit = ($haveData && $openingBalance->GetCoa()->DcSaldo == "D") ? $transaction["saldo"] : 0;
		$totalCredit = ($haveData && $openingBalance->GetCoa()->DcSaldo == "K") ? $transaction["saldo"] : 0;
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
				$link = $helper->site_url("accounting.voucher/view/" . $row["id"]);
				$anchor = '<a href="' . $link . '">' . $prevVoucherNo . '</a>';
			} else {
				$flagVoucherNo = false;
				$anchor = null;
			}

			$debit = $row["acc_debit_id"] == $accountId ? $row["amount"] : 0;
			$credit = $row["acc_credit_id"] == $accountId ? $row["amount"] : 0;
			$totalDebit += $debit;
			$totalCredit += $credit;
		?>
		<tr class="<?php print($className); ?>">
			<td><?php print($flagDate ? date("d-m-Y", $prevDate) : "&nbsp;"); ?></td>
			<td><?php print($flagVoucherNo ? $anchor : "&nbsp;"); ?></td>
			<td><?php print($row["note"]); ?></td>
			<td class="right"><?php print(number_format($debit, 2)); ?></td>
			<td class="right"><?php print(number_format($credit, 2)); ?></td>
		</tr>
		<?php }	?>
		<tr class="bold right">
			<td colspan="3">GRAND TOTAL :</td>
			<td><?php print(number_format($totalDebit, 2)); ?></td>
			<td><?php print(number_format($totalCredit, 2)); ?></td>
		</tr>
		<tr class="bold right">
			<td colspan="3">SALDO AKHIR :</td>
			<td><?php print($selectedAccount->DcSaldo == "D" ? number_format($totalDebit - $totalCredit, 2) : "&nbsp;"); ?></td>
			<td><?php print($selectedAccount->DcSaldo == "K" ? number_format($totalCredit - $totalDebit, 2) : "&nbsp;"); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


</body>
</html>
