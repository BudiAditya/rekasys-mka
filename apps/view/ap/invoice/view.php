<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $invoice Invoice */
/** @var $creditors Creditor[] */
$counter = 0;
?>
<head>
    <title>REKASYS | A/P Invoice Review</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/sweetalert.min.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>
    <style scoped>
        .f1{
            width:200px;
        }
    </style>

    <style type="text/css">
        #fd{
            margin:0;
            padding:5px 10px;
        }
        .ftitle{
            font-size:14px;
            font-weight:bold;
            padding:5px 0;
            margin-bottom:10px;
            border-bottom:1px solid #ccc;
        }
        .fitem{
            margin-bottom:5px;
        }
        .fitem label{
            display:inline-block;
            width:100px;
        }
        .numberbox .textbox-text{
            text-align: right;
            color: blue;
        }
    </style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php }
$badd = base_url('public/images/button/').'add.png';
$bsave = base_url('public/images/button/').'accept.png';
$bcancel = base_url('public/images/button/').'cancel.png';
$bview = base_url('public/images/button/').'view.png';
$bedit = base_url('public/images/button/').'edit.png';
$bdelete = base_url('public/images/button/').'delete.png';
$bclose = base_url('public/images/button/').'close.png';
$bsearch = base_url('public/images/button/').'search.png';
$bkembali = base_url('public/images/button/').'back.png';
$bcetak = base_url('public/images/button/').'printer.png';
$bsubmit = base_url('public/images/button/').'ok.png';
$baddnew = base_url('public/images/button/').'create_new.png';
$bpdf = base_url('public/images/button/').'pdf.png';
?>
<br />
<div id="p" class="easyui-panel" title="A/P Invoice Review" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" disabled>
                    <option value="">- Pilih Proyek -</option>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project) {
                        if ($project->Id == $invoice->ProjectId) {
                            printf('<option value="%d" selected="selected">%s</option>', $project->Id, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s</option>', $project->Id, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($invoice->Id);?>"/>
                    <input type="hidden" name="InvNo" id="InvNo" value="<?php print($invoice->InvoiceNo);?>"/>
                </select>
            </td>
            <td class="right">Invoice Type :</td>
            <td><select class="easyui-combobox" id="InvoiceType" name="InvoiceType" style="width: 130px" required>
                    <option value=""></option>
                    <?php
                    /** @var $invtypes ArInvoiceType[] */
                    foreach ($invtypes as $type) {
                        if ($type->Id == $invoice->InvoiceType) {
                            printf('<option value="%d" selected="selected">%s</option>', $type->Id, $type->InvoiceType);
                        } else {
                            printf('<option value="%d">%s</option>', $type->Id, $type->InvoiceType);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">Invoice No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '[AUTO]'); ?>" disabled/></td>
            <td class="right">Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 117px" id="InvoiceStatus" name="InvoiceStatus" value="<?php print($invoice->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Creditor :</td>
            <td><select class="easyui-combobox" id="CreditorId" name="CreditorId" style="width: 250px" disabled>
                    <option value="">- Pilih Creditor -</option>
                    <?php
                    foreach ($creditors as $creditor) {
                        if ($creditor->Id == $invoice->CreditorId) {
                            printf('<option value="%d" selected="selected">%s</option>', $creditor->Id, $creditor->CreditorName);
                        } else {
                            printf('<option value="%d">%s</option>', $creditor->Id, $creditor->CreditorName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">Invoice Date :</td>
            <td><input type="text" style="width: 127px" class="bold" id="InvoiceDate" name="InvoiceDate" value="<?php print($invoice->FormatInvoiceDate(JS_DATE));?>" disabled/></td>
            <td class="right">Credit Terms :</td>
            <td><input type="text" style="width: 50px" class="bold right" id="CreditTerms" name="CreditTerms" value="<?php print($invoice->CreditTerms);?>" disabled/>&nbsp;Day(s)</td>
            <td class="right">Due Date :</td>
            <td><input type="text" style="width: 100px" class="bold" id="DueDate" name="DueDate" value="<?php print($invoice->FormatDueDate(JS_DATE));?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 30px;" value="<?php print($invoice->InvoiceDescs);?>" disabled></td>
            <td class="right">Reff No. :</td>
            <td><input type="text" class="easyui-textbox"  style="width: 130px" id="ReffNo" name="ReffNo" value="<?php print($invoice->ReffNo);?>" disabled/></td>
            <td class="right">GRN No :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="GrnNo" name="GrnNo" value="<?php print($invoice->GrnNo); ?>" disabled/></td>
            <td colspan="2" class="right">
                <?php
                if ($acl->CheckUserAccess("ap.invoice", "approve") && $invoice->InvoiceStatus == 0 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btApprove" href="%s" class="button"><b><font color="#a52a2a">APPROVE</font></b></a>',$helper->site_url("ap.invoice/approve?&id[]=".$invoice->Id));
                }
                if ($acl->CheckUserAccess("ap.invoice", "approve") && $invoice->InvoiceStatus == 1 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btUnapprove" href="%s" class="button"><b><font color="#a52a2a">UNAPPROVE</font></b></a>',$helper->site_url("ap.invoice/unapprove?&id[]=".$invoice->Id));
                }
                if ($acl->CheckUserAccess("ap.invoice", "posted") && $invoice->InvoiceStatus == 1 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btPosting" href="%s" class="button"><b><font color="#dc143c">POSTING</font></b></a>',$helper->site_url("ap.invoice/posting?&id[]=".$invoice->Id));
                }
                if ($acl->CheckUserAccess("ap.invoice", "posted") && $invoice->InvoiceStatus == 2 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btUnposting" href="%s" class="button"><b><font color="#dc143c">UNPOSTING</font></b></a>',$helper->site_url("ap.invoice/unposting?&id[]=".$invoice->Id));
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="10">Invoice Detail</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Trx Code</th>
                        <th>Description</th>
                        <th>QTY</th>
                        <th>UOM</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                        <th>Dept</th>
                        <th>Activity</th>
                        <th>Unit</th>
                    </tr>
                    <?php
                    $dtx = null;
                    foreach($invoice->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td class='right'>%s</td>",number_format($detail->Qty,0));
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",number_format($detail->Price,0));
                        printf("<td class='right'>%s</td>",number_format($detail->Qty * $detail->Price,0));
                        printf("<td>%s</td>",$detail->DeptName);
                        printf("<td>%s</td>",$detail->ActName);
                        printf("<td>%s</td>",$detail->UnitCode);
                        print ("</tr>");
                    }
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="bold right">Sub Total</td>
                        <td class="bold right"><?php print(number_format($invoice->BaseAmount,0)) ?></td>
                        <td colspan="3">&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="bold right">Discount</td>
                        <td class="bold right"><input type="text" class="bold right" name="Disc1Pct" id="Disc1Pct" size="3" maxlength="3" value="<?php print($invoice->Disc1Pct);?>" disabled>%</td>
                        <td class="bold right"><?php print(number_format($invoice->Disc1Amount,0)) ?></td>
                        <td colspan="3">&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bold right">Jenis Pajak 1 :</td>
                        <td colspan="3" class="bold"><select name="TaxType1Id" id="TaxType1Id" style="width:200px;height:20px;" disabled>
                                <option value=""></option>
                                <?php
                                /** @var $taxtypes TaxType[] */
                                foreach ($taxtypes as $taxtype){
                                    if ($taxtype->Id == $invoice->TaxType1Id) {
                                        printf("<option value='%d' selected='selected'>%s - %s</option>", $taxtype->Id, $taxtype->TaxCode, $taxtype->TaxType);
                                    }else{
                                        printf("<option value='%d'>%s - %s</option>",$taxtype->Id,$taxtype->TaxCode,$taxtype->TaxType);
                                    }

                                }
                                ?>
                            </select>
                        </td>
                        <td class="bold right"><input type="text" class="bold right" name="Tax1Rate" id="Tax1Rate" size="3" maxlength="3" value="<?php print($invoice->Tax1Rate);?>" disabled>%</td>
                        <td class="bold right"><?php print(number_format($invoice->Tax1Amount,0)) ?></td>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bold right">Jenis Pajak 2 :</td>
                        <td colspan="3" class="bold"><select name="TaxType2Id" id="TaxType2Id" style="width:200px;height:20px;" disabled>
                                <option value=""></option>
                                <?php
                                /** @var $taxtypes TaxType[] */
                                foreach ($taxtypes as $taxtype){
                                    if ($taxtype->Id == $invoice->TaxType2Id) {
                                        printf("<option value='%d' selected='selected'>%s - %s</option>", $taxtype->Id, $taxtype->TaxCode, $taxtype->TaxType);
                                    }else{
                                        printf("<option value='%d'>%s - %s</option>",$taxtype->Id,$taxtype->TaxCode,$taxtype->TaxType);
                                    }

                                }
                                ?>
                            </select>
                        </td>
                        <td class="bold right"><input type="text" class="bold right" name="Tax2Rate" id="Tax2Rate" size="3" maxlength="2" value="<?php print($invoice->Tax2Rate);?>" disabled>%</td>
                        <td class="bold right"><?php print(number_format($invoice->Tax2Amount,0)) ?></td>
                        <td colspan="3">&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bold right">No. Seri Faktur PPN :</td>
                        <td colspan="3" class="bold"><input type="text" class="bold" name="TaxInvoiceNo" id="TaxInvoiceNo" style="width:200px" maxlength="25" value="<?php print($invoice->TaxInvoiceNo);?>" disabled></td>
                        <td class="bold right">Grand Total</td>
                        <td class="bold right"><?php print(number_format($invoice->BaseAmount + $invoice->Tax1Amount + $invoice->Tax2Amount,0)) ?></td>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="10" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("ap.invoice", "add")) {
                                printf('<img src="%s" alt="New Invoice" title="New Invoice" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("ap.invoice", "edit")) {
                                printf('<img src="%s" alt="Edit Invoice" title="Edit Invoice" id="bEdit" style="cursor: pointer;"/>&nbsp;&nbsp;',$bedit);
                            }
                            if ($acl->CheckUserAccess("ap.invoice", "delete")) {
                                printf('<img src="%s" alt="Void Invoice" title="Void Invoice" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("ap.invoice", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print Invoice" title="Print Invoice" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="Invoice List" title="Invoice List" style="cursor: pointer;"/>',$bkembali);
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2019  PT. Rekasystem Technology
</div>

<script type="text/javascript">
    var InvId = "<?php print($invoice->Id);?>";
    var InvStatus = "<?php print($invoice->InvoiceStatus);?>";
    $(document).ready(function() {
        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("ap.invoice/add/0")); ?>";
        });

        $("#bEdit").click(function(e){
            if (InvId > 0) {
                if (confirm("Edit Data Invoice ini?")) {
                    location.href = "<?php print($helper->site_url("ap.invoice/add/")); ?>" + InvId;
                }
            }
        });

        $("#bHapus").click(function(e){
            if (InvStatus == 0) {
                if (InvId > 0) {
                    if (confirm("Void Data Invoice ini?")) {
                        location.href = "<?php print($helper->site_url("ap.invoice/void/")); ?>" + InvId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (InvId > 0) {
                if (confirm("Cetak Data Invoice ini?")) {
                    location.href = "<?php print($helper->site_url("ap.invoice/doc_print/pdf?&id[]=")); ?>" + InvId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("ap.invoice")); ?>";
        });

    });

</script>
</body>
</html>
