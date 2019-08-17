<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Tambah Data Dokumen</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["DocTypeId", "TrxMonth", "TrxYear", "Counter","IsLocked"];
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
	<legend><b>Tambah Data Counter Dokumen</b></legend>
	<form id="frm" action="<?php print($helper->site_url("common.doccounter/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Company</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td>Kode</td>
                <td><select name="DocTypeId" class="text2" id="DocTypeId">
                    <option value=""></option>
                    <?php
                    foreach ($doctypes as $doctype) {
                        if ($doctype->Id == $doccounter->DocTypeId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $doctype->Id, $doctype->DocCode, $doctype->Description);
                        } else {
                            printf('<option value="%d">%s - %s</option>',  $doctype->Id, $doctype->DocCode, $doctype->Description);
                        }
                    }
                    ?>
                </select>
                </td>
			</tr>
			<tr>
				<td>Bulan</td>
                <td><input type="text" name="TrxMonth" id="TrxMonth" maxlength="2" size="4" class="text2" value="<?php print($doccounter->TrxMonth); ?>" /></td>
			</tr>
			<tr>
				<td>Tahun</td>
				<td><input type="text" name="TrxYear" id="TrxYear" maxlength="4" size="4" class="text2" value="<?php print($doccounter->TrxYear); ?>" /></td>
			</tr>
			<tr>
				<td>Counter</td>
				<td><input type="text" name="Counter" id="Counter" maxlength="6" size="8" class="text2" value="<?php print($doccounter->Counter); ?>" /></td>
			</tr>
            <tr>
                <td>Status</td>
                <td><select name="IsLocked" id="IsLocked">
                    <option value="0">Open</option>
                    <option value="1">Locked</option>
                </select>

                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.doccounter")); ?>" class="button">Daftar Counter Dokumen</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
