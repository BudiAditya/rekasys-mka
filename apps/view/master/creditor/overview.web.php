<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Creditor Master Report </title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
    <legend><span class="bold">Report Data Creditor</span></legend>

    <form action="<?php print($helper->site_url("master.creditor/overview")); ?>" method="get">
        <div class="center">
            <label for="jenis">Jenis Creditor : </label>
            <select id="jenis" name="jenis">
                <option value="-1">-- SEMUA JENIS CREDITOR --</option>
                <?php
                foreach ($type as $row) {
                    $id = $row->Id;
                    $desc = $row->CreditorTypeDesc;
                    echo "<option value='$id'>$desc</option>";
                }
                ?>
            </select>

            <label for="output">Output laporan: </label>
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
        <span class="title">Report Data Creditor</span><br />
        <span class="subTitle">Jenis Creditor : <?php echo $typeDesc; ?></span><br />
        <p>&nbsp;</p>

        <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
            <tr class="bold center">
                <td class="bN bE bS bW">No.</td>
                <td class="bN bE bS">Company</td>
                <td class="bN bE bS">Kode</td>
                <td class="bN bE bS">Jenis</td>
                <td class="bN bE bS">Nama Creditor</td>
                <td class="bN bE bS">Alamat</td>
                <td class="bN bE bS">Core Business</td>
            </tr>

            <?php
            $i = 0;
            while($rs = $report->fetch_assoc()) {
                $i++;
                ?>

                <tr>
                    <td class="center bE bS bW"><?php echo $i; ?></td>
                    <td class="left bE bS"><?php echo $rs["entity"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["creditor_cd"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["type_desc"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["creditor_name"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["address1"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["core_business"]; ?></td>
                </tr>
                <?php }?>
            </tr>
        </table>
    </div>
</div>
    <?php } ?>

</body>
</html>
