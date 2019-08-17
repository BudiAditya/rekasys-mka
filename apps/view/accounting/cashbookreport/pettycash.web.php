<!DOCTYPE HTML>
<html>
<?php
/** @var $obal OpeningBalance */ /** @var $obalTransaction array */
/** @var $accountId int */ /** @var $accounts Coa[] */ /** @var $start int */ /** @var $end int */ /** @var $status string */ /** @var $report ReaderBase */
/** @var $output string */ /** @var $orientation int */ /** @var $company Company */
?>
<head>
	<title>Rekasys - Report Petty Cash</title>
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
	<legend><span class="bold">Report Petty Cash</span></legend>
	<form action="<?php print($helper->site_url("accounting.cashbookreport/pettycash")); ?>" method="POST">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right"><label for="Account">Pilih Akun : </label></td>
				<td><select id="Account" name="account">
					<option value="">-- PILIH AKUN --</option>
					<?php
					$selectedAccount = null;
					foreach ($accounts as $account) {
						if ($account->Id == $accountId) {
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
				<td class="right"><label for="Start">Periode : </label></td>
				<td>
					<input type="text" id="Start" name="start" value="<?php print(date(JS_DATE, $start)) ?>" />
					<label for="End"> s.d. </label>
					<input type="text" id="End" name="end" value="<?php print(date(JS_DATE, $end)) ?>" />
				</td>
			</tr>
			<tr>
				<td class="right"><label for="DocStatus">Status Dokumen :</label></td>
				<td><select id="DocStatus" name="status">
					<option value="" <?php print($status == "" ? 'selected="selected"' : ''); ?>>SEMUA DOKUMEN</option>
					<option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>BELUM APPROVED</option>
					<option value="2" <?php print($status == 2 ? 'selected="selected"' : ''); ?>>SUDAH APPROVED</option>
					<option value="3" <?php print($status == 3 ? 'selected="selected"' : ''); ?>>VERIFIED</option>
					<option value="4" <?php print($status == 4 ? 'selected="selected"' : ''); ?>>POSTED</option>
				</select></td>
			</tr>
            <tr>
                <td class="right" valign="top"><label for="UserLvl">Beban Project :</label></td>
                <td>
                    <?php
                    /** @var $projects Project[] */
                    $cprojects = explode(",",$cprojects);
                    foreach ($projects as $project){
                        if (in_array($project->Id,$cprojects)) {
                            printf("<input type='checkbox' checked='checked' name='cProjectId[]' value='%d'>%s - %s<br>", $project->Id, $project->ProjectCd, $project->ProjectName);
                        }else{
                            printf("<input type='checkbox' name='cProjectId[]' value='%d'>%s - %s<br>", $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                </td>
            </tr>
			<tr>
				<td class="right"><label for="Output">Output : </label></td>
				<td>
					<select id="Output" name="output">
						<option value="web" <?php print($output == "web" ? 'selected="selected"' : '') ?>>Web Browser</option>
						<option value="pdf" <?php print($output == "pdf" ? 'selected="selected"' : '') ?>>PDF</option>
                        <option value="xls" <?php print($output == "xls" ? 'selected="selected"' : '') ?>>Excel</option>
					</select>
					<label for="Orientation"> posisi cetak : </label>
					<select id="Orientation" name="orientation">
						<option value="p">Portrait</option>
						<option value="l">Landscape</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Generate</button></td>
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
		LAPORAN PETTY CASH<br />
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?><br />
		Akun: <?php printf("%s - %s", $selectedAccount->AccNo, $selectedAccount->AccName); ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
		<tr class="bold center">
			<td>Tgl</td>
			<td>No. Voucher</td>
			<td>Kontra Pos</td>
			<td>Uraian</td>
			<td>Project</td>
			<td>Dept</td>
            <td>Act</td>
			<td>Debet</td>
			<td>Kredit</td>
			<td>Saldo</td>
		</tr>
		<tr class="bold right">
			<td colspan="7">Saldo Awal per tgl. <?php print(date(HUMAN_DATE, $start)); ?></td>
			<?php
			if ($obal != null) {
				printf("<td>%s</td>", $selectedAccount->DcSaldo == "D" ? number_format($obalTransaction["saldo"], 2) : "&nbsp;");
				printf("<td>%s</td>", $selectedAccount->DcSaldo == "K" ? number_format($obalTransaction["saldo"], 2) : "&nbsp;");
			} else {
				print("<td>-</td>");
				print("<td>-</td>");
			}
			?>
			<td>&nbsp;</td>
		</tr>
		<?php
		$counter = 0;
		$prevDate = null;
		$prevVoucherNo = null;

		$flagDate = true;
		$flagVoucherNo = true;
		$flagSbu = true;

		$subTotalDebit = 0;
		$subTotalCredit = 0;
		$totalDebit = 0;
		$totalCredit = 0;
		$saldo = $obal == null ? 0 : $obalTransaction["saldo"];
		foreach ($report as $master) {
			foreach ($master["details"] as $row) {
				$counter++;
				$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
				if ($prevDate != $row["voucher_date"]) {
					if ($counter > 1) {
						// Sudah pernah ada data yang ditulis
						$totalDebit += $subTotalDebit;
						$totalCredit += $subTotalCredit;
						printf('<tr class="bold right"><td colspan="7">Sub Total %s :</td><td>%s</td><td>%s</td><td>&nbsp;</td></tr>', date("d F", $prevDate), number_format($subTotalDebit, 2), number_format($subTotalCredit, 2));
					}

					$prevDate = $row["voucher_date"];
					$flagDate = true;

					$subTotalDebit = 0;
					$subTotalCredit = 0;
				} else {
					$flagDate = false;
				}

				if ($prevVoucherNo != $row["doc_no"]) {
					$prevVoucherNo = $row["doc_no"];
					$flagVoucherNo = true;
				} else {
					$flagVoucherNo = false;
				}

				$subTotalDebit += $row["debit"];
				$subTotalCredit += $row["credit"];
				if ($selectedAccount->DcSaldo == "D") {
					$saldo += ($row["debit"] - $row["credit"]);
				} else {
					$saldo += ($row["credit"] - $row["debit"]);
				}

		?>
		<tr class="<?php print($className); ?>">
			<td><?php print($flagDate ? date("d", $prevDate) : "&nbsp;"); ?></td>
            <td><?php print($row["doc_no"]); ?></td>
			<td><?php print($row["opposite_no"]); ?></td>
			<td><?php print($row["note"]); ?></td>
			<td><?php print(left($row["project_name"],3)); ?></td>
			<td><?php print($row["dept_code"]); ?></td>
            <td><?php print($row["act_code"]); ?></td>
			<td class="right"><?php print(number_format($row["debit"], 2)); ?></td>
			<td class="right"><?php print(number_format($row["credit"], 2)); ?></td>
			<td class="right"><?php print(number_format($saldo, 2)); ?></td>
		</tr>
		<?php
			}
		}
		// Seperti biasa untuk yang subTotal paling akhir terlupakan
		if ($counter > 1) {
			// Sudah pernah ada data yang ditulis
			$totalDebit += $subTotalDebit;
			$totalCredit += $subTotalCredit;
			printf('<tr class="bold right"><td colspan="7">Sub Total %s :</td><td>%s</td><td>%s</td><td>&nbsp;</td></tr>', date("d F", $prevDate), number_format($subTotalDebit, 2), number_format($subTotalCredit, 2));
		}
		?>
		<tr class="bold right">
			<td colspan="7">GRAND TOTAL :</td>
			<td><?php print(number_format($totalDebit, 2)); ?></td>
			<td><?php print(number_format($totalCredit, 2)); ?></td>
			<td><?php print(number_format($saldo, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


</body>
</html>
