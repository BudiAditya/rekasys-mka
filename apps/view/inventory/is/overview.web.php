<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Item Issue Report </title>
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
    <legend><span class="bold">Report Data Item Issue</span></legend>

    <form action="<?php print($helper->site_url("inventory.is/overview")); ?>" method="get">
        <div class="center">
            <label for="project">Project : </label>
            <select id="project" name="project">
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
            </select>

            <label for="dept">Dept : </label>
            <select id="dept" name="dept">
                <option value="-1">-- All Dept --</option>
                <?php
                foreach ($dept as $row) {
                    $id = $row->Id;
                    $name = $row->DeptName;
                    echo "<option value='$id'>$name</option>";
                }
                ?>
            </select>

            <label for="status">Status : </label>
            <select id="status" name="status">
                <option value="-1">-- All Status --</option>
                <?php
                foreach ($is_status as $row) {
                    $code = $row->Code;
                    $desc = $row->ShortDesc;
                    echo "<option value='$code'>$desc</option>";
                }
                ?>
            </select>

            <label for="startDate">Tgl : </label>
            <input type="text" id="startDate" name="startDate" size="10" value="<?php print(date(JS_DATE, $startDate)); ?>" />

            <label for="endDate">s.d </label>
            <input type="text" id="endDate" name="endDate" size="10" value="<?php print(date(JS_DATE, $endDate)); ?>" />

            <label for="output">Output : </label>
            <select id="output" name="output">
                <option value="web">Web</option>
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
    <div class="bold center">
        <span class="title">Report Data Item Issue</span><br />
        <span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
        <span class="subTitle">Status : <?php echo $statusName; ?></span><br />
        <p>&nbsp;</p>

        <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
            <tr class="bold center">
                <td class="bN bE bS bW">No.</td>
                <td class="bN bE bS">Project</td>
                <td class="bN bE bS">Departemen</td>
                <td class="bN bE bS">Activity</td>
                <td class="bN bE bS">Unit</td>
                <td class="bN bE bS">Issue No.</td>
                <td class="bN bE bS">Date</td>
                <td class="bN bE bS">Costs</td>
                <td class="bN bE bS">Status</td>
                <td class="bN bE bS">Last Update</td>
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
                    <td class="left bE bS"><?php echo $rs["dept"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["act_code"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["unit_code"]; ?></td>
                    <td class="left bE bS"><a href="<?php echo site_url("inventory.is/view/" . $rs["id"])?>" target="_blank"><?php echo $rs["doc_no"]; ?></a></td>
                    <td class="left bE bS"><?php echo date('d M Y', strtotime($rs["issue_date"])); ?></td>
                    <td class="right bE bS"><?php echo  number_format($rs["costs"]); ?></td>
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
