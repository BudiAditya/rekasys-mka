<?php
switch ($output) {
    case "xls":
        require_once(LIBRARY . "PHPExcel.php");

        include("rpt_recap.excel.php");
        break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class CashFlowRecapReportPdf extends TabularPdf {
			private $company;
			private $monthName;
			private $year;

			public function SetHeaderData(Company $company, $monthName, $year) {
				$this->company = $company;
				$this->monthName = $monthName;
				$this->year = $year;
			}

			public function Header() {
				$this->SetFont("Arial","B",18);
				$this->Cell(400, 7, $this->company->CompanyName);
				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 7, "Periode : ", 0, 0, "R");
				$this->Cell(40, 7, $this->monthName . " " . $this->year);
				$this->Ln();

				$this->SetFont("Arial","",11);
				$this->Cell(400, 5, "Rekap Cash Flow Per Akun");
				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 5, "Lembar : ", 0, 0, "R");
				$this->Cell(40, 5, $this->PageNo() . " dari {nb}");
				$this->Ln(10);
			}
		}

		include("rpt_recap.pdf.php");
		break;
	default:
		include("rpt_recap.web.php");
		break;
}

// End of File: rpt_recap.php
