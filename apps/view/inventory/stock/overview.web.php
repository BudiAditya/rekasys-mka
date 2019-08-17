<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Report Stock Gudang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		.overStock {
			background-color: yellow;
			color: red;
		}

		.lowStock {
			background-color: #E41B17;
			color: white;
		}

		.discontinue {
			font-style: oblique;
		}
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$("#Date").customDatePicker();
			$("#Filter").change(function (e) {
				Filter_Change(this, e);
			});
		});

		function Filter_Change(sender, e) {
            if (sender.value == -1) {
                $(".rowItem").show();
            } else {
                $(".rowItem").hide();

                switch (sender.value) {
                    case "1":
                        $(".lowStock").show();
                        break;
                    case "2":
                        $(".overStock").show();
                        break;
                    case "3":
                        $(".stockOk").show();
                        break;
                    case "4":
                        $(".discontinue").show();
                        break;
                }
            }
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
	<legend><span class="bold">Item Stock Report</span></legend>

	<form action="<?php print($helper->site_url("inventory.stock/overview")); ?>" method="get">
		<div class="center">
			<label for="ProjectId">Project/Warehouse : </label>
			<select id="ProjectId" name="project">
				<?php
                if ($userLevel > 4){
                    print('<option value="">-- ALL PROJECT --</option>');
                }
				$selectedProjectId = null;
				foreach ($projects as $project) {
					if ($project->Id == $projectId) {
						$selectedProjectId = $project;
						printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
					} else {
						printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
					}
				}
				?>
			</select>

			<label for="Date">As per date : </label>
			<input type="text" id="Date" name="date" size="10" value="<?php print(date(JS_DATE, $date)); ?>" />

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
<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="bold center">
		<span class="title">Item Stock Report Per: <?php print(date(HUMAN_DATE, $date)); ?></span><br />
		<span class="subTitle">Project/Warehouse : <?php print($selectedProjectId == null ? 'All Warehouse' : $selectedProjectId->ProjectCd . " - " . $selectedProjectId->ProjectName); ?></span><br />
		<label for="Filter">Filter Item Status : </label>
		<select id="Filter">
			<option value="-1">-- UnFiltered --</option>
			<option value="1">LOW STOCK</option>
			<option value="2">OVER STOCK</option>
			<option value="3">OK</option>
			<option value="4">DISCONTINUE</option>
		</select>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
		<tr class="bold center">
			<td class="bN bE bS bW">No.</td>
			<td class="bN bE bS">Item Code</td>
            <td class="bN bE bS">Part Number</td>
			<td class="bN bE bS">Item Name</td>
            <td class="bN bE bS">Unit Brand</td>
            <td class="bN bE bS">Unit Type</td>
            <td class="bN bE bS">S/N</td>
			<td class="bN bE bS" colspan="2">Qty</td>
			<td class="bN bE bS">Status</td>
			<td class="bN bE bS">Track Detail</td>
		</tr>
		<?php
		$counter = 0;
		// Variable start akan otomatis di generate oleh method jika kosong dan memang ini yang kita inginkan
		$baseUrl = $helper->site_url("inventory.stock/track?item=%d&uom=%s&start=&end=" . date(SQL_DATETIME, $date) . "&project=" . $projectId . "&output=%s");
		while ($row = $report->fetch_assoc()) {
			$counter++;
			$className = $counter % 2 == 0 ? "rowItem evenRow" : "rowItem oddRow";
			$qty = $row["qty_stock"];
			$minQty = $row["min_qty"];
			$maxQty = $row["max_qty"];

			if ($minQty != 0 && $qty <= $minQty) {
				$status = "STOCK KURANG DARI BATAS MINIMUM";
				$className .= " lowStock";
			} else if ($maxQty != 0 && $qty >= $maxQty) {
                $status = "STOCK MELEBIHI BATAS MAXIMUM";
                $className .= " overStock";
            } else {
                $status = "OK";
                $className .= " stockOk";
            }

			if ($row["is_discontinue"] == 1) {
				$status .= " - BARANG DISCONTINUE !";
				$className .= " discontinue";
			}
			$web = sprintf($baseUrl, $row["item_id"], $row["uom_cd"], "web");
			$excel = sprintf($baseUrl, $row["item_id"], $row["uom_cd"], "xls");
			?>
			<tr class="<?php print($className); ?>">
				<td class="right bE bS bW"><?php print($counter); ?>.</td>
				<td class="bE bS"><?php print($row["item_code"]); ?></td>
                <td class="bE bS"><?php print($row["part_no"]); ?></td>
				<td class="bE bS"><?php print($row["item_name"]); ?></td>
                <td class="bE bS"><?php print($row["brand_name"]); ?></td>
                <td class="bE bS"><?php print($row["type_desc"]); ?></td>
                <td class="bE bS"><?php print($row["sn_no"]); ?></td>
				<td class="right bS"><b><?php print(number_format($row["qty_stock"], 0)); ?></b></td>
				<td class="bE bS"><?php print($row["uom_cd"]); ?></td>
				<td class="center bE bS"><?php print($status); ?></td>
				<td class="center bE bS">
					<a href="<?php print($web); ?>">Web</a>
					&nbsp;&nbsp;&nbsp;
					<a href="<?php print($excel); ?>">Excel</a>
				</td>
			</tr>
			<?php } ?>
	</table>
</div>
	<?php } ?>
<!-- END REGION: LAPORAN-->

</body>
</html>
