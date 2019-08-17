<?php

namespace Hr;


class ObalController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");

		require_once(MODEL . "hr/opening_balance.php");
	}

	public function index() {
		$router = \Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "c.entity_cd", "display" => "Company", "width" => 80);
		$settings["columns"][] = array("name" => "b.nama", "display" => "Employee Name", "width" => 200);
		$settings["columns"][] = array("name" => "b.nik", "display" => "N I K", "width" => 80);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.date, '%d-%m-%Y')", "display" => "Per Date", "width" => 100);
		$settings["columns"][] = array("name" => "FORMAT(a.debit_amount, 2)", "display" => "Loan Balance", "width" => 100, "align" => "right");

		$settings["filters"][] = array("name" => "b.nama", "display" => "Employee Name");
		$settings["filters"][] = array("name" => "b.nik", "display" => "NIK");

		if (!$router->IsAjaxRequest) {
			$acl = \AclManager::GetInstance();
			$settings["title"] = "Employee Loan Opening";

			if ($acl->CheckUserAccess("hr.obal", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "hr.obal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("hr.obal", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "hr.obal/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if ($acl->CheckUserAccess("hr.obal", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "hr.obal/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

		} else {
			$settings["from"] = "hr_opening_balance AS a JOIN hr_employee_master AS b ON a.employee_id = b.id JOIN cm_company AS c ON a.entity_id = c.entity_id";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		\Dispatcher::CreateInstance()->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(OpeningBalance $obal) {
		if ($obal->EmployeeId == null) {
			$this->Set("error", "Harap memilih karyawan terlebih dahulu");
			return false;
		}
		if (!is_int($obal->Date)) {
			$this->Set("error", "Tanggal saldo awal masih salah");
			return false;
		}
		if (!is_numeric($obal->CreditAmount)) {
			$this->Set("error", "Saldo awal piutang karyawan masih salah");
			return false;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "hr/employee.php");
        require_once(MODEL . "master/company.php");
        $cmp = new \Company($this->userCompanyId);
        //$year = date('Y',$cmp->StartDate);
		$obal = new OpeningBalance();
		//$obal->Date = mktime(0, 0, 0, 1, 1, 2013);
        $obal->Date = strtotime($cmp->StartDate);
		$obal->DebitAmount = 0;

		if (count($this->postData) > 0) {
		    $obal->EntityId = $this->userCompanyId;
			$obal->EmployeeId = $this->GetPostValue("EmployeeId");
			$obal->DebitAmount = str_replace(",", "", $this->GetPostValue("DebitAmount"));

			if ($this->ValidateData($obal)) {
				$obal->CreatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($obal->Insert() == 1) {
					$this->persistence->SaveState("info", "Data opening balance piutang karyawan sudah berhasil disimpan");
					\Dispatcher::RedirectUrl("hr.obal");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf data opening balance karyawan yang bersangkutan sudah ada.");
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

		$employee = new \Employee();

		$this->Set("obal", $obal);
		$this->Set("employees", $employee->LoadByEntityId($this->userCompanyId));
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum proses edit.");
			\Dispatcher::RedirectUrl("hr.obal");
		}
		require_once(MODEL . "hr/employee.php");

		$obal = new OpeningBalance($id);
		if ($obal->Id != $id) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			\Dispatcher::RedirectUrl("hr.obal");
		}
		$employee = new \Employee($obal->EmployeeId, true);

		if (count($this->postData) > 0) {
            $obal->EntityId = $this->userCompanyId;
			$obal->DebitAmount = str_replace(",", "", $this->GetPostValue("DebitAmount"));

			if ($this->ValidateData($obal)) {
				$obal->UpdatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($obal->Update($obal->Id) == 1) {
					$this->persistence->SaveState("info", "Perubahan data opening balance piutang karyawan sudah berhasil disimpan");
					\Dispatcher::RedirectUrl("hr.obal");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf data opening balance karyawan yang bersangkutan sudah ada.");
					} else {
						$this->Set("error", "Terjadi error ketika menyimpan perubahan data. Error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		} else {
			if ($employee->EntityId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
				\Dispatcher::RedirectUrl("hr.obal");
			}
		}

		$this->Set("obal", $obal);
		$this->Set("employee", $employee);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum proses edit.");
			\Dispatcher::RedirectUrl("hr.obal");
		}
		require_once(MODEL . "hr/employee.php");

		$obal = new OpeningBalance($id);
		if ($obal->Id != $id) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			\Dispatcher::RedirectUrl("hr.obal");
		}
		$employee = new \Employee($obal->EmployeeId);
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			if ($employee->CompanyId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
				\Dispatcher::RedirectUrl("hr.obal");
			}
		}

		if ($obal->Delete($obal->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Saldo awal piutang karyawan %s sudah dihapus.", $employee->Name));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus saldo awal piutang karyawan %s. Error: %s", $employee->Name, $this->connector->GetErrorMessage()));
		}

		\Dispatcher::RedirectUrl("hr.obal");
	}
}

// EoF: obal_controller.php