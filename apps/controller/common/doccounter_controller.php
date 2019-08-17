<?php
class DocCounterController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/doc_counter.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "c.doc_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "c.description", "display" => "Jenis Dokumen", "width" => 300);
		$settings["columns"][] = array("name" => "d.module_cd", "display" => "Module", "width" => 80);
		$settings["columns"][] = array("name" => "e.voucher_cd", "display" => "Voucher", "width" => 80);
		$settings["columns"][] = array("name" => "a.trx_month", "display" => "Bulan", "width" => 50, "sortable" => false, "align" => "right");
		$settings["columns"][] = array("name" => "a.trx_year", "display" => "Tahun", "width" => 50, "sortable" => false, "align" => "right");
		$settings["columns"][] = array("name" => "a.counter", "display" => "Counter", "width" => 80, "sortable" => false, "align" => "right");
		$settings["columns"][] = array("name" => "CASE WHEN a.is_locked = 0 THEN 'Open' ELSE 'Locked' END", "display" => "Status", "width" => 80, "sortable" => false);

		$settings["filters"][] = array("name" => "c.doc_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "c.description", "display" => "Jenis Dokumen");
		$settings["filters"][] = array("name" => "a.trx_year", "display" => "Tahun", "numeric" => true);

		if (!$router->IsAjaxRequest) {
			// Bikin Flexigrid User Interface
			$acl = AclManager::GetInstance();

			$settings["title"] = "Manajemen Penomoran Dokumen";
			if ($acl->CheckUserAccess("doccounter", "add", "common")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.doccounter/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("doccounter", "add", "common")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.doccounter/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if ($acl->CheckUserAccess("doccounter", "add", "common")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.doccounter/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] =
"cm_doccounter AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_doctype AS c ON a.doctype_id = c.id
	JOIN sys_module AS d ON c.module_id = d.id
	JOIN ac_voucher_type AS e ON c.accvoucher_id = e.id";
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$settings["where"] = "c.is_deleted = 0";
			} else {
				$settings["where"] = "c.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
			}

			// HACK agar kalau kirim kosong akan default ke pencarian berdasarkan Tahun Berjalan
			if ($_GET["query"] == null) {
				$_GET["query"] = date("Y");	// Tahun Berjalan
				$_GET["qtype"] = 2;			// Filter tahun ada pada index #2
			}
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "common/doc_type.php");

		$loader = null;
        $doctype = null;

        $doccounter = new DocCounter();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $doccounter->EntityId = $this->userCompanyId;
            $doccounter->DocTypeId = $this->GetPostValue("DocTypeId");
            $doccounter->TrxMonth = $this->GetPostValue("TrxMonth");
            $doccounter->TrxYear = $this->GetPostValue("TrxYear");
            $doccounter->Counter = $this->GetPostValue("Counter");
            $doccounter->IsLocked = $this->GetPostValue("IsLocked");

			if ($this->DoInsert($doccounter)) {
				$this->persistence->SaveState("info", sprintf("Data telah berhasil disimpan."));
				redirect_url("common.doccounter");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $doccounter->DocTypeId));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// load data company for combo box
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

        // load data document for combo box
        $loader = new DocType();
        $doctype = $loader->LoadAll();

		// untuk kirim variable ke view
		$this->Set("doccounter", $doccounter);
        $this->Set("doctypes", $doctype);
		$this->Set("company", $company);
	}

	private function DoInsert(DocCounter $doccounter) {

		if ($doccounter->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
		if ($doccounter->DocTypeId == "") {
			$this->Set("error", "Kode dokumen masih kosong");
			return false;
		}

		if ($doccounter->TrxMonth == "") {
			$this->Set("error", "Bulan masih kosong");
			return false;
		}

        if ($doccounter->TrxYear == "") {
            $this->Set("error", "Tahun masih kosong");
            return false;
        }

		if ($doccounter->Counter == "") {
			$this->Set("error", "Counter masih kosong");
			return false;
		}

		if ($doccounter->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "common/doc_type.php");

        $loader = null;
        $doctype = null;

		$doccounter = new DocCounter();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$doccounter->Id = $this->GetPostValue("Id");
            $doccounter->EntityId = $this->userCompanyId;
            $doccounter->DocTypeId = $this->GetPostValue("DocTypeId");
            $doccounter->TrxMonth = $this->GetPostValue("TrxMonth");
            $doccounter->TrxYear = $this->GetPostValue("TrxYear");
            $doccounter->Counter = $this->GetPostValue("Counter");
            $doccounter->IsLocked = $this->GetPostValue("IsLocked");

			if ($this->DoUpdate($doccounter)) {
				$this->persistence->SaveState("info", sprintf("Data telah berhasil diupdate."));
				redirect_url("common.doccounter");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $doccounter->DocTypeId));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data sebelum melakukan edit data !");
				redirect_url("common.doccounter");
			}
			$doccounter = $doccounter->FindById($id);
			if ($doccounter == null) {
				$this->persistence->SaveState("error", "Data Dokumen yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.doccounter");
			}
		}

        // load data company for combo box
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

        // load data document for combo box
        $loader = new DocType();
        $doctype = $loader->LoadAll();

        // untuk kirim variable ke view
        $this->Set("doccounter", $doccounter);
        $this->Set("doctypes", $doctype);
        $this->Set("company", $company);
	}

	private function DoUpdate(DocCounter $doccounter) {
        if ($doccounter->EntityId == "") {
            $this->Set("error", "Kode perusahaan masih kosong");
            return false;
        }
        if ($doccounter->DocTypeId == "") {
            $this->Set("error", "Kode dokumen masih kosong");
            return false;
        }

        if ($doccounter->TrxMonth == "") {
            $this->Set("error", "Bulan masih kosong");
            return false;
        }

        if ($doccounter->TrxYear == "") {
            $this->Set("error", "Tahun masih kosong");
            return false;
        }

        if ($doccounter->Counter == "") {
            $this->Set("error", "Counter masih kosong");
            return false;
        }

		if ($doccounter->Update($doccounter->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data dokumen sebelum melakukan hapus data !");
			redirect_url("common.doccounter");
		}

		$doccounter = new DocCounter();
        $doccounter = $doccounter->FindById($id);
		if ($doccounter == null) {
			$this->persistence->SaveState("error", "Data dokumen yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.doccounter");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($doccounter->EntityId != $this->userCompanyId) {
				// Simulate not found ! Access data which belong to other Company without CORPORATE access level
				$this->persistence->SaveState("error", "Data dokumen yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.doccounter");
			}
		}

		if ($doccounter->Delete($doccounter->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Dokumen telah berhasil dihapus."));
			redirect_url("common.doccounter");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data.. Message: %s", $this->connector->GetErrorMessage()));
		}
		redirect_url("common.doccounter");
	}
}
