<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $rn Rrn */
$counter = 0;
?>
<head>
    <title>REKASYS | R/N Entry</title>
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
<div id="p" class="easyui-panel" title="R/N Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" required>
                    <?php
                    /** @var $projects Project[] */
                    if (count($projects) > 1){
                        print('<option value="">- Pilih Proyek -</option>');
                    }
                    foreach ($projects as $project) {
                        if ($project->Id == $rn->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($rn->Id);?>"/>
                    <input type="hidden" name="RnNo" id="RnNo" value="<?php print($rn->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">R/N No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($rn->DocumentNo != null ? $rn->DocumentNo : '[AUTO]'); ?>" readonly/></td>
            <td class="right">R/N Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="StatusCode" name="StatusCode" value="<?php print($rn->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Supplier :</td>
            <td><select class="easyui-combobox" id="SupplierId" name="SupplierId" style="width: 250px" required>
                    <option value="">- Pilih Supplier -</option>
                    <?php
                    /** @var $suppliers Creditor[] */
                    foreach ($suppliers as $supplier) {
                        if ($supplier->Id == $rn->SupplierId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">R/N Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="RnDate" name="RnDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($rn->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Invoice No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="InvoiceNo" name="InvoiceNo" value="<?php print($rn->InvoiceNo); ?>" readonly/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 40px;" value="<?php print($rn->Note);?>"></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="8">R/N Detail</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>RO Number</th>
                        <th>RO Date</th>
                        <th>Item Code</th>
                        <th>Part Number</th>
                        <th>Item Name</th>
                        <th>UOM</th>
                        <th>QTY</th>
                    </tr>
                    <?php
                    $dtx = null;
                    foreach($rn->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr>");
                        printf("<td align='center'>%s</td>",$counter);
                            printf("<td nowrap>%s</td>",$detail->RoNo);
                        printf("<td nowrap>%s</td>",$detail->RoDate);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->PartNo);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",$detail->Qty);
                        print("<td align='center'>");
                        printf('&nbsp<img src="%s" alt="Hapus Item" title="Hapus Item" style="cursor: pointer" onclick="return fDelDetail(%s);"/>',$bclose,$detail->Id);
                        print("</td>");
                        print ("</tr>");
                    }
                    ?>
                    <tr>
                        <td colspan="8" align="right">&nbsp;</td>
                        <?php if ($acl->CheckUserAccess("inventory.rn", "add")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Add Item" title="Add Item" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="9" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("inventory.rn", "add")) {
                                printf('<img src="%s" alt="New R/N" title="New R/N" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("inventory.rn", "delete")) {
                                printf('<img src="%s" alt="Delete R/N" title="Delete R/N" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("inventory.rn", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print R/N" title="Print R/N" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="R/N List" title="R/N List" style="cursor: pointer;"/>',$bkembali);
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
                <td class="right">R/O Items :</td>
                <td colspan="6"><input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:560px"/></td>
            </tr>
            <tr>
                <td class="right">Item Code :</td>
                <td colspan="3">
                    <input type="text" class="easyui-textbox" id="aItemCode" name="aItemCode" style="width:200px" value="" required/>
                </td>
                <td class="right">Part Number :</td>
                <td><input type="text" class="easyui-textbox" id="aPartNo" name="aPartNo" style="width:200px" value="" required/></td>
            </tr>
            <tr>
                <td class="right">Item Name :</td>
                <td colspan="6"><input type="text" class="easyui-textbox" id="aItemName" name="aItemName" style="width:560px" value="" readonly/></td>
            </tr>
            <tr>
                <td class="right">QTY :</td>
                <td><input type="text" class="easyui-textbox right" id="aRnQty" name="aRnQty" style="width:80px" value="0" required/></td>
                <td class="right">UOM :</td>
                <td><input type="text"  class="easyui-textbox" id="aUomCd" name="aUomCd" style="width:100px" value="" readonly/>
                    <input type="hidden" name="aId" id="aId" value="0">
                    <input type="hidden" name="aItemId" id="aItemId" value="0">
                    <input type="hidden" name="aRoDetailId" id="aRoDetailId" value="0">
                    <input type="hidden" id="aPrice" name="aPrice" value="0"/>
                    <input type="hidden" name="aMode" id="aMode" value="N"/>
                </td>
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
        var RnId = "<?php print($rn->Id);?>";
        var RnStatus = "<?php print($rn->StatusCode);?>";
        var ProjectId = "<?php print($rn->ProjectId);?>";
        var SupplierId = "<?php print($rn->SupplierId);?>";

        $('#ProjectId').combobox({
            onChange: function(pri){
                SupplierId = $("#SupplierId").combobox('getValue');
                urz = "<?php print($helper->site_url("inventory.rn/getjson_roitems/"));?>"+pri+'/'+SupplierId;
                $('#aItemSearch').combogrid({url: urz});
            }
        });

        $('#SupplierId').combobox({
            onChange: function(spi){
                ProjectId = $("#ProjectId").combobox('getValue');
                urz = "<?php print($helper->site_url("inventory.rn/getjson_roitems/"));?>"+ProjectId+'/'+spi;
                $('#aItemSearch').combogrid({url: urz});
            }
        });

        $('#aItemSearch').combogrid({
            panelWidth:800,
            url: "<?php print($helper->site_url("inventory.rn/getjson_roitems/".$rn->ProjectId.'/'.$rn->SupplierId));?>",
            idField: 'id',
            textField: 'item_code',
            mode: 'remote',
            fitColumns: true,
            columns: [[
                {field: 'item_code', title: 'Item Code', width: 130},
                {field: 'part_no', title: 'Part Number', width: 160},
                {field: 'item_name', title: 'Item Name', width: 300},
                {field: 'uom_cd', title: 'UOM', width: 50},
                {field: 'ro_qty', title: 'QTY', width: 50},
                {field: 'ro_date', title: 'RO Date', width: 80},
                {field: 'ro_no', title: 'RO Number', width: 170}
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
                var qty = row.ro_qty;
                console.log(qty);
                var prc = row.price;
                console.log(prc);
                $("#aItemId").val(iti);
                $("#aRoDetailId").val(mdi);
                $("#aItemCode").textbox("setValue",itc);
                $("#aItemName").textbox("setValue",itn);
                $("#aPartNo").textbox("setValue",ptn);
                $("#aUomCd").textbox("setValue",ucd);
                $("#aPrice").val(prc);
                $("#aRnQty").textbox("setValue",qty);
                $("#aRnQty").focus();
            }
        });

        $("#bAdDetail").click(function(e){
            newItem(0);
        });

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.rn/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (RnStatus == 1) {
                if (RnId > 0) {
                    if (confirm("Hapus Data R/N ini?")) {
                        location.href = "<?php print($helper->site_url("inventory.rn/delete/")); ?>" + RnId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (RnId > 0) {
                if (confirm("Cetak Data R/N ini?")) {
                    location.href = "<?php print($helper->site_url("inventory.rn/doc_print/pdf?&id[]=")); ?>" + RnId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.rn")); ?>";
        });

    });

    function newItem(mid){
        if (checkMaster()) {
            $('#dlg').dialog('open').dialog('setTitle', 'Add New R/N Item');
            $('#fm').form('clear');
            $('#aMode').val('N');
            $('#aItemSearch').focus();
        }else{
            alert ('Data Master tidak lengkap!');
        }
    }

    function fDelDetail(dId) {
        var RnStatus = "<?php print($rn->StatusCode);?>";
        if (RnStatus == 1) {
            var urx = "<?php print($helper->site_url("inventory.rn/delete_detail/")); ?>" + dId;
            if (confirm('Hapus Item R/N ini ?')) {
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
        var mGdt = $("#RnDate").datebox('getValue');
        if (mPri > 0 && mSpi > 0 && mGdt != ''){
            return true;
        }else{
            return false;
        }
    }

    function saveDetail(){
        RnId = $("#Id").val();
        var tMod = $("#aMode").val();
        var mRno = $("#RnNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mSpi = $("#SupplierId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mGdt = $("#RnDate").datebox('getValue');
        var dIti = $("#aItemId").val();
        var dQty = $("#aRnQty").textbox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        var dPrc = $("#aPrice").val();
        var dMdi = $("#aRoDetailId").val();
        if (dIti > 0 && dQty > 0){
            var urm = "<?php print($helper->site_url("inventory.rn/proses_master/")); ?>" + RnId;
            //proses simpan dan update master
            $.post(urm,{ProjectId: mPri, SupplierId: mSpi,Note: mNot, RnDate: mGdt, RnNo: mRno}, function( data ) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    RnId = rst[2];
                    if (RnId > 0) {
                        //proses simpan detail
                        var aid = $("#aId").val();
                        if (tMod == 'N') {
                            var urd = "<?php print($helper->site_url("inventory.rn/add_detail/")); ?>" + RnId;
                        } else {
                            var urd = "<?php print($helper->site_url("inventory.rn/edit_detail/")); ?>" + aid;
                        }
                        $.ajax({
                            type : 'POST',
                            url : urd,
                            data: {aId: aid,aItemId: dIti, aRnQty: dQty, aUomCd: dUom, aRoDetailId: dMdi, aPrice: dPrc},
                            success:function (data) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    location.href = "<?php print($helper->site_url("inventory.rn/add/")); ?>" + RnId;
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
