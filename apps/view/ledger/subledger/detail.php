<?php
switch ($output) {
	case "xls":
		require_once(LIBRARY . "PHPExcel.php");
		include("detail.excel.php");
		break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class BukuTambahanReportPdf extends TabularPdf {
			private $company;
			/** @var Coa */
			private $account;
			private $period;
			private $status;
            /** @var Project */
            private $project;

			public function SetHeaderData(Company $company, Coa $account, $period, Project $project = null, $status) {
				$this->company = $company;
				$this->account = $account;
				$this->period = $period;
				$this->status = $status;
                $this->project = $project;
			}

			public function Header() {
				$this->SetFont("Arial","B",14);
                if($this->project == null){
                    $this->Cell(400, 7, $this->company->CompanyName);
                }else{
                    $this->Cell(400, 7, $this->company->CompanyName." (Proyek: ".$this->project->ProjectCd." - ".$this->project->ProjectName.")");
                }
				$this->Ln();
				$this->SetFont("Arial","B",11);
				$this->Cell(400, 7, "Sub Ledger");
				$this->Ln();

				$this->SetFont("Arial","",11);
				$this->Cell(30, 5, "Akun Perkiraan");
				$this->Cell(30, 5, sprintf(": %s (%s)", $this->account->AccName, $this->account->AccNo));
				$this->SetX(-83, true);
				$this->Cell(30, 5, "Periode : ", 0, 0, "R");
				$this->Cell(40, 5, $this->period);
				$this->Ln();

				$this->Cell(30, 5, "Status");
				$this->Cell(30, 5, ": " . $this->status);
				$this->SetX(-83, true);
				$this->Cell(30, 5, "Lembar : ", 0, 0, "R");
				$this->Cell(40, 5, $this->PageNo() . " dari {nb}");
				$this->Ln(10);
			}
		}

		include("detail.pdf.php");
		break;
	default:
		include("detail.web.php");
		break;
}

// End of File: detail.php
