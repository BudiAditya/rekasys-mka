<?php

class CashRequestCategoryController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "accounting/cash_request_category.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a.code", "display" => "Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.name", "display" => "Category", "width" => 150);
        $settings["columns"][] = array("name" => "d.project_name", "display" => "Project", "width" => 100);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Account Control", "width" => 150);
		$settings["columns"][] = array("name" => "c.acc_name", "display" => "Account Name", "width" => 250);

		$settings["filters"][] = array("name" => "d.project_cd", "display" => "Project");
		$settings["filters"][] = array("name" => "a.code", "display" => "Code");
		$settings["filters"][] = array("name" => "a.name", "display" => "Category");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Cash Request Category";

			$acl = AclManager::GetInstance();
			if ($acl->CheckUserAccess("CashRequestCategory", "add", "accounting")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "accounting.cashrequestcategory/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("CashRequestCategory", "edit", "accounting")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "accounting.cashrequestcategory/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih kategori NPKP terlebih dahulu sebelum melakukan proses edit.\nPERHATIAN: Harap memilih tepat 1 data.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("CashRequestCategory", "delete", "accounting")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "accounting.cashrequestcategory/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih kategori NPKP terlebih dahulu sebelum melakukan proses penghapusan.\nPERHATIAN: Harap memilih tepat 1 data.",
					"Confirm" => "Apakah anda yakin mau menghapus kategori NPKP yang dipilih ?\nKlik 'OK' untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ac_cash_request_category AS a JOIN cm_company AS b ON a.entity_id = b.entity_id JOIN cm_acc_detail AS c ON a.acc_control_id = c.id Left Join cm_project AS d On a.project_id = d.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(CashRequestCategory $category) {
		if ($category->Code == null) {
			$this->Set("error", "Mohon memasukkan kode kategori terlebih dahulu.");
			return false;
		}
		if ($category->Name == null) {
			$this->Set("error", "Mohon memasukkan nama kategori terlebih dahulu");
			return false;
		}
		if ($category->AccountControlId == null) {
			$this->Set("error", "Mohon memilih akun kontrol terlebih dahulu. Akun ini akan digunakan pada saat pencairan dana.");
			return false;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
        require_once(MODEL . "master/project.php");
		$category = new CashRequestCategory();

		if (count($this->postData) > 0) {
			$category->EntityId = $this->userCompanyId;
            $category->ProjectId = $this->GetPostValue("ProjectId");
			$category->Code = $this->GetPostValue("Code");
			$category->Name = $this->GetPostValue("Name");
			$category->AccountControlId = $this->GetPostValue("AccId");

			if ($this->ValidateData($category)) {
				$category->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $category->Insert();
				if ($rs == 1) {
					$this->persistence->SaveState("info", sprintf("Kategori NPKP: %s - %s sudah berhasil disimpan.", $category->Code, $category->Name));
					redirect_url("accounting.cashrequestcategory");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf kode kategori sudah ada pada database. Harap memeriksanya kembali.");
					} else {
						$this->Set("error", "Gagal simpan data kategori NPKP. Error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$account = new Coa();
        $projects = new Project();
        $this->Set("projects", $projects->LoadByEntityId($this->userCompanyId));
		$this->Set("company", $company);
		$this->Set("category", $category);
		$this->Set("accounts", $account->LoadLevel3ByFirstCode($this->userCompanyId,array("1")));
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih kategori NPKP terlebih dahulu sebelum proses edit.");
			redirect_url("accounting.cashrequestcategory");
		}

		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
        require_once(MODEL . "master/project.php");
		$category = new CashRequestCategory();

		if (count($this->postData) > 0) {
			$category->Id = $id;
			$category->EntityId = $this->userCompanyId;
            $category->ProjectId = $this->GetPostValue("ProjectId");
			$category->Code = $this->GetPostValue("Code");
			$category->Name = $this->GetPostValue("Name");
			$category->AccountControlId = $this->GetPostValue("AccId");

			if ($this->ValidateData($category)) {
				$category->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $category->Update($category->Id);
				if ($rs == 1) {
					$this->persistence->SaveState("info", sprintf("Kategori NPKP: %s - %s sudah berhasil disimpan.", $category->Code, $category->Name));
					redirect_url("accounting.cashrequestcategory");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf kode kategori sudah ada pada database. Harap memeriksanya kembali.");
					} else {
						$this->Set("error", "Gagal simpan data kategori NPKP. Error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		} else {
			$category = $category->LoadById($id);
			if ($category == null || $category->IsDeleted) {
				$this->persistence->SaveState("error", "Maaf kategori NPKP yang anda minta tidak dapat ditemukan / sudah dihapus.");
				redirect_url("accounting.cashrequestcategory");
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($category->EntityId != $this->userCompanyId) {
					// Hwe... direct access ? KICK and simulate not found.
					$this->persistence->SaveState("error", "Maaf kategori NPKP yang anda minta tidak dapat ditemukan / sudah dihapus.");
					redirect_url("accounting.cashrequestcategory");
				}
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$account = new Coa();
        $projects = new Project();
        $this->Set("projects", $projects->LoadByEntityId($this->userCompanyId));
		$this->Set("company", $company);
		$this->Set("category", $category);
        $this->Set("accounts", $account->LoadLevel3ByFirstCode($this->userCompanyId,array("1")));
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih kategori NPKP terlebih dahulu sebelum proses penghapusan data.");
			redirect_url("accounting.cashrequestcategory");
		}

		$category = new CashRequestCategory();
		$category = $category->LoadById($id);
		if ($category == null || $category->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf kategori NPKP yang anda minta tidak dapat ditemukan / sudah dihapus.");
			redirect_url("accounting.cashrequestcategory");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($category->EntityId != $this->userCompanyId) {
				// Hwe... direct access ? KICK and simulate not found.
				$this->persistence->SaveState("error", "Maaf kategori NPKP yang anda minta tidak dapat ditemukan / sudah dihapus.");
				redirect_url("accounting.cashrequestcategory");
			}
		}

		$rs = $category->Delete($category->Id);
		if ($rs == 1) {
			$this->persistence->SaveState("info", sprintf("Kategori NPKP: %s - %s sudah berhasil dihapus", $category->Code, $category->Name));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus Kategori NPKP: %s - %s. Error: %s", $category->Code, $category->Name, $this->connector->GetErrorMessage()));
		}

		redirect_url("accounting.cashrequestcategory");
	}
}


// End of File: cashrequestcategory_controller.php
