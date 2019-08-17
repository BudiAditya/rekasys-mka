<?php

// Make sure loading correct file from library folder. Some PEAR installation have their own FPDF class
require_once(LIBRARY . "fpdf.php");
/**
 * Class ini berfungsi untuk membuat PDF yang memiliki format tabular yang support multi line pada masing-masing barisnya
 */
class TabularPdf extends FPDF {
	// Start tabular column definitions
	protected $headerTitles = array();
	protected $widths = array();
	protected $alignments = array();
	protected $borders = array();

	protected $totalColumns = -1;

	public function __construct($orientation='P', $unit='mm', $size='A4') {
		parent::xfpdf($orientation, $unit, $size);
	}

	/**
	 * Berfungsi untuk memberitahukan engine PDF judul-judul kolom beserta ukuran besar kolomnya.
	 * NOTE: Jika ada kolom yang besarnya = 0 bearti kolom tersebut akan menggunakan space yang tersisa dari kolom-kolom lainnya
	 * 		 Penggunaan kolom = 0 harus sesudah pengaturan kertas dan margin agar berjalan sesuai kehendak.
	 *
	 * @param array $headers
	 * @param array $widths
	 * @throws Exception
	 */
	public function SetColumns(array $headers, array $widths) {
		if (count($headers) != count($widths)) {
			throw new Exception("Columns header count != Columns widths count. Both size must be equal");
		}

		// New Feature: Jika ketemu width 0 maka dia akan secara otomatis mengambil semua lebar sisa kertas
		// NOTE: Hanya berlaku untuk kolom dengan nilai 0 yang pertama.
		for ($i = 0; $i < count($widths); $i++) {
			if ($widths[$i] == 0) {
				$temp = array_sum($widths);
				$widths[$i] = $this->w - $this->lMargin - $this->rMargin - $temp;
				if ($widths[$i] <= 0) {
					throw new Exception("Auto Detect Width Failed ! Paper width less than total remaining columns width ! Please change your paper size or orientation");
				}
				break;
			}
		}

		// Berhubung ga pass by reference maka harus proses terlebih dahulu baru simpan di variable
		$this->headerTitles = $headers;
		$this->widths = $widths;
		$this->totalColumns = count($headers);
	}

	/**
	 * Untuk mengambil ukuran kolom yang digunakan oleh table jika ada kolom yang bernilai 0 akan berubah karena akan menggunakan space yang available
	 *
	 * @return array
	 */
	public function GetWidths() {
		return $this->widths;
	}

	public function SetDefaultAlignments(array $alignments) {
		$this->alignments = $alignments;
	}

	public function SetDefaultBorders(array $borders) {
		$this->borders = $borders;
	}

	public function GetHeaderTitles() {
		return $this->headerTitles;
	}

	public function GetColumnWidths() {
		return $this->widths;
	}

	public function GetColumnAlignments() {
		return $this->alignments;
	}

	/**
	 * Write header row from SetColumns data
	 *
	 * @param $height
	 * @param array $borders
	 * @param null $ln
	 * @param array $alignments
	 * @param bool $fill
	 */
	public function RowHeader($height, array $borders = null, $ln = null, array $alignments = null, $fill = false) {
		if ($borders === null) {
			$borders = $this->borders;
		}
		if ($alignments === null) {
			$alignments = &$this->alignments;
		}
		for ($i = 0; $i < $this->totalColumns; $i++) {
			$border = isset($borders[$i]) ? $borders[$i] : "";
			$alignment = isset($alignments[$i]) ? $alignments[$i] : "L";
			$this->Cell($this->widths[$i], $height, $this->headerTitles[$i], $border, 0, $alignment, $fill);
		}
		$this->Ln($ln);
	}

	/**
	 * Write the given data into a single row.
	 * Borders and alignments will use default value if you does not specify a value
	 *
	 * @param array $data
	 * @param $height
	 * @param array $borders
	 * @param int $ln
	 * @param array $alignments
	 * @param bool $fill
	 * @throws Exception
	 */
	public function RowData(array $data, $height, array $borders = null, $ln = 0, array $alignments = null, $fill = false) {
		if (count($data) != $this->totalColumns) {
			throw new Exception(sprintf("Total data count != Columns header count (%d != %d). Both size must be equal", count($data), $this->totalColumns));
		}
		if ($borders === null) {
			$borders = $this->borders;
		}
		if ($alignments === null) {
			$alignments = &$this->alignments;
		}

		// Store counter for maximum number of row used by each column
		$maxRow = 1;
		for ($i = 0; $i < $this->totalColumns; $i++) {
			$maxRow = max($maxRow, $this->DetectRowsUsed($data[$i], $this->widths[$i]));
		}

		// OK we know that current row will occupy x row(s) and we should check against bottom margin
		$maxHeight = $maxRow * $height;
		if ($this->GetY() + $maxHeight > $this->PageBreakTrigger) {
			// O.o overflow....
			$w = array_sum($this->widths);

			// Bikin Garis tutup dahulu
			$x = $this->GetX();
			$y = $this->GetY();
			$this->Line($x, $y, $x + $w, $y);
			// Tambah halaman baru...
			$this->AddPage($this->CurOrientation);
			// Harus bikin garis paling atas lagi deh...
			$x = $this->GetX();
			$y = $this->GetY();
			$this->Line($x, $y, $x + $w, $y);
		}

		// Write data row...
		for ($i = 0; $i < $this->totalColumns; $i++) {
			$x = $this->GetX();
			$y = $this->GetY();
			$w = $this->widths[$i];
			$border = isset($borders[$i]) ? $borders[$i] : "";
			$alignment = isset($alignments[$i]) ? $alignments[$i] : "L";

			// Print Text
			$this->MultiCell($w, $height, $data[$i], "", $alignment, $fill);
			// MutliCell() will change cursor position... we must reset it
			$this->SetXY($x + $w, $y);

			// Hwee... buat bordernya ga bisa curang..... ga bisa pake yang dari MultiCell()... T_T
			if (strpos($border, "T") !== false) {
				$this->Line($x, $y, $x + $w, $y);
			}
			if (strpos($border, "R") !== false) {
				$this->Line($x + $w, $y, $x + $w, $y + $maxHeight);
			}
			if (strpos($border, "B") !== false) {
				$this->Line($x, $y + $maxHeight, $x + $w, $y + $maxHeight);
			}
			if (strpos($border, "L") !== false) {
				$this->Line($x, $y, $x, $y + $maxHeight);
			}
		}

		// Next Line
		$this->Ln($maxHeight + $ln);
	}

	/**
	 * Used to calculate the required row(s) for the given text with specified width
	 *
	 * @param $txt
	 * @param $width
	 * @return int
	 */
	public function DetectRowsUsed($txt, $width) {
		// Output text with automatic or explicit line breaks
		$charWidths = &$this->CurrentFont['cw'];
		if ($width == 0) {
			$width = $this->w - $this->rMargin - $this->x;
		}
		$maxTextWidth = ($width - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$text = str_replace("\r", '', $txt);
		$textLength = strlen($text);
		if ($textLength > 0 && $text[$textLength - 1] == "\n") {
			$textLength--;
		}

		$sep = -1;	// Posisisi spasi / separator
		$i = 0;		// Counter posisi check character
		$l = 0;		// Width yang sudah di occupy oleh karakter substring
		$nl = 1;	// Jumlah baris yang digunakan (By default 1)
		while ($i < $textLength) {
			// Get next character
			$c = $text[$i];
			if ($c == "\n") {
				$i++;
				$sep = -1;
				$l = 0;
				$nl++;
				continue;
			}

			if ($c == ' ') {
				$sep = $i;
			}

			$l += $charWidths[$c];
			if ($l > $maxTextWidth) {
				// Automatic line break
				if ($sep != -1) {
					$i = $sep + 1;
				}

				$sep = -1;
				$l = 0;
				$nl++;
			} else {
				$i++;
			}
		}

		return $nl;
	}

	// Extending this class using code from http://www.fpdf.org/en/script/script2.php
	public $angle = 0;

	function Rotate($angle, $x = -1, $y = -1) {
		if ($x == -1) {
			$x = $this->x;
		}
		if ($y == -1) {
			$y = $this->y;
		}
		if ($this->angle != 0) {
			$this->_out('Q');
		}
		$this->angle = $angle;
		if ($angle != 0) {
			$angle *= M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	function _endpage() {
		if ($this->angle != 0) {
			$this->angle = 0;
			$this->_out('Q');
		}
		parent::_endpage();
	}

}


// End of File: tabular_pdf.php
