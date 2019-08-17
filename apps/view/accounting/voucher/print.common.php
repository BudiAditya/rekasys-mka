<?php
function CreateCommon(TabularPdf $pdf, Voucher $rs, $counter, $subPage, $totalPage) {
    /* yg dipake adalah counter dan bukan idx, krn detail voucher tdk bisa diprediksi */
    if ($counter % 2 == 0) {
        $pdf->AddPage();
    } else {
        $pdf->SetY(140);
    }

	$widths = $pdf->GetColumnWidths();
	$sumWidths = array_sum($widths);

    $pdf->SetFont("Tahoma", "", 14);
    $pdf->Cell(100, 5, $rs->Company->CompanyName, 0, 0, "L");

    $pdf->Ln(7);

    $pdf->SetFont("Tahoma", "", 14);
    $pdf->Cell($pdf->GetPaperWidth(), 5, $rs->VoucherType->VoucherDesc, 0, 0, "C");
    $pdf->SetFont("Tahoma", "", 10);
	$pdf->SetX(-55, true);
    $pdf->Cell(15, 5, "No.Bukti", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "C");
    $pdf->Cell(50, 5, $rs->DocumentNo, 0, 0, "L");
    $pdf->Ln();

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->SetX(-55, true);
    $pdf->Cell(15, 5, "Tanggal", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "C");
    $pdf->Cell(50, 5, long_date(date("Y-m-d", $rs->Date)), 0, 0, "L");
    $pdf->Ln(7);

    $pdf->Line(5, $pdf->GetY(), $sumWidths + 5, $pdf->GetY()); //top
    $pdf->Line(5, $pdf->GetY(), 5, 22 + $pdf->GetY()); //left
    $pdf->Line(140, ($pdf->GetY()), 140, (22 + $pdf->GetY())); //center vertikal
    $pdf->Line($sumWidths + 5, $pdf->GetY(), $sumWidths + 5, 22 + $pdf->GetY()); //right

    $pdf->SetY($pdf->GetY() + 2);
    $pdf->Cell(30, 5, "Diterima dari ", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "C");
    $pdf->SetX(140);
    $pdf->Cell(28, 5, "Rekening Bank", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "L");
    $pdf->Ln(8);

    $pdf->Cell(30, 5, "Terbilang", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "C");

    $x1 = $pdf->GetX();
    $y1 = $pdf->GetY();

    $pdf->SetX(140);
    $pdf->Cell(28, 5, "No. Chq/Giro/Trf", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "L");
    $pdf->Ln();

    $pdf->SetY($pdf->GetY() + 7);
    $pdf->SetFont("Tahoma", "", 10);
	$pdf->RowHeader(5, array('TRBL', 'TRB', 'TRB', 'TRB'), null, array('C', 'C', 'C', 'C'));

    $pdf->Line(5, $pdf->GetY(), 5, (45 + $pdf->GetY()));
    $pdf->Line(5 + $widths[0], $pdf->GetY(), 5 + $widths[0], (45 + $pdf->GetY()));
    $pdf->Line(5 + $widths[0] + $widths[1], $pdf->GetY(), 5 + $widths[0] + $widths[1], (45 + $pdf->GetY()));
    $pdf->Line(5 + $widths[0] + $widths[1] + $widths[2], $pdf->GetY(), 5 + $widths[0] + $widths[1] + $widths[2], (45 + $pdf->GetY()));
    $pdf->Line(5 + $sumWidths, $pdf->GetY(), 5 + $sumWidths, (45 + $pdf->GetY()));
    $y3 = 45 + $pdf->GetY();

    $subTotal = 0;
    $coa = null;
    $coaHeader = array();
    //foreach ($rs->Details as $row) {
    $start = ($subPage - 1) * 9;
    $end = min($start + 9, count($rs->Details));
    for ($i = $start; $i < $end; $i++) {
        $row = $rs->Details[$i];

        $subTotal += $row->Amount;
        $coaDetail = null;

        if ($rs->VoucherType->Id == 1 || $rs->VoucherType->Id == 3) {
            $coaDetail = $row->Credit->AccNo;

            if (!in_array($row->Debit->AccNo, $coaHeader)) {
                $coaHeader[] = $row->Debit->AccNo;
            }
        } elseif ($rs->VoucherType->Id == 2 || $rs->VoucherType->Id == 6) {
            $coaDetail = $row->Debit->AccNo;

            if (!in_array($row->Credit->AccNo, $coaHeader)) {
                $coaHeader[] = $row->Credit->AccNo;
            }
        }

        $pdf->RowData(array($coaDetail, $row->Department != null ? $row->Department->DeptCode : "", $row->Note, "Rp. " . number_format($row->Amount, 2,",",".")),
            5, null, 0, array("L", "L", "L", "R"));
    }

    $header = implode(",", $coaHeader);

    $pdf->SetX($x);
    $pdf->SetY($y);
    $pdf->Cell($sumWidths, 5, $header, 0, 0, "C");

    $pdf->SetFont("Tahoma", "", 10);
    $pdf->SetY($y3);
    $pdf->Cell($widths[0] + $widths[1] + $widths[2], 5, 'SUB TOTAL', 'LTR', 0, 'R');
    $pdf->Cell($widths[3], 5, "Rp. " . number_format($subTotal, 2,",","."), 'BTR', 0, 'R');
    $pdf->Ln();

    if ($subPage == $totalPage) {
        $grandTotal = 0;
        foreach ($rs->Details as $detail) {
            $grandTotal += $detail->Amount;
        }

        $pdf->Cell($widths[0] + $widths[1] + $widths[2], 5, 'GRAND TOTAL', 'LTR', 0, 'R');
        $pdf->Cell($widths[3], 5, "Rp. " . number_format($grandTotal, 2,",","."), 'BTR', 0, 'R');
        $pdf->Ln();

        $pdf->SetXY($x1, $y1);
        $pdf->SetFont("Tahoma", "", 10);
        $pdf->MultiCell(90, 5, "#" . terbilang($grandTotal) . " #");

        $pdf->SetY($y3);
        $pdf->Ln(10);
    }

	$cellWidth1 = floor($sumWidths / 5);
	$cellWidth2 = $sumWidths - (4 * $cellWidth1);
    //$pdf->SetY(100 + $coordinat);
    $pdf->SetFont("Tahoma", "", 10);
    $pdf->Cell($cellWidth1, 5, 'Dibayar', 'LRBT', 0, 'C');
    $pdf->Cell($cellWidth1, 5, 'Diperiksa', 'BTR', 0, 'C');
    $pdf->Cell($cellWidth1, 5, 'Dibukukan', 'BTR', 0, 'C');
    $pdf->Cell($cellWidth1, 5, 'Disetujui', 'BTR', 0, 'C');
    $pdf->Cell($cellWidth2, 5, 'Diterima', 'BTR', 0, 'C');
    $pdf->Ln();
    $pdf->Cell($cellWidth1, 15, '', 'LRBT', 0, 'C');
    $pdf->Cell($cellWidth1, 15, '', 'BTR', 0, 'C');
    $pdf->Cell($cellWidth1, 15, '', 'BTR', 0, 'C');
    $pdf->Cell($cellWidth1, 15, '', 'BTR', 0, 'C');
    $pdf->Cell($cellWidth2, 15, '', 'BTR', 0, 'C');
    $pdf->Ln();

    $pdf->SetY($pdf->GetY() - 5);
    $pdf->Cell($cellWidth1, 5, '(___________)', 0, 0, 'C');
    $pdf->Cell($cellWidth1, 5, '(___________)', 0, 0, 'C');
    $pdf->Cell($cellWidth1, 5, '(___________)', 0, 0, 'C');
    $pdf->Cell($cellWidth1, 5, '(___________)', 0, 0, 'C');
    $pdf->Cell($cellWidth2, 5, '(___________)', 0, 0, 'C');
    $pdf->Ln();
    if ($totalPage > 1) {
        $pdf->SetFont("Tahoma", "", 8);
        $pdf->Cell(-100, 5, sprintf("%s Halaman: %s dari %d", $rs->DocumentNo, $subPage, $totalPage), "", 0, "R");
    }
}

