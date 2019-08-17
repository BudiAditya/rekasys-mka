<!DOCTYPE html>
<html>
<?php
/** @var $modules Module[] */ /** @var $trxClasses TrxClass[] */ /** @var $accounts Coa[] */ /** @var $company Company */ /** @var $trxType TrxTypeBase */
?>
<head>
	<title>Rekasys - Tambah Data Jenis Transaksi</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
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
	<legend><b>Tambah Data Jenis dan Kode Transaksi</b></legend>
	<form id="frm" action="<?php print($helper->site_url("common.trxtype/add")); ?>" method="post">
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td align="right">Company :</td>
				<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			</tr>
			<tr>
				<td align="right"><label for="TrxCd">Kode :</label></td>
				<td><input type="text" name="TrxCd" id="TrxCd" size="50" maxlength="50" value="<?php print($trxType->Code); ?>" /></td>
			</tr>
			<tr>
				<td align="right"><label for="TrxDesc">Jenis Transaksi :</label></td>
				<td><input type="text" name="TrxDesc" id="TrxDesc" size="50" maxlength="100" value="<?php print($trxType->Description); ?>" /></td>
			</tr>
			<tr>
				<td align="right"><label for="ModuleId">Module :</label></td>
				<td><select name="ModuleId" id="ModuleId">
					<option value=""></option>
					<?php
					foreach ($modules as $mod) {
						if ($mod->Id == $trxType->ModuleId) {
							printf('<option value="%s" selected="selected">%s - %s</option>', $mod->Id, $mod->ModuleCd, $mod->ModuleName);
						} else {
							printf('<option value="%s">%s - %s</option>', $mod->Id, $mod->ModuleCd, $mod->ModuleName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td align="right"><label for="TrxClassId">Kelas :</label></td>
				<td><select name="TrxClassId" id="TrxClassId">
					<option value=""></option>
					<?php
					foreach ($trxClasses as $txclass) {
						if ($txclass->Id == $trxType->TrxClassId) {
							printf('<option value="%s" selected="selected">%s - %s</option>', $txclass->Id, $txclass->TrxClassCd, $txclass->TrxClassDesc);
						} else {
							printf('<option value="%s">%s - %s</option>', $txclass->Id, $txclass->TrxClassCd, $txclass->TrxClassDesc);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td align="right"><label for="AccDebitId">Kode Akun Debet :</label></td>
				<td>
					<select name="AccDebitId" id="AccDebitId">
						<option value="">-- TIDAK ADA -- (AKAN MEMILIH BANK / KAS)</option>
						<?php
						foreach ($accounts as $acn) {
							if ($acn->Id == $trxType->AccDebitId) {
								printf('<option value="%s" selected="selected">%s - %s</option>', $acn->Id, $acn->AccNo, $acn->AccName);
							} else {
								printf('<option value="%s">%s - %s</option>', $acn->Id, $acn->AccNo, $acn->AccName);
							}
						}
						?>
					</select><br />
					<input type="checkbox" id="ShowDebit" name="ShowDebit" value="1" <?php print($trxType->ShowDebit ? 'checked="checked"' : ''); ?> />
					<label for="ShowDebit">Tampilkan No Akun Debet</label>
				</td>
			</tr>
			<tr>
				<td align="right"><label for="AccCreditId">Kode Akun Kredit :</label></td>
				<td>
					<select name="AccCreditId" id="AccCreditId">
						<option value="">-- TIDAK ADA -- (AKAN MEMILIH BANK / KAS)</option>
						<?php
						foreach ($accounts as $acn) {
							if ($acn->Id == $trxType->AccCreditId) {
								printf('<option value="%s" selected="selected">%s - %s</option>', $acn->Id, $acn->AccNo, $acn->AccName);
							} else {
								printf('<option value="%s">%s - %s</option>', $acn->Id, $acn->AccNo, $acn->AccName);
							}
						}
						?>
					</select><br />
					<input type="checkbox" id="ShowCredit" name="ShowCredit" value="1" <?php print($trxType->ShowCredit ? 'checked="checked"' : ''); ?> />
					<label for="ShowCredit">Tampilkan No Akun Kredit</label>
				</td>
			</tr>
			<tr>
				<td align="right">Wajib Ada:</td>
				<td>
					<input type="checkbox" id="reqDebtor" name="reqDebtor" value="<?php print(TrxTypeBase::REQUIRE_DEBTOR); ?>" <?php print($trxType->IsRequireDebtor() ? 'checked="checked"' : ''); ?> />
					<label for="reqDebtor">Debtor</label>
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" id="reqCreditor" name="reqCreditor" value="<?php print(TrxTypeBase::REQUIRE_CREDITOR); ?>" <?php print($trxType->IsRequireCreditor() ? 'checked="checked"' : ''); ?> />
					<label for="reqCreditor">Creditor</label>
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" id="reqEmployee" name="reqEmployee" value="<?php print(TrxTypeBase::REQUIRE_EMPLOYEE); ?>" <?php print($trxType->IsRequireEmployee() ? 'checked="checked"' : ''); ?> />
					<label for="reqEmployee">Karyawan</label>
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" id="reqAsset" name="reqAsset" value="<?php print(TrxTypeBase::REQUIRE_ASSET); ?>" <?php print($trxType->IsRequireAsset() ? 'checked="checked"' : ''); ?> />
					<label for="reqAsset">Asset</label>
					<br />
					NOTE: Centang data-data yang wajib ada berhubungan dengan transaksi diatas. Boleh lebih dari 1
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("common.trxtype")); ?>" class="button">Daftar Jenis Transaksi</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
</body>
</html>
