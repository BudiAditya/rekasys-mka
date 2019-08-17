<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - View Company</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>

<br />
<fieldset>
	<legend><b>Company Information</b></legend>
    <table cellpadding="2" cellspacing="1">
        <tr>
            <td class="right bold">Company Code :</td>
            <td><input type="text" name="EntityCd" size="5" maxlength="5" class="text2" id="EntityCd" value="<?php print($company->EntityCd); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">Company Name :</td>
            <td><input type="text" name="CompanyName" size="50" maxlength="150" class="text2" id="CompanyName" value="<?php print($company->CompanyName); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">Office Address :</td>
            <td><input type="text" name="Address" size="50" class="text2" id="Address" value="<?php print($company->Address); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">C i t y :</td>
            <td><input type="text" name="City" size="35" maxlength="50" class="text2" id="City" value="<?php print($company->City); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">Province/State :</td>
            <td><input type="text" name="Province" size="35" maxlength="50" class="text2" id="Province" value="<?php print($company->Province); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">Office Phone :</td>
            <td><input type="text" name="Telephone" size="35" maxlength="50" class="text2" id="Telephone" value="<?php print($company->Telephone); ?>" readonly/></td>
        </tr>
        <tr>
            <td  class="right bold">Facsimile :</td>
            <td><input type="text" name="Facsimile" size="35" maxlength="50" class="text2" id="Facsimile" value="<?php print($company->Facsimile); ?>" readonly/></td>
        </tr>
        <tr>
            <td  class="right bold">Email :</td>
            <td><input type="text" name="Email" size="35" class="text2" id="Email" value="<?php print($company->Email); ?>" readonly/></td>
        </tr>
        <tr>
            <td  class="right bold">Website :</td>
            <td><input type="text" name="Website" size="35" class="text2" id="Website" value="<?php print($company->Website); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">N P W P :</td>
            <td><input type="text" name="Npwp" size="25" maxlength="25" class="text2" id="Npwp" value="<?php print($company->Npwp); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">P I C :</td>
            <td><input type="text" name="PersonInCharge" size="25" maxlength="50" class="text2" id="PersonInCharge" value="<?php print($company->PersonInCharge); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">Postition :</td>
            <td><input type="text" name="PicStatus" size="25" maxlength="50" class="text2" id="PicStatus" value="<?php print($company->PicStatus); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right bold">Start Date :</td>
            <td><input type="text" id="StartDate" name="StartDate" value="<?php print($company->StartDate);?>" placeholder="YYYY-MM-DD" maxlength="10" size="10" readonly></td>
        </tr>
        <tr>
            <td class="right bold">Default Project :</td>
            <td><select name="DefProjectId" id="DefProjectId" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($projects as $project){
                        if ($company->DefProjectId == $project->Id){
                            printf("<option value='%d' selected='selected'>%s - %s</option>", $project->Id, $project->ProjectCd, $project->ProjectName);
                        }else {
                            printf("<option value='%d'>%s - %s</option>", $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right bold">General Cash Account :</td>
            <td><select name="GeneralCashAccId" id="GeneralCashAccId" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($accounts as $account){
                        if ($company->GeneralCashAccId == $account->Id){
                            printf("<option value='%d' selected='selected'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }else {
                            printf("<option value='%d'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right bold">PPN In Account :</td>
            <td><select name="PpnInAccId" id="PpnInAccId" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($accounts as $account){
                        if ($company->PpnInAccId == $account->Id){
                            printf("<option value='%d' selected='selected'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }else {
                            printf("<option value='%d'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right bold">PPN In Transit :</td>
            <td><select name="PpnTrxAccId" id="PpnTrxAccId" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($accounts as $account){
                        if ($company->PpnTrxAccId == $account->Id){
                            printf("<option value='%d' selected='selected'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }else {
                            printf("<option value='%d'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right bold">PPN Out Account :</td>
            <td><select name="PpnOutAccId" id="PpnOutAccId" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($accounts as $account){
                        if ($company->PpnOutAccId == $account->Id){
                            printf("<option value='%d' selected='selected'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }else {
                            printf("<option value='%d'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <a href="<?php print($helper->site_url("master.company")); ?>">Company List</a>
            </td>
        </tr>
    </table>
</fieldset>
</body>
</html>
