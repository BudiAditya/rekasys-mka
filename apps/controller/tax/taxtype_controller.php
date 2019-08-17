<?php
class TaxTypeController extends AppController {
    private $userCompanyId;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "tax/taxtype.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
    }

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Perusahaan", "width" => 80);
		$settings["columns"][] = array("name" => "a.tax_code", "display" => "Kode", "width" => 80);
		$settings["columns"][] = array("name" => "a.tax_type", "display" => "Nama Pajak", "width" => 250);
        $settings["columns"][] = array("name" => "if(a.tax_mode = 1,'Masukan','Keluaran')", "display" => "Jenis", "width" => 100);
		$settings["columns"][] = array("name" => "format(a.tax_rate,2)", "display" => "Tarif (%)", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "d.acc_no", "display" => "Temp Account", "width" => 80);
        $settings["columns"][] = array("name" => "c.acc_no", "display" => "Post Account", "width" => 80);

		$settings["filters"][] = array("name" => "a.tax_code", "display" => "Code");
		$settings["filters"][] = array("name" => "a.tax_type", "display" => "Tax Type");
		$settings["filters"][] = array("name" => "d.acc_no", "display" => "Temp Account");
        $settings["filters"][] = array("name" => "c.acc_no", "display" => "Post Account");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
            $settings["title"] = "Taxes Type";
			if ($acl->CheckUserAccess("taxtype", "add", "common")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "tax.taxtype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("taxtype", "edit", "common")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "tax.taxtype/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih jenis pajak terlebih dahulu sebelum melakukan proses edit !\n\nHarap memilih tepat 1 jenis pajak",
											   "Confirm" => "Apakah anda mau merubah data jenis pajak yang dipilih ?");
			}
			if ($acl->CheckUserAccess("taxtype", "delete", "common")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "tax.taxtype/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih jenis pajak terlebih dahulu sebelum melakukan proses edit !\n\nHarap memilih tepat 1 jenis pajak",
											   "Confirm" => "Apakah anda mau menghapus data jenis pajak yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_taxtype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id Left Join cm_acc_detail AS c On a.post_acc_id = c.id Left Join cm_acc_detail AS d On a.temp_acc_id = d.id";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

    public function add() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        $taxType = new TaxType();
        $loader = null;
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $taxType->EntityId = $this->userCompanyId;
            $taxType->TaxCode = $this->GetPostValue("TaxCode");
            $taxType->TaxType = $this->GetPostValue("TaxType");
            $taxType->TaxMode = $this->GetPostValue("TaxMode");
            $taxType->TaxRate = $this->GetPostValue("TaxRate");
            $taxType->IsDeductable = $this->GetPostValue("IsDeductable");
            $taxType->PostAccId = $this->GetPostValue("PostAccId");
            $taxType->TempAccId = $this->GetPostValue("TempAccId");
            $taxType->CreatebyId = $this->userUid;
            if ($this->DoInsert($taxType)) {
                $this->persistence->SaveState("info", sprintf("Data Tax Type: '%s' Dengan Kode: %s telah berhasil disimpan.", $taxType->TaxType, $taxType->TaxCode));
                redirect_url("tax.taxtype");
            } else {
                if ($this->connector->GetHasError()) {
                    if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
                        $this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $taxType->TaxCode));
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
        $this->Set("taxType", $taxType);
    }

    private function DoInsert(TaxType $taxType) {
        if ($taxType->TaxCode == "") {
            $this->Set("error", "Kode Type tax masih kosong");
            return false;
        }

        if ($taxType->TaxType == "") {
            $this->Set("error", "Type Tax masih kosong");
            return false;
        }

        if ($taxType->Insert() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        $taxType = new TaxType();
        $loader = null;
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $taxType->Id = $id;
            $taxType->EntityId = $this->userCompanyId;
            $taxType->TaxCode = $this->GetPostValue("TaxCode");
            $taxType->TaxType = $this->GetPostValue("TaxType");
            $taxType->TaxMode = $this->GetPostValue("TaxMode");
            $taxType->TaxRate = $this->GetPostValue("TaxRate");
            $taxType->IsDeductable = $this->GetPostValue("IsDeductable");
            $taxType->PostAccId = $this->GetPostValue("PostAccId");
            $taxType->TempAccId = $this->GetPostValue("TempAccId");
            $taxType->UpdatebyId = $this->userUid;
            if ($this->DoUpdate($taxType)) {
                $this->persistence->SaveState("info", sprintf("Data Tax Type: '%s' Dengan Kode: %s telah berhasil diupdate.", $taxType->TaxType, $taxType->TaxCode));
                redirect_url("tax.taxtype");
            } else {
                if ($this->connector->GetHasError()) {
                    if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
                        $this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $taxType->TaxCode));
                    } else {
                        $this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
                    }
                }
            }
        } else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data Tax Type sebelum melakukan edit data !");
                redirect_url("tax.taxtype");
            }
            $taxType = $taxType->FindById($id);
            if ($taxType == null) {
                $this->persistence->SaveState("error", "Data Tax Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
                redirect_url("tax.taxtype");
            }
        }

        // load combobox data
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $loader = new Coa();
        $accounts = $loader->LoadByLevel($this->userCompanyId,3);
        $this->Set("accounts", $accounts);
        $this->Set("company", $company);
        $this->Set("taxType", $taxType);
    }

    private function DoUpdate(TaxType $taxType) {
        if ($taxType->TaxCode == "") {
            $this->Set("error", "Kode Tax Type masih kosong");
            return false;
        }

        if ($taxType->TaxType == "") {
            $this->Set("error", "Nama Tax Type masih kosong");
            return false;
        }

        if ($taxType->Update($taxType->Id) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih data Tax Type sebelum melakukan hapus data !");
            redirect_url("tax.taxtype");
        }

        $taxType = new TaxType();
        $taxType = $taxType->FindById($id);
        if ($taxType == null) {
            $this->persistence->SaveState("error", "Data Tax Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("tax.taxtype");
        }

        if ($taxType->Delete($taxType->Id) == 1) {
            $this->persistence->SaveState("info", sprintf("Data Tax Type: '%s' Dengan Kode: %s telah berhasil dihapus.", $taxType->TaxType, $taxType->TaxCode));
            redirect_url("tax.taxtype");
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus data Tax Type: '%s'. Message: %s", $taxType->TaxType, $this->connector->GetErrorMessage()));
        }
        redirect_url("tax.taxtype");
    }
}
