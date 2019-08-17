<?php
class UnitTypeController extends AppController {
	private  $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/unittype.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "a.type_code", "display" => "Code", "width" => 50);
        $settings["columns"][] = array("name" => "a.type_initial", "display" => "Initial", "width" => 50);
		$settings["columns"][] = array("name" => "a.type_desc", "display" => "Type Name", "width" => 250);

		$settings["filters"][] = array("name" => "a.type_code", "display" => "Code");
        $settings["filters"][] = array("name" => "a.type_initial", "display" => "Initial");
		$settings["filters"][] = array("name" => "a.type_desc", "display" => "Type Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Unit Type Master";
			if($acl->CheckUserAccess("unittype", "add", "master")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.unittype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("unittype", "edit", "master")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.unittype/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unittype sebelum melakukan proses edit data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if($acl->CheckUserAccess("unittype", "delete", "master")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.unittype/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unittype sebelum melakukan proses penghapusan data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?");
			}
			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_unit_type AS a";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/company.php");
        $unittype = new UnitType();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$unittype->EntityId = $this->userCompanyId;
            $unittype->TypeCode = $this->GetPostValue("TypeCode");
			$unittype->TypeDesc = $this->GetPostValue("TypeDesc");
            $unittype->TypeInitial = $this->GetPostValue("TypeInitial");

			if ($this->DoInsert($unittype)) {
                $this->persistence->SaveState("info", sprintf("Data UnitType: '%s' Dengan Kode: %s telah berhasil disimpan.", $unittype->TypeDesc, $unittype->TypeCode));
                redirect_url("master.unittype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unittype->TypeCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		$this->Set("unittype", $unittype);
        $this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(UnitType $unittype) {
		if ($unittype->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
        if ($unittype->TypeCode == "") {
			$this->Set("error", "Kode unittype masih kosong");
			return false;
		}
		if ($unittype->TypeDesc == "") {
			$this->Set("error", "Nama unittype masih kosong");
			return false;
		}
		if ($unittype->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        $loader = null;
        $unittype = new UnitType();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $unittype->Id = $id;
			$unittype->EntityId = $this->userCompanyId;
            $unittype->TypeCode = $this->GetPostValue("TypeCode");
            $unittype->TypeDesc = $this->GetPostValue("TypeDesc");
            $unittype->TypeInitial = $this->GetPostValue("TypeInitial");
            if ($this->DoUpdate($unittype)) {
				$this->persistence->SaveState("info", sprintf("Data UnitType: '%s' Dengan Kode: %s telah berhasil diupdate.", $unittype->TypeDesc, $unittype->TypeCode));
                redirect_url("master.unittype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unittype->TypeCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
                redirect_url("master.unittype");
		    }
			$unittype = $unittype->FindById($id);
			if ($unittype == null) {
				$this->persistence->SaveState("error", "Data UnitType yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.unittype");
			}
		}
        // untuk kirim variable ke view
        $this->Set("company", new Company($this->userCompanyId));
		$this->Set("unittype", $unittype);
	}

	private function DoUpdate(UnitType $unittype) {
		if ($unittype->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

        if ($unittype->TypeCode == "") {
			$this->Set("error", "Kode unittype masih kosong");
			return false;
		}

        if ($unittype->TypeDesc == "") {
			$this->Set("error", "Nama unittype masih kosong");
			return false;
		}

		if ($unittype->Update($unittype->Id) > -1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data unittype sebelum melakukan hapus data !");
			redirect_url("master.unittype");
		}

		$unittype = new UnitType();
		$unittype = $unittype->FindById($id);
        if ($unittype == null) {
			$this->persistence->SaveState("error", "Data unittype yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.unittype");
		}

		if ($unittype->Delete($unittype->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data UnitType: '%s' Dengan Kode: %s telah berhasil dihapus.", $unittype->TypeDesc, $unittype->TypeCode));
            redirect_url("master.unittype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data unittype: '%s'. Message: %s", $unittype->TypeDesc, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.unittype");
	}
}
