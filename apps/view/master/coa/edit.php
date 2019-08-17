<!DOCTYPE HTML>
<html>
<?php /** @var $jsCoa array */ /** @var $coa Coa */ ?>
<head>
	<title>Rekasys - Edit Chart of Account</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var jsCoa = eval(<?php print(json_encode($jsCoa)); ?>);
		var ddlAccParentId;

		$(document).ready(function() {
			ddlAccParentId = $("#AccParentId");
			$("#AccLevel").change(function(e) { ddlType_Changed(this, e); }).change();
			ddlAccParentId.val(<?php print($coa->AccParentId); ?>);
		});

		function ddlType_Changed(sender, e) {
			if (sender.value == 0) {
				ddlAccParentId.html('<option value="">-- TIDAK ADA AKUN UTAMA --</option>');
			} else {
				var buff = jsCoa[sender.value - 1];
				if (buff == undefined) {
					alert("Data Akun Utama tidak ditemukan !");
					return;
				}

				var options = '';
				for (var i in buff) {
					//console.log(buff[i].AccountNo);
					options += '<option value="' + buff[i].Id + '">' + buff[i].AccNo + ' - ' + buff[i].AccName + '</option>';
				}
				ddlAccParentId.html(options);
			}
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
	<legend><span class="bold">Edit Chart of Account</span></legend>

	<form action="<?php print($helper->site_url("master.coa/edit/".$coa->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="AccLevel">Account Level :</label></td>
				<td colspan="3"><select id="AccLevel" name="AccLevel" required>
					<option value="0" <?php print($coa->AccLevel == 0 ? 'selected="selected"' : ''); ?>>0 - Master</option>
					<option value="1" <?php print($coa->AccLevel == 1 ? 'selected="selected"' : ''); ?>>1 - Header</option>
					<option value="2" <?php print($coa->AccLevel == 2 ? 'selected="selected"' : ''); ?>>2 - Sub Header</option>
					<option value="3" <?php print($coa->AccLevel == 3 ? 'selected="selected"' : ''); ?>>3 - Transaction</option>
				</select></td>
			</tr>
			<tr>
				<td class="bold right"><label for="AccParentId">Account Parent :</label></td>
				<td colspan="3"><select id="AccParentId" name="AccParentId"></select></td>
			</tr>
			<tr>
				<td class="bold right"><label for="AccNo">Account Number :</label></td>
				<td colspan="3"><input type="number" id="AccNo" name="AccNo" maxlength="12" value="<?php print($coa->AccNo); ?>" required/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="AccName">Account Name :</label></td>
                <td colspan="3"><input type="text" id="AccName" name="AccName" value="<?php print($coa->AccName); ?>" size="55" required onkeyup="this.value = this.value.toUpperCase();"/></td>
            </tr>
			<tr>
				<td class="bold right"><label for="DcSaldo">D/C Position :</label></td>
				<td>
					<select id="DcSaldo" name="DcSaldo" required>
						<option value="D" <?php print($coa->DcSaldo == "D" ? 'selected="selected"' : ''); ?>>DEBIT</option>
						<option value="K" <?php print($coa->DcSaldo == "K" ? 'selected="selected"' : ''); ?>>CREDIT</option>
					</select>
				</td>
                <td class="bold right"><label for="AccStatus">Status :</label></td>
                <td>
                    <select id="AccStatus" name="AccStatus" required>
                        <option value="1" <?php print($coa->AccStatus == "1" ? 'selected="selected"' : ''); ?>> 1 - AKTIF </option>
                        <option value="0" <?php print($coa->AccStatus == "0" ? 'selected="selected"' : ''); ?>> 0 - NON-AKTIF </option>
                    </select>
                </td>
			</tr>
			<tr>
                <td>&nbsp;</td>
				<td colspan="3">
					<button>UPDATE</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("master.coa")); ?>">COA List</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

</body>
</html>
