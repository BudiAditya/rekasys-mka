<!DOCTYPE html>
<html>
<head>
	<title>Rekasys - Module System</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/flexigrid.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
		<?php print($fgScript); ?>
		});

		function bt_click(com, grid) {
			var items = $('.trSelected');
			var v_ecd = $('.trSelected td:eq(1)').text();
			var itemlist = '';
			var urx = '';
			if (com == 'Delete') {
				itemlist = items[0].id.substr(3);
				if (($('.trSelected').length > 0)) {
					if (confirm('Hapus Data Module Kode : ' + v_ecd + ' ?')) {
						urx = '<?php print($helper->site_url("master.module/delete")); ?>/' + itemlist;
						//alert(urx);
						window.location.href = urx;
					}
				} else {
					alert('Please Select One To Delete');
				}
			} else if (com == 'Edit') {
				itemlist = items[0].id.substr(3);
				if (($('.trSelected').length > 0)) {
					if (confirm('Edit Data Module Kode : ' + v_ecd + ' ?')) {
						urx = '<?php print($helper->site_url("master.module/edit")); ?>/' + itemlist;
						//alert(urx);
						window.location.href = urx;
					}
				} else {
					alert('Please Select One To Delete');
				}
			} else if (com == 'Add') {
				urx = '<?php print($helper->site_url("master.module/add")); ?>';
				window.location.href = urx;
			}
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
