<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Test Datepicker</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var b = $("#txt1").customDatePicker();
			var a = $("#txt2").datepicker();
			b.datepicker("setDate", new Date());

			var div = $('<div></div>')
			var test = $("<input type='text' id='txt3' name='txt3' />");
			div.append(test);
			test.customDatePicker().datepicker("setDate", new Date());
			$("body").append(div);
		});
	</script>
</head>

<body>
<input type="text" id="asd" name="asd" />

<input type="text" id="txt1" name="txt1" />

<input type="text" id="txt2" name="txt2" />

</body>
</html>
