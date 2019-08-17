<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<?php
/** @var $categories AssetCategory[] */ /** @var $year int */ /** @var $month int */ /** @var $isCommit bool */ /** @var $journal \Asset\DepreciationJournal */
$indonesianMonths = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
?>
<head>
	<title>Rekasys - Pembuatan Voucher Memorial</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

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
	<legend><span class="bold">Create Depreciation Cost Journal</span></legend>

	<?php
	if ($journal->TotalDepreciation === null) {
	?>
	<form action="<?php print($helper->site_url("asset.voucher/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td><label for="category">Category :</label></td>
				<td>
					<select id="category" name="category">
						<?php
						foreach ($categories as $category) {
							if ($category->Id == $journal->AssetCategoryId) {
								printf('<option value="%d" selected="selected">%s - %s</option>', $category->Id, $category->EntityCode, $category->Name);
							} else {
								printf('<option value="%d">%s - %s</option>', $category->Id, $category->EntityCode, $category->Name);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="month">Periode :</label></td>
				<td>
					<select id="month" name="month">
						<?php
						foreach ($indonesianMonths as $idx => $monthName) {
							if ($idx == $month) {
								printf('<option value="%d" selected="selected">%s</option>', $idx, $monthName);
							} else {
								printf('<option value="%d">%s</option>', $idx, $monthName);
							}
						}
						?>
					</select>
					<label for="year"> Year </label>
					<select id="year" name="year">
						<?php
						for ($i = date("Y"); $i >= 2018; $i--) {
							if ($i == $year) {
								printf('<option value="%d" selected="selected">%s</option>', $i, $i);
							} else {
								printf('<option value="%d">%s</option>', $i, $i);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Rekap Depresiasi Asset</button>
				</td>
			</tr>
		</table>
	</form>
	<?php
	} else {
		require_once(LIBRARY . "dot_net_tools.php");
		/** @var $assetCategory AssetCategory */
		$assetCategory = DotNetTools::FindInArray($categories, function(AssetCategory $lhs) use (&$journal) {
			return ($lhs->Id == $journal->AssetCategoryId);
		});
	?>
	<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
		<tr>
			<td class="bold right">Asset Category :</td>
			<td><?php printf("%s - %s", $assetCategory->EntityCode, $assetCategory->Name); ?></td>
		</tr>
		<tr>
			<td class="bold right">Periode :</td>
			<td><?php printf("%s %d", $indonesianMonths[$month], $year); ?></td>
		</tr>
		<tr>
			<td class="bold right">Total Depreciation :</td>
			<td>Rp. <?php print(number_format($journal->TotalDepreciation, 2)); ?></td>
		</tr>
        <?php
        if ($journal->TotalDepreciation == 0){
            print("<tr>");
            print("<td>&nbsp;</td>");
            print("<td colspan='2'>");
            printf('<h3>Depresiasi Category Asset periode ini belum diproses! <a href="%s">[Klik disini untuk Proses Depresiasi]<a/></h3>',$helper->site_url("asset.depreciation/process_all"));
            print("&nbsp;&nbsp;&nbsp;");
            printf('<a href="%s">Ganti Periode</a>',$helper->site_url('asset.voucher/add'));
            print("</td>");
            print("</tr>");
        }else{
        ?>
            <form action="<?php print($helper->site_url("asset.voucher/add")); ?>" method="post">
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
                <td colspan="2">
                    <input type="hidden" name="year" value="<?php print($year); ?>">
                    <input type="hidden" name="month" value="<?php print($month); ?>">
                    <input type="hidden" name="category" value="<?php print($assetCategory->Id); ?>">
                    <input type="hidden" name="commit" value="1" />

                    <button type="submit"><b>Create Journal</b></button>
                    &nbsp;&nbsp;&nbsp;
                    <a href="<?php print($helper->site_url("asset.voucher/add")); ?>">Ganti Periode</a>
                </td>
            </tr>
            </form>
        <?php } ?>
    </table>
	<?php }?>
</fieldset>

<!-- REGION: LAPORAN -->
<?php if ($report != null) { ?>
    <br />
    <div class="container">
        <div class="bold left">
            <span class="title">Asset Depreciation Cost</span><br />
            <span class="subTitle">Asset Category : <?php print($assetCategory->Name); ?></span><br />
        </div>
        <br />
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Code</th>
                <th>Asset Name</th>
                <th>Depr Date</th>
                <th>Depr Cost</th>
            </tr>
            <?php
            $nmr = 1;
            $tCost = 0;
            $pro = null;
            $cat = null;
            while ($row = $report->FetchAssoc()) {
                print("<tr>");
                printf("<td>%s</td>", $nmr);
                printf("<td nowrap='nowrap'>%s</td>", $row["asset_code"]);
                printf("<td nowrap='nowrap'>%s</td>", $row["asset_name"]);
                printf("<td nowrap='nowrap'>%s</td>", date("d-m-Y",strtotime($row["depreciation_date"])));
                printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($row["amount"],2));
                print("</tr>");
                $nmr++;
                $tCost+= round($row["amount"],2);
                $pro = $row["category_code"];
            }
            print("<tr>");
            print("<td colspan='4' class='right'>Total Depreciation Cost</td>");
            printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($tCost,2));
            print("</tr>");
            ?>
        </table>
    </div>
<?php } ?>
<!-- END REGION: LAPORAN-->
</body>
</html>
