<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Edit Opening Balance</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function() {
			$("#Debit").autoNumeric({ vMax: "99999999999999.99" });
			$("#Credit").autoNumeric({ vMax: "99999999999999.99" });
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
	<legend><span class="bold">Entry Opening Balance</span></legend>

	<form action="<?php print($helper->site_url("ledger.obal/edit/" . $openingBalance->Id)) ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td class="right"><label for="AccountId">Account Number : </label></td>
				<td><select id="AccountId" name="AccountId">
					<option value="">-- PILIH AKUN --</option>
					<?php
					$prevParentId = null;
					foreach ($accounts as $account) {
					    if ($prevParentId != $account->AccParentId) {
							$prevParentId = $account->AccParentId;
							$parent = $parentAccounts[$prevParentId];
							printf('<optgroup label="%s - %s"></optgroup>', $parent->AccNo, $parent->AccName);
						}
                        if ($account->Id == $openingBalance->AccountId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						} else {
							printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="right"><label for="Year">Trx Year : </label></td>
				<td>
					<select id="Year" name="Year">
						<?php
						$year = $openingBalance->FormatDate("Y");
						for ($i = date("Y"); $i >= 2010; $i--) {
							if ($i == $year) {
								printf('<option value="%d" selected="selected">%s</option>', $i, $i);
							} else {
								printf('<option value="%d">%s</option>', $i, $i);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="Debit">Debit Amount :</label></td>
				<td><input type="text" class="right bold" id="Debit" name="Debit" value="<?php print(number_format($openingBalance->DebitAmount)); ?>" /></td>
			</tr>
			<tr>
				<td class="right"><label for="Credit">Credit Amount :</label></td>
				<td><input type="text" class="right bold" id="Credit" name="Credit" value="<?php print(number_format($openingBalance->CreditAmount)); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Submit</button>
                    &nbsp;
                    <a class="button" href="<?php print($helper->site_url("ledger.obal")); ?>">Account Opening List</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
