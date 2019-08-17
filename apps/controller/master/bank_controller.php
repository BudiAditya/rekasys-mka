<?php

class BankController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/bank.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a.bank_name", "display" => "Bank Name", "width" => 180);
		$settings["columns"][] = array("name" => "a.branch", "display" => "Branch", "width" => 100);
		$settings["columns"][] = array("name" => "a.rek_no", "display" => "Bank Acc No", "width" => 100);
		$settings["columns"][] = array("name" => "a.currency_cd", "display" => "Currency", "width" => 60);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Account No", "width" => 100);
		$settings["columns"][] = array("name" => "d.acc_no", "display" => "Cost Account", "width" => 100);
		$settings["columns"][] = array("name" => "e.acc_no", "display" => "Revenue Account", "width" => 100);

		$settings["filters"][] = array("name" => "a.bank_name", "display" => "Bank Name");
		$settings["filters"][] = array("name" => "a.branch", "display" => "Branch");
		$settings["filters"][] = array("name" => "a.rek_no", "display" => "Bank Acc No");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Bank Account Master";

			if ($acl->CheckUserAccess("master.bank", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.bank/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.bank", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.bank/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih bank terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu bank.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.bank", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.bank/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih bank terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu bank.",
					"Confirm" => "Apakah anda mau menghapus data bank yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] =
"cm_bank_account AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	LEFT JOIN cm_acc_detail AS c ON a.acc_id = c.id
	LEFT JOIN cm_acc_detail AS d ON a.cost_acc_id = d.id
	LEFT JOIN cm_acc_detail AS e ON a.rev_acc_id = e.id";

			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$settings["where"] = "a.is_deleted = 0";
			} else {
				$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
			}
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Bank $bank) {
		if ($bank->Name == null) {
			$this->Set("error", "Mohon memasukkan nama bank terlebih dahulu.");
			return false;
		}
		if ($bank->CurrencyCode == null) {
			$this->Set("error", "Mohon memasukkan mata uang rekening bank terlebih dahulu.");
			return false;
		}
		if ($bank->AccId == null) {
			$this->Set("error", "Mohon memilih akun kontrol terlebih dahulu.");
			return false;
		}

		if ($bank->CostAccId == "") {
			$bank->CostAccId = null;
		}
		if ($bank->RevAccId == "") {
			$bank->RevAccId = null;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");

		$bank = new Bank();

		if (count($this->postData) > 0) {
			$bank->Name = $this->GetPostValue("Name");
			$bank->Branch = $this->GetPostValue("Branch");
			$bank->Address = $this->GetPostValue("Address");
			$bank->NoRekening = $this->GetPostValue("NoRek");
			$bank->CurrencyCode = $this->GetPostValue("CurrencyCode");
			$bank->AccId = $this->GetPostValue("AccId");
			$bank->CostAccId = $this->GetPostValue("CostAccId");
			$bank->RevAccId = $this->GetPostValue("RevAccId");

			if ($this->ValidateData($bank)) {
				$bank->EntityId = $this->userCompanyId;
				$bank->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				$rs = $bank->Insert();
				if ($rs == 1) {
					$this->persistence->SaveState("info", sprintf("Data bank: %s (%s) sudah berhasil disimpan", $bank->Name, $bank->Branch));
					redirect_url("master.bank");
				} else {
					$this->Set("error", "Gagal pada saat menyimpan data bank. Message: " . $this->connector->GetErrorMessage());
				}
			}
		}

		$company = new Company();
		$company->LoadById($this->userCompanyId);
		$account = new Coa();

		$this->Set("company", $company);
		$this->Set("bank", $bank);
		// Bank, Cash, Piutang Tidak Teridentifikasi
		$this->Set("cashAccounts", $account->LoadByLevel($this->userCompanyId,3));
		$this->Set("costAccounts", $account->LoadByLevel($this->userCompanyId,3));
		$this->Set("revenueAccounts", $account->LoadByLevel($this->userCompanyId,3));
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Marap memilih bank terlebih dahulu sebelum melakukan proses edit.");
			redirect_url("master.bank");
		}

		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		$bank = new Bank();
		if (count($this->postData) > 0) {
			$bank->Id = $id;
			$bank->Name = $this->GetPostValue("Name");
			$bank->Branch = $this->GetPostValue("Branch");
			$bank->Address = $this->GetPostValue("Address");
			$bank->NoRekening = $this->GetPostValue("NoRek");
			$bank->CurrencyCode = $this->GetPostValue("CurrencyCode");
			$bank->AccId = $this->GetPostValue("AccId");
			$bank->CostAccId = $this->GetPostValue("CostAccId");
			$bank->RevAccId = $this->GetPostValue("RevAccId");

			if ($this->ValidateData($bank)) {
				$bank->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				$rs = $bank->Update($bank->Id);
				if ($rs == 1) {
					$this->persistence->SaveState("info", sprintf("Perubahan data bank: %s (%s) sudah berhasil disimpan", $bank->Name, $bank->Branch));
					redirect_url("master.bank");
				} else {
					$this->Set("error", "Gagal pada saat merubah data bank. Message: " . $this->connector->GetErrorMessage());
				}
			}
		} else {
			$bank = $bank->LoadById($id);
			if ($bank == null || $bank->IsDeleted) {
				$this->persistence->SaveState("error", "Maaf bank yang diminta tidak dapat ditemukan atau sudah dihapus.");
				redirect_url("master.bank");
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($bank->EntityId != $this->userCompanyId) {
					// Simulate not found. Trying to access other Company data
					$this->persistence->SaveState("error", "Maaf bank yang diminta tidak dapat ditemukan atau sudah dihapus.");
					redirect_url("master.bank");
				}
			}
		}

		$company = new Company();
		$company->LoadById($bank->EntityId);
		$account = new Coa();

		$this->Set("company", $company);
		$this->Set("bank", $bank);
        $this->Set("cashAccounts", $account->LoadByLevel($this->userCompanyId,3));
        $this->Set("costAccounts", $account->LoadByLevel($this->userCompanyId,3));
        $this->Set("revenueAccounts", $account->LoadByLevel($this->userCompanyId,3));
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Marap memilih bank terlebih dahulu sebelum melakukan proses penghapusan data bank.");
			redirect_url("master.bank");
		}

		$bank = new Bank();
		$bank = $bank->LoadById($id);
		if ($bank == null || $bank->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf bank yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("master.bank");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($bank->EntityId != $this->userCompanyId) {
				// Simulate not found. Trying to access other Company data
				$this->persistence->SaveState("error", "Maaf bank yang diminta tidak dapat ditemukan atau sudah dihapus.");
				redirect_url("master.bank");
			}
		}

		$rs = $bank->Delete($bank->Id);
		if ($rs == 1) {
			$this->persistence->SaveState("info", sprintf("Bank: %s (%s) sudah dihapus", $bank->Name, $bank->Branch));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus bank: %s (%s). Error: %s", $bank->Name, $bank->Branch, $this->connector->GetErrorMessage()));
		}

		redirect_url("master.bank");
	}
}

// End of file: bank_controller.php
