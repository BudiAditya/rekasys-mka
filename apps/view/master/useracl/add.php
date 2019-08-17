<!DOCTYPE HTML>
<html>
<head>
	<title>REKASYS - User Access Control</title>
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
	<script type="text/javascript">
		$(document).ready(function () {
			//var elements = ["UserId", "UserName", "UserEmail","UserPwd1","UserPwd2","EntityId", "ProjectId","UserLvl","AllowMultipleLogin","IsAktif","BtSimpan"];
			//BatchFocusRegister(elements);

			$("#btLoad").click(function(){
				var pri = $("#xProjectId").combobox('getValue');
				var url = "<?php printf($helper->site_url('master.useracl/add/%d/'), $userdata->UserUid);?>"+pri;
				location.href = url;
			});

			$('#xProjectId').combobox({
				onChange: function(data){
				    console.log(data);
					$("#ProjectId").val(data);
					$("#tProjectId").val(data);
				}
			});
		});
	</script>
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
	<legend><b>User Access Control: <?php print(strtoupper($userdata->UserId));?></b></legend>
    <form action="<?php printf($helper->site_url('master.useracl/copy/%s'), $userdata->UserUid); ?>" method="post">
		<input type="hidden" id="tProjectId" name="tProjectId" value="0"/>
        <div align="left">Copy Hak Akses dari User:
        <select id="copyFrom" name="copyFrom">
            <option value="">--</option>
            <?php
			while ($row = $userlist->FetchAssoc()) {
				printf("<option value='%s'>%s [%s]</option>",$row['user_uid'].'|'.$row['project_id'],$row['user_id'],$row['project_code']);
			}
            ?>
        </select>
        <button type="submit">Copy</button>
		&nbsp;
		--> Pilih dulu Hak Akses Sumber -> Pilih Hak Akses Project Tujuan -> Klik -Copy- untuk menyalin hak akses.
        </div>
    </form>
    <br>
	<form id="frm" action="<?php printf($helper->site_url('master.useracl/add/%s'), $userdata->UserUid); ?>" method="post">
		<div>
			<input type="hidden" name="ProjectId" id="ProjectId" value="<?php print($uproId);?>"/>
			<label for="xProjectId">Hak Akses Project:</label>
			<select class="easyui-combobox" name="xProjectId" id="xProjectId" required>
				<option value="0">--PILIH PROJECT--</option>
				<?php
                    /** @var $projects Project[] */
                    /** @var $userdata UserAdmin */
					$upro = $userdata->AProjectId;
					foreach ($projects as $pro){
						if (strstr($upro,$pro->Id)) {
							if ($pro->Id == $uproId){
								printf('<option value="%d" selected="selected">%s</option>', $pro->Id, $pro->ProjectName);
							}else {
								printf('<option value="%d">%s</option>', $pro->Id, $pro->ProjectName);
							}
						}
					}
				?>
			</select>
			&nbsp;
			<input type="button" id="btLoad" value="1 - Load Data"/>
			&nbsp;
			2 - Edit Hak Akses
			&nbsp;
			<button type="submit" class="bold">3 - Simpan Data</button>
			&nbsp;
			<a href="<?php print($helper->site_url("master.useradmin")); ?>" class="button">Daftar User</a>
		</div>
		<br/>
		<div class="easyui-accordion" style="width:800px;">
			<?php
			$m1 = "";
			$akses = null;
			foreach ($resources as $menu) {
				if ($m1 != $menu->MenuName) {
					if ($m1 != "") {
						print('</table>');
						print('</div>');
					}

					$m1 = $menu->MenuName;
					//printf('<h3><a href="#">%s</a></h3>', $menu->MenuName);
					printf('<div title="%s" style="overflow:auto;padding:10px;">',strtoupper($menu->MenuName));
					print('<table border="1" cellpadding="0" cellspacing="0" class="smallTablePadding">');
					print('<tr class="text2"><th>No.</th><th>Resource Name</th><th>Add</th><th>Edit</th><th>Delete</th><th>View</th><th>Print</th><th>Approve</th><th>Verify</th><th>Post</th><th>All</th></tr>');
					//print('<tr class="text2"><th>No.</th><th>Resource Name</th><th>Add</th><th>Edit</th><th>Delete</th><th>View</th><th>Print</th><th>Approve</th><th>All</th></tr>');
				}

				if (isset($hak[$menu->ResourceId])) {
					$akses = $hak[$menu->ResourceId];
				} else {
					$akses = null;
				}

				print('<tr>');
				printf('<td align="center" class="text2">%d</td>', $menu->ResourceSeq);
				printf('<td class="text2">%s</td>', $menu->ResourceName);
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|1" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "1") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|2" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "2") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|3" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "3") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|4" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "4") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|5" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "5") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|6" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "6") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|7" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "7") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|8" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "8") !== false) ? 'checked="checked"' : '');
				printf('<td align="center"><input type="checkbox" name="hakakses[]" value="%s|9" %s /></td>', $menu->ResourceId, ($akses != null && strpos($akses->Rights, "9") !== false) ? 'checked="checked"' : '');
				print('</tr>');
			}

			// Hmm spt biasa yang terakhir tidak ter print untuk tag close nya
			if ($m1 != "") {
				print("</table></div>");
			}
			?>
		</div>
	</form>
</fieldset>
</body>
</html>
