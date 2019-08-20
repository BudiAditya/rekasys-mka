<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $mr Mr */
$counter = 0;
?>
<head>
    <title>REKASYS | MR Entry</title>
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
<div id="p" class="easyui-panel" title="MR Entry" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px">
                    <?php
                    /** @var $projects Project[] */
                    if (count($projects) > 1){
                        print('<option value="">- Pilih Proyek -</option>');
                    }
                    foreach ($projects as $project) {
                        if ($project->Id == $mr->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            if ($project->Id == $projectId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }else {
                                printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($mr->Id);?>"/>
                    <input type="hidden" name="MrNo" id="MrNo" value="<?php print($mr->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">Activity :</td>
            <td colspan="2"><select class="easyui-combobox" id="ActivityId" name="ActivityId" style="width: 250px">
                    <option value="">- Pilih Activity -</option>
                    <?php
                    /** @var $activities Activity[] */
                    foreach ($activities as $act) {
                        if ($act->Id == $mr->ActivityId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">MR No :</td>
            <td>
                <input type="text" class="f1 easyui-textbox" style="width: 140px" id="DocumentNo" name="DocumentNo" value="<?php print($mr->DocumentNo != null ? $mr->DocumentNo : '[AUTO]'); ?>" readonly/>
            </td>
            <td>
                <input type="text" class="easyui-textbox" style="width: 117px" id="StatusCode" name="StatusCode" value="<?php print($mr->GetStatus());?>" disabled/>
            </td>
        </tr>
        <tr>
            <td class="right">Departement :</td>
            <td><select class="easyui-combobox" id="DepartmentId" name="DepartmentId" style="width: 250px">
                    <option value="">- Pilih Departemen -</option>
                    <?php
                    /** @var $departments Department[] */
                    foreach ($departments as $dept) {
                        if ($dept->Id == $mr->DepartmentId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">MR Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="MrDate" name="MrDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($mr->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Request By :</td>
            <td colspan="2"><input type="text" class="easyui-textbox" style="width: 200px" id="RequestBy" name="RequestBy" value="<?php print($mr->RequestBy);?>"/></td>
        </tr>
        <tr valign="top">
            <td class="right">Notes :</td>
            <td colspan="3"><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 460px; height:35px;" value="<?php print($mr->Note);?>"></td>
            <td class="right">Request Level :</td>
            <td colspan="2">
                <select class="easyui-combobox" id="ReqLevel" name="ReqLevel" style="width: 200px" required>
                    <option value="1" <?php print($mr->ReqLevel == 1 ? 'selected="selected"' : '');?>> 1 - Normal </option>
                    <option value="2" <?php print($mr->ReqLevel == 2 ? 'selected="selected"' : '');?>> 2 - Medium </option>
                    <option value="3" <?php print($mr->ReqLevel == 3 ? 'selected="selected"' : '');?>> 3 - Urgent </option>
                </select>
            </td>
            <td class="center">
                <?php
                if ($acl->CheckUserAccess("inventory.mr", "edit") && $mr->StatusCode < 3 && $mr->DocumentNo != null) {
                    printf('<img src="%s" alt="Update MR" title="Update MR" id="bUpdate" style="cursor: pointer;"/>&nbsp;&nbsp;',$bsubmit);
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
                        <th colspan="9">MR Item Detail</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Item Code</th>
                        <th>Part Number</th>
                        <th>Item Name</th>
                        <th>UOM</th>
                        <th>Requested</th>
                        <th>Approved</th>
                        <th>Unit Code</th>
                        <th>Req Status</th>
                    </tr>
                    <?php
                    $dtx = null;
                    foreach($mr->MrDetails as $idx => $detail) {
                        $counter++;
                        print ("<tr class='bold'>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->PartNo);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",$detail->RequestedQty);
                        printf("<td class='right'>%s</td>",$detail->ApprovedQty);
                        printf("<td nowrap>%s</td>",$detail->UnitCode);
                        printf("<td nowrap>%s</td>",$detail->StsItem == 2 ? 'Repair' : 'New');
                        print("<td>");
                        $dtx = addslashes($detail->Id.'|'.$detail->ItemId.'|'.$detail->ItemCode.'|'.$detail->PartNo.'|'.$detail->ItemName.'|'.$detail->UomCd.'|'.$detail->RequestedQty.'|'.$detail->UnitId.'|'.$detail->StsItem);
                        printf('&nbsp<img src="%s" alt="Edit Item" title="Edit Item" style="cursor: pointer" onclick="return fEditDetail(%s);"/>',$bedit,"'".$dtx."'");
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
                        <td>&nbsp;</td>
                        <?php if ($acl->CheckUserAccess("inventory.mr", "add")) { ?>
                            <td class='center'><?php printf('<img src="%s" alt="Add Item" title="Add Item" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        <?php }else{ ?>
                            <td>&nbsp</td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="10" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("inventory.mr", "add")) {
                                printf('<img src="%s" alt="New MR" title="New MR" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("inventory.mr", "delete")) {
                                printf('<img src="%s" alt="Delete MR" title="Delete MR" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("inventory.mr", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print MR" title="Print MR" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="MR List" title="MR List" style="cursor: pointer;"/>',$bkembali);
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
<div id="dlg" class="easyui-dialog" style="width:700px;height:220px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right">Search Item :</td>
                <td colspan="6"><input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:500px"/></td>
            </tr>
            <tr>
                <td class="right">Item Code :</td>
                <td colspan="3"><input type="text" class="easyui-textbox" id="aItemCode" name="aItemCode" style="width:200px" value="" required/></td>
                <td class="right">Part Number :</td>
                <td><input type="text" class="easyui-textbox" id="aPartNo" name="aPartNo" style="width:200px" value="" required/></td>
            </tr>
            <tr>
                <td class="right">Item Name :</td>
                <td colspan="6"><input type="text" class="easyui-textbox" id="aItemName" name="aItemName" style="width:500px" value="" readonly/></td>
            </tr>
            <tr>
                <td class="right">QTY Requested :</td>
                <td><input type="text" class="easyui-textbox right" id="aRequestedQty" name="aRequestedQty" style="width:80px" value="0" required/></td>
                <td class="right">UOM :</td>
                <td><input type="text"  class="easyui-textbox" id="aUomCd" name="aUomCd" style="width:50px" value="" readonly/></td>
                <td class="right">For Unit :</td>
                <td><select name="aUnitId" id="aUnitId" class="easyui-combobox" style="width:200px">
                        <option value="0">-- Pilih Unit --</option>
                        <?php
                        /** @var $units Units[] */
                        foreach ($units as $unit) {
                            printf('<option value="%d">%s - %s</option>', $unit->Id, $unit->UnitCode, $unit->UnitName);
                        }
                        ?>
                    </select>
                    <input type="hidden" name="aId" id="aId" value="0">
                    <input type="hidden" name="aItemId" id="aItemId" value="0">
                    <input type="hidden" name="aMode" id="aMode" value="N"/>
                </td>
            </tr>
            <tr>
                <td class="right">Req Status :</td>
                <td><select name="aStsItem" id="aStsItem" class="easyui-combobox" style="width:80px">
                        <option value="1">New</option>
                        <option value="2">Repair</option>
                    </select>
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
        var MrId = "<?php print($mr->Id);?>";
        var MrStatus = "<?php print($mr->StatusCode);?>";

        $('#aItemSearch').combogrid({
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
                $("#aItemCode").textbox("setValue",itc);
                $("#aItemName").textbox("setValue",itn);
                $("#aPartNo").textbox("setValue",ptn);
                $("#aUomCd").textbox("setValue",ucd);
                $("#aRequestedQty").focus();
            }
        });

        $("#bUpdate").click(function(e){
            //proses update master MR
            if (checkMaster()) {
                if (confirm("Update MR Master?")) {
                    var mMrn = $("#MrNo").val();
                    var mPri = $("#ProjectId").combobox('getValue');
                    var mDpi = $("#DepartmentId").combobox('getValue');
                    var mAci = $("#ActivityId").combobox('getValue');
                    var mNot = $("#Note").textbox('getValue');
                    var mDte = $("#MrDate").datebox('getValue');
                    var mRqb = $("#RequestBy").textbox('getValue');
                    var mRql = $("#ReqLevel").combobox('getValue');
                    var urm = "<?php print($helper->site_url("inventory.mr/proses_master/")); ?>" + MrId;
                    //proses simpan dan update master
                    $.post(urm,{ProjectId: mPri, DepartmentId: mDpi, ActivityId: mAci, Note: mNot, MrDate: mDte, MrNo: mMrn, RequestBy: mRqb, ReqLevel: mRql}, function( data ) {
                        var rst = data.split('|');
                        if (rst[0] == 'OK') {
                            location.reload();
                            //alert(data+' - Update MR Master berhasil!');
                        }else{
                            alert(data+' - Update MR Master gagal!');
                        }
                    });
                }
            }
        });

        $("#bAdDetail").click(function(e){
            newItem(0);
        });

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.mr/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (MrStatus == 1) {
                if (MrId > 0) {
                    if (confirm("Hapus Data MR ini?")) {
                        location.href = "<?php print($helper->site_url("inventory.mr/delete/")); ?>" + MrId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (MrId > 0) {
                if (confirm("Cetak Data MR ini?")) {
                    location.href = "<?php print($helper->site_url("inventory.mr/doc_print/pdf?&id[]=")); ?>" + MrId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.mr")); ?>";
        });

    });

    function newItem(mid){
        if (checkMaster()) {
            $('#dlg').dialog('open').dialog('setTitle', 'Add New MR Item');
            $('#fm').form('clear');
            $('#aMode').val('N');
            $('#aItemSearch').focus();
        }else{
            alert ('Data Master tidak lengkap!');
        }
    }

    function fEditDetail(dta) {
        //$dtx = $detail->Id.'|'.$detail->ItemId.'|'.$detail->ItemCode.'|'.$detail->PartNo.'|'.$detail->ItemName.'|'.$detail->UomCd.'|'.$detail->RequestedQty.'|'.$detail->UnitCode;
        var MrStatus = "<?php print($mr->StatusCode);?>";
        var dtx = dta.split('|');
        var dId = dtx[0];
        //alert("["+dId+"] Proses Edit detail!");
        if (MrStatus == 1) {
            $('#dlg').dialog('open').dialog('setTitle', 'Editing MR Item');
            $('#fm').form('clear');
            $('#aMode').val('E');
            $('#aId').val(dtx[0]);
            $('#aItemId').val(dtx[1]);
            $('#aItemCode').textbox('setValue',dtx[2]);
            $('#aPartNo').textbox('setValue',dtx[3]);
            $('#aItemName').textbox('setValue',dtx[4]);
            $('#aUomCd').textbox('setValue',dtx[5]);
            $('#aRequestedQty').textbox('setValue',dtx[6]);
            $('#aUnitId').combobox('setValue',dtx[7]);
            $('#aStsItem').combobox('setValue',dtx[8]);
            $('#aItemSearch').focus();
        }else{
            alert ("Proses editing tidak diijinkan!");
        }
    }

    function fDelDetail(dId) {
        var MrStatus = "<?php print($mr->StatusCode);?>";
        if (MrStatus == 1) {
            var urx = "<?php print($helper->site_url("inventory.mr/delete_detail/")); ?>" + dId;
            if (confirm('Hapus Item MR ini ?')) {
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
        var mDpi = $("#DepartmentId").combobox('getValue');
        var mAci = $("#ActivityId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mDte = $("#MrDate").datebox('getValue');
        if (mPri > 0 && mDpi > 0 && mAci > 0 && mDte != ''){
            return true;
        }else{
            return false;
        }
    }

    function saveDetail(){
        MrId = $("#Id").val();
        var tMod = $("#aMode").val();
        var mMrn = $("#MrNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mDpi = $("#DepartmentId").combobox('getValue');
        var mAci = $("#ActivityId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mDte = $("#MrDate").datebox('getValue');
        var mRqb = $("#RequestBy").textbox('getValue');
        var mRql = $("#ReqLevel").combobox('getValue');
        var dIti = $("#aItemId").val();
        var dQty = $("#aRequestedQty").textbox('getValue');
        var dUni = $("#aUnitId").combobox('getValue');
        var dSts = $("#aStsItem").combobox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        alert (mRqb+ ' - '+mRql);
        if (dIti > 0 && dQty > 0){
            var urm = "<?php print($helper->site_url("inventory.mr/proses_master/")); ?>" + MrId;
            //proses simpan dan update master
            $.post(urm,{ProjectId: mPri, DepartmentId: mDpi, ActivityId: mAci, Note: mNot, MrDate: mDte, MrNo: mMrn, RequestBy: mRqb, ReqLevel: mRql}, function( data ) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    MrId = rst[2];
                    if (MrId > 0) {
                        //proses simpan detail
                        var aid = $("#aId").val();
                        if (tMod == 'N') {
                            var urd = "<?php print($helper->site_url("inventory.mr/add_detail/")); ?>" + MrId;
                        } else {
                            var urd = "<?php print($helper->site_url("inventory.mr/edit_detail/")); ?>" + aid;
                        }
                        $.ajax({
                            type : 'POST',
                            url : urd,
                            data: {aId: aid,aItemId: dIti, aReqQty: dQty, aUnitId: dUni, aUomCd: dUom, aStsItem: dSts},
                            success:function (data) {
                                var rst = data.split('|');
                                if (rst[0] == 'OK') {
                                    location.href = "<?php print($helper->site_url("inventory.mr/add/")); ?>" + MrId;
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

    function saveDetail1(){
        var tMod = $("#aMode").val();
        var mMri = $("#Id").val();
        var mMrn = $("#MrNo").val();
        var mPri = $("#ProjectId").combobox('getValue');
        var mDpi = $("#DepartmentId").combobox('getValue');
        var mAci = $("#ActivityId").combobox('getValue');
        var mNot = $("#Note").textbox('getValue');
        var mDte = $("#MrDate").datebox('getValue');
        var mRqb = $("#RequestBy").textbox('getValue');
        var mRql = $("#ReqLevel").combobox('getValue');
        var dIti = $("#aItemId").val();
        var dQty = $("#aRequestedQty").textbox('getValue');
        var dUni = $("#aUnitId").combobox('getValue');
        var dUom = $("#aUomCd").textbox('getValue');
        if (dIti > 0 && dQty > 0){
            var urm = "<?php print($helper->site_url("inventory.mr/proses_master/")); ?>" + mMri;
            //proses simpan dan update master
            $.post(urm, {
                ProjectId: mPri,
                DepartmentId: mDpi,
                ActivityId: mAci,
                Note: mNot,
                MrDate: mDte,
                MrNo: mMrn,
                RequestBy: mRqb,
                ReqLevel: mRql
            }).done(function (data) {
                var rst = data.split('|');
                if (rst[0] == 'OK') {
                    var dMri = rst[2];
                    //proses simpan detail
                    if (tMod == 'N'){
                        var urd = "<?php print($helper->site_url("inventory.mr/add_detail/")); ?>" + dMri;
                    }else{
                        var urd = "<?php print($helper->site_url("inventory.mr/edit_detail/")); ?>" + dMri;
                    }
                    $.post(urd, {
                        aItemId: dIti,
                        aReqQty: dQty,
                        aUnitId: dUni,
                        aUomCd: dUom
                    }).done(function (data) {
                        var rsx = data.split('|');
                        var urz = "<?php print($helper->site_url("inventory.mr/add/")); ?>" + dMri;
                        if (rsx[0] == 'OK') {
                            location.href = urz;
                        } else {
                            alert(data);
                        }
                    });
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
