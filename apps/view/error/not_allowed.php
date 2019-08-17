<!--
	You can change this template to fulfill your requirement.
	DON'T DELETE THIS TEMPLATE BECAUSE REQUIRED AS CORE PART OF THE FRAMEWORK
-->
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Error : Access Forbidden !</title>
</head>

<body>
	<h2>Sorry you're not allowed to access this resource(s) !</h2>
	<h3>
		You system administrator explicitly or implicitly forbidden your access this resource(s).<br />
		If you think you're should be allowed to access this resources please contact your site administrator.<br /><br />
		Trying to access : '<?php printf("%s/%s", $fqn, $method); ?>'
	</h3>
	<?php print($helper->a("Go to home", $helper->url("home"))); ?>
</body>
</html>
