<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Material Requisition Report </title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#startDate").customDatePicker();
            $("#endDate").customDatePicker();
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
    <legend><span class="bold">Report Data Material Requisition</span></legend>

    <form action="<?php print($helper->site_url("inventory.mr/overview")); ?>" method="get">
        <div>
            <label for="projectId">Project : </label>
            <select id="projectId" name="projectId">
                <?php
                if ($userLevel > 4) {
                    print('<option value="0">-- ALL PROJECT --</option>');
                }
                foreach ($projects as $project) {
                    if ($project->Id == $projectId){
                        printf("<option value='%d' selected='selected'>%s - %s</option>",$project->Id,$project->ProjectCd,$project->ProjectName);
                    }else{
                        printf("<option value='%d'>%s - %s</option>",$project->Id,$project->ProjectCd,$project->ProjectName);
                    }
                }
                ?>
            </select>

            <label for="status">MR Status : </label>
            <select id="status" name="status">
                <option value="-1">-- ALL STATUS --</option>
                <?php
                foreach ($mr_status as $row) {
                    $code = $row->Code;
                    $desc = $row->ShortDesc;
                    echo "<option value='$code'>$desc</option>";
                }
                ?>
            </select>

            <label for="startDate">Tgl : </label>
            <input type="text" id="startDate" name="startDate" value="<?php print(date(JS_DATE, $startDate)); ?>" size="10"/>

            <label for="endDate">s.d </label>
            <input type="text" id="endDate" name="endDate" value="<?php print(date(JS_DATE, $endDate)); ?>" size="10"/>

            <label for="output">Output : </label>
            <select id="output" name="output">
                <option value="web">Web Browser</option>
                <option value="xls">Excel</option>
                <option value="pdf">PDF</option>
            </select>

            <button type="submit">Generate</button>
        </div>
    </form>
</fieldset>

<?php if($report != null) { ?>
<br />
<div class="container">
    <div>
        <span class="title">Report Data Material Requisition</span><br />
        <span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
        <span class="subTitle">Status : <?php echo $statusName; ?></span><br />
        <p>&nbsp;</p>

        <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0;">
            <tr>
                <th>No.</th>
                <th>Project</th>
                <th>MR Number</th>
                <th>MR Date</th>
                <th>Status</th>
                <th>Last Update</th>
            </tr>

            <?php
            $i = 0;
            while($rs = $report->fetch_assoc()) {
                $i++;
                $updated = $rs["update_time"] != null ? date('d M Y', strtotime($rs["update_time"])) : "";
                ?>

                <tr>
                    <td class="center bE bS bW"><?php echo $i; ?></td>
                    <td class="left bE bS"><?php echo $rs["project_name"]; ?></td>
                    <td class="left bE bS"><a href="<?php echo site_url("inventory.mr/view/" . $rs["id"])?>" target="_blank"><?php echo $rs["doc_no"]; ?></a></td>
                    <td class="left bE bS"><?php echo date('d M Y', strtotime($rs["mr_date"])); ?></td>
                    <td class="left bE bS"><?php echo $rs["status_name"]; ?></td>
                    <td class="left bE bS"><?php echo $updated; ?></td>
                </tr>
                <?php }?>
            </tr>
        </table>
    </div>
</div>
    <?php } ?>

</body>
</html>
