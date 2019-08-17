<?php
class TrxClassController extends AppController {
	protected function Initialize() {
		require_once(MODEL . "common/trx_class.php");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.trxclass_cd", "display" => "Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.trxclass_desc", "display" => "Class Description", "width" => 350);

		$settings["filters"][] = array("name" => "a.trxclass_cd", "display" => "Code");
		$settings["filters"][] = array("name" => "a.trxclass_desc", "display" => "Class Description");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Transaction Class";
			if($acl->CheckUserAccess("trxclass", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.trxclass/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("trxclass", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.trxclass/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("trxclass", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.trxclass/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "sys_trxclass AS a";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		$trxClass = new TrxClass();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$trxClass->TrxClassCd = $this->GetPostValue("TrxClassCd");
			$trxClass->TrxClassDesc = $this->GetPostValue("TrxClassDesc");

			if ($this->DoInsert($trxClass)) {
                $this->persistence->SaveState("info", sprintf("Data Trx Class: '%s' Dengan Kode: %s telah berhasil disimpan.", $trxClass->TrxClassDesc, $trxClass->TrxClassCd));
                redirect_url("common.trxclass");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $trxClass->TrxClassCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		$this->Set("trxclass", $trxClass);
	}

	private function DoInsert(TrxClass $trxClass) {
		if ($trxClass->TrxClassCd == "") {
			$this->Set("error", "Kode trx class masih kosong");
			return false;
		}

		if ($trxClass->TrxClassDesc == "") {
			$this->Set("error", "Nama trx class masih kosong");
			return false;
		}

		if ($trxClass->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {

		$trxClass = new TrxClass();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$trxClass->Id = $this->GetPostValue("Id");
			$trxClass->TrxClassCd = $this->GetPostValue("TrxClassCd");
			$trxClass->TrxClassDesc = $this->GetPostValue("TrxClassDesc");

			if ($this->DoUpdate($trxClass)) {
				$this->persistence->SaveState("info", sprintf("Data Trx Class: '%s' Dengan Kode: %s telah berhasil diupdate.", $trxClass->TrxClassDesc, $trxClass->TrxClassCd));
                redirect_url("common.trxclass");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $trxClass->TrxClassCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data trx class sebelum melakukan edit data !");
                redirect_url("common.trxclass");
		    }
			$trxClass = $trxClass->FindById($id);
			if ($trxClass == null) {
				$this->persistence->SaveState("error", "Data Trx Class yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.trxclass");
			}
		}
		$this->Set("trxclass", $trxClass);
	}

	private function DoUpdate(TrxClass $trxClass) {
		if ($trxClass->TrxClassCd == "") {
			$this->Set("error", "Kode trx class masih kosong");
			return false;
		}

		if ($trxClass->TrxClassDesc == "") {
			$this->Set("error", "Nama trx class masih kosong");
			return false;
		}

		if ($trxClass->Update($trxClass->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data trx class sebelum melakukan hapus data !");
			redirect_url("common.trxclass");
		}

		$trxClass = new TrxClass();
		$trxClass = $trxClass->FindById($id);
		if ($trxClass == null) {
			$this->persistence->SaveState("error", "Data trx class yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.trxclass");
		}

		if ($trxClass->Delete($trxClass->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Trx Class: '%s' Dengan Kode: %s telah berhasil dihapus.", $trxClass->TrxClassDesc, $trxClass->TrxClassCd));
            redirect_url("common.trxclass");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data trx class: '%s'. Message: %s", $trxClass->TrxClassDesc, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.trxclass");
	}
}
