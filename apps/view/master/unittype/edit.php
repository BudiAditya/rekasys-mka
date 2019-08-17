<!DOCTYPE html>
<?php /** @var  $unittype UnitType */?>
<html>
<head>
    <title>Rekasys - Edit Unit Type</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["TypeCode", "TypeInitial", "TypeDesc" ,"Update"];
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

<br/>
<fieldset>
    <legend><b>Edit Unit Type</b></legend>
    <form id="frm" action="<?php print($helper->site_url("master.unittype/edit/".$unittype->Id)); ?>" method="post">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <td class="bold right">Company :</td>
                <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
            </tr>
            <tr>
                <td class="bold right">Code :</td>
                <td><input type="text" class="text2" name="TypeCode" id="TypeCode" maxlength="2" size="3" value="<?php print($unittype->TypeCode); ?>" onkeyup="this.value = this.value.toUpperCase();" required/>&nbsp;<sub>01 ~ 99</sub></td>
            </tr>
            <tr>
                <td class="bold right">Initial :</td>
                <td><input type="text" class="text2" name="TypeInitial" id="TypeInitial" maxlength="5" size="5" value="<?php print($unittype->TypeInitial); ?>" onkeyup="this.value = this.value.toUpperCase();" required/>&nbsp;<sub>*Singkatan jenis unit</sub></td>
            </tr>
            <tr>
                <td class="bold right">Type Name :</td>
                <td><input type="text" class="text2" name="TypeDesc" id="TypeDesc" maxlength="50" size="30" value="<?php print($unittype->TypeDesc); ?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button id="Update" type="submit">Update</button>
                    <a href="<?php print($helper->site_url("master.unittype")); ?>">Unit Type List</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
</body>
</html>
