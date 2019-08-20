<?php

class PoController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "purchase/po.php");
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
        $settings["columns"][] = array("name" => "a.doc_no", "display" => "PO Number", "width" => 120);
		$settings["columns"][] = array("name" => "c.creditor_name", "display" => "Supplier", "width" => 200);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.po_date, '%d %M %Y')", "display" => "Date", "width" => 80);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.expected_date, '%d %M %Y')", "display" => "Required Date", "width" => 80);
		$settings["columns"][] = array("name" => "format(g.subtotal,0)", "display" => "Amount", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "d.short_desc", "display" => "Status", "width" => 100);
		//$settings["columns"][] = array("name" => "CASE WHEN a.is_vat = 1 THEN 'Iya' ELSE 'Tidak' END", "display" => "PPN ?", "width" => 60);
		//$settings["columns"][] = array("name" => "CASE WHEN a.is_vat <> 1 THEN '-' WHEN a.is_inc_vat = 1 THEN 'Iya' ELSE 'Tidak' END", "display" => "Included ?", "width" => 60);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "Nomor PO");
		$settings["filters"][] = array("name" => "c.creditor_name", "display" => "Supplier");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Purchase Order";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("po", "add_master", "purchase")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "purchase.po/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("po", "view", "purchase")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "purchase.po/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "purchase.po/overview", "Class" => "bt_report", "ReqId" => 0);
			}
            if ($acl->CheckUserAccess("po", "doc_print", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "XLS Print", "Url" => "purchase.po/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
                $settings["actions"][] = array("Text" => "PDF Print", "Url" => "purchase.po/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }

			if ($acl->CheckUserAccess("po", "edit_master", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "Edit", "Url" => "purchase.po/add/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen PO terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "Apakah anda mau merubah data Dokumen PO yang dipilih ?");
			}
			/*
			if ($acl->CheckUserAccess("po", "split", "purchase")) {
				$settings["actions"][] = array("Text" => "Split", "Url" => "purchase.po/split/%s", "Class" => "bt_split", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen PO terlebih dahulu sebelum melakukan proses split dokumen.\nPERHATIAN: Pilih tepat 1 dokumen",
											   "Confirm" => "");
			}
			*/
			if ($acl->CheckUserAccess("po", "delete", "purchase")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "purchase.po/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen PO terlebih dahulu sebelum melakukan proses delete !\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
											   "Confirm" => "Apakah anda mau menghapus Dokumen PO yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("po", "batch_approve", "purchase")) {
				$settings["actions"][] = array("Text" => "Approval", "Url" => "purchase.po/batch_approve/", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen PO !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua dokumen PO yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("po", "batch_disapprove", "purchase")) {
				$settings["actions"][] = array("Text" => "Batal Approval", "Url" => "purchase.po/batch_disapprove/", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen PO !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen PO yang dipilih ?\nProses Dis-Approve Akan membuat status dokumen menjadi DRAFT.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Kill Session
			$this->persistence->DestroyState("purchase.po.po");
		} else {
			$settings["from"] =
"ic_po_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	LEFT JOIN ap_creditor_master AS c ON a.supplier_id = c.id
	JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'po_status'
	JOIN cm_project AS e ON a.project_id = e.id
	JOIN (Select f.po_master_id, sum(f.qty * f.price) AS subtotal From ic_po_detail AS f Group By f.po_master_id) AS g On a.id = g.po_master_id
	";

			$settings["where"] = "a.is_deleted = 0 AND b.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen PO terlebih dahulu.");
			redirect_url("purchase.po");
			return;
		}

		$po = new Po();
		$po = $po->LoadById($id);
		if ($po == null || $po->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen PO yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("purchase.po");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($po->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen PO yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("purchase.po");
				return;
			}
		}
		if ($po->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen PO yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("purchase.po/view/" . $po->Id);
			return;
		}

		//cek detail
        $details = $po->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "PO No: ". $po->DocumentNo." Harap hapus dulu Detail Itemnya");
            redirect_url("purchase.po");
            return;
        }

		// Everything is green
		// ToDo: Kalau Referensi PR nya bukan proses PO bagaimana ?
		$this->connector->BeginTransaction();
		// Step 1: OK Hapus referensi PR jika ada...
		//	NOTE : Reset status PR jika dan hanya jika PR tersebut sudah tidak direferensikan lagi oleh PO yang lain
		$this->connector->CommandText =
"UPDATE ic_pr_master SET
	status = 2
	, updateby_id = ?user
	, update_time = NOW()
WHERE id IN (
	-- LOGIC: cari semua PR id (self join berdasarkan pr_id) yang mana tidak boleh sama dengan PO yang dihapus dan statusnnya belum di delete
	--        Jika ketemu pasangannya bearti masih ada referensinya. CARI YANG REFERENSINYA NULL
	SELECT a.pr_id -- AS del_pr_id, a.po_id AS del_po_id, a.is_deleted AS del_is_deleted, b.*
	FROM ic_link_pr_po AS a
		LEFT JOIN ic_link_pr_po AS b ON a.pr_id = b.pr_id AND b.po_id <> ?poId AND b.is_deleted = 0
	WHERE a.po_id = ?poId AND b.pr_id IS NULL
)";
		$this->connector->AddParameter("?user", AclManager::GetInstance()->GetCurrentUser()->Id);
		$this->connector->AddParameter("?poId", $po->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PO: %s ! Gagal Hapus Referensi PR<br /> Harap hubungi system administrator.<br />Error: %s", $po->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.po");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_pr_po SET is_deleted = 1 WHERE po_id = ?poId";
		$this->connector->AddParameter("?poId", $po->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PO: %s ! Gagal Hapus Link PR-PO<br /> Harap hubungi system administrator.<br />Error: %s", $po->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.po");
		}

		// Step 3: Hapus Link ke NPKP jika statusnya masih draft
		require_once(MODEL . "accounting/cash_request.php");
		$cashRequest = new CashRequest();
		$cashDetail = new CashRequestDetail();
		$cashDetail = $cashDetail->LoadByPoId($po->Id);
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

		// Step 4: Hapus dokumen PO
		$po->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($po->Delete($po->Id) == 1) {
			$this->connector->CommitTransaction();
			$message = sprintf("Dokumen PO: %s sudah berhasil dihapus.", $po->DocumentNo);
			if ($cashDetail != null) {
				if ($flagDelete) {
					$message .= sprintf("<br />NPKP: %s sudah diupdate (detail sudah dihapus)", $cashRequest->DocumentNo);
				} else {
					$this->persistence->SaveState("error", sprintf("Maaf NPKP: %s sudah diproses. Link PO pada NPKP tidak dihapus !", $cashRequest->DocumentNo));
				}
			}
			$this->persistence->SaveState("info", $message);
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PO: %s ! Harap hubungi system administrator.<br />Error: %s", $po->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("purchase.po");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("purchase.po");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$po = new Po();
			$po = $po->LoadById($id);

			if ($po->StatusCode != 1) {
				$errors[] = sprintf("Dokumen PO: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $po->DocumentNo, $po->GetStatus());
				continue;
			}
			if ($po->EntityId != $this->userCompanyId) {
                // Trying to access other Company data ! Bypass it..
                continue;
            }

			$po->ApprovedById = $userId;
			$rs = $po->Approve($po->Id);
			if ($rs == 1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen PO: %s sudah berhasil di approve", $po->DocumentNo);
			} else {
				$errors[] =  sprintf("Gagal Approve Dokumen PO: %s. Message: %s", $po->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("purchase.po");
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen PO yang akan di batalkan !");
			redirect_url("purchase.po");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$po = new Po();
			$po = $po->LoadById($id);

			if ($po->StatusCode != 3) {
				$errors[] = sprintf("Dokumen PO: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $po->DocumentNo, $po->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($po->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$po->UpdatedById = $userId;
			$rs = $po->DisApprove($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen PO: %s sudah berhasil di dibatalkan (disapprove)", $po->DocumentNo);
			} else {
				$errors[] =  sprintf("Gagal Membatalkan / Disapprove Dokumen PO: %s. Message: %s", $po->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url("purchase.po");
	}

	/**
	 * Untuk memilih dokumen PR mana yang akan dijadikan PO
	 */
	public function find_pr() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.project_name", "display" => "Project", "width" => 100);
        $settings["columns"][] = array("name" => "a.dept_name", "display" => "Dept", "width" => 150);
        $settings["columns"][] = array("name" => "a.doc_no", "display" => "PR Number", "width" => 120);
        $settings["columns"][] = array("name" => "DATE_FORMAT(a.pr_date, '%d %M %Y')", "display" => "PR Date", "width" => 100, "sortable" => false);
        $settings["columns"][] = array("name" => "c.short_desc", "display" => "Req Level", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.qty_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Qty Status", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.prc_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Price Status", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.sup_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Vendor Status", "width" => 70);
        $settings["columns"][] = array("name" => "b.short_desc", "display" => "Progress Status", "width" => 100);
        $settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d %M %Y')", "display" => "Last Update", "width" => 100, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No Dokumen PR");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "PR to PO Process";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("pr", "view", "purchase")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "purchase.pr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("po", "process_pr", "purchase")) {
				$settings["actions"][] = array("Text" => "Proses Menjadi PO", "Url" => "purchase.po/process_pr", "Class" => "bt_process", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen PR !",
											   "Confirm" => "Apakah anda mau membuat PO berdasarkan PR yang sudah dipilih ?\n\nPERHATIAN: PO yang dibuat bisa > 1 jika ditemukan supplier yang berbeda.");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("po", "process_all_pr", "purchase")) {
				$settings["actions"][] = array("Text" => "Proses Semua PR menjadi PO", "Url" => "purchase.po/process_all_pr", "Class" => "bt_process", "ReqId" => 0,
											   "Confirm" => "Apakah anda mau memproses semua PR menjadi PO ?\n\nPERHATIAN: proses ini dapat membuat PO > 1 jika ditemukan supplier yang berbeda.");
			}

			$settings["def_filter"] = 1;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;
		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
            $settings["from"] = "vw_ic_pr_master AS a JOIN sys_status_code AS b ON a.status = b.code AND b.key = 'pr_status' LEFT JOIN sys_status_code AS c ON a.req_level = c.code AND c.key = 'mr_req_level'";
			$settings["where"] = "a.qty_status > 0 AND a.prc_status > 0 AND a.sup_status > 0 AND a.is_deleted = 0 AND a.status = 3 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function process_pr() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen PR yang akan diproses menjadi PO !");
			redirect_url("purchase.po/find_pr");
			return;
		}

		$infos = array();
		$errors = array();

		// OK everything is green
		$this->GeneratePo($ids, $errors, $infos);
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");

		redirect_url("purchase.po");
	}

	/**
	 * Proses utama yang membuat PO
	 *
	 * @param array $ids => ID dokumen PR (automatically filtered by current user Company)
	 * @param $errors	[OUT]
	 * @param $infos	[OUT]
	 * @return bool
	 */
	private function GeneratePo(array $ids = null, &$errors, &$infos) {
	// Step 1:	Ambil semua barang dari PR berdasarkan ID yang dikirim
	//			NOTE: supplier dan harga sudah auto select dari selected_supplier, dan sudah di sort berdasarkan supplier
		$query =
"SELECT
	a.id AS id_mst
	, a.doc_no
	, a.pr_date
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
FROM ic_pr_master AS a
	JOIN ic_pr_detail AS b ON a.id = b.pr_master_id
	-- JOIN ic_item_master AS c ON b.item_id = c.id
WHERE b.supplier_id_1 > 0 AND b.price_1 > 0 And b.qty > 0 AND a.is_deleted = 0 AND a.status = 3 AND a.entity_id = ?sbu %s
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
			$infos[] = "Proses PO otomatis dibatalkan";
			return false;
		}
		if ($rs->GetNumRows() == 0) {
			if ($ids == null) {
				$this->persistence->SaveState("error", "Maaf tidak ada PR yang diproses ! Pastikan Data PR sudah lengkap dan diapprove agar dibuatkan PO otomatis.");
			} else {
				$this->persistence->SaveState("error", "Data barang PR tidak ditemukan ! Pastikan dokumen PR yang dipilih sudah benar");
			}
			$infos[] = "Proses PO otomatis dibatalkan";

			return false;
		}

		// Step 2:	List barang sudah ada dan sudah berurut... mari kita bikin PO nya...
		//			NOTE: Berbeda dengan proses buat PR kalau ini kita sulit fix pasti jadi 1 karena supplier bisa mana aja...
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		$poevSupplier = null;
		$poevDate = null;
		$poevDocNo = null;
		$po = null;
		$allPo = array();

		while ($row = $rs->FetchAssoc()) {
			$flagNewPo = false;

			if (($poevSupplier != $row["supplier_id"]) || ($poevDate != $row["pr_date"])) {
				if ($po != null) {
					// OK entry data PO sebelumnya
					$allPo[] = $po;
				}

				// Sudah beda supplier artinya beda PO
				$poevSupplier = $row["supplier_id"];
                $poevDate = $row["pr_date"];

				$flagNewPo = true;
				$po = new Po();
				$po->EntityId = $this->userCompanyId;
				$po->CreatedById = $userId;
				$po->Date = strtotime($row["pr_date"]);
				$po->SupplierId = $poevSupplier;
				$po->IsVat = 1;
				$po->IsIncludeVat = false;
				$po->ExpectedDate = $po->Date + 604800;
				$po->PaymentTerms = 30;
				$po->Note = "PR:";
				$po->StatusCode = 1;
				$po->ProjectId = $row["project_id"];
			}

			// OK looping data barangnya...
			$itemId = $row["item_id"];
			if ($poevDocNo != $row["doc_no"] || $flagNewPo) {
				$poevDocNo = $row["doc_no"];
				$po->Note .= "\n * " . $poevDocNo;
				$po->PrIds[] = $row["id_mst"];
				$po->PrCodes[] = $row["doc_no"];
			}
			if (array_key_exists($itemId, $po->Details)) {
				// Hwee barang yang sama uda ada di PO ? Kemungkinan terbesar adalah beda dept tetapi request barang yang sama
				// NOTE:
				//	* PoDetailId tidak di update
				//	* Jumlah ditambah dan harga pakai yang minimum
				//	* ItemDescription akan di append
				$detail = $po->Details[$itemId];

				$detail->Qty += $row["qty"];
				$detail->Price = min($detail->Price, $row["price"]);
				if (strlen($detail->ItemDescription) > 0) {
					$detail->ItemDescription .= ", " . $row["item_description"];
				} else {
					$detail->ItemDescription = $row["item_description"];
				}
			} else {
				$detail = new PoDetail();

				$detail->PrDetailId = $row["id_dtl"];
				$detail->ItemId = $itemId;
				$detail->Qty = $row["qty"];
				$detail->UomCd = $row["uom_cd"];
				$detail->Price = $row["price"];
				$detail->ItemDescription = $row["item_description"];

				$po->Details[$itemId] = $detail;
			}
		}

		// Seperti biasa PO yang terakhir terlupakan...
		$allPo[] = $po;

		// Karena kita mau bikin NPKP untuk semua PO maka prosesnya kita ganti sedikit disini....
		// Transactionnya harus di level global ga per level PO lagi sekarang
		$this->connector->BeginTransaction();
		$poCodes = array();

		// Step 03: Entry semua data PO
		foreach ($allPo as $po) {
			if ($this->doAdd($po)) {
				$poCodes[] = $po->DocumentNo;
				$infos[] = "Berhasil Membuat PO: " . $po->DocumentNo;
			} else {
				$infos[] = "Proses pembuatan PO dibatalkan karena ada error !";
				if ($this->connector->GetHasError()) {
					$errors[] = "Database error while processing auto PO ! Message: " . $this->connector->GetErrorMessage();
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
		$crcategory = $crcategory->LoadByProjectId($po->ProjectId);
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
		$cashRequest->Objective = "Untuk PO: " . implode(", ", $poCodes);
		$cashRequest->Note = "Ini merupakan NPKP otomatis yang dibuat pada saat proses PR -> PO. Untuk Nomor PO harap lihat ke detailnya dikarenakan yang ditujuan bisa berbeda jika ada PO yang dibatalkan.";
		$cashRequest->EtaDate = $cashRequest->Date + 604800;	// Margin 1 minggu
		$cashRequest->CategoryId = $crCategoryId;

		foreach ($allPo as $po) {
			$cashDetail = new CashRequestDetail();
			$cashDetail->Note = "Untuk PO: " . $po->DocumentNo;
			$cashDetail->Amount = $po->GetTotalAmount();
			$cashDetail->PoId = $po->Id;

			$cashRequest->Details[] = $cashDetail;
		}

		// Step 05: Entry NPKP
		if ($cashRequest->DocumentNo == null) {
			$infos[] = "Proses pembuatan PO dibatalkan karena ada error !";
			$errors[] = "Gagal membuat NPKP untuk PO ! Tanggal hari ini sudah ter-locked oleh dokumen NPKP.";
			$this->connector->RollbackTransaction();
			return false;
		}

		$rs = $cashRequest->Insert();
		if ($rs != 1) {
			$infos[] = "Proses pembuatan PO dibatalkan karena ada error !";
			$errors[] = "Gagal entry master NPKP ! Message: " . $this->connector->GetErrorMessage();
			$this->connector->RollbackTransaction();
			return false;
		}

		foreach ($cashRequest->Details as $cashDetail) {
			$cashDetail->CashRequestId = $cashRequest->Id;
			$rs = $cashDetail->Insert();
			if ($rs != 1) {
				$infos[] = "Proses pembuatan PO dibatalkan karena ada error !";
				$errors[] = "Gagal entry detail NPKP ! Message: " . $this->connector->GetErrorMessage();
				$this->connector->RollbackTransaction();
				return false;
			}
		}

		// HOOORAAAAAYYYYYYYY SUKSES :)
		$infos[] = "NPKP Untuk PO yang telah dibuat: " . $cashRequest->DocumentNo;
		$this->connector->CommitTransaction();
		return true;
	}


    /**
     * Proses entry PO ke DBase. Pastikan DBase transaction sudah aktif ! Akan ambil data running number
     *
     * @param Po $po
     * @return bool
     */
    private function doAdd(Po $po) {
        require_once(MODEL . "common/doc_counter.php");
        $docCounter = new DocCounter();
        $po->DocumentNo = $docCounter->AutoDocNoPo($po->EntityId, $po->Date, 1);
        if ($po->DocumentNo == null) {
            $this->Set("error", "Maaf anda tidak dapat membuat dokumen PO pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
            return false;
        }

        $rs = $po->Insert();
        if ($rs != 1) {
            if ($this->connector->IsDuplicateError()) {
                $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
            } else {
                $this->Set("error", "Maaf error saat simpan master PO. Message: " . $this->connector->GetErrorMessage());
            }
            return false;
        }

        foreach ($po->Details as $idx => $detail) {
            $detail->PoId = $po->Id;
            $rs = $detail->Insert();
            if ($rs == 1) {
                $this->connector->CommandText = "Update ic_pr_detail AS a Set a.po_qty = a.po_qty + ?qty Where a.id = ?prd_id And a.item_id = ?item_id";
                $this->connector->AddParameter("?qty", $detail->Qty);
                $this->connector->AddParameter("?prd_id", $detail->PrDetailId);
                $this->connector->AddParameter("?item_id", $detail->ItemId);
                $rs = $this->connector->ExecuteNonQuery();
                if ($rs == -1) {
                    $this->Set("error", "Gagal update PO Qty PR Detail ! Error: " . $this->connector->GetErrorMessage());
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
                $this->Set("error", "Maaf error saat simpan detail PO No. $no. Message: " . $this->connector->GetErrorMessage());
            }
            return false;
        }

        // Linking ke PR
        if (count($po->PrIds) > 0) {
            require_once(MODEL . "purchase/pr.php");

            $pr = new Pr();
            foreach ($po->PrIds as $id) {
                $pr->StatusCode = 4;
                $pr->UpdatedById = $po->CreatedById;
                $rs = $pr->SetStatus($id);
                if ($rs == -1) {
                    $this->Set("error", "Gagal set status PR ! Error: " . $this->connector->GetErrorMessage());
                    return false;	// FAILURE
                }

                $this->connector->CommandText = "INSERT INTO ic_link_pr_po(pr_id, po_id) VALUES (?pr, ?po)";
                $this->connector->AddParameter("?pr", $id);
                $this->connector->AddParameter("?po", $po->Id);
                $rs = $this->connector->ExecuteNonQuery();
                if ($rs == -1) {
                    $this->Set("error", "Gagal link PR-PO ! Error: " . $this->connector->GetErrorMessage());
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
		$this->GeneratePo(null, $errors, $infos);
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");

		redirect_url("purchase.po");
	}

	public function split($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Dokumen PO terlebih dahulu !");
			redirect_url("purchase.po");
			return;
		}

		$po = new Po();
		$po = $po->LoadById($id);

		if (count($this->postData) > 0) {
			$newSupplierId = $this->GetPostValue("SupplierId");
			$ids = $this->GetPostValue("id", array());

			if ($this->ValidateSplit($po, $newSupplierId, $ids)) {
				$this->connector->BeginTransaction();
				if ($this->doSplit($po, $newSupplierId, $ids)) {
					$this->connector->CommitTransaction();
					redirect_url("purchase.po");
				} else {
					$this->connector->RollbackTransaction();
				}
			}
		} else {
			if ($po == null || $po->IsDeleted) {
				$this->persistence->SaveState("error", "Dokumen PO yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url("purchase.po");
				return;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($po->EntityId != $this->userCompanyId) {
					// WOW coba akses data lintas Company ? Simulate not found !
					$this->persistence->SaveState("error", "Dokumen Item Order yang diminta tidak dapat ditemukan / sudah dihapus !");
					redirect_url("purchase.po");
					return;
				}
			}
			// ToDo: Apakah perlu checking hanya PO draft ?
			// Saat ini saya rasa tidak perlu checking PO harus draft karena kasus ini terjadi ketika PO sudah dibawa ke supplier
			// Sudah dibawa bearti sudah di approve... dan semua data akan di copas dari PO asli

			$newSupplierId = null;
		}

		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");
		// Load PO Details kalau PO memang bisa dilihat
		$po->LoadDetails();
		$po->LoadAssociatedPo();

		$company = new Company();
		$company = $company->FindById($po->EntityId);
		$supplier = new Creditor();
		$supplier = $supplier->FindById($po->SupplierId);

		$this->Set("company", $company);
		$this->Set("supplier", $supplier);
		$this->Set("po", $po);
		$this->Set("suppliers", $supplier->LoadSuppliersByEntity($po->EntityId));
		$this->Set("newSupplierId", $newSupplierId);
        $project = new Project($po->ProjectId);
        $this->Set("project", $project);
	}

	private function ValidateSplit(Po $po, $newSupplierId, array $ids) {
		if ($newSupplierId == null) {
			$this->Set("error", "Mohon memilih supplier baru terlebih dahulu");
			return false;
		}
		if ($po->SupplierId == $newSupplierId) {
			$this->Set("error", "Supplier lama dan supplier baru sama. Mohon pilih supplier yang berbeda untuk PO yang baru");
			return false;
		}
		if (count($ids) == 0) {
			$this->Set("error", "Mohon memilih detail PO yang akan di split terlebih dahulu.");
			return false;
		}

		// Sudah tahap POST artinya sudah valid datanya
		$po->LoadDetails();

		$buff = array();
		foreach ($po->Details as $detail) {
			$buff[] = $detail->Id;
		}
		foreach ($ids as $id) {
			if (!in_array($id, $buff)) {
				// AJAIB.... bisa kirim ID yang tidak ada pada detail PO
				$this->Set("error", "Failed in integrity check ! Mohon ulangi proses split PO dari awal");
				return false;
			}
		}

		return true;
	}

	private function doSplit(Po $po, $newSupplierId, array $ids) {
		$newPo = new Po();
		$newPo->EntityId = $po->EntityId;
		$newPo->Date = time();
		$newPo->SupplierId = $newSupplierId;
		$newPo->IsVat = $po->IsVat;
		$newPo->IsIncludeVat = $po->IsIncludeVat;
		$newPo->ExpectedDate = $newPo->Date + 604800;
		$newPo->PaymentTerms = $po->PaymentTerms;
		$newPo->Note = "PO baru hasil split dari PO: " . $po->DocumentNo . "\nKeterangan PO Lama:\n" . $po->Note;
		$newPo->StatusCode = 1;
		$newPo->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		$newPo->ParentPoId = $po->Id;

		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$newPo->DocumentNo = $docCounter->AutoDocNoPo($newPo->EntityId, $newPo->Date, 1);
		if ($newPo->DocumentNo == null) {
			$this->Set("error", "Maaf proses split PO tidak dapat dilakukan. Tanggal hari ini sudah terlocked oleh system. Hubungi system admin.");
			return false;
		}

		// OK Cara Curang is back in action.... (Migrasi data secara langsung....)
		$rs = $newPo->Insert();
		if ($rs != 1) {
			$this->Set("error", "Proses split PO gagal karena dokumen PO terbaru tidak berhasil disimpan. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		$counter = 0;
		foreach ($po->Details as $detail) {
			if (!in_array($detail->Id, $ids)) {
				continue;
			}

			// Mari kita migrasikan
			$counter++;
			$detail->PoId = $newPo->Id;
			$rs = $detail->Update($detail->Id);
			if ($rs != 1) {
				$this->Set("error", "Gagal migrasi detail PO ke yang baru. Detail ID: " . $detail->Id . ". Message: " . $this->connector->GetErrorMessage());
				return false;
			}
		}

		// Last integrity check
		if ($counter != count($ids)) {
			$this->Set("error", sprintf("Integrity check failed ! Jumlah migrasi != jumlah yang dipilih (%d != %d)", $counter, count($ids)));
			return false;
		}

		// Bikin Link PR - PO
		$this->connector->CommandText =
"INSERT INTO ic_link_pr_po
SELECT DISTINCT b.pr_master_id, ?newPoId
-- SELECT a.id, a.pr_detail_id, b.id, b.pr_master_id
FROM ic_po_detail AS a
	JOIN ic_pr_detail AS b ON a.pr_detail_id = b.id
WHERE a.po_master_id = ?newPoId";
		$this->connector->AddParameter("?newPoId", $newPo->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal migrasi link PR - PO. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// OK remove redundant link PR - PO pada dokumen yang lama
		$this->connector->CommandText =
"UPDATE ic_link_pr_po SET is_deleted = 1 WHERE po_id = ?oldPoId AND pr_id NOT IN (
	SELECT DISTINCT b.pr_master_id
	-- SELECT a.id, a.pr_detail_id, b.id, b.pr_master_id
	FROM ic_po_detail AS a
		JOIN ic_pr_detail AS b ON a.pr_detail_id = b.id
	WHERE a.po_master_id = ?oldPoId
)";
		$this->connector->AddParameter("?oldPoId", $po->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal hapus link PR - PO pada dokumen PO yang lama. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// OK logic na sih copas dari split PR jadi... semoga we ga salah wakakakak
		$this->persistence->SaveState("info", sprintf("PO: %s telah berhasil di split menjadi PO baru dengan no: %s", $po->DocumentNo, $newPo->DocumentNo));
		return true;
	}

    //proses cetak form PO
    public function doc_print($output){

        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
            redirect_url("purchase.po");
            return;
        }

        $report = array();

        foreach ($ids as $id) {

            $po = new Po();
            $po = $po->LoadById($id);
            $po->LoadDetails(true);
            $po->LoadSupplier();
            $po->LoadUsers();
            $po->LoadCompany();
            $po->LoadProject();

            $report[] = $po;
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

        $po = new Po();

        if (count($this->getData) > 0) {
            $supplierId = $this->GetGetValue("supplier");
            $status = $this->GetGetValue("status");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.entity_cd AS entity, c.creditor_name AS supplier, d.short_desc AS status_name
                                            FROM ic_po_master AS a
                                            JOIN cm_company AS b ON a.entity_id = b.entity_id
                                            JOIN ap_creditor_master AS c ON a.supplier_id = c.id
                                            JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'po_status'
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

            $this->connector->CommandText .= " AND a.po_date >= ?start
                                               AND a.po_date <= ?end
                                               ORDER BY a.po_date ASC";
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
        $this->Set("po_status", $syscode->LoadPoStatus());

        $temp = $syscode->FindBy("po_status", $status);
        $statusName = $temp != null ? $temp->ShortDesc : "SEMUA STATUS";
        $this->Set("statusName", $statusName);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
        $project = new Project($po->ProjectId);
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
                                            FROM ic_po_detail AS a
                                            JOIN ic_po_master AS b ON a.po_master_id = b.id
                                            JOIN ic_item_master AS c ON a.item_id = c.id
                                            WHERE b.is_deleted = 0
                                            AND b.status = ?status
                                            AND b.po_date >= ?start
                                            AND b.po_date <= ?end
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
        $this->Set("po_status", $syscode->LoadPoStatus());
        $this->Set("statusName", $syscode->FindBy("po_status", $status));

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
    }

    public function add($poId = 0) {
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $po = new Po();
        if ($poId > 0 ) {
            $po = $po->LoadById($poId);
            if ($po == null) {
                $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.po");
            }
            if ($po->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $po->DocumentNo,$po->GetStatus()));
                redirect_url("purchase.po");
            }
        }else{
            $po->Date = date('d-m-Y');
        }

        // load details
        $po->LoadDetails();
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
        $this->Set("po", $po);
    }

    public function proses_master($poId = 0) {
        $po = new Po();
        if (count($this->postData) > 0) {
            $po->Id = $poId;
            $po->EntityId = $this->userCompanyId;
            $po->ProjectId = $this->GetPostValue("ProjectId");
            $po->Date = strtotime($this->GetPostValue("PoDate"));
            $po->ExpectedDate = strtotime($this->GetPostValue("ExpectedDate"));
            $po->SupplierId = $this->GetPostValue("SupplierId");
            $po->PaymentTerms = $this->GetPostValue("PaymentTerms");
            $po->Note = $this->GetPostValue("Note");
            $po->DocumentNo = $this->GetPostValue("PoNo");
            $po->IsVat = $this->GetPostValue("IsVat");
            $po->IsIncludeVat = $this->GetPostValue("IsIncVat");
            $po->CreatedById = $this->userUid;
            if ($po->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $po->DocumentNo = $docCounter->AutoDocNoPo($po->EntityId, $po->Date, 1);
                $rs = $po->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$po->Id,$po->DocumentNo);
                }else{
                    printf("ER|A|%d",$po->Id);
                }
            }else{
                $rs = $po->Update($po->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$po->Id,$po->DocumentNo);
                }else{
                    printf("ER|U|%d",$po->Id);
                }
            }
        }else{
            printf("ER|X|%d",$poId);
        }
    }

    public function add_detail($poId = null) {
        $rst = null;
        $po = new Po($poId);
        $podetail = new PoDetail();
        $podetail->PoId = $poId;
        $po_item_exist = false;
        if (count($this->postData) > 0) {
            $podetail->ItemId = $this->GetPostValue("aItemId");
            $podetail->Qty = $this->GetPostValue("aPoQty");
            $podetail->ItemDescription = '-';
            $podetail->PrDetailId = $this->GetPostValue("aPrDetailId");
            $podetail->UomCd = $this->GetPostValue("aUomCd");
            $podetail->Price = $this->GetPostValue("aPrice");
            // item baru simpan
            $rs = $podetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$podetail->Id);
                //creat mr link
                if ($podetail->PrDetailId > 0) {
                    //create link to mr
                    $this->connector->CommandText = "INSERT INTO ic_link_pr_po(pr_id, po_id) VALUES (?pr, ?po)";
                    $this->connector->AddParameter("?pr", $podetail->PrDetailId);
                    $this->connector->AddParameter("?po", $poId);
                    $rs = $this->connector->ExecuteNonQuery();
                    //update mr qty
                    $this->connector->CommandText = "Update ic_pr_detail AS a Set a.po_qty = a.po_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $podetail->Qty);
                    $this->connector->AddParameter("?id", $podetail->PrDetailId);
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
        $podetail = new PoDetail();
        $podetail = $podetail->LoadById($dId);
        $xQty = 0;
        if (count($this->postData) > 0) {
            $podetail->PoId = $this->GetPostValue("aPoId");
            $podetail->ItemId = $this->GetPostValue("aItemId");
            $podetail->Qty = $this->GetPostValue("aPoQty");
            $podetail->ItemDescription = '-';
            $podetail->PrDetailId = $this->GetPostValue("aPrDetailId");
            $podetail->UomCd = $this->GetPostValue("aUomCd");
            $podetail->Price = $this->GetPostValue("aPrice");
            $xQty = $this->GetPostValue("xPoQty");
            // item baru simpan
            $rs = $podetail->Update($dId);
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$podetail->Id);
                //creat mr link
                if ($podetail->PrDetailId > 0) {
                    //update pr qty
                    $this->connector->CommandText = "Update ic_pr_detail AS a Set a.po_qty = a.po_qty - ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $xQty);
                    $this->connector->AddParameter("?id", $podetail->PrDetailId);
                    $rs = $this->connector->ExecuteNonQuery();
                    $this->connector->CommandText = "Update ic_pr_detail AS a Set a.po_qty = a.po_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $podetail->Qty);
                    $this->connector->AddParameter("?id", $podetail->PrDetailId);
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
        $podetail = new PoDetail();
        $podetail = $podetail->LoadById($id);
        if ($podetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $pri = $podetail->PrDetailId;
        $poi = $podetail->PoId;
        $qty = $podetail->Qty;
        if ($podetail->Delete($id) == 1) {
            if ($pri > 0) {
                //delete link to mr
                $this->connector->CommandText = "Delete From ic_link_pr_po Where pr_id = ?pr And po_id = ?po";
                $this->connector->AddParameter("?pr", $pri);
                $this->connector->AddParameter("?po", $poi);
                $rs = $this->connector->ExecuteNonQuery();
                //update mr qty
                $this->connector->CommandText = "Update ic_pr_detail AS a Set a.po_qty = a.po_qty - ?qty Where a.id = ?id";
                $this->connector->AddParameter("?qty", $qty);
                $this->connector->AddParameter("?id", $pri);
                $rs = $this->connector->ExecuteNonQuery();
            }
            printf("Data Detail PO ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail PO ID: %d gagal dihapus!",$id);
        }
    }

    public function getjson_pritems($projectId = 0, $supplierId = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $pritems = new Po();
        $pritems = $pritems->GetJSonUnfinishedPrItems($projectId,$supplierId,$filter);
        echo json_encode($pritems);
    }

    public function view($poId = 0) {
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $po = new Po();
        if ($poId > 0 ) {
            $po = $po->LoadById($poId);
            if ($po == null) {
                $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.po");
            }
        }else{
            $po->Date = date('d-m-Y');
        }

        // load details
        $po->LoadDetails();
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
        $this->Set("po", $po);
    }
}


// End of File: po_controller.php
