<?php

class IsController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "inventory/item_issue.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		//$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 80);
		$settings["columns"][] = array("name" => "c.project_name", "display" => "Project", "width" => 100);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "Issue No", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.issue_date, '%d %M %Y')", "display" => "Issue Date", "width" => 80);
		$settings["columns"][] = array("name" => "d.short_desc", "display" => "Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d %M %Y')", "display" => "Last Update", "width" => 80);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "Issue No");
		$settings["filters"][] = array("name" => "c.project_cd", "display" => "Project");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Inventory Issue";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("inventory.is", "add_master")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.is/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.is", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "inventory.is/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
			}
            if ($acl->CheckUserAccess("inventory.is", "doc_print")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.is/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
            }
            if ($acl->CheckUserAccess("inventory.is", "doc_print")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.is/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.is", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.is/add/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen Item Issue terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.is", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.is/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen Item Issue terlebih dahulu sebelum melakukan proses delete !\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
											   "Confirm" => "Apakah anda mau menghapus Dokumen Item Issue yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.is", "batch_approve")) {
				$settings["actions"][] = array("Text" => "Approve Item Issue", "Url" => "inventory.is/batch_approve/", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Item Issue !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua dokumen Item Issue yang dipilih ?\nKetika Item Issue berhasil di approve maka akan mengurangi stock gudang");
			}
			if ($acl->CheckUserAccess("inventory.is", "batch_disapprove")) {
				$settings["actions"][] = array("Text" => "Dis-Approve Item Issue", "Url" => "inventory.is/batch_disapprove/", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Item Issue !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen Item Issue yang dipilih ?\nProses Dis-Approve Akan membuat status dokumen menjadi DRAFT dan membatalkan transaksi barang / item yang bersangkutan.");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.is", "posting")) {
				$settings["actions"][] = array("Text" => "Posting Item Issue", "Url" => "inventory.is/posting", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Item Issue !",
					"Confirm" => "Apakah anda mau meng-posting semua semua dokumen Item Issue yang dipilih ?\nKetika Item Issue berhasil diposting maka akan otomatis dibuatkan voucher.");
			}
			if ($acl->CheckUserAccess("inventory.is", "unposting")) {
				$settings["actions"][] = array("Text" => "Unposting Item Issue", "Url" => "inventory.is/unposting", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Item Issue !",
					"Confirm" => "Apakah anda mau mem-UNPOSTING-kan semua semua dokumen Item Issue yang dipilih ?\nProses Un-Posting Akan membuat status dokumen menjadi APPROVED dan menghapus voucher yang bersangkutan.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Kill Session
			$this->persistence->DestroyState("inventory.is.is");
		} else {
			$settings["from"] = "ic_is_master AS a JOIN cm_project AS c ON a.project_id = c.id JOIN cm_company AS b ON c.entity_id = b.entity_id JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'is_status'";
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
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Item Issue terlebih dahulu.");
			redirect_url("inventory.is");
			return;
		}

		$is = new ItemIssue();
		$is = $is->LoadById($id);
		if ($is == null || $is->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen Item Issue yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("inventory.is");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($is->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen Item Issue yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("inventory.is");
				return;
			}
		}
		if ($is->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen Item Issue yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("inventory.is");
			return;
		}

		$details = $is->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "Issue No: ". $is->DocumentNo." Harap hapus dulu Detail Item Issuenya");
            redirect_url("inventory.is");
            return;
        }
		// Everything is green
		// ToDo: Kalau Referensi Issue nya bukan Delivery bagaimana ?
		$this->connector->BeginTransaction();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		// Step 1: OK Hapus referensi Issue jika ada...
		//	NOTE : Sama seperti IS ada kemungkinan dari 1 Issue akan terbit > 1 IS (Jika barang belum diberikan semua kepada departemen yang terkait)
		$this->connector->CommandText = "UPDATE ic_mr_master SET status = 3, updateby_id = ?user, update_time = NOW() WHERE id IN (
	-- LOGIC: cari semua Issue id (self join berdasarkan mr_id) yang mana tidak boleh sama dengan IS yang dihapus dan statusnnya belum di delete
	--        Jika ketemu pasangannya bearti masih ada referensinya. CARI YANG REFERENSINYA NULL
	SELECT a.mr_id -- AS del_mr_id, a.is_id AS del_is_id, a.is_deleted AS del_is_deleted, b.*
	FROM ic_link_mr_is AS a
		LEFT JOIN ic_link_mr_is AS b ON a.mr_id = b.mr_id AND b.is_id <> ?isId AND b.is_deleted = 0
	WHERE a.is_id = ?isId AND b.mr_id IS NULL
)";
		$this->connector->AddParameter("?user", $userId);
		$this->connector->AddParameter("?isId", $is->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Item Issue: %s ! Gagal Hapus Referensi PO<br /> Harap hubungi system administrator.<br />Error: %s", $is->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.is");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_mr_is SET is_deleted = 1 WHERE is_id = ?isId";
		$this->connector->AddParameter("?isId", $is->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Item Issue: %s ! Gagal Hapus Link Issue-IS<br /> Harap hubungi system administrator.<br />Error: %s", $is->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.is");
		}

		// Step 3: Hapus dokumen Item Issue
		$is->UpdatedById = $userId;
		if ($is->Delete($is->Id) == 1) {
			$this->connector->CommitTransaction();
			$this->persistence->SaveState("info", sprintf("Dokumen Item Issue: %s sudah berhasil dihapus.", $is->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Item Issue: %s ! Harap hubungi system administrator.<br />Error: %s", $is->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("inventory.is");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("inventory.is");
			return;
		}
		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/stock.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$itemIssue = new ItemIssue();
			$itemIssue = $itemIssue->LoadById($id);

			if ($itemIssue->StatusCode != 1) {
				$errors[] = sprintf("Dokumen Item Issue: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $itemIssue->DocumentNo, $itemIssue->GetStatus());
				continue;
			}
			if ($itemIssue->EntityId != $this->userCompanyId) {
                // Trying to access other Company data ! Bypass it..
                continue;
            }

			$itemIssue->ApprovedById = $userId;

			// Pakai transaction karena kita harus ada entry barang ke gudang
			$this->connector->BeginTransaction();
			$rs = $itemIssue->Approve($itemIssue->Id);
			if ($rs != 1) {
				$errors[] =  sprintf("Gagal Approve Dokumen Item Issue: %s. Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			$itemIssue->LoadDetails(false);
			$items = array();
			$flagSuccess = true;

//			$detail = new ItemIssueDetail();
			foreach ($itemIssue->Details as $detail) {
				if (isset($items[$detail->ItemId])) {
					$item = $items[$detail->ItemId];
				} else {
					$item = new Item();
					$item = $item->LoadById($detail->ItemId, true);

					$items[] = $item;
				}

				if ($item->AssetCategoryId == null || $item->AssetCategoryId == 0) {
					// OK barang yang bisa di issue
					$stock = new Stock();
					$stocks = $stock->LoadStocksFifo($detail->ItemId, $detail->UomCd,$itemIssue->ProjectId);

					// Set variable-variable pendukung
					$remainingQty = $detail->Qty;
					$detail->TotalCost = 0;

					/** @var $stock Stock */
					foreach ($stocks as $stock) {
						// Buat object buat issue nya
						$issue = new Stock();
						$issue->CreatedById = $userId;
						$issue->StockTypeCode = 101;				// Item Issue dari IS
						$issue->ReferenceId = $detail->Id;
						$issue->Date = $itemIssue->Date;
						$issue->WarehouseId = $stock->WarehouseId;	// Barang yang dikeluarkan pasti dari gudang yang sama dengan stock barang !
                        $issue->ProjectId = $stock->ProjectId;
						$issue->ItemId = $detail->ItemId;
						//$issue->Qty = $stock->QtyBalance;			// Depend on case...
						$issue->UomCd = $detail->UomCd;
						$issue->Price = $stock->Price;				// Ya pastilah pake angka ini...
						$issue->UseStockId = $stock->Id;			// Kasi tau kalau issue ini based on stock id mana
						$issue->QtyBalance = null;					// Klo issue harus NULL

						$stock->UpdatedById = $userId;

						if ($remainingQty > $stock->QtyBalance) {
							// Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...

							$issue->Qty = $stock->QtyBalance;			// Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya

							$remainingQty -= $stock->QtyBalance;		// Kita masih perlu...
							$stock->QtyBalance = 0;						// Habis...
						} else {
							// Barang di gudang mencukupi atau PAS
							$issue->Qty = $remainingQty;

							$stock->QtyBalance -= $remainingQty;
							$remainingQty = 0;
						}

						// Apapun yang terjadi masukkan data issue stock
						if ($issue->Insert() != 1) {
							$errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $itemIssue->DocumentNo, $item->ItemCode, $item->ItemName);
							$flagSuccess = false;
							break;		// Break loop stocks
						}
						// Update Qty Balance
						if ($stock->Update($stock->Id) != 1) {
							$errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $itemIssue->DocumentNo, $item->ItemCode, $item->ItemName, $this->connector->GetErrorMessage());
							$flagSuccess = false;
							break;		// Break loop stocks
						}
						// OK jangan lupa update data cost
						$detail->TotalCost += $issue->Qty * $issue->Price;
						if ($remainingQty <= 0) {
							// Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
							break;
						}
					}	// End Loop: foreach ($stocks as $stock) {

					if (!$flagSuccess) {
						// Ada error ketika proses stock !
						break;
					}

					// Nah sekarang saatnya checking barang cukup atau tidak
					if ($remainingQty > 0) {
						// WTF... barang tidak cukup !!!
                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $itemIssue->DocumentNo, $item->ItemCode, $item->ItemName);
						$flagSuccess = false;
						break;		// Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
					}
					// Update total cost
					if ($detail->UpdateCost($detail->Id) != 1) {
						// WTF... barang tidak cukup !!!
                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $itemIssue->DocumentNo, $item->ItemCode, $item->ItemName, $this->connector->GetErrorMessage());
						$flagSuccess = false;
						break;		// Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
					}
				} else {
					// Hwee kok ada asset yang dikeluarkan lewat GN ??
					$errors[] = sprintf("Gagal issue stock IS: %s -> Barang: %s. Message: Barang Berupa Asset !", $itemIssue->DocumentNo, $item->ItemName);
					$flagSuccess = false;
					break;		// Buat apa lanjut klo 1 aja uda gagal... NEXT !!!
				}
			}	// End Loop: foreach ($itemIssue->Details as $detail) {

			// Kita menggunakan transaction per level GN bukan semua approval
			if ($flagSuccess) {
				$infos[] = sprintf("Dokumen Item Issue: %s sudah berhasil di approve", $itemIssue->DocumentNo);
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
		redirect_url("inventory.is");
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());
		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Item Issue yang akan di batalkan !");
			redirect_url("inventory.is");
			return;
		}
		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$itemIssue = new ItemIssue();
			$itemIssue = $itemIssue->LoadById($id);

			if ($itemIssue->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Item Issue: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $itemIssue->DocumentNo, $itemIssue->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($itemIssue->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$itemIssue->UpdatedById = $userId;

			// Pakai transaction karena kita harus membatalkan barang2 yang sudah di issue dari gudang !!
			$this->connector->BeginTransaction();

			// Update qty_balance terlebih dahulu
			$this->connector->CommandText =
"UPDATE ic_stock AS a JOIN ic_stock AS b ON a.id = b.use_stock_id AND b.is_deleted = 0 SET
	a.qty_balance = a.qty_balance + b.qty
	, a.updateby_id = ?user
	, a.update_time = NOW()
WHERE b.stock_type = 101 AND b.reference_id IN (
	SELECT id FROM ic_is_detail WHERE is_master_id = ?id
);";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove Item Issue: %s ! Gagal menarik stock barang yang dikeluarkan ! Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}
			$temp = $rs;	// Simpan data jumlah data yang berhasil diupdate (harus sama dengan query berikutnya)

			// OK remove semua barang yang sudah di issue
			$this->connector->CommandText =
"UPDATE ic_stock SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE is_deleted = 0 AND stock_type = 101 AND reference_id IN (
	SELECT id FROM ic_is_detail WHERE is_master_id = ?id
)";
			$this->connector->AddParameter("?id", $id);
			$this->connector->AddParameter("?user", $userId);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// Error occurred
				$errors[] = sprintf("Gagal Disapprove Item Issue: %s ! Gagal menghapus barang yang sudah di issue ! Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}
			if ($rs != $temp) {
				// Aneh... kok bisa-bisanya jumlah barang yang ditarik tidak sama dengan yang dihapus ???
				$errors[] = sprintf("Gagal Disapprove Item Issue: %s ! Jumlah %s yang ditarik != jumlah dihapus ! Message: hubungi system admin dan beritahukan no IS yang gagal dihapus", $itemIssue->DocumentNo);
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK Hapus total cost
			$this->connector->CommandText = "UPDATE ic_is_detail SET total_cost = 0 WHERE is_master_id = ?id";
			$this->connector->AddParameter("?id", $id);
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				// DAFUQ !!!! step terakhir gagal ???
				$errors[] = sprintf("Gagal Disapprove Item Issue: %s ! Gagal update total_cost ! Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// OK everything is green
			$rs = $itemIssue->DisApprove($id);
			if ($rs != -1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Item Issue: %s sudah berhasil di dibatalkan (disapprove)", $itemIssue->DocumentNo);
			} else {
				$errors[] =  sprintf("Gagal Membatalkan / Disapprove Dokumen Item Issue: %s. Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("inventory.is");
	}

    //buat halaman search data
    public function overview() {
        require_once(MODEL. "master/project.php");
        require_once(MODEL. "master/department.php");
        require_once(MODEL. "status_code.php");

        if (count($this->getData) > 0) {
            $projectId = $this->GetGetValue("project");
            $deptId = $this->GetGetValue("dept");
            $status = $this->GetGetValue("status");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.dept_code AS dept, c.entity_cd AS entity, d.short_desc AS status_name, e.project_cd, e.project_name, f.act_code,f.act_name,g.unit_code,g.unit_name,h.costs
                                            FROM ic_is_master AS a
                                            LEFT JOIN cm_dept AS b ON a.dept_id = b.id
                                            JOIN cm_company AS c ON b.entity_id = c.entity_id
                                            JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'is_status'
                                            JOIN cm_project AS e ON a.project_id = e.id
                                            LEFT JOIN cm_activity AS f ON a.activity_id = f.id
                                            LEFT JOIN cm_units AS g ON a.unit_id = g.id
                                            JOIN (Select aa.is_master_id,coalesce(sum(aa.total_cost),0) AS costs From ic_is_detail AS aa Group By aa.is_master_id) AS h ON a.id = h.is_master_id
                                            WHERE a.is_deleted = 0";

            $this->connector->CommandText .= " AND c.entity_id = ?entity";
            $this->connector->AddParameter("?entity", $this->userCompanyId);

            if ($projectId != -1) {
                $this->connector->CommandText .= " AND a.project_id = ?project";
                $this->connector->AddParameter("?project", $projectId);
            }

            if ($deptId != -1) {
                $this->connector->CommandText .= " AND a.dept_id = ?dept";
                $this->connector->AddParameter("?dept", $deptId);
            }

            if ($status != -1) {
                $this->connector->CommandText .= " AND a.status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.issue_date >= ?start
                                               AND a.issue_date <= ?end
                                               ORDER BY a.issue_date ASC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $projectId = null;
            $deptId = null;
            $status = null;
            $startDate = time();
            $endDate = time();
            $output = "web";
            $report = null;
        }


        $dept = new Department();
        $deptCd = $dept->LoadByEntityId($this->userCompanyId);
        $this->Set("dept", $deptCd);

        $deptName = $dept->FindById($deptId);
        $deptName = $deptName != null ? $deptName->DeptName : "SEMUA DEPARTEMEN";
        $this->Set("deptName", $deptName);

        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);

        $deptName = $dept->FindById($deptId);
        $deptName = $deptName != null ? $deptName->DeptName : "SEMUA DEPARTEMEN";
        $this->Set("deptName", $deptName);

        $syscode = new StatusCode();
        $this->Set("is_status", $syscode->LoadIsStatus());

        $temp = $syscode->FindBy("gn_status", $status);
        $statusName = $temp != null ? $temp->ShortDesc : "SEMUA STATUS";
        $this->Set("statusName", $statusName);

        $this->Set("report", $report);
        $this->Set("projectId", $projectId);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
        $this->Set("userLevel", $this->userLevel);
    }

    //proses cetak form IS
    public function doc_print($output){
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
            redirect_url("inventory.is");
            return;
        }

        $report = array();
        foreach ($ids as $id) {
            $is = new ItemIssue();
            $is = $is->LoadById($id);
            $is->LoadDetails();
            $is->LoadUsers();
            $is->LoadDepartment();

            $report[] = $is;
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

            $this->connector->CommandText = "SELECT a.item_id, a.uom_cd, c.item_code, c.part_no, c.item_name, SUM(a.qty) AS jumlah, SUM(a.total_cost) AS total
                                            FROM ic_is_detail AS a
                                            JOIN ic_is_master AS b ON a.is_master_id = b.id
                                            JOIN ic_item_master AS c ON a.item_id = c.id
                                            WHERE b.is_deleted = 0 AND b.status > 1
                                            AND b.project_id = ?project
                                            AND b.issue_date >= ?start
                                            AND b.issue_date <= ?end
                                            GROUP BY a.item_id, a.uom_cd, c.item_code, c.part_no, c.item_name
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
        $this->Set("output", $output);
        $this->Set("userLevel", $this->userLevel);
    }

	public function posting() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di posting !");
			redirect_url("inventory.is");
			return;
		}

		require_once(MODEL . "inventory/item.php");
		require_once(MODEL . "inventory/item_category.php");
		require_once(MODEL . "accounting/voucher.php");

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$itemIssue = new ItemIssue();
			$itemIssue = $itemIssue->LoadById($id);

			if ($itemIssue == null || $itemIssue->IsDeleted) {
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($itemIssue->EntityId != $this->userCompanyId) {
					continue;
				}
			}
			if ($itemIssue->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Item Issue: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $itemIssue->DocumentNo, $itemIssue->GetStatus());
				continue;
			}

			$itemIssue->PostedById = $userId;

			// Biasa... harus pakai transaction biar aman...
			$this->connector->BeginTransaction();

			// Step #01: Variable Voucher
			$voucher = new Voucher();
			$voucher->DocumentTypeId = 9;
			$voucher->DocumentNo = $itemIssue->DocumentNo;
			$voucher->Date = $itemIssue->Date;
			$voucher->EntityId = $itemIssue->EntityId;
			$voucher->Note = "Posting otomatis dari Issue " . $itemIssue->DocumentNo;
			$voucher->StatusCode = 4;	// lsg status posted krn dari GN
			$voucher->CreatedById = $userId;
			$voucher->VoucherSource = "ISSUE";

			// Step #02: Post Item Issue
			$rs = $itemIssue->Post($itemIssue->Id);
			if ($rs != 1) {
				$errors[] =  sprintf("Gagal Step 02 Posting Dokumen Item Issue: %s. Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// Step #03: Detail Voucher
			$itemIssue->LoadDetails();
			$sqn = 1;
			foreach ($itemIssue->Details as $detail) {
				// Ambil data barang
				$item = new Item();
				$item = $item->LoadById($detail->ItemId, true);

				$itemCategory = new ItemCategory();
				$itemCategory = $itemCategory->LoadById($item->CategoryId);

				// Voucher...
				$voucherDetail = new VoucherDetail();
                $voucherDetail->Sequence = $sqn;
				$voucherDetail->AccDebitId = $itemCategory->CostAccountId;
				$voucherDetail->AccCreditId = $itemCategory->InventoryAccountId;
				$voucherDetail->Amount = $detail->TotalCost;
				$voucherDetail->Note = sprintf("%s %s %s", $detail->Qty, $item->UomCode, $detail->ItemName);
				$voucherDetail->ProjectId = $itemIssue->ProjectId;
				$voucherDetail->DepartmentId = $detail->DeptId;
				$voucherDetail->ActivityId = $itemIssue->ActivityId;
				$voucherDetail->UnitId = $detail->UnitId;
				$voucher->Details[] = $voucherDetail;
				$sqn++;
			}

			// Step #04: Simpan Master Voucher
			$rs = $voucher->Insert();
			if ($rs != 1) {
				$errors[] =  sprintf("Gagal Step 04 Posting Dokumen Item Issue: %s. Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// Step #05: Simpan Detail Voucher
			$flagSuccess = true;
			foreach ($voucher->Details as $voucherDetail) {
				$voucherDetail->VoucherId = $voucher->Id;

				$rs = $voucherDetail->Insert();
				if ($rs != 1) {
					$errors[] =  sprintf("Gagal Step 05 Posting Dokumen Item Issue: %s. Message: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
					$this->connector->RollbackTransaction();
					$flagSuccess = false;
					break;
				}
			}

			// Step #06: Commit (tidak perlu RollBack secara explicit disini karena sudah di rollback pada saat gagal)
			if ($flagSuccess) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Item Issue: %s sudah berhasil diposting.", $itemIssue->DocumentNo);
			}
		}

		// OK semua process posting complete... tendang ke halaman voucher jika boleh
		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}

		redirect_url("inventory.is");
	}

	public function unposting() {
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
			$itemIssue = new ItemIssue();
			$itemIssue = $itemIssue->LoadById($id);

			if ($itemIssue == null || $itemIssue->IsDeleted) {
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($itemIssue->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}
			if ($itemIssue->StatusCode != 5) {
				$errors[] = sprintf("Dokumen Item Issue: %s tidak diproses karena status sudah bukan POSTED ! Status Dokumen: %s", $itemIssue->DocumentNo, $itemIssue->GetStatus());
				continue;
			}

			// Open transaction
			$this->connector->BeginTransaction();

			// Step #01: Delete Voucher terlebih dahulu
			$voucher = new Voucher();
			$rs = $voucher->DeleteByDocNo($itemIssue->DocumentNo);
			if ($rs == -1) {
				$errors[] = sprintf("Gagal unposting Item Issue (gagal hapus voucher): %s. Error: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			} else if ($rs == 0) {
				// Ini aneh... status posted tapi ga ketemu vouchernya pas saat hapus...
				$infos[] = sprintf("NOTICE: Dokumen Item Issue: %s tidak memiliki Voucher tetapi status POSTED.", $itemIssue->DocumentNo);
			}

			// Step #02: Batalkan status posted GN
			$itemIssue->UpdatedById = $userId;
			$rs = $itemIssue->UnPost($itemIssue->Id);
			if ($rs != 1) {
				$errors[] = sprintf("Gagal unposting Item Issue (gagal set flag): %s. Error: %s", $itemIssue->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
				continue;
			}

			// Step #03: Commit
			$this->connector->CommitTransaction();
			$infos[] = sprintf("Dokumen Item Issue: %s sudah berhasil un-posting.", $itemIssue->DocumentNo);
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("inventory.is");
	}

    public function add($isId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/units.php");

        $loader = null;
        $is = new ItemIssue();
        if ($isId > 0 ) {
            $is = $is->LoadById($isId);
            if ($is == null) {
                $this->persistence->SaveState("error", "Maaf Data Issue dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.is");
            }
            if ($is->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $is->DocumentNo,$is->GetStatus()));
                redirect_url("inventory.is");
            }
        }else{
            $is->Date = date('d-m-Y');
        }

        // load details
        $is->LoadDetails();
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load activity
        $activity = new Activity();
        $activities = $activity->LoadByEntityId($this->userCompanyId);
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //load units
        $units = new Units();
        $units = $units->LoadAll($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("activities", $activities);
        $this->Set("units", $units);
        $this->Set("is", $is);
    }

    public function proses_master($isId = 0) {
        $is = new ItemIssue();
        if (count($this->postData) > 0) {
            $is->Id = $isId;
            $is->EntityId = $this->userCompanyId;
            $is->ProjectId = $this->GetPostValue("ProjectId");
            $is->Date = strtotime($this->GetPostValue("IssueDate"));
            $is->DepartmentId = $this->GetPostValue("DepartmentId");
            $is->ActivityId = $this->GetPostValue("ActivityId");
            $is->Note = $this->GetPostValue("Note");
            $is->DocumentNo = $this->GetPostValue("IssueNo");
            $is->CreatedById = $this->userUid;
            if ($is->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $is->DocumentNo = $docCounter->AutoDocNoIs($is->EntityId, $is->Date, 1);
                $rs = $is->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$is->Id,$is->DocumentNo);
                }else{
                    printf("ER|A|%d",$is->Id);
                }
            }else{
                $rs = $is->Update($is->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$is->Id,$is->DocumentNo);
                }else{
                    printf("ER|U|%d",$is->Id);
                }
            }
        }else{
            printf("ER|X|%d",$isId);
        }
    }

    public function add_detail($isId = null) {
        $rst = null;
        $is = new ItemIssue($isId);
        $isdetail = new ItemIssueDetail();
        $isdetail->IsId = $isId;
        $is_item_exist = false;
        if (count($this->postData) > 0) {
            $isdetail->ItemId = $this->GetPostValue("aItemId");
            $isdetail->Qty = $this->GetPostValue("aIssueQty");
            if ($this->GetPostValue("aUnitId") > 0){
                $isdetail->UnitId = $this->GetPostValue("aUnitId");
            }else{
                $isdetail->UnitId = 0;
            }
            $isdetail->ItemDescription = '-';
            $isdetail->MrDetailId = $this->GetPostValue("aMrDetailId");
            $isdetail->UomCd = $this->GetPostValue("aUomCd");
            if ($this->GetPostValue("aHm") == '' || $this->GetPostValue("aHm") == null){
                $isdetail->Hm = 0;
            }else{
                $isdetail->Hm = $this->GetPostValue("aHm");
            }
            if ($this->GetPostValue("aDayShift") == '' || $this->GetPostValue("aDayShift") == null){
                $isdetail->DayShift = 0;
            }else{
                $isdetail->DayShift = $this->GetPostValue("aDayShift");
            }
            $isdetail->Operator = $this->GetPostValue("aOperator");
            $isdetail->DeptId = $this->GetPostValue("aDeptId");
            if ($isdetail->DeptId == 0 || $isdetail->DeptId == ''){
                $isdetail->DeptId = $is->DepartmentId;
            }
            // item baru simpan
            $rs = $isdetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$isdetail->Id);
                //creat mr link
                if ($isdetail->MrDetailId > 0) {
                    //create link to mr
                    $this->connector->CommandText = "INSERT INTO ic_link_mr_is(mr_id, is_id) VALUES (?mr, ?is)";
                    $this->connector->AddParameter("?mr", $isdetail->MrDetailId);
                    $this->connector->AddParameter("?is", $isId);
                    $rs = $this->connector->ExecuteNonQuery();
                    //update mr qty
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.iss_qty = a.iss_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $isdetail->Qty);
                    $this->connector->AddParameter("?id", $isdetail->MrDetailId);
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

    public function edit_detail($dId = null) {
        $rst = null;
        $isdetail = new ItemIssueDetail();
        $isdetail = $isdetail->LoadById($dId);
        $is_item_exist = false;
        if (count($this->postData) > 0) {
            $isdetail->ItemId = $this->GetPostValue("aItemId");
            $isdetail->Qty = $this->GetPostValue("aIssueQty");
            $xQty = $this->GetPostValue("xIssueQty");
            if ($this->GetPostValue("aUnitId") > 0){
                $isdetail->UnitId = $this->GetPostValue("aUnitId");
            }else{
                $isdetail->UnitId = 0;
            }
            $isdetail->ItemDescription = '-';
            $isdetail->MrDetailId = $this->GetPostValue("aMrDetailId");
            $isdetail->UomCd = $this->GetPostValue("aUomCd");
            if ($this->GetPostValue("aHm") == '' || $this->GetPostValue("aHm") == null){
                $isdetail->Hm = 0;
            }else{
                $isdetail->Hm = $this->GetPostValue("aHm");
            }
            if ($this->GetPostValue("aDayShift") == '' || $this->GetPostValue("aDayShift") == null){
                $isdetail->DayShift = 0;
            }else{
                $isdetail->DayShift = $this->GetPostValue("aDayShift");
            }
            $isdetail->Operator = $this->GetPostValue("aOperator");
            $isdetail->DeptId = $this->GetPostValue("aDeptId");
            if ($isdetail->DeptId == 0 || $isdetail->DeptId == ''){
                $is = new ItemIssue($isdetail->IsId);
                $isdetail->DeptId = $is->DepartmentId;
            }
            // item baru simpan
            $rs = $isdetail->Update($dId) == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses update data berhasil!',$isdetail->Id);
                //creat mr link
                if ($isdetail->MrDetailId > 0) {
                    //update mr qty
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.iss_qty = a.iss_qty - ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $xQty);
                    $this->connector->AddParameter("?id", $isdetail->MrDetailId);
                    $rs = $this->connector->ExecuteNonQuery();
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.iss_qty = a.iss_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $isdetail->Qty);
                    $this->connector->AddParameter("?id", $isdetail->MrDetailId);
                    $rs = $this->connector->ExecuteNonQuery();
                }
            } else {
                $rst = 'ER|Gagal proses update data!';
            }
        }else{
            $rst = "ER|No Data updated!";
        }
        print($rst);
    }

    public function delete_detail($id) {
        // Cek datanya
        $isdetail = new ItemIssueDetail();
        $isdetail = $isdetail->LoadById($id);
        if ($isdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $mri = $isdetail->MrDetailId;
        $isi = $isdetail->IsId;
        $qty = $isdetail->Qty;
        if ($isdetail->Delete($id) == 1) {
            if ($mri > 0) {
                //delete link to mr
                $this->connector->CommandText = "Delete From ic_link_mr_is Where mr_id = ?mr And is_id = ?is";
                $this->connector->AddParameter("?mr", $mri);
                $this->connector->AddParameter("?is", $isi);
                $rs = $this->connector->ExecuteNonQuery();
                //update mr qty
                $this->connector->CommandText = "Update ic_mr_detail AS a Set a.iss_qty = a.iss_qty - ?qty Where a.id = ?id";
                $this->connector->AddParameter("?qty", $qty);
                $this->connector->AddParameter("?id", $mri);
                $rs = $this->connector->ExecuteNonQuery();
            }
            printf("OK|%d|Hapus Data berhasil!",$id);
        }else{
            printf("ER|%d|Hapus Data gagal!",$id);
        }
    }

    public function getjson_mritems($projectId = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $mritems = new ItemIssue();
        $mritems = $mritems->GetJSonUnfinishedMrItems($projectId,$filter);
        echo json_encode($mritems);
    }

    public function view($isId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/units.php");

        $loader = null;
        $is = new ItemIssue();
        if ($isId > 0 ) {
            $is = $is->LoadById($isId);
            if ($is == null) {
                $this->persistence->SaveState("error", "Maaf Data Issue dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.is");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Issue tidak ditemukan!");
            redirect_url("inventory.is");
        }

        // load details
        $is->LoadDetails();
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load activity
        $activity = new Activity();
        $activities = $activity->LoadByEntityId($this->userCompanyId);
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //load units
        $units = new Units();
        $units = $units->LoadAll($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("activities", $activities);
        $this->Set("units", $units);
        $this->Set("is", $is);
    }
}


// End of File: is_controller.php
