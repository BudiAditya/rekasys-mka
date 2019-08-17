<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Unfinished MR Report</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#frmRecap").submit(function(e) { return frmRecap_Submit(this, e); });
		});

		function frmRecap_Submit(sender, e) {
			var checked = $("input:checked").length;
			if (checked == 0) {
				alert("Harap memilih beberapa dokumen MR terlebih dahulu dengan cara mencentangnya.")
				return false;
			} else {
				return true;
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
	<legend><span class="bold">Unfinished MR vs Inventory</span></legend>

	<form action="<?php print($helper->site_url("inventory.mr/search_unfinished")); ?>" method="get">
		<label for="projectId">Project/Warehouse : </label>
		<select id="projectId" name="projectId">
			<?php
            if ($userLevel > 4) {
                print('<option value="0">-- ALL PROJECT --</option>');
            }
			foreach ($projects as $project) {
				if ($projectId == $project->Id) {
					printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
				} else {
					printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
				}
			}
			?>
		</select> &nbsp;&nbsp;
		<button type="submit">Process</button>
	</form>
</fieldset>

<!-- REGION: LAPORAN -->
<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="bold center">
		<span class="subTitle">Unfinished MR List</span><br />
		<span class="subTitle"><?php print($projectId == 0 ? '- All Project -' : 'Project : '.$project->ProjectCd . " - " . $project->ProjectName); ?></span><br />
	</div> <br /><br />
	
	<form id="frmRecap" action="<?php print($helper->site_url("inventory.mr/rpt_recap_item")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr class="bold center">
				<td class="bN bE bS bW">No.</td>
				<td class="bN bE bS">Choose</td>
				<td class="bN bE bS">MR Number</td>
				<td class="bN bE bS">MR Date</td>
				<td class="bN bE bS">Project</td>
                <td class="bN bE bS">Dept</td>
                <td class="bN bE bS">Status</td>
			</tr>
			<?php
			foreach ($report as $idx => $mr) {
				$className = $idx % 2 == 0 ? "oddRow" : "evenRow";
			?>
			<tr class="<?php print($className); ?>">
				<td class="bE bS bW right"><?php print($idx + 1); ?>.</td>
				<td class="bE bS center">
					<input type="checkbox" id="cb_<?php print($idx); ?>" name="id[]" value="<?php print($mr->Id); ?>" />
				</td>
                <td class="bE bS"><label for="cb_<?php print($idx); ?>"><a href="<?php print($helper->site_url("inventory.mr/view/" . $mr->Id)); ?>"><?php print($mr->DocumentNo); ?></a></label></td>
				<td class="bE bS"><?php print($mr->FormatDate()); ?></td>
				<td class="bE bS"><?php print($mr->ProjectCd.' - '.$mr->ProjectName); ?></td>
                <td class="bE bS"><?php print($mr->DepartmentCode); ?></td>
				<td class="bE bS"><?php print($mr->GetStatus()); ?></td>
			</tr>
			<?php } ?>
		</table><br />

		<div class="center">
			<label for="output">Output :</label>
			<select id="output" name="output">
				<option value="web">Web Browser</option>
				<option value="xls">Excel</option>
			</select>
			<button type="submit">Generate Detail</button>
		</div>
	</form>
</div>
<?php } ?>

</body>
</html>
