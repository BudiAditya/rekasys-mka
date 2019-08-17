<?php
namespace Ar;

/**
 * Class ObalController
 * @package Ar
 *
 *          Serupa dengan yang ada pada module accounting. Ini akan membuat opening balance per debtor bukan per akun
 */
class ObalController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ar/opening_balance.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = \Router::GetInstance()->GetRouteData();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.debtor_cd", "display" => "Creditor Code", "width" => 100);
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Creditor Name", "width" => 150);
        $settings["columns"][] = array("name" => "a.date", "display" => "Date", "width" => 70);
		$settings["columns"][] = array("name" => "FORMAT(a.debit_amount, 2)", "display" => "Debet", "width" => 100, "align" => "right");
		$settings["columns"][] = array("name" => "FORMAT(a.credit_amount, 2)", "display" => "Kredit", "width" => 100, "align" => "right");

		$settings["filters"][] = array("name" => "b.debtor_cd", "display" => "Debtor Code");
		$settings["filters"][] = array("name" => "b.debtor_name", "display" => "Debtor Name");

		if (!$router->IsAjaxRequest) {
			$acl = \AclManager::GetInstance();
			$settings["title"] = "A/R Debtor Opening";
			if ($acl->CheckUserAccess("ar.obal", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ar.obal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ar.obal", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ar.obal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Harap memilih opening balance sebelum proses edit !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ar.obal", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ar.obal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Harap memilih opening balance sebelum proses delete !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
					"Confirm" => "Apakah anda yakin mau menghapus data opening balance yang dipilih ?\nKlik 'OK' untuk melanjutkan prosedur delete.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = false;
		} else {
			$settings["from"] = "ar_opening_balance AS a JOIN ar_debtor_master AS b ON a.debtor_id = b.id JOIN cm_company AS c ON b.entity_id = c.entity_id";
		}

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(OpeningBalance $obal) {
		if ($obal->DebtorId == null) {
			$this->Set("error", "Harap memilih debtor terlebih dahulu");
			return false;
		}
		if (!is_int($obal->Date)) {
			$this->Set("error", "Tanggal saldo awal masih salah");
			return false;
		}
		if (!is_numeric($obal->DebitAmount)) {
			$this->Set("error", "Saldo Debit awal piutang debtor masih salah");
			return false;
		}
		if (!is_numeric($obal->CreditAmount)) {
			$this->Set("error", "Saldo Credit awal piutang debtor masih salah");
			return false;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/debtor.php");
        require_once(MODEL . "master/company.php");
        $company = new \Company($this->userCompanyId);

        $obal = new OpeningBalance();
        $obal->Date = mktime(0, 0, 0, 1, 1, left($company->StartDate,4));
        $obal->DebitAmount = 0;

		if (count($this->postData) > 0) {
			$obal->DebtorId = $this->GetPostValue("DebtorId");
			$obal->DebitAmount = str_replace(",", "", $this->GetPostValue("DebitAmount", "0"));
			$obal->CreditAmount = str_replace(",", "", $this->GetPostValue("CreditAmount", "0"));

			if ($this->ValidateData($obal)) {
				$obal->CreatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($obal->Insert() == 1) {
					$this->persistence->SaveState("info", "Data opening balance piutang debtor sudah berhasil disimpan");
					\Dispatcher::RedirectUrl("ar.obal");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf data opening balance debtor yang bersangkutan sudah ada.");
					} else {
						$this->Set("error", "Terjadi error ketika menyimpan data. Error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		} else {
			$obal->DebitAmount = 0;
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->Set("info", "Proses ini harus dilakukan pada login Company. Harap impersonate terlebih dahulu");
			}
		}

		$debtor = new \Debtor();

		$this->Set("obal", $obal);
		$this->Set("debtors", $debtor->LoadByEntity($this->userCompanyId));
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum proses edit.");
			\Dispatcher::RedirectUrl("ar.obal");
		}
		require_once(MODEL . "master/debtor.php");

		$obal = new OpeningBalance($id);
		if ($obal->Id != $id) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			\Dispatcher::RedirectUrl("ar.obal");
		}
		$debtor = new \Debtor($obal->DebtorId);

		if (count($this->postData) > 0) {
			$obal->DebitAmount = str_replace(",", "", $this->GetPostValue("DebitAmount", "0"));
			$obal->CreditAmount = str_replace(",", "", $this->GetPostValue("CreditAmount", "0"));

			if ($this->ValidateData($obal)) {
				$obal->UpdatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($obal->Update($obal->Id) == 1) {
					$this->persistence->SaveState("info", "Perubahan data opening balance piutang debtor sudah berhasil disimpan");
					\Dispatcher::RedirectUrl("ar.obal");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf data opening balance debtor yang bersangkutan sudah ada.");
					} else {
						$this->Set("error", "Terjadi error ketika menyimpan perubahan data. Error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		} else {
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($debtor->EntityId != $this->userCompanyId) {
					$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
					\Dispatcher::RedirectUrl("ar.obal");
				}
			}
		}

		$this->Set("obal", $obal);
		$this->Set("debtor", $debtor);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum proses edit.");
			\Dispatcher::RedirectUrl("ar.obal");
		}
		require_once(MODEL . "master/debtor.php");

		$obal = new OpeningBalance($id);
		if ($obal->Id != $id) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			\Dispatcher::RedirectUrl("ar.obal");
		}
		$debtor = new \Debtor($obal->DebtorId);
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			if ($debtor->EntityId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
				\Dispatcher::RedirectUrl("ar.obal");
			}
		}

		if ($obal->Delete($obal->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Saldo awal piutang debtor %s sudah dihapus.", $debtor->DebtorName));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus saldo awal piutang debtor %s. Error: %s", $debtor->DebtorName, $this->connector->GetErrorMessage()));
		}

		\Dispatcher::RedirectUrl("ar.obal");
	}
}

// End of File: obal_controller.php