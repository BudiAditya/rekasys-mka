<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Good Receipt Note Report </title>
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
    <legend><span class="bold">Report Data Good Receipt Note</span></legend>

    <form action="<?php print($helper->site_url("inventory.gn/overview")); ?>" method="get">
        <div class="center">
            <label for="supplier">Project : </label>
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
            <label for="supplier">Supplier : </label>
            <select id="supplier" name="supplier" style="width:200px">
                <option value="-1">-- SEMUA SUPPLIER --</option>
                <?php
                foreach ($creditorAll as $row) {
                    $id = $row->Id;
                    $name = $row->CreditorName;
                    echo "<option value='$id'>$name</option>";
                }
                ?>
            </select>
            <label for="status">Status : </label>
            <select id="status" name="status">
                <option value="-1">-- SEMUA STATUS --</option>
                <?php
                foreach ($gn_status as $row) {
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
        <span class="title">Report Data Good Receipt Note</span><br />
        <span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
        <span class="subTitle">Supplier : <?php echo $supplierName; ?></span><br />
        <span class="subTitle">Status : <?php echo $statusName; ?></span><br />
        <p>&nbsp;</p>

        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0;">
            <tr>
                <th>No.</th>
                <th>Project</th>
                <th>Supplier</th>
                <th>GN Number</th>
                <th>GN Date</th>
                <th>Status</th>
                <th>PPN ?</th>
                <th>Included ?</th>
                <th>Payment</th>
                <th>Terms</th>
            </tr>

            <?php
            $i = 0;
            while($rs = $report->fetch_assoc()) {
                $i++;

                $ppn = $rs["is_vat"] == 1 ? "Ya" : "Tidak";
                $inc = $rs["is_inc_vat"] == 1 ? "Ya" : "Tidak";
                $pay = $rs["pay_mode"] == 1 ? "CASH" : "KREDIT";
                ?>

                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $rs["warehouse"]; ?></td>
                    <td><?php echo $rs["supplier"]; ?></td>
                    <td><a href="<?php echo site_url("inventory.gn/view/" . $rs["id"])?>" target="_blank"><?php echo $rs["doc_no"]; ?></a></td>
                    <td><?php echo date('d M Y', strtotime($rs["gn_date"])); ?></td>
                    <td><?php echo $rs["status_name"]; ?></td>
                    <td><?php echo $ppn; ?></td>
                    <td><?php echo $inc; ?></td>
                    <td><?php echo $pay; ?></td>
                    <td><?php echo $rs["credit_terms"] . " hari"; ?></td>
                </tr>
                <?php }?>
            </tr>
        </table>
    </div>
</div>
    <?php } ?>

</body>
</html>
