<?php

class ItemCategoryController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/item_category.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		//$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a.category_code", "display" => "Code", "width" => 50);
		$settings["columns"][] = array("name" => "a.category_desc", "display" => "Item Category", "width" => 150);
		$settings["columns"][] = array("name" => "c.acc_no", "display" => "Inventory Account", "width" => 120);
		$settings["columns"][] = array("name" => "d.acc_no", "display" => "Cost Account", "width" => 120);
		$settings["columns"][] = array("name" => "CASE WHEN a.is_stock = 1 THEN 'Yes' ELSE 'No' END", "display" => "Stock ?", "width" => 80);

		$settings["filters"][] = array("name" => "a.category_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.category_desc", "display" => "Deskripsi");

		if (!$router->IsAjaxRequest) {
			// UI Settings
			$settings["title"] = "Items Category Master";
			$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemcategory/add", "Class" => "bt_add", "ReqId" => 0);
			$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemcategory/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
										   "Error" => "Mohon memilih kategori item terlebih dahulu sebelum melakukan proses edit !",
										   "Confirm" => "Apakah anda mau merubah data kategori item yang dipilih ?");
			$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemcategory/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
										   "Error" => "Mohon memilih kategori item terlebih dahulu sebelum melakukan proses delete !",
										   "Confirm" => "Apakah anda mau menghapus data kategori item yang dipilih ?");
			$settings["def_filter"] = 1;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			// Search Settings
			$settings["from"] = "ic_item_category AS a JOIN cm_company AS b ON a.entity_id = b.entity_id Left Join cm_acc_detail AS c On a.invt_acc_id = c.id Left Join cm_acc_detail AS d On a.cost_acc_id = d.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");

		$itemCategory = new ItemCategory();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$itemCategory->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
			$itemCategory->EntityId = $this->userCompanyId;
			$itemCategory->Code = $this->GetPostValue("Code");
			$itemCategory->Description = $this->GetPostValue("Description");
			$itemCategory->InventoryAccountId = $this->GetPostValue("InvAccId");
			$itemCategory->CostAccountId = $this->GetPostValue("CostAccId");
			$itemCategory->IsStock = $this->GetPostValue("IsStock", false);
			$itemCategory->IsStock = $itemCategory->IsStock == "1";
			if ($itemCategory->CostAccountId == "") {
				$itemCategory->CostAccountId = null;
			}
			if ($itemCategory->InventoryAccountId == "") {
				$itemCategory->InventoryAccountId = null;
			}
			if ($this->DoInsert($itemCategory)) {
				$this->persistence->SaveState("info", sprintf("Data Kategori Item: '%s' Dengan Kode: %s telah berhasil disimpan.", $itemCategory->Description, $itemCategory->Code));
				redirect_url("inventory.itemcategory");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $itemCategory->Code));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// load data company for combo box
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$account = new Coa();
		$accounts = $account->LoadByLevel($this->userCompanyId,3);

		// untuk kirim variable ke view
		$this->Set("itemcategory", $itemCategory);
		$this->Set("company", $company);
		$this->Set("accounts", $accounts);
	}

	private function DoInsert(ItemCategory $itemCategory) {
		if ($itemCategory->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu");
			return false;
		}
		if ($itemCategory->Code == null) {
			$this->Set("error", "Mohon memasukkan kode kategori item terlebih dahulu !");
			return false;
		}
		if ($itemCategory->Description == null) {
			$this->Set("error", "Mohon memasukkan deskripsi kategori item terlebih dahulu !");
			return false;
		}

		if ($itemCategory->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");

		$itemCategory = new ItemCategory();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$itemCategory->Id = $id;
			$itemCategory->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
			$itemCategory->EntityId = $this->userCompanyId;
			$itemCategory->Code = $this->GetPostValue("Code");
			$itemCategory->Description = $this->GetPostValue("Description");
			$itemCategory->InventoryAccountId = $this->GetPostValue("InvAccId");
			$itemCategory->CostAccountId = $this->GetPostValue("CostAccId");
			$itemCategory->IsStock = $this->GetPostValue("IsStock", false);
			$itemCategory->IsStock = $itemCategory->IsStock == "1";
			if ($itemCategory->CostAccountId == "") {
				$itemCategory->CostAccountId = null;
			}
			if ($itemCategory->InventoryAccountId == "") {
				$itemCategory->InventoryAccountId = null;
			}

			if ($this->DoEdit($itemCategory)) {
				$this->persistence->SaveState("info", sprintf("Data Kategori Item: '%s' Dengan Kode: %s telah berhasil disimpan.", $itemCategory->Description, $itemCategory->Code));
				redirect_url("inventory.itemcategory");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $itemCategory->Code));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}

		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih kategori item terlebih dahulu sebelum melakukan proses edit !");
				redirect_url("inventory.itemcategory");
				return;
			}
			$itemCategory = $itemCategory->LoadById($id);
			if ($itemCategory == null || $itemCategory->IsDeleted) {
				$this->persistence->SaveState("error", "Maaf Kategori Item yang anda cari tidak dapat ditemukan !");
				redirect_url("inventory.itemcategory");
				return;
			}
		}

		// load data company for combo box
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$account = new Coa();
        $accounts = $account->LoadByLevel($this->userCompanyId,3);

		// untuk kirim variable ke view
		$this->Set("itemcategory", $itemCategory);
		$this->Set("company", $company);
		$this->Set("accounts", $accounts);
	}

	private function DoEdit(ItemCategory $itemCategory) {
		if ($itemCategory->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu");
			return false;
		}
		if ($itemCategory->Code == null) {
			$this->Set("error", "Mohon memasukkan kode kategori item terlebih dahulu !");
			return false;
		}
		if ($itemCategory->Description == null) {
			$this->Set("error", "Mohon memasukkan deskripsi kategori item terlebih dahulu !");
			return false;
		}
		if ($itemCategory->InventoryAccountId == null) {
			$this->Set("error", "Mohon memasukkan kode account inventory terlebih dahulu !");
			return false;
		}

		if ($itemCategory->Update($itemCategory->Id) == -1) {
			return false;
		} else {
			return true;
		}
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih kategori item terlebih dahulu sebelum melakukan proses delete !");
			redirect_url("inventory.itemcategory");
			return;
		}

		$itemCategory = new ItemCategory();
		$itemCategory = $itemCategory->LoadById($id);
		if ($itemCategory == null) {
			$this->persistence->SaveState("error", "Data kategori item yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("inventory.itemcategory");
		}

		if ($itemCategory->Delete($itemCategory->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Kategori Item: %s telah berhasil dihapus.", $itemCategory->Code));
			redirect_url("inventory.itemcategory");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Kategori Item: '%s'. Message: %s", $itemCategory->Code, $this->connector->GetErrorMessage()));
		}
		redirect_url("inventory.itemcategory");
	}
}


// End of File: itemcategory_controller.php
