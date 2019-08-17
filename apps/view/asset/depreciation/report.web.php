<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Asset Reporting</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
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
	<legend><span class="bold">Asset Report</span></legend>

	<form action="<?php print($helper->site_url("asset.depreciation/report")); ?>" method="post">
		<div class="left">
            <label for="CategoryId">Category : </label>
            <select id="CategoryId" name="CategoryId">
                <option value="0">-- ALL CATEGORY --</option>
                <?php
                /** @var $acats AssetCategory[] */
                $selectedCategoryId= null;
                foreach ($acats as $category) {
                    if ($category->Id == $CategoryId) {
                        $selectedCategoryId = $category;
                        printf('<option value="%d" selected="selected">%s - %s</option>', $category->Id, $category->Code, $category->Name);
                    } else {
                        printf('<option value="%d">%s - %s</option>', $category->Id, $category->Code, $category->Name);
                    }
                }
                ?>
            </select>
            &nbsp;
            <label for="DepMonth">Period : </label>
            <select id="DepMonth" name="DepMonth">
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
            <select id="DepYear" name="DepYear">
                <?php
                for ($i = (int)date("Y"); $i >= $startYear; $i--) {
                    printf('<option value="%d">%d</option>', $i, $i);
                }
                ?>
            </select>
            &nbsp;
			<label for="Output">Report Output : </label>
			<select id="Output" name="Output">
				<option value="1" <?php print($OutPut == 1 ? 'selected="selected"' : '');?>>HTML</option>
				<option value="2" <?php print($OutPut == 2 ? 'selected="selected"' : '');?>>Excel</option>
			</select>
            &nbsp;
			<button type="submit">Generate</button>
		</div>
	</form>
</fieldset>

<!-- REGION: LAPORAN -->
<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="bold left">
		<span class="title">Asset Depreciation Cost</span><br />
		<span class="subTitle">Asset Category : <?php print($selectedCategoryId == null ? 'All Category' : $selectedCategoryId->Code . " - " . $selectedCategoryId->Name); ?></span><br />
	</div>
	<br />
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Category</th>
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
            if ($pro == $row["category_code"]) {
                print("<td>&nbsp;</td>");
            }else{
                printf("<td nowrap='nowrap'>%s</td>", $row["category_code"]);
            }
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
        print("<td colspan='5' class='right'>Total Depreciation Cost</td>");
        printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($tCost,2));
        print("</tr>");
        ?>
    </table>
</div>
	<?php } ?>
<!-- END REGION: LAPORAN-->

</body>
</html>
