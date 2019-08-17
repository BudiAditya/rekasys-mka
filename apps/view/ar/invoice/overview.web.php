<!DOCTYPE HTML>
<html>
<?php
/** @var $output string */
/** @var $debtors Debtor[] */ /** @var $debt Debtor */ /** @var $debtorId int */
/** @var $invTypes ArInvoiceType[] */ /** @var $invTypeId int[] */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */ /** @var int $groupBy */ /** @var string $key */
?>
<head>
	<title>Rekasys - A/R Invoice Listing </title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/select2.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<style type="text/css">
		#list {
			margin: 0;
			padding: 0;
		}
		#list li {
			display: inline-block;
			padding: 0 2px;
		}
		#list li label {
			position: relative;
			top: 1px;
			display: inline-block;
			width: 130px;
			overflow: hidden;
		}
		.nowrap { white-space: nowrap; }
		.colCode { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colText { display: inline-block; width: 310px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.blue { color: blue; }
	</style>

	<script type="text/javascript">
		$(document).ready(function () {
			$("#startDate").customDatePicker();
			$("#endDate").customDatePicker();

			$("#debtorId").select2({
				placeholderOption: "first",
				allowClear: false,
				minimumInputLength: 1,
				formatResult: formatOptionList,
				formatSelection: formatOptionResult
			});
		});

		function formatOptionList(state) {
			if (state.id == -1) {
				return "-- SEMUA DEBTOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode">' + originalOption.data("code") + '</div><div class="colText">' + originalOption.data("name") + '</div>';
		}

		function formatOptionResult(state) {
			if (state.id == -1) {
				return "-- SEMUA DEBTOR --";
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
	<legend><span class="bold">Report Data Invoice</span></legend>

	<form action="<?php print($helper->site_url("ar.invoice/overview")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; width: 90%;">
			<tr>
				<td class="bold right"><label for="startDate">Tgl : </label></td>
				<td>
					<input type="text" id="startDate" name="startDate" value="<?php print(date(JS_DATE, $startDate)); ?>" />
					<label for="endDate">s.d </label>
					<input type="text" id="endDate" name="endDate" value="<?php print(date(JS_DATE, $endDate)); ?>" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="debtorId">Debtor : </label></td>
				<td>
					<select id="debtorId" name="debtorId" style="width:450px;">
						<option value="-1">-- SEMUA DEBTOR --</option>
						<?php
						foreach($debtors as $debtor){
							if($debtor->Id ==  $debtorId) {
								printf('<option value="%s" selected="selected" data-code="%s" data-name="%s">%s - %s</option>', $debtor->Id, $debtor->DebtorCd, $debtor->DebtorName, $debtor->DebtorCd, $debtor->DebtorName);
							} else {
								printf('<option value="%s" data-code="%s" data-name="%s">%s - %s</option>', $debtor->Id, $debtor->DebtorCd, $debtor->DebtorName, $debtor->DebtorCd, $debtor->DebtorName);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="nowrap bold right"><label for="invTypeId">Jenis Tagihan : </label></td>
				<td>
					<ul id="list">
						<?php
						$buff = array();
						foreach($invTypes as $invType){
							if(in_array($invType->Id, $invTypeId)) {
								$checkbox = sprintf('<input type="checkbox" id="cb_%d" name="invTypeId[]" value="%d" checked="checked" />', $invType->Id, $invType->Id);
								$buff[] = $invType->InvoiceType;
							} else {
								$checkbox = sprintf('<input type="checkbox" id="cb_%d" name="invTypeId[]" value="%d" />', $invType->Id, $invType->Id);
							}
							$label = sprintf('<label for="cb_%d" class="nowrap">%s</label>', $invType->Id, strtoupper($invType->InvoiceType));
							printf("<li>%s %s</li>", $checkbox, $label);
						}
						?>
					</ul>
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="status">Status : </label></td>
				<td>
					<select id="status" name="status">
						<option value="-1" <?php print($status == -1 ? 'selected="selected"' : ''); ?>>-- SEMUA STATUS --</option>
						<option value="0" <?php print($status == 0 ? 'selected="selected"' : ''); ?>>-- DRAFT --</option>
						<option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>-- APPROVED --</option>
                        <option value="2" <?php print($status == 2 ? 'selected="selected"' : ''); ?>>-- POSTED --</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="nowrap bold right"><label for="output">Output laporan: </label></td>
				<td>
					<select id="output" name="output">
						<option value="web">Web Browser</option>
						<option value="xls">Excel</option>
						<!--option value="pdf">PDF</option-->
					</select>
					<button type="submit">Generate</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

<?php if($report != null) {
	$debtorName = $debt != null ? $debt->DebtorName : "SEMUA DEBTOR";
	$billDesc = count($buff) > 0 ? implode(", ", $buff) : "SEMUA JENIS TAGIHAN";
	if ($status == -1) {
		$statusName = "SEMUA STATUS";
	} elseif ($status == 0) {
		$statusName = "UNPOSTED";
	} else {
		$statusName = "POSTED";
	}
?>
<br />
<div class="container">
	<div>
		<span class="title">Report Data Invoice</span><br />
		<span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
		<span class="subTitle">Debtor : <?php echo $debtorName; ?></span><br />
		<span class="subTitle">Jenis Tagihan : <?php echo $billDesc; ?></span><br />
		<span class="subTitle">Status : <?php echo $statusName; ?></span><br />
		<p>&nbsp;</p>

		<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0;">
			<tr>
				<th>No.</th>
				<th>No.Invoice</th>
				<th>Tgl.Invoice</th>
				<th>Kode Debtor</th>
				<th>Nama Debtor</th>
                <th>Type</th>
				<th>Deskripsi</th>
				<th>DPP</th>
				<th>PPN</th>
				<th>PPh</th>
				<th>DPP + PPN</th>
                <th>Status</th>
			</tr>
			<?php
			$docNo = null;
			$i = 1;
			$prevKey = null;
			$subTotals = array ("base" => 0, "tax" => 0, "deduction" => 0);
			$totals = array ("base" => 0, "tax" => 0, "deduction" => 0);

			while($rs = $report->FetchAssoc()) {
				if ($rs["invoice_no"] != $docNo){
					$entity = $rs["entity_cd"];
					$doc = $rs["invoice_no"];
					$docStatus = $rs["short_desc"];
					$date = date('d M Y', strtotime($rs["invoice_date"]));
					$kodeDebtor = $rs["debtor_cd"];
					$namaDebtor = $rs["debtor_name"];
					$docNo = $rs["invoice_no"];
					$counter = $i++;
				} else {
					$entity = "";
					$doc = "";
					$docStatus = "";
					$date = "";
					$kodeDebtor = "";
					$namaDebtor = "";
					$counter = "";
				}

				// Apakah kita harus proses grouping ?
				if ($groupBy != -1) {
					if ($prevKey != $rs[$key]) {
						// OK periksa apakah ada nilai sebelumnya atau tidak
						if ($prevKey != null) {
							print('<tr class="bold right forceN forceS">');
							printf('<td colspan="10">SUB TOTAL %s : </td>', strtoupper($prevKey));
							printf('<td>%s</td>', number_format($subTotals["base"]));
							printf('<td>%s</td>', number_format($subTotals["tax"]));
							printf('<td>%s</td>', number_format($subTotals["deduction"]));
							printf('<td>%s</td>', number_format($subTotals["base"] + $subTotals["tax"]));
							print('</tr>');
						}

						$prevKey = $rs[$key];
						$subTotals = array ("base" => 0, "tax" => 0, "deduction" => 0);
					}

					// Untuk subtotal apabila di grouping
					$subTotals["base"] += $rs["base_amount"];
					$subTotals["tax"] += $rs["vat_amount"];
					$subTotals["deduction"] += $rs["wht_amount"];
				}
				// Untuk Grand total
				$totals["base"] += $rs["base_amount"];
				$totals["tax"] += $rs["vat_amount"];
				$totals["deduction"] += $rs["wht_amount"];
			?>
			<tr>
				<td class="right"><?php echo $counter; ?></td>
                <td><a href="<?php echo site_url("ar.invoice/view/" . $rs["id"])?>" target="_blank"><?php echo $doc; ?></a></td>
				<td><?php echo $date ; ?></td>
				<td><?php echo $kodeDebtor; ?></td>
				<td><?php echo $namaDebtor; ?></td>
				<td><?php echo $rs["invoice_type"]; ?></td>
				<td><?php echo $rs["invoice_descs"]; ?></td>
				<td class="right"><?php echo number_format($rs["base_amount"]); ?></td>
				<td class="right"><?php echo number_format($rs["vat_amount"]); ?></td>
				<td class="right"><?php echo number_format($rs["wht_amount"]); ?></td>
				<td class="right"><?php echo number_format($rs["base_amount"] + $rs["vat_amount"]); ?></td>
                <td><?php echo $docStatus; ?></td>
			</tr>
			<?php
			}

			// Yang terakhir pasti terlupakan
			if ($groupBy != -1 && $prevKey != null) {
				print('<tr class="bold right forceN">');
				printf('<td colspan="7">SUB TOTAL %s : </td>', strtoupper($prevKey));
				printf('<td>%s</td>', number_format($subTotals["base"]));
				printf('<td>%s</td>', number_format($subTotals["tax"]));
				printf('<td>%s</td>', number_format($subTotals["deduction"]));
				printf('<td>%s</td>', number_format($subTotals["base"] + $subTotals["tax"]));
				print("<td>&nbsp;</td>");
				print('</tr>');
			}
			?>
			<tr class="right forceN forceS">
				<td colspan="7">TOTAL : </td>
				<td><?php echo number_format($totals["base"]); ?></td>
				<td><?php echo number_format($totals["tax"]); ?></td>
				<td><?php echo number_format($totals["deduction"]); ?></td>
				<td><?php echo number_format($totals["base"] + $totals["tax"]); ?></td>
                <td>&nbsp;</td>
			</tr>
		</table>
	</div>
</div>
<?php } ?>

</body>
</html>
