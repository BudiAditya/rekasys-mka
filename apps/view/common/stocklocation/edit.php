<!DOCTYPE html>
<html>
<head>
    <title>Rekasys - Tambah Data Lokasi Stock</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["BinCode", "LocName", "Description", "Submit"];
            BatchFocusRegister(elements);
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
    <legend><b>Ubah Data Lokasi Stock</b></legend>
    <form id="frm" action="<?php print($helper->site_url("common.stocklocation/edit/".$stocklocation->Id)); ?>" method="post">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <td>Kode Bind</td>
                <td><input type="text" name="BinCode" size="25" class="text2" id="BinCode" value="<?php print($stocklocation->BinCode); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td>Lokasi</td>
                <td colspan="2"><input type="text" name="LocName" size="50" class="text2" id="LocName" value="<?php print($stocklocation->LocName); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><input type="text" name="Description" size="50" class="text2" id="Description" value="<?php print($stocklocation->Description); ?>"/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button id="Submit" type="submit">UPDATE</button>
                    <a href="<?php print($helper->site_url("common.stocklocation")); ?>">Daftar Lokasi Stock</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
</body>
</html>
