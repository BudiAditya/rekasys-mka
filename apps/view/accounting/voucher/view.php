<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $docType DocType */ /** @var $accounts Coa[] */ /** @var $departments Department */ /** @var $activitys Activity[] */
/** @var $projects Project[] */ /** @var $debtors Debtor[] */ /** @var $creditors Creditor[] */ /** @var $employees Employee[] */ /** @var $voucher Voucher */
/** @var Asset[] $assets */ /** @var Voucher $previous */ /** @var Voucher $next */
?>
<head>
	<title>Rekasys - Voucher: <?php print($docType->Description); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		.linkVoucher {
			margin: 0 10px;
		}
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#linkDelete").click(function() {
                return confirm("Hapus voucher ini?");
            });
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
	<legend><span class="bold"><?php printf("Voucher: %s (%s)", $voucher->DocumentNo, $docType->Description); ?></span></legend>

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr>
			<td class="bold right">Company :</td>
			<td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
			<td class="bold right">Voucher Type :</td>
			<td><?php printf("%s - %s", $docType->DocCode, $docType->Description); ?></td>
			<td class="bold right">Status :</td>
			<td><?php print($voucher->GetStatus()); ?></td>
		</tr>
		<tr>
			<td class="bold right">Voucher No. :</td>
			<td><?php print($voucher->DocumentNo); ?></td>
			<td class="bold right">Document Date :</td>
			<td><?php print($voucher->FormatDate()); ?></td>
			<td class="bold right">Source :</td>
			<td><?php print($voucher->VoucherSource); ?></td>
		</tr>
		<tr>
			<td class="bold right">Voucher Notes :</td>
			<td colspan="5"><?php print(str_replace("\n", "<br />", $voucher->Note)); ?></td>
		</tr>
        <tr>
            <td class="bold right">Report Status :</td>
            <td><?php printf("%d - %s", $voucher->RStatus, $voucher->RStatus == 1 ? 'Normal' : 'Advanced'); ?></td>
        </tr>
	</table><br />

	<div id="container" style="min-width: 600px; display: inline-block; padding: 5px;">
		<div class="bold center subTitle">
			Detail Voucher:
		</div>

        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
            <tr class="bold center">
                <td>No.</td>
                <td>Debit</td>
                <td>Credit</td>
                <td>Description</td>
                <td>Amount</td>
                <td>Project</td>
                <td>Dept</td>
                <td>Activity</td>
                <td>Unit</td>
                <td>Debtor</td>
                <td>Kreditor</td>
                <td>Employee</td>
            </tr>
            <?php
            $counter = 0;
            $total = 0;
            $linkDebtor = $helper->site_url("master.debtor/view/");
            $linkCreditor = $helper->site_url("master.creditor/view/");
            $linkProject = $helper->site_url("master.project/view/");
            foreach($voucher->Details as $idx => $detail) {
                $counter++;

                if ($detail->DepartmentId == null) {
                    $deptName = "-";
                } else {
                    /** @var $dept Department */
                    $dept = $departments[$detail->DepartmentId];
                    $deptName = $dept == null ? "ERROR" : $dept->DeptCode;
                }
                if ($detail->ActivityId == null) {
                    $actName = "-";
                } else {
                    /** @var $act Activity */
                    $act = $activitys[$detail->ActivityId];
                    $actName = $act == null ? "ERROR" :  $act->ActCode;
                }
                if ($detail->UnitId == null) {
                    $unitName = "-";
                } else {
                    /** @var $unit Units */
                    $unit = $units[$detail->UnitId];
                    $unitName = $unit == null ? "ERROR" :  $unit->UnitName;
                }

                // Print data
                $total += $detail->Amount;
                print("<tr>");
                printf('<td class="right">%s.</td>', ($idx + 1));
                printf('<td>%s</td>', $accounts[$detail->AccDebitId]->AccNo);
                printf('<td>%s</td>', $accounts[$detail->AccCreditId]->AccNo);
                printf('<td>%s</td>', str_replace("\n", "<br />", $detail->Note));
                printf('<td class="right">%s</td>', number_format($detail->Amount, 2));
                if ($detail->ProjectId == null) {
                    print("<td>-</td>");
                } else {
                    printf('<td><a target="_blank" href="%s">%s</a></td>', $linkProject . $detail->ProjectId, $projects[$detail->ProjectId]->ProjectName);
                }
                printf('<td>%s</td>', $deptName);
                printf('<td>%s</td>', $actName);
                printf('<td>%s</td>', $unitName);
                if ($detail->DebtorId == null) {
                    print("<td>-</td>");
                } else {
                    printf('<td><a target="_blank" href="%s">%s</a></td>', $linkDebtor . $detail->DebtorId, $debtors[$detail->DebtorId]->DebtorName);
                }
                if ($detail->CreditorId == null) {
                    print("<td>-</td>");
                } else {
                    printf('<td><a target="_blank" href="%s">%s</a></td>', $linkCreditor . $detail->CreditorId, $creditors[$detail->CreditorId]->CreditorName);
                }
                if ($detail->EmployeeId == null) {
                    print("<td>-</td>");
                } else {
                    printf('<td>%s</td>', $employees[$detail->EmployeeId]->Nama);
                }

                print("</tr>");
            }
            ?>
            <tr class="bold right">
                <td colspan="4">TOTAL : </td>
                <td><?php print(number_format($total, 2)); ?></td>
                <td colspan="7">&nbsp;</td>
            </tr>
        </table><br />

		<div class="center">
			<a style="height: 20px;vertical-align: middle" class="button" href="<?php print($helper->site_url("accounting.voucher")); ?>">Voucher List</a>
			<?php

			$acl = AclManager::GetInstance();
			if($voucher->StatusCode == 1){
				if ($acl->CheckUserAccess("accounting.voucher", "edit_master")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Edit</a>', $helper->site_url("accounting.voucher/edit_master/".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "delete")) {
					printf('<a style="height: 20px;vertical-align: middle" id="linkDelete" class="button" href="%s">Delete</a>', $helper->site_url("accounting.voucher/delete/".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "batch_approve")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Approve</a>', $helper->site_url("accounting.voucher/batch_approve/?id[]=".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "print")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="$s">Print</a>', $helper->site_url("accounting.voucher/print/?id[]=".$voucher->Id));
				}
			} elseif ($voucher->StatusCode == 2) {
				if ($acl->CheckUserAccess("accounting.voucher", "batch_disapprove")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Dis-Approve</a>', $helper->site_url("accounting.voucher/batch_disapprove/?id[]=".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "verify")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Verify</a>', $helper->site_url("accounting.voucher/verify/?id[]=".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "print")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Print</a>', $helper->site_url("accounting.voucher/print/?id[]=".$voucher->Id));
				}
			} elseif($voucher->StatusCode == 3){
				if ($acl->CheckUserAccess("accounting.voucher", "unverify")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Un-Verify</a>', $helper->site_url("accounting.voucher/unverify/?id[]=".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "posting")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Posting</a>', $helper->site_url("accounting.voucher/posting/?id[]=".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "print")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Print</a>', $helper->site_url("accounting.voucher/print/?id[]=".$voucher->Id));
				}
			} elseif($voucher->StatusCode == 4){
				if ($acl->CheckUserAccess("accounting.voucher", "unposting")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Un-Posting</a>', $helper->site_url("accounting.voucher/unposting/?id[]=".$voucher->Id));
				}
				if ($acl->CheckUserAccess("accounting.voucher", "print")) {
					printf('<a style="height: 20px;vertical-align: middle" class="button" href="%s">Print</a>', $helper->site_url("accounting.voucher/print/?id[]=".$voucher->Id));
				}
			}
			?>
		</div>
		<?php if ($previous != null || $next != null) {
			print('<br /><div class="center">');
			if ($previous != null) {
				printf('<a style="height: 20px;vertical-align: middle" class="button linkVoucher" href="%s">&laquo; %s</a>', $helper->site_url("accounting.voucher/view/" . $previous->Id), $previous->DocumentNo);
			}
			if ($next != null) {
				printf('<a style="height: 20px;vertical-align: middle" class="button linkVoucher" href="%s">%s &raquo;</a>', $helper->site_url("accounting.voucher/view/" . $next->Id), $next->DocumentNo);
			}
			print('</div>');
		} ?>
	</div>


</fieldset>

</body>
</html>
