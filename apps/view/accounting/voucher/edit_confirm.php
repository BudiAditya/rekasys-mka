<!DOCTYPE HTML>
<html>
<?php
/** @var $company Company */ /** @var $docType DocType */ /** @var $accounts Coa[] */ /** @var $departments Department[] */ /** @var $divisions Division[] */
/** @var $debtors Debtor[] */ /** @var $creditors Creditor[] */ /** @var $employees Employee[] */ /** @var $projects Project[] */ /** @var $voucher Voucher */
?>
<head>
	<title>Rekasys - Edit Voucher Step 3</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/flexigrid.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />

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
	<legend><span class="bold">Edit Voucher Step 3 - Konfirmasi</span></legend>

    <table cellpadding="0" cellspacing="0" class="tablePadding">
        <tr>
            <td class="bold right">Company :</td>
            <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
        </tr>
        <tr>
            <td class="bold right">Voucher Type :</td>
            <td><?php printf("%s - %s", $docType->DocCode, $docType->Description); ?></td>
        </tr>
        <tr>
            <td class="bold right">Voucher No. :</td>
            <td><?php print($voucher->DocumentNo); ?></td>
        </tr>
        <tr>
            <td class="bold right">Voucher Source :</td>
            <td><?php print($voucher->VoucherSource); ?></td>
        </tr>
        <tr>
            <td class="bold right">Voucher Date :</td>
            <td><?php print($voucher->FormatDate()); ?></td>
        </tr>
        <tr>
            <td class="bold right">Notes :</td>
            <td><?php print(str_replace("\n", "<br />", $voucher->Note)); ?></td>
        </tr>
        <tr>
            <td class="bold right">Report Status :</td>
            <td><?php printf("%d - %s", $voucher->RStatus, $voucher->RStatus == 1 ? 'Normal' : 'Advanced'); ?></td>
        </tr>
    </table>

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
                if ($detail->MarkedForDeletion) {
                    continue;
                }
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

		<form action="<?php print($helper->site_url("accounting.voucher/edit_confirm/" . $voucher->Id)); ?>" method="post" class="center">
			<input type="hidden" name="confirmed" value="1" />
			<button type="button" onclick="window.location='<?php print($helper->site_url("accounting.voucher/edit_detail/" . $voucher->Id)); ?>';">&lt; Sebelumnya</button>
			&nbsp;&nbsp;&nbsp;
			<button type="submit">Simpan</button>
		</form>

	</div>


</fieldset>

</body>
</html>
