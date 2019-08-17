<?php
class UnitClassController extends AppController {
	private  $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/unitclass.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "a.class_code", "display" => "Code", "width" => 50);
		$settings["columns"][] = array("name" => "a.class_name", "display" => "Class Name", "width" => 250);

		$settings["filters"][] = array("name" => "a.class_code", "display" => "Code");
		$settings["filters"][] = array("name" => "a.class_name", "display" => "Class Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Unit Class";
			if($acl->CheckUserAccess("unitclass", "add", "master")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.unitclass/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("unitclass", "edit", "master")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.unitclass/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unitclass sebelum melakukan proses edit data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if($acl->CheckUserAccess("unitclass", "delete", "master")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.unitclass/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unitclass sebelum melakukan proses penghapusan data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?");
			}
			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_unit_class AS a";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/company.php");
        $unitclass = new UnitClass();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$unitclass->EntityId = $this->userCompanyId;
            $unitclass->ClassCode = $this->GetPostValue("ClassCode");
			$unitclass->ClassName = $this->GetPostValue("ClassName");

			if ($this->DoInsert($unitclass)) {
                $this->persistence->SaveState("info", sprintf("Data UnitClass: '%s' Dengan Kode: %s telah berhasil disimpan.", $unitclass->ClassName, $unitclass->ClassCode));
                redirect_url("master.unitclass");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unitclass->ClassCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		$this->Set("unitclass", $unitclass);
        $this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(UnitClass $unitclass) {
		if ($unitclass->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
        if ($unitclass->ClassCode == "") {
			$this->Set("error", "Kode unitclass masih kosong");
			return false;
		}
		if ($unitclass->ClassName == "") {
			$this->Set("error", "Nama unitclass masih kosong");
			return false;
		}
		if ($unitclass->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        $loader = null;
        $unitclass = new UnitClass();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $unitclass->Id = $id;
			$unitclass->EntityId = $this->userCompanyId;
            $unitclass->ClassCode = $this->GetPostValue("ClassCode");
            $unitclass->ClassName = $this->GetPostValue("ClassName");
            if ($this->DoUpdate($unitclass)) {
				$this->persistence->SaveState("info", sprintf("Data UnitClass: '%s' Dengan Kode: %s telah berhasil diupdate.", $unitclass->ClassName, $unitclass->ClassCode));
                redirect_url("master.unitclass");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unitclass->ClassCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
                redirect_url("master.unitclass");
		    }
			$unitclass = $unitclass->FindById($id);
			if ($unitclass == null) {
				$this->persistence->SaveState("error", "Data UnitClass yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.unitclass");
			}
		}
        // untuk kirim variable ke view
        $this->Set("company", new Company($this->userCompanyId));
		$this->Set("unitclass", $unitclass);
	}

	private function DoUpdate(UnitClass $unitclass) {
		if ($unitclass->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

        if ($unitclass->ClassCode == "") {
			$this->Set("error", "Kode unitclass masih kosong");
			return false;
		}

        if ($unitclass->ClassName == "") {
			$this->Set("error", "Nama unitclass masih kosong");
			return false;
		}

		if ($unitclass->Update($unitclass->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data unitclass sebelum melakukan hapus data !");
			redirect_url("master.unitclass");
		}

		$unitclass = new UnitClass();
		$unitclass = $unitclass->FindById($id);
        if ($unitclass == null) {
			$this->persistence->SaveState("error", "Data unitclass yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.unitclass");
		}

		if ($unitclass->Delete($unitclass->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data UnitClass: '%s' Dengan Kode: %s telah berhasil dihapus.", $unitclass->ClassName, $unitclass->ClassCode));
            redirect_url("master.unitclass");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data unitclass: '%s'. Message: %s", $unitclass->ClassName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.unitclass");
	}
}
