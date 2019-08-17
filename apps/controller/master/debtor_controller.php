<?php
class DebtorController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/debtor.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "a.debtor_cd", "display" => "Code", "width" => 60);
		$settings["columns"][] = array("name" => "c.debtortype_cd", "display" => "Type", "width" => 60);
		$settings["columns"][] = array("name" => "a.debtor_name", "display" => "Debtor/Customer Name", "width" => 200);
		$settings["columns"][] = array("name" => "a.address1", "display" => "Address", "width" => 300);
		$settings["columns"][] = array("name" => "a.core_business", "display" => "Core Business", "width" => 300);

		$settings["filters"][] = array("name" => "a.debtor_name", "display" => "Vendor Name");
		$settings["filters"][] = array("name" => "c.debtortype_cd", "display" => "Vendor Type");
		$settings["filters"][] = array("name" => "a.core_business", "display" => "Core Business");
		$settings["filters"][] = array("name" => "a.debtor_cd", "display" => "Vendor Code");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Debtor Master";

			if ($acl->CheckUserAccess("debtor", "add", "master")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.debtor/add", "Class" => "bt_add", "ReqId" => 0);
			}
            if ($acl->CheckUserAccess("debtor", "edit", "master")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "master.debtor/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Debtor terlebih dahulu sebelum proses edit !\nPERHATIAN: Pastikan anda memilih tepat 1 debtor.",
                    "Confirm" => "Apakah anda yakin mau mengedit data debtor yang dipilih ?");
            }
			if ($acl->CheckUserAccess("debtor", "view", "master")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "master.debtor/view/%s", "Class" => "bt_view", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih Debtor terlebih dahulu sebelum dapat melihat data !\nPERHATIAN: Pastikan anda memilih tepat 1 debtor.",
											   "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("debtor", "delete", "master")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.debtor/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih Debtor terlebih dahulu sebelum proses edit !\nPERHATIAN: Pastikan anda memilih tepat 1 debtor.",
											   "Confirm" => "Apakah anda yakin mau menghapus data debtor yang dipilih ?\nPERHATIAN: pastikan anda sudah memilih data dengan benar");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ar_debtor_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id JOIN ar_debtortype AS c ON a.debtortype_id = c.id";
            $settings["where"] = "a.is_deleted = 0 And a.entity_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Debtor $debtor) {
		if ($debtor->DebtorCd == "") {
			$this->Set("error", "Kode Debtor masih kosong");
			return false;
		}
		if ($debtor->DebtorName == "") {
			$this->Set("error", "Nama Debtor masih kosong");
			return false;
		}
		if ($debtor->PhoneNo == "") {
			$this->Set("error", "No. Telepon masih kosong");
			return false;
		}
		if ($debtor->ContactPerson == "") {
			$this->Set("error", "Contact Person masih kosong");
			return false;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/debtor_type.php");

		$debtor = new Debtor();
		$loader = null;

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$debtor->EntityId = $this->userCompanyId;
			$debtor->DebtorCd = $this->GetPostValue("DebtorCd");
			$debtor->DebtorName = $this->GetPostValue("DebtorName");
			$debtor->DebtorTypeId = $this->GetPostValue("DebtorTypeId");
			$debtor->Address1 = $this->GetPostValue("Address1");
			$debtor->Address2 = $this->GetPostValue("Address2");
			$debtor->Address3 = $this->GetPostValue("Address3");
			$debtor->PostalCode = $this->GetPostValue("PostCd");
			$debtor->PhoneNo = $this->GetPostValue("TelNo");
			$debtor->HandPhone = $this->GetPostValue("HandPhone");
			$debtor->FaxNo = $this->GetPostValue("FaxNo");
			$debtor->Remark = $this->GetPostValue("Remark");
			$debtor->Npwp = $this->GetPostValue("Npwp");
			$debtor->ContactPerson = $this->GetPostValue("ContactPerson");
			$debtor->Position = $this->GetPostValue("Position");
			$debtor->EmailAddress = $this->GetPostValue("EmailAdd");
			$debtor->WebSite = $this->GetPostValue("WebSite");
			$debtor->CoreBusiness = $this->GetPostValue("CoreBusiness");
			$debtor->BankAccount = $this->GetPostValue("BankAccount");

			if ($this->ValidateData($debtor)) {
				$debtor->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				if ($debtor->Insert() == 1) {
					$this->persistence->SaveState("info", sprintf("Data Debtor: '%s' telah berhasil disimpan.", $debtor->DebtorName));
					redirect_url("master.debtor");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Debtor: '%s' telah ada pada database !", $debtor->DebtorCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$loader = new DebtorType();
		$debtorTypes = $loader->LoadByEntity($this->userCompanyId);
		$this->Set("company", $company);
		$this->Set("debtorTypes", $debtorTypes);
		$this->Set("debtor", $debtor);
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/debtor_type.php");
		$loader = null;
		$debtor = new Debtor();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$debtor->Id = $id;
			$debtor->DebtorCd = $this->GetPostValue("DebtorCd");
			$debtor->DebtorName = $this->GetPostValue("DebtorName");
			$debtor->DebtorTypeId = $this->GetPostValue("DebtorTypeId");
			$debtor->Address1 = $this->GetPostValue("Address1");
			$debtor->Address2 = $this->GetPostValue("Address2");
			$debtor->Address3 = $this->GetPostValue("Address3");
			$debtor->PostalCode = $this->GetPostValue("PostCd");
			$debtor->PhoneNo = $this->GetPostValue("TelNo");
			$debtor->HandPhone = $this->GetPostValue("HandPhone");
			$debtor->FaxNo = $this->GetPostValue("FaxNo");
			$debtor->Remark = $this->GetPostValue("Remark");
			$debtor->Npwp = $this->GetPostValue("Npwp");
			$debtor->ContactPerson = $this->GetPostValue("ContactPerson");
			$debtor->Position = $this->GetPostValue("Position");
			$debtor->EmailAddress = $this->GetPostValue("EmailAdd");
			$debtor->WebSite = $this->GetPostValue("WebSite");
			$debtor->CoreBusiness = $this->GetPostValue("CoreBusiness");
			$debtor->BankAccount = $this->GetPostValue("BankAccount");

			if ($this->ValidateData($debtor)) {
				$debtor->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				if ($debtor->Update($debtor->Id) == 1) {
					$this->persistence->SaveState("info", sprintf("Data Debtor: '%s' telah berhasil diupdate.", $debtor->DebtorName));
					redirect_url("master.debtor");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Debtor: '%s' telah ada pada database !", $debtor->DebtorCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data debtor sebelum melakukan edit data !");
				redirect_url("master.debtor");
			}
			$debtor = $debtor->FindById($id);
			if ($debtor == null || $debtor->IsDeleted) {
				$this->persistence->SaveState("error", "Data Debtor yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.debtor");
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($debtor->EntityId != $this->userCompanyId) {
					// AKSES DATA BEDA Company KICK!!!!
					$this->persistence->SaveState("error", "Maaf, Data debtor yang dipilih tidak boleh diedit oleh anda..");
					redirect_url("master.debtor");
					return;
				}
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$loader = new DebtorType();
		$debtorTypes = $loader->LoadByEntity($this->userCompanyId);
		$this->Set("company", $company);
		$this->Set("debtorTypes", $debtorTypes);
		$this->Set("debtor", $debtor);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data debtor sebelum melakukan hapus data !");
			redirect_url("master.debtor");
		}

		$debtor = new Debtor();
		$debtor = $debtor->FindById($id);
		if ($debtor == null) {
			$this->persistence->SaveState("error", "Data debtor yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.debtor");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($debtor->EntityId != $this->userCompanyId) {
				// AKSES DATA BEDA Company KICK!!!!
				$this->persistence->SaveState("error", "Maaf, Data debtor yang dipilih tidak boleh dihapus oleh anda..");
				redirect_url("master.debtor");
				return;
			}
		}

		if ($debtor->Delete($debtor->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Debtor: '%s' Dengan Kode: %s telah berhasil dihapus.", $debtor->DebtorName, $debtor->DebtorCd));
			redirect_url("master.debtor");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data debtor: '%s'. Message: %s", $debtor->DebtorName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.debtor");
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data debtor terlebih dahulu sebelum melihat data !");
			redirect_url("master.debtor");
		}

		$debtor = new Debtor();
		$debtor = $debtor->FindById($id);
		if ($debtor == null) {
			$this->persistence->SaveState("error", "Data debtor yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.debtor");
		}
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/debtor_type.php");

		$company = new Company();
		$company = $company->FindById($debtor->EntityId);
		$debtorType = new DebtorType();
		$debtorType = $debtorType->FindById($debtor->DebtorTypeId);

		$this->Set("company", $company);
		$this->Set("debtor", $debtor);
		$this->Set("debtorType", $debtorType);
	}

	public function autodebtorcd($debtorname) {
		$debtor = new Debtor();
		$debtorcd = $debtor->GetAutoCode($debtorname);
		print($debtorcd);
	}

    //halaman search data
    public function overview() {

        require_once(MODEL. "master/debtor.php");
        require_once(MODEL. "common/debtor_type.php");

        $debtor = new Debtor();

        if(count($this->getData) > 0 ) {
            $typeId = $this->GetGetValue("jenis");
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.entity_cd AS entity, c.debtortype_desc AS type_desc
                                            FROM ar_debtor_master AS a
                                            JOIN cm_company AS b ON a.entity_id = b.entity_id
                                            JOIN ar_debtortype AS c ON a.debtortype_id = c.id
                                            WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7) {
                $this->connector->CommandText .= " AND a.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }
            if ($typeId != -1) {
                $this->connector->CommandText .= " AND a.debtortype_id = ?type";
                $this->connector->AddParameter("?type", $typeId);
            }

            $this->connector->CommandText .= " ORDER BY a.debtor_name ASC";
            $report = $this->connector->ExecuteQuery();

        } else {
            $typeId = null;
            $output = "web";
            $report = null;
        }

        $debtorType = new DebtorType();
        $type = $this->userCompanyId != 7 ? $debtorType->LoadByEntity($this->userCompanyId) : $debtorType->LoadAll();
        $this->Set("type", $type);

        $res = $debtorType->FindById($typeId);
        $typeDesc = $res != null ? $res->DebtorTypeDesc : "SEMUA JENIS CREDITOR";
        $this->Set("typeDesc", $typeDesc);


        $this->Set("report", $report);
        $this->Set("output", $output);
    }
}
