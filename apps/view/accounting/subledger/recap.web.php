<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $monthNames string[] */ /** @var $parentAccounts Coa[] */ /** @var $parentId int */
/** @var $month int */ /** @var $year int */ /** @var int $status */ /** @var string $statusName */
/** @var $report null|ReaderBase */
/** @var $projectId int */ /** @var $projectList Project[] */
?>
<head>
	<title>Rekasys - Sub Ledger</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

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
	<legend><span class="bold">Report Sub Ledger</span></legend>

	<form action="<?php print($helper->site_url("accounting.subledger/recap")); ?>" method="GET">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="right"><label for="ParentId">Jenis Akun : </label></td>
				<td colspan="3">
					<select id="ParentId" name="parentId">
                        <?php
						$selectedAccount = null;
						foreach ($parentAccounts as $account) {
							if ($account->Id == $parentId) {
								$selectedAccount = $account;
								printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
							} else {
								printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
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
				<td class="right"><label for="Month">Period : </label></td>
				<td>
					<select id="Month" name="month">
						<?php
						foreach ($monthNames as $idx => $name) {
							if ($idx == $month) {
								printf('<option value="%d" selected="selected">%s</option>', $idx, $name);
							} else {
								printf('<option value="%d">%s</option>', $idx, $name);
							}
						}
						?>
					</select>
					<select id="Year" name="year">
						<?php
						for ($i = date("Y"); $i >= 2010; $i--) {
							if ($i == $year) {
								printf('<option value="%d" selected="selected">%s</option>', $i, $i);
							} else {
								printf('<option value="%d">%s</option>', $i, $i);
							}
						}
						?>
					</select>
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
						<option value="web">Web Browser</option>
						<option value="pdf">PDF Format</option>
                        <option value="xls">Excel Format</option>
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
		<?php printf('Akun : %s - %s (Status: %s)', $selectedAccount->AccNo, $selectedAccount->AccName, $statusName); ?><br />
		Period : <?php printf("%s %s", $monthNames[$month], $year); ?>
        <?php
        if($selectedProject != null){printf('<br>Project : %s - %s', $selectedProject->ProjectCd, $selectedProject->ProjectName);}
        if($selectedDept != null){printf('<br>Department : %s - %s', $selectedDept->DeptCode, $selectedDept->DeptName);}
        if($selectedAct!= null){printf('<br>Activity : %s - %s', $selectedAct->ActCode, $selectedAct->ActName);}
        if($selectedUnit!= null){printf('<br>Unit : %s - %s', $selectedUnit->UnitCode, $selectedUnit->UnitName);}
        ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr class="bold center">
			<td rowspan="2" class="bN bE bS bW">Account</td>
			<td rowspan="2" class="bN bE bS">Account Name</td>
			<td rowspan="2" class="bN bE bS">Last Month</td>
			<td colspan="2" class="bN bE bS">Transaction <?php printf("%s %s", $monthNames[$month], $year); ?></td>
			<td rowspan="2" class="bN bE bS">Until This Month</td>
		</tr>
		<tr class="bold center">
			<td class="bE bS">Debit</td>
			<td class="bE bS">Credit</td>
		</tr>
		<?php
		$sumDebit = 0;
		$sumCredit = 0;
		$sumPrevSaldo = 0;
		$sumSaldo = 0;
		$startDate = mktime(0, 0, 0, $month, 1, $year);
		$endDate = mktime(0, 0, 0, $month + 1, 0, $year);
		$baseLink = sprintf("accounting.subledger/detail?account=%s&start=%s&end=%s&status=%d&output=web", "%d", date(SQL_DATEONLY, $startDate), date(SQL_DATEONLY, $endDate), $status);
		while($row = $report->FetchAssoc()) {
			$posisiSaldo = $row["dc_saldo"];
			$sumDebit += $row["total_debit"];
			$sumCredit += $row["total_credit"];

			if ($posisiSaldo == "D") {
				$prevSaldo = ($row["bal_debit_amt"] - $row["bal_credit_amt"]) + ($row["total_debit_prev"] - $row["total_credit_prev"]);
				$saldo = $row["total_debit"] - $row["total_credit"];
			} else  if($posisiSaldo == "K") {
				$prevSaldo = ($row["bal_credit_amt"] - $row["bal_debit_amt"]) + ($row["total_credit_prev"] - $row["total_debit_prev"]);
				$saldo = $row["total_credit"] - $row["total_debit"];
			} else {
				throw new Exception("Invalid dc_saldo! CODE: " . $posisiSaldo);
			}

			$sumPrevSaldo += $prevSaldo;
			$sumSaldo += $prevSaldo + $saldo;
			$link = $helper->site_url(sprintf($baseLink, $row["id"]));
		?>
		<tr>
			<td class="bE bW"><a href="<?php print($link); ?>"><?php print($row["acc_no"]); ?></a></td>
			<td class="bE"><a href="<?php print($link); ?>"><?php print($row["acc_name"]); ?></a></td>
			<td class="bE right"><?php print(number_format($prevSaldo, 2)); ?></td>
			<td class="bE right"><?php print(number_format($row["total_debit"], 2)); ?></td>
			<td class="bE right"><?php print(number_format($row["total_credit"], 2)); ?></td>
			<td class="bE right"><?php print(number_format($prevSaldo + $saldo, 2)); ?></td>
		</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="2" class="bN bE bS bW right">TOTAL :</td>
			<td class="bN bE bS right"><?php print(number_format($sumPrevSaldo, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumDebit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumCredit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumSaldo, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->

</body>
</html>
