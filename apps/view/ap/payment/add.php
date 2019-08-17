<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $payment Payment */ /** @var $banks Bank[] */
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
?>
<head>
	<title>REKASYS - Payment Entry</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

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
    <script type="text/javascript">
        var paymentId, creditorId, paymentDate, urz;
        $(document).ready(function() {
            // init value
            paymentId = '<?php print($payment->Id == null ? 0 : $payment->Id);?>';
            creditorId = '<?php print($payment->CreditorId);?>';
            paymentDate = '<?php print($payment->PaymentDate);?>';
            urz = "<?php print($helper->site_url("ap.payment/getoutstandinginvoices_json/"));?>"+creditorId;

            $('#CreditorId').combobox({
                onChange: function(dbi){
                    urz = "<?php print($helper->site_url("ap.payment/getoutstandinginvoices_json/"));?>"+dbi;
                    $('#aInvoiceSearch').combogrid({url: urz});
                }
            });

            $('#aInvoiceSearch').combogrid({
                panelWidth:600,
                url: urz,
                idField:'id',
                textField:'id',
                mode:'get',
                fitColumns:true,
                columns:[[
                    {field:'reff_no',title:'Reff No.',width:60},
                    {field:'invoice_no',title:'No. Invoice',width:60},
                    {field:'invoice_date',title:'Tanggal',width:50},
                    {field:'due_date',title:'J T P',width:50},
                    {field:'balance_amount',title:'Outstanding',width:50,align:'right'}
                ]],
                onSelect: function(index,row){
                    var id = row.id;
                    console.log(id);
                    var ivn = row.invoice_no;
                    console.log(ivn);
                    var ivd = row.invoice_date;
                    console.log(ivd);
                    var due = row.due_date;
                    console.log(due);
                    var bal = row.balance_amount;
                    console.log(bal);
                    $('#aInvoiceId').val(id);
                    $('#aInvoiceNo').val(ivn);
                    $('#aInvoiceDate').val(ivd);
                    $('#aDueDate').val(due);
                    $('#aInvoiceOutStanding').val(bal);
                    $('#aAllocateAmount').val(bal);
                    $('#aBalanceAmount').val(0);
                }
            });

            $("#aInvoiceNo").change(function(e){
                var ivn = $("#aInvoiceNo").val();
                var dbi = $("#CreditorId").combobox('getValue');
                var url = "<?php print($helper->site_url("ap.payment/getoutstandinginvoices_plain/"));?>"+dbi+"/"+ivn;
                if (ivn != ''){
                    $.get(url, function(data, status){
                        //alert("Data: " + data + "\nStatus: " + status);
                        if (status == 'success'){
                            var dtx = data.split('|');
                            if (dtx[0] == 'OK'){
                                $('#aInvoiceId').val(dtx[1]);
                                $('#aInvoiceSearch').val(dtx[1]);
                                $('#aInvoiceDate').val(dtx[2]);
                                $('#aDueDate').val(dtx[3]);
                                $('#aInvoiceOutStanding').val(Number(dtx[4]));
                                $('#aAllocateAmount').val(Number(dtx[4]));
                                $('#aBalanceAmount').val(0);
                            }else{
                                alert('Data Invoice Piutang tidak ditemukan!');
                            }
                        }else{
                            alert('Data Invoice Piutang tidak ditemukan!');
                        }
                    });
                }
            });

            $("#aAllocateAmount").change(function(e){
                var out = Number($('#aInvoiceOutStanding').val());
                var alo = Number($("#aAllocateAmount").val());
                $('#aBalanceAmount').val(out-alo);
            });

            $("#bAdDetail").click(function(e){
                if (checkMaster()){
                    newItem();
                }else{
                    alert("Data Master tidak valid!")
                }
            });

            $("#bUpdate").click(function(){
                if (confirm('Save/Update data master?')){
                    saveMaster();
                }
            });

            $("#bTambah").click(function(){
                if (confirm('Buat Payment baru?')){
                    location.href="<?php print($helper->site_url("ap.payment/add/0")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan membatalkan data payment ini?')){
                    location.href="<?php print($helper->site_url("ap.payment/void/").$payment->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak payment ini?')){
                    //location.href="<?php //print($helper->site_url("ap.payment/print_pdf/").$payment->Id); ?>";
                    alert('Proses cetak belum siap..');
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ap.payment")); ?>";
            });

        });

        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var ivn = dtx[1];
            var bal = dtx[2];
            var urx = '<?php print($helper->site_url("ap.payment/delete_detail/"));?>'+id;
            if (confirm('Hapus Detail Pembayaran \nInvoice No: '+ivn+ '\nNilai: '+bal+' ?')) {
                $.get(urx, function(data){
                    alert(data);
                    location.reload();
                });
            }
        }

        function newItem(){
            $('#aInvoiceId').val('');
            $('#aInvoiceNo').val('');
            $('#aInvoiceDate').val('');
            $('#aDueDate').val('');
            $('#aInvoiceOutStanding').val(0);
            $('#aAllocateAmount').val(0);
            $('#aBalanceAmount').val(0);
            $('#dlg').dialog('open').dialog('setTitle','Add New Detail Payment');
            //$('#fm').form('clear');
            url = "<?php print($helper->site_url("ap.payment/add_detail/".$payment->Id));?>";
            $('#aInvoiceNo').focus();
        }

        function saveMaster(){
            var dataform = $("#frmMaster").serialize();
            var urm = "<?php print($helper->site_url("ap.payment/proses_master/")); ?>" + paymentId;
            //proses simpan dan update master
            if (checkMaster()) {
                $.post(urm, dataform, function (dta) {
                    var rst = dta.split('|');
                    var rci = rst[2];
                    if (rst[0] == 'OK') {
                        urm = "<?php print($helper->site_url("ap.payment/add/")); ?>" + rci;
                        location.href = urm;
                    } else {
                        alert(dta);
                    }
                });
            }else{
                alert('Data Master tidak valid!');
            }
        }

        function saveDetail(){
            var aivi = Number($('#aInvoiceId').val());
            var aalo = Number($('#aAllocateAmount').val());
            if (aivi > 0 && aalo > 0){
                if (paymentId == 0){
                    var dataform = $("#frmMaster").serialize();
                    var urm = "<?php print($helper->site_url("ap.payment/proses_master/")); ?>" + paymentId;
                    $.post(urm, dataform, function (dta) {
                        var rst = dta.split('|');
                        var rci = rst[2];
                        if (rst[0] == 'OK') {
                            url = "<?php print($helper->site_url("ap.payment/add_detail/"));?>" + rci;
                            $('#frmDetail').form('submit',{
                                url: url,
                                onSubmit: function(){
                                    return $(this).form('validate');
                                },
                                success: function(result){
                                    var result = eval('('+result+')');
                                    if (result.errorMsg){
                                        $.messager.show({
                                            title: 'Error',
                                            msg: result.errorMsg
                                        });
                                    } else {
                                        location.href = "<?php print($helper->site_url("ap.payment/add/")); ?>"+rci;
                                        $('#dlg').dialog('close');		// close the dialog
                                    }
                                }
                            });
                        } else {
                            alert(dta);
                        }
                    });
                }else{
                    $('#frmDetail').form('submit',{
                        url: url,
                        onSubmit: function(){
                            return $(this).form('validate');
                        },
                        success: function(result){
                            var result = eval('('+result+')');
                            if (result.errorMsg){
                                $.messager.show({
                                    title: 'Error',
                                    msg: result.errorMsg
                                });
                            } else {
                                location.reload();
                                $('#dlg').dialog('close');		// close the dialog
                            }
                        }
                    });
                }

            }else{
                alert('Maaf, Data input tidak valid!');
            }
        }

        function checkMaster() {
            var mRcd = $("#PaymentDate").datebox('getValue');
            var mDbi = $("#CreditorId").combobox('getValue');
            if (mDbi > 0 && mRcd != ''){
                return true;
            }else{
                return false;
            }
        }

        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
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
<div id="p" class="easyui-panel" title="Payment Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Payment Date</td>
                <td><input type="text" class="easyui-datebox"  id="PaymentDate" name="PaymentDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($payment->FormatPaymentDate(SQL_DATEONLY));?>"/></td>
                <td>No. Payment</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="PaymentNo" name="PaymentNo" value="<?php print($payment->PaymentNo != null ? $payment->PaymentNo : '[AUTO]'); ?>" readonly/></td>
                <td>Status</td>
                <td><input class="easyui-textbox" id="PaymentStatus" name="PaymentStatus" style="width: 150px" value="<?php print($payment->GetStatus());?>" readonly></td>
            </tr>
            <tr>
                <td>Creditor Name</td>
                <td><select class="easyui-combobox" id="CreditorId" name="CreditorId" style="width: 250px">
                        <option value="">- Pilih Creditor -</option>
                        <?php
                        foreach ($creditors as $creditor) {
                            if ($creditor->Id == $payment->CreditorId) {
                                printf('<option value="%d" selected="selected">%s</option>', $creditor->Id, $creditor->CreditorName);
                            } else {
                                printf('<option value="%d">%s</option>', $creditor->Id, $creditor->CreditorName);
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" name="Id" id="Id" value="<?php print($payment->Id == null ? 0 : $payment->Id);?>"/>
                </td>
                <td>Payment Type</td>
                <td><select class="easyui-combobox" id="WarkatTypeId" name="WarkatTypeId" style="width: 150px">
                        <?php
                        foreach ($warkattypes as $wti) {
                            if ($wti->Id == $payment->WarkatTypeId) {
                                printf('<option value="%d" selected="selected"> %s - %s </option>',$wti->Id, $wti->Id, $wti->Type);
                            } else {
                                printf('<option value="%d"> %s - %s </option>',$wti->Id, $wti->Id, $wti->Type);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Cash / Bank</td>
                <td><select class="easyui-combobox" id="WarkatBankId" name="WarkatBankId" style="width: 150px">
                        <?php
                        foreach ($banks as $bank) {
                            if ($bank->Id == $payment->WarkatBankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->Name);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->Name);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Cheque No.</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="WarkatNo" name="WarkatNo" value="<?php print($payment->WarkatNo); ?>"/></td>
                <td>Cheque Date</td>
                <td><input type="text" class="easyui-datebox"  id="WarkatDate" name="WarkatDate" data-options="formatter:myformatter,parser:myparser" value="<?php print($payment->FormatWarkatDate(SQL_DATEONLY));?>"/></td>
                <td>Amount</td>
                <td><input type="text" class="easyui-numberbox numberbox-f validatebox-text" data-options="precision:0,groupSeparator:',',decimalSeparator:'.'"  style="width: 150px" id="PaymentAmount" name="PaymentAmount" value="<?php print($payment->PaymentAmount); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Description</td>
                <td colspan="4"><b><input type="text" class="easyui-textbox" id="PaymentDescs" name="PaymentDescs" style="width: 600px" value="<?php print($payment->PaymentDescs != null ? $payment->PaymentDescs : '-'); ?>" required/></b></td>
                <td colspan="3">
                    <?php
                    if ($acl->CheckUserAccess("ap.payment", "edit")) {
                        printf('<img src="%s" alt="Update Master" title="Update Master" id="bUpdate" style="cursor: pointer;"/> &nbsp',$bsubmit);
                    }else{
                        print("&nbsp;");
                    }
                    if ($acl->CheckUserAccess("ap.payment", "approve") && $payment->PaymentStatus == 0 && $payment->PaymentAmount > 0) {
                        printf('&nbsp;<a id="btApprove" href="%s" class="button"><b><font color="#a52a2a">APPROVE</font></b></a>',$helper->site_url("ap.payment/approve?&id[]=".$payment->Id));
                    }
                    if ($acl->CheckUserAccess("ap.payment", "approve") && $payment->PaymentStatus == 1 && $payment->PaymentAmount > 0) {
                        printf('&nbsp;<a id="btUnapprove" href="%s" class="button"><b><font color="#a52a2a">UNAPPROVE</font></b></a>',$helper->site_url("ap.payment/unapprove?&id[]=".$payment->Id));
                    }
                    if ($acl->CheckUserAccess("ap.payment", "posted") && $payment->PaymentStatus == 1 && $payment->PaymentAmount > 0) {
                        printf('&nbsp;<a id="btPosting" href="%s" class="button"><b><font color="#dc143c">POSTING</font></b></a>',$helper->site_url("ap.payment/posting?&id[]=".$payment->Id));
                    }
                    if ($acl->CheckUserAccess("ap.payment", "posted") && $payment->PaymentStatus == 2 && $payment->PaymentAmount > 0) {
                        printf('&nbsp;<a id="btUnposting" href="%s" class="button"><b><font color="#dc143c">UNPOSTING</font></b></a>',$helper->site_url("ap.payment/unposting?&id[]=".$payment->Id));
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="7">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th colspan="8">A/P Payment Detail</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>Reff No.</th>
                            <th>Invoice No.</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Outstanding</th>
                            <th>Payment</th>
                            <th>Balance</th>
                        </tr>
                        <?php
                        $counter = 0;
                        $tout = 0;
                        $tall = 0;
                        $tbal = 0;
                        $dta = null;
                        $url = null;
                        foreach($payment->Details as $idx => $detail) {
                            $url = $helper->site_url("ap.invoice/view/".$detail->InvoiceId);
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf("<td>%s</td>",$detail->ReffNo);
                            printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,$detail->InvoiceNo);
                            printf('<td>%s</td>', $detail->InvoiceDate);
                            printf('<td>%s</td>', $detail->DueDate);
                            printf('<td class="right">%s</td>', number_format($detail->InvoiceOutstanding,0));
                            printf('<td class="right">%s</td>', number_format($detail->AllocateAmount,0));
                            printf('<td class="right">%s</td>', number_format(($detail->InvoiceOutstanding - $detail->AllocateAmount),0));
                            print("<td class='center'>");
                            $dta = addslashes($detail->Id.'|'.$detail->InvoiceNo.'|'.$detail->InvoiceOutstanding);
                            printf('&nbsp<img src="%s" alt="Hapus Detail" title="Hapus Detail" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                            print("</td>");
                            print("</tr>");
                            $tall += $detail->AllocateAmount;
                            $tout += $detail->InvoiceOutstanding;
                            $tbal += ($detail->InvoiceOutstanding - $detail->AllocateAmount);
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
                            <td class='center'><?php printf('<img src="%s" alt="Tambah Detail" title="Tambah Detail" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="bold right">Sub Total :</td>
                            <td class="bold right"><?php print(number_format($tout,0,',','.')) ?></td>
                            <td class="bold right"><?php print(number_format($tall,0,',','.')) ?></td>
                            <td class="bold right"><?php print(number_format($tbal,0,',','.')) ?></td>
                            <td class="center">

                            </td>

                        </tr>
                        <tr>
                            <td colspan="9" class="right">
                                <?php
                                if ($acl->CheckUserAccess("ap.payment", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("ap.payment", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                if ($acl->CheckUserAccess("ap.payment", "print")) {
                                    printf('<img src="%s" alt="Cetak Payment" title="Cetak Payment" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                                }
                                printf('<img src="%s" id="bKembali" alt="Daftar Penerimaan" title="Kembali ke daftar Pembayaran" style="cursor: pointer;"/>',$bkembali);
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<!-- Form Add Payment Detail -->
<div id="dlg" class="easyui-dialog" style="width:850px;height:150px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="frmDetail" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="font-size: 12px;font-family: tahoma">
            <tr>
                <th>No.Invoice</th>
                <th>Tgl.Invoice</th>
                <th>J T P</th>
                <th>Outstanding</th>
                <th>Dibayar</th>
                <th>Sisa</th>
            </tr>
            <tr>
                <td>
                    <input type="text" id="aInvoiceNo" name="aInvoiceNo" size="15" value="" required/>
                    <input id="aInvoiceSearch" name="aInvoiceSearch" style="width: 20px"/>
                    <input type="hidden" id="aInvoiceId" name="aInvoiceId" value="0"/>
                </td>
                <td>
                    <input type="text" id="aInvoiceDate" name="aInvoiceDate" size="10" value="" disabled/>
                </td>
                <td>
                    <input type="text" id="aDueDate" name="aDueDate" size="10" value="" disabled/>
                </td>
                <td>
                    <input class="right" type="text" id="aInvoiceOutStanding" name="aInvoiceOutStanding" size="15" value="0" readonly/>
                </td>
                <td>
                    <input class="right" type="text" id="aAllocateAmount" name="aAllocateAmount" size="15" value="0"/>
                </td>
                <td>
                    <input class="right" type="text" id="aBalanceAmount" name="aBalanceAmount" size="15" value="0" readonly/>
                </td>
            </tr>
        </table>
    </form>
    <br>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Proses</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Batal</a>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2019 PT. Reka Sistem Teknologi
</div>
</body>
</html>
