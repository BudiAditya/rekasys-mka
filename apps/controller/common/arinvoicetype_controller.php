<?php
class ArInvoiceTypeController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/ar_invoice_type.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.invoice_prefix", "display" => "Prefix", "width" => 50);
        $settings["columns"][] = array("name" => "a.invoice_type", "display" => "Type", "width" => 100);
		$settings["columns"][] = array("name" => "a.invoice_type_descs", "display" => "Description", "width" => 200);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Revenue Account", "width" => 100);
        $settings["columns"][] = array("name" => "c.acc_name", "display" => "Revenue Account Name", "width" => 200);
        $settings["columns"][] = array("name" => "d.taxsch_desc", "display" => "Tax Scheme", "width" => 200);

		$settings["filters"][] = array("name" => "a.invoice_type", "display" => "Type");
		$settings["filters"][] = array("name" => "a.invoice_type_descs", "display" => "Invoice Type");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "A/R Invoice Type";
			if($acl->CheckUserAccess("arinvoicetype", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.arinvoicetype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("arinvoicetype", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.arinvoicetype/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("arinvoicetype", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.arinvoicetype/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ar_invoicetype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id JOIN cm_acc_detail AS c ON a.rev_acc_id = c.id LEFT JOIN cm_taxschmaster AS d On a.taxscheme_id = d.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        require_once(MODEL . "common/tax_rate.php");
		$arinvoiceType = new ArInvoiceType();
		$loader = null;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$arinvoiceType->EntityId = $this->userCompanyId;
			$arinvoiceType->InvoiceType = $this->GetPostValue("InvoiceType");
            $arinvoiceType->InvoicePrefix = $this->GetPostValue("InvoicePrefix");
			$arinvoiceType->InvoiceTypeDescs = $this->GetPostValue("InvoiceTypeDescs");
			$arinvoiceType->RevAccId = $this->GetPostValue("RevAccId");
            $arinvoiceType->TaxSchemeId = $this->GetPostValue("TaxSchemeId");
			if ($this->DoInsert($arinvoiceType)) {
				$this->persistence->SaveState("info", sprintf("Data Invoice Type: '%s' Dengan Kode: %s telah berhasil disimpan.", $arinvoiceType->InvoiceTypeDescs, $arinvoiceType->InvoiceType));
				redirect_url("common.arinvoicetype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $arinvoiceType->InvoiceType));
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
		$this->Set("arinvoicetype", $arinvoiceType);
	}

	private function DoInsert(ArInvoiceType $arinvoiceType) {
		if ($arinvoiceType->InvoiceType == "") {
			$this->Set("error", "Kode Type Invoice masih kosong");
			return false;
		}

        if ($arinvoiceType->InvoicePrefix == "") {
            $this->Set("error", "Prefix Type Invoice masih kosong");
            return false;
        }

		if ($arinvoiceType->InvoiceTypeDescs == "") {
			$this->Set("error", "Type Invoice masih kosong");
			return false;
		}
		if ($arinvoiceType->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
        require_once(MODEL . "common/tax_rate.php");
		$arinvoiceType = new ArInvoiceType();
		$loader = null;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$arinvoiceType->Id = $id;
            $arinvoiceType->EntityId = $this->userCompanyId;
            $arinvoiceType->InvoiceType = $this->GetPostValue("InvoiceType");
            $arinvoiceType->InvoicePrefix = $this->GetPostValue("InvoicePrefix");
            $arinvoiceType->InvoiceTypeDescs = $this->GetPostValue("InvoiceTypeDescs");
            $arinvoiceType->RevAccId = $this->GetPostValue("RevAccId");
            $arinvoiceType->TaxSchemeId = $this->GetPostValue("TaxSchemeId");
			if ($this->DoUpdate($arinvoiceType)) {
				$this->persistence->SaveState("info", sprintf("Data Invoice Type: '%s' Dengan Kode: %s telah berhasil diupdate.", $arinvoiceType->InvoiceTypeDescs, $arinvoiceType->InvoiceType));
				redirect_url("common.arinvoicetype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $arinvoiceType->InvoiceType));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data Invoice Type sebelum melakukan edit data !");
				redirect_url("common.arinvoicetype");
			}
			$arinvoiceType = $arinvoiceType->FindById($id);
			if ($arinvoiceType == null) {
				$this->persistence->SaveState("error", "Data Invoice Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.arinvoicetype");
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
        $this->Set("arinvoicetype", $arinvoiceType);
	}

	private function DoUpdate(ArInvoiceType $arinvoiceType) {
		if ($arinvoiceType->InvoiceType == "") {
			$this->Set("error", "Kode Invoice Type masih kosong");
			return false;
		}

        if ($arinvoiceType->InvoicePrefix == "") {
            $this->Set("error", "Prefix Type Invoice masih kosong");
            return false;
        }

		if ($arinvoiceType->InvoiceTypeDescs == "") {
			$this->Set("error", "Nama Invoice Type masih kosong");
			return false;
		}

		if ($arinvoiceType->Update($arinvoiceType->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Invoice Type sebelum melakukan hapus data !");
			redirect_url("common.arinvoicetype");
		}
		$arinvoiceType = new ArInvoiceType();
		$arinvoiceType = $arinvoiceType->FindById($id);
		if ($arinvoiceType == null) {
			$this->persistence->SaveState("error", "Data Invoice Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.arinvoicetype");
		}
		if ($arinvoiceType->Delete($arinvoiceType->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Invoice Type: '%s' Dengan Kode: %s telah berhasil dihapus.", $arinvoiceType->InvoiceTypeDescs, $arinvoiceType->InvoiceType));
			redirect_url("common.arinvoicetype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Invoice Type: '%s'. Message: %s", $arinvoiceType->InvoiceTypeDescs, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.arinvoicetype");
	}
}
