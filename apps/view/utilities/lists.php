<!DOCTYPE HTML>
<html>
<?php /** @var $listLink string */ /** @var $settings array */ /** @var $request array */ /** @var $result array */ ?>
<head>
	<title>Rekasys - <?php print($settings["title"]); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<style type="text/css">
		#navigation {
			list-style: none;
			margin: 15px 0 0 0;
			padding: 0;
		}
		#navigation li {
			display: inline;
			padding: 0 2px;
		}
	</style>

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
	<legend><span class="bold"><?php print($settings["title"]); ?></span></legend>
	<span class="title bold"><?php print($settings["title"]); ?></span><br />
	<?php if ($settings["subTitle"] != null) { printf('<span class="subTitle">%s</span><br />', $settings["subTitle"]); } ?>
	<br />

	<table cellspacing="0" cellpadding="0" class="tablePadding">
		<tr>
			<th>No</th>
			<th>Actions</th>
			<?php foreach ($settings["columns"] as $idx => $column) { if ($idx == 0) { continue; } printf('<th>%s</th>', $column["display"]); } ?>
		</tr>
		<?php
		$link = "";
		foreach ($settings["actions"] as $action) {
			$link .= sprintf('<a class="button" href="%s">%s</a>', $helper->site_url($action["Url"]), $action["Text"]);
		}
		$totalColumn = count($settings["columns"]);
		$startFrom = ($request["page"] - 1) * $settings["recordPerPage"] + 1;

		foreach ($result as $idx => $row) {
			print("<tr>");
			printf('<td>%s.</td>', $startFrom + $idx);
			printf('<td>%s</td>', str_replace("%s", $row[0], $link));
			for ($i = 1; $i < $totalColumn; $i++) {
				printf('<td class="%s">%s</td>', $settings["columns"][$i]["align"], $row[$i]);
			}
			print("</tr>\n");
		}
		?>
	</table>
	<ol id="navigation">
		<li>Halaman ke : </li>
		<?php
		$lowerBound = max (1, $request["page"] - 2);
		$upperBound = min($settings["totalPages"], $request["page"] + 2);
		for ($i = $lowerBound; $i <= $upperBound; $i++) {
			if ($i == $request["page"]) {
				print('<li><div class="button">' . $i . '</div></li>');
			} else {
				printf('<li><a class="button" href="%s?p=%s">%s</a></li>', $listLink, $i, $i);
			}
		}
		print("<li>(Total: " . $settings["totalPages"] . ")</li>");

		if ($settings["returnUrl"] != null) {
			print('<li><div class="right" style="display: inline-block; width: 100px;">Kembali ke : </div></li>');
			printf('<li><a class="button" href="%s">%s</a></li>', $helper->site_url($settings["returnUrl"]["Url"]), $settings["returnUrl"]["Text"]);
		}
		?>
	</ol>
</fieldset>

</body>
</html>
