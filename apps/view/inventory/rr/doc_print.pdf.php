<?php
$pdf = new TabularPdf("P", "mm", "A4");
$pdf->SetAutoPageBreak(true,10);

$pdf->SetColumns(
    array("No", "Code", "P/N", "Item Name", "Qty", "Unit", "Remarks"),
    array(10, 30, 40, 70, 15, 15, 20)
);
$pdf->SetDefaultBorders(array("RBL", "RB", "RB", "RB", "RB", "RB", "RB"));
$pdf->SetMargins(5, 10);

$pdf->Open();
$pdf->AddPage();

foreach ($report as $idx => $rs){
    $logo = 'public/images/company/'.$company->FileLogo;
    $pdf->Image($logo, 5, 5, 40, 25);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 7, 'PURCHASE REQUISITION', 0, 0, 'C');
    $pdf->Cell(60, 7, strtoupper($rs->ProjectCd.' - '.$rs->ProjectName), 1, 0, 'C');
    $pdf->Ln();

    $pdf->Cell(145);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(10, 7, 'No', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'L');
    $pdf->Cell(20, 7, $rs->DocumentNo, 0, 0, 'L');
    $pdf->Ln();
    $pdf->Cell(145);
    $pdf->Cell(10, 7, 'Date', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'L');
    $pdf->Cell(20, 7, date('d M Y', $rs->Date), 0, 0, 'L');
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',10);
    $pdf->RowHeader(7, array("TRBL", "TRB", "TRB", "TRB", "TRB", "TRB", "TRB"), null, array("C", "C", "C", "C", "C", "C", "C"));

    $pdf->SetFont('Arial','',10);

    $i = 0;
    $sumTotal = 0;
    foreach ($rs->Details as $row){
        $i++;
        $pdf->RowData(array($i, $row->ItemCode, $row->PartNo, $row->ItemName, $row->Qty, $row->UomCd, $row->ItemDescription),
            5, null, 0, array("C", "L", "L", "L", "C", "L", "L"));
    }
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(20, 7, 'Notes : ', 0, 0, 'L');
    $pdf->Cell(170, 7, $rs->Note, 0, 0, 'L');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(70, 7, 'Prepared by :', 0, 0, 'C');
    $pdf->Cell(70, 7, 'Approved by1 :', 0, 0, 'C');
    $pdf->Cell(70, 7, 'Approved by2 :', 0, 0, 'C');
    $pdf->Ln(20);

    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(70, 7, $rs->CreatedUser->UserName, 0, 0, 'C');

    $approvedUser = $rs->ApprovedUser != null ? $rs->ApprovedUser->UserName : "";
    $pdf->Cell(70, 7, $approvedUser, 0, 0, 'C');

    $approved2User = $rs->Approved2User != null ? $rs->Approved2User->UserName : "";
    $pdf->Cell(70, 7, $approved2User, 0, 0, 'C');
    $pdf->Ln(20);
}

$pdf->Output("IVT-PR.pdf", "D");