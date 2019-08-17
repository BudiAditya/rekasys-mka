<!DOCTYPE HTML>
<html>
<?php
/** @var Creditor[] $creditors */ /** @var int $creditorId */ /** @var StatusCode[] $codes */ /** @var int $status */ /** @var ReaderBase $report */
/** @var int $startDate */ /** @var int $endDate */ /** @var string $output */
?>
<head>
	<title>Rekasys - AP Payment Listing</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
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
		$(document).ready(function () {
			$("#startDate").customDatePicker();
			$("#endDate").customDatePicker();

			$("#creditorId").select2({
				placeholderOption: "first",
				allowClear: false,
				minimumInputLength: 1,
				formatResult: formatOptionList,
				formatSelection: formatOptionResult
			});
		});

		function formatOptionList(state) {
			if (state.id == -1) {
				return "-- SEMUA CREDITOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode">' + originalOption.data("code") + '</div><div class="colText">' + originalOption.data("name") + '</div>';
		}

		function formatOptionResult(state) {
			if (state.id == -1) {
				return "-- SEMUA CREDITOR --";
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
	<legend><span class="bold">Report Data AP Payment</span></legend>

	<form action="<?php print($helper->site_url("ap.payment/overview")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td><label for="startDate">Tgl : </label></td>
				<td>
					<input type="text" id="startDate" name="startDate" value="<?php print(date(JS_DATE, $startDate)); ?>" />
					<label for="endDate">s.d </label>
					<input type="text" id="endDate" name="endDate" value="<?php print(date(JS_DATE, $endDate)); ?>" />
				</td>
			</tr>
			<tr>
				<td><label for="creditorId">Creditor : </label></td>
				<td>
					<select id="creditorId" name="creditorId" style="width:450px;">
						<option value="-1">-- SEMUA CREDITOR --</option>
						<?php
						$selectedCreditor = null;
						foreach($creditors as $creditor){
							if ($creditor->Id == $creditorId) {
								$selectedCreditor = $creditor;
								printf('<option value="%d" data-code="%s" data-name="%s" selected="selected">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName, $creditor->CreditorCd, $creditor->CreditorName);
							} else {
								printf('<option value="%d" data-code="%s" data-name="%s">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName, $creditor->CreditorCd, $creditor->CreditorName);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="status">Status : </label></td>
				<td>
					<select id="status" name="status">
						<option value="-1">-- SEMUA STATUS --</option>
						<?php
						$selectedStatus = null;
						foreach ($codes as $code) {
							if ($code->Code == $status) {
								$selectedStatus = $code;
								printf('<option value="%d" selected="selected">%s</option>', $code->Code, $code->ShortDesc);
							} else {
								printf('<option value="%d">%s</option>', $code->Code, $code->ShortDesc);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="output">Output laporan: </label></td>
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

<?php
if($report != null) {
	$creditorName = $selectedCreditor != null ? $selectedCreditor->CreditorName: "SEMUA CREDITOR";
	$statusName = $selectedStatus != null ? $selectedStatus->ShortDesc : "SEMUA STATUS";
?>
<br />
<div class="container">
	<div class="bold">
		<div class="title center">Report Data AP Payment</div>
		<div class="subTitle center">
			Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?><br />
			Creditor : <?php echo $creditorName; ?><br />
			Status : <?php echo $statusName; ?><br />
		</div><br />

		<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0 auto;">
			<tr class="bold center">
				<td>No.</td>
				<td>Company</td>
				<td>No. PV</td>
				<td>Tgl. PV</td>
				<td>Status</td>
				<td>Kreditor</td>
				<td>Invoice / GN</td>
				<td>Deskripsi</td>
				<td>Jumlah</td>
			</tr>

			<?php
			$i = 0;
			$prevId = null;
			while($row = $report->FetchAssoc()) {
				if ($prevId != $row["id"]) {
					$i++;
					$flagSame = false;
					$link = sprintf('<a href="%s">%s', $helper->site_url("ap.payment/view/" . $row["id"]), $row["doc_no"]);
					$prevId = $row["id"];
				} else {
					$flagSame = true;
					$link = "&nbsp;";
				}
			?>
			<tr>
				<td><?php echo $flagSame ? "&nbsp;" : $i; ?></td>
				<td><?php echo $flagSame ? "&nbsp;" : $row["entity_cd"]; ?></td>
				<td class="noWrap"><?php print($link); ?></td>
				<td class="noWrap"><?php echo $flagSame ? "&nbsp;" : date('d M Y', strtotime($row["doc_date"])); ?></td>
				<td><?php echo $flagSame ? "&nbsp;" : $row["short_desc"]; ?></td>
				<td><?php echo $flagSame ? "&nbsp;" : $row["creditor_name"]; ?></td>
				<td class="noWrap"><?php echo $row["paid_doc_no"]; ?></td>
				<td><?php echo $row["paid_note"]; ?></td>
				<td class="right"><?php echo number_format($row["amount"]); ?></td>
			</tr>
			<?php }?>
		</table>
	</div>
</div>
	<?php } ?>

</body>
</html>
