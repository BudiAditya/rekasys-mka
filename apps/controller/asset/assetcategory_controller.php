<?php

class AssetCategoryController extends AppController {
    private $userCompanyId;

    protected function Initialize() {
        require_once(MODEL . "asset/asset_category.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        //$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
        $settings["columns"][] = array("name" => "a.code", "display" => "Code", "width" => 50);
        $settings["columns"][] = array("name" => "a.name", "display" => "Asset Category", "width" => 250);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Asset Account", "width" => 100);
		$settings["columns"][] = array("name" => "d.acc_no", "display" => "Depreciation", "width" => 100);
		$settings["columns"][] = array("name" => "e.acc_no", "display" => "Cost Account", "width" => 100);
		$settings["columns"][] = array("name" => "f.acc_no", "display" => "Revenue Account", "width" => 100);
		$settings["columns"][] = array("name" => "CASE WHEN a.dep_method = 1 THEN 'Straight Line' WHEN a.dep_method = 2 THEN 'Double Declining' ELSE '-' END", "display" => "Depreciation Method", "width" =>100);
		$settings["columns"][] = array("name" => "a.max_age", "display" => "Useful", "width" => 50, "align" => "right");
		$settings["columns"][] = array("name" => "a.dep_percentage", "display" => "% Dep Rate", "width" => 60, "align" => "right");

        $settings["filters"][] = array("name" => "a.name", "display" => "Kategori Aktiva");

        if (!$router->IsAjaxRequest) {
            // UI Settings
            $settings["title"] = "Assets Category";
            $settings["actions"][] = array("Text" => "Add", "Url" => "asset.assetcategory/add", "Class" => "bt_add", "ReqId" => 0);
            $settings["actions"][] = array("Text" => "Edit", "Url" => "asset.assetcategory/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                "Error" => "Mohon memilih kategori aktiva terlebih dahulu sebelum melakukan proses edit !",
                "Confirm" => "Apakah anda mau merubah data kategori aktiva yang dipilih ?");
            $settings["actions"][] = array("Text" => "Delete", "Url" => "asset.assetcategory/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
                "Error" => "Mohon memilih kategori aktiva terlebih dahulu sebelum melakukan proses delete !",
                "Confirm" => "Apakah anda mau menghapus data kategori item yang dipilih ?");

            $settings["def_filter"] = 1;
            $settings["def_order"] = 1;
            $settings["singleSelect"] = true;
        } else {
            // Search Settings
            $settings["from"] = "ac_asset_category AS a
	        JOIN cm_company AS b ON a.entity_id = b.entity_id
	        JOIN cm_acc_detail AS c ON a.ast_acc_id = c.id
	        JOIN cm_acc_detail AS d ON a.dep_acc_id = d.id
	        JOIN cm_acc_detail AS e ON a.cos_acc_id = e.id
	        JOIN cm_acc_detail AS f ON a.rev_acc_id = f.id";
            $settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    public function add() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");

        $category = new AssetCategory();

        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $category->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
            $category->EntityId = $this->userCompanyId;
            $category->Code = $this->GetPostValue("Code");
            $category->Name = $this->GetPostValue("Name");
            $category->AssetAccountId = $this->GetPostValue("ActAccId");
            $category->DepreciationAccountId = $this->GetPostValue("DepAccId");
			$category->CostAccountId = $this->GetPostValue("CosAccId");
			$category->RevenueAccountId = $this->GetPostValue("RevAccId");
			$category->DepreciationMethodId = $this->GetPostValue("DepMethod");
			$category->MaxAge = $this->GetPostValue("MaxAge");
			$category->DepreciationPercentage = $this->GetPostValue("DepPercentage");

            if ($this->ValidateData($category)) {
				if ($category->Insert() == 1) {
					$this->persistence->SaveState("info", sprintf("Data Kategori Aktiva: '%s' telah berhasil disimpan.", $category->Name));
					redirect_url("asset.assetcategory");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kategori Aktiva: '%s' telah ada pada database !", $category->Name));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
            }
        }

        // load data company for combo box
        $companies = new Company($this->userCompanyId);

        $account = new Coa();
        $accounts = $account->LoadByLevel($this->userCompanyId,3);

        // untuk kirim variable ke view
        $this->Set("category", $category);
        $this->Set("companies", $companies);
        $this->Set("accounts", $accounts);
    }

    private function ValidateData(AssetCategory $category) {
        if ($category->EntityId == null) {
            $this->Set("error", "Mohon memilih Company terlebih dahulu");
            return false;
        }
        if ($category->Name == null) {
            $this->Set("error", "Mohon memasukkan nama kategori aktiva terlebih dahulu !");
            return false;
        }
        if ($category->AssetAccountId == null) {
            $this->Set("error", "Mohon memasukkan kode akun aktiva terlebih dahulu !");
            return false;
        }
		if ($category->DepreciationAccountId == null) {
			$this->Set("error", "Mohon memasukkan kode akun depresiasi terlebih dahulu !");
			return false;
		}
		if ($category->CostAccountId == null) {
			$this->Set("error", "Mohon memasukkan kode akun biaya terlebih dahulu !");
			return false;
		}
		if ($category->RevenueAccountId == null) {
			$this->Set("error", "Mohon memasukkan kode akun revenue terlebih dahulu !");
			return false;
		}
		if ($category->DepreciationMethodId == null) {
			$this->Set("error", "Mohon memasukkan metode penyusutan aktiva terlebih dahulu");
			return false;
		}
		if ($category->MaxAge == null) {
			$this->Set("error", "Mohon memasukkan umur aktiva terlebih dahulu");
			return false;
		}
		if ($category->DepreciationPercentage == null) {
			$this->Set("error", "Mohon memasukkan persentase penyusutan aktiva terlebih dahulu");
			return false;
		}

		return true;
    }

    public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");

        $category = new AssetCategory();

        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $category->Id = $id;
			$category->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
			$category->EntityId = $this->userCompanyId;
            $category->Code = $this->GetPostValue("Code");
			$category->Name = $this->GetPostValue("Name");
			$category->AssetAccountId = $this->GetPostValue("ActAccId");
			$category->DepreciationAccountId = $this->GetPostValue("DepAccId");
			$category->CostAccountId = $this->GetPostValue("CosAccId");
			$category->RevenueAccountId = $this->GetPostValue("RevAccId");
			$category->DepreciationMethodId = $this->GetPostValue("DepMethod");
			$category->MaxAge = $this->GetPostValue("MaxAge");
			$category->DepreciationPercentage = $this->GetPostValue("DepPercentage");

			if ($this->ValidateData($category)) {
				if ($category->Update($id) == 1) {
					$this->persistence->SaveState("info", sprintf("Perubahan Data Kategori Aktiva: '%s' telah berhasil disimpan.", $category->Name));
					redirect_url("asset.assetcategory");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kategori Aktiva: '%s' telah ada pada database !", $category->Name));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}

        } else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih kategori aktiva terlebih dahulu sebelum melakukan proses edit !");
                redirect_url("asset.assetcategory");
                return;
            }
            $category = $category->LoadById($id);
            if ($category == null || $category->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf Kategori Aktiva yang anda cari tidak dapat ditemukan !");
                redirect_url("asset.assetcategory");
                return;
            }
        }

        // load data company for combo box
        $companies = new Company($this->userCompanyId);

        $account = new Coa();
        $accounts = $account->LoadByLevel($this->userCompanyId,3);

        // untuk kirim variable ke view
        $this->Set("category", $category);
        $this->Set("companies", $companies);
        $this->Set("accounts", $accounts);
    }

    public function delete($id) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih kategori aktiva terlebih dahulu sebelum melakukan proses delete !");
            redirect_url("asset.assetcategory");
            return;
        }

        $category = new AssetCategory();
        $category = $category->LoadById($id);
        if ($category == null) {
            $this->persistence->SaveState("error", "Data kategori aktiva yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("asset.assetcategory");
        }
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// Checking Company
			if ($this->userCompanyId != $category->EntityId) {
				// Simulate not found !
				$this->persistence->SaveState("error", "Data kategori aktiva yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("asset.assetcategory");
			}
		}

        if ($category->Delete($category->Id) == 1) {
            $this->persistence->SaveState("info", sprintf("Data Kategori Aktiva: %s telah berhasil dihapus.", $category->Code));
            redirect_url("asset.assetcategory");
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus data Kategori Aktiva: '%s'. Message: %s", $category->Code, $this->connector->GetErrorMessage()));
        }
        redirect_url("asset.assetcategory");
    }
}


// End of File: assetcategory_controller.php
