<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $rr Rr */
$counter = 0;
?>
<head>
    <title>REKASYS | R/R Approval Process</title>
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
<div id="p" class="easyui-panel" title="R/R Approval Process" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" disabled>
                    <option value="">- Pilih Proyek -</option>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project) {
                        if ($project->Id == $rr->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($rr->Id);?>"/>
                    <input type="hidden" name="PrNo" id="PrNo" value="<?php print($rr->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">Dept :</td>
            <td><select class="easyui-combobox" id="DeptId" name="DeptId" style="width: 250px" disabled>
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
            <td rowspan="2"><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 40px;" value="<?php print($rr->Note);?>" disabled></td>
            <td class="right">R/R Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="RrDate" name="RrDate" data-options="formatter:myformatter,parser:myparser" readonly value="<?php print($rr->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="StatusCode" name="StatusCode" value="<?php print($rr->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Request Level :</td>
            <td>
                <select class="easyui-combobox" id="ReqLevel" name="ReqLevel" style="width: 130px" disabled>
                    <option value="1" <?php print($rr->ReqLevel == 1 ? 'selected="selected"' : '');?>> 1 - Normal </option>
                    <option value="2" <?php print($rr->ReqLevel == 2 ? 'selected="selected"' : '');?>> 2 - Medium </option>
                    <option value="3" <?php print($rr->ReqLevel == 3 ? 'selected="selected"' : '');?>> 3 - Urgent </option>
                </select>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <form action="<?php print($helper->site_url("purchase.rr/process/" . $rr->Id)); ?>" method="post">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th colspan="10">Repair Requisition Detail</th>
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
                            <th>Repair Price</th>
                            <th>Supplier / Bengkel</th>
                        </tr>
                        <?php
                        $dtx = null;
                        foreach($rr->Details as $idx => $detail) {
                            $counter++;
                            print ("<tr>");
                            printf("<td align='center'>%s",$counter);
                            printf('<input type="hidden" name="data[]" value="%s|%s|%s|%s|%s"/>', $detail->Id, $detail->MrDetailId, $detail->ItemId, $detail->Qty, $detail->UomCd);
                            print("</td>");
                            printf("<td nowrap>%s</td>",$detail->MrNo);
                            printf("<td nowrap>%s</td>",$detail->MrDate);
                            printf("<td nowrap>%s</td>",$detail->ItemCode);
                            printf("<td nowrap>%s</td>",$detail->PartNo);
                            printf("<td nowrap>%s</td>",$detail->ItemName);
                            printf("<td>%s</td>",$detail->UomCd);
                            printf("<td class='right'>%s</td>",$detail->Qty);
                            printf('<td class="right"><input type="text" class="right" id="Price1_%d" name="Price1[]" size="10" value="%d" %s/></td>',$idx,$detail->Price1,$rr->StatusCode > 3 ? 'readonly' : '');
                            print("<td>");
                            printf("<select name='SupplierId1[]' id='SupplierId1_%d' %s>",$idx,$rr->StatusCode > 3 ? 'disabled' : '');
                            print("<option value='0'></option>");
                            /** @var $suppliers Creditor[] */
                            foreach ($suppliers as $supplier){
                                if ($supplier->Id == $detail->SupplierId1) {
                                    printf("<option value='%d' selected='selected'>%s</option>", $supplier->Id, $supplier->CreditorName);
                                }else{
                                    printf("<option value='%d'>%s</option>",$supplier->Id,$supplier->CreditorName);
                                }
                            }
                            print("</select>");
                            print("</td>");
                            print ("</tr>");
                        }
                        ?>
                        <tr>
                            <td colspan="10" valign="middle" align="right">
                                <?php if ($rr->StatusCode < 4){ ?>
                                <button type="submit">PROCESS</button>
                                &nbsp;&nbsp;&nbsp;
                                <?php } ?>
                                <a href="<?php print($helper->site_url("purchase.rr")); ?>">R/R List</a>
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2019  PT. Rekasystem Technology
</div>

<script type="text/javascript">
    $( function() {
        var PrId = "<?php print($rr->Id);?>";
        var PrStatus = "<?php print($rr->StatusCode);?>";
        var ProjectId = "<?php print($rr->ProjectId);?>";

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("purchase.rr/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (PrStatus == 1) {
                if (PrId > 0) {
                    if (confirm("Hapus Data R/R ini?")) {
                        location.href = "<?php print($helper->site_url("purchase.rr/delete/")); ?>" + PrId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (PrId > 0) {
                if (confirm("Cetak Data R/R ini?")) {
                    location.href = "<?php print($helper->site_url("purchase.rr/doc_print/pdf?&id[]=")); ?>" + PrId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("purchase.rr")); ?>";
        });

    })

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
