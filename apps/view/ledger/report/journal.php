<?php
switch ($output) {
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class AccountingJournalPdf extends TabularPdf {
			private $company;
			private $period;
			private $subTitle;
            /** @var Project */
            private $project;

			public function SetCompany(Company $company, Project $project = null) {
				$this->company = $company;
                $this->project = $project;
			}
			public function SetPeriod($period) {
				$this->period = $period;
			}
			public function SetSubTitle($subTitle) {
				$this->subTitle = $subTitle;
			}

			public function Header() {
				if($this->project == null){
                    $this->SetFont("Arial","B",18);
                    $this->Cell(400, 8, $this->company->CompanyName);
                }else{
                    $this->SetFont("Arial","B",16);
                    $this->Cell(400, 8, $this->company->CompanyName." (".$this->project->ProjectCd." - ".$this->project->ProjectName.")");
                }
				$this->SetFont("Arial","",11);
				$this->Cell(-70, 8, "Periode: " . $this->period);
				$this->Ln();
				$this->SetFont("Arial","B",12);
				$this->Cell(400, 8, $this->subTitle);
				$this->SetFont("Arial","",11);
				$this->Cell(-70, 8, "Lembar: " . $this->PageNo() . " dari {nb}");
				$this->Ln(12);
			}
		}

		include("journal.pdf.php");
		break;
	case "xls":
		require_once(LIBRARY . "PHPExcel.php");
		include("journal.excel.php");
		break;
	default:
		include("journal.web.php");
		break;
}

// EoF: journal.php