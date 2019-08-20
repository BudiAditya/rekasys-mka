<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $mr Mr */
$counter = 0;
?>
<head>
    <title>REKASYS | MR Approval - Level <?php print($level);?></title>
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
<div id="p" class="easyui-panel" title="MR Approval <?php print($level);?>" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><input class="easyui-textbox" id="ProjectId" name="ProjectId" style="width: 250px" value="<?php printf("%s - %s", $project->ProjectCd, $project->ProjectName); ?>" readonly></td>
            <td class="right">Activity :</td>
            <td><input class="easyui-textbox" id="ActivityId" name="ActivityId" style="width: 250px" value="<?php printf("%s - %s", $activity->ActCode, $activity->ActName); ?>" readonly></td>
            <td class="right">MR No :</td>
            <td>
                <input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($mr->DocumentNo != null ? $mr->DocumentNo : '[AUTO]'); ?>" readonly/>
                <input type="text" class="easyui-textbox" style="width: 117px" id="StatusCode" name="StatusCode" value="<?php print($mr->GetStatus());?>" disabled/>
            </td>
        </tr>
        <tr>
            <td class="right">Departement :</td>
            <td><input class="easyui-textbox" id="DepartmentId" name="DepartmentId" style="width: 250px" value="<?php printf("%s - %s", $department->DeptCode, $department->DeptName); ?>" readonly></td>
            <td class="right">MR Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="MrDate" name="MrDate" data-options="formatter:myformatter,parser:myparser"  readonly value="<?php print($mr->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Request By :</td>
            <td colspan="2"><input type="text" class="easyui-textbox" style="width: 200px" id="RequestBy" name="RequestBy" value="<?php print($mr->RequestBy);?>" readonly/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td colspan="3"><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 460px; height: 35px;" value="<?php print($mr->Note);?>" readonly></td>
            <td class="right">Request Level :</td>
            <td colspan="2">
                <select class="easyui-combobox" id="ReqLevel" name="ReqLevel" style="width: 200px" disabled>
                    <option value="1" <?php print($mr->ReqLevel == 1 ? 'selected="selected"' : '');?>> 1 - Normal </option>
                    <option value="2" <?php print($mr->ReqLevel == 2 ? 'selected="selected"' : '');?>> 2 - Medium </option>
                    <option value="3" <?php print($mr->ReqLevel == 3 ? 'selected="selected"' : '');?>> 3 - Urgent </option>
                </select>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <form action="<?php print($helper->site_url("inventory.mr/approve/".$level."/". $mr->Id)); ?>" method="post">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th colspan="9">MR Item Detail</th>
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
                            printf("<td align='center'>%s",$counter);
                            printf('<input type="hidden" name="data[]" value="%s|%s|%s|%s|%s|%s"/>', $detail->Id, $detail->ItemCode, $detail->ItemName, $detail->RequestedQty, $detail->UomCd, $detail->ItemDescription);
                            print("</td>");
                            printf("<td nowrap>%s</td>",$detail->ItemCode);
                            printf("<td nowrap>%s</td>",$detail->PartNo);
                            printf("<td nowrap>%s</td>",$detail->ItemName);
                            printf("<td>%s</td>",$detail->UomCd);
                            printf("<td class='right'>%s</td>",$detail->RequestedQty);
                            printf('<td class="right"><input type="text" class="right" id="txtQty_%d" name="QtyApprove[]" size="5" value="%d"/></td>',$idx,$detail->ApprovedQty);
                            printf("<td nowrap>%s</td>",$detail->UnitCode);
                            printf("<td nowrap>%s</td>",$detail->StsItem == 2 ? 'Repair' : 'New');
                            print ("</tr>");
                        }
                        ?>
                        <tr>
                            <td colspan="10" valign="middle" align="right">
                                <button type="submit">APPROVAL <?php print($level);?></button>
                                &nbsp;&nbsp;&nbsp;
                                <a href="<?php print($helper->site_url("inventory.mr")); ?>">MR List</a>
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
        var MrId = "<?php print($mr->Id);?>";
        var MrStatus = "<?php print($mr->StatusCode);?>";
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
