<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $ro Ro */
$counter = 0;
?>
<head>
    <title>REKASYS | R/O Entry</title>
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
<div id="p" class="easyui-panel" title="R/O Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" required>
                    <option value="">- Pilih Proyek -</option>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project) {
                        if ($project->Id == $ro->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($ro->Id);?>"/>
                    <input type="hidden" name="PoNo" id="PoNo" value="<?php print($ro->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">R/O No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($ro->DocumentNo != null ? $ro->DocumentNo : '[AUTO]'); ?>" readonly/></td>
            <td class="right">R/O Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="StatusCode" name="StatusCode" value="<?php print($ro->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Supplier :</td>
            <td><select class="easyui-combobox" id="SupplierId" name="SupplierId" style="width: 250px" required>
                    <option value=""></option>
                    <?php
                    /** @var $suppliers Creditor[] */
                    foreach ($suppliers as $supplier) {
                        if ($supplier->Id == $ro->SupplierId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">R/O Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="RoDate" name="RoDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($ro->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Expected Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="ExpectedDate" name="ExpectedDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($ro->FormatExpectedDate(SQL_DATEONLY));?>"/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 40px;" value="<?php print($ro->Note);?>"></td>
            <td class="right">Credit Terms :</td>
            <td><input type="text" style="width: 50px" class="bold right" id="PaymentTerms" name="PaymentTerms" value="<?php print($ro->PaymentTerms);?>"/>&nbsp;Day(s)</td>
            <td class="right">P P N :</td>
            <td>
                <input type="checkbox" id="IsVat" name="IsVat" value="1" <?php print($ro->IsVat ? 'checked="checked"' : '') ?> />
                <label for="IsVat">Kena PPN 10%</label>
                <input type="checkbox" id="IsIncVat" name="IsIncVat" value="1" <?php print($ro->IsIncludeVat ? 'checked="checked"' : '') ?> />
                <label for="IsIncVat">Include PPN</label>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="10">R/O Detail</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>RR Number</th>
                        <th>PR Date</th>
                        <th>Item Code</th>
                        <th>Part Number</th>
                        <th>Item Name</th>
                        <th>UOM</th>
                        <th>QTY</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                    <?php
                    $dtx = null;
                    $stotal = 0;
                    foreach($ro->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->RrNo);
                        printf("<td nowrap>%s</td>",$detail->RrDate);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->PartNo);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",$detail->Qty);
                        printf("<td class='right'>%s</td>",number_format($detail->Price,0));
                        printf("<td class='right'>%s</td>",number_format($detail->Qty * $detail->Price,0));
                        print("<td align='center'>");
                        printf('&nbsp<img src="%s" alt="Hapus Item" title="Hapus Item" style="cursor: pointer" onclick="return fDelDetail(%s);"/>',$bclose,$detail->Id);
                        print("</td>");
                        print ("</tr>");
                        $stotal+= $detail->Qty * $detail->Price;
                    }
                    ?>
                    <tr>
                        <td colspan="9" align="right"><?php print($ro->IsVat == 1 ? 'Sub Total' : 'Total');?></td>
                        <td align="right"><?php print(number_format($stotal,0));?></td>
                        <?php if ($acl->CheckUserAccess("purchase.ro", "add")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Add Item" title="Add Item" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <?php
                    if ($ro->IsVat == 1){
                        if ($ro->IsIncludeVat == 1) {
                            print("<tr>");
                            print('<td colspan="9" align="right">DPP</td>');
                            printf('<td align="right">%s</td>',number_format(round($stotal/1.1,0),0));
                            print('<td>&nbsp</td>');
                            print("</tr>");
                            print("<tr>");
                            print('<td colspan="9" align="right">PPN 10%</td>');
                            printf('<td align="right">%s</td>',number_format(round(($stotal/1.1)/10,0),0));
                            print('<td>&nbsp</td>');
                            print("</tr>");
                        }else{
                            print("<tr>");
                            print('<td colspan="9" align="right">PPN 10%</td>');
                            printf('<td align="right">%s</td>',number_format(round($stotal/10,0),0));
                            print('<td>&nbsp</td>');
                            print("</tr>");
                            print("<tr>");
                            print('<td colspan="9" align="right">Total</td>');
                            printf('<td align="right">%s</td>',number_format(round($stotal * 1.1,0),0));
                            print('<td>&nbsp</td>');
                            print("</tr>");
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="11" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("purchase.ro", "add")) {
                                printf('<img src="%s" alt="New R/O" title="New R/O" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("purchase.ro", "delete")) {
                                printf('<img src="%s" alt="Delete R/O" title="Delete R/O" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("purchase.ro", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print R/O" title="Print R/O" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="R/O List" title="R/O List" style="cursor: pointer;"/>',$bkembali);
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
<!-- Form Add/Edit Mr Detail -->
<div id="dlg" class="easyui-dialog" style="width:700px;height:250px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right">PR Items :</td>
                <td colspan="6"><input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:560px"/></td>
            </tr>
            <tr>
                <td class="right">Item Code :</td>
                <td colspan="3"><input type="text" class="easyui-textbox" id="aItemCode" name="aItemCode" style="width:200px" value="" required/></td>
                <td class="right">Part Number :</td>
                <td><input type="text" class="easyui-textbox" id="aPartNo" name="aPartNo" style="width:200px" value="" required/></td>
            </tr>
            <tr>
                <td class="right">Item Name :</td>
                <td colspan="6"><input type="text" class="easyui-textbox" id="aItemName" name="aItemName" style="width:560px" value="" readonly/></td>
            </tr>
            <tr>
                <td class="right">QTY :</td>
                <td><input type="text" class="easyui-textbox right" id="aRoQty" name="aRoQty" style="width:80px" value="0" required/></td>
                <td class="right">UOM :</td>
                <td><input type="text"  class="easyui-textbox" id="aUomCd" name="aUomCd" style="width:100px" value="" readonly/>
                    <input type="hidden" name="aId" id="aId" value="0">
                    <input type="hidden" name="aItemId" id="aItemId" value="0">
                    <input type="hidden" name="aRrDetailId" id="aRrDetailId" value="0">
                    <input type="hidden" name="aMode" id="aMode" value="N"/>
                </td>
                <td class="right">Price :</td>
                <td><input type="text" class="easyui-textbox right" id="aPrice" name="aPrice" style="width:100px" value="0" required/></td>
            </tr>
        </table>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Save</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
</div>

<script type="text/javascript">
    $( function() {
        var PoId = "<?php print($ro->Id);?>";
        var PoStatus = "<?php print($ro->StatusCode);?>";
        var ProjectId = "<?php print($ro->ProjectId);?>";
        var SupplierId = "<?php print($ro->SupplierId);?>";

        $('#ProjectId').combobox({
            onChange: function(pri){
                SupplierId = $("#SupplierId").combobox('getValue');
                urz = "<?php print($helper->site_url("purchase.ro/getjson_rritems/"));?>"+pri+'/'+SupplierId;
                $('#aItemSearch').combogrid({url: urz});
            }
        });

        $('#SupplierId').combobox({
            onChange: function(spi){
                ProjectId = $("#ProjectId").combobox('getValue');
                urz = "<?php print($helper->site_url("purchase.ro/getjson_rritems/"));?>"+ProjectId+'/'+spi;
                $('#aItemSearch').combogrid({url: urz});
            }
        });

        $('#aItemSearch').combogrid({
            panelWidth:800,
            url: "<?php print($helper->site_url("purchase.ro/getjson_rritems/".$ro->ProjectId.'/'.$ro->SupplierId));?>",
            idField: 'id',
            textField: 'item_code',
            mode: 'remote',
            fitColumns: true,
            columns: [[
                {field: 'item_code', title: 'Item Code', width: 130},
                {field: 'part_no', title: 'Part Number', width: 160},
                {field: 'item_name', title: 'Item Name', width: 300},
                {field: 'uom_cd', title: 'UOM', width: 50},
                {field: 'pr_qty', title: 'QTY', width: 50},
                {field: 'price', title: 'Price', width: 80},
                {field: 'pr_no', title: 'PR Number', width: 170}
            ]],
            onSelect: function (index, row) {
                var mdi = row.id;
                console.log(mdi);
                var iti = row.item_id;
                console.log(iti);
                var itc = row.item_code;
                console.log(itc);
                var ptn = row.part_no;
                console.log(ptn);
                var itn = row.item_name;
                console.log(itn);
                var ucd = row.uom_cd;
                console.log(ucd);
                var qty = row.pr_qty;
                console.log(qty);
                var prc = row.price;
                console.log(prc);
                $("#aItemId").val(iti);
                $("#aRrDetailId").val(mdi);
                $("#aItemCode").textbox("setValue",itc);
                $("#aItemName").textbox("setValue",itn);
                $("#aPartNo").textbox("setValue",ptn);
                $("#aUomCd").textbox("setValue",ucd);
                $("#aPrice").textbox("setValue",prc);
                $("#aRoQty").textbox("setValue",qty);
                $("#aRoQty").focus();
            }
        });

        $("#bAdDetail").click(function(e){
            newItem(0);
        });

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("purchase.ro/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (PoStatus == 1) {
                if (PoId > 0) {
                    if (confirm("Hapus Data R/O ini?")) {
                        location.href = "<?php print($helper->site_url("purchase.ro/delete/")); ?>" + PoId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (PoId > 0) {
                if (confirm("Cetak Data R/O ini?")) {
                    location.href = "<?php print($helper->site_url("purchase.ro/doc_print/pdf?&id[]=")); ?>" + PoId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("purchase.ro")); ?>";
        });

    });

    function newItem(mid){
        if (checkMaster()) {
            $('#dlg').dialog('open').dialog('setTitle', 'Add New R/O Item');
            $('#fm').form('clear');
            $('#aMode').val('N');
            $('#aItemSearch').focus();
        }else{
            alert ('Data Master tidak lengkap!');
        }
    }

    function fDelDetail(dId) {
        var PoStatus = "<?php print($ro->StatusCode);?>";
        if (PoStatus == 1) {
            var urx = "<?php print($helper->site_url("purchase.ro/delete_detail/")); ?>" + dId;
            if (confirm('Hapus Item R/O ini ?')) {
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
        var mSpi = $("#SupplierId").combobox('getValue');
        var mPdt = $("#RoDate").datebox('getValue');
        if (mPri > 0 && mSpi > 0 && mPdt != ''){
            return true;
        }else{
            return false;
        }
    }

    function saveDetail(){
        PoId = $("#Id").val();
        var tMod = $("#aMode").val();
        var mPon = $("#PoNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mSpi = $("#SupplierId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mPdt = $("#RoDate").datebox('getValue');
        var mEdt = $("#ExpectedDate").datebox('getValue');
        var mPtr = $("#PaymentTerms").val();
        var mIsv = $("#IsVat").prop("checked") ? 1 : 0;
        var mIsi = $("#IsIncVat").prop("checked") ? 1 : 0;
        var dIti = $("#aItemId").val();
        var dQty = $("#aRoQty").textbox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        var dPrc = $("#aPrice").textbox('getValue');
        var dMdi = $("#aRrDetailId").val();
        if (dIti > 0 && dQty > 0){
            var urm = "<?php print($helper->site_url("purchase.ro/proses_master/")); ?>" + PoId;
            //proses simpan dan update master
            $.post(urm,{ProjectId: mPri, SupplierId: mSpi,Note: mNot, RoDate: mPdt, ExpectedDate: mEdt, PoNo: mPon, PaymentTerms: mPtr, IsVat: mIsv, IsIncVat: mIsi}, function( data ) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    PoId = rst[2];
                    if (PoId > 0) {
                        //proses simpan detail
                        var aid = $("#aId").val();
                        if (tMod == 'N') {
                            var urd = "<?php print($helper->site_url("purchase.ro/add_detail/")); ?>" + PoId;
                        } else {
                            var urd = "<?php print($helper->site_url("purchase.ro/edit_detail/")); ?>" + aid;
                        }
                        $.ajax({
                            type : 'POST',
                            url : urd,
                            data: {aId: aid,aItemId: dIti, aRoQty: dQty, aUomCd: dUom, aRrDetailId: dMdi, aPrice: dPrc},
                            success:function (data) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    location.href = "<?php print($helper->site_url("purchase.ro/add/")); ?>" + PoId;
                                }else {
                                    alert(data);
                                }
                            }
                        });
                    }
                }else{
                    alert(data);
                }
            });
        }else{
            alert("Detail Invalid!");
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
</body>
</html>
