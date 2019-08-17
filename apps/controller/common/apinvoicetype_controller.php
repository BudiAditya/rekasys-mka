<?php
class ApInvoiceTypeController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/ap_invoice_type.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.invoice_prefix", "display" => "Prefix", "width" => 50);
        $settings["columns"][] = array("name" => "a.invoice_type", "display" => "Type", "width" => 100);
		$settings["columns"][] = array("name" => "a.invoice_type_descs", "display" => "Description", "width" => 200);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Control Account", "width" => 100);
        $settings["columns"][] = array("name" => "c.acc_name", "display" => "Account Name", "width" => 200);
        $settings["columns"][] = array("name" => "d.taxsch_desc", "display" => "Tax Scheme", "width" => 200);

		$settings["filters"][] = array("name" => "a.invoice_type", "display" => "Type");
		$settings["filters"][] = array("name" => "a.invoice_type_descs", "display" => "Invoice Type");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "A/P Invoice Type";
			if($acl->CheckUserAccess("apinvoicetype", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.apinvoicetype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("apinvoicetype", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.apinvoicetype/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("apinvoicetype", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.apinvoicetype/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ap_invoicetype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_acc_detail AS c ON a.ctl_acc_id = c.id LEFT JOIN cm_taxschmaster AS d On a.taxscheme_id = d.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        require_once(MODEL . "common/tax_rate.php");
		$apinvoiceType = new ApInvoiceType();
		$loader = null;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$apinvoiceType->EntityId = $this->userCompanyId;
			$apinvoiceType->InvoiceType = $this->GetPostValue("InvoiceType");
            $apinvoiceType->InvoicePrefix = $this->GetPostValue("InvoicePrefix");
			$apinvoiceType->InvoiceTypeDescs = $this->GetPostValue("InvoiceTypeDescs");
			$apinvoiceType->CtlAccId = $this->GetPostValue("CtlAccId");
            $apinvoiceType->TaxSchemeId = $this->GetPostValue("TaxSchemeId");
			if ($this->DoInsert($apinvoiceType)) {
				$this->persistence->SaveState("info", sprintf("Data Invoice Type: '%s' Dengan Kode: %s telah berhasil disimpan.", $apinvoiceType->InvoiceTypeDescs, $apinvoiceType->InvoiceType));
				redirect_url("common.apinvoicetype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $apinvoiceType->InvoiceType));
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
        $loader = new TaxRate();
        $taxscheme = $loader->LoadByEntityId($this->userCompanyId);
		$this->Set("accounts", $accounts);
		$this->Set("company", $company);
        $this->Set("taxscheme", $taxscheme);
		$this->Set("apinvoicetype", $apinvoiceType);
	}

	private function DoInsert(ApInvoiceType $apinvoiceType) {
		if ($apinvoiceType->InvoiceType == "") {
			$this->Set("error", "Kode Type Invoice masih kosong");
			return false;
		}

        if ($apinvoiceType->InvoicePrefix == "") {
            $this->Set("error", "Prefix Type Invoice masih kosong");
            return false;
        }

		if ($apinvoiceType->InvoiceTypeDescs == "") {
			$this->Set("error", "Type Invoice masih kosong");
			return false;
		}
		if ($apinvoiceType->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
        require_once(MODEL . "common/tax_rate.php");
		$apinvoiceType = new ApInvoiceType();
		$loader = null;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$apinvoiceType->Id = $id;
            $apinvoiceType->EntityId = $this->userCompanyId;
            $apinvoiceType->InvoiceType = $this->GetPostValue("InvoiceType");
            $apinvoiceType->InvoicePrefix = $this->GetPostValue("InvoicePrefix");
            $apinvoiceType->InvoiceTypeDescs = $this->GetPostValue("InvoiceTypeDescs");
            $apinvoiceType->CtlAccId = $this->GetPostValue("CtlAccId");
            $apinvoiceType->TaxSchemeId = $this->GetPostValue("TaxSchemeId");
			if ($this->DoUpdate($apinvoiceType)) {
				$this->persistence->SaveState("info", sprintf("Data Invoice Type: '%s' Dengan Kode: %s telah berhasil diupdate.", $apinvoiceType->InvoiceTypeDescs, $apinvoiceType->InvoiceType));
				redirect_url("common.apinvoicetype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $apinvoiceType->InvoiceType));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data Invoice Type sebelum melakukan edit data !");
				redirect_url("common.apinvoicetype");
			}
			$apinvoiceType = $apinvoiceType->FindById($id);
			if ($apinvoiceType == null) {
				$this->persistence->SaveState("error", "Data Invoice Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.apinvoicetype");
			}
		}

		// load combobox data
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $loader = new Coa();
        $accounts = $loader->LoadByLevel($this->userCompanyId,3);
        $loader = new TaxRate();
        $taxscheme = $loader->LoadByEntityId($this->userCompanyId);
        $this->Set("accounts", $accounts);
        $this->Set("company", $company);
        $this->Set("taxscheme", $taxscheme);
        $this->Set("apinvoicetype", $apinvoiceType);
	}

	private function DoUpdate(ApInvoiceType $apinvoiceType) {
		if ($apinvoiceType->InvoiceType == "") {
			$this->Set("error", "Kode Invoice Type masih kosong");
			return false;
		}

        if ($apinvoiceType->InvoicePrefix == "") {
            $this->Set("error", "Prefix Type Invoice masih kosong");
            return false;
        }

		if ($apinvoiceType->InvoiceTypeDescs == "") {
			$this->Set("error", "Nama Invoice Type masih kosong");
			return false;
		}

		if ($apinvoiceType->Update($apinvoiceType->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Invoice Type sebelum melakukan hapus data !");
			redirect_url("common.apinvoicetype");
		}
		$apinvoiceType = new ApInvoiceType();
		$apinvoiceType = $apinvoiceType->FindById($id);
		if ($apinvoiceType == null) {
			$this->persistence->SaveState("error", "Data Invoice Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.apinvoicetype");
		}
		if ($apinvoiceType->Delete($apinvoiceType->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Invoice Type: '%s' Dengan Kode: %s telah berhasil dihapus.", $apinvoiceType->InvoiceTypeDescs, $apinvoiceType->InvoiceType));
			redirect_url("common.apinvoicetype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Invoice Type: '%s'. Message: %s", $apinvoiceType->InvoiceTypeDescs, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.apinvoicetype");
	}
}
