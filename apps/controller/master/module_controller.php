<?php
class ModuleController extends AppController {
	protected function Initialize() {
		require_once(MODEL . "master/module.php");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.module_cd", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.module_name", "display" => "Nama Modul", "width" => 150);

		$settings["filters"][] = array("name" => "a.module_cd", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.module_name", "display" => "Nama Modul");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Modul System";

			if ($acl->CheckUserAccess("master.module", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.module/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.module", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.module/edit/%s", "Class" => "bt_edit", "ReqId" => 1, "Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.module", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.module/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}
		} else {
			$settings["from"] = "sys_module AS a";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		$module = new Module();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$module->ModuleCd = $this->GetPostValue("ModuleCd");
			$module->ModuleName = $this->GetPostValue("ModuleName");

			if ($this->DoInsert($module)) {
				$this->persistence->SaveState("info", sprintf("Data Modul: '%s' Dengan Kode: %s telah berhasil disimpan.", $module->ModuleName, $module->ModuleCd));
				redirect_url("master.module");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $module->ModuleCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		$this->Set("module", $module);
	}

	private function DoInsert(Module $module) {
		if ($module->ModuleCd == "") {
			$this->Set("error", "Kode modul masih kosong");
			return false;
		}

		if ($module->ModuleName == "") {
			$this->Set("error", "Nama modul masih kosong");
			return false;
		}

		if ($module->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {

		$module = new Module();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$module->Id = $this->GetPostValue("Id");
			$module->ModuleCd = $this->GetPostValue("ModuleCd");
			$module->ModuleName = $this->GetPostValue("ModuleName");

			if ($this->DoUpdate($module)) {
				$this->persistence->SaveState("info", sprintf("Data Module: '%s' Dengan Kode: %s telah berhasil diupdate.", $module->ModuleName, $module->ModuleCd));
				redirect_url("master.module");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $module->ModuleCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data Module sebelum melakukan edit data !");
				redirect_url("master.module");
			}
			$module = $module->FindById($id);
			if ($module == null) {
				$this->persistence->SaveState("error", "Data Module yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.module");
			}
		}

		$this->Set("module", $module);
	}

	private function DoUpdate(Module $module) {
		if ($module->ModuleCd == "") {
			$this->Set("error", "Kode modul masih kosong");
			return false;
		}

		if ($module->ModuleName == "") {
			$this->Set("error", "Nama modul masih kosong");
			return false;
		}

		if ($module->Update($module->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Module sebelum melakukan hapus data !");
			redirect_url("master.module");
		}

		$module = new Module();
		$module = $module->FindById($id);
		if ($module == null) {
			$this->persistence->SaveState("error", "Data Module yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.module");
		}

		if ($module->Delete($module->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Module: '%s' Dengan Kode: %s telah berhasil dihapus.", $module->ModuleName, $module->ModuleCd));
			redirect_url("master.module");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Module: '%s'. Message: %s", $module->ModuleName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.module");
	}
}
