<!DOCTYPE HTML>
<html>
<?php
/** @var $output string */
/** @var $creditors Creditor[] */ /** @var $credt Creditor */ /** @var $creditorId int */
/** @var $docTypes DocType[] */ /** @var $doc DocType */ /** @var $docTypeId int */
/** @var $status int */ /** @var $report ReaderBase */ /** @var $startDate int */ /** @var $endDate int */
?>
<head>
    <title>Rekasys - AP Invoice Listing </title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/select2/select2.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<style type="text/css">
		.colCode { display: inline-block; width: 90px; overflow: hidden; border-right: black 1px dotted; margin: 0 2px; text-align: center; }
		.colText { display: inline-block; width: 310px; overflow: hidden; white-space: nowrap; margin: 0 2px; }
		.blue { color: blue; }
	</style>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/select2.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#startDate").customDatePicker();
            $("#endDate").customDatePicker();

			$("#creditorId").select2({
				placeholderOption: "first",
				allowClear: false,
				minimumInputLength: 1,
				formatResult: formatOptionList,
				formatSelection: formatOptionResult
			});
        });

		function formatOptionList(state) {
			if (state.id == -1) {
				return "-- SEMUA CREDITOR --";
			}

			var originalOption = $(state.element);
			return '<div class="colCode">' + originalOption.data("code") + '</div><div class="colText">' + originalOption.data("name") + '</div>';
		}

		function formatOptionResult(state) {
			if (state.id == -1) {
				return "-- SEMUA CREDITOR --";
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
    <legend><span class="bold">Report Data Invoice</span></legend>

    <form action="<?php print($helper->site_url("ap.invoice/overview")); ?>" method="get">
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
            <tr>
                <td><label for="startDate">Tanggal : </label></td>
                <td>
                    <input type="text" id="startDate" name="startDate" value="<?php print(date(JS_DATE, $startDate)); ?>" />
                    <label for="endDate">s.d </label>
                    <input type="text" id="endDate" name="endDate" value="<?php print(date(JS_DATE, $endDate)); ?>" /> 
                </td>
            </tr>
            <tr>
                <td><label for="creditorId">Creditor : </label></td>
                <td>
                    <select id="creditorId" name="creditorId" style="width:450px;">
                        <option value="-1">-- SEMUA CREDITOR --</option>
                        <?php
                        foreach($creditors as $creditor){
                            if($creditor->Id ==  $creditorId) {
                                printf('<option value="%d" data-code="%s" data-name="%s" selected="selected">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName, $creditor->CreditorCd, $creditor->CreditorName);
                            } else {
                                printf('<option value="%d" data-code="%s" data-name="%s">%s - %s</option>', $creditor->Id, $creditor->CreditorCd, $creditor->CreditorName, $creditor->CreditorCd, $creditor->CreditorName);
                            }
                        }
                        ?>
                    </select> 
                </td>
            </tr>
            <tr>
                <td><label for="docTypeId">Jenis Tagihan : </label></td>
                <td>
                    <select id="docTypeId" name="docTypeId">
                        <option value="-1">-- SEMUA JENIS TAGIHAN --</option>
                        <option value="8">IN - INVOICE SUPPLIER</option>
                        <option value="7">IK - INVOICE KONTRAKTOR</option>
                    </select> 
                </td>
            </tr>
            <tr>
                <td><label for="status">Status : </label></td>
                <td>
                    <select id="status" name="status">
                        <option value="-1" <?php print($status == -1 ? 'selected="selected"' : ''); ?>>-- SEMUA STATUS --</option>
                        <option value="0" <?php print($status == 0 ? 'selected="selected"' : ''); ?>>-- UNPOSTED --</option>
                        <option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>-- POSTED --</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="output">Output laporan: </label></td>
                <td>
                    <select id="output" name="output">
                        <option value="web">Web Browser</option>
                        <option value="xls">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                    <button type="submit">Generate</button>
                </td>
            </tr>
        </table>
    </form>
</fieldset>

<?php if($report != null) {
    $creditorName = $credt != null ? $credt->CreditorName : "SEMUA CREDITOR";
    $docDesc = $doc != null ? $doc->Description : "SEMUA JENIS TAGIHAN";
    if ($status == -1) {
        $statusName = "SEMUA STATUS";
    } elseif ($status == 0) {
        $statusName = "UNPOSTED";
    } else {
        $statusName = "POSTED";
    }
?>
<br />
<div class="container">
    <div>
        <div class="title center">Report Data Invoice</div>
		<div class="subTitle center">
			<?php printf("Tanggal %s s.d. %s", date(HUMAN_DATE, $startDate), date(HUMAN_DATE, $endDate)); ?><br />
			Creditor : <?php echo $creditorName; ?><br />
			Jenis Tagihan : <?php echo $docDesc; ?><br />
			Status : <?php echo $statusName; ?>
		</div><br />

        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="margin: 0 auto;">
            <tr class="center">
                <th>No.</th>
                <th>Project</th>
                <th>No.Invoice</th>
                <th>Tgl.Invoice</th>
				<th>No. Reference</th>
                <th>Nama Creditor</th>
                <th>Deskripsi</th>
                <th>DPP</th>
                <th>PPN</th>
                <th>PPh</th>
				<th>Total</th>
            </tr>
            <?php
            $docNo = null;
            $baseAmount = 0;
            $taxAmount = 0;
            $deductAmount = 0;
            $i = 1;
            while($rs = $report->FetchAssoc()) {
                if ($rs["invoice_no"] != $docNo){
                    $entity = $rs["entity_cd"];
                    $doc = $rs["invoice_no"];
                    $date = date('d M Y', strtotime($rs["invoice_date"]));
					$reff = $rs["reff_no"];
                    $namaCreditor = $rs["creditor_name"];
                    $docNo = $rs["invoice_no"];
                    $counter = $i++;
                } else {
                    $entity = "";
                    $doc = "";
                    $date = "";
					$reff = "";
                    $namaCreditor = "";
                    $counter = "";
                }
                $baseAmount += $rs["base_amount"];
                $taxAmount += $rs["vat_amount"];
                $deductAmount += $rs["wht_amount"];
            ?>
			<tr>
				<td class="center"><?php echo $counter; ?></td>
				<td nowrap><?php echo $rs["project_name"]; ?></td>
				<td nowrap><?php echo $doc; ?></td>
				<td nowrap><?php echo $date ; ?></td>
				<td nowrap><?php echo $reff ; ?></td>
				<td nowrap><?php echo $namaCreditor; ?></td>
				<td nowrap><?php echo $rs["invoice_descs"]; ?></td>
				<td class="right"><?php echo number_format($rs["base_amount"]); ?></td>
				<td class="right"><?php echo number_format($rs["vat_amount"]); ?></td>
				<td class="right"><?php echo number_format($rs["wht_amount"]); ?></td>
				<td class="right"><?php echo number_format($rs["base_amount"] + $rs["vat_amount"] - $rs["wht_amount"]); ?></td>
			</tr>
            <?php }?>
            <tr class="right">
                <td colspan="7">TOTAL</td>
                <td><?php echo number_format($baseAmount); ?></td>
                <td><?php echo number_format($taxAmount); ?></td>
                <td><?php echo number_format($deductAmount); ?></td>
				<td><?php echo number_format($baseAmount + $taxAmount - $deductAmount); ?></td>
            </tr>
        </table>
    </div>
</div>
    <?php } ?>

</body>
</html>
