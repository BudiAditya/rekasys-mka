<!DOCTYPE HTML>
<html>
<?php
/** @var array $sortableColumns */
/** @var int $start */ /** @var int $end */ /** @var int $status */
/** @var int $sort1 */ /** @var int $sort2 */ /** @var int $sort3 */ /** @var $reader ReaderBase */
/** @var VoucherType[] $types */
?>
<head>
	<title>Rekasys - Print Voucher</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#start").customDatePicker({ phpDate: <?php print(is_int($start) ? $start : "null"); ?> });
			$("#end").customDatePicker({ phpDate: <?php print(is_int($end) ? $end : "null"); ?> });
			$("#btnCheckAll").click(function(e) { SetCheck(true); });
			$("#btnUnCheckAll").click(function(e) { SetCheck(false); });
		});

		function SetCheck(state, classSelector) {
			if (classSelector == undefined) {
				classSelector = ".cbVoucher";
			}
			$(classSelector).each(function(idx, ele) {
				$(ele).prop("checked", state);
			});
		}

		function checkVoucher(id) {
			SetCheck(false);
			SetCheck(true, ".voc_" + id);
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
	<legend><span class="bold">Print Voucher</span></legend>
	<form action="<?php print($helper->site_url("accounting.voucher/print_all")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="start">Periode :</label></td>
				<td>
					<input type="text" id="start" name="start" size="12" />
					<label for="end"> s.d. </label>
					<input type="text" id="end" name="end" size="12" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="status">Status Voucher</label></td>
				<td>
					<select id="status" name="status">
						<option value="-1">SEMUA STATUS</option>
						<option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>DRAFT</option>
						<option value="2" <?php print($status == 2 ? 'selected="selected"' : ''); ?>>APPROVED</option>
						<option value="3" <?php print($status == 3 ? 'selected="selected"' : ''); ?>>VERIFIED</option>
						<option value="4" <?php print($status == 4 ? 'selected="selected"' : ''); ?>>POSTED</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="sort1">Urutkan berdasar :</label></td>
				<td>
					<select id="sort1" name="sort1">
						<?php
						foreach ($sortableColumns as $idx => $column) {
							if ($idx == $sort1) {
								printf('<option value="%d" selected="selected">%s</option>', $idx, $column["display"]);
							} else {
								printf('<option value="%d">%s</option>', $idx, $column["display"]);
							}
						}
						?>
					</select>
					<label for="sort2"> lalu </label>
					<select id="sort2" name="sort2">
						<option value="-1">--</option>
						<?php
						foreach ($sortableColumns as $idx => $column) {
							if ($idx == $sort2) {
								printf('<option value="%d" selected="selected">%s</option>', $idx, $column["display"]);
							} else {
								printf('<option value="%d">%s</option>', $idx, $column["display"]);
							}
						}
						?>
					</select>
					<label for="sort3"> terakhir </label>
					<select id="sort3" name="sort3">
						<option value="-1">--</option>
						<?php
						foreach ($sortableColumns as $idx => $column) {
							if ($idx == $sort3) {
								printf('<option value="%d" selected="selected">%s</option>', $idx, $column["display"]);
							} else {
								printf('<option value="%d">%s</option>', $idx, $column["display"]);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Preview Voucher</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

<?php if ($reader !== null) { ?>
<br />
<div class="container">
	<div class="title bold center">Daftar Voucher</div>
	<div class="center subTitle">Semua voucher yang tercentang akan di print</div>
	<br />

	<form action="<?php print($helper->site_url("accounting.voucher/print/output:pdf")); ?>" method="get">
		<div class="center">
			Centang :
			<button type="button" id="btnCheckAll">Semua</button>
			<?php
			foreach ($types as $type) {
				printf('<button type="button" onclick="checkVoucher(\'%s\')">%s</button>', $type->Id, $type->VoucherCd);
			}
			?>
			<br />
			<button type="button" id="btnUnCheckAll">Un-check Semua</button>
			<button type="submit">Print</button>
		</div><br />

		<table cellspacing="0" cellpadding="0" class="tablePadding tableBorder" style="margin: 0 auto;">
			<tr class="bold center">
				<th>No.</th>
				<th>Company</th>
				<th>Jenis</th>
				<th colspan="2">No. Voucher</th>
				<th>Tgl. Voucher</th>
				<th>Status</th>
				<th>Keterangan</th>
			</tr>
			<?php
			$idx = 1;
			while ($row = $reader->FetchAssoc()) {
				print("<tr>");
				printf('<td class="right">%s.</td>', $idx++);
				printf('<td class="center">%s</td>', $row["entity_cd"]);
				printf('<td class="center">%s</td>', $row["doc_code"]);
				printf('<td style="border-right: none;"><input type="checkbox" id="cb_%d" name="id[]" value="%d" checked="checked" class="cbVoucher %s voc_%s" /></td>', $idx, $row["id"], strtoupper($row["doc_code"]), $row["accvoucher_id"]);
				printf('<td><label for="cb_%d">%s</label></td>', $idx, $row["doc_no"]);
				printf('<td>%s</td>', date(HUMAN_DATE_NUMERIC, strtotime($row["voucher_date"])));
				printf('<td>%s</td>', $row["short_desc"]);
				printf('<td>%s</td>', str_replace("\n", "<br />", $row["note"]));
				print("</tr>");
			}
			?>
		</table>
	</form>
</div>
<?php } ?>

</body>
</html>
