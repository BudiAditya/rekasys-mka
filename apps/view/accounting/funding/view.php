<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $npkp CashRequest */ /** @var $funding NpkpFunding */ /** @var $voucher Voucher */
?>
<head>
	<title>Rekasys - Funding NPKP: <?php print($npkp->DocumentNo); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
	<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
	<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold"><?php printf("Funding NPKP: %s, Date: %s", $npkp->DocumentNo, $npkp->FormatDate()); ?></span></legend>

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
		</tr>
		<tr>
			<td class="bold right">NPKP Purpose :</td>
			<td><?php print($npkp->Objective); ?></td>
		</tr>
		<tr>
			<td class="bold right">Request date :</td>
			<td><?php print($npkp->FormatEtaDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Request Amount :</td>
			<td>Rp. <?php print(number_format(DotNetTools::ArraySum($npkp->Details, function(CashRequestDetail $detail) { return $detail->Amount; } ))); ?></td>
		</tr>
		<tr>
			<td class="bold right">Funding Date :</td>
			<td><?php print($funding->FormatFundingDate()); ?></td>
		</tr>
        <tr>
            <td class="bold right">Cash Source :</td>
            <td><?php print($funding->CashSourceAccNo.' - '.$funding->CashSourceAccName); ?></td>
        </tr>
		<tr>
			<td class="bold right">Funding Amount :</td>
			<td>Rp. <?php print(number_format($funding->Amount)); ?></td>
		</tr>
		<tr>
			<td class="bold right">Accounting Voucher :</td>
			<td><a href="<?php print($helper->site_url("accounting.voucher/view/" . $voucher->Id)); ?>"><?php print($voucher->DocumentNo); ?></a></td>
		</tr>
	</table><br />
</fieldset>

</body>
</html>
