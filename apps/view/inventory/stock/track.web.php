<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Tracking Item: <?php printf("%s - %s", $item->ItemCode, $item->ItemName); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$("#StartDate").customDatePicker();
			$("#EndDate").customDatePicker();
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
	<legend><span class="bold">Tracking Item: <?php printf("%s - %s %s", $item->ItemCode, $item->ItemName, strlen($item->PartNo) > 2 ? '('.$item->PartNo.')' : '' ); ?></span></legend>

	<form action="<?php print($helper->site_url("inventory.stock/track")); ?>" method="get">
		<input type="hidden" name="item" value="<?php print($item->Id); ?>" />

		<div class="center">
			<label for="Project">Project/Warehouse : </label>
            <select id="ProjectId" name="project">
                <?php
                if ($userLevel > 4){
                    print('<option value="">-- ALL PROJECT --</option>');
                }
                $selectedProject = null;
                foreach ($projects as $project) {
                    if ($project->Id == $projectId) {
                        $selectedProject = $project;
                        printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                    } else {
                        printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                    }
                }
                ?>
            </select>

			<label for="StartDate">Track Per : </label>
			<input type="text" id="StartDate" name="start" size="10" value="<?php print(date(JS_DATE, $startDate)); ?>" />
			<label for="EndDate">s.d.</label>
			<input type="text" id="EndDate" name="end" size="10" value="<?php print(date(JS_DATE, $endDate)); ?>" />

			<label for="Output">Output : </label>
			<select id="Output" name="output">
				<option value="web">Web Browser</option>
				<option value="xls">Excel</option>
			</select>

			<button type="submit">Generate</button>
		</div>
	</form>
</fieldset>

<!-- REGION: LAPORAN -->
<?php if ($histories !== null) { ?>
<br />
<div class="container">
	<div class="bold center">
		<span class="title">Tracking Item: <?php printf("%s - %s %s", $item->ItemCode, $item->ItemName, strlen($item->PartNo) > 2 ? '('.$item->PartNo.')' : '' ); ?></span><br />
	<span class="subTitle">
		Gudang: <?php print($selectedProject == null ? 'ALL WAREHOUSE' : $selectedProject->ProjectCd . " - " . $selectedProject->ProjectName); ?><br />
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $startDate), date(HUMAN_DATE, $endDate)); ?>
	</span>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
		<tr class="bold center">
			<td rowspan="2" class="bN bE bS bW">No.</td>
			<td rowspan="2" class="bN bE bS">Document No</td>
			<td rowspan="2" class="bN bE bS">Type</td>
			<td rowspan="2" class="bN bE bS">Date</td>
			<td colspan="2" class="bN bE bS">QTY</td>
			<td rowspan="2" class="bN bE bS">COR</td>
			<td rowspan="2" class="bN bE bS">BAL</td>
		</tr>
		<tr class="bold center">
			<td class="bE bS">IN</td>
			<td class="bE bS">OUT</td>
		</tr>
		<tr class="bold">
			<td colspan="3" class="right bE bS bW">Opening</td>
			<td class="bE bS"><?php print(date(HUMAN_DATE, $startDate)); ?></td>
			<td class="right bE bS"><?php print($saldoAwal >= 0 ? number_format($saldoAwal, 2) : "&nbsp;"); ?></td>
			<td class="right bE bS"><?php print($saldoAwal < 0 ? number_format(abs($saldoAwal), 2) : "&nbsp;"); ?></td>
			<td class="bE bS">&nbsp;</td>
			<td class="right bE bS"><?php print(number_format($saldoAwal, 2)); ?></td>
		</tr>
		<?php
		$saldoAkhir = $saldoAwal;
		$totalIn = 0;
		$totalOut = 0;
		$totalCorrection = 0;
		// Untuk saldo awal
		if ($saldoAwal >= 0) {
			$totalIn += $saldoAwal;
		} else {
			$totalOut += abs($saldoAwal);
		}

		foreach ($histories as $idx => $stock) {
			$isSo = in_array($stock->StockTypeCode, array(102));

			if ($stock->StockTypeCode < 100) {
				$saldoAkhir += $stock->Qty;
				if ($isSo) {
					$totalCorrection += $stock->Qty;
				} else {
					$totalIn += $stock->Qty;
				}
			} else if ($stock->StockTypeCode > 100) {
				$saldoAkhir -= $stock->Qty;
				if ($isSo) {
					$totalCorrection -= $stock->Qty;
				} else {
					$totalOut += $stock->Qty;
				}
			}

			// HACK agar klo SO (-) maka qty menjadi negatif (harus jalan terakhir karena script diatas untuk qty jika type > 100 akan dikurangi)
			if ($stock->StockTypeCode == 102) {
				$stock->Qty *= -1;
			}
			?>
			<tr>
				<td class="right bE bS bW"><?php print($idx + 1); ?>.</td>
                <td class="bE bS"><a href="<?php print($helper->site_url($stock->GenerateReferenceLink())); ?>" target="_blank"><?php print($stock->DocumentNo); ?></a></td>
				<td class="bE bS"><?php print($stock->DocumentType); ?></td>
				<td class="bE bS"><?php print($stock->FormatDocumentDate()); ?></td>
				<td class="right bE bS"><?php print(!$isSo && $stock->StockTypeCode < 100 ? number_format($stock->Qty, 2) : "&nbsp;"); ?></td>
				<td class="right bE bS"><?php print(!$isSo && $stock->StockTypeCode > 100 ? number_format($stock->Qty, 2) : "&nbsp;"); ?></td>
				<td class="right bE bS"><?php print($isSo ? number_format($stock->Qty, 2) : "&nbsp;"); ?></td>
				<td class="right bE bS"><?php print(number_format($saldoAkhir, 2)); ?></td>
			</tr>
			<?php } ?>
		<tr class="bold">
			<td colspan="3" class="right bE bS bW">Closing</td>
			<td class="bE bS"><?php print(date(HUMAN_DATE, $endDate)); ?></td>
			<td class="right bE bS"><?php print(number_format($totalIn, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($totalOut, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($totalCorrection, 2)); ?></td>
			<td class="right bE bS"><?php print(number_format($saldoAkhir, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN -->

</body>
</html>
