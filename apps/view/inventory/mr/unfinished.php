<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Unfinished M/R</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#processDate").customDatePicker();
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
	<legend><span class="bold">UNFINISHED M/R PROCESS</span></legend>

	<form action="<?php print($helper->site_url("inventory.mr/unfinished")); ?>" method="post">
		<label for="projectId">Project/Warehouse : </label>
		<select id="projectId" name="projectId">
			<?php
            $projectName = null;
			foreach ($projects as $project) {
				if ($projectId == $project->Id) {
				    $projectName = $project->ProjectCd.' - '.$project->ProjectName;
					printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
				} else {
					printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
				}
			}
			?>
		</select> &nbsp;&nbsp;
        <label for="processType">Report Type : </label>
        <select id="processType" name="processType">
            <option value="1" <?php print($processType == 1 ? 'selected="selected"' : '');?>>1 - Detail</option>
            <option value="2" <?php print($processType == 2 ? 'selected="selected"' : '');?>>2 - Summary</option>
        </select> &nbsp;&nbsp;
		<button type="submit">Generate</button>
	</form>
</fieldset>

<!-- REGION: LAPORAN -->
<?php if ($report != null) { ?>
<br />
<div class="container">
	<div>
		<span class="subTitle">UNFINISHED MR LIST</span><br />
		<span class="subTitle"><?php print('PROJECT : '.$projectName); ?></span><br />
        <span class="subTitle"><?php print($processType == 1 ? 'DETAIL' : 'SUMMARY VS STOCK'); ?></span><br />
	</div> <br />
    <?php
    if ($processType == 1) {
        ?>
    <form action="<?php print($helper->site_url("inventory.mr/process")); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Dept Name</th>
                <th colspan="6">M/R Information</th>
                <th colspan="3">Stock</th>
                <th colspan="3">QTY</th>
                <th colspan="2">Process</th>
            </tr>
            <tr>
                <th>M/R No.</th>
                <th>M/R Date</th>
                <th>Item Code</th>
                <th>Part Number</th>
                <th>Item Name</th>
                <th>UOM</th>
                <th>Stock Code</th>
                <th>Q/C</th>
                <th>Qty</th>
                <th>MR</th>
                <th>PR</th>
                <th>IS</th>
                <th>PR</th>
                <th>IS</th>

            </tr>
            <?php
            $nrt = 0;
            $dep = null;
            $mrn = null;
            $mrd = null;
            while ($rpt = $report->FetchAssoc()) {
                $nrt++;
                print("<tr>");
                printf("<td align='center'>%d</td>",$nrt);
                printf("<td>%s</td>",$rpt['dept_name']);
                printf("<td>%s</td>",$rpt['mr_no']);
                printf("<td>%s</td>",$rpt['mr_date']);
                printf("<td>%s</td>",$rpt['item_code']);
                printf("<td>%s</td>",$rpt['part_no']);
                printf("<td>%s</td>",$rpt['item_name']);
                printf("<td>%s</td>",$rpt['uom_cd']);
                printf("<td>%s</td>",$rpt['stock_item_code']);
                printf("<td align='center'>%s</td>",$rpt['qstock']);
                printf("<td align='right'>%s</td>",$rpt['st_qty']);
                printf("<td align='right'>%s</td>",$rpt['mr_qty']);
                printf("<td align='right'>%s</td>",$rpt['pr_qty']);
                printf("<td align='right'>%s</td>",$rpt['is_qty']);
                if ($rpt['st_qty'] < 1 && ($rpt['pr_qty'] < $rpt['mr_qty'])) {
                    printf("<td align='center'><input type='checkbox' value='%d|%s' name='cpr[]' checked='checked'></td>",$rpt['id'],$rpt['qstock']);
                }else{
                    printf("<td align='center'><input type='checkbox' value='%d|%s' name='cpr[]'></td>",$rpt['id'],$rpt['qstock']);
                }
                if ($rpt['st_qty'] > 0 && ($rpt['is_qty'] < $rpt['mr_qty'])) {
                    printf("<td align='center'><input type='checkbox' value='%d|%s' name='cis[]' checked='checked'></td>",$rpt['id'],$rpt['qstock']);
                }else{
                    printf("<td align='center'><input type='checkbox' value='%d|%s' name='cis[]'></td>",$rpt['id'],$rpt['qstock']);
                }
                print("</tr>");
            }
            ?>
        </table>
        <br>
        <div>
            <label for="processDate">Process Date : </label>
            <input type="hidden" name="projectId" value="<?php print($projectId);?>">
            <input type="text" id="processDate" name="processDate" size="10" value="<?php print($processDate == null ? date("d-m-Y") : $processDate); ?>" />
            <button type="submit">PROCESS</button>
        </div>
    </form>
        <?php
    }else{
        ?>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Item Code</th>
                <th>Part Number</th>
                <th>Item Name</th>
                <th>UOM</th>
                <th>MR Qty</th>
                <th>PR Qty</th>
                <th>IS Qty</th>
                <th>Stock Qty</th>
            </tr>
            <?php
            $nrt = 0;
            $dep = null;
            $mrn = null;
            $mrd = null;
            while ($rpt = $report->FetchAssoc()) {
                $nrt++;
                print("<tr>");
                printf("<td align='center'>%d</td>",$nrt);
                printf("<td>%s</td>",$rpt['item_code']);
                printf("<td>%s</td>",$rpt['part_no']);
                printf("<td>%s</td>",$rpt['item_name']);
                printf("<td>%s</td>",$rpt['uom_cd']);
                printf("<td align='right'>%s</td>",$rpt['smr_qty']);
                printf("<td align='right'>%s</td>",$rpt['spr_qty']);
                printf("<td align='right'>%s</td>",$rpt['sis_qty']);
                printf("<td align='right'>%s</td>",$rpt['stk_qty']);
                print("</tr>");
            }
            ?>
        </table>
    <?php
    }
    ?>
</div>
<?php } ?>

</body>
</html>
