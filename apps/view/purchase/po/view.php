<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<?php
/** @var $po Po */
$counter = 0;
?>
<head>
    <title>REKASYS | P/O Review</title>
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
<div id="p" class="easyui-panel" title="P/O Review" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right">Project :</td>
            <td><select class="easyui-combobox" id="ProjectId" name="ProjectId" style="width: 250px" required>
                    <option value="">- Pilih Proyek -</option>
                    <?php
                    /** @var $projects Project[] */
                    foreach ($projects as $project) {
                        if ($project->Id == $po->ProjectId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                        }
                    }
                    ?>
                    <input type="hidden" name="Id" id="Id" value="<?php print($po->Id);?>"/>
                    <input type="hidden" name="PoNo" id="PoNo" value="<?php print($po->DocumentNo);?>"/>
                </select>
            </td>
            <td class="right">P/O No :</td>
            <td><input type="text" class="f1 easyui-textbox" style="width: 130px" id="DocumentNo" name="DocumentNo" value="<?php print($po->DocumentNo != null ? $po->DocumentNo : '[AUTO]'); ?>" readonly/></td>
            <td class="right">P/O Status :</td>
            <td><input type="text" class="easyui-textbox" style="width: 130px" id="StatusCode" name="StatusCode" value="<?php print($po->GetStatus());?>" disabled/></td>
        </tr>
        <tr>
            <td class="right">Supplier :</td>
            <td><select class="easyui-combobox" id="SupplierId" name="SupplierId" style="width: 250px" required>
                    <option value="">- Pilih Supplier -</option>
                    <?php
                    /** @var $suppliers Creditor[] */
                    foreach ($suppliers as $supplier) {
                        if ($supplier->Id == $po->SupplierId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->CreditorCd, $supplier->CreditorName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="right">P/O Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="PoDate" name="PoDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($po->FormatDate(SQL_DATEONLY));?>"/></td>
            <td class="right">Expected Date :</td>
            <td><input type="text" class="easyui-datebox" style="width: 130px" id="ExpectedDate" name="ExpectedDate" data-options="formatter:myformatter,parser:myparser" required="required" value="<?php print($po->FormatExpectedDate(SQL_DATEONLY));?>"/></td>
        </tr>
        <tr>
            <td class="right">Notes :</td>
            <td><input type="text" class="easyui-textbox" name="Note" id="Note" data-options="multiline:true" style="width: 250px; height: 40px;" value="<?php print($po->Note);?>"></td>
            <td class="right">Credit Terms :</td>
            <td><input type="text" style="width: 50px" class="bold right" id="PaymentTerms" name="PaymentTerms" value="<?php print($po->PaymentTerms);?>"/>&nbsp;Day(s)</td>
            <td class="right">P P N :</td>
            <td>
                <input type="checkbox" id="IsVat" name="IsVat" value="1" <?php print($po->IsVat ? 'checked="checked"' : '') ?> />
                <label for="IsVat">Kena PPN 10%</label>
                <input type="checkbox" id="IsIncVat" name="IsIncVat" value="1" <?php print($po->IsIncludeVat ? 'checked="checked"' : '') ?> />
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
                        <th colspan="10">P/O Detail</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>PR Number</th>
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
                    foreach($po->Details as $idx => $detail) {
                        $counter++;
                        print ("<tr>");
                        printf("<td align='center'>%s</td>",$counter);
                        printf("<td nowrap>%s</td>",$detail->PrNo);
                        printf("<td nowrap>%s</td>",$detail->PrDate);
                        printf("<td nowrap>%s</td>",$detail->ItemCode);
                        printf("<td nowrap>%s</td>",$detail->PartNo);
                        printf("<td nowrap>%s</td>",$detail->ItemName);
                        printf("<td>%s</td>",$detail->UomCd);
                        printf("<td class='right'>%s</td>",$detail->Qty);
                        printf("<td class='right'>%s</td>",number_format($detail->Price,0));
                        printf("<td class='right'>%s</td>",number_format($detail->Qty * $detail->Price,0));
                        print ("</tr>");
                        $stotal+= $detail->Qty * $detail->Price;
                    }
                    ?>
                    <tr>
                        <td colspan="9" align="right"><?php print($po->IsVat == 1 ? 'Sub Total' : 'Total');?></td>
                        <td align="right"><?php print(number_format($stotal,0));?></td>
                    </tr>
                    <?php
                    if ($po->IsVat == 1){
                        if ($po->IsIncludeVat == 1) {
                            print("<tr>");
                            print('<td colspan="9" align="right">DPP</td>');
                            printf('<td align="right">%s</td>',number_format(round($stotal/1.1,0),0));
                            print("</tr>");
                            print("<tr>");
                            print('<td colspan="9" align="right">PPN 10%</td>');
                            printf('<td align="right">%s</td>',number_format(round(($stotal/1.1)/10,0),0));
                            print("</tr>");
                        }else{
                            print("<tr>");
                            print('<td colspan="9" align="right">PPN 10%</td>');
                            printf('<td align="right">%s</td>',number_format(round($stotal/10,0),0));
                            print("</tr>");
                            print("<tr>");
                            print('<td colspan="9" align="right">Total</td>');
                            printf('<td align="right">%s</td>',number_format(round($stotal * 1.1,0),0));
                            print("</tr>");
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="10" valign="middle" align="right">
                            <?php
                            if ($acl->CheckUserAccess("purchase.po", "add")) {
                                printf('<img src="%s" alt="New P/O" title="New P/O" id="bTambah" style="cursor: pointer;"/>&nbsp;&nbsp;',$baddnew);
                            }
                            if ($acl->CheckUserAccess("purchase.po", "delete")) {
                                printf('<img src="%s" alt="Delete P/O" title="Delete P/O" id="bHapus" style="cursor: pointer;"/>&nbsp;&nbsp;',$bdelete);
                            }
                            if ($acl->CheckUserAccess("purchase.po", "print")) {
                                printf('<img src="%s" id="bCetak" alt="Print P/O" title="Print P/O" style="cursor: pointer;"/>&nbsp;&nbsp;',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="P/O List" title="P/O List" style="cursor: pointer;"/>',$bkembali);
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
        var PoId = "<?php print($po->Id);?>";
        var PoStatus = "<?php print($po->StatusCode);?>";
        var ProjectId = "<?php print($po->ProjectId);?>";
        var SupplierId = "<?php print($po->SupplierId);?>";

        $("#bTambah").click(function(e){
            location.href = "<?php print($helper->site_url("purchase.po/add/0")); ?>";
        });

        $("#bHapus").click(function(e){
            if (PoStatus == 1) {
                if (PoId > 0) {
                    if (confirm("Hapus Data P/O ini?")) {
                        location.href = "<?php print($helper->site_url("purchase.po/delete/")); ?>" + PoId;
                    }
                }
            }else{
                alert ("Proses Delete tidak diijinkan!");
            }
        });

        $("#bCetak").click(function(e){
            if (PoId > 0) {
                if (confirm("Cetak Data P/O ini?")) {
                    location.href = "<?php print($helper->site_url("purchase.po/doc_print/pdf?&id[]=")); ?>" + PoId;
                }
            }
        });

        $("#bKembali").click(function(e){
            location.href = "<?php print($helper->site_url("purchase.po")); ?>";
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
