<!DOCTYPE html>
<?php /** @var $project Project */ ?>
<html>
<head>
    <title>Rekasys - Update Project</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["ProjectCd", "ProjectName", "ProjectLocation", "Pic", "Update"];
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
    <legend><b>Update Project</b></legend>
    <form id="frm" action="<?php print($helper->site_url("master.project/edit/".$project->Id)); ?>" method="post">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <td>Company</td>
                <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
            </tr>
            <tr>
                <td><label for="ProjectCd">Project Code</label></td>
                <td><input type="text" name="ProjectCd" id="ProjectCd" maxlength="10" size="10" value="<?php print($project->ProjectCd);?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
            <tr>
                <td><label for="ProjectName">Project Name</label></td>
                <td><input type="text" name="ProjectName" id="ProjectName" maxlength="50" size="50"  value="<?php print($project->ProjectName);?>" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
            <tr>
                <td><label for="ProjectLocation">Location</label></td>
                <td><input type="text" name="ProjectLocation" id="ProjectLocation" size="50" value="<?php print($project->ProjectLocation);?>" required/></td>
            </tr>
            <tr>
                <td><label for="Pic">P M</label></td>
                <td><input type="text" name="Pic" id="Pic" size="50" value="<?php print($project->Pic);?>" required/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button id="Update" type="submit">Update</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("master.project")); ?>">Projects List</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
</body>
</html>
