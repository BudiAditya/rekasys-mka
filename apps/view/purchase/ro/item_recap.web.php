<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Rekap Barang PO</title>
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
    <legend><span class="bold">Report Rekap Barang PO</span></legend>

    <form action="<?php print($helper->site_url("purchase.po/item_recap")); ?>" method="get">
        <div class="center">
            <label for="status">Status Dokumen: </label>
            <select id="status" name="status">
                <?php
                foreach ($po_status as $row) {
                    $code = $row->Code;
                    $desc = $row->ShortDesc;
                    if ($code == 3) {
                        echo "<option value='$code' selected>$desc</option>";
                    } else {
                        echo "<option value='$code'>$desc</option>";
                    }
                }
                ?>
            </select>

            <label for="startDate">Tgl. Dokumen : </label>
            <input type="text" id="startDate" name="startDate" value="<?php print(date(JS_DATE, $startDate)); ?>" />

            <label for="endDate">s.d </label>
            <input type="text" id="endDate" name="endDate" value="<?php print(date(JS_DATE, $endDate)); ?>" />

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
        <span class="title">Report Data Rekap Barang PO</span><br />
        <span class="subTitle">Tanggal <?php print(date(HUMAN_DATE, $startDate)); ?> s/d <?php print(date(HUMAN_DATE, $endDate)); ?></span><br />
        <span class="subTitle">Status : <?php echo $statusName->ShortDesc; ?></span><br />
        <p>&nbsp;</p>

        <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
            <tr class="bold center">
                <td class="bN bE bS bW">No.</td>
                <td class="bN bE bS">Kode Barang</td>
                <td class="bN bE bS">Nama Barang</td>
                <td class="bN bE bS">Jumlah</td>
                <td class="bN bE bS">Satuan</td>
                <td class="bN bE bS">Harga</td>
            </tr>

            <?php
            $i = 0;
            $total = 0;
            while($rs = $report->fetch_assoc()) {
                $i++;
                $total = $total + $rs["total"];
            ?>
                <tr>
                    <td class="center bE bS bW"><?php echo $i; ?></td>
                    <td class="left bE bS"><?php echo $rs["item_code"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["item_name"]; ?></td>
                    <td class="right bE bS"><?php echo $rs["jumlah"]; ?></td>
                    <td class="left bE bS"><?php echo $rs["uom_cd"]; ?></td>
                    <td class="right bE bS"><?php echo "Rp . " . number_format($rs["total"], 0,",","."); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td class="right bE bS bW" colspan="5">TOTAL</td>
                <td class="right bE bS" colspan="5"><?php echo "Rp . " . number_format($total, 0,",","."); ?></td>
            </tr>

            </tr>
        </table>
    </div>
</div>
    <?php } ?>

</body>
</html>
