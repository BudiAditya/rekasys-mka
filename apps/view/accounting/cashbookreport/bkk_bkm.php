<?php
switch ($output) {
    case "xls":
        require_once(LIBRARY . "PHPExcel.php");

        include("bkk_bkm.excel.php");
        break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class BkkBkmReportPdf extends TabularPdf {
			private $company;
			private $account;
			private $period;

			public function SetHeaderData(Company $company, Coa $account, $period) {
				$this->company = $company;
				$this->account = $account;
				$this->period = $period;
			}

			public function Header() {
				$this->SetFont("Arial","B",18);
				$this->Cell(400, 7, $this->company->CompanyName);
				$this->Ln();
				$this->SetFont("Arial","B",11);
				$this->Cell(400, 7, "Report BKK dan BKM");
				$this->Ln();

				$this->SetFont("Arial","",11);
				$this->Cell(30, 5, "Kode Perkiraan");
				$this->Cell(30, 5, ": " . $this->account->AccNo);
				$this->SetX(-83, true);
				$this->Cell(30, 5, "Periode : ", 0, 0, "R");
				$this->Cell(40, 5, $this->period);
				$this->Ln();

				$this->Cell(30, 5, "Nama Perkiraan");
				$this->Cell(30, 5, ": " . $this->account->AccName);
				$this->SetX(-83, true);
				$this->Cell(30, 5, "Lembar : ", 0, 0, "R");
				$this->Cell(40, 5, $this->PageNo() . " dari {nb}");
				$this->Ln(10);
			}
		}

		include("bkk_bkm.pdf.php");
		break;
	default:
		include("bkk_bkm.web.php");
		break;
}



// End of file: bkk_bkm.php
