<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Rekap Barang GN</title>
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
    <legend><span class="bold">Report Rekap Barang GN</span></legend>

    <form action="<?php print($helper->site_url("inventory.gn/item_recap")); ?>" method="get">
        <div>
            <label for="projectId">Project : </label>
            <select id="projectId" name="projectId">
                <?php
                if ($userLevel > 4) {
                    print('<option value="0">-- ALL PROJECT --</option>');
                }
                /** @var $projects Project[] */
                $projectName = null;
                foreach ($projects as $project) {
                    if ($project->Id == $projectId){
                        $projectName = $project->ProjectName;
                        printf("<option value='%d' selected='selected'>%s - %s</option>",$project->Id,$project->ProjectCd,$project->ProjectName);
                    }else{
                        printf("<option value='%d'>%s - %s</option>",$project->Id,$project->ProjectCd,$project->ProjectName);
                    }
                }
                ?>
            </select>

            <label for="startDate">Tanggal : </label>
            <input type="text" id="startDate" name="startDate" size="10" value="<?php print(date(JS_DATE, $startDate)); ?>" />

            <label for="endDate">s.d </label>
            <input type="text" id="endDate" name="endDate" size="10" value="<?php print(date(JS_DATE, $endDate)); ?>" />

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
    <div class="bold center">
        <span class="title">Rekap Penerimaan Barang (GN)</span><br />
        <span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
        <span class="subTitle">Project : <?php echo $projectName; ?></span><br />
        <p>&nbsp;</p>

        <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
            <tr>
                <th>No.</th>
                <th>Kode</th>
                <th>Part Number</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Satuan</th>
            </tr>

            <?php
            $i = 0;
            while($rs = $report->fetch_assoc()) {
                $i++;
                ?>
                <tr>
                    <td class="center"><?php echo $i; ?></td>
                    <td><?php echo $rs["item_code"]; ?></td>
                    <td><?php echo $rs["part_no"]; ?></td>
                    <td><?php echo $rs["item_name"]; ?></td>
                    <td><?php echo $rs["jumlah"]; ?></td>
                    <td><?php echo $rs["uom_cd"]; ?></td>
                </tr>
                <?php } ?>
        </table>
    </div>
</div>
    <?php } ?>

</body>
</html>
