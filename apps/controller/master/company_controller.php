<?php
class CompanyController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/company.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");

		//TO-DO: Apakah controller ini hanya boleh diakses oleh Corporate Level ? Bila Diakses non-CORP datanya cuma ada 1 LOLZ
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.entity_id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Code", "width" => 50);
		$settings["columns"][] = array("name" => "a.company_name", "display" => "Company Name", "width" => 200);
		$settings["columns"][] = array("name" => "a.address", "display" => "Address", "width" => 250);
		$settings["columns"][] = array("name" => "a.telephone", "display" => "Telephone", "width" => 100);
		$settings["columns"][] = array("name" => "a.facsimile", "display" => "Facsimile", "width" => 100);
        $settings["columns"][] = array("name" => "a.email", "display" => "Email", "width" => 100);
		$settings["columns"][] = array("name" => "a.npwp", "display" => "NPWP", "width" => 100);
		$settings["columns"][] = array("name" => "a.personincharge", "display" => "P I C", "width" => 150);
		$settings["columns"][] = array("name" => "a.pic_status", "display" => "Position", "width" => 100);

		$settings["filters"][] = array("name" => "a.entity_cd", "display" => "Code");
		$settings["filters"][] = array("name" => "a.company_name", "display" => "Company Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Company Information";

			if ($acl->CheckUserAccess("master.company", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.company/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.company", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.company/edit/%s", "Class" => "bt_edit", "ReqId" => 1, "Confirm" => "");
			}
            if ($acl->CheckUserAccess("master.company", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "master.company/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
            }
			if ($acl->CheckUserAccess("master.company", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.company/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

		} else {
			$settings["from"] = "cm_company AS a";
			//$settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
	    require_once (MODEL . "master/coa.php");
        require_once (MODEL . "master/project.php");
		$company = new Company();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$company->EntityCd = $this->GetPostValue("EntityCd");
			$company->CompanyName = $this->GetPostValue("CompanyName");
			$company->Address = $this->GetPostValue("Address");
			$company->City = $this->GetPostValue("City");
			$company->Province = $this->GetPostValue("Province");
			$company->Npwp = trim($this->GetPostValue("Npwp"));
			$company->Telephone = trim($this->GetPostValue("Telephone"));
			$company->Facsimile = trim($this->GetPostValue("Facsimile"));
			$company->PersonInCharge = trim($this->GetPostValue("PersonInCharge"));
			$company->PicStatus = trim($this->GetPostValue("PicStatus"));
            $company->PpnOutAccId = trim($this->GetPostValue("PpnOutAccId"));
            $company->PpnInAccId = trim($this->GetPostValue("PpnInAccId"));
            $company->PpnTrxAccId = trim($this->GetPostValue("PpnTrxAccId"));
            $company->Email = trim($this->GetPostValue("Email"));
            $company->Website = trim($this->GetPostValue("Website"));
            $company->DefProjectId = trim($this->GetPostValue("DefProjectId"));
            $company->GeneralCashAccId = trim($this->GetPostValue("GeneralCashAccId"));
            $company->StartDate = $this->GetPostValue("StartDate");
			if ($this->DoInsert($company)) {
				$this->persistence->SaveState("info", sprintf("Data Perusahaan: '%s' Dengan Kode: %s telah berhasil disimpan.", $company->CompanyName, $company->EntityCd));
				redirect_url("master.company");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $company->EntityCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
        $loader = new Coa();
		$coas = $loader->LoadByLevel($this->userCompanyId,3);
		$loader = new Project();
		$projects = $loader->LoadByEntityId($this->userCompanyId);
        $this->Set("accounts", $coas);
        $this->Set("projects", $projects);
		$this->Set("company", $company);
	}

	private function DoInsert(Company $company) {
		if ($company->EntityCd == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

		if ($company->CompanyName == "") {
			$this->Set("error", "Nama perusahaan masih kosong");
			return false;
		}

		if ($company->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once (MODEL . "master/coa.php");
        require_once (MODEL . "master/project.php");
		$company = new Company();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$company->EntityId = $this->GetPostValue("EntityId");
			$company->EntityCd = $this->GetPostValue("EntityCd");
			$company->CompanyName = $this->GetPostValue("CompanyName");
			$company->Address = $this->GetPostValue("Address");
			$company->City = $this->GetPostValue("City");
			$company->Province = $this->GetPostValue("Province");
			$company->Npwp = trim($this->GetPostValue("Npwp"));
			$company->Telephone = trim($this->GetPostValue("Telephone"));
			$company->Facsimile = trim($this->GetPostValue("Facsimile"));
			$company->PersonInCharge = trim($this->GetPostValue("PersonInCharge"));
			$company->PicStatus = trim($this->GetPostValue("PicStatus"));
            $company->PpnOutAccId = trim($this->GetPostValue("PpnOutAccId"));
            $company->PpnInAccId = trim($this->GetPostValue("PpnInAccId"));
            $company->PpnTrxAccId = trim($this->GetPostValue("PpnTrxAccId"));
            $company->Email = trim($this->GetPostValue("Email"));
            $company->Website = trim($this->GetPostValue("Website"));
            $company->DefProjectId = trim($this->GetPostValue("DefProjectId"));
            $company->GeneralCashAccId = trim($this->GetPostValue("GeneralCashAccId"));
            $company->StartDate = $this->GetPostValue("StartDate");

			if ($this->DoUpdate($company)) {
				$this->persistence->SaveState("info", sprintf("Data Perusahaan: '%s' Dengan Kode: %s telah berhasil diupdate.", $company->CompanyName, $company->EntityCd));
				redirect_url("master.company");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $company->EntityCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
				redirect_url("master.company");
			}
			$company = $company->FindById($id);
			if ($company == null) {
				$this->persistence->SaveState("error", "Data Perusahaan yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.company");
			}
		}

        $loader = new Coa();
        $coas = $loader->LoadByLevel($this->userCompanyId,3);
        $loader = new Project();
        $projects = $loader->LoadByEntityId($this->userCompanyId);
        $this->Set("accounts", $coas);
        $this->Set("projects", $projects);
        $this->Set("company", $company);
	}

	private function DoUpdate(Company $company) {
		if ($company->EntityCd == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

		if ($company->CompanyName == "") {
			$this->Set("error", "Nama perusahaan masih kosong");
			return false;
		}

		if ($company->Update($company->EntityId) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan hapus data !");
			redirect_url("master.company");
		}

		$company = new company();
		$company = $company->FindById($id);
		if ($company == null) {
			$this->persistence->SaveState("error", "Data perusahaan yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.company");
		}

		if ($company->Delete($company->EntityId) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Perusahaan: '%s' Dengan Kode: %s telah berhasil dihapus.", $company->CompanyName, $company->EntityCd));
			redirect_url("master.company");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data perusahaan: '%s'. Message: %s", $company->CompanyName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.company");
	}

    public function View($id = null) {
        require_once (MODEL . "master/coa.php");
        require_once (MODEL . "master/project.php");
        $company = new Company();

        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
            redirect_url("master.company");
        }
        $company = $company->FindById($id);
        if ($company == null) {
            $this->persistence->SaveState("error", "Data Perusahaan yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("master.company");
        }
        $loader = new Coa();
        $coas = $loader->LoadByLevel($this->userCompanyId,3);
        $loader = new Project();
        $projects = $loader->LoadByEntityId($this->userCompanyId);
        $this->Set("accounts", $coas);
        $this->Set("projects", $projects);
        $this->Set("company", $company);
    }
}
