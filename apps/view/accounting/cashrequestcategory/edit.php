<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Edit NPKP Category</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var elements = ["Code", "Name", "AccId"];
		$(document).ready(function() {
			BatchFocusRegister(elements);
		});
	</script>
</head>

<body>
<?php /** @var $company Company */ /** @var $category CashRequestCategory */ /** @var $accounts Coa[] */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Edit NPKP Category : <?php printf("%s - %s", $category->Code, $category->Name); ?></span></legend>
	<form action="<?php print($helper->site_url("accounting.cashrequestcategory/edit/" . $category->Id)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="bold right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td class="bold right"><label for="Code">Category Code :</label></td>
				<td><input type="text" id="Code" name="Code" value="<?php print($category->Code); ?>" </td>
			</tr>
			<tr>
				<td class="bold right"><label for="Name">Category Name :</label></td>
				<td><input type="text" id="Name" name="Name" value="<?php print($category->Name); ?>" </td>
			</tr>
            <tr>
                <td class="bold right"><label for="ProjectId">Project :</label></td>
                <td><select id="ProjectId" name="ProjectId">
                        <option value="0">-- Pilih Proyek --</option>
                        <?php
                        foreach ($projects as $project) {
                            if ($project->Id == $category->ProjectId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="bold right"><label for="AccId">Control Account :</label></td>
                <td><select id="AccId" name="AccId">
                        <option value="0">-- Pilih Akun Kontrol --</option>
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $category->AccountControlId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Update</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("accounting.cashrequestcategory")); ?>">NPKP Category List</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
