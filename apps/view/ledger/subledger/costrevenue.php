<?php
switch ($output) {
    case "xls":
        require_once(LIBRARY . "PHPExcel.php");

        include("costrevenue.excel.php");
        break;
	case "pdf":
		require_once(LIBRARY . "tabular_pdf.php");

		class CostRevenueReportPdf extends TabularPdf {
			/** @var Company */
			private $company;
			private $monthName;
			private $year;
			/** @var Coa */
			private $account;
            /** @var Project */
            private $project;
			private $status;

			public function SetHeaderData(Company $company, $monthName, $year, $parent_id, Project $project = null, $status) {
				$this->company = $company;
				$this->monthName = $monthName;
				$this->year = $year;
				$this->account = $parent_id;
                $this->project = $project;
				$this->status = $status;
			}

			public function Header() {
				$this->SetFont("Arial","",14);
                if($this->project == null){
                    $this->Cell(400, 7, $this->company->CompanyName);
                }else{
                    $this->Cell(400, 7, $this->company->CompanyName." (Proyek: ".$this->project->ProjectCd." - ".$this->project->ProjectName.")");
                }
				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 7, "Periode : ", 0, 0, "R");
				$this->Cell(40, 7, $this->monthName . " " . $this->year);
				$this->Ln();

				$this->SetFont("Arial","",11);
                if($this->account == "4"){
                   $this->Cell(400, 5, sprintf("LAPORAN PENDAPATAN (400.00.00) - (%s)", $this->status));
                }else{
                   $this->Cell(400, 5, sprintf("LAPORAN BIAYA-BIAYA (500.00.00) - (%s)", $this->status));
                }

				$this->SetFont("Arial","",11);
				$this->SetX(-70, true);
				$this->Cell(30, 5, "Lembar : ", 0, 0, "R");
				$this->Cell(40, 5, $this->PageNo() . " dari {nb}");
				$this->Ln(10);
			}
		}

		include("costrevenue.pdf.php");
		break;
	default:
		include("costrevenue.web.php");
		break;
}

// End of File: recap_in.php
