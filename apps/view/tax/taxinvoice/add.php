<!DOCTYPE html>
<?php
/** @var  $taxtypes TaxType[] */
/** @var  $taxInvoice TaxInvoice */
?>

<html>
<head>
	<title>Rekasys - Add New Faktur Pajak</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

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

	<script type="text/javascript">
		$(document).ready(function () {
			//var elements = ["TaxCode", "TaxType","SourceFrom","TaxRate","IsDeductable","AccId","Save"];
			//BatchFocusRegister(elements);

            $('#xTaxTypeId').combobox({
                onSelect: function (row) {
                    var dta = row.value.split('|');
                    var urx = "";
                    $("#TaxTypeId").val(dta[0]);
                    $("#TaxMode").val(dta[1]);
                    $("#TaxRate").val(dta[2]);
                    if (dta[1] == 1){
                        urx = "<?php print($helper->site_url("tax.taxinvoice/getcreditor_json")); ?>";
                    }else if (dta[1] == 2){
                        urx = "<?php print($helper->site_url("tax.taxinvoice/getdebtor_json")); ?>";
                    }
                    $("#xDbCrId").combobox('reload', urx);
                }
            });

            $("#xDbCrId").combobox({
                valueField:'id',textField:'name',
                onSelect: function (row) {
                    $("#DbCrId").val(row.id);
                    var txm = $("#TaxMode").val();
                    var dci = row.id;
                    var urx = "";
                    if (txm == 1){
                        urx = "<?php print($helper->site_url("tax.taxinvoice/getapinvoice_json/")); ?>"+dci;
                    }else if (txm == 2){
                        urx = "<?php print($helper->site_url("tax.taxinvoice/getarinvoice_json/")); ?>"+dci;
                    }
                    $("#ReffNo").combobox('reload', urx);
                }
            });

            //$out = "OK|".$invoice->Id."|".$invoice->InvoiceDate."|".$invoice->TaxInvoiceNo."|".$invoice->BaseAmount;
            $("#ReffNo").combobox({
                valueField:'reff_no',textField:'reff_no',
                onSelect: function(row){
                    var txm = $("#TaxMode").val();
                    var rli = $("#DbCrId").val();
                    //var rno = $("#ReffNo").textbox('getValue');
                    var rno = row.reff_no;
                    var txr = $("#TaxRate").val();
                    if (txm == 1) {
                        var urx = "<?php print($helper->site_url("tax.taxinvoice/getDataApInvoice")); ?>";
                    }else {
                        var urx = "<?php print($helper->site_url("tax.taxinvoice/getDataArInvoice")); ?>";
                    }
                    $.post(urx,{relasiId: rli, reffNo: rno}, function(data){
                        var dta = data.split("|");
                        if (dta[0] == "OK"){
                            $("#TaxInvoiceDate").datebox('setValue',dta[2]);
                            $("#TaxInvoiceNo").textbox('setValue',dta[3]);
                            $("#DppAmount").textbox('setValue',dta[4]);
                            var dpp = Number(dta[4]);
                            var tax = 0;
                            if (txr > 0){
                                tax = Math.round(dpp * (txr/100),0);
                            }
                            $("#TaxAmount").textbox('setValue',tax);
                        }else{
                            if (dta[1] == "1"){
                                alert("Data Refferensi/Invoice tidak sesuai dengan Creditor/Debtor!");
                            }else{
                                alert("Data Refferensi/Invoice tidak ditemukan!");
                            }
                        }
                    });
                }
            });

            $("#bSave").click(function(e){
               var rno = $("#ReffNo").combobox("getValue");
               var tin = $("#TaxInvoiceNo").textbox("getValue");
               var tid = $("#TaxInvoiceDate").datebox("getValue");
               var tlp = $("#TglLapor").datebox("getValue");
               var dci = $("#DbCrId").val();
               var tti = $("#TaxTypeId").val();
               var trt = $("#TaxRate").val();
               var txm = $("#TaxMode").val();
               var dpp = $("#DppAmount").textbox("getValue");
               var tax = $("#TaxAmount").textbox("getValue");
               var urx = "<?php print($helper->site_url("tax.taxinvoice/simpan/0")); ?>";
               dpp = dpp.replace(/\,/g,'');
               dpp = Number(dpp);
               tax = tax.replace(/\,/g,'');
               tax = Number(tax);
               //validate first
               if (dci > 0 && tti > 0 && trt > 0 && dpp > 0 && tax > 0 && rno != '' && tin != '' && tid != ''){
                   if (confirm("Apakah data input sudah benar?")) {
                       $.post(urx,
                           {
                               ReffNo: rno,
                               TaxInvoiceNo: tin,
                               TaxInvoiceDate: tid,
                               TglLapor: tlp,
                               DbCrId: dci,
                               TaxTypeId: tti,
                               DppAmount: dpp,
                               TaxAmount: tax,
                               TaxRate: trt,
                               TaxMode: txm
                           },
                           function (data) {
                               var dtx = data.split('|');
                               if (dtx[0] == "OK") {
                                   location.href = "<?php print($helper->site_url("tax.taxinvoice")); ?>";
                               } else {
                                   alert(data);
                               }
                           });
                   }
               } else{
                   alert ('Data input tidak valid!');
               }
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
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend class="bold">Add New Faktur Pajak</legend>
    <table cellpadding="0" cellspacing="0" class="tablePadding">
        <tr>
            <td class="right">Perusahaan :</td>
            <td><?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?></td>
        </tr>
        <tr>
            <td align="right">Jenis Pajak :</td>
            <td><select class="easyui-combobox" name="xTaxTypeId" id="xTaxTypeId" style="width: 200px" required>
                    <option value=""> - Pilih Jenis Pajak - </option>
                    <?php
                    foreach ($taxtypes as $taxtype) {
                        if ($taxtype->Id == $taxInvoice->TaxTypeId) {
                            printf('<option value="%d|%d|%s" selected="selected">%s - %s</option>', $taxtype->Id, $taxtype->TaxMode, $taxtype->TaxRate, $taxtype->TaxCode, $taxtype->TaxType);
                        } else {
                            printf('<option value="%d|%d|%s">%s - %s</option>', $taxtype->Id, $taxtype->TaxMode, $taxtype->TaxRate, $taxtype->TaxCode, $taxtype->TaxType);
                        }
                    }
                    ?>
                </select>
                <input type="hidden" name="TaxTypeId" id="TaxTypeId" value="<?php print($taxInvoice->TaxTypeId);?>">
                <input type="hidden" name="SourceFrom" id="SourceFrom" value="<?php print($taxInvoice->SourceFrom);?>">
                <input type="hidden" name="TaxRate" id="TaxRate" value="<?php print($taxInvoice->TaxRate);?>">
                <input type="hidden" name="TaxMode" id="TaxMode" value="<?php print($taxInvoice->TaxMode);?>">
                <input type="hidden" name="DbCrId" id="DbCrId" value="<?php print($taxInvoice->DbCrId);?>">
                <input type="hidden" name="Id" id="Id" value="<?php print($taxInvoice->Id);?>">
            </td>
            <td align="right">Status :</td>
            <td><input name="Status" id="Status" class="easyui-textbox" style="width: 120px" value="<?php print($taxInvoice->GetStatus()); ?>" disabled></td>
        </tr>
        <tr>
            <td align="right">Nama Relasi :</td>
            <td colspan="2"><input id="xDbCrId" name="xDbCrId" style="width: 300px"></td>
        </tr>
        <tr>
            <td align="right">No. Reff/Invoice :</td>
            <td><input class="easyui-textbox" name="ReffNo" id="ReffNo" style="width: 200px" value="<?php print($taxInvoice->ReffNo); ?>" required/></td>
            <td align="right">Tgl Transaksi :</td>
            <td><input type="text" class="easyui-datebox" style="width: 120px" id="TaxInvoiceDate" name="TaxInvoiceDate" data-options="formatter:myformatter,parser:myparser" required value="<?php print($taxInvoice->FormatTaxInvoiceDate(SQL_DATEONLY));?>"/></td>
            <td align="right">Tgl Lapor :</td>
            <td><input type="text" class="easyui-datebox" style="width: 120px" id="TglLapor" name="TglLapor" data-options="formatter:myformatter,parser:myparser" value="<?php print($taxInvoice->FormatTglLapor(SQL_DATEONLY));?>"/></td>
        </tr>
        <tr>
            <td align="right">No. Seri Faktur :</td>
            <td><input class="easyui-textbox" name="TaxInvoiceNo" id="TaxInvoiceNo" style="width: 200px" value="<?php print($taxInvoice->TaxInvoiceNo); ?>" required onkeyup="this.value = this.value.toUpperCase();"/></td>
            <td align="right">D P P :</td>
            <td><input type="text" class="easyui-numberbox numberbox-f validatebox-text" data-options="precision:0,groupSeparator:',',decimalSeparator:'.'" id="DppAmount" name="DppAmount" style="width:120px" value="<?php print($taxInvoice->DppAmount); ?>" required/></td>
            <td align="right">Pajak :</td>
            <td><input type="text" class="easyui-numberbox numberbox-f validatebox-text" data-options="precision:0,groupSeparator:',',decimalSeparator:'.'" id="TaxAmount" name="TaxAmount" style="width:120px" value="<?php print($taxInvoice->TaxAmount); ?>" required/></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="5">
                <button id="bSave" type="button">SIMPAN</button>
                &nbsp;
                <a href="<?php print($helper->site_url("tax.taxinvoice")); ?>">Tax Invoice List</a>
            </td>
        </tr>
    </table>
</fieldset>
</body>

</html>
