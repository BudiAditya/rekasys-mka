<?php
$pdf = new TabularPdf("L", "mm", "A4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Item Code", "Part Number", "Item Description", "QTY", "Price", "Total"),
    array(9, 30, 40, 100, 20, 35, 40)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

foreach ($report as $idx => $rs){
    $logo = 'public/images/company/'.$rs->Company->FileLogo;
    $pdf->Image($logo, 1, 5, 55, 25);

    $pdf->Cell(207);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 7, strtoupper($rs->Project->ProjectCd.' - '.$rs->Project->ProjectName), 1, 0, 'C');
    $pdf->Ln();

    $pdf->Cell(208);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(10, 7, 'No', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'L');
    $pdf->Cell(20, 7, $rs->DocumentNo, 0, 0, 'L');
    $pdf->Ln(5);
    $pdf->Cell(208);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(10, 7, 'Date', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'L');
    $pdf->Cell(20, 7, long_date(date('Y-m-d', $rs->Date)), 0, 0, 'L');
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(100, 5, $rs->Company->Address.' '.$rs->Company->City, 0, 0, "L");
    $pdf->Ln();
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(100, 5, "Telp. ".$rs->Company->Telephone."  Fax. ".$rs->Company->Facsimile, 0, 0, "L");
    $pdf->Ln();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(287, 5, "PURCHASE ORDER", 0, 0, "C");
    $pdf->Ln();

    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(6, 5, "To", 0, 0, "L");
    $pdf->Cell(5, 5, ":", 0, 0, "L");
    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(30, 5, $rs->Supplier->CreditorName, 0, 0, "L");
    $pdf->Cell(170);
    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(13, 5, "NPWP", 0, 0, "L");
    $pdf->Cell(5, 5, ":", 0, 0, "L");
    $pdf->SetFont('Arial','B', 10);
    $pdf->Cell(30, 5, $rs->Supplier->Npwp, 0, 0, "L");
    $pdf->Ln();

    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(36, 5, $rs->Supplier->Address1, 0, 0, "L");
    $pdf->Cell(175);
    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(13, 5, "Telp", 0, 0, "L");
    $pdf->Cell(5, 5, ":", 0, 0, "L");
    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(30, 5, $rs->Supplier->PhoneNo, 0, 0, "L");
    $pdf->Ln();

    $pdf->Cell(211);
    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(13, 5, "Fax", 0, 0, "L");
    $pdf->Cell(5, 5, ":", 0, 0, "L");
    $pdf->SetFont('Arial','', 10);
    $pdf->Cell(30, 5, $rs->Supplier->FaxNo, 0, 0, "L");
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',10);
    $pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null,
        array("C", "C", "C", "C", "C", "C", "C"));

    $pdf->SetFont('Arial','',10);

    $i = 0;
    $sumTotal = 0;
    foreach ($rs->Details as $row){
        $i++;

        $price = $rs->IsVat == 1 && $rs->IsIncludeVat == 1 ? $row->Price / 1.1 : $row->Price;
        $total = $row->Qty * $price;

        $pdf->RowData(array($i, $row->ItemCode, $row->PartNo, $row->ItemName, $row->Qty . " " . $row->UomCd,
                     "Rp. " . number_format($price, 2,",","."), "Rp. " . number_format($total, 2,",",".")),
                      5, null, 0, array("C", "L", "L", "L", "R", "R", "R"));

        $sumTotal =  $sumTotal + $total;

    }

    $ppn = $rs->IsVat == 1 ? $sumTotal * 0.1 : 0;
    $AmountPaid = $sumTotal + $ppn;

    $pdf->Cell(204);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 5, "SUB TOTAL", 0, 0, "L");
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "Rp. " . number_format($sumTotal, 2,",","."), "LRTB", 0, "R");

    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, 'Remarks : ', 0, 0, 'L');
    $pdf->Cell(184, 5, $rs->Note, 0, 'J');
    $pdf->Cell(30, 5, "VAT (PPN)", 0, 0, "L");
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, "Rp. " . number_format($ppn, 2,",","."), "LRTB", 0, "R");

    $pdf->Ln();
    $pdf->Cell(204);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 5, "TOTAL PO", 0, 0, "L");
    $pdf->Cell(40, 5, "Rp. " . number_format($AmountPaid, 2,",","."), "LRTB", 0, "R");

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(10, 5, 'Note :', 0, 0, 'L');
    $pdf->Cell(140);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 5, "Confirmed By :", 0, 0, "L");
    $pdf->Cell(45, 5, "Issued By :", 0, 0, "L");
    $pdf->Cell(45, 5, "Approved By :", 0, 0, "L");
    $pdf->Ln();

    $pdf->Cell(5, 5, "1.", 0, 0, "L");
    $pdf->MultiCell(100, 5, "If The Quality and specification did not meet  the qualification,it will be returned of seller expenses", 0, "J");
    $pdf->Cell(5, 5, "2.", 0, 0, "L");
    $pdf->Cell(100, 5, "Please attached the original Purchase Order (PO)", 0, 0, "L");
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(25, 5, 'Condition :', 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5, 5, "1.", 0, 0, "L");
    $pdf->Cell(70, 5, "Delivery Date : " . long_date(date("Y-m-d", $rs->ExpectedDate)), 0, 0, "L");
    $pdf->Ln();

    $pdf->Cell(5, 5, "2.", 0, 0, "L");
    $pdf->Cell(70, 5, "Term Of Payment : " . $rs->PaymentTerms." day(s)", 0, 0, "L");
    $pdf->Cell(75);
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(45, 5, $rs->Supplier->CreditorName, 0, 0, "L");
    $pdf->Cell(45, 5, $rs->CreatedUser->UserName, 0, 0, "L");
    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "                           ";
    $pdf->Cell(45, 5, $approvedUser, 0, 0, "L");
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(200,5, "Original - Supplier   Copy 1 - Account Payable   Copy 2 - Purchasing", 0, 0, "L");
}

$pdf->Output("Nota PO.pdf", "D");