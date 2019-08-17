<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Rekap Cash/Bank Out</title>
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
	<legend><span class="bold">Rekap Cash/Bank</span></legend>

	<div class="center">
		<form action="<?php print($helper->site_url("accounting.cashbookreport/recap_out")); ?>" method="GET">
			<label for="Month">Periode : </label>
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
			<label for="Year">Tahun : </label>
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
			<label for="Output">Output : </label>
			<select id="Output" name="output">
				<option value="web">Web Browser</option>
				<option value="pdf">PDF Format</option>
                <option value="xls">Excel Format</option>
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
		Rekap Cash/Bank Out Per Akun<br />
		Periode: <?php printf("%s %s", $monthNames[$month], $year); ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr class="bold center">
			<td rowspan="2" class="bN bE bS bW">No. Akun</td>
			<td rowspan="2" class="bN bE bS">Nama Akun</td>
			<td colspan="2" class="bN bE bS">Mutasi <?php printf("%s %s", $monthNames[$month], $year); ?></td>
			<td colspan="2" class="bN bE bS">Jumlah s.d. <?php printf("%s %s", $monthNames[$month], $year); ?></td>
		</tr>
		<tr class="bold center">
			<td class="bE bS">Debet</td>
			<td class="bE bS">Kredit</td>
			<td class="bE bS">Debet</td>
			<td class="bE bS">Kredit</td>
		</tr>
		<?php
		$sumDebit = 0;
		$sumCredit = 0;
		$sumAllDebit = 0;
		$sumAllCredit = 0;
		while($row = $report->FetchAssoc()) {
			$sumDebit += $row["total_debit"];
			$sumCredit += $row["total_credit"];
			$sumAllDebit += $row["total_debit"] + $row["total_debit_prev"];
			$sumAllCredit += $row["total_credit"] + $row["total_credit_prev"];
		?>
		<tr>
			<td class="bE bW"><?php print($row["acc_no"]); ?></td>
			<td class="bE"><?php print($row["acc_name"]); ?></td>
			<td class="bE right"><?php print(number_format($row["total_debit"], 2)); ?></td>
			<td class="bE right"><?php print(number_format($row["total_credit"], 2)); ?></td>
			<td class="bE right"><?php print(number_format($row["total_debit"] + $row["total_debit_prev"], 2)); ?></td>
			<td class="bE right"><?php print(number_format($row["total_credit"] + $row["total_credit_prev"], 2)); ?></td>
		</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="2" class="bN bE bS bW right">TOTAL :</td>
			<td class="bN bE bS right"><?php print(number_format($sumDebit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumCredit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumAllDebit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumAllCredit, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->

</body>
</html>
