<!DOCTYPE HTML>
<html>
<?php /** @var $notifications NotificationGroup[] */ ?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Rekasys - PT. Manado Karya Anugrah</title>

	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>

<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>

<br/>
<br/>
<br/>
<br/>
<hr>
<div align="center">
    <p style="color:darkblue;font-size: x-large;font-weight: bold">REKASYS - INTEGRATED ACCOUNTING SYSTEM</p>
</div>
<table align="center" width="100%" border="0" cellpadding="4" cellspacing="0">
    <td align="center">
        <img src="<?php print(base_url('public/images/company/mka.png'));?>" width="600" height="300"></td>
    </td>
</table>
<hr>
<div class="text1" align="center">Your IP Address: <b><?php echo "<span class='text2'>" . getenv("REMOTE_ADDR") . "</span>"; ?></b></div>
<div class="text1" align="center">Support & Help Desk: <b>WA 08114319858, 081244138229</b> Email: <b>support@rekasys.com</b></div>
<div class="text1" align="center">(c) 2019 - <a href="https://rekasys.com">https://rekasys.com</a></div>
<!--
<div id="notifications" class="subTitle" style="border: dotted #000000 1px; margin: 10px 20px; padding: 10px;">
	<div class="bold">Pengumuman</div>

</div>
-->
</body>
</html>
