<?php
class UnitBrandController extends AppController {
	private  $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/unitbrand.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "a.brand_code", "display" => "Code", "width" => 50);
		$settings["columns"][] = array("name" => "a.brand_name", "display" => "Brand Name", "width" => 250);

		$settings["filters"][] = array("name" => "a.brand_code", "display" => "Code");
		$settings["filters"][] = array("name" => "a.brand_name", "display" => "Brand Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Unit Brand";
			if($acl->CheckUserAccess("unitbrand", "add", "master")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.unitbrand/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("unitbrand", "edit", "master")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.unitbrand/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unitbrand sebelum melakukan proses edit data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if($acl->CheckUserAccess("unitbrand", "delete", "master")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.unitbrand/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih unitbrand sebelum melakukan proses penghapusan data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?");
			}
			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_unit_brand AS a";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/company.php");
        $unitbrand = new UnitBrand();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$unitbrand->EntityId = $this->userCompanyId;
            $unitbrand->BrandCode = $this->GetPostValue("BrandCode");
			$unitbrand->BrandName = $this->GetPostValue("BrandName");

			if ($this->DoInsert($unitbrand)) {
                $this->persistence->SaveState("info", sprintf("Data UnitBrand: '%s' Dengan Kode: %s telah berhasil disimpan.", $unitbrand->BrandName, $unitbrand->BrandCode));
                redirect_url("master.unitbrand");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unitbrand->BrandCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		$this->Set("unitbrand", $unitbrand);
        $this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(UnitBrand $unitbrand) {
		if ($unitbrand->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
        if ($unitbrand->BrandCode == "") {
			$this->Set("error", "Kode unitbrand masih kosong");
			return false;
		}
		if ($unitbrand->BrandName == "") {
			$this->Set("error", "Nama unitbrand masih kosong");
			return false;
		}
		if ($unitbrand->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        $loader = null;
        $unitbrand = new UnitBrand();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $unitbrand->Id = $id;
			$unitbrand->EntityId = $this->userCompanyId;
            $unitbrand->BrandCode = $this->GetPostValue("BrandCode");
            $unitbrand->BrandName = $this->GetPostValue("BrandName");
            if ($this->DoUpdate($unitbrand)) {
				$this->persistence->SaveState("info", sprintf("Data UnitBrand: '%s' Dengan Kode: %s telah berhasil diupdate.", $unitbrand->BrandName, $unitbrand->BrandCode));
                redirect_url("master.unitbrand");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unitbrand->BrandCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
                redirect_url("master.unitbrand");
		    }
			$unitbrand = $unitbrand->FindById($id);
			if ($unitbrand == null) {
				$this->persistence->SaveState("error", "Data UnitBrand yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.unitbrand");
			}
		}
        // untuk kirim variable ke view
        $this->Set("company", new Company($this->userCompanyId));
		$this->Set("unitbrand", $unitbrand);
	}

	private function DoUpdate(UnitBrand $unitbrand) {
		if ($unitbrand->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

        if ($unitbrand->BrandCode == "") {
			$this->Set("error", "Kode unitbrand masih kosong");
			return false;
		}

        if ($unitbrand->BrandName == "") {
			$this->Set("error", "Nama unitbrand masih kosong");
			return false;
		}

		if ($unitbrand->Update($unitbrand->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data unitbrand sebelum melakukan hapus data !");
			redirect_url("master.unitbrand");
		}

		$unitbrand = new UnitBrand();
		$unitbrand = $unitbrand->FindById($id);
        if ($unitbrand == null) {
			$this->persistence->SaveState("error", "Data unitbrand yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.unitbrand");
		}

		if ($unitbrand->Delete($unitbrand->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data UnitBrand: '%s' Dengan Kode: %s telah berhasil dihapus.", $unitbrand->BrandName, $unitbrand->BrandCode));
            redirect_url("master.unitbrand");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data unitbrand: '%s'. Message: %s", $unitbrand->BrandName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.unitbrand");
	}
}
