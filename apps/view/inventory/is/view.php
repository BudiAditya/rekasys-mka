<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $is ItemIssue */
$counter = 0;
?>
<head>
    <title>REKASYS | Item Issue Review</title>
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
<div id="p" class="easyui-panel" title="Item Issue Review" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" disabled>
                    <option value="">- Pilih Proyek -</option>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project) {
                        if ($project->Id == $is->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($is->Id);?>"/>
                    <input type="hidden" name="IssueNo" id="IssueNo" value="<?php print($is->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">Activity :</td>
            <td><select class="easyui-combobox" id="ActivityId" name="ActivityId" style="width: 250px" disabled>
                    <option value="">- Pilih Activity -</option>
                    <?php
                    /** @var $activities Activity[] */
                    foreach ($activities as $act) {
                        if ($act->Id == $is->ActivityId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $act->Id, $act->ActCode, $act->ActName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right">Departement :</td>
            <td><select class="easyui-combobox" id="DepartmentId" name="DepartmentId" style="width: 250px" disabled>
                    <option value="">- Pilih Departemen -</option>
                    <?php
                    /** @var $departments Department[] */
                    foreach ($departments as $dept) {
                        if ($dept->Id == $is->DepartmentId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $dept->Id, $dept->DeptCode, $dept->DeptName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">Issue No :</td>
            <td>
                <input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($is->DocumentNo != null ? $is->DocumentNo : '[AUTO]'); ?>" readonly/>
                <input type="text" class="easyui-textbox" style="width: 117px" id="StatusCode" name="StatusCode" value="<?php print($is->GetStatus());?>" disabled/>
            </td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 50px;" value="<?php print($is->Note);?>" disabled></td>
            <td class="right">Issue Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="IssueDate" name="IssueDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($is->FormatDate(SQL_DATEONLY));?>" disabled/></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="12">Item Issue Detail</th>
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
                        <th>Unit</th>
                        <th>HM</th>
                        <th>Operator</th>
                        <th>Shift</th>
                    </tr>
                    <?php
                    $dtx = null;
                    foreach($is->Details as $idx => $detail) {
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
                        printf("<td nowrap>%s</td>",$detail->UnitCode);
                        printf("<td nowrap>%s</td>",$detail->Hm);
                        printf("<td nowrap>%s</td>",$detail->Operator);
                        printf("<td nowrap>%s</td>",$detail->DayShift);
                        print ("</tr>");
                    }
                    ?>
                    <tr>
                        <td colspan="12" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("inventory.is", "add")) {
                                printf('<img src="%s" alt="New Item Issue" title="New Item Issue" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("inventory.is", "delete")) {
                                printf('<img src="%s" alt="Delete Item Issue" title="Delete Item Issue" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("inventory.is", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print Item Issue" title="Print Item Issue" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="Item Issue List" title="Item Issue List" style="cursor: pointer;"/>',$bkembali);
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
    $( function() {
        var IssueId = "<?php print($is->Id);?>";
        var IssueStatus = "<?php print($is->StatusCode);?>";
        var ProjectId = "<?php print($is->ProjectId);?>";

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.is/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (IssueStatus == 1) {
                if (IssueId > 0) {
                    if (confirm("Hapus Data Item Issue ini?")) {
                        location.href = "<?php print($helper->site_url("inventory.is/delete/")); ?>" + IssueId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (IssueId > 0) {
                if (confirm("Cetak Data Item Issue ini?")) {
                    location.href = "<?php print($helper->site_url("inventory.is/doc_print/pdf?&id[]=")); ?>" + IssueId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.is")); ?>";
        });

    });

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
