<?php
switch ($output) {
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class AccountingJournalRecapPdf extends TabularPdf {
			private $company;
			private $monthName;
			private $year;
			private $subTitle;
            /** @var Project */
            private $project;

			public function SetHeaderData(Company $company, $monthName, $year, $subTitle, Project $project = null) {
				$this->company = $company;
				$this->monthName = $monthName;
				$this->year = $year;
				$this->subTitle = $subTitle;
                $this->project = $project;
			}

			public function Header() {
				if($this->project == null){
                    $this->SetFont("Arial","B",18);
                    $this->Cell(400, 7, $this->company->CompanyName);
                }else{
                    $this->SetFont("Arial","B",16);
                    $this->Cell(400, 7, $this->company->CompanyName." (".$this->project->ProjectCd." - ".$this->project->ProjectName.")");
                }
				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 7, "Periode : ", 0, 0, "R");
				$this->Cell(40, 7, $this->monthName . " " . $this->year);
				$this->Ln();

				$this->SetFont("Arial","",11);
				$this->Cell(400, 5, $this->subTitle);
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

// EoF: recap.php