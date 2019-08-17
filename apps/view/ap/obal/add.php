<!DOCTYPE HTML>
<html>
<?php
/** @var Ap\OpeningBalance $obal */ /** @var Creditor[] $creditors */
?>
<head>
	<title>Rekasys - A/P Opening Balance</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<style type="text/css">
		.colCode { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colText { display: inline-block; width: 350px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.blue { color: blue; }
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/select2.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#DebitAmount").autoNumeric();
			$("#CreditAmount").autoNumeric();
			$("#CreditorId").select2({
				placeholderOption: "first",
				allowClear: false,
				formatResult: formatOptionList,
				formatSelection: formatOptionResult
			});
		});

		function formatOptionList(state) {
			if (state.id == "") {
				return "-- PILIH CREDITOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode">' + originalOption.data("code") + '</div><div class="colText">' + originalOption.data("name") + '</div>';
		}

		function formatOptionResult(state) {
			if (state.id == "") {
				return "-- PILIH CREDITOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode bold blue">' + originalOption.data("code") + '</div><div class="colText bold blue">' + originalOption.data("name") + '</div>';
		}
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
	<legend><span class="bold">A/P Opening Balance Entry</span></legend>

	<form action="<?php print($helper->site_url("ap.obal/add")); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="CreditorId">Creditor Name :</label></td>
				<td>
					<select id="CreditorId" name="CreditorId" style="width: 500px;">
						<option value="">-- PILIH CREDITOR --</option>
						<?php
						foreach ($creditors as $creditor) {
							if ($obal->CreditorId == $creditor->Id) {
								printf('<option value="%d" selected="selected" data-code="%s" data-name="%s">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName, $creditor->CreditorCd, $creditor->CreditorName);
							} else {
								printf('<option value="%d" data-code="%s" data-name="%s">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName, $creditor->CreditorCd, $creditor->CreditorName);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="DebitAmount">Saldo Awal :</label></td>
				<td><input type="text" class="bold right" id="CreditAmount" name="CreditAmount" value="<?php print(number_format($obal->CreditAmount, 2)); ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Submit</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("ap.obal")); ?>">A/P Opening List</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
