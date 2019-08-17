<?php

/**
 * Hmm nama class nya emang aneh.. tapi gw uda ga kepikiran mau kasi nama apa lagi untuk OpeningBalance
 * Obal == Opening Balance. Dan modul ini untuk akun-akun yang akan di proses opening balancenya
 *
 * Nanti mungkin akan ada modul lain yang merupakan subset dari modul ini dan gw akan pakai tehnik dispatcher untuk itu agar tida buang-buang waktu
 * Dispatcher yang baru sudah bisa bypass ACL jadi ga masalah untuk user access nya cukup di level yang specific nya saja
 */
class ObalController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ledger/opening_balance.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.acc_no", "display" => "Account No.", "width" => 100);
		$settings["columns"][] = array("name" => "b.acc_name", "display" => "Account Name", "width" => 250);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.bal_date, '%Y')", "display" => "Year", "width" => 70);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.bal_date, '%m')", "display" => "Month", "width" => 100);
		$settings["columns"][] = array("name" => "FORMAT(a.bal_debit_amt, 2)", "display" => "Debit", "width" => 100, "align" => "right");
		$settings["columns"][] = array("name" => "FORMAT(a.bal_credit_amt, 2)", "display" => "Credit", "width" => 100, "align" => "right");

		$settings["filters"][] = array("name" => "b.acc_no", "display" => "Account No.");
		$settings["filters"][] = array("name" => "b.acc_name", "display" => "Account Name");
		$settings["filters"][] = array("name" => "DATE_FORMAT(a.bal_date, '%Y')", "display" => "Year", "numeric" => true);
		$settings["filters"][] = array("name" => "DATE_FORMAT(a.bal_date, '%m')", "display" => "Month", "numeric" => true);

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Account Opening Balance";
			if ($acl->CheckUserAccess("obal", "add", "accounting")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ledger.obal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("obal", "edit", "accounting")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ledger.obal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Harap memilih opening balance sebelum proses edit !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
											   "Confirm" => "");
			}
			if ($acl->CheckUserAccess("obal", "delete", "accounting")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ledger.obal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Harap memilih opening balance sebelum proses delete !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
											   "Confirm" => "Apakah anda yakin mau menghapus data opening balance yang dipilih ?\nKlik 'OK' untuk melanjutkan prosedur delete.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = false;
		} else {
			$settings["from"] =
"ac_opening_balance1 AS a
	JOIN cm_acc_detail AS b ON a.acc_id = b.id";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/coa.php");

		$openingBalance = new OpeningBalance();
		if (count($this->postData) > 0) {
            $openingBalance->EntityId = $this->userCompanyId;
			$month = $this->GetPostValue("Month", 1);
			$year = $this->GetPostValue("Year", date("Y"));
			$openingBalance->AccountId = $this->GetPostValue("AccountId");
			$openingBalance->Date = mktime(0, 0, 0, $month, 1, $year);
			$openingBalance->DebitAmount = str_replace(",","", $this->GetPostValue("Debit"));
			$openingBalance->CreditAmount = str_replace(",","", $this->GetPostValue("Credit"));

			if ($this->ValidateData($openingBalance)) {
				$openingBalance->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($openingBalance->Insert() == 1) {
					$this->persistence->SaveState("info", sprintf("Opening balance periode %s sudah disimpan. Debet: %s Kredit: %s", $openingBalance->FormatDate(), number_format($openingBalance->DebitAmount, 2), number_format($openingBalance->CreditAmount)));
					redirect_url("ledger.obal");
				} else {
					if ($this->connector->GetHasError()) {
						if ($this->connector->IsDuplicateError()) {
							$this->Set("error", "Maaf data opening balance pada periode yang diminta sudah ada.");
						} else {
							$this->Set("error", "Database error: " . $this->connector->GetErrorMessage());
						}
					}
				}
			}
		} else {
			$openingBalance->Date = time();
			$openingBalance->DebitAmount = 0;
			$openingBalance->CreditAmount = 0;
		}

		$account = new Coa();
		$parentAccounts = $account->LoadByLevel($this->userCompanyId,2, true);
        $account = new Coa();
		$accounts = $account->LoadLevel3ByFirstCode($this->userCompanyId,array("1", "2", "3"));

		$this->Set("parentAccounts", $parentAccounts);
		$this->Set("accounts", $accounts);
		$this->Set("openingBalance", $openingBalance);
	}

	private function ValidateData(OpeningBalance $openingBalance) {
		if ($openingBalance->AccountId == null) {
			$this->Set("error", "Maaf anda harus memilih akun terlebih dahulu.");
			return false;
		}
		if (!is_int($openingBalance->Date)) {
			$this->Set("error", "Maaf anda harus memilih periode opening balance terlebih dahulu");
			return false;
		}
		/*
		if ($openingBalance->DebitAmount < 0) {
			$this->Set("error", "Maaf untuk jumlah debet tidak bisa kurang dari 0");
			return false;
		}
		if ($openingBalance->CreditAmount < 0) {
			$this->Set("error", "Maaf untuk jumlah kredit tidak bisa kurang dari 0");
			return false;
		}
		*/
		if ($openingBalance->DebitAmount == 0 && $openingBalance->CreditAmount == 0) {
			$this->Set("error", "Maaf Debet dan Kredit bernilai 0. Tidak boleh bernilai 0 untuk kedua field tersebut");
			return false;
		}

		return true;
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih data opening balance terlebih dahulu.");
			redirect_url("ledger.obal");
			return;
		}
		require_once(MODEL . "master/coa.php");

		$openingBalance = new OpeningBalance();
		if (count($this->postData) > 0) {
		    $openingBalance->EntityId = $this->userCompanyId;
			$month = $this->GetPostValue("Month", 1);
			$year = $this->GetPostValue("Year", date("Y"));
			$openingBalance->Id = $id;
			$openingBalance->AccountId = $this->GetPostValue("AccountId");
			$openingBalance->Date = mktime(0, 0, 0, $month, 1, $year);
			$openingBalance->DebitAmount = str_replace(",","", $this->GetPostValue("Debit"));
			$openingBalance->CreditAmount = str_replace(",","", $this->GetPostValue("Credit"));

			if ($this->ValidateData($openingBalance)) {
				$openingBalance->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($openingBalance->Update($openingBalance->Id) == 1) {
					$this->persistence->SaveState("info", sprintf("Opening balance periode %s sudah disimpan. Debet: %s Kredit: %s", $openingBalance->FormatDate(), number_format($openingBalance->DebitAmount, 2), number_format($openingBalance->CreditAmount)));
					redirect_url("ledger.obal");
				} else {
					if ($this->connector->GetHasError()) {
						if ($this->connector->IsDuplicateError()) {
							$this->Set("error", "Maaf data opening balance pada periode yang diminta sudah ada.");
						} else {
							$this->Set("error", "Database error: " . $this->connector->GetErrorMessage());
						}
					}
				}
			}
		} else {
			$openingBalance = $openingBalance->LoadById($id);
			if ($openingBalance == null) {
				$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
				redirect_url("ledger.obal");
				return;
			}
		}

        $account = new Coa();
        $parentAccounts = $account->LoadByLevel($this->userCompanyId,2, true);
        $account = new Coa();
        $accounts = $account->LoadLevel3ByFirstCode($this->userCompanyId,array("1", "2", "3"));

		$this->Set("parentAccounts", $parentAccounts);
		$this->Set("accounts", $accounts);
		$this->Set("openingBalance", $openingBalance);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih data opening balance terlebih dahulu.");
			redirect_url("ledger.obal");
			return;
		}

		$openingBalance = new OpeningBalance();
		$openingBalance = $openingBalance->LoadById($id);
		if ($openingBalance == null) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			redirect_url("ledger.obal");
			return;
		}

		$rs = $openingBalance->Delete($openingBalance->Id);
		if ($rs == 1) {
			$this->persistence->SaveState("info", sprintf("Opening Balance %s periode %s sudah dihapus", $openingBalance->AccountNo, $openingBalance->FormatDate("F Y")));
		} else {
			$this->persistence->SaveState("info", sprintf("Gagal hapus opening balance %s periode %s. Message: %s", $openingBalance->AccountNo, $openingBalance->FormatDate("F Y"), $this->connector->GetErrorMessage()));
		}
	}
}


// End of File: obal_controller.php
