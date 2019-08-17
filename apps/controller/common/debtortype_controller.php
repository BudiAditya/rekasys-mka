<?php
class DebtorTypeController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/debtor_type.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 80);
		$settings["columns"][] = array("name" => "a.debtortype_cd", "display" => "Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.debtortype_desc", "display" => "Debtor Type", "width" => 250);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "A/R Number", "width" => 120);
        $settings["columns"][] = array("name" => "c.acc_name", "display" => "Account Name", "width" => 250);

		$settings["filters"][] = array("name" => "a.debtortype_cd", "display" => "Code");
		$settings["filters"][] = array("name" => "a.debtortype_desc", "display" => "Debtor Type");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Debtor Type";
			if($acl->CheckUserAccess("debtortype", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.debtortype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("debtortype", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.debtortype/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("debtortype", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.debtortype/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ar_debtortype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id JOIN cm_acc_detail AS c ON a.acc_control_id = c.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		$debtorType = new DebtorType();

		$loader = null;

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$debtorType->EntityId = $this->userCompanyId;
			$debtorType->DebtorTypeCd = $this->GetPostValue("DebtorTypeCd");
			$debtorType->DebtorTypeDesc = $this->GetPostValue("DebtorTypeDesc");
			//$debtorType->DebtorTypeClass = $this->GetPostValue("DebtorTypeClass");
			$debtorType->AccControlId = $this->GetPostValue("AccCtl");

			if ($this->DoInsert($debtorType)) {
				$this->persistence->SaveState("info", sprintf("Data Debtor Type: '%s' Dengan Kode: %s telah berhasil disimpan.", $debtorType->DebtorTypeDesc, $debtorType->DebtorTypeCd));
				redirect_url("common.debtortype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $debtorType->DebtorTypeCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		// load combobox data
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$loader = new Coa();
		$accounts = $loader->LoadByLevel($this->userCompanyId,3);

		$this->Set("accounts", $accounts);
		$this->Set("company", $company);
		$this->Set("debtorType", $debtorType);
	}

	private function DoInsert(DebtorType $debtorType) {
		if ($debtorType->DebtorTypeCd == "") {
			$this->Set("error", "Kode Type debtor masih kosong");
			return false;
		}

		if ($debtorType->DebtorTypeDesc == "") {
			$this->Set("error", "Type Debtor masih kosong");
			return false;
		}

		if ($debtorType->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		$debtorType = new DebtorType();

		$loader = null;

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$debtorType->Id = $this->GetPostValue("Id");
			$debtorType->EntityId = $this->userCompanyId;
			$debtorType->DebtorTypeCd = $this->GetPostValue("DebtorTypeCd");
			$debtorType->DebtorTypeDesc = $this->GetPostValue("DebtorTypeDesc");
			//$debtorType->DebtorTypeClass = $this->GetPostValue("DebtorTypeClass");
			$debtorType->AccControlId = $this->GetPostValue("AccCtl");

			if ($this->DoUpdate($debtorType)) {
				$this->persistence->SaveState("info", sprintf("Data Debtor Type: '%s' Dengan Kode: %s telah berhasil diupdate.", $debtorType->DebtorTypeDesc, $debtorType->DebtorTypeCd));
				redirect_url("common.debtortype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $debtorType->DebtorTypeCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data Debtor Type sebelum melakukan edit data !");
				redirect_url("common.debtortype");
			}
			$debtorType = $debtorType->FindById($id);
			if ($debtorType == null) {
				$this->persistence->SaveState("error", "Data Debtor Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.debtortype");
			}
		}

		// load combobox data
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$loader = new Coa();
		$accounts = $loader->LoadByLevel($this->userCompanyId,3);

		$this->Set("company", $company);
		$this->Set("accounts", $accounts);
		$this->Set("debtorType", $debtorType);
	}

	private function DoUpdate(DebtorType $debtorType) {
		if ($debtorType->DebtorTypeCd == "") {
			$this->Set("error", "Kode Debtor Type masih kosong");
			return false;
		}

		if ($debtorType->DebtorTypeDesc == "") {
			$this->Set("error", "Nama Debtor Type masih kosong");
			return false;
		}

		if ($debtorType->Update($debtorType->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Debtor Type sebelum melakukan hapus data !");
			redirect_url("common.debtortype");
		}

		$debtorType = new DebtorType();
		$debtorType = $debtorType->FindById($id);
		if ($debtorType == null) {
			$this->persistence->SaveState("error", "Data Debtor Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.debtortype");
		}
		if ($debtorType->Delete($debtorType->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Debtor Type: '%s' Dengan Kode: %s telah berhasil dihapus.", $debtorType->DebtorTypeDesc, $debtorType->DebtorTypeCd));
			redirect_url("common.debtortype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Debtor Type: '%s'. Message: %s", $debtorType->DebtorTypeDesc, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.debtortype");
	}
}
