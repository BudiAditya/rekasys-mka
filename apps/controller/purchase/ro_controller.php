<?php

class RoController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "purchase/ro.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "e.project_name", "display" => "Project", "width" => 100);
        $settings["columns"][] = array("name" => "a.doc_no", "display" => "RO Number", "width" => 120);
		$settings["columns"][] = array("name" => "c.creditor_name", "display" => "Supplier", "width" => 200);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.ro_date, '%d %M %Y')", "display" => "Date", "width" => 80);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.expected_date, '%d %M %Y')", "display" => "Required Date", "width" => 80);
		$settings["columns"][] = array("name" => "format(g.subtotal,0)", "display" => "Amount", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "d.short_desc", "display" => "Status", "width" => 100);
		//$settings["columns"][] = array("name" => "CASE WHEN a.is_vat = 1 THEN 'Iya' ELSE 'Tidak' END", "display" => "PPN ?", "width" => 60);
		//$settings["columns"][] = array("name" => "CASE WHEN a.is_vat <> 1 THEN '-' WHEN a.is_inc_vat = 1 THEN 'Iya' ELSE 'Tidak' END", "display" => "Included ?", "width" => 60);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "Nomor RO");
		$settings["filters"][] = array("name" => "c.creditor_name", "display" => "Supplier");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Repair Order";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("ro", "add_master", "purchase")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "purchase.ro/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ro", "view", "purchase")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "purchase.ro/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "purchase.ro/overview", "Class" => "bt_report", "ReqId" => 0);
			}
            if ($acl->CheckUserAccess("ro", "doc_print", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "XLS Print", "Url" => "purchase.ro/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
                $settings["actions"][] = array("Text" => "PDF Print", "Url" => "purchase.ro/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }

			if ($acl->CheckUserAccess("ro", "edit_master", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "Edit", "Url" => "purchase.ro/add/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen RO terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "Apakah anda mau merubah data Dokumen RO yang dipilih ?");
			}
			/*
			if ($acl->CheckUserAccess("ro", "split", "purchase")) {
				$settings["actions"][] = array("Text" => "Split", "Url" => "purchase.ro/split/%s", "Class" => "bt_split", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen RO terlebih dahulu sebelum melakukan proses split dokumen.\nPERHATIAN: Pilih tepat 1 dokumen",
											   "Confirm" => "");
			}
			*/
			if ($acl->CheckUserAccess("ro", "delete", "purchase")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "purchase.ro/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen RO terlebih dahulu sebelum melakukan proses delete !\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
											   "Confirm" => "Apakah anda mau menghapus Dokumen RO yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("ro", "batch_approve", "purchase")) {
				$settings["actions"][] = array("Text" => "Approval", "Url" => "purchase.ro/batch_approve/", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen RO !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua dokumen RO yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("ro", "batch_disapprove", "purchase")) {
				$settings["actions"][] = array("Text" => "Batal Approval", "Url" => "purchase.ro/batch_disapprove/", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen RO !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen RO yang dipilih ?\nProses Dis-Approve Akan membuat status dokumen menjadi DRAFT.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;
            
		} else {
			$settings["from"] =
"ic_ro_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ap_creditor_master AS c ON a.supplier_id = c.id
	JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'po_status'
	JOIN cm_project AS e ON a.project_id = e.id
	JOIN (Select f.ro_master_id, sum(f.qty * f.price) AS subtotal From ic_ro_detail AS f Group By f.ro_master_id) AS g On a.id = g.ro_master_id
	";

			$settings["where"] = "a.is_deleted = 0 AND b.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen RO terlebih dahulu.");
			redirect_url("purchase.ro");
			return;
		}

		$ro = new Ro();
		$ro = $ro->LoadById($id);
		if ($ro == null || $ro->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen RO yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("purchase.ro");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($ro->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen RO yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("purchase.ro");
				return;
			}
		}
		if ($ro->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen RO yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("purchase.ro/view/" . $ro->Id);
			return;
		}

		//cek detail
        $details = $ro->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "RO No: ". $ro->DocumentNo." Harap hapus dulu Detail Itemnya");
            redirect_url("purchase.ro");
            return;
        }

		// Everything is green
		// ToDo: Kalau Referensi PR nya bukan proses RO bagaimana ?
		$this->connector->BeginTransaction();
		// Step 1: OK Hapus referensi PR jika ada...
		//	NOTE : Reset status PR jika dan hanya jika PR tersebut sudah tidak direferensikan lagi oleh RO yang lain
		$this->connector->CommandText =
"UPDATE ic_rr_master SET
	status = 2
	, updateby_id = ?user
	, update_time = NOW()
WHERE id IN (
	-- LOGIC: cari semua PR id (self join berdasarkan pr_id) yang mana tidak boleh sama dengan RO yang dihapus dan statusnnya belum di delete
	--        Jika ketemu pasangannya bearti masih ada referensinya. CARI YANG REFERENSINYA NULL
	SELECT a.pr_id -- AS del_pr_id, a.ro_id AS del_ro_id, a.is_deleted AS del_is_deleted, b.*
	FROM ic_link_pr_po AS a
		LEFT JOIN ic_link_pr_po AS b ON a.pr_id = b.pr_id AND b.ro_id <> ?roId AND b.is_deleted = 0
	WHERE a.ro_id = ?roId AND b.pr_id IS NULL
)";
		$this->connector->AddParameter("?user", AclManager::GetInstance()->GetCurrentUser()->Id);
		$this->connector->AddParameter("?roId", $ro->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen RO: %s ! Gagal Hapus Referensi PR<br /> Harap hubungi system administrator.<br />Error: %s", $ro->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.ro");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_pr_po SET is_deleted = 1 WHERE ro_id = ?roId";
		$this->connector->AddParameter("?roId", $ro->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen RO: %s ! Gagal Hapus Link PR-RO<br /> Harap hubungi system administrator.<br />Error: %s", $ro->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.ro");
		}

		// Step 3: Hapus Link ke NPKP jika statusnya masih draft
		require_once(MODEL . "accounting/cash_request.php");
		$cashRequest = new CashRequest();
		$cashDetail = new CashRequestDetail();
		$cashDetail = $cashDetail->LoadByRoId($ro->Id);
		$flagDelete = false;
		if ($cashDetail != null) {
			$cashRequest = $cashRequest->LoadById($cashDetail->CashRequestId);
			if ($cashRequest->StatusCode == 1) {
				// OK mari kita delete....
				$flagDelete = $cashDetail->Delete($cashDetail->Id) == 1;
			} else {
				// Ops... jangan delete karena sudah proses...
				$flagDelete = false;
			}
		}

		// Step 4: Hapus dokumen RO
		$ro->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($ro->Delete($ro->Id) == 1) {
			$this->connector->CommitTransaction();
			$message = sprintf("Dokumen RO: %s sudah berhasil dihapus.", $ro->DocumentNo);
			if ($cashDetail != null) {
				if ($flagDelete) {
					$message .= sprintf("<br />NPKP: %s sudah diupdate (detail sudah dihapus)", $cashRequest->DocumentNo);
				} else {
					$this->persistence->SaveState("error", sprintf("Maaf NPKP: %s sudah diproses. Link RO pada NPKP tidak dihapus !", $cashRequest->DocumentNo));
				}
			}
			$this->persistence->SaveState("info", $message);
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen RO: %s ! Harap hubungi system administrator.<br />Error: %s", $ro->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("purchase.ro");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("purchase.ro");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$ro = new Ro();
			$ro = $ro->LoadById($id);

			if ($ro->StatusCode != 1) {
				$errors[] = sprintf("Dokumen RO: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $ro->DocumentNo, $ro->GetStatus());
				continue;
			}
			if ($ro->EntityId != $this->userCompanyId) {
                // Trying to access other Company data ! Bypass it..
                continue;
            }

			$ro->ApprovedById = $userId;
			$rs = $ro->Approve($ro->Id);
			if ($rs == 1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen RO: %s sudah berhasil di approve", $ro->DocumentNo);
			} else {
				$errors[] =  sprintf("Gagal Approve Dokumen RO: %s. Message: %s", $ro->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("purchase.ro");
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen RO yang akan di batalkan !");
			redirect_url("purchase.ro");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$ro = new Ro();
			$ro = $ro->LoadById($id);

			if ($ro->StatusCode != 3) {
				$errors[] = sprintf("Dokumen RO: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $ro->DocumentNo, $ro->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($ro->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$ro->UpdatedById = $userId;
			$rs = $ro->DisApprove($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen RO: %s sudah berhasil di dibatalkan (disapprove)", $ro->DocumentNo);
			} else {
				$errors[] =  sprintf("Gagal Membatalkan / Disapprove Dokumen RO: %s. Message: %s", $ro->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("purchase.ro");
	}

	/**
	 * Untuk memilih dokumen PR mana yang akan dijadikan RO
	 */
	public function find_rr() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.project_name", "display" => "Project", "width" => 100);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No Dokumen RR", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.rr_date, '%d %M %Y')", "display" => "Tgl. RR", "width" => 100, "sortable" => false);
        $settings["columns"][] = array("name" => "c.short_desc", "display" => "Req Level", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.qty_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Qty Status", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.prc_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Price Status", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.sup_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Vendor Status", "width" => 70);
		$settings["columns"][] = array("name" => "b.short_desc", "display" => "Progress Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d %M %Y')", "display" => "Tgl. Update", "width" => 100, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No Dokumen PR");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "RR to RO Process";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("pr", "view", "purchase")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "purchase.rr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("ro", "process_rr", "purchase")) {
				$settings["actions"][] = array("Text" => "Proses Menjadi RO", "Url" => "purchase.ro/process_rr", "Class" => "bt_process", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen PR !",
											   "Confirm" => "Apakah anda mau membuat RO berdasarkan PR yang sudah dipilih ?\n\nPERHATIAN: RO yang dibuat bisa > 1 jika ditemukan supplier yang berbeda.");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("ro", "process_all_pr", "purchase")) {
				$settings["actions"][] = array("Text" => "Proses Semua RR menjadi RO", "Url" => "purchase.ro/process_all_pr", "Class" => "bt_process", "ReqId" => 0,
											   "Confirm" => "Apakah anda mau memproses semua RR menjadi RO ?\n\nPERHATIAN: proses ini dapat membuat RO > 1 jika ditemukan supplier yang berbeda.");
			}

			$settings["def_filter"] = 1;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;
		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
			$settings["from"] = "vw_ic_rr_master AS a JOIN sys_status_code AS b ON a.status = b.code AND b.key = 'pr_status' LEFT JOIN sys_status_code AS c ON a.req_level = c.code AND c.key = 'mr_req_level'";
            $settings["where"] = "a.qty_status > 0 AND a.prc_status > 0 AND a.sup_status > 0 AND a.is_deleted = 0 AND a.status = 3 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function process_rr() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen PR yang akan diproses menjadi RO !");
			redirect_url("purchase.ro/find_pr");
			return;
		}

		$infos = array();
		$errors = array();

		// OK everything is green
		$this->GenerateRo($ids, $errors, $infos);
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");

		redirect_url("purchase.ro");
	}

	/**
	 * Proses utama yang membuat RO
	 *
	 * @param array $ids => ID dokumen PR (automatically filtered by current user Company)
	 * @param $errors	[OUT]
	 * @param $infos	[OUT]
	 * @return bool
	 */
	private function GenerateRo(array $ids = null, &$errors, &$infos) {
	// Step 1:	Ambil semua barang dari PR berdasarkan ID yang dikirim
	//			NOTE: supplier dan harga sudah auto select dari selected_supplier, dan sudah di sort berdasarkan supplier
		$query =
"SELECT
	a.id AS id_mst
	, a.doc_no
	, b.id AS id_dtl
	, b.item_id
	-- , c.item_name
	, b.selected_supplier
	, CASE b.selected_supplier
			WHEN 1 THEN b.supplier_id_1
			WHEN 2 THEN b.supplier_id_2
			WHEN 3 THEN b.supplier_id_3
			ELSE NULL END AS supplier_id
	, CASE b.selected_supplier
			WHEN 1 THEN b.price_1
			WHEN 2 THEN b.price_2
			WHEN 3 THEN b.price_3
			ELSE NULL END AS price
	, b.qty
	, b.uom_cd
	, b.item_description
	, a.project_id
FROM ic_rr_master AS a
	JOIN ic_rr_detail AS b ON a.id = b.rr_master_id
	-- JOIN ic_item_master AS c ON b.item_id = c.id
WHERE a.is_deleted = 0 AND a.status = 3 AND a.entity_id = ?sbu %s
ORDER BY
	CASE b.selected_supplier WHEN 1 THEN b.supplier_id_1 WHEN 2 THEN b.supplier_id_2 WHEN 3 THEN b.supplier_id_3 ELSE NULL END
	, a.id
-- ORDER BY a.id, b.id;";
		if ($ids == null) {
			// Jgn filter berdasarkan ID
			$this->connector->CommandText = sprintf($query, "");
		} else {
			$this->connector->CommandText = sprintf($query, "AND a.id IN ?ids");
			$this->connector->AddParameter("?ids", $ids);
		}

		$this->connector->AddParameter("?sbu", $this->userCompanyId);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			$errors[] = "Gagal mengambil data barang PR ! Hubungi system administrator !<br />Message: " . $this->connector->GetErrorMessage();
			$infos[] = "Proses RO otomatis dibatalkan";
			return false;
		}
		if ($rs->GetNumRows() == 0) {
			if ($ids == null) {
				$this->persistence->SaveState("error", "Maaf tidak ada PR yang dapat diproses ! Pastikan dokumen PR sudah di approve agar dapat dibuat RO otomatis.");
			} else {
				$this->persistence->SaveState("error", "Data barang PR tidak ditemukan ! Pastikan dokumen PR yang dipilih sudah benar");
			}
			$infos[] = "Proses RO otomatis dibatalkan";

			return false;
		}

		// Step 2:	List barang sudah ada dan sudah berurut... mari kita bikin RO nya...
		//			NOTE: Berbeda dengan proses buat PR kalau ini kita sulit fix pasti jadi 1 karena supplier bisa mana aja...
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		$roevSupplier = null;
		$roevDocNo = null;
		$ro = null;
		$allRo = array();

		while ($row = $rs->FetchAssoc()) {
			$flagNewRo = false;

			if ($roevSupplier != $row["supplier_id"]) {
				if ($ro != null) {
					// OK entry data RO sebelumnya
					$allRo[] = $ro;
				}

				// Sudah beda supplier artinya beda RO
				$roevSupplier = $row["supplier_id"];

				$flagNewRo = true;
				$ro = new Ro();
				$ro->EntityId = $this->userCompanyId;
				$ro->CreatedById = $userId;
				$ro->Date = time();
				$ro->SupplierId = $roevSupplier;
				$ro->IsVat = 1;
				$ro->IsIncludeVat = false;
				$ro->ExpectedDate = $ro->Date + 604800;
				$ro->PaymentTerms = 30;
				$ro->Note = "PR:";
				$ro->StatusCode = 1;
				$ro->ProjectId = $row["project_id"];
			}

			// OK looping data barangnya...
			$itemId = $row["item_id"];
			if ($roevDocNo != $row["doc_no"] || $flagNewRo) {
				$roevDocNo = $row["doc_no"];
				$ro->Note .= "\n * " . $roevDocNo;
				$ro->PrIds[] = $row["id_mst"];
				$ro->PrCodes[] = $row["doc_no"];
			}
			if (array_key_exists($itemId, $ro->Details)) {
				// Hwee barang yang sama uda ada di RO ? Kemungkinan terbesar adalah beda dept tetapi request barang yang sama
				// NOTE:
				//	* RoDetailId tidak di update
				//	* Jumlah ditambah dan harga pakai yang minimum
				//	* ItemDescription akan di append
				$detail = $ro->Details[$itemId];

				$detail->Qty += $row["qty"];
				$detail->Price = min($detail->Price, $row["price"]);
				if (strlen($detail->ItemDescription) > 0) {
					$detail->ItemDescription .= ", " . $row["item_description"];
				} else {
					$detail->ItemDescription = $row["item_description"];
				}
			} else {
				$detail = new RoDetail();

				$detail->RrId = $row["id_dtl"];
				$detail->ItemId = $itemId;
				$detail->Qty = $row["qty"];
				$detail->UomCd = $row["uom_cd"];
				$detail->Price = $row["price"];
				$detail->ItemDescription = $row["item_description"];

				$ro->Details[$itemId] = $detail;
			}
		}

		// Seperti biasa RO yang terakhir terlupakan...
		$allRo[] = $ro;

		// Karena kita mau bikin NPKP untuk semua RO maka prosesnya kita ganti sedikit disini....
		// Transactionnya harus di level global ga per level RO lagi sekarang
		$this->connector->BeginTransaction();
		$roCodes = array();

		// Step 03: Entry semua data RO
		foreach ($allRo as $ro) {
			if ($this->doAdd($ro)) {
				$roCodes[] = $ro->DocumentNo;
				$infos[] = "Berhasil Membuat RO: " . $ro->DocumentNo;
			} else {
				$infos[] = "Proses pembuatan RO dibatalkan karena ada error !";
				if ($this->connector->GetHasError()) {
					$errors[] = "Database error while processing auto RO ! Message: " . $this->connector->GetErrorMessage();
				} else {
					// Ambil error dari proses doAdd
					$errors[] = $this->dataForView["error"];
				}
				$this->connector->RollbackTransaction();
				return false;
			}
		}

        // get NPKP category
        require_once(MODEL . "accounting/cash_request_category.php");
		$crCategoryId = 0;
		$crcategory = new CashRequestCategory();
		$crcategory = $crcategory->LoadByProjectId($ro->ProjectId);
		if ($crcategory != null){
		    $crCategoryId = $crcategory->Id;
        }
		// Step 04: Buat NPKP
		require_once(MODEL . "accounting/cash_request.php");
		$docCounter = new DocCounter();

		$cashRequest = new CashRequest();
		$cashRequest->CreatedById = $userId;
		$cashRequest->EntityId = $this->userCompanyId;
		$cashRequest->Date = time();
		$cashRequest->DocumentNo = $docCounter->AutoDocNoNpkp($this->userCompanyId, $cashRequest->Date, 1);
		$cashRequest->StatusCode = 1;
		$cashRequest->Objective = "Untuk RO: " . implode(", ", $roCodes);
		$cashRequest->Note = "Ini merupakan NPKP otomatis yang dibuat pada saat proses PR -> RO. Untuk Nomor RO harap lihat ke detailnya dikarenakan yang ditujuan bisa berbeda jika ada RO yang dibatalkan.";
		$cashRequest->EtaDate = $cashRequest->Date + 604800;	// Margin 1 minggu
		$cashRequest->CategoryId = $crCategoryId;

		foreach ($allRo as $ro) {
			$cashDetail = new CashRequestDetail();
			$cashDetail->Note = "Untuk RO: " . $ro->DocumentNo;
			$cashDetail->Amount = $ro->GetTotalAmount();
			$cashDetail->PoId = $ro->Id;

			$cashRequest->Details[] = $cashDetail;
		}

		// Step 05: Entry NPKP
		if ($cashRequest->DocumentNo == null) {
			$infos[] = "Proses pembuatan RO dibatalkan karena ada error !";
			$errors[] = "Gagal membuat NPKP untuk RO ! Tanggal hari ini sudah ter-locked oleh dokumen NPKP.";
			$this->connector->RollbackTransaction();
			return false;
		}

		$rs = $cashRequest->Insert();
		if ($rs != 1) {
			$infos[] = "Proses pembuatan RO dibatalkan karena ada error !";
			$errors[] = "Gagal entry master NPKP ! Message: " . $this->connector->GetErrorMessage();
			$this->connector->RollbackTransaction();
			return false;
		}

		foreach ($cashRequest->Details as $cashDetail) {
			$cashDetail->CashRequestId = $cashRequest->Id;
			$rs = $cashDetail->Insert();
			if ($rs != 1) {
				$infos[] = "Proses pembuatan RO dibatalkan karena ada error !";
				$errors[] = "Gagal entry detail NPKP ! Message: " . $this->connector->GetErrorMessage();
				$this->connector->RollbackTransaction();
				return false;
			}
		}

		// HOOORAAAAAYYYYYYYY SUKSES :)
		$infos[] = "NPKP Untuk RO yang telah dibuat: " . $cashRequest->DocumentNo;
		$this->connector->CommitTransaction();
		return true;
	}


    /**
     * Proses entry RO ke DBase. Pastikan DBase transaction sudah aktif ! Akan ambil data running number
     *
     * @param Ro $ro
     * @return bool
     */
    private function doAdd(Ro $ro) {
        require_once(MODEL . "common/doc_counter.php");
        $docCounter = new DocCounter();
        $ro->DocumentNo = $docCounter->AutoDocNoRo($ro->EntityId, $ro->Date, 1);
        if ($ro->DocumentNo == null) {
            $this->Set("error", "Maaf anda tidak dapat membuat dokumen RO pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
            return false;
        }

        $rs = $ro->Insert();
        if ($rs != 1) {
            if ($this->connector->IsDuplicateError()) {
                $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
            } else {
                $this->Set("error", "Maaf error saat simpan master RO. Message: " . $this->connector->GetErrorMessage());
            }
            return false;
        }

        foreach ($ro->Details as $idx => $detail) {
            $detail->RoId = $ro->Id;
            $rs = $detail->Insert();
            if ($rs == 1) {
                $this->connector->CommandText = "Update ic_rr_detail AS a Set a.ro_qty = a.ro_qty + ?qty Where a.id = ?prd_id And a.item_id = ?item_id";
                $this->connector->AddParameter("?qty", $detail->Qty);
                $this->connector->AddParameter("?prd_id", $detail->RrId);
                $this->connector->AddParameter("?item_id", $detail->ItemId);
                $rs = $this->connector->ExecuteNonQuery();
                if ($rs == -1) {
                    $this->Set("error", "Gagal update RO Qty PR Detail ! Error: " . $this->connector->GetErrorMessage());
                }else {
                    // Lanjutttt
                    continue;
                }
            }

            // Gagal Insert Detail
            $no = $idx + 1;
            if ($this->connector->IsDuplicateError()) {
                $this->Set("error", "Maaf barang No. $no sudah ada pada database. Pastikan barang pada dokumen tidak ada yang sama");
            } else {
                $this->Set("error", "Maaf error saat simpan detail RO No. $no. Message: " . $this->connector->GetErrorMessage());
            }
            return false;
        }

        // Linking ke PR
        if (count($ro->PrIds) > 0) {
            require_once(MODEL . "purchase/pr.php");

            $pr = new Pr();
            foreach ($ro->PrIds as $id) {
                $pr->StatusCode = 4;
                $pr->UpdatedById = $ro->CreatedById;
                $rs = $pr->SetStatus($id);
                if ($rs == -1) {
                    $this->Set("error", "Gagal set status PR ! Error: " . $this->connector->GetErrorMessage());
                    return false;	// FAILURE
                }

                $this->connector->CommandText = "INSERT INTO ic_link_rr_ro(rr_id, ro_id) VALUES (?pr, ?po)";
                $this->connector->AddParameter("?pr", $id);
                $this->connector->AddParameter("?po", $ro->Id);
                $rs = $this->connector->ExecuteNonQuery();
                if ($rs == -1) {
                    $this->Set("error", "Gagal link PR-RO ! Error: " . $this->connector->GetErrorMessage());
                    return false;	// FAILURE
                }
            }
        }

        return true;
    }

	public function process_all_pr() {
		$infos = array();
		$errors = array();

		// OK everything is green
		$this->GenerateRo(null, $errors, $infos);
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");

		redirect_url("purchase.ro");
	}

	public function split($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Dokumen RO terlebih dahulu !");
			redirect_url("purchase.ro");
			return;
		}

		$ro = new Ro();
		$ro = $ro->LoadById($id);

		if (count($this->postData) > 0) {
			$newSupplierId = $this->GetPostValue("SupplierId");
			$ids = $this->GetPostValue("id", array());

			if ($this->ValidateSplit($ro, $newSupplierId, $ids)) {
				$this->connector->BeginTransaction();
				if ($this->doSplit($ro, $newSupplierId, $ids)) {
					$this->connector->CommitTransaction();
					redirect_url("purchase.ro");
				} else {
					$this->connector->RollbackTransaction();
				}
			}
		} else {
			if ($ro == null || $ro->IsDeleted) {
				$this->persistence->SaveState("error", "Dokumen RO yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url("purchase.ro");
				return;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($ro->EntityId != $this->userCompanyId) {
					// WOW coba akses data lintas Company ? Simulate not found !
					$this->persistence->SaveState("error", "Dokumen Item Order yang diminta tidak dapat ditemukan / sudah dihapus !");
					redirect_url("purchase.ro");
					return;
				}
			}
			// ToDo: Apakah perlu checking hanya RO draft ?
			// Saat ini saya rasa tidak perlu checking RO harus draft karena kasus ini terjadi ketika RO sudah dibawa ke supplier
			// Sudah dibawa bearti sudah di approve... dan semua data akan di copas dari RO asli

			$newSupplierId = null;
		}

		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");
		// Load RO Details kalau RO memang bisa dilihat
		$ro->LoadDetails();
		$ro->LoadAssociatedRo();

		$company = new Company();
		$company = $company->FindById($ro->EntityId);
		$supplier = new Creditor();
		$supplier = $supplier->FindById($ro->SupplierId);

		$this->Set("company", $company);
		$this->Set("supplier", $supplier);
		$this->Set("ro", $ro);
		$this->Set("suppliers", $supplier->LoadSuppliersByEntity($ro->EntityId));
		$this->Set("newSupplierId", $newSupplierId);
        $project = new Project($ro->ProjectId);
        $this->Set("project", $project);
	}

	private function ValidateSplit(Ro $ro, $newSupplierId, array $ids) {
		if ($newSupplierId == null) {
			$this->Set("error", "Mohon memilih supplier baru terlebih dahulu");
			return false;
		}
		if ($ro->SupplierId == $newSupplierId) {
			$this->Set("error", "Supplier lama dan supplier baru sama. Mohon pilih supplier yang berbeda untuk RO yang baru");
			return false;
		}
		if (count($ids) == 0) {
			$this->Set("error", "Mohon memilih detail RO yang akan di split terlebih dahulu.");
			return false;
		}

		// Sudah tahap ROST artinya sudah valid datanya
		$ro->LoadDetails();

		$buff = array();
		foreach ($ro->Details as $detail) {
			$buff[] = $detail->Id;
		}
		foreach ($ids as $id) {
			if (!in_array($id, $buff)) {
				// AJAIB.... bisa kirim ID yang tidak ada pada detail RO
				$this->Set("error", "Failed in integrity check ! Mohon ulangi proses split RO dari awal");
				return false;
			}
		}

		return true;
	}

	private function doSplit(Ro $ro, $newSupplierId, array $ids) {
		$newRo = new Ro();
		$newRo->EntityId = $ro->EntityId;
		$newRo->Date = time();
		$newRo->SupplierId = $newSupplierId;
		$newRo->IsVat = $ro->IsVat;
		$newRo->IsIncludeVat = $ro->IsIncludeVat;
		$newRo->ExpectedDate = $newRo->Date + 604800;
		$newRo->PaymentTerms = $ro->PaymentTerms;
		$newRo->Note = "RO baru hasil split dari RO: " . $ro->DocumentNo . "\nKeterangan RO Lama:\n" . $ro->Note;
		$newRo->StatusCode = 1;
		$newRo->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		$newRo->ParentRoId = $ro->Id;

		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$newRo->DocumentNo = $docCounter->AutoDocNoRo($newRo->EntityId, $newRo->Date, 1);
		if ($newRo->DocumentNo == null) {
			$this->Set("error", "Maaf proses split RO tidak dapat dilakukan. Tanggal hari ini sudah terlocked oleh system. Hubungi system admin.");
			return false;
		}

		// OK Cara Curang is back in action.... (Migrasi data secara langsung....)
		$rs = $newRo->Insert();
		if ($rs != 1) {
			$this->Set("error", "Proses split RO gagal karena dokumen RO terbaru tidak berhasil disimpan. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		$counter = 0;
		foreach ($ro->Details as $detail) {
			if (!in_array($detail->Id, $ids)) {
				continue;
			}

			// Mari kita migrasikan
			$counter++;
			$detail->RoId = $newRo->Id;
			$rs = $detail->Update($detail->Id);
			if ($rs != 1) {
				$this->Set("error", "Gagal migrasi detail RO ke yang baru. Detail ID: " . $detail->Id . ". Message: " . $this->connector->GetErrorMessage());
				return false;
			}
		}

		// Last integrity check
		if ($counter != count($ids)) {
			$this->Set("error", sprintf("Integrity check failed ! Jumlah migrasi != jumlah yang dipilih (%d != %d)", $counter, count($ids)));
			return false;
		}

		// Bikin Link PR - RO
		$this->connector->CommandText =
"INSERT INTO ic_link_pr_po
SELECT DISTINCT b.rr_master_id, ?newRoId
-- SELECT a.id, a.pr_detail_id, b.id, b.rr_master_id
FROM ic_ro_detail AS a
	JOIN ic_rr_detail AS b ON a.pr_detail_id = b.id
WHERE a.ro_master_id = ?newRoId";
		$this->connector->AddParameter("?newRoId", $newRo->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal migrasi link PR - RO. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// OK remove redundant link PR - RO pada dokumen yang lama
		$this->connector->CommandText =
"UPDATE ic_link_pr_po SET is_deleted = 1 WHERE ro_id = ?oldRoId AND pr_id NOT IN (
	SELECT DISTINCT b.rr_master_id
	-- SELECT a.id, a.pr_detail_id, b.id, b.rr_master_id
	FROM ic_ro_detail AS a
		JOIN ic_rr_detail AS b ON a.pr_detail_id = b.id
	WHERE a.ro_master_id = ?oldRoId
)";
		$this->connector->AddParameter("?oldRoId", $ro->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal hapus link PR - RO pada dokumen RO yang lama. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// OK logic na sih copas dari split PR jadi... semoga we ga salah wakakakak
		$this->persistence->SaveState("info", sprintf("RO: %s telah berhasil di split menjadi RO baru dengan no: %s", $ro->DocumentNo, $newRo->DocumentNo));
		return true;
	}

    //proses cetak form RO
    public function doc_print($output){

        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
            redirect_url("purchase.ro");
            return;
        }

        $report = array();

        foreach ($ids as $id) {

            $ro = new Ro();
            $ro = $ro->LoadById($id);
            $ro->LoadDetails(true);
            $ro->LoadSupplier();
            $ro->LoadUsers();
            $ro->LoadCompany();
            $ro->LoadProject();

            $report[] = $ro;
        }

        $this->Set("report", $report);
        $this->Set("output", $output);
    }

    //buat halaman search data
    public function overview() {

        require_once(MODEL. "purchase/po.php");
        require_once(MODEL. "master/creditor.php");
        require_once(MODEL. "status_code.php");
        require_once(MODEL . "master/project.php");

        $ro = new Ro();

        if (count($this->getData) > 0) {
            $supplierId = $this->GetGetValue("supplier");
            $status = $this->GetGetValue("status");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.entity_cd AS entity, c.creditor_name AS supplier, d.short_desc AS status_name
                                            FROM ic_ro_master AS a
                                            JOIN cm_company AS b ON a.entity_id = b.entity_id
                                            JOIN ap_creditor_master AS c ON a.supplier_id = c.id
                                            JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'ro_status'
                                            WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7){
                $this->connector->CommandText .= " AND b.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }
            if ($supplierId != -1) {
                $this->connector->CommandText .= " AND a.supplier_id = ?supplier";
                $this->connector->AddParameter("?supplier", $supplierId);
            }
            if ($status != -1) {
                $this->connector->CommandText .= " AND a.status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.ro_date >= ?start
                                               AND a.ro_date <= ?end
                                               ORDER BY a.ro_date ASC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $supplierId = null;
            $status = null;
            $startDate = time();
            $endDate = time();
            $output = "web";
            $report = null;
        }


        $creditor = new Creditor();
        $creditorAll = $this->userCompanyId != 7 ? $creditor->LoadByEntity($this->userCompanyId) : $creditor->LoadAll();
        $this->Set("creditorAll", $creditorAll);

        $supplier = $creditor->FindById($supplierId);
        $supplier = $supplier != null ? $supplier->CreditorName : "SEMUA SUPPLIER";
        $this->Set("supplierName", $supplier);

        $syscode = new StatusCode();
        $this->Set("ro_status", $syscode->LoadRoStatus());

        $temp = $syscode->FindBy("ro_status", $status);
        $statusName = $temp != null ? $temp->ShortDesc : "SEMUA STATUS";
        $this->Set("statusName", $statusName);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
        $project = new Project($ro->ProjectId);
        $this->Set("project", $project);
    }

    //pencarian jumlah barang by tgl.dokumen (tracking & monitoring)
    public function item_recap(){
        require_once(MODEL. "status_code.php");

        if (count($this->getData) > 0) {
            $status = $this->GetGetValue("status");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.item_id, a.uom_cd, c.item_code, c.item_name, SUM(a.qty) AS jumlah, SUM(a.qty * a.price) AS total
                                            FROM ic_ro_detail AS a
                                            JOIN ic_ro_master AS b ON a.ro_master_id = b.id
                                            JOIN ic_item_master AS c ON a.item_id = c.id
                                            WHERE b.is_deleted = 0
                                            AND b.status = ?status
                                            AND b.ro_date >= ?start
                                            AND b.ro_date <= ?end
                                            GROUP BY a.item_id, a.uom_cd, c.item_code, c.item_name
                                            ORDER BY c.item_code";

            $this->connector->AddParameter("?status", $status);
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $status = null;
            $startDate = time();
            $endDate = time();
            $output = "web";
            $report = null;
        }

        $syscode = new StatusCode();
        $this->Set("ro_status", $syscode->LoadRoStatus());
        $this->Set("statusName", $syscode->FindBy("ro_status", $status));

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
    }

    public function add($roId = 0) {
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $ro = new Ro();
        if ($roId > 0 ) {
            $ro = $ro->LoadById($roId);
            if ($ro == null) {
                $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.ro");
            }
            if ($ro->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $ro->DocumentNo,$ro->GetStatus()));
                redirect_url("purchase.ro");
            }
        }else{
            $ro->Date = date('d-m-Y');
        }

        // load details
        $ro->LoadDetails();
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
        $this->Set("ro", $ro);
    }

    public function proses_master($roId = 0) {
        $ro = new Ro();
        if (count($this->postData) > 0) {
            $ro->Id = $roId;
            $ro->EntityId = $this->userCompanyId;
            $ro->ProjectId = $this->GetPostValue("ProjectId");
            $ro->Date = strtotime($this->GetPostValue("RoDate"));
            $ro->ExpectedDate = strtotime($this->GetPostValue("ExpectedDate"));
            $ro->SupplierId = $this->GetPostValue("SupplierId");
            $ro->PaymentTerms = $this->GetPostValue("PaymentTerms");
            $ro->Note = $this->GetPostValue("Note");
            $ro->DocumentNo = $this->GetPostValue("RoNo");
            $ro->IsVat = $this->GetPostValue("IsVat");
            $ro->IsIncludeVat = $this->GetPostValue("IsIncVat");
            $ro->CreatedById = $this->userUid;
            if ($ro->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $ro->DocumentNo = $docCounter->AutoDocNoRo($ro->EntityId, $ro->Date, 1);
                $rs = $ro->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$ro->Id,$ro->DocumentNo);
                }else{
                    printf("ER|A|%d",$ro->Id);
                }
            }else{
                $rs = $ro->Update($ro->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$ro->Id,$ro->DocumentNo);
                }else{
                    printf("ER|U|%d",$ro->Id);
                }
            }
        }else{
            printf("ER|X|%d",$roId);
        }
    }

    public function add_detail($roId = null) {
        $rst = null;
        $ro = new Ro($roId);
        $rodetail = new RoDetail();
        $rodetail->RoId = $roId;
        $ro_item_exist = false;
        if (count($this->postData) > 0) {
            $rodetail->ItemId = $this->GetPostValue("aItemId");
            $rodetail->Qty = $this->GetPostValue("aRoQty");
            $rodetail->ItemDescription = '-';
            $rodetail->RrDetailId = $this->GetPostValue("aRrDetailId");
            $rodetail->UomCd = $this->GetPostValue("aUomCd");
            $rodetail->Price = $this->GetPostValue("aPrice");
            // item baru simpan
            $rs = $rodetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$rodetail->Id);
                //creat mr link
                if ($rodetail->RrId > 0) {
                    //update mr qty
                    $this->connector->CommandText = "Update ic_rr_detail AS a Set a.po_qty = a.po_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $rodetail->Qty);
                    $this->connector->AddParameter("?id", $rodetail->RrDetailId);
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
        $rodetail = new RoDetail();
        $rodetail = $rodetail->LoadById($id);
        if ($rodetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $pri = $rodetail->RrId;
        $roi = $rodetail->RoId;
        $qty = $rodetail->Qty;
        if ($rodetail->Delete($id) == 1) {
            if ($pri > 0) {
                //delete link to mr
                $this->connector->CommandText = "Delete From ic_link_pr_po Where pr_id = ?pr And ro_id = ?po";
                $this->connector->AddParameter("?pr", $pri);
                $this->connector->AddParameter("?po", $roi);
                $rs = $this->connector->ExecuteNonQuery();
                //update mr qty
                $this->connector->CommandText = "Update ic_rr_detail AS a Set a.ro_qty = a.ro_qty - ?qty Where a.id = ?id";
                $this->connector->AddParameter("?qty", $qty);
                $this->connector->AddParameter("?id", $pri);
                $rs = $this->connector->ExecuteNonQuery();
            }
            printf("Data Detail RO ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail RO ID: %d gagal dihapus!",$id);
        }
    }

    public function getjson_rritems($projectId = 0, $supplierId = 0){
        $filter = isset($_ROST['q']) ? strval($_ROST['q']) : '';
        $rritems = new Ro();
        $rritems = $rritems->GetJSonUnfinishedRrItems($projectId,$supplierId,$filter);
        echo json_encode($rritems);
    }

    public function view($roId = 0) {
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $ro = new Ro();
        if ($roId > 0 ) {
            $ro = $ro->LoadById($roId);
            if ($ro == null) {
                $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.ro");
            }
        }else{
            $ro->Date = date('d-m-Y');
        }

        // load details
        $ro->LoadDetails();
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
        $this->Set("ro", $ro);
    }
}


// End of File: ro_controller.php
