<!--
	You can change this template to fulfill your requirement.
	DON'T DELETE THIS TEMPLATE BECAUSE REQUIRED AS CORE PART OF THE FRAMEWORK
-->
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Error : Missing Controller</title>
</head>

<body>
	<h2>Missing controller class definition for '<?php print($controller); ?>Controller'</h2>
	<h3>
		Please make sure that you have DEFINED class '<?php print($controller); ?>Controller' in the controller file !<br />
		Controller filename : '<?php print($controller); ?>_controller.php'<br />
		Controller location : 'controller<?php print($folder); ?>'<br /><br />
		Make sure you are type the correct class name definition in your PHP script<br />
		If problem persist please contact Site Administrator
	</h3>
	<?php print($helper->a("Go to home", $helper->url("home"))); ?>
</body>
</html>
