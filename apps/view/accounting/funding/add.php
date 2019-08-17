<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $npkp CashRequest */ /** @var $funding NpkpFunding */
/** @var $accounts Coa[] */
$reqamount = DotNetTools::ArraySum($npkp->Details, function(CashRequestDetail $detail) { return $detail->Amount; } );
?>
<head>
	<title>Rekasys - Pencairan NPKP</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#amount").autoNumeric();
			$("#date").customDatePicker({ phpDate: <?php print(is_int($funding->FundingDate) ? $funding->FundingDate : "null"); ?> });

		});
	</script>
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
			<td class="bold right">Request Date :</td>
			<td><?php print($npkp->FormatEtaDate()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Requested Amount :</td>
			<td>Rp. <?php print(number_format(DotNetTools::ArraySum($npkp->Details, function(CashRequestDetail $detail) { return $detail->Amount; } ))); ?></td>
		</tr>
		<tr>
			<td class="bold right">Approved Amount :</td>
			<td>Rp. <?php print(number_format(DotNetTools::ArraySum($npkp->Funds, function(NpkpFunding $funding) { return $funding->Amount; } ))); ?></td>
		</tr>
	</table><br />

	<form action="<?php print($helper->site_url("accounting.funding/add/" . $npkp->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding">
			<tr>
				<td class="right"><label for="date">Funding Date :</label></td>
				<td><input type="text" id="date" name="date" size="12" /></td>
			</tr>
            <tr>
                <td class="right"><label for="cash_source">Cash Source :</label></td>
                <td><select name="cash_source" id="cash_source" required style="width: 350px;">
                        <option value=""></option>
                        <?php
                        foreach ($accounts as $account){
                            if ($account->Id == $accdest || left($account->AccNo,4) > 1102){
                                continue;
                            }else {
                                if ($account->Id == $funding->CashSourceAccId) {
                                    printf("<option value='%d' selected='selected'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                                }else{
                                    if ($account->Id == $company->GeneralCashAccId){
                                        printf("<option value='%d' selected='selected'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                                    }else {
                                        printf("<option value='%d'>%s - %s</option>", $account->Id, $account->AccNo, $account->AccName);
                                    }
                                }
                            }
                        }
                        ?>
                    </select>

                </td>
            </tr>
			<tr>
				<td class="right"><label for="amount">Approved Amount :</label></td>
				<td><input type="text" class="right" id="amount" name="amount" value="<?php print(number_format($funding->Amount == 0 ? $reqamount : $funding->Amount, 2)); ?>"  style="width: 150px;"/></td>
			</tr>
			<tr>
				<td class="right"><label for="note">Notes :</label></td>
				<td><input type="text" id="note" name="note" value="<?php print($funding->Note); ?>"  style="width: 350px;"/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Process</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
