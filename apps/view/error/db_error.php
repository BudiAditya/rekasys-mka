<!--
	You can change this template to fulfill your requirement.
	DON'T DELETE THIS TEMPLATE BECAUSE REQUIRED AS CORE PART OF THE FRAMEWORK
-->
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Error : Database Related Error</title>
	<style type="text/css">
		.bold { font-weight: bold; }
		.title { font-size: 24px; }
		.subTitle { font-size: 18px; }
		.right { text-align: right; }
		.nowrap { white-space: nowrap; }
	</style>
</head>

<body>
	<div class="title bold" style="color: red">Sorry but we encountered a database error !</div><br />
	<div class="subTitle bold">
		Error Message :
	</div>
	<div>
		<?php printf("%s - %s", $errCode, $errMsg); ?>
	</div><br />
	<div class="subTitle bold">
		Last Executed Query :
	</div>
	<div>
		<pre style="border: solid black 1px; padding: 5px 10px;"><?php print($lastQuery); ?></pre>
	</div><br />
	<div>
		You see this error message because your connector setting for DebugMode is equal to 'true'.<br />
		If you want to suppress this error message please disable DebugMode in your connector settings
	</div>
</body>
</html>
