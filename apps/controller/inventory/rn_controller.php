<?php

class RnController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "inventory/rrn.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		//$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
        $settings["columns"][] = array("name" => "concat(e.project_cd,' - ',e.project_name)", "display" => "Project", "width" => 120);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "RN Number", "width" => 120);
        $settings["columns"][] = array("name" => "c.creditor_name", "display" => "Vendor", "width" => 200);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.rn_date, '%d %M %Y')", "display" => "RN Date", "width" => 80);
		$settings["columns"][] = array("name" => "d.short_desc", "display" => "Status", "width" => 80);
        $settings["columns"][] = array("name" => "a.invoice_no", "display" => "AP Invoice", "width" => 120);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "Nomor RN");
		$settings["filters"][] = array("name" => "c.creditor_name", "display" => "Supplier");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "RRN List";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("inventory.rn", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.rn/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.rn", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.rn/add/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen RN terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.rn", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.rn/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen RN terlebih dahulu sebelum melakukan proses delete !\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
											   "Confirm" => "Apakah anda mau menghapus Dokumen RN yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("inventory.rn", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.rn/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.rn/overview", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.rn", "doc_print")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.rn/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
            }
            if ($acl->CheckUserAccess("inventory.rn", "doc_print")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.rn/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.rn", "batch_approve")) {
				$settings["actions"][] = array("Text" => "Approve", "Url" => "inventory.rn/batch_approve/", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu Dokumen RN !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua Dokumen RN yang dipilih ?\nKetika GN berhasil di approve maka akan menambah stock gudang");
			}
			if ($acl->CheckUserAccess("inventory.rn", "batch_disapprove")) {
				$settings["actions"][] = array("Text" => "Batal Approve", "Url" => "inventory.rn/batch_disapprove/", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu Dokumen RN !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua Dokumen RN yang dipilih ?\nProses Dis-Approve Akan membuat status dokumen menjadi DRAFT dan membatalkan transaksi barang / item yang bersangkutan.");
			}
			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Kill Session
			$this->persistence->DestroyState("inventory.rn.gn");
		} else {
			$settings["from"] = "ic_rn_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ap_creditor_master AS c ON a.supplier_id = c.id
	JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'gn_status'
	JOIN (Select aa.rn_master_id, sum(aa.qty * aa.price) AS subtotal From ic_rn_detail AS aa Group By aa.rn_master_id) AS f On a.id = f.rn_master_id
	JOIN cm_project AS e ON a.project_id = e.id";
			if ($this->userLevel < 5){
                $settings["where"] = "a.is_deleted = 0 And Locate(a.project_id,".$this->userProjectIds.")";
            }else {
                $settings["where"] = "a.is_deleted = 0 And b.entity_id = " . $this->userCompanyId;
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Repaired Receipt terlebih dahulu.");
			redirect_url("inventory.rn");
			return;
		}

		$rn = new Rrn();
		$rn = $rn->LoadById($id);
		if ($rn == null || $rn->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen Repaired Receipt yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("inventory.rn");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($rn->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen Repaired Receipt yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("inventory.rn");
				return;
			}
		}
		if ($rn->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen Repaired Receipt yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("inventory.rn");
			return;
		}

        //cek detail
        $details = $rn->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "GN No: ". $rn->DocumentNo." Harap hapus dulu Detail Itemnya");
            redirect_url("inventory.rn");
            return;
        }

		// Everything is green
		// ToDo: Kalau Referensi PO nya bukan proses GN bagaimana ?
		$this->connector->BeginTransaction();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		// Step 1: OK Hapus referensi PO jika ada...
		//	NOTE : Sama seperti PR ada kemungkinan dari 1 PO akan terbit > 1 GN (pengiriman barang berkala)
		$this->connector->CommandText =
"UPDATE ic_po_master SET
	status = 3
	, updateby_id = ?user
	, update_time = NOW()
WHERE id IN (
	-- LOGIC: cari semua PO id (self join berdasarkan po_id) yang mana tidak boleh sama dengan GN yang dihapus dan statusnnya belum di delete
	--        Jika ketemu pasangannya bearti masih ada referensinya. CARI YANG REFERENSINYA NULL
	SELECT a.po_id -- AS del_po_id, a.gn_id AS del_gn_id, a.is_deleted AS del_is_deleted, b.*
	FROM ic_link_po_gn AS a
		LEFT JOIN ic_link_po_gn AS b ON a.po_id = b.po_id AND b.gn_id <> ?gnId AND b.is_deleted = 0
	WHERE a.gn_id = ?gnId AND b.po_id IS NULL
)";
		$this->connector->AddParameter("?user", $userId);
		$this->connector->AddParameter("?gnId", $rn->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Repaired Receipt: %s ! Gagal Hapus Referensi PO<br /> Harap hubungi system administrator.<br />Error: %s", $rn->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.rn");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_po_gn SET is_deleted = 1 WHERE gn_id = ?gnId";
		$this->connector->AddParameter("?gnId", $rn->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Repaired Receipt: %s ! Gagal Hapus Link PO-GN<br /> Harap hubungi system administrator.<br />Error: %s", $rn->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.rn");
		}

		// Step 3: Hapus dokumen Repaired Receipt
		$rn->UpdatedById = $userId;
		if ($rn->Delete($rn->Id) == 1) {
			$this->connector->CommitTransaction();
			$this->persistence->SaveState("info", sprintf("Dokumen Repaired Receipt: %s sudah berhasil dihapus.", $rn->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Repaired Receipt: %s ! Harap hubungi system administrator.<br />Error: %s", $rn->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("inventory.rn");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("inventory.rn");
			return;
		}
		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/stock.php");
		//require_once(MODEL . "asset/asset.php");
        require_once(MODEL . "ap/invoice.php");
        require_once(MODEL . "ap/invoice_detail.php");
        require_once(MODEL . "common/doc_counter.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$rn = new Rrn();
			$rn = $rn->LoadById($id);

			if ($rn->StatusCode > 2) {
				$errors[] = sprintf("Dokumen Repaired Receipt: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $rn->DocumentNo, $rn->GetStatus());
				continue;
			}
			if ($rn->EntityId != $this->userCompanyId) {
                // Trying to access other Company data ! Bypass it..
                continue;
            }

			$rn->ApprovedById = $userId;

			// Pakai transaction karena kita harus ada entry barang ke gudang
			$this->connector->BeginTransaction();
			//create invoice supplier
            $invId = 0;
            $invoice = new \Ap\Invoice();
            $docCounter = new DocCounter();
            $invoice->EntityId = $rn->EntityId;
            $invoice->InvoiceDate = $rn->Date;
            $invoice->DueDate = $rn->Date;
            $invoice->CreditorId = $rn->SupplierId;
            $invoice->ProjectId = $rn->ProjectId;
            $invoice->InvoiceType = 8;
            $invoice->ReffNo = '-';
            $invoice->GrnNo = $rn->DocumentNo;
            $invoice->PaymentType = 1;
            $invoice->CreditTerms = 0;
            $invoice->InvoiceDescs = 'Jasa Repair Ex. '.$rn->DocumentNo;
            $invoice->BaseAmount = $rn->GetSubTotal();
            $invoice->InvoiceNo = $docCounter->AutoDocNo($invoice->EntityId,$invoice->InvoiceType, $invoice->InvoiceDate, 1);
            $invoice->CreatebyId = $this->userUid;
            $rx = $invoice->Insert();
            if ($rx == 1){
                $invId = $invoice->Id;
            }else{
                $errors[] =  sprintf("Gagal membuat Invoice Supplier: %s -> %s. Message: %s", $rn->DocumentNo, $invoice->InvoiceNo, $this->connector->GetErrorMessage());
                $this->connector->RollbackTransaction();
                continue;
            }
            $rn->InvoiceNo = $invoice->InvoiceNo;
            $rs = $rn->Approve($rn->Id);
            if ($rs != 1) {
                $errors[] =  sprintf("Gagal Approve Dokumen Repaired Receipt: %s. Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
                $this->connector->RollbackTransaction();
                continue;
            }

			$rn->LoadDetails(false);
			//$detail = new RrnDetail();
			$items = array();
			$flagSuccess = true;

			$counterAsset = 0;
			$counterStock = 0;
			$counterItem = 0;
			foreach ($rn->Details as $detail) {
			    $counterItem++;
				if (isset($items[$detail->ItemId])) {
					$item = $items[$detail->ItemId];
				} else {
					$item = new Item();
					$item = $item->LoadById($detail->ItemId, true);

					$items[$detail->ItemId] = $item;
				}
                if ($item->AssetCategoryId > 0) {
					$price = $detail->Price / $detail->Qty;

					for ($i = 0; $i < $detail->Qty; $i++) {
						$counterAsset++;
						$asset = new Asset();

						$asset->UpdatedUserId = $userId;
						$asset->EntityId = $rn->EntityId;
						//$asset->CategoryId = $item->AssetCategoryId;
						$asset->ItemId = $item->Id;
						$asset->Code = sprintf('%s - %03d', $rn->DocumentNo, $counterAsset);	// Dummy code
						$asset->Description = "[AUTO FROM GN: " . $rn->DocumentNo .  "] " . $detail->ItemDescription;
						$asset->PurchaseDate = $rn->Date;
						$asset->Price = $price;
						$asset->GnDetailId = $detail->Id;

						$rs = $asset->Insert();
						if ($rs != 1) {
							$errors[] = sprintf("Gagal entry asset GN: %s -> Barang: %s. Message: %s", $rn->DocumentNo, $item->ItemName, $this->connector->GetErrorMessage());
							$flagSuccess = false;
							break;		// Ini cuma break looping qty
						}
					}

					// Keluar dari loop di cek kembali
					if (!$flagSuccess) {
						break;		// Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
					}
				} else {
					// Karena ini barang direpair jadi item codenya berubah
                    // cek dulu apakah item code yg sama sudah ada?
                    $citems = new Item();
                    $citems = $citems->LoadById($detail->ItemId);
                    $ckode  = null;
                    $citid  = 0;
                    if ($citems == null){
                        $errors[] = sprintf("Gagal entry stock RN: %s -> Barang: %s. Message: %s", $rn->DocumentNo, $item->ItemName, $this->connector->GetErrorMessage());
                        $flagSuccess = false;
                        break;
                    }else{
                        $ckode = $citems->ItemCode.'R';
                        $xitems = new Item();
                        $xitems = $xitems->FindByCode($this->userCompanyId,$ckode);
                        if ($xitems == null){
                            $xitems = new Item();
                            $xitems->EntityId = $this->userCompanyId;
                            $xitems->ItemCode = $ckode;
                            $xitems->ItemName = $citems->ItemName;
                            $xitems->CategoryId = $citems->CategoryId;
                            $xitems->UomCode = $citems->UomCode;
                            $xitems->PartNo = $citems->PartNo;
                            $xitems->Barcode = $citems->Barcode;
                            $xitems->SnNo = $citems->SnNo;
                            $xitems->MaxQty = $citems->MaxQty;
                            $xitems->MinQty = $citems->MinQty;
                            $xitems->IsDiscontinued = $citems->IsDiscontinued;
                            $xitems->Note = '* Repaired *'.$citems->Note;
                            $xitems->AssetCategoryId = $citems->AssetCategoryId;
                            $xitems->UnitTypeCode = $citems->UnitTypeCode;
                            $xitems->UnitBrandCode = $citems->UnitBrandCode;
                            $xitems->UnitCompCode = $citems->UnitCompCode;
                            $xitems->IcxCode = $citems->ItemCode;
                            $xitems->Qclass = 3;
                            $xitems->LUomCode = $citems->LUomCode;
                            $xitems->UomConversion = $citems->UomConversion;
                            $xitems->UpdatedUserId = $this->userUid;
                            if ($xitems->Insert() == 1){
                                $citid = $xitems->Id;
                            }else{
                                $errors[] = sprintf("Gagal entry stock RN: %s -> Barang: %s. Message: %s", $rn->DocumentNo, $item->ItemName, $this->connector->GetErrorMessage());
                                $flagSuccess = false;
                                break;
                            }
                        }else{
                            $citid = $xitems->Id;
                        }
                    }
					$stock = new Stock();
					$stock->CreatedById = $userId;
					$stock->StockTypeCode = 2;		// Barang dari GN
					$stock->ReferenceId = $detail->Id;
					$stock->Date = $rn->Date;
					$stock->WarehouseId = $rn->WarehouseId;
                    $stock->ProjectId = $rn->ProjectId;
					$stock->ItemId = $citid;
					$stock->Qty = $detail->Qty;
					$stock->UomCd = $detail->UomCd;
					$stock->Price = $detail->Price;
					$stock->UseStockId = null;
					$stock->QtyBalance = $stock->Qty;	// Stock IN maka by default balance = qty

					$rs = $stock->Insert();
					if ($rs != 1) {
						$errors[] = sprintf("Gagal entry stock RN: %s -> Barang: %s. Message: %s", $rn->DocumentNo, $item->ItemName, $this->connector->GetErrorMessage());
						$flagSuccess = false;
						break;		// Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
					}

					//create detail invoics supplier
                    $ivdetail = new \Ap\InvoiceDetail();
                    $ivdetail->InvoiceId = $invId;
                    $ivdetail->ItemId = $detail->ItemId;
                    $ivdetail->ItemCode = $item->ItemCode;
                    $ivdetail->ItemName = $item->ItemName;
                    $ivdetail->ItemDescs = 'Repair - '.$item->ItemName.' ('.$item->PartNo.')';
                    $ivdetail->Qty = $detail->Qty;
                    $ivdetail->Price = $detail->Price;
                    $ivdetail->UomCd = $detail->UomCd;
                    $ivdetail->IsAuto = 1;
                    $rz = $ivdetail->Insert();
                    if ($rz != 1) {
                        $errors[] = sprintf("Gagal insert item IS: %s -> Barang: %s. Message: %s", $invoice->InvoiceNo, $item->ItemName, $this->connector->GetErrorMessage());
                        $flagSuccess = false;
                        break;		// Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
                    }
                    $counterStock++;
				}
			}
			// Kita menggunakan transaction per level GN bukan semua approval
			if ($flagSuccess) {
				$infos[] = sprintf("[$counterStock of $counterItem] Dokumen Repaired Receipt: %s sudah berhasil di approve", $rn->DocumentNo);
				$this->connector->CommitTransaction();
			} else {
				$this->connector->RollbackTransaction();
			}
		}	// END LOOP foreach ($ids => $id)

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("inventory.rn");
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Repaired Receipt yang akan di batalkan !");
			redirect_url("inventory.rn");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$rn = new Rrn();
			$rn = $rn->LoadById($id);

			if ($rn->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Repaired Receipt: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $rn->DocumentNo, $rn->GetStatus());
				continue;
			}

			$rn->UpdatedById = $userId;

			// Pakai transaction karena kita harus membatalkan barang2 yang sudah di entry ke gudang dll nya
			$this->connector->BeginTransaction();
			$this->connector->CommandText =
"SELECT COUNT(a.id)
FROM ic_stock AS a
WHERE a.is_deleted = 0 AND a.use_stock_id IN (
	-- Ambil ID stock yang direfer oleh GN (stock_type = 2 WAJIB !)
	SELECT aa.id
	FROM ic_stock AS aa
	WHERE aa.is_deleted = 0 AND aa.stock_type = 2 AND aa.reference_id IN (
		-- Ambil id detail GN karena ini digunakan di stock
		SELECT id FROM ic_rn_detail WHERE rn_master_id = ?id
	)
)";
			$this->connector->AddParameter("?id", $id);
			$rs = $this->connector->ExecuteScalar();
			if ($rs > 0) {
				// Hwee... masih dipake ga boleh dihapus GN nya...
				$errors[] = sprintf("Gagal Disapprove GN: %s ! Stock barang sudah pernah di issue. Lakukan tracking barang !", $rn->DocumentNo);
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK Remove stock terlebih dahulu dari table stock (INGAT TYPE STOCK HARUS = 1)
			$this->connector->CommandText =
"UPDATE ic_stock SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE is_deleted = 0 AND stock_type = 2 AND reference_id IN (
	SELECT id FROM ic_rn_detail WHERE rn_master_id = ?id
)";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove GN: %s ! Gagal remove stock ! Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}
			// ToDo: Asset yang sudah kena depresiasi harus dibatalkan ! (Menunggu table transaksi depresiasi barang)
			// Batalkan Asset
			$this->connector->CommandText =
"UPDATE ac_asset_master SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE is_deleted = 0 AND rn_detail_id IN (
	SELECT id FROM ic_rn_detail WHERE rn_master_id = ?id
)";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapproce GN: %s ! Gagal remove stock ! Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}
			// Batalkan GN terakhir ketika semua proses lainya sukses... (Jika gagal pada proses sebelumnya auto rollback dan continue)
			$rs = $rn->DisApprove($id);
			if ($rs != -1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Repaired Receipt: %s sudah berhasil di dibatalkan (disapprove)", $rn->DocumentNo);
			} else {
				$errors[] =  sprintf("Gagal Membatalkan / Disapprove Dokumen Repaired Receipt: %s. Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("inventory.rn");
	}

    //buat halaman search data
    public function overview() {
        require_once(MODEL. "master/creditor.php");
        require_once(MODEL. "master/project.php");
        require_once(MODEL. "status_code.php");

        $rn = new Rrn();

        if (count($this->getData) > 0) {
            $projectId = $this->GetGetValue("project");
            $supplierId = $this->GetGetValue("supplier");
            $status = $this->GetGetValue("status");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.entity_cd AS entity, c.creditor_name AS supplier, d.short_desc AS status_name, e.project_name AS warehouse
                                            FROM ic_rn_master AS a
                                            JOIN cm_company AS b ON a.entity_id = b.entity_id
                                            JOIN ap_creditor_master AS c ON a.supplier_id  = c.id
                                            JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'gn_status'
                                            JOIN cm_project AS e ON a.project_id = e.id
                                            WHERE a.is_deleted = 0";

            $this->connector->CommandText .= " AND b.entity_id = ?entity";
            $this->connector->AddParameter("?entity", $this->userCompanyId);

            if ($projectId != -1) {
                $this->connector->CommandText .= " AND a.project_id = ?project";
                $this->connector->AddParameter("?project", $projectId);
            }

            if ($supplierId != -1) {
                $this->connector->CommandText .= " AND a.supplier_id = ?supplier";
                $this->connector->AddParameter("?supplier", $supplierId);
            }

            if ($status != -1) {
                $this->connector->CommandText .= " AND a.status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.gn_date >= ?start
                                               AND a.gn_date <= ?end
                                               ORDER BY a.gn_date ASC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $projectId = null;
            $supplierId = null;
            $status = null;
            $startDate = time();
            $endDate = time();
            $output = "web";
            $report = null;
        }


        $creditor = new Creditor();
        $creditorAll = $creditor->LoadByEntity($this->userCompanyId);
        $this->Set("creditorAll", $creditorAll);

        $supplier = $creditor->FindById($supplierId);
        $supplier = $supplier != null ? $supplier->CreditorName : "SEMUA SUPPLIER";
        $this->Set("supplierName", $supplier);

        $syscode = new StatusCode();
        $this->Set("gn_status", $syscode->LoadGnStatus());

        $temp = $syscode->FindBy("gn_status", $status);
        $statusName = $temp != null ? $temp->ShortDesc : "SEMUA STATUS";
        $this->Set("statusName", $statusName);

        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
        $this->Set("projectId", $projectId);
        $this->Set("userLevel", $this->userLevel);
    }

    //proses cetak form GN
    public function doc_print($output){
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
            redirect_url("inventory.rn");
            return;
        }

        $report = array();
        foreach ($ids as $id) {
            $rn = new Rrn();
            $rn = $rn->LoadById($id);
            $rn->LoadDetails();
            $rn->LoadUsers();

            $report[] = $rn;
        }

        $this->Set("report", $report);
        $this->Set("output", $output);
    }

    //pencarian jumlah barang by tgl.dokumen (tracking & monitoring)
    public function item_recap(){
        require_once(MODEL. "master/project.php");

        if (count($this->getData) > 0) {
            $projectId = $this->GetGetValue("projectId");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.item_id, a.uom_cd, c.item_code, c.part_no, c.item_name, SUM(a.qty) AS jumlah, SUM(a.qty * a.price) AS total
                                            FROM ic_rn_detail AS a
                                            JOIN ic_rn_master AS b ON a.rn_master_id = b.id
                                            JOIN ic_item_master AS c ON a.item_id = c.id
                                            WHERE b.is_deleted = 0 And b.status > 1 
                                            AND b.project_id = ?project
                                            AND b.gn_date >= ?start
                                            AND b.gn_date <= ?end
                                            GROUP BY a.item_id, a.uom_cd, c.item_code, c.item_name
                                            ORDER BY c.item_code";

            $this->connector->AddParameter("?project", $projectId);
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $projectId = null;
            $startDate = time();
            $endDate = time();
            $output = "web";
            $report = null;
        }
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
        $this->Set("projectId", $projectId);
        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("output", $output);
    }

	public function batch_posting() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di posting !");
			redirect_url("inventory.rn");
			return;
		}

		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/item_category.php");
		require_once(MODEL . "asset/asset_category.php");
		require_once(MODEL . "common/creditor_type.php");
		require_once(MODEL . "accounting/voucher.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$rn = new Rrn();
			$rn = $rn->LoadById($id);

			if ($rn == null || $rn->IsDeleted) {
				continue;
			}
			if ($rn->EntityId != $this->userCompanyId) {
                // Trying to access other Company data ! Bypass it..
                continue;
            }

			if ($rn->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Repaired Receipt: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $rn->DocumentNo, $rn->GetStatus());
				continue;
			}

			$rn->PostedById = $userId;

			// OK ini hueboh klo ga pake transaction :p
			$this->connector->BeginTransaction();

			// Step #01: Variable Voucher
			$voucher = new Voucher();
			$voucher->DocumentTypeId = $rn->PaymentModeCode == 1 ? 2 : 5;
			$voucher->DocumentNo = $rn->DocumentNo;
			$voucher->Date = $rn->Date;
			$voucher->EntityId = $rn->EntityId;
			$voucher->Note = "[AUTO] " . $rn->DocumentNo;
			$voucher->StatusCode = 4;	// lsg status posted krn dari GN
			$voucher->CreatedById = $userId;
			$voucher->VoucherSource = "GN";

			// Step #02: Posting GN
			$rs = $rn->Post($rn->Id);
			if ($rs != 1) {
				$errors[] =  sprintf("Gagal Step 02 Posting Dokumen Repaired Receipt: %s. Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// Step #03: Buat Detail Voucher
			$rn->LoadDetails(false);
			$sqn = 1;
			foreach ($rn->Details as $detail) {
				// Ambil data barang
				$item = new Item();
				$item = $item->LoadById($detail->ItemId, true);

				if ($item->AssetCategoryId > 0) {
					// Asset... ambil data dari asset category
					$assetCategory = new AssetCategory();
					$assetCategory = $assetCategory->LoadById($item->AssetCategoryId);

					$accDebit = $assetCategory->AssetAccountId;
				} else {
					// Barang biasa
					$itemCategory = new ItemCategory();
					$itemCategory = $itemCategory->LoadById($item->CategoryId);

					$accDebit = $itemCategory->InventoryAccountId;
				}

				if ($rn->PaymentModeCode == 1) {
					// CASH payment
					$accCredit = $rn->PaymentAccountId;
				} else {
					$creditorType = new CreditorType();
					$creditorType = $creditorType->LoadByCreditorId($rn->SupplierId);

					$accCredit = $creditorType->AccControlId;
				}

				// Detail Voucher
				$voucherDetail = new VoucherDetail();
				$voucherDetail->Sequence = $sqn;
				$voucherDetail->AccDebitId = $accDebit;
				$voucherDetail->AccCreditId = $accCredit;
				$voucherDetail->Amount = $detail->Price * $detail->Qty;
				$voucherDetail->CreditorId = $rn->SupplierId;
				$voucherDetail->Note = sprintf("%s %s - %s", $detail->Qty, $item->UomCode, $item->ItemName);
				$voucherDetail->ProjectId = $rn->ProjectId;

				$voucher->Details[] = $voucherDetail;
				$sqn++;
			}

			// Step #04: Simpan Voucher
			$rs = $voucher->Insert();
			if ($rs != 1) {
				$errors[] =  sprintf("Gagal Step 04 Posting Dokumen Repaired Receipt: %s. Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// Step #05: Simpan Detail Voucher
			$flagSuccess = true;
			foreach ($voucher->Details as $voucherDetail) {
				$voucherDetail->VoucherId = $voucher->Id;

				$rs = $voucherDetail->Insert();
				if ($rs != 1) {
					$errors[] =  sprintf("Gagal Step 05 Posting Dokumen Repaired Receipt: %s. Message: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
					$this->connector->RollbackTransaction();
					$flagSuccess = false;
					break;
				}
			}

			// Step #06: Commit (tidak perlu RollBack secara explicit disini karena sudah di rollback pada saat gagal)
			if ($flagSuccess) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Repaired Receipt: %s sudah berhasil diposting.", $rn->DocumentNo);
			}
		}

		// OK semua process posting complete... tendang ke halaman voucher jika boleh
		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}

		redirect_url("inventory.rn");
	}

	public function batch_unposting() {
		$ids = $this->GetGetValue("id", array());
		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih OR yang akan di-unposting");
			redirect_url("ar.receipt");
		}

		require_once(MODEL . "accounting/voucher.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$rn = new Rrn();
			$rn = $rn->LoadById($id);

			if ($rn == null || $rn->IsDeleted) {
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($rn->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}
			if ($rn->StatusCode != 5) {
				$errors[] = sprintf("Dokumen Repaired Receipt: %s tidak diproses karena status sudah bukan POSTED ! Status Dokumen: %s", $rn->DocumentNo, $rn->GetStatus());
				continue;
			}

			// Open transaction
			$this->connector->BeginTransaction();

			// Step #01: Delete Voucher terlebih dahulu
			$voucher = new Voucher();
			$rs = $voucher->DeleteByDocNo($rn->DocumentNo);
			if ($rs == -1) {
				$errors[] = sprintf("Gagal unposting GN (gagal hapus voucher): %s. Error: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			} else if ($rs == 0) {
				// Ini aneh... status posted tapi ga ketemu vouchernya pas saat hapus...
				$infos[] = sprintf("NOTICE: Dokumen RN: %s tidak memiliki Voucher tetapi status POSTED.", $rn->DocumentNo);
			}

			// Step #02: Batalkan status posted GN
			$rn->UpdatedById = $userId;
			$rs = $rn->UnPost($rn->Id);
			if ($rs != 1) {
				$errors[] = sprintf("Gagal unposting GN (gagal set flag): %s. Error: %s", $rn->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// Step #03: Commit
			$this->connector->CommitTransaction();
			$infos[] = sprintf("Dokumen Repaired Receipt: %s sudah berhasil un-posting.", $rn->DocumentNo);
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("inventory.rn");
	}

    public function add($rnId = 0) {
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $rn = new Rrn();
        if ($rnId > 0 ) {
            $rn = $rn->LoadById($rnId);
            if ($rn == null) {
                $this->persistence->SaveState("error", "Maaf Data GN dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.rn");
            }
            if ($rn->StatusCode > 2) {
                $this->persistence->SaveState("error", sprintf("Maaf GN No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $rn->DocumentNo,$rn->GetStatus()));
                redirect_url("inventory.rn");
            }
        }else{
            $rn->Date = date('d-m-Y');
        }

        // load details
        $rn->LoadDetails();
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load supplier
        $supplier = new Creditor();
        $suppliers = $supplier->LoadByEntity($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("suppliers", $suppliers);
        $this->Set("projects", $projects);
        $this->Set("rn", $rn);
    }

    public function proses_master($rnId = 0) {
        $rn = new Rrn();
        if (count($this->postData) > 0) {
            $rn->Id = $rnId;
            $rn->EntityId = $this->userCompanyId;
            $rn->ProjectId = $this->GetPostValue("ProjectId");
            $rn->Date = strtotime($this->GetPostValue("GnDate"));
            $rn->SupplierId = $this->GetPostValue("SupplierId");
            $rn->Note = $this->GetPostValue("Note");
            $rn->DocumentNo = $this->GetPostValue("GnNo");
            $rn->IsVat = 0;
            $rn->IsIncludeVat = 0;
            $rn->CreditTerms = 0;
            if ($rnId > 0) {
                $rn->StatusCode = 2;
            }else{
                $rn->StatusCode = 1;
            }
            $rn->PaymentModeCode = 0;
            $rn->PaymentAccountId = 0;
            $rn->CreatedById = $this->userUid;
            if ($rn->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $rn->DocumentNo = $docCounter->AutoDocNoRn($rn->EntityId, $rn->Date, 1);
                $rs = $rn->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$rn->Id,$rn->DocumentNo);
                }else{
                    printf("ER|A|%d",$rn->Id);
                }
            }else{
                $rs = $rn->Update($rn->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$rn->Id,$rn->DocumentNo);
                }else{
                    printf("ER|U|%d",$rn->Id);
                }
            }
        }else{
            printf("ER|X|%d",$rnId);
        }
    }

    public function add_detail($rnId = null) {
        $rst = null;
        $rn = new Rrn($rnId);
        $rndetail = new RrnDetail();
        $rndetail->RnId = $rnId;
        $rn_item_exist = false;
        if (count($this->postData) > 0) {
            $rndetail->ItemId = $this->GetPostValue("aItemId");
            $rndetail->Qty = $this->GetPostValue("aRnQty");
            $rndetail->ItemDescription = '-';
            $rndetail->RoDetailId = $this->GetPostValue("aRoDetailId");
            $rndetail->UomCd = $this->GetPostValue("aUomCd");
            $rndetail->Price = $this->GetPostValue("aPrice");
            // item baru simpan
            $rs = $rndetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$rndetail->Id);
                //creat mr link
                if ($rndetail->RoDetailId > 0) {
                    //update mr qty
                    $this->connector->CommandText = "Update ic_ro_detail AS a Set a.rec_qty = a.rec_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $rndetail->Qty);
                    $this->connector->AddParameter("?id", $rndetail->RoDetailId);
                    $rs = $this->connector->ExecuteNonQuery();
                }
            } else {
                $rst = 'ER|Gagal proses simpan data!';
            }
        }else{
            $rst = "ER|No Data posted!";
        }
        print($rst);
    }

    public function delete_detail($id) {
        // Cek datanya
        $rndetail = new RrnDetail();
        $rndetail = $rndetail->LoadById($id);
        if ($rndetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $pri = $rndetail->RoDetailId;
        $rni = $rndetail->GnId;
        $qty = $rndetail->Qty;
        if ($rndetail->Delete($id) == 1) {
            if ($pri > 0) {
                //update mr qty
                $this->connector->CommandText = "Update ic_ro_detail AS a Set a.rec_qty = a.rec_qty - ?qty Where a.id = ?id";
                $this->connector->AddParameter("?qty", $qty);
                $this->connector->AddParameter("?id", $pri);
                $rs = $this->connector->ExecuteNonQuery();
            }
            printf("Data Detail RO ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail RO ID: %d gagal dihapus!",$id);
        }
    }

    public function getjson_roitems($projectId = 0, $supplierId = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $poitems = new Rrn();
        $poitems = $poitems->GetJSonUnfinishedRoItems($projectId,$supplierId,$filter);
        echo json_encode($poitems);
    }

    public function view($rnId = 0) {
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $rn = new Rrn();
        if ($rnId > 0 ) {
            $rn = $rn->LoadById($rnId);
            if ($rn == null) {
                $this->persistence->SaveState("error", "Maaf Data GN dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.rn");
            }
        }else{
            $rn->Date = date('d-m-Y');
        }

        // load details
        $rn->LoadDetails();
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load supplier
        $supplier = new Creditor();
        $suppliers = $supplier->LoadByEntity($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("suppliers", $suppliers);
        $this->Set("projects", $projects);
        $this->Set("rn", $rn);
    }
}


// End of File: gn_controller.php
