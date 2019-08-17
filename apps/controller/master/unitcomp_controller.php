<?php
class UnitCompController extends AppController {
	private  $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/unitcomp.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "a.comp_code", "display" => "Code", "width" => 50);
		$settings["columns"][] = array("name" => "a.comp_name", "display" => "Component", "width" => 250);
        $settings["columns"][] = array("name" => "a.comp_model", "display" => "Model", "width" => 150);

		$settings["filters"][] = array("name" => "a.comp_code", "display" => "Code");
		$settings["filters"][] = array("name" => "a.comp_name", "display" => "Component");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Unit & Equipment Component";
			
			if($acl->CheckUserAccess("unitcomp", "add", "master")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.unitcomp/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("unitcomp", "edit", "master")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.unitcomp/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unitcomp sebelum melakukan proses edit data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if($acl->CheckUserAccess("unitcomp", "delete", "master")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.unitcomp/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unitcomp sebelum melakukan proses penghapusan data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?");
			}
			
			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_unit_component AS a";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/company.php");
        $unitcomp = new UnitComp();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$unitcomp->EntityId = $this->userCompanyId;
            $unitcomp->CompCode = $this->GetPostValue("CompCode");
			$unitcomp->CompName = $this->GetPostValue("CompName");
            $unitcomp->CompModel = $this->GetPostValue("CompModel");
			if ($this->DoInsert($unitcomp)) {
                $this->persistence->SaveState("info", sprintf("Data UnitComp: '%s' Dengan Kode: %s telah berhasil disimpan.", $unitcomp->CompName, $unitcomp->CompCode));
                redirect_url("master.unitcomp");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unitcomp->CompCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		$this->Set("unitcomp", $unitcomp);
        $this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(UnitComp $unitcomp) {
		if ($unitcomp->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
        if ($unitcomp->CompCode == "") {
			$this->Set("error", "Kode unitcomp masih kosong");
			return false;
		}
		if ($unitcomp->CompName == "") {
			$this->Set("error", "Nama unitcomp masih kosong");
			return false;
		}
		if ($unitcomp->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        $loader = null;
        $unitcomp = new UnitComp();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $unitcomp->Id = $id;
			$unitcomp->EntityId = $this->userCompanyId;
            $unitcomp->CompCode = $this->GetPostValue("CompCode");
            $unitcomp->CompName = $this->GetPostValue("CompName");
            $unitcomp->CompModel = $this->GetPostValue("CompModel");
            if ($this->DoUpdate($unitcomp)) {
				$this->persistence->SaveState("info", sprintf("Data UnitComp: '%s' Dengan Kode: %s telah berhasil diupdate.", $unitcomp->CompName, $unitcomp->CompCode));
                redirect_url("master.unitcomp");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unitcomp->CompCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
                redirect_url("master.unitcomp");
		    }
			$unitcomp = $unitcomp->FindById($id);
			if ($unitcomp == null) {
				$this->persistence->SaveState("error", "Data UnitComp yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.unitcomp");
			}
		}
        // untuk kirim variable ke view
        $this->Set("company", new Company($this->userCompanyId));
		$this->Set("unitcomp", $unitcomp);
	}

	private function DoUpdate(UnitComp $unitcomp) {
		if ($unitcomp->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

        if ($unitcomp->CompCode == "") {
			$this->Set("error", "Kode unitcomp masih kosong");
			return false;
		}

        if ($unitcomp->CompName == "") {
			$this->Set("error", "Nama unitcomp masih kosong");
			return false;
		}

		if ($unitcomp->Update($unitcomp->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data unitcomp sebelum melakukan hapus data !");
			redirect_url("master.unitcomp");
		}

		$unitcomp = new UnitComp();
		$unitcomp = $unitcomp->FindById($id);
        if ($unitcomp == null) {
			$this->persistence->SaveState("error", "Data unitcomp yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.unitcomp");
		}

		if ($unitcomp->Delete($unitcomp->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data UnitComp: '%s' Dengan Kode: %s telah berhasil dihapus.", $unitcomp->CompName, $unitcomp->CompCode));
            redirect_url("master.unitcomp");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data unitcomp: '%s'. Message: %s", $unitcomp->CompName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.unitcomp");
	}
}
