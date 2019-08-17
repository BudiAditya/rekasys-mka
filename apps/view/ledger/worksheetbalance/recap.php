<?php
switch ($output) {
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class WorksheetBalanceRecapReportPdf extends TabularPdf {
			/** @var Company */
			private $company;
			private $monthName;
			private $year;
			/** @var Coa */
			private $account;

			public function SetHeaderData(Company $company, $monthName, $year, Coa $account) {
				$this->company = $company;
				$this->monthName = $monthName;
				$this->year = $year;
				$this->account = $account;
			}

			public function Header() {
				$this->SetFont("Arial","",18);
				$this->Cell(400, 7, $this->company->CompanyName);
				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 7, "Periode : ", 0, 0, "R");
				$this->Cell(40, 7, $this->monthName . " " . $this->year);
				$this->Ln();

				$this->SetFont("Arial","",11);
				$this->Cell(400, 5, sprintf("Trial Balance %s (%s)", $this->account->AccName, $this->account->AccNo));
				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 5, "Lembar : ", 0, 0, "R");
				$this->Cell(40, 5, $this->PageNo() . " dari {nb}");
				$this->Ln(10);
			}
		}
		include("recap.pdf.php");
		break;
    case "xls":
        require_once(LIBRARY . "PHPExcel.php");
        include("recap.excel.php");
        break;
	default:
		include("recap.web.php");
		break;
}

// End of File: recap_in.php
