<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $rr Rr */
$counter = 0;
?>
<head>
    <title>REKASYS | R/R Entry</title>
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
<?php if (isset($epror)) { ?>
    <div class="ui-state-epror subTitle center"><?php print($epror); ?></div><?php } ?>
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
<div id="p" class="easyui-panel" title="R/R Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
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
                        if ($project->Id == $rr->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($rr->Id);?>"/>
                    <input type="hidden" name="RrNo" id="RrNo" value="<?php print($rr->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">Dept :</td>
            <td><select class="easyui-combobox" id="DeptId" name="DeptId" style="width: 250px" required>
                    <option value="">- Pilih Departemen -</option>
                    <?php
                    /** @var $departments Department[] */
                    foreach ($departments as $dept) {
                        if ($dept->Id == $rr->DeptId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">R/R No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($rr->DocumentNo != null ? $rr->DocumentNo : '[AUTO]'); ?>" readonly/></td>
        </tr>
        <tr>
            <td rowspan="2" class="right">Notes :</td>
            <td rowspan="2"><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 40px;" value="<?php print($rr->Note);?>"></td>
            <td class="right">R/R Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="RrDate" name="RrDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($rr->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="StatusCode" name="StatusCode" value="<?php print($rr->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Request Level :</td>
            <td>
                <select class="easyui-combobox" id="ReqLevel" name="ReqLevel" style="width: 130px" required>
                    <option value="1" <?php print($rr->ReqLevel == 1 ? 'selected="selected"' : '');?>> 1 - Normal </option>
                    <option value="2" <?php print($rr->ReqLevel == 2 ? 'selected="selected"' : '');?>> 2 - Medium </option>
                    <option value="3" <?php print($rr->ReqLevel == 3 ? 'selected="selected"' : '');?>> 3 - Urgent </option>
                </select>
            </td>
            <td class="center">
                <?php
                if ($acl->CheckUserAccess("inventory.pr", "edit") && $rr->StatusCode == 1 && $rr->DocumentNo != null) {
                    printf('<img src="%s" alt="Update PR" title="Update PR" id="bUpdate" style="cursor: pointer;"/>&nbsp;&nbsp;',$bsubmit);
                }else{
                    print("&nbsp;");
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
                        <th colspan="8">Repair Request Detail</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>MR Number</th>
                        <th>MR Date</th>
                        <th>Item Code</th>
                        <th>Part Number</th>
                        <th>Item Name</th>
                        <th>UOM</th>
                        <th>QTY</th>
                    </tr>
                    <?php
                    $dtx = null;
                    foreach($rr->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->MrNo);
                        printf("<td nowrap>%s</td>",$detail->MrDate);
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
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <?php if ($acl->CheckUserAccess("inventory.rr", "add")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Add Item" title="Add Item" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="9" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("inventory.rr", "add")) {
                                printf('<img src="%s" alt="New R/R" title="New R/R" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("inventory.rr", "delete")) {
                                printf('<img src="%s" alt="Delete R/R" title="Delete R/R" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("inventory.rr", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print R/R" title="Print R/R" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="R/R List" title="R/R List" style="cursor: pointer;"/>',$bkembali);
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
                <td class="right">MR Items :</td>
                <td colspan="6"><input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:560px"/></td>
            </tr>
            <tr>
                <td class="right">Item Code :</td>
                <td colspan="3">
                    <input type="text" class="easyui-textbox" id="aItemCode" name="aItemCode" style="width:200px" value="" required/>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="searchItem();" style="width: 30px;height: 20px;"></a>
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
                <td><input type="text" class="easyui-textbox right" id="aPrQty" name="aPrQty" style="width:80px" value="0" required/></td>
                <td class="right">UOM :</td>
                <td><input type="text"  class="easyui-textbox" id="aUomCd" name="aUomCd" style="width:100px" value="" readonly/>
                    <input type="hidden" name="aId" id="aId" value="0">
                    <input type="hidden" name="aItemId" id="aItemId" value="0">
                    <input type="hidden" name="aMrDetailId" id="aMrDetailId" value="0">
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
<!-- Modal search item -->
<div id="dlg-search" class="easyui-dialog" style="width:650px;height:80px;padding:5px 5px" closed="true">
    <input class="easyui-combogrid" id="xItemSearch" name="xItemSearch" style="width:590px"/>
</div>

<script type="text/javascript">
    $( function() {
        var RrId = "<?php print($rr->Id);?>";
        var RrStatus = "<?php print($rr->StatusCode);?>";
        var ProjectId = "<?php print($rr->ProjectId);?>";

        $('#ProjectId').combobox({
            onChange: function(pri){
                urz = "<?php print($helper->site_url("inventory.rr/getjson_mritems/"));?>"+pri;
                $('#aItemSearch').combogrid({url: urz});
            }
        });

        $('#aItemSearch').combogrid({
            panelWidth:800,
            url: "<?php print($helper->site_url("inventory.rr/getjson_mritems/".$rr->ProjectId));?>",
            idField: 'id',
            textField: 'item_code',
            mode: 'remote',
            fitColumns: true,
            columns: [[
                {field: 'item_code', title: 'Item Code', width: 130},
                {field: 'part_no', title: 'Part Number', width: 160},
                {field: 'item_name', title: 'Item Name', width: 300},
                {field: 'uom_cd', title: 'UOM', width: 50},
                {field: 'qty', title: 'QTY', width: 50},
                {field: 'mr_no', title: 'MR Number', width: 160},
                {field: 'mr_date', title: 'MR Date', width: 100}
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
                var qty = row.qty;
                console.log(qty);
                var uni = row.unit_id;
                console.log(uni);
                $("#aItemId").val(iti);
                $("#aMrDetailId").val(mdi);
                $("#aItemCode").textbox("setValue",itc);
                $("#aItemName").textbox("setValue",itn);
                $("#aPartNo").textbox("setValue",ptn);
                $("#aUomCd").textbox("setValue",ucd);
                $("#aUnitId").combobox("setValue",uni);
                $("#aPrQty").textbox("setValue",qty);
                $("#aPrQty").focus();
            }
        });

        $('#xItemSearch').combogrid({
            panelWidth:800,
            url: "<?php print($helper->site_url("inventory.item/getjson_items"));?>",
            idField: 'id',
            textField: 'item_code',
            mode: 'remote',
            fitColumns: true,
            columns: [[
                {field: 'item_code', title: 'Item Code', width: 100},
                {field: 'part_no', title: 'Part Number', width: 150},
                {field: 'item_name', title: 'Item Name', width: 300},
                {field: 'uom_cd', title: 'UOM', width: 50},
                {field: 'brand_name', title: 'Brand', width: 100},
                {field: 'type_desc', title: 'Type', width: 100}
            ]],
            onSelect: function (index, row) {
                var iti = row.id;
                console.log(iti);
                var itc = row.item_code;
                console.log(itc);
                var ptn = row.part_no;
                console.log(ptn);
                var itn = row.item_name;
                console.log(itn);
                var ucd = row.uom_cd;
                console.log(ucd);
                $("#aItemId").val(iti);
                $("#aMrDetailId").val(0);
                $("#aItemCode").textbox("setValue",itc);
                $("#aItemName").textbox("setValue",itn);
                $("#aPartNo").textbox("setValue",ptn);
                $("#aUomCd").textbox("setValue",ucd);
                $("#aPrQty").textbox("setValue",1);
                $("#aPrQty").focus();
                $('#dlg-search').dialog('close');
            }
        });

        $("#bUpdate").click(function(e){
            if (checkMaster()){
                if (confirm('Update PR Master?')) {
                    var mPrn = $("#RrNo").val();
                    var mPri = $("#ProjectId").combobox('getValue');
                    var mDpi = $("#DeptId").combobox('getValue');
                    var mNot = $("#Note").textbox('getValue');
                    var mDte = $("#RrDate").datebox('getValue');
                    var mRql = $("#ReqLevel").combobox('getValue');
                    var urm = "<?php print($helper->site_url("inventory.rr/proses_master/")); ?>" + RrId;
                    //proses simpan dan update master
                    $.post(urm, {ProjectId: mPri, DeptId: mDpi, Note: mNot, RrDate: mDte, RrNo: mPrn, ReqLevel: mRql}, function (data) {
                        var rst = data.split('|');
                        if (rst[0] == 'OK') {
                            location.reload();
                        }else{
                            alert(data + ' - Update Master RR Gagal!');
                        }
                    });
                }
            }
        });

        $("#bAdDetail").click(function(e){
            newItem(0);
        });

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.rr/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (RrStatus == 1) {
                if (RrId > 0) {
                    if (confirm("Hapus Data R/R ini?")) {
                        location.href = "<?php print($helper->site_url("inventory.rr/delete/")); ?>" + RrId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (RrId > 0) {
                if (confirm("Cetak Data R/R ini?")) {
                    location.href = "<?php print($helper->site_url("inventory.rr/doc_print/pdf?&id[]=")); ?>" + RrId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.rr")); ?>";
        });

    });

    function newItem(mid){
        if (checkMaster()) {
            $('#dlg').dialog('open').dialog('setTitle', 'Add New R/R Item');
            $('#fm').form('clear');
            $('#aMode').val('N');
            $('#aItemSearch').focus();
        }else{
            alert ('Data Master tidak lengkap!');
        }
    }

    function fDelDetail(dId) {
        var RrStatus = "<?php print($rr->StatusCode);?>";
        if (RrStatus == 1) {
            var urx = "<?php print($helper->site_url("inventory.rr/delete_detail/")); ?>" + dId;
            if (confirm('Hapus Item R/R ini ?')) {
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
        var mDpi = $("#DeptId").combobox('getValue');
        var mDte = $("#RrDate").datebox('getValue');
        if (mPri > 0 && mDpi > 0 && mDte != ''){
            return true;
        }else{
            return false;
        }
    }

    function saveDetail(){
        RrId = $("#Id").val();
        var tMod = $("#aMode").val();
        var mPrn = $("#RrNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mDpi = $("#DeptId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mDte = $("#RrDate").datebox('getValue');
        var mRql = $("#ReqLevel").combobox('getValue');
        var dIti = $("#aItemId").val();
        var dQty = $("#aPrQty").textbox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        var dMdi = $("#aMrDetailId").val();
        if (dIti > 0 && dQty > 0){
            var urm = "<?php print($helper->site_url("inventory.rr/proses_master/")); ?>" + RrId;
            //proses simpan dan update master
            $.post(urm,{ProjectId: mPri, DeptId: mDpi,Note: mNot, RrDate: mDte, RrNo: mPrn, ReqLevel: mRql}, function( data ) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    RrId = rst[2];
                    if (RrId > 0) {
                        //proses simpan detail
                        var aid = $("#aId").val();
                        if (tMod == 'N') {
                            var urd = "<?php print($helper->site_url("inventory.rr/add_detail/")); ?>" + RrId;
                        } else {
                            var urd = "<?php print($helper->site_url("inventory.rr/edit_detail/")); ?>" + aid;
                        }
                        $.ajax({
                            type : 'POST',
                            url : urd,
                            data: {aId: aid,aItemId: dIti, aPrQty: dQty, aUomCd: dUom, aMrDetailId: dMdi},
                            success:function (data) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    location.href = "<?php print($helper->site_url("inventory.rr/add/")); ?>" + RrId;
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

    function searchItem(){
        $('#dlg-search').dialog('open').dialog('setTitle', 'Find Items');
        $('#xItemSearch').focus();
    }

</script>
</body>
</html>
