<?php
class DocTypeController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/doc_type.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "Id", "width" => 50);
		$settings["columns"][] = array("name" => "a.doc_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.description", "display" => "Jenis Dokumen", "width" => 300);
		$settings["columns"][] = array("name" => "b.module_cd", "display" => "Module", "width" => 80);
		$settings["columns"][] = array("name" => "COALESCE(c.voucher_cd, '-')", "display" => "Voucher", "width" => 80);

		$settings["filters"][] = array("name" => "a.doc_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.description", "display" => "Jenis Dokumen");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Jenis - jenis Dokumen yang Digunakan";
			if ($acl->CheckUserAccess("doctype", "add", "common")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.doctype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("doctype", "add", "common")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.doctype/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if ($acl->CheckUserAccess("doctype", "add", "common")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.doctype/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] =
"cm_doctype AS a
	JOIN sys_module AS b ON a.module_id = b.id
	LEFT JOIN ac_voucher_type AS c ON a.accvoucher_id = c.id";
			$settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {

		require_once(MODEL . "master/module.php");
		require_once(MODEL . "accounting/voucher_type.php");
		$loader = null;

		$docType = new DocType();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$docType->DocCode = $this->GetPostValue("DocCode");
			$docType->Description = $this->GetPostValue("Description");
			$docType->ModuleId = $this->GetPostValue("ModuleId");
			$docType->AccVoucherId = $this->GetPostValue("AccVoucherId");
			if ($docType->AccVoucherId == "") {
				$docType->AccVoucherId = null;
			}

			if ($this->DoInsert($docType)) {
				$this->persistence->SaveState("info", sprintf("Data Dokumen: '%s' Dengan Kode: %s telah berhasil disimpan.", $docType->DocCode, $docType->Description));
				redirect_url("common.doctype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $docType->DocCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// load data company for combo box
		$loader = new Module();
		$modules = $loader->LoadAll();
		$loader = new VoucherType();
		$vouchers = $loader->LoadAll();

		// untuk kirim variable ke view
		$this->Set("doctype", $docType);
		$this->Set("modules", $modules);
		$this->Set("vouchers", $vouchers);
	}

	private function DoInsert(DocType $docType) {
		if ($docType->DocCode == "") {
			$this->Set("error", "Kode dokumen masih kosong");
			return false;
		}
		if ($docType->Description == "") {
			$this->Set("error", "Nama dokumen masih kosong");
			return false;
		}

		if ($docType->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/module.php");
		require_once(MODEL . "accounting/voucher_type.php");
		$loader = null;

		$docType = new DocType();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$docType->Id = $this->GetPostValue("DocId");
			$docType->DocCode = $this->GetPostValue("DocCode");
			$docType->Description = $this->GetPostValue("Description");
			$docType->ModuleId = $this->GetPostValue("ModuleId");
			$docType->AccVoucherId = $this->GetPostValue("AccVoucherId");
			if ($docType->AccVoucherId == "") {
				$docType->AccVoucherId = null;
			}

			if ($this->DoUpdate($docType)) {
				$this->persistence->SaveState("info", sprintf("Data Dokumen: '%s' Dengan Kode: %s telah berhasil diupdate.", $docType->DocCode, $docType->Description));
				redirect_url("common.doctype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $docType->DocCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
				redirect_url("common.doctype");
			}
			$docType = $docType->FindById($id);
			if ($docType == null) {
				$this->persistence->SaveState("error", "Data Dokumen yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.doctype");
			}
		}

		// load data company for combo box

		$loader = new Module();
		$modules = $loader->LoadAll();
		$loader = new VoucherType();
		$vouchers = $loader->LoadAll();

		// untuk kirim variable ke view
		$this->Set("doctype", $docType);
		$this->Set("modules", $modules);
		$this->Set("vouchers", $vouchers);
	}

	private function DoUpdate(DocType $docType) {
		if ($docType->DocCode == "") {
			$this->Set("error", "Nama perusahaan masih kosong");
			return false;
		}

		if ($docType->Update($docType->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data proyek sebelum melakukan hapus data !");
			redirect_url("common.doctype");
		}

		$docType = new DocType();
		$docType = $docType->FindById($id);
		if ($docType == null) {
			$this->persistence->SaveState("error", "Data proyek yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.doctype");
		}

		if ($docType->Delete($docType->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Dokumen: '%s' Dengan Kode: %s telah berhasil dihapus.", $docType->DocCode, $docType->Description));
			redirect_url("common.doctype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data proyek: '%s'. Message: %s", $docType->DocCode, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.doctype");
	}
}
