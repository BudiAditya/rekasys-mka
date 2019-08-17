<?php
function CreateXlsCommon(PHPExcel_Worksheet $sheet, Voucher $voucher, $counter, $subPage, $totalPage) {
	// #REGION - styling
	$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
	$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
	$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
	$idrFormat = array("numberformat" => array("code" => '_([$Rp-421]* #,##0.00_);_([$Rp-421]* (#,##0.00);_([$Rp-421]* "-"??_);_(@_)'));
	//var_dump(array_merge($center, $allBorders));exit();
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
	$noAccountRow = $row;
	$sheet->mergeCells("L$row:AO$row");
	$sheet->setCellValue("AP$row", "Tanggal");
	$sheet->setCellValue("AU$row", ":");
	$sheet->setCellValue("AV$row", $voucher->FormatDate());

	$row++;
	$sheet->getRowDimension($row)->setRowHeight(5);

	$row++;
	$sheet->setCellValue("A$row", "Dibayar Kepada");
	$sheet->setCellValue("J$row", ":");
	$sheet->setCellValue("AP$row", "Rekening Bank");
	$sheet->setCellValue("AY$row", ":");
	$sheet->getStyle("A$row:BF$row")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A$row:A" . ($row + 2))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AP$row:AP" . ($row + 2))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("BF$row:BF" . ($row + 2))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("A".($row+2).":BF".($row+2))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$row++;
	$terbilangRow = $row;
	$sheet->setCellValue("A$row", "Terbilang");
	$sheet->setCellValue("J$row", ":");
	$sheet->mergeCells("K$row:AN" . ($row + 1));
	$sheet->setCellValue("AP$row", "No. Chq/Giro/Trf");
	$sheet->setCellValue("AY$row", ":");

	$row += 2;
	$sheet->getRowDimension($row)->setRowHeight(5);

	$row++;
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
	$sheet->getStyle("A$row:A" . ($row + 9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("J$row:J" . ($row + 9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("O$row:O" . ($row + 9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("AU$row:AU" . ($row + 9))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$sheet->getStyle("BF$row:BF" . ($row + 9))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

	$signatureRow = $row + 10;	// Untuk bikin tanda tangan
	$subTotal = 0;
	$coaHeader = array();
	$start = ($subPage - 1) * 9;
	$end = min($start + 9, count($voucher->Details));
	for ($i = $start; $i < $end; $i++) {
		$row++;
		$voucherDetail = $voucher->Details[$i];

		$subTotal += $voucherDetail->Amount;
		if ($voucher->VoucherType->Id == 1 || $voucher->VoucherType->Id == 3) {
			// BJK (id=1) atau BKM (id=3)
			$coaNo = $voucherDetail->Credit->AccNo;

			if (!in_array($voucherDetail->Debit->AccNo, $coaHeader)) {
				$coaHeader[] = $voucherDetail->Debit->AccNo;
			}
		} else if ($voucher->VoucherType->Id == 2 || $voucher->VoucherType->Id == 6) {
			// BKK (id=2) atau BPK (id=6)
			$coaNo = $voucherDetail->Debit->AccNo;

			if (!in_array($voucherDetail->Credit->AccNo, $coaHeader)) {
				$coaHeader[] = $voucherDetail->Credit->AccNo;
			}
		} else {
			$coaNo = "Unknown";
		}

		// Tulis data
		$sheet->setCellValue("A$row", $coaNo);
		$sheet->setCellValue("J$row", $voucherDetail->Department != null ? $voucherDetail->Department->DeptCd : "");
		$sheet->setCellValue("O$row", $voucherDetail->Note);
		$sheet->setCellValue("AU$row", $voucherDetail->Amount);
		$sheet->mergeCells("AU$row:BF$row");
		$sheet->getStyle("AU$row")->applyFromArray(array_merge($right, $idrFormat));
	}

	// Header no akun
	$sheet->setCellValue("L$noAccountRow", implode(", ", $coaHeader));
	$sheet->getStyle("L$noAccountRow")->applyFromArray($center);

	$row = $signatureRow;
	$sheet->setCellValue("A$row", "SUB TOTAL");
	$sheet->mergeCells("A$row:AT$row");
	$sheet->getStyle("A$row:AT$row")->applyFromArray(array_merge($right, $allBorders));
	$sheet->setCellValue("AU$row",  $subTotal);
	$sheet->mergeCells("AU$row:BF$row");
	$sheet->getStyle("AU$row:BF$row")->applyFromArray(array_merge($right, $allBorders, $idrFormat));

	if ($subPage == $totalPage) {
		$row++;
		// Halaman terakhir mari kita tambah terbilang dan grand total
		$grandTotal = 0;
		foreach ($voucher->Details as $detail) {
			$grandTotal += $detail->Amount;
		}
		// Grand Total
		$sheet->setCellValue("A$row", "GRAND TOTAL");
		$sheet->mergeCells("A$row:AT$row");
		$sheet->getStyle("A$row:AT$row")->applyFromArray(array_merge($right, $allBorders));
		$sheet->setCellValue("AU$row", $grandTotal);
		$sheet->mergeCells("AU$row:BF$row");
		$sheet->getStyle("AU$row:BF$row")->applyFromArray(array_merge($right, $allBorders, $idrFormat));
		// Terbilang
		$sheet->setCellValue("K$terbilangRow", "# " . terbilang($grandTotal) . " #");
		$sheet->getStyle("K$terbilangRow")->getAlignment()->setWrapText(true);
	}

	// Border untuk tanda tangan
	$row++;
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


// EoF: print.xls.common.php