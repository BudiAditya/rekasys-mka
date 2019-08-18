<?php
class StockLocationController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;
    private $userProjectId;

	protected function Initialize() {
		require_once(MODEL . "common/stock_location.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
        $this->userProjectId = $this->persistence->LoadState("project_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.bin_code", "display" => "Kode Bind", "width" => 100);
		$settings["columns"][] = array("name" => "a.loc_name", "display" => "Lokasi", "width" => 200);
		$settings["columns"][] = array("name" => "a.description", "display" => "Keterangan", "width" => 400);

		$settings["filters"][] = array("name" => "a.loc_name", "display" => "Lokasi");
		$settings["filters"][] = array("name" => "a.bin_code", "display" => "Kode");
        $settings["filters"][] = array("name" => "a.description", "display" => "Keterangan");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Lokasi Stock";
			if($acl->CheckUserAccess("stocklocation", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.stocklocation/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("stocklocation", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.stocklocation/edit/%s", "Class" => "bt_edit", "ReqId" => 1, "Confirm" => "");
			}
			if($acl->CheckUserAccess("stocklocation", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.stocklocation/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_stock_location AS a";
            $settings["where"] = "a.project_id = ".$this->userProjectId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		$stloc = new StockLocation();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $stloc->ProjectId = $this->userProjectId;
			$stloc->BinCode = $this->GetPostValue("BinCode");
			$stloc->LocName = $this->GetPostValue("LocName");
            $stloc->Description = $this->GetPostValue("Description");

			if ($this->DoInsert($stloc)) {
                $this->persistence->SaveState("info", sprintf("Data Lokasi Stock: '%s' Dengan Kode: %s telah berhasil disimpan.", $stloc->LocName, $stloc->BinCode));
                redirect_url("common.stocklocation");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $stloc->BinCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		$this->Set("stocklocation", $stloc);
	}

	private function DoInsert(StockLocation $stloc) {
		if ($stloc->BinCode == "") {
			$this->Set("error", "Kode Lokasi Stock masih kosong");
			return false;
		}

		if ($stloc->LocName == "") {
			$this->Set("error", "Nama Lokasi Stock masih kosong");
			return false;
		}

		if ($stloc->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {

		$stloc = new StockLocation();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$stloc->Id = $id;
			$stloc->BinCode = $this->GetPostValue("BinCode");
			$stloc->LocName = $this->GetPostValue("LocName");
            $stloc->Description = $this->GetPostValue("Description");
            $stloc->ProjectId = $this->userProjectId;
			if ($this->DoUpdate($stloc)) {
				$this->persistence->SaveState("info", sprintf("Data Lokasi Stock: '%s' Dengan Kode: %s telah berhasil diupdate.", $stloc->LocName, $stloc->BinCode));
                redirect_url("common.stocklocation");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $stloc->BinCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data lokasi sebelum melakukan edit data !");
                redirect_url("common.stocklocation");
		    }
			$stloc = $stloc->FindById($id);
			if ($stloc == null) {
				$this->persistence->SaveState("error", "Data Lokasi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.stocklocation");
			}
		}
		$this->Set("stocklocation", $stloc);
	}

	private function DoUpdate(StockLocation $stloc) {
		if ($stloc->BinCode == "") {
			$this->Set("error", "Kode lokasi masih kosong");
			return false;
		}

		if ($stloc->LocName == "") {
			$this->Set("error", "Nama lokasi masih kosong");
			return false;
		}

		if ($stloc->Update($stloc->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Lokasi Stock sebelum melakukan hapus data !");
			redirect_url("common.stocklocation");
		}

		$stloc = new StockLocation();
		$stloc = $stloc->FindById($id);
		if ($stloc == null) {
			$this->persistence->SaveState("error", "Data Lokasi Stock yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.stocklocation");
		}

		if ($stloc->Delete($stloc->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Lokasi Stock: '%s' Dengan Kode: %s telah berhasil dihapus.", $stloc->LocName, $stloc->BinCode));
            redirect_url("common.stocklocation");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Lokasi Stock: '%s'. Message: %s", $stloc->LocName, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.stocklocation");
	}
}
