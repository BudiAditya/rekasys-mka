<?php
class CreditorTypeController extends AppController {
    private $userCompanyId;

    protected function Initialize() {
        require_once(MODEL . "common/creditor_type.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
    }

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 80);
		$settings["columns"][] = array("name" => "a.creditortype_cd", "display" => "Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.creditortype_desc", "display" => "Vendor Type", "width" => 350);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Liabilities Account", "width" => 100);

		$settings["filters"][] = array("name" => "a.creditortype_cd", "display" => "Code");
		$settings["filters"][] = array("name" => "a.creditortype_desc", "display" => "Vendor Type");
		$settings["filters"][] = array("name" => "c.acc_no", "display" => "Liabilities Account");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
            $settings["title"] = "Vendor & Supplier Type";
			if ($acl->CheckUserAccess("creditortype", "add", "common")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.creditortype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("creditortype", "edit", "common")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.creditortype/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih jenis creditor terlebih dahulu sebelum melakukan proses edit !\n\nHarap memilih tepat 1 jenis creditor",
											   "Confirm" => "Apakah anda mau merubah data jenis creditor yang dipilih ?");
			}
			if ($acl->CheckUserAccess("creditortype", "delete", "common")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.creditortype/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih jenis creditor terlebih dahulu sebelum melakukan proses edit !\n\nHarap memilih tepat 1 jenis creditor",
											   "Confirm" => "Apakah anda mau menghapus data jenis creditor yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ap_creditortype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id Left Join cm_acc_detail AS c On a.acc_control_id = c.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

    public function add() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        $creditorType = new CreditorType();
        $loader = null;
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $creditorType->EntityId = $this->userCompanyId;
            $creditorType->CreditorTypeCd = $this->GetPostValue("CreditorTypeCd");
            $creditorType->CreditorTypeDesc = $this->GetPostValue("CreditorTypeDesc");
            $creditorType->AccControlId = $this->GetPostValue("AccControlId");
            if ($this->DoInsert($creditorType)) {
                $this->persistence->SaveState("info", sprintf("Data Creditor Type: '%s' Dengan Kode: %s telah berhasil disimpan.", $creditorType->CreditorTypeDesc, $creditorType->CreditorTypeCd));
                redirect_url("common.creditortype");
            } else {
                if ($this->connector->GetHasError()) {
                    if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
                        $this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $creditorType->CreditorTypeCd));
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
        $accounts = $loader->LoadLevel3ByFirstCode($this->userCompanyId,array("2"));
        $this->Set("accounts", $accounts);
        $this->Set("company", $company);
        $this->Set("creditorType", $creditorType);
    }

    private function DoInsert(CreditorType $creditorType) {
        if ($creditorType->CreditorTypeCd == "") {
            $this->Set("error", "Kode Type creditor masih kosong");
            return false;
        }

        if ($creditorType->CreditorTypeDesc == "") {
            $this->Set("error", "Type Creditor masih kosong");
            return false;
        }

        if ($creditorType->Insert() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        $creditorType = new CreditorType();
        $loader = null;
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $creditorType->Id = $id;
            $creditorType->EntityId = $this->userCompanyId;
            $creditorType->CreditorTypeCd = $this->GetPostValue("CreditorTypeCd");
            $creditorType->CreditorTypeDesc = $this->GetPostValue("CreditorTypeDesc");
            $creditorType->AccControlId = $this->GetPostValue("AccControlId");
            if ($this->DoUpdate($creditorType)) {
                $this->persistence->SaveState("info", sprintf("Data Creditor Type: '%s' Dengan Kode: %s telah berhasil diupdate.", $creditorType->CreditorTypeDesc, $creditorType->CreditorTypeCd));
                redirect_url("common.creditortype");
            } else {
                if ($this->connector->GetHasError()) {
                    if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
                        $this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $creditorType->CreditorTypeCd));
                    } else {
                        $this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
                    }
                }
            }
        } else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data Creditor Type sebelum melakukan edit data !");
                redirect_url("common.creditortype");
            }
            $creditorType = $creditorType->FindById($id);
            if ($creditorType == null) {
                $this->persistence->SaveState("error", "Data Creditor Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
                redirect_url("common.creditortype");
            }
        }

        // load combobox data
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $loader = new Coa();
        $accounts = $loader->LoadLevel3ByFirstCode($this->userCompanyId,array("2"));
        $this->Set("accounts", $accounts);
        $this->Set("company", $company);
        $this->Set("creditorType", $creditorType);
    }

    private function DoUpdate(CreditorType $creditorType) {
        if ($creditorType->CreditorTypeCd == "") {
            $this->Set("error", "Kode Creditor Type masih kosong");
            return false;
        }

        if ($creditorType->CreditorTypeDesc == "") {
            $this->Set("error", "Nama Creditor Type masih kosong");
            return false;
        }

        if ($creditorType->Update($creditorType->Id) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih data Creditor Type sebelum melakukan hapus data !");
            redirect_url("common.creditortype");
        }

        $creditorType = new CreditorType();
        $creditorType = $creditorType->FindById($id);
        if ($creditorType == null) {
            $this->persistence->SaveState("error", "Data Creditor Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("common.creditortype");
        }
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($creditorType->EntityId != $this->userCompanyId) {
				// Simulate not found ! Access data which belong to other Company without CORPORATE access level
				$this->persistence->SaveState("error", "Data Creditor Type yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.creditortype");
			}
		}

        if ($creditorType->Delete($creditorType->Id) == 1) {
            $this->persistence->SaveState("info", sprintf("Data Creditor Type: '%s' Dengan Kode: %s telah berhasil dihapus.", $creditorType->CreditorTypeDesc, $creditorType->CreditorTypeCd));
            redirect_url("common.creditortype");
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus data Creditor Type: '%s'. Message: %s", $creditorType->CreditorTypeDesc, $this->connector->GetErrorMessage()));
        }
        redirect_url("common.creditortype");
    }
}
