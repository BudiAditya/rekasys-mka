<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $invoice Ap\Invoice */
/** @var $creditors Creditor[] */
$counter = 0;
?>
<head>
    <title>REKASYS | A/P Invoice Entry</title>
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
<div id="p" class="easyui-panel" title="A/P Invoice Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px">
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
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '[AUTO]'); ?>" readonly/></td>
            <td class="right">Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 117px" id="InvoiceStatus" name="InvoiceStatus" value="<?php print($invoice->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Creditor :</td>
            <td><select class="easyui-combobox" id="CreditorId" name="CreditorId" style="width: 250px">
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
            <td><input type="text" style="width: 127px" class="bold" id="InvoiceDate" name="InvoiceDate" value="<?php print($invoice->FormatInvoiceDate(JS_DATE));?>"/></td>
            <td class="right">Credit Terms :</td>
            <td><input type="text" style="width: 50px" class="bold right" id="CreditTerms" name="CreditTerms" value="<?php print($invoice->CreditTerms);?>"/>&nbsp;Day(s)</td>
            <td class="right">Due Date :</td>
            <td><input type="text" style="width: 100px" class="bold" id="DueDate" name="DueDate" value="<?php print($invoice->FormatDueDate(JS_DATE));?>"/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 30px;" value="<?php print($invoice->InvoiceDescs);?>"></td>
            <td class="right">Reff No. :</td>
            <td><input type="text" class="easyui-textbox"  style="width: 130px" id="ReffNo" name="ReffNo" value="<?php print($invoice->ReffNo);?>" required/></td>
            <td class="right">GRN No :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="GrnNo" name="GrnNo" value="<?php print($invoice->GrnNo); ?>" readonly/></td>
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
                        <th rowspan="2">Action</th>
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
                        print("<td class='center'>");
                        if ($acl->CheckUserAccess("ap.invoice", "edit") && $invoice->InvoiceStatus == 0) {
                            $dtx = addslashes($detail->Id.'|'.$detail->ItemId.'|'.$detail->ItemCode.'|'.$detail->ItemName.'|'.$detail->UomCd.'|'.$detail->Qty.'|'.$detail->Price.'|'.$detail->DeptId.'|'.$detail->ActivityId.'|'.$detail->IsAuto.'|'.$detail->ItemDescs.'|'.$detail->UnitId);
                            printf('&nbsp<img src="%s" alt="Edit Item" title="Edit Item" style="cursor: pointer" onclick="return fEditDetail(%s);"/>',$bedit,"'".$dtx."'");
                        }else{
                            print("&nbsp;");
                        }
                        if ($acl->CheckUserAccess("ap.invoice", "delete") && $invoice->InvoiceStatus == 0) {
                            printf('&nbsp<img src="%s" alt="Hapus Item" title="Hapus Item" style="cursor: pointer" onclick="return fDelDetail(%s);"/>', $bclose, $detail->Id);
                        }else{
                            print("&nbsp;");
                        }
                        print("</td>");
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
                        <?php if ($acl->CheckUserAccess("ap.invoice", "add")  && $invoice->InvoiceStatus == 0) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Add Item" title="Add Item" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="6" class="bold right">Sub Total</td>
                        <td class="bold right"><?php print(number_format($invoice->BaseAmount,0)) ?></td>
                        <td colspan="3">&nbsp</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="bold right">Discount</td>
                        <td class="bold right"><input type="text" class="bold right" name="Disc1Pct" id="Disc1Pct" size="3" maxlength="3" value="<?php print($invoice->Disc1Pct);?>">%</td>
                        <td class="bold right"><input type="text" class="bold right" name="Disc1Amount" id="Disc1Amount" size="10" value="<?php print(number_format($invoice->Disc1Amount,0));?>"></td>
                        <td colspan="3">&nbsp</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bold right">Jenis Pajak 1 :</td>
                        <td colspan="3" class="bold"><select name="TaxType1Id" id="TaxType1Id" style="width:200px;height:20px;">
                                <option value="0"></option>
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
                        <td class="bold right"><input type="text" class="bold right" name="Tax1Rate" id="Tax1Rate" size="3" maxlength="3" value="<?php print($invoice->Tax1Rate);?>">%</td>
                        <td class="bold right"><?php print(number_format($invoice->Tax1Amount,0)) ?></td>
                        <td colspan="3">&nbsp;</td>
                        <td class="center">
                            <?php
                            if ($acl->CheckUserAccess("ap.invoice", "edit") && $invoice->InvoiceStatus == 0) {
                                printf('<img src="%s" alt="Update Invoice" title="Update Invoice" id="bUpdate" style="cursor: pointer;"/>&nbsp;&nbsp;',$bsubmit);
                            }else{
                                print("&nbsp;");
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bold right">Jenis Pajak 2 :</td>
                        <td colspan="3" class="bold"><select name="TaxType2Id" id="TaxType2Id" style="width:200px;height:20px;">
                                <option value="0"></option>
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
                        <td class="bold right"><input type="text" class="bold right" name="Tax2Rate" id="Tax2Rate" size="3" maxlength="2" value="<?php print($invoice->Tax2Rate);?>">%</td>
                        <td class="bold right"><?php print(number_format($invoice->Tax2Amount,0)) ?></td>
                        <td colspan="3">&nbsp</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bold right">No. Seri Faktur PPN :</td>
                        <td colspan="3" class="bold"><input type="text" class="bold" name="TaxInvoiceNo" id="TaxInvoiceNo" style="width:200px" maxlength="25" value="<?php print($invoice->TaxInvoiceNo);?>"></td>
                        <td class="bold right">Grand Total</td>
                        <td class="bold right"><?php print(number_format(($invoice->BaseAmount - $invoice->Disc1Amount) + $invoice->Tax1Amount + $invoice->Tax2Amount,0)) ?></td>
                        <td colspan="3">&nbsp</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="11" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("ap.invoice", "add")) {
                                printf('<img src="%s" alt="New Invoice" title="New Invoice" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("ap.invoice", "delete") && $invoice->InvoiceStatus == 0) {
                                printf('<img src="%s" alt="Void Invoice" title="Void Invoice" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("ap.invoice", "print") && $invoice->InvoiceStatus > 0) {
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
<!-- Form Add/Edit Inv Detail -->
<div id="dlg" class="easyui-dialog" style="width:600px;height:300px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right">Department :</td>
                <td colspan="3"><select class="easyui-combobox" id="DeptId" name="DeptId" style="width: 300px">
                        <option value=""></option>
                        <?php
                        /** @var $depts Department[] */
                        $dtx = null;
                        foreach ($depts as $dept) {
                            printf('<option value="%s">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right">Activity :</td>
                <td colspan="3"><select class="easyui-combobox" id="ActivityId" name="ActivityId" style="width: 300px">
                        <option value=""></option>
                        <?php
                        /** @var $activitys Activity[] */
                        $dtx = null;
                        foreach ($activitys as $act) {
                            printf('<option value="%s">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                        }
                        ?>
                    </select>

                </td>
            </tr>
            <tr>
                <td class="right">Unit :</td>
                <td colspan="3"><select class="easyui-combobox" id="UnitId" name="UnitId" style="width: 300px">
                        <option value=""></option>
                        <?php
                        /** @var $units Units[] */
                        $dtx = null;
                        foreach ($units as $unit) {
                            printf('<option value="%s">%s - %s</option>', $unit->Id, $unit->UnitCode, $unit->UnitName);
                        }
                        ?>
                    </select>

                </td>
            </tr>
            <tr>
                <td class="right">Trx Code :</td>
                <td colspan="3"><select class="easyui-combobox" id="xItemCode" name="xItemCode" style="width: 450px" required>
                        <option value=""></option>
                        <?php
                        /** @var $trxtypes TrxType[] */
                        $dtx = null;
                        foreach ($trxtypes as $trxtype) {
                            $dtx = $trxtype->Id.'|'.$trxtype->Code.'|'.$trxtype->Description;
                            printf('<option value="%s">%s - %s</option>', $dtx, $trxtype->Code, $trxtype->Description);
                        }
                        ?>
                    </select>
                    <input type="hidden" name="aId" id="aId" value="0">
                    <input type="hidden" name="aItemId" id="aItemId" value="0">
                    <input type="hidden" name="aItemCode" id="aItemCode" value="">
                    <input type="hidden" name="aItemDescs" id="aItemDescs" value="-">
                    <input type="hidden" name="aMode" id="aMode" value="N">
                    <input type="hidden" name="aIsAuto" id="aIsAuto" value="0">
                </td>
            </tr>
            <tr>
                <td class="right">Description :</td>
                <td colspan="3"><input type="text" class="easyui-textbox" id="aItemName" name="aItemName" style="width:450px" value="" required/></td>
            </tr>
            <tr>
                <td class="right">QTY :</td>
                <td><input type="text" class="easyui-numberbox numberbox-f validatebox-text" value="0" data-options="precision:0,groupSeparator:',',decimalSeparator:'.'" id="aQty" name="aQty" style="width:130px" value="0" required/></td>
                <td class="right">UOM :</td>
                <td><input type="text" class="easyui-textbox" id="aUomCd" name="aUomCd" style="width:150px" value="" required/></td>
            </tr>
            <tr>
                <td class="right">Unit Price :</td>
                <td><input type="text" class="easyui-numberbox numberbox-f validatebox-text" value="0" data-options="precision:0,groupSeparator:',',decimalSeparator:'.'" id="aPrice" name="aPrice" style="width:130px" value="0" required/></td>
                <td class="right">Amount :</td>
                <td><input type="text" class="easyui-numberbox numberbox-f validatebox-text" value="0" data-options="precision:0,groupSeparator:',',decimalSeparator:'.'" id="aAmount" name="aAmount" style="width:150px" value="0" required/></td>
            </tr>
        </table>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Save</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
</div>

<script type="text/javascript">
    var InvId = "<?php print($invoice->Id);?>";
    var InvStatus = "<?php print($invoice->InvoiceStatus);?>";
    var BaseAmount = Number("<?php print($invoice->BaseAmount);?>");
    var InvDate, CrTerms, InvDueDate, dQty, dPrice, dAmount;
    $(document).ready(function() {

        InvDate = $("#InvoiceDate").customDatePicker({showOn: "focus"});
        InvDueDate = $("#DueDate").customDatePicker({showOn: "focus"});
        CrTerms = $("#CreditTerms").autoNumeric({ mDec: 0 });

        CrTerms.change(function(e) { Terms_Changed(this, e); });
        InvDueDate.change(function(e) { DueDate_Changed(this, e); });

        $('#xItemCode').combobox({
            onSelect: function(row){
                var dtx = row.value.split('|');
                $("#aItemId").val(dtx[0]);
                $("#aItemCode").val(dtx[1]);
                if ($("#aIsAuto").val() == 0) {
                    $("#aItemName").textbox('setValue', dtx[2]);
                    $("#aQty").textbox('setValue',0);
                    $("#aPrice").textbox('setValue',0);
                    $("#aQty").focus();
                }
            }
        });

        $('#TaxType1Id').change(function () {
            //$out = "OK|1|".$taxtype->TaxCode.'|'.$taxtype->TaxRate.'|'.$taxtype->IsDeductable;
            var urz = "<?php print($helper->site_url("ap.invoice/getTaxtypeData/")); ?>"+this.value;
            $.get(urz, function(data){
               var dta = data.split('|');
               if (dta[0] == "OK"){
                  var txr = dta[3];
                  var isd = dta[4];
                  if (isd == 1) {
                      txr = txr * -1;
                  }
                  $('#Tax1Rate').val(txr);
               }else{
                   alert(data);
               }
            });
        });

        $('#TaxType2Id').change(function () {
            //$out = "OK|1|".$taxtype->TaxCode.'|'.$taxtype->TaxRate.'|'.$taxtype->IsDeductable;
            var urz = "<?php print($helper->site_url("ap.invoice/getTaxtypeData/")); ?>"+this.value;
            $.get(urz, function(data){
                var dta = data.split('|');
                if (dta[0] == "OK"){
                    var txr = dta[3];
                    var isd = dta[4];
                    if (isd == 1) {
                        txr = txr * -1;
                    }
                    $('#Tax2Rate').val(txr);
                }else{
                    alert(data);
                }
            });
        });

        $('#Disc1Pct').change(function () {
           var d1p = this.value;
           var d1a = 0;
           if (BaseAmount > 0 && this.value > 0){
               d1a = Math.round(BaseAmount * (d1p/100),0);
               $('#Disc1Amount').val(d1a);
           }
        });

        $('#aQty').textbox({
            onChange: function(value){
                dQty = $("#aQty").textbox('getValue');
                dPrice = $("#aPrice").textbox('getValue');
                $("#aAmount").textbox('setValue',dQty * dPrice);
            }
        });

        $('#aPrice').textbox({
            onChange: function(value){
                dQty = $("#aQty").textbox('getValue');
                dPrice = $("#aPrice").textbox('getValue');
                $("#aAmount").textbox('setValue',dQty * dPrice);
            }
        });

        $("#bAdDetail").click(function(e){
            newItem(0);
        });

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("ap.invoice/add/0")); ?>";
        });

        $("#bUpdate").click(function(e){
            if (checkMaster()) {
                updateMaster();
            }else{
                alert("Data master tidak valid!");
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

    function Terms_Changed(sender, e) {
        // Berhubung autoMuneric parseInt pasti sukses
        var days = parseInt(sender.value);
        var docDate = InvDate.datepicker("getDate");
        if (docDate == null) {
            return;
        }
        docDate.setDate(docDate.getDate() + days);
        InvDueDate.datepicker("setDate", docDate);
        // Invoke Paksa event change pada textbox
        InvDueDate.change();
    }

    function DueDate_Changed(sender, e) {
        var docDate = InvDate.datepicker("getDate");
        var dueDate = InvDueDate.datepicker("getDate");
        var diff = (dueDate - docDate) / 86400000;
        if (diff < 0) {
            alert("Tanngal jatuh tempo salah ! Otomatis disamakan dengan tanggal dokumen.");
            InvDueDate.datepicker("setDate", docDate);
            // Invoke Paksa event change pada textbox
            InvDueDate.change();
        } else {
            CrTerms.val(diff);
        }
    }

    function newItem(mid){
        if (checkMaster()) {
            $('#dlg').dialog('open').dialog('setTitle', 'Add New Invoice Item');
            clearForm();
            $('#aMode').val('N');
        }else{
            alert ('Data Master tidak lengkap!');
        }
    }

    function fEditDetail(dta) {
        //$dtx = addslashes($detail->Id.'|'.$detail->ItemId.'|'.$detail->ItemCode.'|'.$detail->ItemName.'|'.$detail->UomCd.'|'.$detail->Qty.'|'.$detail->Price.'|'.$detail->DeptId.'|'.$detail->ActivityId.'|'.$detail->IsAuto.'|'.$detail->ItemDescs.'|'.$detail->UnitId);
        var IvStatus = "<?php print($invoice->InvoiceStatus);?>";
        var dtx = dta.split('|');
        var dId = dtx[0];
        //alert("["+dId+"] Proses Edit detail!");
        if (IvStatus == 0) {
            $('#dlg').dialog('open').dialog('setTitle', 'Editing Invoice Item');
            clearForm();
            $('#aMode').val('E');
            $('#aId').val(dtx[0]);
            $('#aItemId').val(dtx[1]);
            $('#aItemCode').val(dtx[2]);
            $('#aItemName').textbox('setValue',dtx[3]);
            $('#aUomCd').textbox('setValue',dtx[4]);
            $('#aQty').textbox('setValue',dtx[5]);
            $('#aPrice').textbox('setValue',dtx[6]);
            $('#DeptId').combobox('setValue',dtx[7]);
            $('#ActivityId').combobox('setValue',dtx[8]);
            $('#aIsAuto').val(dtx[9]);
            $('#aItemDescs').val(dtx[10]);
            $('#UnitId').combobox('setValue',dtx[11]);
            $('#aItemSearch').focus();
        }else{
            alert ("Proses editing tidak diijinkan!");
        }
    }

    function fDelDetail(dId) {
        var InvStatus = "<?php print($invoice->InvoiceStatus);?>";
        if (InvStatus == 0) {
            var urx = "<?php print($helper->site_url("ap.invoice/delete_detail/")); ?>" + dId;
            if (confirm('Hapus Item Invoice ini ?')) {
                $.get(urx, function(data){
                    alert(data);
                    location.reload();
                });
            }
        }else{
            alert ("Proses delete tidak diijinkan!");
        }
    }

    function checkMaster() {
        var mPri = $("#ProjectId").combobox('getValue');
        var mCri = $("#CreditorId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mRfn = $("#ReffNo").textbox('getValue');
        var mIdt = $("#InvoiceDate").val();
        var mItp = $("#InvoiceType").combobox('getValue');
        if (mPri > 0 && mCri > 0 && mItp > 0 && mIdt != '' && mRfn != ''){
            return true;
        }else{
            return false;
        }
    }

    function updateMaster(){
        InvId = $("#Id").val();
        var mInvn = $("#InvNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mCri = $("#CreditorId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mRno = $("#ReffNo").textbox('getValue');
        var mGrn = $("#GrnNo").textbox('getValue');
        var mIdt = $("#InvoiceDate").val();
        var mDdt = $("#DueDate").val();
        var mCrt = $("#CreditTerms").val();
        var mItp = $("#InvoiceType").combobox('getValue');
        var mD1p = $("#Disc1Pct").val();
        var mD1a = $("#Disc1Amount").val();
        var mTti1 = $("#TaxType1Id").val();
        var mTti2 = $("#TaxType2Id").val();
        var mTrt1 = $("#Tax1Rate").val();
        var mTrt2 = $("#Tax2Rate").val();
        var mNfp = $("#TaxInvoiceNo").val();
        var urm = "<?php print($helper->site_url("ap.invoice/proses_master/")); ?>" + InvId;
        if (checkMaster()) {
            //proses simpan dan update master
            $.post(urm, {
                ReffNo: mRno,
                GrnNo: mGrn,
                ProjectId: mPri,
                CreditorId: mCri,
                InvoiceType: mItp,
                InvoiceDescs: mNot,
                InvoiceDate: mIdt,
                InvoiceNo: mInvn,
                CreditTerms: mCrt,
                DueDate: mDdt,
                TaxType1Id: mTti1,
                TaxType2Id: mTti2,
                Tax1Rate: mTrt1,
                Tax2Rate: mTrt2,
                TaxInvoiceNo: mNfp,
                Disc1Pct: mD1p,
                Disc1Amount: mD1a
            }, function (data) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    location.reload();
                } else {
                    alert(data);
                }
            });
        }else {
            alert('Data Master tidak valid!');
        }
    }

    function saveDetail(){
        InvId = $("#Id").val();
        var tMod = $("#aMode").val();
        var mInvn = $("#InvNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mCri = $("#CreditorId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mGrn = $("#GrnNo").textbox('getValue');
        var mRno = $("#ReffNo").textbox('getValue');
        var mIdt = $("#InvoiceDate").val();
        var mDdt = $("#DueDate").val();
        var mCrt = $("#CreditTerms").val();
        var mItp = $("#InvoiceType").combobox('getValue');
        var mD1p = $("#Disc1Pct").val();
        var mD1a = $("#Disc1Amount").val();
        var mTti1 = $("#TaxType1Id").val();
        var mTti2 = $("#TaxType2Id").val();
        var mTrt1 = $("#Tax1Rate").val();
        var mTrt2 = $("#Tax2Rate").val();
        var mNfp = $("#TaxInvoiceNo").val();
        var dIti = $("#aItemId").val();
        var dItc = $("#aItemCode").val();
        var dIts = $("#aItemDescs").val();
        var dItn = $("#aItemName").textbox('getValue');
        var dQty = $("#aQty").textbox('getValue');
        var dPrc = $("#aPrice").textbox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        var dAmo = $("#aAmount").textbox('getValue');
        var dDpi = $("#DeptId").combobox('getValue');
        var dAci = $("#ActivityId").combobox('getValue');
        var dUni = $("#UnitId").combobox('getValue');
        if (dIti > 0 && dQty > 0 && dAmo > 0){
            var urm = "<?php print($helper->site_url("ap.invoice/proses_master/")); ?>" + InvId;
            //proses simpan dan update master
            $.post(urm,{
                GrnNo: mGrn,
                ReffNo: mRno,
                ProjectId: mPri,
                CreditorId: mCri,
                InvoiceType: mItp,
                InvoiceDescs: mNot,
                InvoiceDate: mIdt,
                InvoiceNo: mInvn,
                CreditTerms: mCrt,
                DueDate: mDdt,
                TaxType1Id: mTti1,
                TaxType2Id: mTti2,
                Tax1Rate: mTrt1,
                Tax2Rate: mTrt2,
                TaxInvoiceNo: mNfp,
                Disc1Pct: mD1p,
                Disc1Amount: mD1a},
                function( data ) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    InvId = rst[2];
                    if (InvId > 0) {
                        //proses simpan detail
                        var aid = $("#aId").val();
                        if (tMod == 'N') {
                            var urd = "<?php print($helper->site_url("ap.invoice/add_detail/")); ?>" + InvId;
                        } else {
                            var urd = "<?php print($helper->site_url("ap.invoice/edit_detail/")); ?>" + aid;
                        }
                        $.post(urd,
                            {   aId: aid,
                                aItemId: dIti,
                                aItemCode: dItc,
                                aItemName: dItn,
                                aQty: dQty,
                                aPrice: dPrc,
                                aUomCd: dUom,
                                aActivityId: dAci,
                                aDeptId: dDpi,
                                aUnitId: dUni,
                                aItemDescs: dIts},
                            function( data ) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    location.href = "<?php print($helper->site_url("ap.invoice/add/")); ?>" + InvId;
                                }else {
                                    alert(data);
                                }
                            }
                        );
                    }
                }else{
                    alert(data);
                }
            });
        }else{
            alert("Detail Invalid!");
        }
    }

    function clearForm() {
        $('#aMode').val('N');
        $('#aId').val(0);
        $('#aItemId').val(0);
        $('#aItemCode').val('');
        $('#aItemName').textbox('setValue','');
        $('#aUomCd').textbox('setValue','');
        $('#aQty').textbox('setValue',0);
        $('#aPrice').textbox('setValue',0);
        $('#aIsAuto').val(0);
        $('#DeptId').combobox('setValue',0);
        $('#ActivityId').combobox('setValue',0);
        $('#UnitId').combobox('setValue',0);
    }
</script>
</body>
</html>
