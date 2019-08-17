<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $gn GoodReceipt */
?>
<head>
    <title>REKASYS | G/N Review</title>
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
<div id="p" class="easyui-panel" title="G/N Review" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" disabled>
                    <option value="">- Pilih Proyek -</option>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project) {
                        if ($project->Id == $gn->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($gn->Id);?>"/>
                    <input type="hidden" name="GnNo" id="GnNo" value="<?php print($gn->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">G/N No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($gn->DocumentNo != null ? $gn->DocumentNo : '[AUTO]'); ?>" disabled/></td>
            <td class="right">G/N Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="StatusCode" name="StatusCode" value="<?php print($gn->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Supplier :</td>
            <td><select class="easyui-combobox" id="SupplierId" name="SupplierId" style="width: 250px" disabled>
                    <option value="">- Pilih Supplier -</option>
                    <?php
                    /** @var $suppliers Creditor[] */
                    foreach ($suppliers as $supplier) {
                        if ($supplier->Id == $gn->SupplierId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">G/N Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="GnDate" name="GnDate" data-options="formatter:myformatter,parser:myparser" disabled value="<?php print($gn->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Invoice No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="InvoiceNo" name="InvoiceNo" value="<?php print($gn->InvoiceNo); ?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 40px;" value="<?php print($gn->Note);?>" disabled></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="8">G/N Detail</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>PO Number</th>
                        <th>PO Date</th>
                        <th>Item Code</th>
                        <th>Part Number</th>
                        <th>Item Name</th>
                        <th>UOM</th>
                        <th>QTY</th>
                    </tr>
                    <?php
                    $dtx = null;
                    $counter = 0;
                    foreach($gn->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->PoNo);
                        printf("<td nowrap>%s</td>",$detail->PoDate);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->PartNo);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",$detail->Qty);
                        print ("</tr>");
                    }
                    ?>
                    <tr>
                        <td colspan="8" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("inventory.gn", "add")) {
                                printf('<img src="%s" alt="New G/N" title="New G/N" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("inventory.gn", "delete")) {
                                printf('<img src="%s" alt="Delete G/N" title="Delete G/N" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("inventory.gn", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print G/N" title="Print G/N" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="G/N List" title="G/N List" style="cursor: pointer;"/>',$bkembali);
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
        var GnId = "<?php print($gn->Id);?>";
        var GnStatus = "<?php print($gn->StatusCode);?>";
        var ProjectId = "<?php print($gn->ProjectId);?>";
        var SupplierId = "<?php print($gn->SupplierId);?>";

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.gn/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (GnStatus == 1) {
                if (GnId > 0) {
                    if (confirm("Hapus Data G/N ini?")) {
                        location.href = "<?php print($helper->site_url("inventory.gn/delete/")); ?>" + GnId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (GnId > 0) {
                if (confirm("Cetak Data G/N ini?")) {
                    location.href = "<?php print($helper->site_url("inventory.gn/doc_print/pdf?&id[]=")); ?>" + GnId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("inventory.gn")); ?>";
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
