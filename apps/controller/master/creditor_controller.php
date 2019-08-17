<?php
class CreditorController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/creditor.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "a.creditor_cd", "display" => "Code", "width" => 60);
		$settings["columns"][] = array("name" => "c.creditortype_cd", "display" => "Type", "width" => 60);
		$settings["columns"][] = array("name" => "a.creditor_name", "display" => "Vendor Name", "width" => 200);
		$settings["columns"][] = array("name" => "a.address1", "display" => "Address", "width" => 300);
		$settings["columns"][] = array("name" => "a.core_business", "display" => "Core Business", "width" => 300);

		$settings["filters"][] = array("name" => "a.creditor_name", "display" => "Vendor Name");
		$settings["filters"][] = array("name" => "c.creditortype_cd", "display" => "Vendor Type");
		$settings["filters"][] = array("name" => "a.core_business", "display" => "Core Business");
		$settings["filters"][] = array("name" => "a.creditor_cd", "display" => "Vendor Code");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Vendor & Supplier Master";

			if ($acl->CheckUserAccess("creditor", "add", "master")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.creditor/add", "Class" => "bt_add", "ReqId" => 0);
			}
            if ($acl->CheckUserAccess("creditor", "edit", "master")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "master.creditor/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Creditor terlebih dahulu sebelum proses edit !\nPERHATIAN: Pastikan anda memilih tepat 1 creditor.",
                    "Confirm" => "Apakah anda yakin mau mengedit data creditor yang dipilih ?");
            }
			if ($acl->CheckUserAccess("creditor", "view", "master")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "master.creditor/view/%s", "Class" => "bt_view", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih Creditor terlebih dahulu sebelum dapat melihat data !\nPERHATIAN: Pastikan anda memilih tepat 1 creditor.",
											   "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("creditor", "delete", "master")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.creditor/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih Creditor terlebih dahulu sebelum proses edit !\nPERHATIAN: Pastikan anda memilih tepat 1 creditor.",
											   "Confirm" => "Apakah anda yakin mau menghapus data creditor yang dipilih ?\nPERHATIAN: pastikan anda sudah memilih data dengan benar");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ap_creditor_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id JOIN ap_creditortype AS c ON a.creditortype_id = c.id";
            $settings["where"] = "a.is_deleted = 0 And a.entity_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Creditor $creditor) {
		if ($creditor->CreditorCd == "") {
			$this->Set("error", "Kode Creditor masih kosong");
			return false;
		}
		if ($creditor->CreditorName == "") {
			$this->Set("error", "Nama Creditor masih kosong");
			return false;
		}
		if ($creditor->PhoneNo == "") {
			$this->Set("error", "No. Telepon masih kosong");
			return false;
		}
		if ($creditor->ContactPerson == "") {
			$this->Set("error", "Contact Person masih kosong");
			return false;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/creditor_type.php");

		$creditor = new Creditor();
		$loader = null;

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$creditor->EntityId = $this->userCompanyId;
			$creditor->CreditorCd = $this->GetPostValue("CreditorCd");
			$creditor->CreditorName = $this->GetPostValue("CreditorName");
			$creditor->CreditorTypeId = $this->GetPostValue("CreditorTypeId");
			$creditor->Address1 = $this->GetPostValue("Address1");
			$creditor->Address2 = $this->GetPostValue("Address2");
			$creditor->Address3 = $this->GetPostValue("Address3");
			$creditor->PostalCode = $this->GetPostValue("PostCd");
			$creditor->PhoneNo = $this->GetPostValue("TelNo");
			$creditor->HandPhone = $this->GetPostValue("HandPhone");
			$creditor->FaxNo = $this->GetPostValue("FaxNo");
			$creditor->Remark = $this->GetPostValue("Remark");
			$creditor->Npwp = $this->GetPostValue("Npwp");
			$creditor->ContactPerson = $this->GetPostValue("ContactPerson");
			$creditor->Position = $this->GetPostValue("Position");
			$creditor->EmailAddress = $this->GetPostValue("EmailAdd");
			$creditor->WebSite = $this->GetPostValue("WebSite");
			$creditor->CoreBusiness = $this->GetPostValue("CoreBusiness");
			$creditor->BankAccount = $this->GetPostValue("BankAccount");

			if ($this->ValidateData($creditor)) {
				$creditor->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				if ($creditor->Insert() == 1) {
					$this->persistence->SaveState("info", sprintf("Data Creditor: '%s' telah berhasil disimpan.", $creditor->CreditorName));
					redirect_url("master.creditor");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Creditor: '%s' telah ada pada database !", $creditor->CreditorCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$loader = new CreditorType();
		$creditorTypes = $loader->LoadByEntity($this->userCompanyId);
		$this->Set("company", $company);
		$this->Set("creditorTypes", $creditorTypes);
		$this->Set("creditor", $creditor);
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/creditor_type.php");
		$loader = null;
		$creditor = new Creditor();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$creditor->Id = $id;
			$creditor->CreditorCd = $this->GetPostValue("CreditorCd");
			$creditor->CreditorName = $this->GetPostValue("CreditorName");
			$creditor->CreditorTypeId = $this->GetPostValue("CreditorTypeId");
			$creditor->Address1 = $this->GetPostValue("Address1");
			$creditor->Address2 = $this->GetPostValue("Address2");
			$creditor->Address3 = $this->GetPostValue("Address3");
			$creditor->PostalCode = $this->GetPostValue("PostCd");
			$creditor->PhoneNo = $this->GetPostValue("TelNo");
			$creditor->HandPhone = $this->GetPostValue("HandPhone");
			$creditor->FaxNo = $this->GetPostValue("FaxNo");
			$creditor->Remark = $this->GetPostValue("Remark");
			$creditor->Npwp = $this->GetPostValue("Npwp");
			$creditor->ContactPerson = $this->GetPostValue("ContactPerson");
			$creditor->Position = $this->GetPostValue("Position");
			$creditor->EmailAddress = $this->GetPostValue("EmailAdd");
			$creditor->WebSite = $this->GetPostValue("WebSite");
			$creditor->CoreBusiness = $this->GetPostValue("CoreBusiness");
			$creditor->BankAccount = $this->GetPostValue("BankAccount");

			if ($this->ValidateData($creditor)) {
				$creditor->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				if ($creditor->Update($creditor->Id) == 1) {
					$this->persistence->SaveState("info", sprintf("Data Creditor: '%s' telah berhasil diupdate.", $creditor->CreditorName));
					redirect_url("master.creditor");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Creditor: '%s' telah ada pada database !", $creditor->CreditorCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data creditor sebelum melakukan edit data !");
				redirect_url("master.creditor");
			}
			$creditor = $creditor->FindById($id);
			if ($creditor == null || $creditor->IsDeleted) {
				$this->persistence->SaveState("error", "Data Creditor yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.creditor");
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($creditor->EntityId != $this->userCompanyId) {
					// AKSES DATA BEDA Company KICK!!!!
					$this->persistence->SaveState("error", "Maaf, Data creditor yang dipilih tidak boleh diedit oleh anda..");
					redirect_url("master.creditor");
					return;
				}
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$loader = new CreditorType();
		$creditorTypes = $loader->LoadByEntity($this->userCompanyId);
		$this->Set("company", $company);
		$this->Set("creditorTypes", $creditorTypes);
		$this->Set("creditor", $creditor);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data creditor sebelum melakukan hapus data !");
			redirect_url("master.creditor");
		}

		$creditor = new Creditor();
		$creditor = $creditor->FindById($id);
		if ($creditor == null) {
			$this->persistence->SaveState("error", "Data creditor yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.creditor");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($creditor->EntityId != $this->userCompanyId) {
				// AKSES DATA BEDA Company KICK!!!!
				$this->persistence->SaveState("error", "Maaf, Data creditor yang dipilih tidak boleh dihapus oleh anda..");
				redirect_url("master.creditor");
				return;
			}
		}

		if ($creditor->Delete($creditor->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Creditor: '%s' Dengan Kode: %s telah berhasil dihapus.", $creditor->CreditorName, $creditor->CreditorCd));
			redirect_url("master.creditor");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data creditor: '%s'. Message: %s", $creditor->CreditorName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.creditor");
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data creditor terlebih dahulu sebelum melihat data !");
			redirect_url("master.creditor");
		}

		$creditor = new Creditor();
		$creditor = $creditor->FindById($id);
		if ($creditor == null) {
			$this->persistence->SaveState("error", "Data creditor yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.creditor");
		}
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/creditor_type.php");

		$company = new Company();
		$company = $company->FindById($creditor->EntityId);
		$creditorType = new CreditorType();
		$creditorType = $creditorType->FindById($creditor->CreditorTypeId);

		$this->Set("company", $company);
		$this->Set("creditor", $creditor);
		$this->Set("creditorType", $creditorType);
	}

	public function autocreditorcd($creditorname) {
		$creditor = new Creditor();
		$creditorcd = $creditor->GetAutoCode($creditorname);
		print($creditorcd);
	}

    //halaman search data
    public function overview() {

        require_once(MODEL. "master/creditor.php");
        require_once(MODEL. "common/creditor_type.php");

        $creditor = new Creditor();

        if(count($this->getData) > 0 ) {
            $typeId = $this->GetGetValue("jenis");
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.entity_cd AS entity, c.creditortype_desc AS type_desc
                                            FROM ap_creditor_master AS a
                                            JOIN cm_company AS b ON a.entity_id = b.entity_id
                                            JOIN ap_creditortype AS c ON a.creditortype_id = c.id
                                            WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7) {
                $this->connector->CommandText .= " AND a.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }
            if ($typeId != -1) {
                $this->connector->CommandText .= " AND a.creditortype_id = ?type";
                $this->connector->AddParameter("?type", $typeId);
            }

            $this->connector->CommandText .= " ORDER BY a.creditor_name ASC";
            $report = $this->connector->ExecuteQuery();

        } else {
            $typeId = null;
            $output = "web";
            $report = null;
        }

        $creditorType = new CreditorType();
        $type = $this->userCompanyId != 7 ? $creditorType->LoadByEntity($this->userCompanyId) : $creditorType->LoadAll();
        $this->Set("type", $type);

        $res = $creditorType->FindById($typeId);
        $typeDesc = $res != null ? $res->CreditorTypeDesc : "SEMUA JENIS CREDITOR";
        $this->Set("typeDesc", $typeDesc);


        $this->Set("report", $report);
        $this->Set("output", $output);
    }
}
