<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $invoice Invoice */
/** @var $debtors Debtor[] */
$counter = 0;
?>
<head>
    <title>REKASYS | A/R Invoice Entry</title>
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
<div id="p" class="easyui-panel" title="A/R Invoice Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
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
            <td><select class="easyui-combobox" id="InvoiceType" name="InvoiceType" style="width: 100px" required>
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
            <td class="right">Debtor :</td>
            <td><select class="easyui-combobox" id="DebtorId" name="DebtorId" style="width: 250px">
                    <option value="">- Pilih Debtor -</option>
                    <?php
                    foreach ($debtors as $debtor) {
                        if ($debtor->Id == $invoice->DebtorId) {
                            printf('<option value="%d" selected="selected">%s</option>', $debtor->Id, $debtor->DebtorName);
                        } else {
                            printf('<option value="%d">%s</option>', $debtor->Id, $debtor->DebtorName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">Invoice Date :</td>
            <td><input type="text" style="width: 100px" class="bold" id="InvoiceDate" name="InvoiceDate" value="<?php print($invoice->FormatInvoiceDate(JS_DATE));?>"/></td>
            <td class="right">Credit Terms :</td>
            <td><input type="text" style="width: 50px" class="bold right" id="CreditTerms" name="CreditTerms" value="<?php print($invoice->CreditTerms);?>"/>&nbsp;Day(s)</td>
            <td class="right">Due Date :</td>
            <td><input type="text" style="width: 100px" class="bold" id="DueDate" name="DueDate" value="<?php print($invoice->FormatDueDate(JS_DATE));?>"/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 30px;" value="<?php print($invoice->InvoiceDescs);?>"></td>
            <td class="right">Reff No. :</td>
            <td colspan="2"><input type="text" class="easyui-textbox"  style="width: 200px" id="ReffNo" name="ReffNo" value="<?php print($invoice->ReffNo);?>"/></td>
            <td colspan="3" class="right">
                <?php
                if ($acl->CheckUserAccess("ar.invoice", "approve") && $invoice->InvoiceStatus == 0 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btApprove" href="%s" class="button"><b><font color="#a52a2a">APPROVE</font></b></a>',$helper->site_url("ar.invoice/approve?&id[]=".$invoice->Id));
                }
                if ($acl->CheckUserAccess("ar.invoice", "approve") && $invoice->InvoiceStatus == 1 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btUnapprove" href="%s" class="button"><b><font color="#a52a2a">UNAPPROVE</font></b></a>',$helper->site_url("ar.invoice/unapprove?&id[]=".$invoice->Id));
                }
                if ($acl->CheckUserAccess("ar.invoice", "posted") && $invoice->InvoiceStatus == 1 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btPosting" href="%s" class="button"><b><font color="#dc143c">POSTING</font></b></a>',$helper->site_url("ar.invoice/posting?&id[]=".$invoice->Id));
                }
                if ($acl->CheckUserAccess("ar.invoice", "posted") && $invoice->InvoiceStatus == 2 && $invoice->BaseAmount > 0) {
                    printf('&nbsp;<a id="btUnposting" href="%s" class="button"><b><font color="#dc143c">UNPOSTING</font></b></a>',$helper->site_url("ar.invoice/unposting?&id[]=".$invoice->Id));
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
                        <th colspan="7">Invoice Detail</th>
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
                    </tr>
                    <?php
                    $dtx = null;
                    foreach($invoice->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr class='bold'>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td class='right'>%s</td>",number_format($detail->Qty,0));
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",number_format($detail->Price,0));
                        printf("<td class='right'>%s</td>",number_format($detail->Qty * $detail->Price,0));
                        print("<td class='center'>");
                        if ($acl->CheckUserAccess("ar.invoice", "delete") && $invoice->InvoiceStatus == 0) {
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
                        <?php if ($acl->CheckUserAccess("ar.invoice", "add")  && $invoice->InvoiceStatus == 0) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Add Item" title="Add Item" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="6" class="bold right">Total</td>
                        <td class="bold right"><?php print(number_format($invoice->BaseAmount,0)) ?></td>
                        <td>&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="bold right">Pph &nbsp;<input type="text" class="bold right" name="WhtPct" id="WhtPct" size="3" maxlength="3" value="<?php print($invoice->WhtPct);?>">%</td>
                        <td class="bold right"><font color="red"><?php print($invoice->WhtAmount > 0 ? '-'.number_format($invoice->WhtAmount,0) : 0) ?></font></td>
                        <td class="center">
                            <?php
                            if ($acl->CheckUserAccess("ar.invoice", "edit") && $invoice->InvoiceStatus == 0) {
                                printf('<img src="%s" alt="Update Invoice" title="Update Invoice" id="bUpdate" style="cursor: pointer;"/>&nbsp;&nbsp;',$bsubmit);
                            }else{
                                print("&nbsp;");
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="bold">No. Seri Faktur &nbsp;<input type="text" class="bold" name="TaxInvoiceNo" id="TaxInvoiceNo" size="30" maxlength="30" value="<?php print($invoice->TaxInvoiceNo);?>"></td>
                        <td class="bold right">PPN &nbsp;<input type="text" class="bold right" name="VatPct" id="VatPct" size="3" maxlength="2" value="<?php print($invoice->VatPct);?>">%</td>
                        <td class="bold right"><?php print(number_format($invoice->VatAmount,0)) ?></td>
                        <td>&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="bold right">Grand Total</td>
                        <td class="bold right"><?php print(number_format($invoice->BaseAmount + $invoice->VatAmount - $invoice->WhtAmount,0)) ?></td>
                        <td>&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="8" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("ar.invoice", "add")) {
                                printf('<img src="%s" alt="New Invoice" title="New Invoice" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("ar.invoice", "delete") && $invoice->InvoiceStatus == 0) {
                                printf('<img src="%s" alt="Void Invoice" title="Void Invoice" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("ar.invoice", "print") && $invoice->InvoiceStatus > 0) {
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
<div id="dlg" class="easyui-dialog" style="width:500px;height:230px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right">Trx Code :</td>
                <td colspan="3"><select class="easyui-combobox" id="xItemCode" name="xItemCode" style="width: 350px" required>
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
                    <input type="hidden" name="aItemId" id="aItemId" value="0">
                    <input type="hidden" name="aItemCode" id="aItemCode" value="">
                    <input type="hidden" name="aMode" id="aMode" value="N">
                </td>
            </tr>
            <tr>
                <td class="right">Description :</td>
                <td colspan="3"><input type="text" class="easyui-textbox" id="aItemName" name="aItemName" style="width:350px" value="" required/></td>
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
                $("#aItemName").textbox('setValue',dtx[2]);
                $("#aQty").textbox('setValue',0);
                $("#aPrice").textbox('setValue',0);
                $("#aQty").focus();
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
            location.href = "<?php print($helper->site_url("ar.invoice/add/0")); ?>";
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
                        location.href = "<?php print($helper->site_url("ar.invoice/void/")); ?>" + InvId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (InvId > 0) {
                if (confirm("Cetak Data Invoice ini?")) {
                    location.href = "<?php print($helper->site_url("ar.invoice/doc_print/pdf?&id[]=")); ?>" + InvId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("ar.invoice")); ?>";
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
            $('#fm').form('clear');
            $('#aMode').val('N');
        }else{
            alert ('Data Master tidak lengkap!');
        }
    }

    function fDelDetail(dId) {
        var InvStatus = "<?php print($invoice->InvoiceStatus);?>";
        if (InvStatus == 0) {
            var urx = "<?php print($helper->site_url("ar.invoice/delete_detail/")); ?>" + dId;
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
        var mDbi = $("#DebtorId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mIdt = $("#InvoiceDate").val();
        var mItp = $("#InvoiceType").combobox('getValue');
        if (mPri > 0 && mDbi > 0 && mItp > 0 && mIdt != ''){
            return true;
        }else{
            return false;
        }
    }

    function updateMaster(){
        InvId = $("#Id").val();
        var mInvn = $("#InvNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mDbi = $("#DebtorId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mRno = $("#ReffNo").textbox('getValue');
        var mIdt = $("#InvoiceDate").val();
        var mDdt = $("#DueDate").val();
        var mCrt = $("#CreditTerms").val();
        var mItp = $("#InvoiceType").combobox('getValue');
        var mVtp = $("#VatPct").val();
        var mWtp = $("#WhtPct").val();
        var mNfp = $("#TaxInvoiceNo").val();
        var urm = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>" + InvId;
        //proses simpan dan update master
        $.post(urm,{ReffNo: mRno, ProjectId: mPri, DebtorId: mDbi, InvoiceType: mItp, InvoiceDescs: mNot, InvoiceDate: mIdt, InvoiceNo: mInvn, CreditTerms: mCrt, DueDate: mDdt, VatPct: mVtp, WhtPct: mWtp, TaxInvoiceNo: mNfp}, function( data ) {
            var rst = data.split('|');
            if (rst[0] == 'OK') {
                location.reload();
            }else{
                alert(data);
            }
        });
    }

    function saveDetail(){
        InvId = $("#Id").val();
        var tMod = $("#aMode").val();
        var mInvn = $("#InvNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mDbi = $("#DebtorId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mRno = $("#ReffNo").textbox('getValue');
        var mIdt = $("#InvoiceDate").val();
        var mDdt = $("#DueDate").val();
        var mCrt = $("#CreditTerms").val();
        var mItp = $("#InvoiceType").combobox('getValue');
        var mVtp = $("#VatPct").val();
        var mWtp = $("#WhtPct").val();
        var mNfp = $("#TaxInvoiceNo").val();
        var dIti = $("#aItemId").val();
        var dItc = $("#aItemCode").val();
        var dItn = $("#aItemName").textbox('getValue');
        var dQty = $("#aQty").textbox('getValue');
        var dPrc = $("#aPrice").textbox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        var dAmo = $("#aAmount").textbox('getValue');
        if (dIti > 0 && dQty > 0 && dAmo > 0){
            var urm = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>" + InvId;
            //proses simpan dan update master
            $.post(urm,{ReffNo: mRno, ProjectId: mPri, DebtorId: mDbi, InvoiceType: mItp, InvoiceDescs: mNot, InvoiceDate: mIdt, InvoiceNo: mInvn, CreditTerms: mCrt, DueDate: mDdt, VatPct: mVtp, WhtPct: mWtp, TaxInvoiceNo: mNfp}, function( data ) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    InvId = rst[2];
                    if (InvId > 0) {
                        //proses simpan detail
                        var aid = $("#aId").val();
                        var urd = "<?php print($helper->site_url("ar.invoice/add_detail/")); ?>" + InvId;
                        $.post(urd,
                            {aId: aid, aItemId: dIti, aItemCode: dItc, aItemName: dItn, aQty: dQty, aPrice: dPrc, aUomCd: dUom},
                            function( data ) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    location.href = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + InvId;
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
</script>
</body>
</html>
