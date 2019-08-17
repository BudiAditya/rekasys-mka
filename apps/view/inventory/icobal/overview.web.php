<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Inventory Opening Balance</title>
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
	<legend><span class="bold">Inventory Opening Balance</span></legend>

	<form action="<?php print($helper->site_url("inventory.icobal/overview")); ?>" method="post">
		<div class="left">
			<label for="ProjectId">Project : </label>
			<select id="ProjectId" name="ProjectId">
				<option value="0">-- ALL PROJECT --</option>
				<?php
				$selectedProjectId = null;
				foreach ($projects as $project) {
					if ($project->Id == $projectId) {
						$selectedProjectId = $project;
						printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
					} else {
						printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
					}
				}
				?>
			</select>
            &nbsp;
            <label for="ProjectId">Category : </label>
            <select id="CategoryId" name="CategoryId">
                <option value="0">-- ALL CATEGORY --</option>
                <?php
                $selectedCategoryId= null;
                foreach ($categorys as $category) {
                    if ($category->Id == $categoryId) {
                        $selectedCategoryId = $category;
                        printf('<option value="%d" selected="selected">%s - %s</option>', $category->Id, $category->Code, $category->Description);
                    } else {
                        printf('<option value="%d">%s - %s</option>', $category->Id, $category->Code, $category->Description);
                    }
                }
                ?>
            </select>
            &nbsp;
            <label for="Date">As per date : </label>
            <input type="text" id="Date" name="date" value="<?php print(date(JS_DATE, $opndate)); ?>" size="10" readonly/>
            &nbsp;
			<label for="Output">Report Output : </label>
			<select id="Output" name="Output">
				<option value="1" <?php print($output == 1 ? 'selected="selected"' : '');?>>HTML</option>
				<option value="2" <?php print($output == 2 ? 'selected="selected"' : '');?>>Excel</option>
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
		<span class="title">Inventory Opening Per: <?php print(date(HUMAN_DATE, $opndate)); ?></span><br />
		<span class="subTitle">Project : <?php print($selectedProjectId == null ? 'All Project' : $selectedProjectId->ProjectCd . " - " . $selectedProjectId->ProjectName); ?></span><br />
        <span class="subTitle">Category : <?php print($selectedCategoryId == null ? 'All Category' : $selectedCategoryId->Code . " - " . $selectedCategoryId->Description); ?></span><br />
	</div>
	<br />
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Project</th>
            <th>Category</th>
            <th>Item Code</th>
            <th>Part Number</th>
            <th>Item Name</th>
            <th>UOM</th>
            <th>QTY</th>
            <th>Price</th>
            <th>Amount</th>
        </tr>
        <?php
        $nmr = 1;
        $tOtal = 0;
        $pro = null;
        $cat = null;
        while ($row = $report->FetchAssoc()) {
            print("<tr>");
            printf("<td>%s</td>", $nmr);
            if ($pro == $row["project_name"]) {
                print("<td>&nbsp;</td>");
            }else{
                printf("<td nowrap='nowrap'>%s</td>", $row["project_name"]);
            }
            if ($cat == $row["category_desc"]) {
                print("<td>&nbsp;</td>");
            }else{
                printf("<td nowrap='nowrap'>%s</td>", $row["category_desc"]);
            }
            printf("<td nowrap='nowrap'>%s</td>", $row["item_code"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["part_no"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["item_name"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["uom_cd"]);
            printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($row["qty"],0));
            printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($row["price"],0));
            printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($row["qty"] * $row["price"],0));
            print("</tr>");
            $nmr++;
            $tOtal+= $row["qty"] * $row["price"];
            $pro = $row["project_name"];
            $cat = $row["category_desc"];
        }
        print("<tr>");
        print("<td colspan='9'>Total Nilai Awal Persediaan</td>");
        printf("<td nowrap='nowrap' class='right'>%s</td>",number_format($tOtal,0));
        print("</tr>");
        ?>
    </table>
</div>
	<?php } ?>
<!-- END REGION: LAPORAN-->

</body>
</html>
