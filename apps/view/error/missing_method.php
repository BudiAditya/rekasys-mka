<!--
	You can change this template to fulfill your requirement.
	DON'T DELETE THIS TEMPLATE BECAUSE REQUIRED AS CORE PART OF THE FRAMEWORK
-->
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Error : Missing Controller Method</title>
</head>

<body>
	<h2>Missing required method '<?php printf("%s/%s", $fqn, $method); ?>'</h2>
	<h3>
		Please make sure that you have 'function <?php print($method); ?>()' in your '<?php print($controller); ?>_controller.php'<br />
		Please check your controller in folder 'controller<?php print($folder); ?>'
	</h3>
	<?php print($helper->a("Go to home", $helper->url("home"))); ?>
</body>
</html>
