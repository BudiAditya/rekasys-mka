<!DOCTYPE html>
<html>
<?php /** @var $userAdmin UserAdmin */ /** @var $companies Company */ ?>
<head>
    <title>Rekasys - Edit System User</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var elements = ["UserId", "EmployeeId", "UserPwd1", "UserPwd2", "UserLvl", "AllowMultipleLogin", "IsAktif"];
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
    <legend><b>Edit System User</b></legend>
    <form id="frm" action="<?php print($helper->site_url("master.useradmin/edit/".$userAdmin->UserUid)); ?>" method="post">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <td class="right"><label for="EntityId">Company :</label></td>
                <td><input type="hidden" name="EntityId" id="EntityId" value="<?php print($companies->EntityId);?>"><?php print($companies->EntityCd.' - '.$companies->CompanyName);?></td>
            </tr>
            <tr>
                <td class="right"><label for="UserId">User ID :</label></td>
                <td><input type="text" name="UserId" id="UserId" maxlength="15" size="15" value="<?php print($userAdmin->UserId);?>" required/></td>
            </tr>
            <tr>
                <td class="right"><label for="UserName">Full Name :</label></td>
                <td colspan="3">
                    <select name="EmployeeId" id="EmployeeId" required>
                        <option value=""></option>
                        <?php
                        /** @var $employees Employee[] */
                        foreach ($employees as $emp){
                            if ($userAdmin->EmployeeId == $emp->Id) {
                                printf('<option value="%d" selected="selected"> %s - %s </option>', $emp->Id, $emp->BadgeId, $emp->Nama);
                            }else{
                                printf('<option value="%d"> %s - %s </option>', $emp->Id, $emp->BadgeId, $emp->Nama);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="UserEmail">Email Address :</label></td>
                <td colspan="2"><input type="text" name="UserEmail" id="UserEmail" maxlength="100" size="40" value="<?php print($userAdmin->UserEmail);?>"/></td>
            </tr>
            <tr>
                <td class="right"><label for="UserPwd1">Password :</label></td>
                <td><input type="password" name="UserPwd1" id="UserPwd1" maxlength="50" size="15" value=""/></td>
            </tr>
            <tr>
                <td class="right"><label for="UserPwd2">Conf. Passwd :</label></td>
                <td><input type="password" name="UserPwd2" id="UserPwd2" maxlength="50" size="15" value=""/></td>
            </tr>
            <tr>
                <td class="right"><label for="UserLvl">User Level :</label></td>
                <td><select name="UserLvl" id="UserLvl">
                        <option value="1" <?php print($userAdmin->UserLvl == 1 ? 'selected="selected"' : ''); ?>>Operator</option>
                        <option value="2" <?php print($userAdmin->UserLvl == 2 ? 'selected="selected"' : ''); ?>>Supervisor</option>
                        <option value="3" <?php print($userAdmin->UserLvl == 3 ? 'selected="selected"' : ''); ?>>Manager</option>
                        <option value="4" <?php print($userAdmin->UserLvl == 4 ? 'selected="selected"' : ''); ?>>Administrator</option>
                        <option value="5" <?php print($userAdmin->UserLvl == 5 ? 'selected="selected"' : ''); ?>>Super User</option>
                    </select></td>
            </tr>
            <tr>
                <td class="right" valign="top"><label for="UserLvl">Allow Access Project :</label></td>
                <td>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project){
                        if (strstr($userAdmin->AProjectId,$project->Id)) {
                            printf("<input type='checkbox' name='AProjectId[]' value='%d' checked='checked'>%s - %s<br>", $project->Id, $project->ProjectCd, $project->ProjectName);
                        }else {
                            printf("<input type='checkbox' name='AProjectId[]' value='%d'>%s - %s<br>", $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="right">Login Rules :</td>
                <td>
                    <input type="checkbox" id="AllowMultipleLogin" name="AllowMultipleLogin" <?php print($userAdmin->AllowMultipleLogin == 1 ? 'checked="checked"' : ''); ?> />
                    <label for="AllowMultipleLogin">Allow Multiple Login</label>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="checkbox" id="IsAktif" name="IsAktif" value="1" <?php print($userAdmin->IsAktif == 1 ? 'checked="checked"' : ''); ?> />
                    <label for="IsAktif">User Active</label>
                    &nbsp;&nbsp;&nbsp;
                    <input type="checkbox" id="IsForcePeriod" name="IsForcePeriod" <?php print($userAdmin->IsForceAccountingPeriod == 1 ? 'checked="checked"' : ''); ?> />
                    <label for="IsForcePeriod">Force Select Accounting Period</label>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button id="BtSimpan" type="submit">U P D A T E</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("master.useradmin")); ?>">System User List</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
</body>
</html>
