<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<?php
/** @var $category AssetCategory */ /** @var $journal \Asset\DepreciationJournal */
$indonesianMonths = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$month = $journal->FormatDepreciationDate("n");
$year = $journal->FormatDepreciationDate("Y");
?>
<head>
	<title>Rekasys - Edit Jurnal Memorial</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#amount").autoNumeric();
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
	<legend><span class="bold"><?php printf("Jurnal Memorial %s Periode %s %s", $category->Name, $indonesianMonths[$month], $year); ?></span></legend>

	<form action="<?php print($helper->site_url("asset.voucher/edit/" . $journal->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right">Kategori Asset :</td>
				<td><?php printf("%s - %s", $category->EntityCode, $category->Name); ?></td>
			</tr>
			<tr>
				<td class="bold right">Periode :</td>
				<td><?php printf("%s %d", $indonesianMonths[$month], $year); ?></td>
			</tr>
			<tr>
				<td class="bold right"><label for="amount">Total Depresiasi :</label></td>
				<td><input type="text" id="amount" name="amount" value="<?php print(number_format($journal->TotalDepreciation, 2)); ?>" /></td>
			</tr>
            <tr>
                <td class="bold right">Project :</td>
                <td><select name="projectId" id="projectId">
                        <option value=""></option>
                        <?php
                        /** @var $projects Project[] */
                        foreach ($projects as $project) {
                            if ($project->Id == $journal->ProjectId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right">Departemen :</td>
                <td><select name="deptId" id="deptId">
                        <option value=""></option>
                        <?php
                        /** @var $depts Department[] */
                        foreach ($depts as $dept) {
                            if ($dept->Id == $journal->DeptId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right">Activity :</td>
                <td><select name="actId" id="actId">
                        <option value=""></option>
                        <?php
                        /** @var $acts Activity[] */
                        foreach ($acts as $act) {
                            if ($act->Id == $journal->ActivityId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">UPDATE</button></td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
