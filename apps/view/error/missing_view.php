<!--
	You can change this template to fulfill your requirement.
	DON'T DELETE THIS TEMPLATE BECAUSE REQUIRED AS CORE PART OF THE FRAMEWORK
-->
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Error : Missing View</title>
</head>

<body>
	<h2>Missing View component for '<?php printf("%s/%s", $fqn, $view); ?>'</h2>
	<h3>
		Please make sure that you have '<?php printf("%s.%s", $view, $ext); ?>' in your View Folder !<br />
		View seek location : '<?php printf("view%s/%s", $folder, $controller); ?>' folder
	</h3>
	<?php print($helper->a("Go to home", $helper->url("home"))); ?>
</body>
</html>
