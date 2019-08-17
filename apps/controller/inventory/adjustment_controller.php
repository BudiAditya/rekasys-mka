<?php

class AdjustmentController extends AppController {
	private $userCompanyId = null;

	protected function Initialize() {
		require_once(MODEL . "inventory/adjustment.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Dokumen", "width" => 120);
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "d.short_desc", "display" => "Jenis", "width" => 120);
		$settings["columns"][] = array("name" => "e.wh_name", "display" => "Gudang", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.adjustment_date, '%d %M %Y')", "display" => "Tgl. Update", "width" => 80);
		$settings["columns"][] = array("name" => "a.note", "display" => "Keterangan", "width" => 300);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No. Dokumen");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Jenis");
		$settings["filters"][] = array("name" => "e.wh_name", "display" => "Gudang");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Dokumen Barang Rusak (BS)";

			if ($acl->CheckUserAccess("adjustment", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.adjustment/add?step=master", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("adjustment", "view", "inventory")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "inventory.adjustment/view/%s", "Class" => "bt_view", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen Adjustment Stock terlebih dahulu sebelum melihat data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("adjustment", "edit", "inventory")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.adjustment/edit/%s?step=master", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen Adjustment Stock terlebih dahulu sebelum mengupdate data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda mau melakukan proses update data Adjustment Stock ?");
			}
			if ($acl->CheckUserAccess("adjustment", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.adjustment/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen Adjustment Stock terlebih dahulu sebelum menghapus data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakan anda yakin mau menghapus dokumen Adjustment Stock yang dipilih ?\nKlik OK untuk menghapus data yang dipilih.");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("adjustment", "batch_approve", "inventory")) {
				$settings["actions"][] = array("Text" => "Batch Approve", "Url" => "inventory.adjustment/batch_approve", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Adjustment Stock !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua dokumen Adjustment Stock yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("adjustment", "batch_disapprove", "inventory")) {
				$settings["actions"][] = array("Text" => "Batch Dis-Approve", "Url" => "inventory.adjustment/batch_disapprove", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Adjustment Stock !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen Adjustment Stock yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Hapus Cache apapun yang terjadi
			$this->persistence->DestroyState("inventory.adjustment.master");
		} else {
			$settings["from"] =
"ic_adjustment_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'adjustment_status'
	JOIN sys_status_code AS d ON a.adjustment_type = d.code AND d.key = 'adjustment_type'
	JOIN ic_warehouse AS e ON a.warehouse_id = e.id";

			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$settings["where"] = "a.is_deleted = 0";
			} else {
				$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
			}
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		$step = strtolower($this->GetGetValue("step"));

		switch ($step) {
			case "master":
				$this->ProcessMaster();
				break;
			case "detail":
				$this->ProcessDetail();
				break;
			case "confirm":
				$this->ProcessConfirm();
				break;
			default:
				redirect_url("inventory.adjustment/add?step=master");
				return;
		}

		require_once(MODEL . "master/company.php");
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("step", $step);
		$this->Set("company", $company);
	}

	private function ProcessMaster($id = null) {
		$sessionValid = false;
		if ($this->persistence->StateExists("inventory.adjustment.master")) {
			$adjustment = $this->persistence->LoadState("inventory.adjustment.master");
			if ($adjustment->Id != $id) {
				// ID pada session beda ?
				if ($id == null) {
					redirect_url("inventory.adjustment/add?step=master");
				} else {
					redirect_url("inventory.adjustment/edit/$id?step=master");
				}
			} else {
				$sessionValid = true;
			}
		} else {
			$adjustment = new Adjustment();

			if ($id == null) {
				$adjustment->EntityId = $this->userCompanyId;
				$adjustment->Date = time();
				$adjustment->DocumentNo = "[AUTO]";
			} else {
				$adjustment = $adjustment->LoadById($id);
				if ($adjustment == null || $adjustment->IsDeleted) {
					$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
					redirect_url("inventory.adjustment");
					return;
				}
				if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
					if ($adjustment->EntityId != $this->userCompanyId) {
						// SIMULATE NOT FOUND !
						$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
						redirect_url("inventory.adjustment");
						return;
					}
				}
				if ($adjustment->StatusCode > 2) {
					$this->persistence->SaveState("error", "Maaf dokumen yang diminta sudah tidak bersifat DRAFT !");
					redirect_url("inventory.adjustment/view/" . $adjustment->Id);
					return;
				}
			}
		}

		require_once(MODEL . "inventory/warehouse.php");

		// OK... process data
		if (count($this->postData) > 0) {
			$adjustment->EntityId = $this->userCompanyId;
			$adjustment->DocumentNo = $this->GetPostValue("DocumentNo");
			$adjustment->Date = strtotime($this->GetPostValue("Date"));
			$adjustment->WarehouseId = $this->GetPostValue("Warehouse");
			$adjustment->Note = $this->GetPostValue("Note");
			$adjustment->StatusCode = 1;
			$adjustment->Type = $this->GetPostValue("Type");

			if ($this->ValidateMaster($adjustment)) {

				if ($id == null) {
					require_once(MODEL . "common/doc_counter.php");
					$docCounter = new DocCounter();
					$adjustment->DocumentNo = $docCounter->AutoDocNoAdjustmentInventory($this->userCompanyId, $adjustment->Date, 0);
					if ($adjustment->DocumentNo != null) {
						$this->persistence->SaveState("inventory.adjustment.master", $adjustment);
						redirect_url("inventory.adjustment/add?step=detail");
					} else {
						$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
						$adjustment->DocumentNo = "[LOCKED]";
					}
				} else {
					if (!$sessionValid) {
						$adjustment->LoadDetails();
					}

					$this->persistence->SaveState("inventory.adjustment.master", $adjustment);
					redirect_url("inventory.adjustment/edit/$id?step=detail");
				}
			}
		}

		$warehouse = new Warehouse();

		$this->Set("adjustment", $adjustment);
		$this->Set("warehouses", $warehouse->LoadByEntityId($adjustment->EntityId));
	}

	private function ValidateMaster(Adjustment $adjustment) {
		if ($adjustment->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu");
			return false;
		}
		if ($adjustment->DocumentNo == null) {
			$this->Set("error", "Mohon memasukkan no dokumen terlebih dahulu");
			return false;
		}
		if (!is_int($adjustment->Date)) {
			$this->Set("error", "Mohon masukkan tanggal Adjustment Stock terlebih dahulu");
			return false;
		}
		if ($adjustment->Type == null) {
			$this->Set("error", "Mohon pilih jenis dokumen terlebih dahulu");
			return false;
		}
		if ($adjustment->WarehouseId == null) {
			$this->Set("error", "Mohon memilih gudang yang dilakukan proses adjustment");
			return false;
		}

		return true;
	}

	private function ProcessDetail($id = null) {
		if ($this->persistence->StateExists("inventory.adjustment.master")) {
			$adjustment = $this->persistence->LoadState("inventory.adjustment.master");
			if ($adjustment->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("inventory.adjustment/add?step=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("inventory.adjustment/edit/$id?step=master");
				}
				return;
			}
		} else {
			// Tidak ada session ???
			if ($id == null) {
				redirect_url("inventory.adjustment/add?step=master");
			} else {
				redirect_url("inventory.adjustment/edit/$id?step=master");
			}
			return;
		}

		require_once(MODEL . "inventory/item_category.php");
		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/warehouse.php");

		// Untuk mode ADD ditambah hidden field yang nilainya selalu "" untuk ID nya
		// Sedangkan untuk yang mark delete hanya ada di edit mode
		if (count($this->postData) > 0) {
			// Reset detail apapun ang terjadi
			$adjustment->Details = array();

			$ids = $this->GetPostValue("Id", array());
			$codes = $this->GetPostValue("Codes", array());
			$invIds = $this->GetPostValue("InvId", array());
			$notes = $this->GetPostValue("Note", array());
			$quantities = $this->GetPostValue("Qty", array());
			// Reference data yang dihapus
			$markDeletes = $this->GetPostValue("markDelete", array());

			$max = count($invIds);
			for ($i = 0; $i < $max; $i++) {
				$tokens = explode("|", $codes[$i]);
				$code = $tokens[0];
				$name = isset($tokens[1]) ? $tokens[1] : "";
				$unit = isset($tokens[2]) ? $tokens[2] : "";

				$detail = new AdjustmentDetail();
				$detail->Id = $ids[$i];
				$detail->ItemId = $invIds[$i];
				$detail->Note = $notes[$i];
				$detail->Qty = str_replace(",","", $quantities[$i]);
				$detail->UomCd = trim($unit);
				$detail->MarkedForDeletion = in_array($detail->Id, $markDeletes);

				$detail->ItemCode = trim($code);
				$detail->ItemName = trim($name);

				$adjustment->Details[] = $detail;
			}

			if ($this->ValidateDetail($adjustment, count($markDeletes))) {
				$this->persistence->SaveState("inventory.adjustment.master", $adjustment);
				if ($id == null) {
					redirect_url("inventory.adjustment/add?step=confirm");
				} else {
					redirect_url("inventory.adjustment/edit/$id?step=confirm");
				}
			}
		}

		$itemCategory = new ItemCategory();
		$itemCategories = $itemCategory->LoadByEntityId($adjustment->EntityId);
		$item = new Item();
		$items = $item->LoadNonAssetsByEntityId($adjustment->EntityId, "a.item_category_id, a.item_code");
		$details = array();
		foreach ($adjustment->Details as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}
		$itemCodes = array();
		foreach ($items as $item) {
			$itemCodes[$item->Id] = sprintf("%s|%s|%s|%d", $item->ItemCode, $item->ItemName, $item->UomCode, $item->CategoryId);
		}
		// Untuk sorting barang per kategori
		$buff = array();
		$prevCatId = null;
		$options = null;
		foreach ($items as $item) {
			if ($prevCatId != $item->CategoryId) {
				if ($options != null) {
					$buff[$prevCatId] = $options;
				}
				$prevCatId = $item->CategoryId;
				$options = '<option value="">-- PILIH BARANG --</option>';
			}

			$options .= sprintf('<option value="%d">%s - %s</option>', $item->Id, $item->ItemCode, $item->ItemName);
		}
		$buff[$prevCatId] = $options;
		$warehouse = new Warehouse();
		$warehouse->LoadById($adjustment->WarehouseId);

		$this->Set("adjustment", $adjustment);
		$this->Set("itemCategories", $itemCategories);
		$this->Set("items", $buff);
		$this->Set("itemCodes", $itemCodes);
		$this->Set("details", $details);
		$this->Set("warehouse", $warehouse);
	}

	private function ValidateDetail(Adjustment $adjustment, $totalDeleted = 0) {
		if (count($adjustment->Details) - $totalDeleted == 0) {
			$this->Set("error", "Maaf anda harus memasukkan sekurang-kurangnya 1 item yang akan di adjust");
			return false;
		}

		// Seperti biasa tidak boleh ada barang yang ganda pada adjustment
		$buff = array();
		$counter = 0;
		foreach ($adjustment->Details as $idx => $detail) {
			$counter++;
			if (in_array($detail->ItemId, $buff)) {
				// Boo... same item found !
				$this->Set("error", sprintf("Maaf barang #%d. %s (Kode: %s) sudah ada pada nomor-nomor sebelumnya.", $counter, $detail->ItemName, $detail->ItemCode));
				return false;
			} else {
				// Tidak ada...
				$buff[] = $detail->ItemId;
			}
			// Checking...
			if ($detail->ItemId == null) {
				$this->Set("error", sprintf("Maaf barang #%d belum dipilih jenis barangnya.", $counter));
				return false;
			}
			if ($detail->Qty == 0) {
				$this->Set("error", sprintf("Maaf barang #%d belum memiliki jumlah.", $counter));
				return false;
			}
		}

		return true;
	}

	private function ProcessConfirm($id = null) {
		if ($this->persistence->StateExists("inventory.adjustment.master")) {
			$adjustment = $this->persistence->LoadState("inventory.adjustment.master");
			if ($adjustment->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("inventory.adjustment/add?step=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("inventory.adjustment/edit/$id?step=master");
				}
				return;
			}
		} else {
			// Tidak ada session ???
			if ($id == null) {
				redirect_url("inventory.adjustment/add?step=master");
			} else {
				redirect_url("inventory.adjustment/edit/$id?step=master");
			}
			return;
		}

		require_once(MODEL . "inventory/warehouse.php");

		if (count($this->postData) > 0) {
			$this->connector->BeginTransaction();
			if ($id == null) {
				$adjustment->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doAdd($adjustment);
			} else {
				$adjustment->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doEdit($adjustment);
			}

			if ($rs) {
				// Sukses
				$this->connector->CommitTransaction();

				if ($id == null) {
					$this->persistence->SaveState("info", sprintf("Dokumen Adjustment: %s telah berhasil disimpan", $adjustment->DocumentNo));
				} else {
					$this->persistence->SaveState("info", sprintf("Perubahan data Dokumen Adjustment: %s telah disimpan", $adjustment->DocumentNo));
				}

				$this->persistence->DestroyState("inventory.adjustment.master");
				redirect_url("inventory.adjustment");
			} else {
				// Swt gagal...
				$this->connector->RollbackTransaction();
			}
		}

		$warehouse = new Warehouse();
		$warehouse->LoadById($adjustment->WarehouseId);

		$this->Set("adjustment", $adjustment);
		$this->Set("warehouse", $warehouse);
	}

	private function doAdd(Adjustment $adjustment) {
		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$adjustment->DocumentNo = $docCounter->AutoDocNoAdjustmentInventory($adjustment->EntityId, $adjustment->Date, 1);
		if ($adjustment->DocumentNo == null) {
			$this->Set("error", "Maaf anda tidak dapat membuat dokumen Adjustment pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
			return false;
		}

		$rs = $adjustment->Insert();
		if ($rs != 1) {
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan master Adjustment. Message: " . $this->connector->GetErrorMessage());
			}
			return false;
		}

		$counter = 0;
		foreach ($adjustment->Details as $idx => $detail) {
			$counter++;
			$detail->AdjustmentId = $adjustment->Id;
			$rs = $detail->Insert();
			if ($rs == 1) {
				// Lanjutttt
				continue;
			}

			// Gagal Insert Detail
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf barang No. $counter sudah ada pada database. Pastikan barang pada dokumen tidak ada yang sama");
			} else {
				$this->Set("error", "Maaf error saat simpan detail Adjustment No. $counter. Message: " . $this->connector->GetErrorMessage());
			}
			return false;
		}

		return true;
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen Adjustment terlebih dahulu.");
			redirect_url("inventory.adjustment");
			return;
		}

		$adjustment = new Adjustment();
		$adjustment = $adjustment->LoadById($id);
		if ($adjustment == null || $adjustment->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("inventory.adjustment");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($adjustment->EntityId != $this->userCompanyId) {
				// Access data beda Company ! Simulate not Found !
				$this->persistence->SaveState("error", "Mohon memilih dokumen Adjustment terlebih dahulu.");
				redirect_url("inventory.adjustment");
				return;
			}
		}

		require_once(MODEL . "master/company.php");
		$adjustment->LoadDetails();

		$company = new Company();
		$company = $company->LoadById($adjustment->EntityId);

		$this->Set("company", $company);
		$this->Set("adjustment", $adjustment);

		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen adjustment stock terlebih dahulu.");
			redirect_url("inventory.adjustment");
			return;
		}

		$step = strtolower($this->GetGetValue("step"));

		switch ($step) {
			case "master":
				$this->ProcessMaster($id);
				break;
			case "detail":
				$this->ProcessDetail($id);
				break;
			case "confirm":
				$this->ProcessConfirm($id);
				break;
			default:
				redirect_url("inventory.adjustment/edit/$id?step=master");
				return;
		}

		require_once(MODEL . "master/company.php");
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("step", $step);
		$this->Set("company", $company);
	}

	private function doEdit(Adjustment $adjustment) {
		$rs = $adjustment->Update($adjustment->Id);
		if ($rs != 1) {
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf No. Dokumen Adjustment Stock sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat merubah master Adjustment Stock. Message: " . $errMsg);
			}
			return false;
		}

		$counter = 0;
		foreach ($adjustment->Details as $detail) {
			// OK Cek untuk penghapusan data dulu
			if ($detail->MarkedForDeletion) {
				$rs = $detail->Delete($detail->Id);
				if ($rs == -1) {
					$this->Set("error", "Gagal hapus detail dengan ID: " . $detail->Id . ". Mohon hubungi system admin.");
					return false;
				}
			} else {
				$counter++;
				$detail->AdjustmentId = $adjustment->Id;
				if ($detail->Id == null) {
					$rs = $detail->Insert();
				} else {
					$rs = $detail->Update($detail->Id);
				}
			}

			// Untuk update gagal kalau -1 (0 = no error and nothing updated)
			if ($rs == 1 || $rs == 0) {
				// Lanjutttt
				continue;
			}

			// Gagal Insert Detail
			$this->Set("error", "Maaf error saat simpan/update detail No. $counter. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		return true;
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen adjustment stock terlebih dahulu sebelum proses penghapusan.");
			redirect_url("inventory.adjustment");
			return;
		}

		$adjustment = new Adjustment();
		$adjustment = $adjustment->LoadById($id);
		if ($adjustment == null || $adjustment->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($adjustment->EntityId != $this->userCompanyId) {
				// Access data beda Company ! Simulate not Found !
				$this->persistence->SaveState("error", "Mohon memilih dokumen adjustment stock terlebih dahulu.");
				redirect_url("inventory.adjustment");
				return;
			}
		}
		if ($adjustment->StatusCode > 1) {
			$this->persistence->SaveState("error", sprintf("Maaf dokumen adjustment stock %s sudah bukan berstatus DRAFT.", $adjustment->DocumentNo));
			redirect_url("inventory.adjustment/view/" . $adjustment->Id);
			return;
		}

		$adjustment->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($adjustment->Delete($adjustment->Id)) {
			$this->persistence->SaveState("info", sprintf("Dokumen adjustment stock %s telah dihapus dari system.", $adjustment->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus dokumen adjustment stock %s. Message: %s", $adjustment->DocumentNo, $this->connector->GetErrorMessage()));
		}

		redirect_url("inventory.adjustment");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("inventory.adjustment");
			return;
		}
		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/stock.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$adjustment = new Adjustment();
			$adjustment = $adjustment->LoadById($id);

			if ($adjustment->StatusCode != 1) {
				$errors[] = sprintf("Dokumen Adjustment: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $adjustment->DocumentNo, $adjustment->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($adjustment->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$adjustment->ApprovedById = $userId;

			// Pakai transaction karena kita harus ada entry barang ke gudang
			$this->connector->BeginTransaction();
			$rs = $adjustment->Approve($adjustment->Id);
			if ($rs != 1) {
				$errors[] = sprintf("Gagal Approve Dokumen Adjustment Stock: %s. Message: %s", $adjustment->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			$adjustment->LoadDetails();
			$items = array();
			$flagSuccess = true;

			foreach ($adjustment->Details as $detail) {
				if (isset($items[$detail->ItemId])) {
					$item = $items[$detail->ItemId];
				} else {
					$item = new Item();
					$item = $item->LoadById($detail->ItemId, true);

					$items[] = $item;
				}

				if ($item->AssetCategoryId == null) {
					// OK untuk SO akan bny case
					switch ($adjustment->Type) {
						case 1: // STOCK OPNAME
							if ($detail->Qty >= 0) {
								// OK stock di lapangan > stock computed jadi nilainya positif bearti kita akan masukkin barang ke gudang
								$flagSuccess = $this->AddStock($userId, $adjustment, $detail, $errors);
							} else {
								// Barang di lapangan < stock computed jadi nilainya negatif bearti kita akan kurangi barang
								$flagSuccess = $this->ReduceStock($userId, $adjustment, $detail, $errors);
							}
							break;
						case 2: // BARANG RUSAK (BS)
							// Klo rusak ya sudah pasti mengurangi stock....
							$flagSuccess = $this->ReduceStock($userId, $adjustment, $detail, $errors);
							break;
						default:
							$this->connector->RollbackTransaction(); // Just for safety...
							throw new Exception("Unsupported Adjustment Type ! Given Type: " . $adjustment->Type);
					}

					if (!$flagSuccess) {
						// Ops.. gagal...
						break; // Keluar dari loop detail
					}
				} else {
					// Hwee kok ada asset yang dikeluarkan lewat GN ??
					$errors[] = sprintf("Gagal Adjustment: %s -> Barang: %s. Message: Barang Berupa Asset !", $adjustment->DocumentNo, $item->ItemName);
					$flagSuccess = false;
					break; // Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
				}
			} // End Loop: foreach ($itemIssue->Details as $detail) {

			// Kita menggunakan transaction per level GN bukan semua approval
			if ($flagSuccess) {
				$infos[] = sprintf("Dokumen Adjustment: %s sudah berhasil di approve", $adjustment->DocumentNo);
				$this->connector->CommitTransaction();
			} else {
				$this->connector->RollbackTransaction();
			}
		} // END LOOP foreach ($ids => $id)

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("inventory.adjustment");
	}

	private function AddStock($userId, Adjustment $adjustment, AdjustmentDetail $detail, &$errors) {
		$stock = new Stock();

		$stock->CreatedById = $userId;
		$stock->StockTypeCode = 2; // Barang dari Stock Opname (+)
		$stock->ReferenceId = $detail->Id;
		$stock->Date = $adjustment->Date;
		$stock->WarehouseId = $adjustment->WarehouseId;
		$stock->ItemId = $detail->ItemId;
		$stock->Qty = $detail->Qty;
		$stock->UomCd = $detail->UomCd;
		$stock->Price = $detail->Price;
		$stock->UseStockId = null;
		$stock->QtyBalance = $stock->Qty; // Stock IN maka by default balance = qty

		$rs = $stock->Insert();
		if ($rs != 1) {
			$errors[] = sprintf("Gagal entry data stock SO: %s -> Barang: %s. Message: %s", $adjustment->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
			return false;
		} else {
			return true;
		}
	}

	private function ReduceStock($userId, Adjustment $adjustment, AdjustmentDetail $detail, &$errors) {
		$stock = new Stock();
		$stocks = $stock->LoadStocksFifo($detail->ItemId, $detail->UomCd);

		// Set variable-variable pendukung
		$remainingQty = abs($detail->Qty);				// Karena kalau dari stock opname akan bernilai (-)
		$detail->TotalCost = 0;

		foreach ($stocks as $stock) {
			// Buat object buat issue nya
			$issue = new Stock();
			$issue->CreatedById = $userId;
			if ($adjustment->Type == 1) {
				$issue->StockTypeCode = 102;			// Stock Opname (-)
			} else {
				if ($adjustment->Type == 2) {
					$issue->StockTypeCode = 103;		// Barang Rusak (BS)
				} else {
					// Harusnya ga bisa masuk sini uda mati di switch atas
					throw new Exception("Weirdo.... harusnya bukan ini yang throw exception tapi dari switch case");
				}
			}
			$issue->ReferenceId = $detail->Id;
			$issue->Date = $adjustment->Date;
			$issue->WarehouseId = $stock->WarehouseId;	// Barang yang dikeluarkan pasti dari gudang yang sama dengan stock barang !
			$issue->ItemId = $detail->ItemId;
			//$issue->Qty = $stock->QtyBalance;			// Depend on case...
			$issue->UomCd = $detail->UomCd;
			$issue->Price = $stock->Price;				// Ya pastilah pake angka ini...
			$issue->UseStockId = $stock->Id;			// Kasi tau kalau issue ini based on stock id mana
			$issue->QtyBalance = null;					// Klo issue harus NULL

			$stock->UpdatedById = $userId;

			if ($remainingQty > $stock->QtyBalance) {
				// Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
				$issue->Qty = $stock->QtyBalance;		// Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya

				$remainingQty -= $stock->QtyBalance;	// Kita masih perlu... jadi kurangi berdasarkan stok yang ada
				$stock->QtyBalance = 0; // Habis...
			} else {
				// Barang di gudang mencukupi atau PAS
				$issue->Qty = $remainingQty;

				$stock->QtyBalance -= $remainingQty;
				$remainingQty = 0;
			}

			// Apapun yang terjadi masukkan data issue stock
			if ($issue->Insert() != 1) {
				$errors[] = sprintf("Gagal SO: %s -> Barang: %s. Message: Gagal entry data pengeluaran barang. Message: %s", $adjustment->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
				return false;
			}
			// Update Qty Balance
			if ($stock->Update($stock->Id) != 1) {
				$errors[] = sprintf("Gagal SO: %s -> Barang: %s. Message: gagal update data stock ! Message: %s", $adjustment->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
				return false;
			}
			// OK jangan lupa update data cost
			$detail->TotalCost += $issue->Qty * $issue->Price;
			if ($remainingQty <= 0) {
				// Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
				break;
			}
		} // End Loop: foreach ($stocks as $stock) {

		// Nah sekarang saatnya checking barang cukup atau tidak
		if ($remainingQty > 0) {
			// WTF... barang tidak cukup !!!
			$errors[] = sprintf("Gagal issue stock SO: %s -> Barang: %s. Message: Barang pada gudang tidak mencukupi ! Stock Opname bila diperlukan !", $adjustment->DocumentNo, $detail->ItemName);
			return false;
		}
		// Update total cost
		if ($detail->UpdateCost($detail->Id) != 1) {
			// WTF... barang tidak cukup !!!
			$errors[] = sprintf("Gagal issue stock SO: %s -> Barang: %s. Message: gagal update total cost ! Message: %s", $adjustment->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
			return false;
		}

		return true;
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Adjustment yang akan di batalkan !");
			redirect_url("inventory.adjustment");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$adjustment = new Adjustment();
			$adjustment = $adjustment->LoadById($id);

			if ($adjustment->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Adjustment Stock: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $adjustment->DocumentNo, $adjustment->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($adjustment->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$adjustment->UpdatedById = $userId;

			// Pakai transaction karena kita harus membatalkan barang2 yang sudah di issue dari gudang !!
			// DAN yang super kehednya ini SO merupakan gabungan 2 modul yang mirip yaitu GN dan IS jadi metode approve dan disapprove oge sama-sama SUSAH !!!
			$this->connector->BeginTransaction();

			// Jika ada stock opname yang bersifat menambah barang maka cek dulu stock itu pernah di issue atau tidak
			$this->connector->CommandText =
"SELECT COUNT(a.id)
FROM ic_stock AS a
WHERE a.is_deleted = 0 AND a.use_stock_id IN (
	-- Ambil ID stock yang direfer oleh Stock Opname (INGAT stock_type harus = 2)
	SELECT aa.id
	FROM ic_stock AS aa
	WHERE aa.is_deleted = 0 AND aa.stock_type = 2 AND aa.reference_id IN (
		-- Ambil id detail GN karena ini digunakan di stock
		SELECT id FROM ic_adjustment_detail WHERE adjustment_master_id = ?id
	)
)";
			$this->connector->AddParameter("?id", $id);
			$rs = $this->connector->ExecuteScalar();
			if ($rs > 0) {
				// Hwee... masih dipake ga boleh dihapus SO nya...
				$errors[] = sprintf("Gagal Disapprove Adjustment Stock: %s ! Stock barang sudah pernah di issue. Lakukan tracking barang !", $adjustment->DocumentNo);
				$this->connector->RollbackTransaction();
				continue;
			}

			// NOTICE: Untuk hapus stock nya akan bareng dengan hapus stock yang mengurangi barang
			// Berikut sudah merupakan modul yang mirip dengan disapprove Item Issue

			// Update qty_balance terlebih dahulu
			$this->connector->CommandText =
"UPDATE ic_stock AS a JOIN ic_stock AS b ON a.id = b.use_stock_id AND b.is_deleted = 0 SET
	qty_balance = a.qty_balance + b.qty
	, updateby_id = ?user
	, update_time = NOW()
WHERE b.stock_type IN (102, 103) AND b.reference_id IN (
	SELECT id FROM ic_adjustment_detail WHERE adjustment_master_id = ?id
);";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove Adjustment Stock: %s ! Gagal menarik stock barang yang dikeluarkan ! Message: %s", $adjustment->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK remove semua barang akibat dari SO (baik yang + atau -)
			$this->connector->CommandText =
"UPDATE ic_stock SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE is_deleted = 0 AND stock_type IN (2, 102, 103) AND reference_id IN (
	SELECT id FROM ic_adjustment_detail WHERE adjustment_master_id = ?id
)";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove Adjustment Stock: %s ! Gagal menghapus barang pada table stock ! Message: %s", $adjustment->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK Hapus total cost
			$this->connector->CommandText = "UPDATE ic_adjustment_detail SET total_cost = NULL WHERE adjustment_master_id = ?id";
			$this->connector->AddParameter("?id", $id);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// DAFUQ !!!! step terakhir gagal ???
				$errors[] = sprintf("Gagal Disapprove Adjustment Stock: %s ! Gagal update total_cost ! Message: %s", $adjustment->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK everything is green
			$rs = $adjustment->DisApprove($id);
			if ($rs != -1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Adjustment Stock: %s sudah berhasil di dibatalkan (disapprove)", $adjustment->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen Adjustment Stock: %s. Message: %s", $adjustment->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("inventory.adjustment");
	}
}


// End of File: adjustment_controller.php
