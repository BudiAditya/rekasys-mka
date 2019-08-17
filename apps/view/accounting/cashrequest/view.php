<!DOCTYPE HTML>
<html>
<?php /** @var $company Company */ /** @var $cashRequest CashRequest */ /** @var $accounts Coa[] */ ?>
<head>
	<title>Rekasys - Cash Request (NPKP): <?php print($cashRequest->DocumentNo); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		var npkp = '<?php print($cashRequest->DocumentNo) ?>';
		var objective = '<?php print($cashRequest->Objective); ?>';

		$(document).ready(function() {
			// Untuk NPKP ada sedikit perbedaan status. untuk link approve maka statusnya verifikasi
			$("#btnApprove").click(function() { return confirmAction(this, "Apakah anda mau meng-Verifikasi NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
			$("#btnDelete").click(function() { return confirmAction(this, "Apakah anda mau menghapus NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
			$("#btnVerify").click(function() { return confirmAction(this, "Apakah anda mau meng-Approve NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
			$("#btnUnApprove").click(function() { return confirmAction(this, "Apakah anda mau membatalkan Verifikasi NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
			$("#btnApprove2").click(function() { return confirmAction(this, "Apakah anda mau meng-Approve Lv 2 NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
			$("#btnUnVerify").click(function() { return confirmAction(this, "Apakah anda mau membatalkan Approve NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
			$("#btnUnApprove2").click(function() { return confirmAction(this, "Apakah anda mau membatalkan Approval Lv 2 NPKP: " + npkp + "\n\nTujuan NPKP:\n" + objective); });
		});

		function confirmAction(anchor, message) {
			if (confirm(message)) {
				window.location = anchor.href;
			}

			return false;
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
	<legend><span class="bold">Cash Request (NPKP): <?php print($cashRequest->DocumentNo); ?></span></legend>

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
    </table><br />

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

		<div class="center">
			<a href="<?php print($helper->site_url("accounting.cashrequest")); ?>">Cash Request (NPKP) List</a>
            &nbsp;
			<?php if ($cashRequest->StatusCode == 1) { ?>
			<a href="<?php print($helper->site_url("accounting.cashrequest/edit/" .  $cashRequest->Id)); ?>"><button>Edit</button></a>
                &nbsp;
			<a id="btnApprove" href="<?php print($helper->site_url("accounting.cashrequest/approve?id[]=" .  $cashRequest->Id)); ?>"><button>Verifikasi</button></a>
			&nbsp;&nbsp;&nbsp;
			<a id="btnDelete" href="<?php print($helper->site_url("accounting.cashrequest/delete/" .  $cashRequest->Id)); ?>"><button>Delete</button></a>
			<?php } else if ($cashRequest->StatusCode == 2) { ?>
			<a id="btnVerify" href="<?php print($helper->site_url("accounting.cashrequest/verify?id[]=" .  $cashRequest->Id)); ?>"><button>Approval</button></a>
			<a id="btnUnApprove" href="<?php print($helper->site_url("accounting.cashrequest/dis_approve?id[]=" .  $cashRequest->Id)); ?>"><button>Un-Verify</button></a>
			<?php } else if ($cashRequest->StatusCode == 3) { ?>
			<a id="btnApprove2" href="<?php print($helper->site_url("accounting.cashrequest/post?id[]=" .  $cashRequest->Id)); ?>"><button>Approve Lv. 2</button></a>
			<a id="btnUnVerify" href="<?php print($helper->site_url("accounting.cashrequest/dis_verify?id[]=" .  $cashRequest->Id)); ?>"><button>Un-Approve</button></a>
			<?php } else if ($cashRequest->StatusCode == 4) { ?>
			<a href="<?php print($helper->site_url("accounting.funding/add/" . $cashRequest->Id)); ?>"><button>Pencairan Dana</button></a>
			&nbsp;&nbsp;&nbsp;
			<a id="btnUnApprove2" href="<?php print($helper->site_url("accounting.cashrequest/un_post?id[]=" .  $cashRequest->Id)); ?>"><button>Batalkan Approve Lv. 2</button></a>
			<?php } ?>
		</div>

	</div>


</fieldset>

</body>
</html>
