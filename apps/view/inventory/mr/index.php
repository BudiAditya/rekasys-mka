<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Daftar Material Requisition</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/flexigrid.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
		<?php print($fgScript); ?>
		});

		function bt_add_click(com, grid) {
			window.location = "<?php print($helper->site_url("inventory.mr/add_master")); ?>";
		}

		function bt_edit_click(com, grid) {
			var items = $("#tblList .trSelected");
			if (items.length != 1) {
				alert("Please Select One to Edit");
				return;
			}

			var code = items.find("td:eq(2)").text();
			var id = items[0].id.substr(3);
			if (!confirm("Edit data inventory dengan kode: " + code + " ?")) {
				return
			}

			window.location = "<?php print($helper->site_url("inventory.mr/edit_master/")) ?>" + id;
		}

		function bt_delete_click(com, grid) {
			var items = $("#tblList .trSelected");
			if (items.length != 1) {
				alert("Please Select One to Delete");
				return;
			}

			var code = items.find("td:eq(2)").text();
			var id = items[0].id.substr(3);
			if (!confirm("Hapus data inventory dengan kode: " + code + " ?\nPERHATIAN: Penghapusan data! Harap Hati-Hati.")) {
				return
			}

			window.location = "<?php print($helper->site_url("inventory.mr/delete/")) ?>" + id;
		}
	</script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>

<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br/>
<table id="tblList"></table>
<br/>

<?php //include(VIEW . "footer.php"); ?>

</body>

</html>
