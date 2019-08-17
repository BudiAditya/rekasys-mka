<?php

function CreateSpecial(TabularPdf $pdf, Voucher $rs, $counter, $subPage, $totalPage) {
    if ($counter % 2 == 0) {
        $pdf->AddPage();
    } else {
        $pdf->SetY(140);
    }

    $pdf->SetFont("Arial", "", 14);
    if (!in_array($rs->EntityId, array(3, 4, 5))) {
        $pdf->Cell(100, 5, $rs->Company->CompanyName, 0, 0, "L");
    } else {
        $pdf->Cell(100, 5, "PT. MEGASURYA NUSALESTARI", 0, 0, "L");
    }
    $pdf->Ln(7);

    $pdf->SetFont("Arial", "B", 14);
    $pdf->Cell(50);
    $pdf->Cell(80, 5, $rs->VoucherType->VoucherDesc, 0, 0, "C");
    $pdf->SetFont("Arial", "", 10);
    $pdf->Cell(15, 5, "No.Bukti", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "C");
    $pdf->Cell(50, 5, $rs->DocumentNo, 0, 0, "L");
    $pdf->Ln();

    $pdf->Cell(130);
    $pdf->Cell(15, 5, "Tanggal", 0, 0, "L");
    $pdf->Cell(3, 5, ":", 0, 0, "C");
    $pdf->Cell(50, 5, long_date(date("Y-m-d", $rs->Date)), 0, 0, "L");
    $pdf->Ln(10);

    $pdf->SetFont("Arial", "", 10);
    $pdf->Cell(30, 5, 'KODE.', 'LRBT', 0, 'C');
    $pdf->Cell(20, 5, 'DEPT', 'BTR', 0, 'C');
    $pdf->Cell(106, 5, 'NAMA PERKIRAAN', 'BTR', 0, 'C');
    $pdf->Cell(39, 5, 'JUMLAH', 'BTR', 0, 'C');
    $pdf->Ln();

    // first detail column
    $pdf->Line(10, $pdf->GetY(), 10, (39 + $pdf->GetY()));
    $pdf->Line(40, $pdf->GetY(), 40, (39 + $pdf->GetY()));
    $pdf->Line(60, $pdf->GetY(), 60, (39 + $pdf->GetY()));
    $pdf->Line(166, $pdf->GetY(), 166, (39 + $pdf->GetY()));
    $pdf->Line(205, $pdf->GetY(), 205, (39 + $pdf->GetY()));
    $pdf->Line(10, (39 + $pdf->GetY()), 205, (39 + $pdf->GetY()));

    $y = 39 + $pdf->GetY();

    $start = ($subPage - 1) * 7;
    $end = min($start + 7, count($rs->Details));
    for ($i = $start; $i < $end; $i++) {
        $row = $rs->Details[$i];

        $pdf->RowData(array($row->Debit->AccNo, $row->Department != null ? $row->Department->DeptCd : "", $row->Debit->AccName, "Rp. " . number_format($row->Amount, 2,",",".")),
            5, null, 0, array("L", "L", "L", "R"));
    }

    $pdf->SetY($y);

    // second detail column
    $pdf->Line(10, $y, 10, (39 + $y));
    $pdf->Line(40, $y, 40, (39 + $y));
    $pdf->Line(60, $y, 60, (39 + $y));
    $pdf->Line(166, $y, 166, (39 + $y));
    $pdf->Line(205, $y, 205, (39 + $y));
    $pdf->Line(10, (39 + $y), 205, (39 + $y));

    $y = 39 + $pdf->GetY();

    for ($i = $start; $i < $end; $i++) {
        $row = $rs->Details[$i];

        $pdf->RowData(array($row->Credit->AccNo, $row->Department != null ? $row->Department->DeptCd : "", $row->Credit->AccName, "Rp. " . number_format($row->Amount, 2,",",".")),
            5, null, 0, array("L", "L", "L", "R"));
    }


    $pdf->SetY($y);

    $pdf->SetFont("Arial", "", 10);
    $pdf->Cell(39, 5, 'Dibayar', 'LRBT', 0, 'C');
    $pdf->Cell(39, 5, 'Diperiksa', 'BTR', 0, 'C');
    $pdf->Cell(39, 5, 'Dibukukan', 'BTR', 0, 'C');
    $pdf->Cell(39, 5, 'Disetujui', 'BTR', 0, 'C');
    $pdf->Cell(39, 5, 'Diterima', 'BTR', 0, 'C');
    $pdf->Ln();
    $pdf->Cell(39, 15, '', 'LRBT', 0, 'C');
    $pdf->Cell(39, 15, '', 'BTR', 0, 'C');
    $pdf->Cell(39, 15, '', 'BTR', 0, 'C');
    $pdf->Cell(39, 15, '', 'BTR', 0, 'C');
    $pdf->Cell(39, 15, '', 'BTR', 0, 'C');
    $pdf->Ln();

    $pdf->SetY($pdf->GetY() - 5);
    $pdf->SetX(10);
    $pdf->Cell(39, 5, '(Nama Jelas)', 0, 0, 'C');
    $pdf->SetX(49);
    $pdf->Cell(39, 5, '(Nama Jelas)', 0, 0, 'C');
    $pdf->SetX(88);
    $pdf->Cell(39, 5, '(Nama Jelas)', 0, 0, 'C');
    $pdf->SetX(127);
    $pdf->Cell(39, 5, '(Nama Jelas)', 0, 0, 'C');
    $pdf->SetX(166);
    $pdf->Cell(39, 5, '(Nama Jelas)', 0, 0, 'C');
    $pdf->Ln();

}