<!DOCTYPE HTML>
<?php
/** @var $employee Employee */
/** @var $depts Department[] */
?>
<html>
<head>
	<title>Rekasys - Add New Employee</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
            var elements = ["EntityId","Nik","Nama","NmPanggilan","DeptId","Jabatan","Alamat","Handphone","T4Lahir","TglLahir","Jkelamin","Agama","Pendidikan","Status","BpjsNo","BpjsDate","MulaiKerja","ResignDate","btSubmit"];
			BatchFocusRegister(elements);
            $("#TglLahir").customDatePicker({ showOn: "focus" });
            $("#MulaiKerja").customDatePicker({ showOn: "focus" });
            $("#BpjsDate").customDatePicker({ showOn: "focus" });
            $("#ResignDate").customDatePicker({ showOn: "focus" });
		});
	</script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>

<br/>
<fieldset>
	<legend><b>Add New Employee</b></legend>
	<form id="frm" action="<?php print($helper->site_url("hr.employee/add")); ?>" method="post" enctype="multipart/form-data">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td class="right">Company :</td>
				<td colspan="2"><select name="EntityId" class="text2" id="EntityId" autofocus required>
					<?php
					foreach ($companies as $sbu) {
						if ($sbu->EntityId == $employee->EntityId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $sbu->EntityId, $sbu->EntityCd, $sbu->CompanyName);
						} else {
							printf('<option value="%d">%s - %s</option>', $sbu->EntityId, $sbu->EntityCd, $sbu->CompanyName);
						}
					}
					?>
				</select></td>
                <td class="right">N I K :</td>
                <td><input type="text" class="text2" name="Nik" id="Nik" maxlength="10" size="20" value="<?php print($employee->Nik); ?>" pattern="^\s*([1-9][0-9]{3})\s*$" required /></td>
                <td rowspan="7" colspan="2" class="center">
                    <?php
                    printf('<img src="%s" width="200" height="200"/>',$helper->site_url($employee->Fphoto));
                    ?>
                </td>
			</tr>
            <tr>
                <td class="right">Full Name :</td>
                <td colspan="2"><input type="text" class="text2" name="Nama" id="Nama" maxlength="50" size="50" value="<?php print($employee->Nama); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
                <td class="right">Nick Name :</td>
                <td><input type="text" class="text2" name="NmPanggilan" id="NmPanggilan" maxlength="50" size="20" value="<?php print($employee->NmPanggilan); ?>" required /></td>
            </tr>
			<tr>
                <td class="right">Department :</td>
                <td colspan="2"><select name="DeptId" class="text2" id="DeptId" required>
                        <option value=""></option>
                        <?php
                        foreach ($depts as $dept) {
                            if ($dept->Id == $employee->DeptId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                            }
                        }
                        ?>
                    </select></td>
                <td class="right">Level :</td>
                <td><select name="Jabatan" class="text2" id="Jabatan">
                        <option value=""></option>
                        <option value="STF" <?php ($employee->Jabatan == "STF" ? print('selected = "selected"'):'');?>>Staf</option>
                        <option value="SPV" <?php ($employee->Jabatan == "SPV" ? print('selected = "selected"'):'');?>>Dept Head</option>
                        <option value="MGR" <?php ($employee->Jabatan == "MGR" ? print('selected = "selected"'):'');?>>Manager</option>
                        <option value="DIR" <?php ($employee->Jabatan == "DIR" ? print('selected = "selected"'):'');?>>Director</option>
                        <option value="PRD" <?php ($employee->Jabatan == "PRD" ? print('selected = "selected"'):'');?>>President Director</option>
                    </select>
                </td>
			</tr>

            <tr>
                <td class="right">Address :</td>
                <td colspan="4"><input type="text" class="text2" name="Alamat" id="Alamat" maxlength="250" size="90" value="<?php print($employee->Alamat); ?>" /></td>
            </tr>
            <tr>
                <td class="right">Mobile Phone :</td>
                <td colspan="3"><input type="tel" class="text2" name="Handphone" id="Handphone" maxlength="50" size="50" value="<?php print($employee->Handphone); ?>" /></td>
            </tr>
            <tr>
                <td class="right">P O B :</td>
                <td><input type="text" class="text2" name="T4Lahir" id="T4Lahir" maxlength="50" size="20" value="<?php print($employee->T4Lahir); ?>" /></td>
                <td class="right">D O B :</td>
                <td><input type="text" class="text2" name="TglLahir" id="TglLahir" maxlength="10" size="15" value="<?php print($employee->FormatTglLahir(JS_DATE)); ?>" /></td>
                <td class="right">Gender :
                    &nbsp;&nbsp;
                   <select name="Jkelamin" class="text2" id="Jkelamin" required style="width: 130px;">
                        <option value=""></option>
                        <option value="L" <?php ($employee->Jkelamin == "L" ? print('selected = "selected"'):'');?>>Laki-laki</option>
                        <option value="P" <?php ($employee->Jkelamin == "P" ? print('selected = "selected"'):'');?>>Perempuan</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right">Religion :</td>
                <td><select name="Agama" class="text2" id="Agama" style="width: 125px">
                        <option value=""></option>
                        <option value="Budha" <?php ($employee->Agama == "Budha" ? print('selected = "selected"'):'');?>>Budha</option>
                        <option value="Hindu" <?php ($employee->Agama == "Hindu" ? print('selected = "selected"'):'');?>>Hindu</option>
                        <option value="Islam" <?php ($employee->Agama == "Islam" ? print('selected = "selected"'):'');?>>Islam</option>
                        <option value="Katolik" <?php ($employee->Agama == "Katolik" ? print('selected = "selected"'):'');?>>Katolik</option>
                        <option value="Kristen" <?php ($employee->Agama == "Kristen" ? print('selected = "selected"'):'');?>>Kristen</option>
                    </select>
                </td>
                <td class="right">Education :</td>
                <td><select name="Pendidikan" class="text2" id="Pendidikan" style="width: 100px;">
                        <option value=""></option>
                        <option value="SD" <?php ($employee->Pendidikan == "SD" ? print('selected = "selected"'):'');?>>SD</option>
                        <option value="SMP" <?php ($employee->Pendidikan == "SMP" ? print('selected = "selected"'):'');?>>SMP</option>
                        <option value="SMA" <?php ($employee->Pendidikan == "SMA" ? print('selected = "selected"'):'');?>>SMA</option>
                        <option value="Diploma" <?php ($employee->Pendidikan == "Diploma" ? print('selected = "selected"'):'');?>>Diploma</option>
                        <option value="Sarjana" <?php ($employee->Pendidikan == "Sarjana" ? print('selected = "selected"'):'');?>>Sarjana</option>
                    </select>
                </td>
                <td class="right">Status :
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <select name="Status" class="text2" id="Status" style="width: 130px;">
                        <option value=""></option>
                        <option value="TK" <?php ($employee->Status == "TK" ? print('selected = "selected"'):'');?>>TK - Tidak Kawin</option>
                        <option value="K0" <?php ($employee->Status == "K0" ? print('selected = "selected"'):'');?>>K/0 - Kawin 0 Anak</option>
                        <option value="K1" <?php ($employee->Status == "K1" ? print('selected = "selected"'):'');?>>K/1 - Kawin 1 Anak</option>
                        <option value="K2" <?php ($employee->Status == "K2" ? print('selected = "selected"'):'');?>>K/2 - Kawin 2 Anak</option>
                        <option value="K3" <?php ($employee->Status == "K3" ? print('selected = "selected"'):'');?>>K/3 - Kawin 3 Anak</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right">No. BPJS</td>
                <td><input type="text" class="text2" name="BpjsNo" id="BpjsNo" maxlength="15" size="20" value="<?php print($employee->BpjsNo); ?>" /></td>
                <td class="right">BPJS Date</td>
                <td><input type="text" class="text2" name="BpjsDate" id="BpjsDate" maxlength="15" size="15" value="<?php print($employee->FormatBpjsDate(JS_DATE)); ?>" /></td>
                <td>&nbsp;</td>
                <td>File Photo</td>
                <td><input type="file" class="text2" name="FileName" id="FileName" accept="image/*" /></td>
            </tr>
            <tr>
                <td class="right">Start Date :</td>
                <td><input type="text" class="text2" name="MulaiKerja" id="MulaiKerja" maxlength="20" size="20" value="<?php print($employee->FormatMulaiKerja(JS_DATE)); ?>" /></td>
                <td class="right">Resign Date :</td>
                <td><input type="text" class="text2" name="ResignDate" id="ResignDate" maxlength="15" size="15" value="<?php print($employee->FormatResignDate(JS_DATE)); ?>" /></td>
            </tr>
			<tr>
                <td>&nbsp;</td>
				<td colspan="3">
					<button id="btSubmit" type="submit">Submit</button>
					<a href="<?php print($helper->site_url("hr.employee")); ?>" class="button">Employee List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
