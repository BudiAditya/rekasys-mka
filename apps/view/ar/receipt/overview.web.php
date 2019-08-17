<!DOCTYPE HTML>
<?php
/** @var Debtor[] $debtors */ /** @var Debtor $debt */  /** @var int $debtorId */  /** @var StatusCode[] $codes */ /** @var StatusCode $codeName */  /** @var int $status */
/** @var ReaderBase $report */ /** @var int $startDate */ /** @var int $endDate */ /** @var string $output */
?>
<html>
<head>
    <title>Rekasys - Official Receipt Listing</title>
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
    <legend><span class="bold">Report Data Official Receipt</span></legend>

    <form action="<?php print($helper->site_url("ar.receipt/overview")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="startDate">Tgl :</label></td>
				<td>
					<input type="text" id="startDate" name="startDate" value="<?php print(date(JS_DATE, $startDate)); ?>" />
					<label for="endDate">s.d </label>
					<input type="text" id="endDate" name="endDate" value="<?php print(date(JS_DATE, $endDate)); ?>" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="debtorId">Debtor :</label></td>
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
				<td class="bold right"><label for="status">Status :</label></td>
				<td>
					<select id="status" name="status">
						<option value="-1">-- SEMUA STATUS --</option>
						<?php
						foreach ($codes as $code) {
							if ($code->Code == $status) {
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
				<td class="bold right"><label for="output">Output laporan:</label></td>
				<td>
					<select id="output" name="output">
						<option value="web">Web Browser</option>
						<option value="xls">Excel</option>
						<option value="pdf">PDF</option>
					</select>
					<button type="submit">Generate</button>
				</td>
			</tr>
		</table>
    </form>
</fieldset>

<?php
    if($report != null) {
        $debtorName = $debt != null ? $debt->DebtorName: "SEMUA DEBTOR";
        $statusName = $codeName != null ? $codeName->ShortDesc : "SEMUA STATUS";
?>

<br />
<div class="container">
    <div>
        <span class="title">Report Data Official Receipt</span><br />
        <span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
        <span class="subTitle">Debtor : <?php echo $debtorName; ?></span><br />
        <span class="subTitle">Status : <?php echo $statusName; ?></span><br /><br />
	</div>

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0;">
		<tr class="bold center">
			<th>No.</th>
			<th>No. Receipt</th>
			<th>Debtor Code</th>
            <th>Debtor Name</th>
			<th>Receipt Date</th>
			<th>Amount</th>
			<th>Allocated</th>
			<th>Balance</th>
			<th>Akun Debet</th>
            <th>Status</th>
		</tr>
		<?php
		$i = 0;
		while($rs = $report->fetch_assoc()) {
			$i++;
		?>
		<tr>
			<td class="center"><?php echo $i; ?></td>
			<td><a href="<?php echo site_url("ar.receipt/view/" . $rs["id"])?>" target="_blank"><?php echo $rs["receipt_no"]; ?></a></td>
			<td><?php echo $rs["debtor_cd"]; ?></td>
            <td><?php echo $rs["debtor_name"]; ?></td>
			<td><?php echo date('d M Y', strtotime($rs["receipt_date"])); ?></td>
			<td class="right"><?php echo number_format($rs["receipt_amount"]); ?></td>
			<td class="right"><?php echo number_format($rs["allocate_amount"]); ?></td>
			<td class="right"><?php echo number_format($rs["receipt_amount"] - $rs["allocate_amount"]); ?></td>
			<td class="right"><?php echo $rs["bank_name"]; ?></td>
            <td><?php echo $rs["short_desc"]; ?></td>
		</tr>
		<?php }?>
	</table>
</div>
    <?php } ?>

</body>
</html>
