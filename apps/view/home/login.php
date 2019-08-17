<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>User Login</title>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["user_id", "user_pwd","user_captcha","btn_login"];
			BatchFocusRegister(elements);
		});
	</script>

	<style type="text/css"> /* css settings */

	.text1 {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 11px;
		color: #000000;
	}

	.text2 {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 10px;
		color: #0000FF;
	}

	.text4 {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 13px;
		color: #000066;
	}
	</style>
</head>

<body>
<div style="padding:5px;" align="center">
    <img src="<?php print(base_url('public/images/company/mka.png'));?>" width="650" height="300">
	<hr/>
	<form action="<?php echo site_url("home/login/"); ?>" method="post">
		<table width="400" border="0" align="center" cellpadding="2" cellspacing="0">
            <tr>
                <td class="text4" width="128">Login to Project</td>
                <td width="302">
                    <select name="project_id" id="project_id" style="width:170px" required>
                        <option value=""> - Choose Project -</option>
                        <?php
                        /** @var $projects Project[]*/
                        foreach ($projects as $project){
                            printf("<option value='%d'>%s - %s</option>",$project->Id,$project->ProjectCd,$project->ProjectName);
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="text4" width="128">User ID or Email</td>
				<td width="302"><input type="text" name="user_id" style="width:170px" value="" id="user_id" required></td>
			</tr>
			<tr>
				<td class="text4">User Password</td>
				<td><input type="password" name="user_pwd" style="width:170px" value="" id="user_pwd" required></td>
			</tr>
            <tr>
                <td class="text4">Captcha Sign</td>
                <td><img src="<?php print($helper->site_url("home/capgambar")); ?>" alt="captcha" width="175" height="40" /></td>
            </tr>
            <tr>
                <td class="text4">Captcha Value</td>
                <td><input type="text" name="user_captcha" style="width:170px" value="" id="user_captcha" required/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td align="left"><input type="submit" name="btn_login" value="LOGIN" id="btn_login"/>
					<input type="reset" name="btn_reset" value="RESET" id="btn_reset"/></td>
			</tr>
		</table>
	</form>
	<hr/>
	<div class="text1" align="center">Your IP Address :
		<b><?php echo "<span class=\"text2\">" . getenv("REMOTE_ADDR") . "</span>"; ?></b></div>
	<div class="text1" align="center">Support : 0811 431 9858 or support@rekasys.com</div>
	<div class="text1" align="center">Copyright (c) 2019 - <a href="https://rekasys.com">https://rekasys.com</a></div>
	<?php if (isset($error)) { ?>
	<script type="text/javascript">
		alert("<?php print($error);?>");
	</script>
	<?php } ?>
</div>
</body>
</html>
