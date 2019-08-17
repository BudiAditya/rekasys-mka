<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Entry Cash Request (NPKP) Step 3</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $company Company */ /** @var $cashRequest CashRequest */ /** @var $accounts Coa[] */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Entry Cash Request (NPKP) Step 3 - Konfirmasi</span></legend>

    <table cellpadding="0" cellspacing="0" class="tablePadding">
        <tr>
            <td class="bold right">Company :</td>
            <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
        </tr>
        <tr>
            <td class="bold right">NPKP No. :</td>
            <td><?php print($cashRequest->DocumentNo); ?></td>
        </tr>
        <tr>
            <td class="bold right">NPKP Date :</td>
            <td><?php print($cashRequest->FormatDate()); ?></td>
        </tr>
        <tr>
            <td class="bold right">Project :</td>
            <td><?php printf("%s - %s", $category->Code, $category->Name); ?></td>
        </tr>
        <tr>
            <td class="bold right">NPKP Purpose :</td>
            <td><?php print($cashRequest->Objective); ?></td>
        </tr>
        <tr>
            <td class="bold right">Description :</td>
            <td><?php print(str_replace("\n", "<br />", $cashRequest->Note)); ?></td>
        </tr>
        <tr>
            <td class="bold right">Request Date :</td>
            <td><?php print($cashRequest->FormatEtaDate()); ?></td>
        </tr>
    </table>

	<div id="container" style="min-width: 600px; display: inline-block; padding: 5px;">
		<div class="bold center subTitle">
			Cash Request (NPKP) Detail
		</div><br />

		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr class="bold center">
				<td class="bN bE bS bW">No.</td>
				<td class="bN bE bS">Post Account</td>
				<td class="bN bE bS" width="400">Cash Purpose</td>
				<td class="bN bE bS">Amount</td>
			</tr>
			<?php
			$sum = 0;
			foreach($cashRequest->Details as $idx => $detail) {
				$sum += $detail->Amount;
				$account = isset($accounts[$detail->AccountId]) ? $accounts[$detail->AccountId] : null;
			?>
			<tr>
				<td class="bE bW"><?php print($idx + 1); ?>.</td>
				<td class="bE"><?php print($account != null ? $account->AccNo : "-"); ?></td>
				<td class="bE"><?php print($detail->Note); ?></td>
				<td class="bE right"><?php print(number_format($detail->Amount, 2)); ?></td>
			</tr>
			<?php } ?>
			<tr class="bold">
				<td colspan="3" class="bN bE right">Total :</td>
				<td class="bN bE bS right"><?php print(number_format($sum, 2)); ?></td>
			</tr>
		</table><br />

		<form action="<?php print($helper->site_url("accounting.cashrequest/add?step=confirm")); ?>" method="post" class="center">
			<input type="hidden" name="confirmed" value="1" />
			<button type="button" onclick="window.location='<?php print($helper->site_url("accounting.cashrequest/add?step=detail")); ?>';">&lt; Previous</button>
			&nbsp;&nbsp;&nbsp;
			<button type="submit">Submit</button>
		</form>

	</div>


</fieldset>

</body>
</html>
