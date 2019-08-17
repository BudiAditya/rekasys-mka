<?php
class UomController extends AppController {
	protected function Initialize() {
		require_once(MODEL . "common/uom_master.php");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.uom_cd", "display" => "Kode", "width" => 80);
		$settings["columns"][] = array("name" => "a.uom_desc", "display" => "Nama Satuan", "width" => 200);
		$settings["columns"][] = array("name" => "a.dimension", "display" => "Dimensi", "width" => 120);

		$settings["filters"][] = array("name" => "a.uom_cd", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.uom_desc", "display" => "Nama Satuan");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Daftar Satuan dan Ukuran";
			if($acl->CheckUserAccess("uom", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.uom/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("uom", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.uom/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("uom", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.uom/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_uomaster AS a";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		$uom = new UomMaster();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$uom->UomCd = $this->GetPostValue("UomCd");
			$uom->UomDesc = $this->GetPostValue("UomDesc");
            $uom->Dimension = $this->GetPostValue("Dimension");

			if ($this->DoInsert($uom)) {
                $this->persistence->SaveState("info", sprintf("Data satuan: '%s' Dengan Kode: %s telah berhasil disimpan.", $uom->UomDesc, $uom->UomCd));
                redirect_url("common.uom");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $uom->UomCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		$this->Set("uom", $uom);
	}

	private function DoInsert(UomMaster $uom) {
		if ($uom->UomCd == "") {
			$this->Set("error", "Kode satuan masih kosong");
			return false;
		}

		if ($uom->UomDesc == "") {
			$this->Set("error", "Nama satuan masih kosong");
			return false;
		}

		if ($uom->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {

		$uom = new UomMaster();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$uom->Id = $this->GetPostValue("Id");
			$uom->UomCd = $this->GetPostValue("UomCd");
			$uom->UomDesc = $this->GetPostValue("UomDesc");
            $uom->Dimension = $this->GetPostValue("Dimension");

			if ($this->DoUpdate($uom)) {
				$this->persistence->SaveState("info", sprintf("Data Satuan: '%s' Dengan Kode: %s telah berhasil diupdate.", $uom->UomDesc, $uom->UomCd));
                redirect_url("common.uom");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $uom->UomCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data lokasi sebelum melakukan edit data !");
                redirect_url("common.uom");
		    }
			$uom = $uom->FindById($id);
			if ($uom == null) {
				$this->persistence->SaveState("error", "Data Lokasi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.uom");
			}
		}

		$this->Set("uom", $uom);
	}

	private function DoUpdate(UomMaster $uom) {
		if ($uom->UomCd == "") {
			$this->Set("error", "Kode lokasi masih kosong");
			return false;
		}

		if ($uom->UomDesc == "") {
			$this->Set("error", "Nama lokasi masih kosong");
			return false;
		}

		if ($uom->Update($uom->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data satuan sebelum melakukan hapus data !");
			redirect_url("common.uom");
		}

		$uom = new UomMaster();
		$uom = $uom->FindById($id);
		if ($uom == null) {
			$this->persistence->SaveState("error", "Data satuan yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.uom");
		}

		if ($uom->Delete($uom->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data satuan: '%s' Dengan Kode: %s telah berhasil dihapus.", $uom->UomDesc, $uom->UomCd));
            redirect_url("common.uom");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data satuan: '%s'. Message: %s", $uom->UomDesc, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.uom");
	}
}
