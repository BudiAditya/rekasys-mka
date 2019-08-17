<?php
/**
 * Modul SO yang awalnya ada pada AjustmentController dipindah kesini
 * REASON: cara inputnya berbeda
 */
class SoController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "inventory/stock_opname.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        $settings["columns"][] = array("name" => "d.project_name", "display" => "Project", "width" => 100);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Dokumen", "width" => 120);
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.so_Date, '%d %M %Y')", "display" => "Tgl. Update", "width" => 80);
		$settings["columns"][] = array("name" => "a.note", "display" => "Keterangan", "width" => 300);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No. Dokumen");
		$settings["filters"][] = array("name" => "d.project_name", "display" => "Project");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Stock Opname (SO)";

			if ($acl->CheckUserAccess("so", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.so/add?step=master", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("so", "view", "inventory")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "inventory.so/view/%s", "Class" => "bt_view", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen Stock Opname terlebih dahulu sebelum melihat data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("so", "edit", "inventory")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.so/edit/%s?step=master", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen Stock Opname terlebih dahulu sebelum mengupdate data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if ($acl->CheckUserAccess("so", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.so/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen Stock Opname terlebih dahulu sebelum menghapus data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakan anda yakin mau menghapus dokumen Stock Opname yang dipilih ?\nKlik OK untuk menghapus data yang dipilih.");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("so", "batch_approve", "inventory")) {
				$settings["actions"][] = array("Text" => "Batch Approve", "Url" => "inventory.so/batch_approve", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Stock Opname !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua dokumen Stock Opname yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("so", "batch_disapprove", "inventory")) {
				$settings["actions"][] = array("Text" => "Batch Dis-Approve", "Url" => "inventory.so/batch_disapprove", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Stock Opname !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen Stock Opname yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Hapus Cache apapun yang terjadi
			$this->persistence->DestroyState("inventory.so.master");
		} else {
			// NOTE: untuk status masih tetap menggunakan key adjustment_status karena SO merupakan subset dari adjustment
			$settings["from"] = "ic_so_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'adjustment_status'
	JOIN cm_project AS d ON a.project_id = d.id";

			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;

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
				redirect_url("inventory.so/add?step=master");
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
		if ($this->persistence->StateExists("inventory.so.master")) {
			$stockOpname = $this->persistence->LoadState("inventory.so.master");
			if ($stockOpname->Id != $id) {
				// ID pada session beda ?
				if ($id == null) {
					redirect_url("inventory.so/add?step=master");
				} else {
					redirect_url("inventory.so/edit/$id?step=master");
				}
			} else {
				$sessionValid = true;
			}
		} else {
			$stockOpname = new StockOpname();

			if ($id == null) {
				$stockOpname->EntityId = $this->userCompanyId;
				$stockOpname->Date = time();
				$stockOpname->DocumentNo = "[AUTO]";
			} else {
				$stockOpname = $stockOpname->LoadById($id);
				if ($stockOpname == null || $stockOpname->IsDeleted) {
					$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
					redirect_url("inventory.so");
					return;
				}
				if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
					if ($stockOpname->EntityId != $this->userCompanyId) {
						// SIMULATE NOT FOUND !
						$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
						redirect_url("inventory.so");
						return;
					}
				}
				if ($stockOpname->StatusCode > 2) {
					$this->persistence->SaveState("error", "Maaf dokumen yang diminta sudah tidak bersifat DRAFT !");
					redirect_url("inventory.so/view/" . $stockOpname->Id);
					return;
				}
			}
		}

		require_once(MODEL . "master/project.php");

		// OK... process data
		if (count($this->postData) > 0) {
			$stockOpname->EntityId = $this->userCompanyId;
			$stockOpname->DocumentNo = $this->GetPostValue("DocumentNo");
			$stockOpname->Date = strtotime($this->GetPostValue("Date"));
			$stockOpname->ProjectId = $this->GetPostValue("Project");
			$stockOpname->Note = $this->GetPostValue("Note");
			$stockOpname->StatusCode = 1;

			if ($this->ValidateMaster($stockOpname)) {

				if ($id == null) {
					require_once(MODEL . "common/doc_counter.php");
					$docCounter = new DocCounter();
					$stockOpname->DocumentNo = $docCounter->AutoDocNoSo($this->userCompanyId, $stockOpname->Date, 0);
					if ($stockOpname->DocumentNo != null) {
						$this->persistence->SaveState("inventory.so.master", $stockOpname);
						redirect_url("inventory.so/add?step=detail");
					} else {
						$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
						$stockOpname->DocumentNo = "[LOCKED]";
					}
				} else {
					if (!$sessionValid) {
						$stockOpname->LoadDetails();
					}

					$this->persistence->SaveState("inventory.so.master", $stockOpname);
					redirect_url("inventory.so/edit/$id?step=detail");
				}
			}
		}

		$warehouse = new Project();

		$this->Set("stockOpname", $stockOpname);
		$this->Set("warehouses", $warehouse->LoadByEntityId($stockOpname->EntityId));
	}

	private function ValidateMaster(StockOpname $stockOpname) {
		if ($stockOpname->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu");
			return false;
		}
		if ($stockOpname->DocumentNo == null) {
			$this->Set("error", "Mohon memasukkan no dokumen terlebih dahulu");
			return false;
		}
		if (!is_int($stockOpname->Date)) {
			$this->Set("error", "Mohon masukkan tanggal Stock Opname Stock terlebih dahulu");
			return false;
		}
		if ($stockOpname->ProjectId == null) {
			$this->Set("error", "Mohon memilih gudang yang dilakukan proses Stock Opname");
			return false;
		}

		return true;
	}

	private function ProcessDetail($id = null) {
		if ($this->persistence->StateExists("inventory.so.master")) {
			$stockOpname = $this->persistence->LoadState("inventory.so.master");
			if ($stockOpname->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("inventory.so/add?step=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("inventory.so/edit/$id?step=master");
				}
				return;
			}
		} else {
			// Tidak ada session ???
			if ($id == null) {
				redirect_url("inventory.so/add?step=master");
			} else {
				redirect_url("inventory.so/edit/$id?step=master");
			}
			return;
		}

		require_once(MODEL . "inventory/item_category.php");
		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "master/project.php");

		// Untuk mode ADD ditambah hidden field yang nilainya selalu "" untuk ID nya
		// Sedangkan untuk yang mark delete hanya ada di edit mode
		if (count($this->postData) > 0) {
			// Reset detail apapun ang terjadi
			$stockOpname->Details = array();

			$ids = $this->GetPostValue("Id", array());
			$itemIds = $this->GetPostValue("Item", array());
			$itemCodes = $this->GetPostValue("Code", array());
			$itemNames = $this->GetPostValue("Name", array());
			$itemQtys = $this->GetPostValue("Qty", array());
			$itemUoms = $this->GetPostValue("Uom", array());
			$prices = $this->GetPostValue("Price", array());
			$markDeletes = $this->GetPostValue("markDelete", array());

			$max = count($itemIds);
			for ($i = 0; $i < $max; $i++) {
				$detail = new StockOpnameDetail();
				$detail->Id = $ids[$i];
				$detail->ItemId = $itemIds[$i];
				$detail->UomCd = trim($itemUoms[$i]);
				$detail->QtySo = $itemQtys[$i];
				$detail->Price = str_replace(",", "", $prices[$i]);

				$detail->ItemCode = $itemCodes[$i];
				$detail->ItemName = $itemNames[$i];
				$detail->MarkedForDeletion = in_array($detail->Id, $markDeletes);

				$stockOpname->Details[] = $detail;
			}

			if ($this->ValidateDetail($stockOpname, count($markDeletes))) {
				$this->persistence->SaveState("inventory.so.master", $stockOpname);
				if ($id == null) {
					redirect_url("inventory.so/add?step=confirm");
				} else {
					redirect_url("inventory.so/edit/$id?step=confirm");
				}
			}
		}

		$item = new Item();
		$items = $item->LoadByEntityId($stockOpname->EntityId);
		$details = array();
		$warehouse = new Project();
		$warehouse->LoadById($stockOpname->ProjectId);

		$autoCompleteJson = array();
		foreach ($items as $item) {
			//$autoCompleteJson[] = $item->ItemCode . " - " . $item->ItemName;
			$autoCompleteJson[] = array("label" => $item->ItemCode . " - " . $item->ItemName, "id" => $item->Id, "code" => $item->ItemCode, "name" => $item->ItemName,
										"uom" => $item->UomCode);
		}
		$detailsJson = array();
		foreach ($stockOpname->Details as $detail) {
			$detailsJson[] = $detail->ToJsonFriendly();
		}

		$this->Set("stockOpname", $stockOpname);
		$this->Set("detailsJson", $detailsJson);
		$this->Set("items", $items);
		$this->Set("autoCompleteJson", $autoCompleteJson);
		$this->Set("details", $details);
		$this->Set("warehouse", $warehouse);
	}

	private function ValidateDetail(StockOpname $stockOpname, $totalDeleted = 0) {
		if (count($stockOpname->Details) - $totalDeleted == 0) {
			$this->Set("error", "Maaf anda harus memasukkan sekurang-kurangnya 1 item yang akan di Stock Opname");
			return false;
		}

		// Seperti biasa tidak boleh ada barang yang ganda pada Stock Opname
		$buff = array();
		$counter = 0;
		foreach ($stockOpname->Details as $idx => $detail) {
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
			if ($detail->QtySo < 0) {
				$this->Set("error", sprintf("Maaf jumlah barang #%d kurang dari 0.", $counter));
				return false;
			}
			if ($detail->Price < 0) {
				$this->Set("error", sprintf("Maaf harga satuan #%d kurang dari 0. Jika menggunakan harga terkahir harap isi dengan nol."));
				return false;
			}
		}

		// Hmm ok sekarang kita harus cari jumlah stock barangnya... (Jika sedang dalam mode ADD)
		require_once(MODEL . "inventory/stock.php");
		$this->connector->CommandText = Stock::QUERY_STOCK_BY_WAREHOUSE;
		// ToDo: Jika ada perubahan pada prosedur SO yang mana mencatat sampai ke jam maka proses dibawah tidak perlu ditambah 23:59:59
		// Sudah ada kesepakatan jika pada hari SO ada Item Issue maka Issue akan dilakukan pada esok harinya walau barang sudah diambil
		// BAHAYA ? Tidak karena qty yang dicatat pada saat SO adalah QTY sebelum di issue
		$this->connector->AddParameter("?date", date(SQL_DATETIME, $stockOpname->Date + 86399));
		$this->connector->AddParameter("?warehouseId", $stockOpname->ProjectId);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			$this->Set("error", "Gagal ambil data stock untuk proses SO ! Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// Simpan data stock dulu buat kalkulasi
		$buff = array();
		while ($row = $rs->FetchAssoc()) {
			$buff[$row["item_id"]] = $row;
		}

		// OK kita hitung selisih
		foreach ($stockOpname->Details as $detail) {
			$detail->QtyComputed = isset($buff[$detail->ItemId]) ? $buff[$detail->ItemId]["qty_stock"] : 0;
		}

		return true;
	}

	private function ProcessConfirm($id = null) {
		if ($this->persistence->StateExists("inventory.so.master")) {
			$stockOpname = $this->persistence->LoadState("inventory.so.master");
			if ($stockOpname->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("inventory.so/add?step=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("inventory.so/edit/$id?step=master");
				}
				return;
			}
		} else {
			// Tidak ada session ???
			if ($id == null) {
				redirect_url("inventory.so/add?step=master");
			} else {
				redirect_url("inventory.so/edit/$id?step=master");
			}
			return;
		}

		require_once(MODEL . "master/project.php");

		if (count($this->postData) > 0) {
			$this->connector->BeginTransaction();
			if ($id == null) {
				$stockOpname->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doAdd($stockOpname);
			} else {
				$stockOpname->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doEdit($stockOpname);
			}

			if ($rs) {
				// Sukses
				$this->connector->CommitTransaction();

				if ($id == null) {
					$this->persistence->SaveState("info", sprintf("Stock Opname: %s telah berhasil disimpan", $stockOpname->DocumentNo));
				} else {
					$this->persistence->SaveState("info", sprintf("Perubahan data Stock Opname: %s telah disimpan", $stockOpname->DocumentNo));
				}

				$this->persistence->DestroyState("inventory.so.master");
				redirect_url("inventory.so");
			} else {
				// Swt gagal...
				$this->connector->RollbackTransaction();
			}
		}

		$warehouse = new Project();
		$warehouse->LoadById($stockOpname->ProjectId);

		$this->Set("stockOpname", $stockOpname);
		$this->Set("warehouse", $warehouse);
	}

	private function doAdd(StockOpname $stockOpname) {
		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$stockOpname->DocumentNo = $docCounter->AutoDocNoSo($stockOpname->EntityId, $stockOpname->Date, 1);
		if ($stockOpname->DocumentNo == null) {
			$this->Set("error", "Maaf anda tidak dapat membuat Stock Opname pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
			return false;
		}

		$rs = $stockOpname->Insert();
		if ($rs != 1) {
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan master Stock Opname. Message: " . $this->connector->GetErrorMessage());
			}
			return false;
		}

		$counter = 0;
		foreach ($stockOpname->Details as $idx => $detail) {
			$counter++;
			$detail->StockOpnameId = $stockOpname->Id;
			$rs = $detail->Insert();
			if ($rs == 1) {
				// Lanjutttt
				continue;
			}

			// Gagal Insert Detail
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf barang No. $counter sudah ada pada database. Pastikan barang pada dokumen tidak ada yang sama");
			} else {
				$this->Set("error", "Maaf error saat simpan detail Stock Opname No. $counter. Message: " . $this->connector->GetErrorMessage());
			}
			return false;
		}

		return true;
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen Stock Opname terlebih dahulu.");
			redirect_url("inventory.so");
			return;
		}

		$stockOpname = new StockOpname();
		$stockOpname = $stockOpname->LoadById($id);
		if ($stockOpname == null || $stockOpname->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("inventory.so");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($stockOpname->EntityId != $this->userCompanyId) {
				// Access data beda Company ! Simulate not Found !
				$this->persistence->SaveState("error", "Mohon memilih dokumen Stock Opname terlebih dahulu.");
				redirect_url("inventory.so");
				return;
			}
		}

		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/project.php");
		$stockOpname->LoadDetails();

		$company = new Company();
		$company = $company->LoadById($stockOpname->EntityId);
		$warehouse = new Project();
		$warehouse = $warehouse->LoadById($stockOpname->ProjectId);

		$this->Set("company", $company);
		$this->Set("warehouse", $warehouse);
		$this->Set("stockOpname", $stockOpname);

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
			$this->persistence->SaveState("error", "Mohon memilih dokumen Stock Opname terlebih dahulu.");
			redirect_url("inventory.so");
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
				redirect_url("inventory.so/edit/$id?step=master");
				return;
		}

		require_once(MODEL . "master/company.php");
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("step", $step);
		$this->Set("company", $company);
	}

	private function doEdit(StockOpname $stockOpname) {
		$rs = $stockOpname->Update($stockOpname->Id);
		if ($rs != 1) {
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf No. Dokumen Stock Opname sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat merubah master Stock Opname. Message: " . $errMsg);
			}
			return false;
		}

		$counter = 0;
		foreach ($stockOpname->Details as $detail) {
			// OK Cek untuk penghapusan data dulu
			if ($detail->MarkedForDeletion) {
				$rs = $detail->Delete($detail->Id);
				if ($rs == -1) {
					$this->Set("error", "Gagal hapus detail dengan ID: " . $detail->Id . ". Mohon hubungi system admin.");
					return false;
				}
			} else {
				$counter++;
				$detail->StockOpnameId = $stockOpname->Id;
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
			$this->persistence->SaveState("error", "Mohon memilih dokumen Stock Opname terlebih dahulu sebelum proses penghapusan.");
			redirect_url("inventory.so");
			return;
		}

		$stockOpname = new StockOpname();
		$stockOpname = $stockOpname->LoadById($id);
		if ($stockOpname == null || $stockOpname->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($stockOpname->EntityId != $this->userCompanyId) {
				// Access data beda Company ! Simulate not Found !
				$this->persistence->SaveState("error", "Mohon memilih dokumen Stock Opname terlebih dahulu.");
				redirect_url("inventory.so");
				return;
			}
		}
		if ($stockOpname->StatusCode > 1) {
			$this->persistence->SaveState("error", sprintf("Maaf dokumen Stock Opname %s sudah bukan berstatus DRAFT.", $stockOpname->DocumentNo));
			redirect_url("inventory.so/view/" . $stockOpname->Id);
			return;
		}

		$stockOpname->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($stockOpname->Delete($stockOpname->Id)) {
			$this->persistence->SaveState("info", sprintf("Dokumen Stock Opname %s telah dihapus dari system.", $stockOpname->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus dokumen Stock Opname %s. Message: %s", $stockOpname->DocumentNo, $this->connector->GetErrorMessage()));
		}

		redirect_url("inventory.so");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("inventory.so");
			return;
		}
		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/stock.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$stockOpname = new StockOpname();
			$stockOpname = $stockOpname->LoadById($id);

			if ($stockOpname->StatusCode != 1) {
				$errors[] = sprintf("Dokumen Stock Opname: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $stockOpname->DocumentNo, $stockOpname->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($stockOpname->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$stockOpname->ApprovedById = $userId;

			// Pakai transaction karena kita harus ada entry barang ke gudang
			$this->connector->BeginTransaction();
			$rs = $stockOpname->Approve($stockOpname->Id);
			if ($rs != 1) {
				$errors[] = sprintf("Gagal Approve Dokumen Stock Opname: %s. Message: %s", $stockOpname->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			$stockOpname->LoadDetails();
			$items = array();
			$flagSuccess = true;

			foreach ($stockOpname->Details as $detail) {
				if (isset($items[$detail->ItemId])) {
					$item = $items[$detail->ItemId];
				} else {
					$item = new Item();
					$item = $item->LoadById($detail->ItemId, true);

					$items[$detail->ItemId] = $item;
				}

				if ($item->AssetCategoryId == null) {
					$diff = $detail->QtySo - $detail->QtyComputed;
					if ($diff == 0) {
						// Tidak ada kesalahan pencatatan....
						continue;
					} else  if ($diff > 0) {
						// OK stock di lapangan > stock computed jadi nilainya positif bearti kita akan masukkin barang ke gudang
						$flagSuccess = $this->AddStock($userId, $stockOpname, $detail, $errors);
					} else {
						// Diff < 0
						// Barang di lapangan < stock computed jadi nilainya negatif bearti kita akan kurangi barang
						$flagSuccess = $this->ReduceStock($userId, $stockOpname, $detail, $errors);
					}

					if (!$flagSuccess) {
						// Ops.. gagal...
						break; // Keluar dari loop detail
					}
				} else {
					// Hwee kok ada asset yang dikeluarkan lewat Stock Opname ??
					$errors[] = sprintf("Gagal Stock Opname: %s -> Barang: %s. Message: Barang Berupa Asset !", $stockOpname->DocumentNo, $item->ItemName);
					$flagSuccess = false;
					break; // Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
				}
			} // End Loop: foreach ($itemIssue->Details as $detail) {

			// Kita menggunakan transaction per level GN bukan semua approval
			if ($flagSuccess) {
				$infos[] = sprintf("Dokumen Stock Opname: %s sudah berhasil di approve", $stockOpname->DocumentNo);
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
		redirect_url("inventory.so");
	}

	private function AddStock($userId, StockOpname $stockOpname, StockOpnameDetail $detail, &$errors) {
		$stock = new Stock();

		$stock->CreatedById = $userId;
		$stock->StockTypeCode = 2;								// Barang dari Stock Opname (+)
		$stock->ReferenceId = $detail->Id;
		$stock->Date = $stockOpname->Date;
		$stock->ProjectId = $stockOpname->ProjectId;
		$stock->ItemId = $detail->ItemId;
		$stock->Qty = $detail->QtySo - $detail->QtyComputed;	// Untuk case ini sudah pasti > 0 karena sudah di filter dari proses sebelumnya
		$stock->UomCd = $detail->UomCd;
		if ($detail->Price <= 0) {
			// ToDo: Cari harga barang masuk akibat SO. Saat ini pakai harga terakhir... Jika tidak ada agar tidak error maka gw tembak 0 saja
			$histories = $stock->LoadStocksFifo($detail->ItemId, $detail->UomCd);
			$lastStock = end($histories);

			$stock->Price = $lastStock !== false ? $lastStock->Price : 0;
		} else {
			$stock->Price = $detail->Price;
		}
		$stock->UseStockId = null;
		$stock->QtyBalance = $stock->Qty; 						// Stock IN maka by default balance = qty

		$rs = $stock->Insert();
		if ($rs != 1) {
			$errors[] = sprintf("Gagal entry data stock SO: %s -> Barang: %s. Message: %s", $stockOpname->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
			return false;
		} else {
			return true;
		}
	}

	private function ReduceStock($userId, StockOpname $stockOpname, StockOpnameDetail $detail, &$errors) {
		$stock = new Stock();
		$stocks = $stock->LoadStocksFifo($detail->ItemId, $detail->UomCd);

		// Set variable-variable pendukung
		$remainingQty = abs($detail->QtySo - $detail->QtyComputed);		// Karena kalau hilang maka akan bernilai (-)
		$detail->TotalCost = 0;

		foreach ($stocks as $stock) {
			// Buat object buat issue nya
			$issue = new Stock();
			$issue->CreatedById = $userId;
			$issue->StockTypeCode = 102;				// Stock Opname (-)
			$issue->ReferenceId = $detail->Id;
			$issue->Date = $stockOpname->Date;
			$issue->ProjectId = $stock->ProjectId;	// Barang yang dikeluarkan pasti dari gudang yang sama dengan stock barang !
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
				$errors[] = sprintf("Gagal SO: %s -> Barang: %s. Message: Gagal entry data pengeluaran barang. Message: %s", $stockOpname->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
				return false;
			}
			// Update Qty Balance
			if ($stock->Update($stock->Id) != 1) {
				$errors[] = sprintf("Gagal SO: %s -> Barang: %s. Message: gagal update data stock ! Message: %s", $stockOpname->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
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
			$errors[] = sprintf("Gagal issue stock SO: %s -> Barang: %s. Message: Barang pada gudang tidak mencukupi ! Stock Opname bila diperlukan !", $stockOpname->DocumentNo, $detail->ItemName);
			return false;
		}
		// Update total cost
		if ($detail->UpdateCost($detail->Id) != 1) {
			// WTF... gagal update cost....
			$errors[] = sprintf("Gagal issue stock SO: %s -> Barang: %s. Message: gagal update total cost ! Message: %s", $stockOpname->DocumentNo, $detail->ItemName, $this->connector->GetErrorMessage());
			return false;
		}

		return true;
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Stock Opname yang akan di batalkan !");
			redirect_url("inventory.so");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$stockOpname = new StockOpname();
			$stockOpname = $stockOpname->LoadById($id);

			if ($stockOpname->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Stock Opname: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $stockOpname->DocumentNo, $stockOpname->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($stockOpname->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$stockOpname->UpdatedById = $userId;

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
		-- Ambil id detail SO karena ini digunakan di stock
		SELECT id FROM ic_so_detail WHERE so_master_id = ?id
	)
)";
			$this->connector->AddParameter("?id", $id);
			$rs = $this->connector->ExecuteScalar();
			if ($rs > 0) {
				// Hwee... masih dipake ga boleh dihapus SO nya...
				$errors[] = sprintf("Gagal Disapprove Stock Opname: %s ! Stock barang sudah pernah di issue. Lakukan tracking barang !", $stockOpname->DocumentNo);
				$this->connector->RollbackTransaction();
				continue;
			}

			// NOTICE: Untuk hapus stock nya akan bareng dengan hapus stock yang mengurangi barang
			// Berikut sudah merupakan modul yang mirip dengan disapprove Item Issue

			// Update qty_balance terlebih dahulu (untuk barang yang dikeluarkan akibat SO harus di tambahkan terlebih dahulu)
			$this->connector->CommandText =
"UPDATE ic_stock AS a JOIN ic_stock AS b ON a.id = b.use_stock_id AND b.is_deleted = 0 SET
	qty_balance = a.qty_balance + b.qty
	, updateby_id = ?user
	, update_time = NOW()
WHERE b.stock_type = 102 AND b.reference_id IN (
	SELECT id FROM ic_so_detail WHERE so_master_id = ?id
);";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove Stock Opname: %s ! Gagal menarik stock barang yang dikeluarkan ! Message: %s", $stockOpname->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK remove semua barang akibat dari SO (baik yang + atau -)
			$this->connector->CommandText =
"UPDATE ic_stock SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE is_deleted = 0 AND stock_type IN (2, 102) AND reference_id IN (
	SELECT id FROM ic_so_detail WHERE so_master_id = ?id
)";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove Stock Opname: %s ! Gagal menghapus barang pada table stock ! Message: %s", $stockOpname->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK Hapus total cost
			$this->connector->CommandText = "UPDATE ic_so_detail SET total_cost = NULL WHERE so_master_id = ?id";
			$this->connector->AddParameter("?id", $id);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// DAFUQ !!!! step terakhir gagal ???
				$errors[] = sprintf("Gagal Disapprove Stock Opname: %s ! Gagal update total_cost ! Message: %s", $stockOpname->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK everything is green
			$rs = $stockOpname->DisApprove($id);
			if ($rs != -1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Stock Opname: %s sudah berhasil di dibatalkan (disapprove)", $stockOpname->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen Stock Opname: %s. Message: %s", $stockOpname->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("inventory.so");
	}
}


// End of File: so_controller.php
