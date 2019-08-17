<?php
$pdf = new TabularPdf("P", "mm", "F4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Code", "Items", "Item Description", "Qty Issued", "Unit Price", "Total Amount"),
    array(9, 20, 30, 50, 25, 30, 40)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

foreach ($report as $idx => $rs){
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(145, 7, 'ITEM ISSUE', 0, 0, 'C');
    $pdf->Cell(60, 7, 'FINANCE & ACCOUNTING', 1, 0, 'C');
    $pdf->Ln();

    if ($rs->StatusCode == 1){
        $pdf->Cell(145, 7, '(DRAFT)', 0, 0, 'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 7, 'No', 0, 0, 'L');
        $pdf->Cell(5, 7, ':', 0, 0, 'L');
        $pdf->Cell(20, 7, $rs->DocumentNo, 0, 0, 'L');
    } else {
        $pdf->Cell(145);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 7, 'No', 0, 0, 'L');
        $pdf->Cell(5, 7, ':', 0, 0, 'L');
        $pdf->Cell(20, 7, $rs->DocumentNo, 0, 0, 'L');
    }

    $pdf->Ln();
    $pdf->Cell(145);
    $pdf->Cell(20, 7, 'Date', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'L');
    $pdf->Cell(20, 7, date('d M Y', $rs->Date), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Cell(145);
    $pdf->Cell(20, 7, 'Departemen', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'L');
    $pdf->Cell(20, 7, $rs->Department->DeptCd, 0, 0, 'L');
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',10);
    $pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C"));

    $pdf->SetFont('Arial','',10);

    $i = 0;
    foreach ($rs->Details as $row){
        $i++;

        $unitPrice = $row->TotalCost / $row->Qty;

        $pdf->RowData(array($i, $row->ItemCode, $row->ItemName, $row->ItemDescription, $row->Qty . " " . $row->UomCd, "Rp. " . number_format($unitPrice ,2,",","."), "Rp. " . number_format($row->TotalCost ,2,",",".")),
            5, null, 0, array("C", "L", "L", "L", "L", "R", "R"));
    }

    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(20, 7, 'REASON : ', 0, 0, 'L');
    $pdf->Cell(170, 7, $rs->Note, 0, 0, 'L');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(68, 7, 'Issued by :', 0, 0, 'C');
    $pdf->Cell(68, 7, 'Approved by :', 0, 0, 'C');
    $pdf->Cell(68, 7, 'Received by :', 0, 0, 'C');
    $pdf->Ln(20);

    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(68, 7, $rs->CreatedUser->UserName, 0, 0, 'C');

    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "";
    $pdf->Cell(68, 7, $approvedUser, 0, 0, 'C');

    $pdf->Cell(68, 7, "___________________", 0, 0, 'C');
    $pdf->Ln(20);

}

$pdf->Output("Nota Item Issue.pdf","D");