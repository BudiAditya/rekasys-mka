<?php
class TrxTypeController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/trx_type.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Compnay", "width" => 60);
		$settings["columns"][] = array("name" => "a.code", "display" => "Trx Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.description", "display" => "Transaction Type", "width" => 250);
		$settings["columns"][] = array("name" => "c.module_name", "display" => "Module", "width" => 120);
		$settings["columns"][] = array("name" => "d.trxclass_desc", "display" => "Trx Class", "width" => 100);
		$settings["columns"][] = array("name" => "g.acc_no", "display" => "Debit Account", "width" => 100);
		$settings["columns"][] = array("name" => "h.acc_no", "display" => "Credit Account", "width" => 100);

		$settings["filters"][] = array("name" => "a.code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.description", "display" => "Jenis Transaksi");
		$settings["filters"][] = array("name" => "c.module_name", "display" => "Module");
		$settings["filters"][] = array("name" => "g.acc_no", "display" => "No. Akun Debit");
		$settings["filters"][] = array("name" => "h.acc_no", "display" => "No. Akun Kredit");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Transaction Types";
			if($acl->CheckUserAccess("trxtype", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.trxtype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("trxtype", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.trxtype/edit/%s", "Class" => "bt_edit", "ReqId" => 1, "Confirm" => "");
			}
			if($acl->CheckUserAccess("trxtype", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.trxtype/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] =
"sys_trx_type AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_module AS c ON a.module_id = c.id
	LEFT JOIN sys_trxclass AS d ON a.trx_class_id = d.id
	LEFT JOIN cm_acc_detail AS g ON a.acc_debit_id = g.id
	LEFT JOIN cm_acc_detail AS h ON a.acc_credit_id = h.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/module.php");
        require_once(MODEL . "common/trx_class.php");
        require_once(MODEL . "master/coa.php");

		$trxType = new TrxType();
		$trxType->EntityId = $this->userCompanyId;

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$trxType->Code = $this->GetPostValue("TrxCd");
			$trxType->Description = $this->GetPostValue("TrxDesc");
            $trxType->ModuleId = $this->GetPostValue("ModuleId");
            $trxType->TrxClassId = $this->GetPostValue("TrxClassId");
            $trxType->AccDebitId = $this->GetPostValue("AccDebitId");
			$trxType->AccCreditId = $this->GetPostValue("AccCreditId");
			$trxType->IsGlobal = false;
			$trxType->ShowDebit = $this->GetPostValue("ShowDebit", "0") == "1";
			$trxType->ShowCredit = $this->GetPostValue("ShowCredit", "0") == "1";

			// OK jenis transaksi sekarang bisa mengharuskan memilih data tambahan (debtor/creditor/employee/asset)
			$reqDebtor = (int)$this->GetPostValue("reqDebtor", 0);
			$reqCreditor = (int)$this->GetPostValue("reqCreditor", 0);
			$reqEmployee = (int)$this->GetPostValue("reqEmployee", 0);
			$reqAsset = (int)$this->GetPostValue("reqAsset", 0);
			$trxType->RequireWhich = ($reqDebtor | $reqCreditor | $reqEmployee | $reqAsset);

			if ($this->ValidateData($trxType)) {
				$trxType->UpdateById = AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($trxType->Insert() == 1) {
					$this->persistence->SaveState("info", sprintf("Data Trx Class: '%s' Dengan Kode: %s telah berhasil disimpan.", $trxType->Description, $trxType->Code));
					redirect_url("common.trxtype");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $trxType->Code));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

        // load combobox data
		$module = new Module();
		$trxClass = new TrxClass();
		$coa = new Coa();
		$company = new Company($this->userCompanyId);

		$this->Set("modules", $module->LoadAll());
		$this->Set("trxClasses", $trxClass->LoadAll());
		$this->Set("accounts", $coa->LoadByLevel($this->userCompanyId,3));
		$this->Set("company", $company);
		$this->Set("trxType", $trxType);
	}

	private function ValidateData(TrxType $trxType) {
		if ($trxType->Code == "") {
			$this->Set("error", "Kode Transaksi masih kosong");
			return false;
		}
		if ($trxType->Description == "") {
			$this->Set("error", "Nama Transaksi masih kosong");
			return false;
		}
		if ($trxType->ModuleId == "") {
			$this->Set("error", "Nama Module masih kosong");
			return false;
		}
		if ($trxType->BillTypeId == "") {
			$trxType->BillTypeId = null;
		}
        if ($trxType->TaxSchId == "") {
            $trxType->TaxSchId = null;
        }
		if ($trxType->TrxClassId == "") {
			$trxType->TrxClassId = null;
		}
		if ($trxType->AccDebitId == "") {
			$trxType->AccDebitId = null;
		}
		if ($trxType->AccCreditId == "") {
			$trxType->AccCreditId = null;
		}
		if ($trxType->AccDebitId == null && $trxType->AccCreditId == null) {
			$this->Set("error", "Maaf untuk Akun debet dan kredit tidak bisa kosong semuanya. Hanya boleh salah satu yang kosong.");
			return false;
		}

		return true;
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/module.php");
        require_once(MODEL . "common/trx_class.php");
        require_once(MODEL . "master/coa.php");

		$trxType = new TrxType($id);

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$trxType->Code = $this->GetPostValue("TrxCd");
			$trxType->Description = $this->GetPostValue("TrxDesc");
			$trxType->ModuleId = $this->GetPostValue("ModuleId");
			$trxType->TrxClassId = $this->GetPostValue("TrxClassId");
			$trxType->AccDebitId = $this->GetPostValue("AccDebitId");
			$trxType->AccCreditId = $this->GetPostValue("AccCreditId");
			$trxType->ShowDebit = $this->GetPostValue("ShowDebit", "0") == "1";
			$trxType->ShowCredit = $this->GetPostValue("ShowCredit", "0") == "1";

			// OK jenis transaksi sekarang bisa mengharuskan memilih data tambahan (debtor/creditor/employee/asset)
			$reqDebtor = (int)$this->GetPostValue("reqDebtor", 0);
			$reqCreditor = (int)$this->GetPostValue("reqCreditor", 0);
			$reqEmployee = (int)$this->GetPostValue("reqEmployee", 0);
			$reqAsset = (int)$this->GetPostValue("reqAsset", 0);
			$trxType->RequireWhich = $reqDebtor | $reqCreditor | $reqEmployee | $reqAsset;

			if ($this->ValidateData($trxType)) {
				$trxType->UpdateById = AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($trxType->Update($trxType->Id) == 1) {
					$this->persistence->SaveState("info", sprintf("Data Transaksi: '%s' Dengan Kode: %s telah berhasil diupdate.", $trxType->Description, $trxType->Code));
					redirect_url("common.trxtype");
				} else {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $trxType->Code));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}

		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data Transaksi sebelum melakukan edit data !");
                redirect_url("common.trxtype");
		    }
			if ($trxType->Id == null || $trxType->Id != $id) {
				$this->persistence->SaveState("error", "Data Transaksi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.trxtype");
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($this->userCompanyId != $trxType->EntityId) {
					// Direct access ? KICK !
					$this->persistence->SaveState("error", "Data Transaksi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
					redirect_url("common.trxtype");
				}
			}
		}

        // load combobox data
        $module = new Module();
        $trxClass = new TrxClass();
        $coa = new Coa();
        $company = new Company();

        $this->Set("modules", $module->LoadAll());
        $this->Set("trxClasses", $trxClass->LoadAll());
        $this->Set("accounts", $coa->LoadAll());
		$this->Set("company", $company->LoadById($trxType->EntityId));
		$this->Set("trxType", $trxType);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Transaksi sebelum melakukan hapus data !");
			redirect_url("common.trxtype");
		}

		$trxType = new TrxType();
		$trxType = $trxType->FindById($id);
		if ($trxType == null) {
			$this->persistence->SaveState("error", "Data Transaksi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.trxtype");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($trxType->EntityId != $this->userCompanyId) {
				// Simulate not found ! Access data which belong to other Company without CORPORATE access level
				$this->persistence->SaveState("error", "Data Transaksi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.trxtype");
			}
		}

		if ($trxType->Delete($trxType->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Transaksi: '%s' Dengan Kode: %s telah berhasil dihapus.", $trxType->Description, $trxType->Code));
            redirect_url("common.trxtype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Transaksi: '%s'. Message: %s", $trxType->Description, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.trxtype");
	}
}
