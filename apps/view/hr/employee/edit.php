<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $employee Employee */
/** @var $depts Department[] */
?>
<head>
    <title>REKASYS | Edit Employee</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/sweetalert.min.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php }
?>
<br />
<div id="p" class="easyui-panel" title="Edit Employee" style="width:100%;height:auto;padding:5px;" data-options="footer:'#ft'">
    <form id="frm" action="<?php print($helper->site_url("hr.employee/edit/".$employee->Id)); ?>" method="post" enctype="multipart/form-data" novalidate>
    <table cellpadding="1" cellspacing="1" align="left" style="font-size: 12px;font-family: tahoma">
        <tr>
            <td class="right">Department :</td>
            <td><select class="easyui-combobox" id="DeptId" name="DeptId" style="width: 200px" required>
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
                </select>
            </td>
            <td class="right">Start Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 110px" id="MulaiKerja" name="MulaiKerja" value="<?php print($employee->FormatMulaiKerja(SQL_DATEONLY));?>" required data-options="formatter:myformatter,parser:myparser"/></td>
            <td class="right">ID Badge :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="BadgeId" name="BadgeId" value="<?php print($employee->BadgeId);?>" required/></td>
            <td class="right">N I K :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Nik" name="Nik" value="<?php print($employee->Nik);?>"/></td>
            <td class="right">NPWP :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Npwp" name="Npwp" value="<?php print($employee->Npwp);?>"/></td>
        </tr>
        <tr>
            <td class="right">Full Name :</td>
            <td><input class="easyui-textbox" style="width: 200px" id="Nama" name="Nama" value="<?php print($employee->Nama);?>" required/></td>
            <td class="right">D O B :</td>
            <td><input type="text" class="easyui-datebox" style="width: 110px" id="TglLahir" name="TglLahir" value="<?php print($employee->FormatTglLahir(SQL_DATEONLY));?>" required data-options="formatter:myformatter,parser:myparser"/></td>
            <td class="right">P O B :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="T4Lahir" name="T4Lahir" value="<?php print($employee->T4Lahir);?>"/></td>
            <td class="right">Gender :</td>
            <td><select class="easyui-combobox" style="width: 110px" id="Jkelamin" name="Jkelamin">
                    <option value="L" <?php print($employee->Jkelamin == "L" ? "selected = 'selected'" : "");?>>LAKI</option>
                    <option value="P" <?php print($employee->Jkelamin == "P" ? "selected = 'selected'" : "");?>>PEREMPUAN</option>
                </select>
            </td>
            <td class="right">Tax Status :</td>
            <td><select class="easyui-combobox" style="width: 110px" id="StsPajak" name="StsPajak">
                    <option value="TK" <?php print($employee->StsPajak == "TK" ? "selected = 'selected'" : "");?>>TK</option>
                    <option value="K/0" <?php print($employee->StsPajak == "K/0" ? "selected = 'selected'" : "");?>>K/0</option>
                    <option value="K/1" <?php print($employee->StsPajak == "K/1" ? "selected = 'selected'" : "");?>>K/1</option>
                    <option value="K/2" <?php print($employee->StsPajak == "K/2" ? "selected = 'selected'" : "");?>>K/2</option>
                    <option value="K/3" <?php print($employee->StsPajak == "K/3" ? "selected = 'selected'" : "");?>>K/3</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right">Address :</td>
            <td><input class="easyui-textbox" style="width: 200px" id="Alamat" name="Alamat" value="<?php print($employee->Alamat);?>"/></td>
            <td class="right">Job Title :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Jabatan" name="Jabatan" value="<?php print($employee->Jabatan);?>"/></td>
            <td class="right">Bagian :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Bagian" name="Bagian" value="<?php print($employee->Bagian);?>"/></td>
            <td class="right">Education :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Pendidikan" name="Pendidikan" value="<?php print($employee->Pendidikan);?>"/></td>
            <td class="right">Grade :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Gol" name="Gol" value="<?php print($employee->Gol);?>"/></td>
        </tr>
        <tr>
            <td class="right">E-Mail :</td>
            <td><input class="easyui-textbox" style="width: 200px" id="Email" name="Email" value="<?php print($employee->Email);?>"/></td>
            <td class="right">Phone :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="NoHp" name="NoHp" value="<?php print($employee->NoHp);?>"/></td>
            <td class="right">P O H :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Poh" name="Poh" value="<?php print($employee->Poh);?>"/></td>
            <td class="right">Religion :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="Agama" name="Agama" value="<?php print($employee->Agama);?>"/></td>
            <td class="right">Mom's Name :</td>
            <td colspan="2"><input type="password" class="easyui-textbox" style="width: 110px" id="NmIbuKandung" name="NmIbuKandung" value="<?php print($employee->NmIbuKandung);?>"/></td>
        </tr>
        <tr>
            <td class="right">Bank Name :</td>
            <td><input class="easyui-textbox" style="width: 200px" id="Bank" name="Bank" value="<?php print($employee->Bank);?>"/></td>
            <td class="right">A/C :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="NoRek" name="NoRek" value="<?php print($employee->NoRek);?>"/></td>
            <td class="right">BPJS Kes No. :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="NoBpjsKes" name="NoBpjsKes" value="<?php print($employee->NoBpjsKes);?>"/></td>
            <td class="right">BPJS TK No. :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="NoBpjsTk" name="NoBpjsTk" value="<?php print($employee->NoBpjsTk);?>"/></td>
            <td class="right">Inhealth No. :</td>
            <td><input class="easyui-textbox" style="width: 110px" id="NoInhealth" name="NoInhealth" value="<?php print($employee->NoInhealth);?>"/></td>
        </tr>
        <tr>
            <td class="right">Employee Status :</td>
            <td><select class="easyui-combobox" style="width: 200px" id="StsKaryawan" name="StsKaryawan">
                    <option value="PC" <?php print($employee->StsKaryawan == "PC" ? "selected = 'selected'" : "");?>>PERCOBAAN</option>
                    <option value="KO" <?php print($employee->StsKaryawan == "KO" ? "selected = 'selected'" : "");?>>KONTRAK</option>
                    <option value="PK" <?php print($employee->StsKaryawan == "PK" ? "selected = 'selected'" : "");?>>PKWT</option>
                    <option value="PR" <?php print($employee->StsKaryawan == "PR" ? "selected = 'selected'" : "");?>>PERMANENT</option>
                </select>
            </td>
            <td class="right">Status :</td>
            <td><select class="easyui-combobox" style="width: 110px" id="IsAktif" name="IsAktif">
                    <option value="1" <?php print($employee->IsAktif == "1" ? "selected = 'selected'" : "");?>>AKTIF</option>
                    <option value="0" <?php print($employee->IsAktif == "0" ? "selected = 'selected'" : "");?>>NON-AKTIF</option>
                </select>
            </td>
            <td class="right">Picture :</td>
            <td colspan="2"><input type="file" id="Fphoto" name="Fphoto" accept="image/*" onchange="previewFphoto();"></td>
            <td colspan="3">Signature : &nbsp;<input type="file" id="Fsignature" name="Fsignature" accept="image/*" onchange="previewFsignature();"></td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;</td>
            <td colspan="3" rowspan="5"><img id="Fphoto-preview" alt="Picture" src="<?php print($helper->site_url($employee->Fphoto));?>" style="width: 200px;height: 200px;"></td>
            <td colspan="3" rowspan="3"><img id="Fsignature-preview" alt="Signature" src="<?php print($helper->site_url($employee->Fsignature));?>" style="width: 200px;height: 200px;"></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td valign="top">
                <button id="btSubmit" type="submit">UPDATE</button>
                <a href="<?php print($helper->site_url("hr.employee")); ?>">Employee List</a>
            </td>
        </tr>
    </table>
    </form>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2019  PT. Rekasystem Technology
</div>

<script type="text/javascript">
    $( function() {
        $('#Nama').textbox('textbox').css('text-transform','uppercase');

    });

    function previewFphoto() {
        document.getElementById("Fphoto-preview").style.display = "block";
        var oFReader = new FileReader();
        oFReader.readAsDataURL(document.getElementById("Fphoto").files[0]);

        oFReader.onload = function(oFREvent) {
            document.getElementById("Fphoto-preview").src = oFREvent.target.result;
        };
    };

    function previewFsignature() {
        document.getElementById("Fsignature-preview").style.display = "block";
        var oFReader = new FileReader();
        oFReader.readAsDataURL(document.getElementById("Fsignature").files[0]);

        oFReader.onload = function(oFREvent) {
            document.getElementById("Fsignature-preview").src = oFREvent.target.result;
        };
    };

    function myformatter(date){
        var y = date.getFullYear();
        var m = date.getMonth()+1;
        var d = date.getDate();
        return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
    }
    function myparser(s){
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0],10);
        var m = parseInt(ss[1],10);
        var d = parseInt(ss[2],10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
            return new Date(y,m-1,d);
        } else {
            return new Date();
        }
    }
</script>
</body>
</html>
