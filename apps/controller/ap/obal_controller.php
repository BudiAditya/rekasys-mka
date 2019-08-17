<?php
namespace Ap;

/**
 * Class ObalController
 * @package Ap
 *          Ini murni copy dari Ar\ObalController
 */
class ObalController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ap/opening_balance.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
	    require_once (MODEL . "master/company.php");

		$router = \Router::GetInstance()->GetRouteData();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.creditor_cd", "display" => "Creditor Code", "width" => 100);
		$settings["columns"][] = array("name" => "b.creditor_name", "display" => "Creditor Name", "width" => 250);
		$settings["columns"][] = array("name" => "a.date", "display" => "Date", "width" => 70);
		$settings["columns"][] = array("name" => "FORMAT(a.debit_amount, 2)", "display" => "Debet", "width" => 100, "align" => "right");
		$settings["columns"][] = array("name" => "FORMAT(a.credit_amount, 2)", "display" => "Kredit", "width" => 100, "align" => "right");

		$settings["filters"][] = array("name" => "b.creditor_cd", "display" => "Creditor Code");
		$settings["filters"][] = array("name" => "b.creditor_name", "display" => "Creditor Name");

		if (!$router->IsAjaxRequest) {
			$acl = \AclManager::GetInstance();
			$settings["title"] = "A/P Creditor Opening";
			if ($acl->CheckUserAccess("ap.obal", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ap.obal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ap.obal", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ap.obal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Harap memilih opening balance sebelum proses edit !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ap.obal", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ap.obal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Harap memilih opening balance sebelum proses delete !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
					"Confirm" => "Apakah anda yakin mau menghapus data opening balance yang dipilih ?\nKlik 'OK' untuk melanjutkan prosedur delete.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = false;
		} else {
			$settings["from"] =
"ap_opening_balance AS a
	JOIN ap_creditor_master AS b ON a.creditor_id = b.id
	JOIN cm_company AS c ON b.entity_id = c.entity_id";
		}

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(OpeningBalance $obal) {
		if ($obal->CreditorId == null) {
			$this->Set("error", "Harap memilih Creditor terlebih dahulu");
			return false;
		}
		if (!is_int($obal->Date)) {
			$this->Set("error", "Tanggal saldo awal masih salah");
			return false;
		}
		if (!is_numeric($obal->DebitAmount)) {
			$this->Set("error", "Saldo Awal Debit hutang Creditor masih salah");
			return false;
		}
		if (!is_numeric($obal->CreditAmount)) {
			$this->Set("error", "Saldo Awal Credit hutang Creditor masih salah");
			return false;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/company.php");
        $company = new \Company($this->userCompanyId);

		$obal = new OpeningBalance();
		$obal->Date = mktime(0, 0, 0, 1, 1, left($company->StartDate,4));
		$obal->DebitAmount = 0;

		if (count($this->postData) > 0) {
			$obal->CreditorId = $this->GetPostValue("CreditorId");
			$obal->DebitAmount = str_replace(",", "", $this->GetPostValue("DebitAmount", "0"));
			$obal->CreditAmount = str_replace(",", "", $this->GetPostValue("CreditAmount", "0"));

			if ($this->ValidateData($obal)) {
				$obal->CreatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($obal->Insert() == 1) {
					$this->persistence->SaveState("info", "Data opening balance hutang Creditor sudah berhasil disimpan");
					\Dispatcher::RedirectUrl("ap.obal");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf data opening balance Creditor yang bersangkutan sudah ada.");
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

		$creditor = new \Creditor();

		$this->Set("obal", $obal);
		$this->Set("creditors", $creditor->LoadByEntity($this->userCompanyId));
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum proses edit.");
			\Dispatcher::RedirectUrl("ap.obal");
		}
		require_once(MODEL . "master/creditor.php");

		$obal = new OpeningBalance($id);
		if ($obal->Id != $id) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			\Dispatcher::RedirectUrl("ap.obal");
		}
		$creditor = new \Creditor($obal->CreditorId);

		if (count($this->postData) > 0) {
			$obal->DebitAmount = str_replace(",", "", $this->GetPostValue("DebitAmount", "0"));
			$obal->CreditAmount = str_replace(",", "", $this->GetPostValue("CreditAmount", "0"));

			if ($this->ValidateData($obal)) {
				$obal->UpdatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($obal->Update($obal->Id) == 1) {
					$this->persistence->SaveState("info", "Perubahan data opening balance hutang Creditor sudah berhasil disimpan");
					\Dispatcher::RedirectUrl("ap.obal");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf data opening balance Creditor yang bersangkutan sudah ada.");
					} else {
						$this->Set("error", "Terjadi error ketika menyimpan perubahan data. Error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		} else {
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($creditor->EntityId != $this->userCompanyId) {
					$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
					\Dispatcher::RedirectUrl("ap.obal");
				}
			}
		}

		$this->Set("obal", $obal);
		$this->Set("creditor", $creditor);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum proses edit.");
			\Dispatcher::RedirectUrl("ap.obal");
		}
		require_once(MODEL . "master/creditor.php");

		$obal = new OpeningBalance($id);
		if ($obal->Id != $id) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			\Dispatcher::RedirectUrl("ap.obal");
		}
		$creditor = new \Creditor($obal->CreditorId);
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			if ($creditor->EntityId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
				\Dispatcher::RedirectUrl("ap.obal");
			}
		}

		if ($obal->Delete($obal->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Saldo awal hutang Creditor %s sudah dihapus.", $creditor->CreditorName));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus saldo awal hutang Creditor %s. Error: %s", $creditor->CreditorName, $this->connector->GetErrorMessage()));
		}

		\Dispatcher::RedirectUrl("ap.obal");
	}
}

// End of File: obal_controller.php 