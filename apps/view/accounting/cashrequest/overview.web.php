<!DOCTYPE HTML>
<html>
<?php
/** @var StatusCode[] $statuses */ /** @var int $statusCode */ /** @var ReaderBase $report */ /** @var string $output */ /** @var int $start */ /** @var int $end */
?>
<head>
	<title>Rekasys - NPKP Report</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#start").customDatePicker({ phpDate: <?php print($start); ?> });
			$("#end").customDatePicker({ phpDate: <?php print($end); ?> });
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
	<legend><span class="bold">Report Dokumen Cash Request (NPKP)</span></legend>

	<form action="<?php print($helper->site_url("accounting.cashrequest/overview")); ?>" method="get">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="right bold"><label for="start">Periode :</label></td>
				<td colspan="3">
					<input type="text" id="start" name="start" />
					<label for="end" class="bold"> s.d. </label>
					<input type="text" id="end" name="end" />
				</td>
			</tr>
			<tr>
                <td class="right bold"><label for="Category">Project :</label></td>
                <td><select id="category" name="category">
                        <?php
                        if ($uLevel > 4){
                            print('<option value="0">-- ALL PROJECT --</option>');
                        }
                        foreach ($categories as $category) {
                            if ($category->Id == $categoryId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $category->Id, $category->Code, $category->Name);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $category->Id, $category->Code, $category->Name);
                            }
                        }
                        ?>
                    </select></td>
				<td class="right bold"><label for="status">Status : </label></td>
				<td>
					<select id="status" name="status">
						<option value="-1">-- SEMUA STATUS --</option>
						<?php
						$statusName = "-- SEMUA STATUS --";
						foreach ($statuses as $row) {
							if ($row->Code == $statusCode) {
								printf('<option value="%d" selected="selected">%s</option>', $row->Code, $row->ShortDesc);
								$statusName = $row->ShortDesc;
							} else {
								printf('<option value="%d">%s</option>', $row->Code, $row->ShortDesc);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right bold"><label for="output">Output laporan: </label></td>
				<td>
					<select id="output" name="output">
						<option value="web">Web Browser</option>
						<option value="xls">Excel</option>
						<option value="pdf">PDF</option>
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

<?php if($report != null) { ?>
<br />
<div class="container">
	<div class="bold center">
		<span class="title">Report Dokumen Cash Request (NPKP)</span><br />
		<span class="subTitle">Status : <?php echo $statusName; ?></span><br />
		<p>&nbsp;</p>
		<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0 auto;">
			<tr class="bold center">
				<td>No.</td>
				<td>Project</td>
				<td>No.Dokumen</td>
				<td>Tujuan NPKP</td>
				<td>Tgl. NPKP</td>
				<td>Prakiran Terima</td>
				<td>Jumlah</td>
				<td>Status</td>
			</tr>

			<?php
			$i = 0;
			$total = 0;
			while($rs = $report->FetchAssoc()) {
				$i++;
				$total+= $rs["jumlah"];
			?>

			<tr>
				<td class="center"><?php echo $i; ?></td>
				<td><?php echo $rs["project"]; ?></td>
				<td><a href="<?php echo site_url("accounting.cashrequest/view/" . $rs["id"])?>" target="_blank"><?php echo $rs["doc_no"]; ?></a></td>
				<td class="left"><?php echo $rs["objective"]; ?></td>
				<td><?php echo date('d M Y', strtotime($rs["cash_request_date"])); ?></td>
				<td><?php echo date('d M Y', strtotime($rs["eta_date"])); ?></td>
				<td class="right"><?php echo number_format($rs["jumlah"], 0); ?></td>
				<td><?php echo $rs["status_name"]; ?></td>
			</tr>
			<?php }
			if ($total > 0){
			    print("<tr>");
                print("<td class='right' colspan='6'>Total..</td>");
                printf("<td class='bold right'>%s</td>",number_format($total,0));
                print("<td>&nbsp;</td>");
                print("</tr>");
            }
			?>
		</table>
	</div>
</div>
<?php } ?>

</body>
</html>
