<!DOCTYPE HTML>
<html>
<?php /** @var $company Company */ /** @var $monthNames string[] */  /** @var $month int */ /** @var $year int */ /** @var $report null|ReaderBase */ ?>
<head>
	<title>Rekasys - Trial Balance</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
    <style type="text/css">
        .level0 {
            font-weight: bold;
            font-size: 1.2em;
            color:red;
        }
        .level1 {
            font-weight: bold;
            color: brown;
        }
        .level2 {
            font-weight: bold;
            color: darkblue;
        }
    </style>

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
	<legend><span class="bold">Report Trial Balance</span></legend>

	<div class="center">
		<form action="<?php print($helper->site_url("accounting.trialbalance/recap")); ?>" method="GET">
			<label for="Month">Month : </label>
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
			<label for="Year">Year : </label>
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
            <label for="IsIncludeOpening">Include Opening : </label>
            <select id="IsIncludeOpening" name="isIncOb">
                <option value="1" <?php if($isIncOb == 1){print("selected='selected'");}?>>Yes</option>
                <option value="0" <?php if($isIncOb == 0){print("selected='selected'");}?>>No</option>
            </select>
            <label for="IsIncludeHeader">Display Header : </label>
            <select id="IsIncludeHeader" name="isIncHd">
                <option value="0" <?php if($isIncHd == 0){print("selected='selected'");}?>>No</option>
                <option value="1" <?php if($isIncHd == 1){print("selected='selected'");}?>>Yes</option>
            </select>
			<label for="Output">Output : </label>
			<select id="Output" name="output">
				<option value="web">Web Browser</option>
				<option value="xls">MS Excel</option>
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
		<?php print($company->CompanyName); ?><br />
	</div>
	<div class="subTitle">
		<?php printf("<u>Periode : %s %s</u>", $monthNames[$month], $year); ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr class="bold center">
			<td rowspan="2" class="bN bE bS bW">Account</td>
			<td rowspan="2" class="bN bE bS">Account Name</td>
			<td colspan="2" class="bN bE bS">Opening Balance</td>
			<td colspan="2" class="bN bE bS">Mutation <?php printf("%s %s", $monthNames[$month], $year); ?></td>
			<td colspan="2" class="bN bE bS">Closing Balance</td>
		</tr>
		<tr class="bold center">
			<td class="bE bS">Debit</td>
			<td class="bE bS">Credit</td>
            <td class="bE bS">Debit</td>
            <td class="bE bS">Credit</td>
            <td class="bE bS">Debit</td>
            <td class="bE bS">Credit</td>
		</tr>

		<?php
        $data = array();
        while ($row = $report->FetchAssoc()) {
            $data[] = $row;
        }
        $sumDebitTrx = 0;
        $sumCreditTrx = 0;
        $sumDebitObl = 0;
        $sumCreditObl = 0;
        $sumDebitCbl = 0;
        $sumCreditCbl = 0;
		//while($row = $report->FetchAssoc()) {
        foreach ($data as $row) {
            $cblDebit = 0;
            $cblCredit = 0;
            $oblDebit = 0;
            $oblCredit = 0;
            $trxDebit = 0;
            $trxCredit = 0;
            $className = "";

            if ($row["acc_level"] == 0) {
                $className = "level0";
                foreach ($data as $temp) {
                    if ($temp["level0"] == $row["id"]) {
                        if ($row["dc_saldo"] == 'D') {
                            $oblDebit += ($temp["bal_debit_amt"] + $temp["total_debit_prev"]) - $temp["total_credit_prev"];
                        }else {
                            $oblCredit += ($temp["bal_credit_amt"] + $temp["total_credit_prev"]) - $temp["total_debit_prev"];
                        }
                        $trxDebit += $temp["total_debit"];
                        $trxCredit += $temp["total_credit"];
                    }
                }
            } else if ($row["acc_level"] == 1) {
                $className = "level1";
                foreach ($data as $temp) {
                    if ($temp["level1"] == $row["id"]) {
                        if ($row["dc_saldo"] == 'D') {
                            $oblDebit += ($temp["bal_debit_amt"] + $temp["total_debit_prev"]) - $temp["total_credit_prev"];
                        }else {
                            $oblCredit += ($temp["bal_credit_amt"] + $temp["total_credit_prev"]) - $temp["total_debit_prev"];
                        }
                        $trxDebit += $temp["total_debit"];
                        $trxCredit += $temp["total_credit"];
                    }
                }
            } else if ($row["acc_level"] == 2) {
                $className = "level2";
                foreach ($data as $temp) {
                    if ($temp["level2"] == $row["id"]) {
                        if ($row["dc_saldo"] == 'D') {
                            $oblDebit += ($temp["bal_debit_amt"] + $temp["total_debit_prev"]) - $temp["total_credit_prev"];
                        }else {
                            $oblCredit += ($temp["bal_credit_amt"] + $temp["total_credit_prev"]) - $temp["total_debit_prev"];
                        }
                        $trxDebit += $temp["total_debit"];
                        $trxCredit += $temp["total_credit"];
                    }
                }
            } else {
                $className = "level3";
                if ($row["dc_saldo"] == 'D') {
                    $oblDebit = ($row["bal_debit_amt"] + $row["total_debit_prev"]) - $row["total_credit_prev"];
                }else{
                    $oblCredit = ($row["bal_credit_amt"] + $row["total_credit_prev"]) - $row["total_debit_prev"];
                }
                $trxDebit += $row["total_debit"];
                $trxCredit += $row["total_credit"];
                $sumDebitTrx += $trxDebit;
                $sumCreditTrx += $trxCredit;
                if($isIncOb == 1){
                    $sumDebitObl += $oblDebit;
                    $sumCreditObl += $oblCredit;
                    if ($row["dc_saldo"] == 'D') {
                        $sumDebitCbl += $trxDebit + $oblDebit - $trxCredit;
                    }else{
                        $sumCreditCbl += $trxCredit + $oblCredit - $trxDebit;
                    }
                }else{
                    if ($row["dc_saldo"] == 'D') {
                        $sumDebitCbl += $trxDebit - $trxCredit;
                    }else{
                        $sumCreditCbl += $trxCredit - $trxDebit;
                    }
                }
            }
            if($isIncOb){
                $cblDebit = $oblDebit + $trxDebit;
                $cblCredit = $oblCredit + $trxCredit;
            }else{
                $cblDebit = $trxDebit;
                $cblCredit = $trxCredit;
            }
            if($isIncHd == 0 && $row["acc_level"]< 3 || ($cblDebit == 0 && $cblCredit == 0)){
              // jangan tampilkan
            }else{
        ?>
		<tr class="<?php print($className); ?>">
			<td class="bE bW"><?php print($row["acc_no"]); ?></td>
			<td class="bE"><?php print($row["acc_name"]); ?></td>
			<td class="bE right"><?php print(number_format($isIncOb ? $oblDebit : 0, 2)); ?></td>
            <td class="bE right"><?php print(number_format($isIncOb ? $oblCredit : 0, 2)); ?></td>
			<td class="bE right"><?php print(number_format($trxDebit, 2)); ?></td>
			<td class="bE right"><?php print(number_format($trxCredit, 2)); ?></td>
            <?php
            if ($row["dc_saldo"] == 'D'){ ?>
                <td class="bE right"><?php print(number_format($isIncOb ? $oblDebit + $trxDebit - $trxCredit : $trxDebit - $trxCredit, 2)); ?></td>
                <td class="bE right"><?php print(number_format(0, 2)); ?></td>
            <?php }else{ ?>
                <td class="bE right"><?php print(number_format(0, 2)); ?></td>
                <td class="bE right"><?php print(number_format($isIncOb ? $oblCredit + $trxCredit - $trxDebit : $trxCredit - $trxDebit, 2)); ?></td>
            <?php } ?>
		</tr>
		<?php }}
        ?>
		<tr class="bold">
			<td colspan="2" class="bN bE bS bW right">TOTAL :</td>
			<td class="bN bE bS right"><?php print(number_format($isIncOb ? $sumDebitObl : 0, 2)); ?></td>
            <td class="bN bE bS right"><?php print(number_format($isIncOb ? $sumCreditObl : 0, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumDebitTrx, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumCreditTrx, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($isIncOb ? $sumDebitCbl : $sumDebitTrx, 2)); ?></td>
            <td class="bN bE bS right"><?php print(number_format($isIncOb ? $sumCreditCbl : $sumCreditTrx, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->
</body>
</html>
