<?php
switch ($output) {
    case "xls":
        require_once(LIBRARY . "PHPExcel.php");

        include("rpt_out.excel.php");
        break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class CashRequestReportOutPdf extends TabularPdf {
			private $company;
			private $period;

			public function SetCompany(Company $company) {
				$this->company = $company;
			}
			public function SetPeriod($period) {
				$this->period = $period;
			}

			public function Header() {
				$this->SetFont("Arial","B",22);
				$this->Cell(400, 7, $this->company->CompanyName);
				$this->SetFont("Arial","",11);
				$this->Cell(-70, 7, "Periode: " . $this->period);
				$this->Ln();
				$this->SetFont("Arial","B",16);
				$this->Cell(400, 7, "JURNAL BUKTI CASH/BANK OUT");
				$this->SetFont("Arial","",11);
				$this->Cell(-70, 7, "Lembar: " . $this->PageNo() . " dari {nb}");
				$this->Ln(12);
			}
		}

		include("rpt_out.pdf.php");
		break;
	default:
		include("rpt_out.web.php");
		break;
}

// End of File: rpt_out.php
