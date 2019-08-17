<?php
function CreateXlsSpecial(PHPExcel_Worksheet $sheet, Voucher $voucher, $counter, $subPage, $totalPage) {
	// #REGION - styling
	$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
	$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
	$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
	$idrFormat = array("numberformat" => array("code" => '_([$Rp-421]* #,##0.00_);_([$Rp-421]* (#,##0.00);_([$Rp-421]* "-"??_);_(@_)'));
	// #END REGION

	$row = ($counter * 29);
	$row++;

	if (!in_array($voucher->EntityId, array(3, 4, 5))) {
		$sheet->setCellValue("A$row", $voucher->Company->CompanyName);
	} else {
		$sheet->setCellValue("A$row", "PT. MEGASURYA NUSALESTARI");
	}

	$row++;
	$sheet->getRowDimension($row)->setRowHeight(5);

	$row++;
	$sheet->setCellValue("L$row", $voucher->VoucherType->VoucherDesc);
	$sheet->getStyle("L$row")->applyFromArray($center);
	$sheet->mergeCells("L$row:AO$row");
	$sheet->setCellValue("AP$row", "No. Bukti");
	$sheet->setCellValue("AU$row", ":");
	$sheet->setCellValue("AV$row", $voucher->DocumentNo);

	$row++;
	$sheet->mergeCells("L$row:AO$row");
	$sheet->setCellValue("AP$row", "Tanggal");
	$sheet->setCellValue("AU$row", ":");
	$sheet->setCellValue("AV$row", $voucher->FormatDate());

	$row++;
	$sheet->getRowDimension($row)->setRowHeight(5);

	$row++;
	$signatureRow = $row + 15;	// Untuk bikin tanda tangan
	$sheet->setCellValue("A$row", "KODE");
	$sheet->mergeCells("A$row:I$row");
	$sheet->getStyle("A$row")->applyFromArray($center);
	$sheet->setCellValue("J$row", "DEPT");
	$sheet->mergeCells("J$row:N$row");
	$sheet->getStyle("J$row")->applyFromArray($center);
	$sheet->setCellValue("O$row", "URAIAN");
	$sheet->mergeCells("O$row:AT$row");
	$sheet->getStyle("O$row")->applyFromArray($center);
	$sheet->setCellValue("AU$row", "JUMLAH");
	$sheet->mergeCells("AU$row:BF$row");
	$sheet->getStyle("AU$row")->applyFromArray($center);

	// Bikin garis untuk header dan kolom detail
	$sheet->getStyle("A$row:BF$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:BF$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:A" . ($row + 14))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("J$row:J" . ($row + 14))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("O$row:O" . ($row + 14))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AU$row:AU" . ($row + 14))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("BF$row:BF" . ($row + 14))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A".($row+7).":BF".($row+7))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A".($row+14).":BF".($row+14))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$start = ($subPage - 1) * 7;
	$end = min($start + 7, count($voucher->Details));
	for ($i = $start; $i < $end; $i++) {
		$row++;
		$voucherDetail = $voucher->Details[$i];

		$sheet->setCellValue("A$row", $voucherDetail->Debit->AccNo);
		$sheet->setCellValue("J$row", $voucherDetail->Department != null ? $voucherDetail->Department->DeptCd : "");
		$sheet->setCellValue("O$row", $voucherDetail->Debit->AccName);
		$sheet->setCellValue("AU$row", $voucherDetail->Amount);
		$sheet->mergeCells("AU$row:BF$row");
		$sheet->getStyle("AU$row")->applyFromArray(array_merge($right, $idrFormat));

		// Behubung ini excel koordinat bawahnya sudah pasti jadi tidak perlu loop 2x
		$oppositeRow = $row + 7;
		$sheet->setCellValue("A$oppositeRow", $voucherDetail->Credit->AccNo);
		$sheet->setCellValue("J$oppositeRow", $voucherDetail->Department != null ? $voucherDetail->Department->DeptCd : "");
		$sheet->setCellValue("O$oppositeRow", $voucherDetail->Credit->AccName);
		$sheet->setCellValue("AU$oppositeRow", $voucherDetail->Amount);
		$sheet->mergeCells("AU$oppositeRow:BF$oppositeRow");
		$sheet->getStyle("AU$oppositeRow")->applyFromArray(array_merge($right, $idrFormat));
	}

	$row = $signatureRow;
	$sheet->getStyle("A$row:BF$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:A" . ($row + 3))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("L$row:L" . ($row + 3))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("W$row:W" . ($row + 3))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AH$row:AH" . ($row + 3))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AS$row:AS" . ($row + 3))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("BF$row:BF" . ($row + 3))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$sheet->setCellValue("A$row", "Dibayar");
	$sheet->mergeCells("A$row:K$row");
	$sheet->getStyle("A$row")->applyFromArray($center);
	$sheet->setCellValue("L$row", "Diperiksa");
	$sheet->mergeCells("L$row:V$row");
	$sheet->getStyle("L$row")->applyFromArray($center);
	$sheet->setCellValue("W$row", "Dibukukan");
	$sheet->mergeCells("W$row:AG$row");
	$sheet->getStyle("W$row")->applyFromArray($center);
	$sheet->setCellValue("AH$row", "Disetujui");
	$sheet->mergeCells("AH$row:AR$row");
	$sheet->getStyle("AH$row")->applyFromArray($center);
	$sheet->setCellValue("AS$row", "Diterima");
	$sheet->mergeCells("AS$row:BF$row");
	$sheet->getStyle("AS$row")->applyFromArray($center);

	$row += 3;
	$sheet->setCellValue("A$row", "(Nama Jelas)");
	$sheet->mergeCells("A$row:K$row");
	$sheet->getStyle("A$row")->applyFromArray($center);
	$sheet->setCellValue("L$row", "(Nama Jelas)");
	$sheet->mergeCells("L$row:V$row");
	$sheet->getStyle("L$row")->applyFromArray($center);
	$sheet->setCellValue("W$row", "(Nama Jelas)");
	$sheet->mergeCells("W$row:AG$row");
	$sheet->getStyle("W$row")->applyFromArray($center);
	$sheet->setCellValue("AH$row", "(Nama Jelas)");
	$sheet->mergeCells("AH$row:AR$row");
	$sheet->getStyle("AH$row")->applyFromArray($center);
	$sheet->setCellValue("AS$row", "(Nama Jelas)");
	$sheet->mergeCells("AS$row:BF$row");
	$sheet->getStyle("AS$row")->applyFromArray($center);


	$sheet->getStyle("A$row:BF$row")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	if ($totalPage > 1) {
		$row++;
		$sheet->setCellValue("A$row", sprintf("%s Halaman: %s dari %d", $voucher->DocumentNo, $subPage, $totalPage));
		$sheet->mergeCells("A$row:BF$row");
		$sheet->getStyle("A$row")->applyFromArray(array_merge(
			$right,
			array("font" => array("size" => 8))
		));
	}

	// OK hack agar pas di print pada continous form
	$row++;
	$sheet->getRowDimension($row)->setRowHeight(10);
}
// EoF: print.xls.special.php