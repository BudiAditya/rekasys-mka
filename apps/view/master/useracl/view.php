<!DOCTYPE HTML>
<html>
<head>
	<title>ERASYS - View Hak Akses</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>
</head>

<body>
<?php include(VIEW . "main/menu.php"); ?>
<br/>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div>
<?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div>
<?php } ?>

<fieldset>
	<legend><b>Daftar Hak Akses untuk User ID: <?php print($userId);?></b></legend>
	<table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
		<tr>
			<th>Cabang/Outlet/Gudang</th>
			<th>Menu Name</th>
			<th>Modul Name</th>
			<th>Tambah</th>
			<th>Ubah</th>
			<th>Hapus</th>
			<th>Lihat</th>
			<th>Cetak</th>
			<th>Approve</th>
            <th>Verifikasi</th>
            <th>Posting</th>
			<th>Semua</th>
		</tr>
		<?php
		$cbc = null;
		$mnm = null;
		$akses = null;
		while ($row = $aclists->FetchAssoc()) {
			print("<tr>");
			if ($cbc == $row['cabang_code']){
				print("<td>&nbsp;</td>");
			}else{
				printf("<td>%s</td>",$row['cabang_code']);
			}
			if ($mnm == $row['menu_name']){
				print("<td>&nbsp;</td>");
			}else{
				printf("<td>%s</td>",$row['menu_name']);
			}
			printf("<td>%s</td>",$row['resource_name']);
			$akses = $row['rights'];
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="1" %s disabled/></td>', ($akses != null && strpos($akses, "1") !== false) ? 'checked="checked"' : '');
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="2" %s disabled/></td>', ($akses != null && strpos($akses, "2") !== false) ? 'checked="checked"' : '');
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="3" %s disabled/></td>', ($akses != null && strpos($akses, "3") !== false) ? 'checked="checked"' : '');
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="4" %s disabled/></td>', ($akses != null && strpos($akses, "4") !== false) ? 'checked="checked"' : '');
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="5" %s disabled/></td>', ($akses != null && strpos($akses, "5") !== false) ? 'checked="checked"' : '');
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="6" %s disabled/></td>', ($akses != null && strpos($akses, "6") !== false) ? 'checked="checked"' : '');
            printf('<td align="center"><input type="checkbox" name="hakakses[]" value="7" %s disabled/></td>', ($akses != null && strpos($akses, "7") !== false) ? 'checked="checked"' : '');
            printf('<td align="center"><input type="checkbox" name="hakakses[]" value="8" %s disabled/></td>', ($akses != null && strpos($akses, "8") !== false) ? 'checked="checked"' : '');
			printf('<td align="center"><input type="checkbox" name="hakakses[]" value="9" %s disabled/></td>', ($akses != null && strpos($akses, "9") !== false) ? 'checked="checked"' : '');
			print("</tr>");
			$cbc = $row['cabang_code'];
			$mnm = $row['menu_name'];
		}
		?>
	</table>
</fieldset>
</body>
</html>
