<?php

class InquiryController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ap/inquiry.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		//$settings["columns"][] = array("name" => "c.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "b.item_code", "display" => "Item Code", "width" => 100);
        $settings["columns"][] = array("name" => "b.part_no", "display" => "Part Number", "width" => 120);
        $settings["columns"][] = array("name" => "b.item_name", "display" => "Item Name", "width" => 250);
		$settings["columns"][] = array("name" => "FORMAT(a.price, 0)", "display" => "Price", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "b.uom_cd", "display" => "UOM", "width" => 50);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.valid_start, '%d %M %Y')", "display" => "Start Date", "width" => 80);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.valid_end, '%d %M %Y')", "display" => "End Date", "width" => 80);
        $settings["columns"][] = array("name" => "d.creditor_name", "display" => "Supplier", "width" => 150);
        $settings["columns"][] = array("name" => "a.reff_no", "display" => "Reff No.", "width" => 150);

		$settings["filters"][] = array("name" => "b.item_name", "display" => "Nama Item");
		$settings["filters"][] = array("name" => "d.creditor_name", "display" => "Supplier");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Items Price & Quotation List";

			$acl = AclManager::GetInstance();
			if ($acl->CheckUserAccess("inquiry", "add", "ap")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ap.inquiry/add", "Class" => "bt_add", "ReqId" => 0);
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inquiry", "edit", "ap")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ap.inquiry/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih data harga barang terlebih dahulu sebelum proses edit !\n\nHarap memilih tepat 1 data.",
											   "Confirm" => "Apakah anda mau merubah data harga barang yang dipilih ?");
			}
			if ($acl->CheckUserAccess("inquiry", "delete", "ap")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ap.inquiry/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih data harga barang terlebih dahulu sebelum menghapusnya !\n\nHarap memilih tepat 1 data..",
											   "Confirm" => "Apakah anda mau menghapus data harga barang yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] =
"ap_item_inquiry AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
	JOIN cm_company AS c ON b.entity_id = c.entity_id
	JOIN ap_creditor_master AS d ON a.supplier_id = d.id";

			$settings["where"] = "a.is_deleted = 0 AND b.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "inventory/item.php");

		$inquiry = new Inquiry();
		if (count($this->postData) > 0) {
			$inquiry->CreateById = AclManager::GetInstance()->GetCurrentUser()->Id;
			$inquiry->SupplierId = $this->GetPostValue("SupplierId");
			$inquiry->ItemId = $this->GetPostValue("ItemId");
            $inquiry->ReffNo = $this->GetPostValue("ReffNo");
			$inquiry->Price = str_replace(",", "", $this->GetPostValue("Price"));
			$inquiry->ValidStart = strtotime($this->GetPostValue("ValidStart"));
			$inquiry->ValidEnd = strtotime($this->GetPostValue("ValidEnd"));

			if ($this->ValidateData($inquiry)) {
				if ($inquiry->Insert() == 1) {
					$this->persistence->SaveState("info", "Daftar harga barang telah berhasil disimpan.");
					redirect_url("ap.inquiry");
				} else {
					// Tidak ada duplicate key pada table ini
					$this->Set("error", "Gagal simpan harga barang ! Message: " . $this->connector->GetErrorMessage());
				}
			}
		} else {
			$inquiry->ValidStart = time();
			$inquiry->ValidEnd = time() + 604800;
		}

		$creditor = new Creditor();
		$suppliers = $creditor->LoadSuppliersByEntity($this->userCompanyId);
		$item = new Item();
		$items = $item->LoadByEntityId($this->userCompanyId);

		$this->Set("inquiry", $inquiry);
		$this->Set("suppliers", $suppliers);
		$this->Set("items", $items);
	}

	private function ValidateData(Inquiry $inquiry) {
		if ($inquiry->SupplierId == null) {
			$this->Set("error", "Mohon memilih supplier terlebih dahulu");
			return false;
		}
		if ($inquiry->ItemId == null) {
			$this->Set("error", "Mohon memilih barang terlebih dahulu");
			return false;
		}
		if ($inquiry->Price <= 0) {
			$this->Set("error", "Mohon masukkan harga barang yang benar. Harga tidak boleh <= 0");
			return false;
		}

		return true;
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih daftar harga terlebih dahulu.");
			redirect_url("ap.inquiry");
			return;
		}

		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "inventory/item.php");

		$inquiry = new Inquiry();
		if (count($this->postData) > 0) {
			$inquiry->Id = $id;
			$inquiry->UpdateById = AclManager::GetInstance()->GetCurrentUser()->Id;
			$inquiry->SupplierId = $this->GetPostValue("SupplierId");
			$inquiry->ItemId = $this->GetPostValue("ItemId");
            $inquiry->ReffNo = $this->GetPostValue("ReffNo");
			$inquiry->Price = str_replace(",", "", $this->GetPostValue("Price"));
			$inquiry->ValidStart = strtotime($this->GetPostValue("ValidStart"));
			$inquiry->ValidEnd = strtotime($this->GetPostValue("ValidEnd"));

			if ($this->ValidateData($inquiry)) {
				if ($inquiry->Update($id) == 1) {
					$this->persistence->SaveState("info", "Perubahan harga barang telah berhasil disimpan.");
					redirect_url("ap.inquiry");
				} else {
					// Tidak ada duplicate key pada table ini
					$this->Set("error", "Gagal merubah harga barang ! Message: " . $this->connector->GetErrorMessage());
				}
			}
		} else {
			$inquiry = $inquiry->LoadById($id);
			if ($inquiry == null) {
				$this->persistence->SaveState("error", "Maaf data daftar harga yang diminta tidak dapat ditemukan / sudah dihapus");
				redirect_url("ap.inquiry");
				return;
			}
		}

		$creditor = new Creditor();
		$suppliers = $creditor->LoadSuppliersByEntity($this->userCompanyId);
		$item = new Item();
		$items = $item->LoadByEntityId($this->userCompanyId);

		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// Check Company
			$item = $item->LoadById($inquiry->ItemId);
			if ($item->EntityId != $this->userCompanyId){
				// Simulate not found ! Access data which belong to other Company without CORPORATE access level
				$this->persistence->SaveState("error", "Maaf data daftar harga yang diminta tidak dapat ditemukan / sudah dihapus");
				redirect_url("ap.inquiry");
			}
		}

		$this->Set("inquiry", $inquiry);
		$this->Set("suppliers", $suppliers);
		$this->Set("items", $items);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih daftar harga terlebih dahulu.");
			redirect_url("ap.inquiry");
			return;
		}

		$inquiry = new Inquiry();
		$inquiry = $inquiry->LoadById($id);
		if ($inquiry == null) {
			$this->persistence->SaveState("error", "Maaf data daftar harga yang diminta tidak dapat ditemukan / sudah dihapus");
			redirect_url("ap.inquiry");
			return;
		}

		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			require_once(MODEL . "inventory/item.php");
			// Check Company
			$item = new Item();
			$item = $item->LoadById($inquiry->ItemId);
			if ($item->EntityId != $this->userCompanyId){
				// Simulate not found ! Access data which belong to other Company without CORPORATE access level
				$this->persistence->SaveState("error", "Maaf data daftar harga yang diminta tidak dapat ditemukan / sudah dihapus");
				redirect_url("ap.inquiry");
			}
		}

		if ($inquiry->Delete($inquiry->Id) != -1) {
			$this->persistence->SaveState("info", "Daftar harga barang yang diminta sudah dihapus.");
		} else {
			$this->persistence->SaveState("error", "Gagal menghapus daftar harga barang ! Message: " . $this->connector->GetErrorMessage());
		}
		redirect_url("ap.inquiry");
	}
}


// End of File: inquiry_controller.php
