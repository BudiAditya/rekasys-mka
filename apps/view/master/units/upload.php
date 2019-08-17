<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var */
?>
<head>
	<title>Rekasys - Upload Unit Data List</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
            //var elements = ["Kode", "Cabang","Alamat", "Pic"];
			//BatchFocusRegister(elements);

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
	<legend><b>UNITS DATA UPLOAD PROCESS</b></legend>
    <ol>
        <p>
            <b><u>Perhatian!</u>
            <br>Proses ini akan menimpa/mengupdate Data Unit sebelumnya atau yang sudah ada dengan Unit Code yang sama.
            <br>Jadi pastikan data yang Anda Upload adalah data terbaru.</b>
        </p>
        <li>
            Pastikan anda sudah memiliki template Unit List (file excel) terlebih dahulu.
            <br>
            Bila template tidak ada maka anda harus mendownload template terlebih dahulu.
            <form action="<?php print($helper->site_url("master.units/template")) ?>" method="get">
                <button type="submit">Klik disini untuk Download Template</button>
            </form>
        </li>
        <li>
            Buka file template lalu ikuti prosedur pengisian (pada sheet 'Panduan') untuk mengisi Unit List.
            <br>
            Jika anda sudah mengisinya maka anda dapat langsung menuju proses upload
            <br>
            <form action="<?php print($helper->site_url("master.units/upload")); ?>" method="post" enctype="multipart/form-data">
                Pilih File Template yang sudah diisi/ubah: <input type="file" id="fileUpload" name="fileUpload" required />
                Lalu klik <button type="submit">Upload</button>
                <a href="<?php print($helper->site_url("master.units")); ?>" class="button">Unit List</a>
            </form>
        </li>
    </ol>
</fieldset>
</body>
</html>
