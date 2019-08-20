<?php

class RrController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "purchase/rr.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.project_name", "display" => "Project", "width" => 100);
        $settings["columns"][] = array("name" => "a.dept_name", "display" => "Dept", "width" => 150);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "R/R Number", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.rr_date, '%d %M %Y')", "display" => "R/R Date", "width" => 100, "sortable" => false);
        $settings["columns"][] = array("name" => "c.short_desc", "display" => "Req Level", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.qty_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Qty Status", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.prc_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Price Status", "width" => 70);
        $settings["columns"][] = array("name" => "If (a.sup_status > 0 ,'COMPLETE','INCOMPLETE')", "display" => "Vendor Status", "width" => 70);
		$settings["columns"][] = array("name" => "b.short_desc", "display" => "Progress Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d %M %Y')", "display" => "Last Update", "width" => 100, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "RR Number");
		$settings["filters"][] = array("name" => "b.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Repair Request List";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("rr", "view", "purchase")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "purchase.rr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "purchase.rr/overview", "Class" => "bt_report", "ReqId" => 0);
			}

			if ($acl->CheckUserAccess("rr", "doc_print", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "XLS Print", "Url" => "purchase.rr/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
				$settings["actions"][] = array("Text" => "PDF Print", "Url" => "purchase.rr/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
			}

            if ($acl->CheckUserAccess("rr", "process", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "<b>Input Harga & Supplier</b>", "Url" => "purchase.rr/process/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Mohon memilih Dokumen R/R terlebih dahulu sebelum melakukan proses!\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
                    "Confirm" => "Apakah anda mau memproses Dokumen R/R yang dipilih ?");
            }

			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;

		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
			$settings["from"] = "vw_ic_rr_master AS a JOIN sys_status_code AS b ON a.status = b.code AND b.key = 'pr_status' LEFT JOIN sys_status_code AS c ON a.req_level = c.code AND c.key = 'mr_req_level'";
			//if ($this->userLevel < 5){
            //    $settings["where"] = "a.is_deleted = 0 And a.status >= 3 And Locate(a.project_id,".$this->userProjectIds.")";
            //}else {
            $settings["where"] = "a.is_deleted = 0 And a.status >= 3 And a.entity_id = " . $this->userCompanyId;
            //}
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	/**
	 * Multi step entry data
	 */

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen R/R terlebih dahulu.");
			redirect_url("purchase.rr");
			return;
		}

		$rr = new Rr();
		$rr = $rr->LoadById($id);
		if ($rr == null || $rr->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen R/R yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("purchase.rr");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($rr->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen R/R yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("purchase.rr");
				return;
			}
		}
		if ($rr->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen R/R yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("purchase.rr");
			return;
		}
        $details = $rr->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "R/R No: ". $rr->DocumentNo." Harap hapus dulu Detail Item Requestnya");
            redirect_url("purchase.rr");
            return;
        }
		// Everything is green
		// ToDo: Kalau Referensi MR nya status bukan 5 bagaimana ?
		$this->connector->BeginTransaction();
		// Step 1: OK Hapus referensi MR jika ada...
		//	NOTE: Walau saat ini tidak dimungkinkan 1 MR akan terbit > 1 R/R tetapi kita siapkan querynya jika terjadi
		//		  Yang mungkin saat ini > 1 MR terbit hanya 1 R/R jika departemennya sama
		$this->connector->CommandText =
			"UPDATE ic_mr_master SET
				status = 3
				, updateby_id = ?user
				, update_time = NOW()
			WHERE id IN (
				-- LOGIC: cari semua MR id (self join berdasarkan mr_id) yang mana tidak boleh sama dengan R/R yang dihapus dan statusnnya belum di delete
				--        Jika ketemu pasangannya bearti masih ada referensinya. CARI YANG REFERENSINYA NULL
				SELECT a.mr_id -- AS del_mr_id, a.pr_id AS del_pr_id, a.is_deleted AS del_is_deleted, b.*
				FROM ic_link_mr_pr AS a
					LEFT JOIN ic_link_mr_pr AS b ON a.mr_id = b.mr_id AND b.pr_id <> ?prId AND b.is_deleted = 0
				WHERE a.pr_id = ?prId AND b.mr_id IS NULL
			)";
		$this->connector->AddParameter("?user", AclManager::GetInstance()->GetCurrentUser()->Id);
		$this->connector->AddParameter("?prId", $rr->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen R/R: %s ! Gagal Hapus Referensi MR<br /> Harap hubungi system administrator.<br />Error: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.rr");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_mr_pr SET is_deleted = 1 WHERE pr_id = ?prId";
		$this->connector->AddParameter("?prId", $rr->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen R/R: %s ! Gagal Hapus Link MR-R/R<br /> Harap hubungi system administrator.<br />Error: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.rr");
		}

		// Step 3: Hapus dokumen R/R
		$rr->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($rr->Delete($rr->Id) == 1) {
			$this->connector->CommitTransaction();
			$this->persistence->SaveState("info", sprintf("Dokumen R/R: %s sudah berhasil dihapus.", $rr->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen R/R: %s ! Harap hubungi system administrator.<br />Error: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("purchase.rr");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("purchase.rr");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$rr = new Rr();
			$rr = $rr->LoadById($id);

			if ($rr->StatusCode != 1) {
				$errors[] = sprintf("Dokumen R/R: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $rr->DocumentNo, $rr->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($rr->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$rr->ApprovedById = $userId;
			$rs = $rr->Approve($rr->Id);
			if ($rs == 1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen R/R: %s sudah berhasil di approve", $rr->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Approve Dokumen R/R: %s. Message: %s", $rr->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("purchase.rr");
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen R/R yang akan di batalkan !");
			redirect_url("purchase.rr");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$rr = new Rr();
			$rr = $rr->LoadById($id);

			if ($rr->StatusCode != 2) {
				$errors[] = sprintf("Dokumen R/R: %s tidak diproses karena status sudah bukan APR/ROVED ! Status Dokumen: %s", $rr->DocumentNo, $rr->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($rr->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$rr->UpdatedById = $userId;
			$rs = $rr->DisApprove($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen R/R: %s sudah berhasil di dibatalkan (disapprove)", $rr->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen R/R: %s. Message: %s", $rr->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("purchase.rr");
	}

	public function split($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Dokumen R/R terlebih dahulu !");
			redirect_url("purchase.rr");
			return;
		}

		$rr = new Rr();
		$rr = $rr->LoadById($id); // Tetap load data master (baik first time atau POST)

		if (count($this->postData) > 0) {
			$ids = $this->GetPostValue("id", array());
			if ($this->ValidateSplit($rr, $ids)) {
				$this->connector->BeginTransaction();
				if ($this->doSplit($rr, $ids)) {
					// YES sukses :D
					$this->connector->CommitTransaction();
					redirect_url("purchase.rr");
				} else {
					$this->connector->RollbackTransaction();
				}
			}
		} else {
			if ($rr == null || $rr->IsDeleted) {
				$this->persistence->SaveState("error", "Harap memilih Dokumen R/R yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url("purchase.rr");
				return;
			}
            if ($rr->EntityId != $this->userCompanyId) {
                // WOW coba akses data lintas Company ? Simulate not found !
                $this->persistence->SaveState("error", "Dokumen R/R yang diminta tidak dapat ditemukan / sudah dihapus !");
                redirect_url("purchase.rr");
                return;
            }
			if ($rr->StatusCode > 1) {
				$this->persistence->SaveState("error", "Maaf Status Dokumen R/R yang diminta sudah bukan DRAFT / INCOMPLETE.");
				redirect_url("purchase.rr/view/" . $rr->Id);
			}
		}

		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
		require_once(MODEL . "master/creditor.php");

		// Load R/R Details kalau R/R memang bisa dilihat
		$rr->LoadDetails();
		$rr->LoadAssociatedMr();

		$company = new Company();
		$company = $company->FindById($rr->EntityId);
		// Cara bego.. buat cari supplier name tapi uda ga nemu cara laen... gw ga mau join 3x ke table creditor...
		$suppliers = array();
		foreach ($rr->Details as $detail) {
			if ($detail->SupplierId1 != null && !array_key_exists($detail->SupplierId1, $suppliers)) {
				$supplier = new Creditor();
				$supplier = $supplier->FindById($detail->SupplierId1);
				$suppliers[$supplier->Id] = $supplier;
			}
			if ($detail->SupplierId2 != null && !array_key_exists($detail->SupplierId2, $suppliers)) {
				$supplier = new Creditor();
				$supplier = $supplier->FindById($detail->SupplierId2);
				$suppliers[$supplier->Id] = $supplier;
			}
			if ($detail->SupplierId3 != null && !array_key_exists($detail->SupplierId3, $suppliers)) {
				$supplier = new Creditor();
				$supplier = $supplier->FindById($detail->SupplierId3);
				$suppliers[$supplier->Id] = $supplier;
			}
		}
        $rroject = new Project($rr->ProjectId);
        $this->Set("project", $rroject);
		$this->Set("company", $company);
		$this->Set("rr", $rr);
		$this->Set("suppliers", $suppliers);
	}

	private function ValidateSplit(Pr $rr, array $ids) {
		if (count($ids) == 0) {
			$this->Set("error", "Mohon pilih barang yang akan di split terlebih dahulu");
			return false;
		}

		// Berhubung sudah masuk method POST maka semua validasi awal sudah lewat...
		$rr->LoadDetails();

		// Jangan sampe dia buang semua item / di split ke no dokumen yang lain
		if (count($ids) == count($rr->Details)) {
			$this->Set("error", "Maaf anda tidak dapat memindahkan semua detail ke dokumen lain tanpa menyisakan satu detail pada dokumen yang lama");
			return false;
		}
		$buff = array();
		foreach ($rr->Details as $detail) {
			$buff[] = $detail->Id;
		}
		foreach ($ids as $id) {
			if (!in_array($id, $buff)) {
				// LHO KOK BISA ADA ID YANG TIDAK ADA DI DETAIL ????
				$this->Set("error", "Failed in integrity check ! Mohon ulangi proses split dokumen dari awal.");
				return false;
			}
		}

		return true;
	}

	private function doSplit(Pr $rr, array $ids) {
		$newPr = new Rr();
		$newPr->Date = time();
		$newPr->EntityId = $rr->EntityId;
		$newPr->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		$newPr->Note = "R/R Baru Hasil Split dari R/R: " . $rr->DocumentNo . "\nKeterangan Lama:\n" . $rr->Note;
		$newPr->StatusCode = 1; // By Default Draft
		foreach ($rr->Details as $detail) {
			if ($detail->SelectedSupplier == -1) {
				$newPr->StatusCode = 0; // wakakak jadi INCOMPLETE LOE !!!!
				break;
			}
		}

		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$newPr->DocumentNo = $docCounter->AutoDocNoPr($rr->EntityId, $rr->Date, 1);
		if ($newPr->DocumentNo == null) {
			$this->Set("error", "Maaf proses split R/R tidak dapat dilakukan. Tanggal hari ini sudah terlocked oleh system. Hubungi system admin.");
			return false;
		}

		// OK gw sih mau pake cara curang... cukup entry R/R baru tapi detail mah kita migrasikan saja
		$rs = $newPr->Insert();
		if ($rs != 1) {
			$this->Set("error", "Proses split gagal karena dokumen baru gagal entry. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		$counter = 0;
		foreach ($rr->Details as $detail) {
			// OK proses migrasi dimulai....
			if (!in_array($detail->Id, $ids)) {
				continue; // Detail yang ini tidak akan di migrasi
			}

			$counter++;
			$detail->RrId = $newPr->Id;
			$rs = $detail->Update($detail->Id);
			if ($rs != 1) {
				$this->Set("error", "Gagal migrasi detail R/R ke yang baru. Detail ID: " . $detail->Id);
				return false;
			}
		}

		// Another integrity check
		if ($counter != count($ids)) {
			$this->Set("error", sprintf("Integrity Check Failed ! Jumlah yang dipilih dengan yang dimigrasikan tidak sama ! Checked (%d) != Migrated (%d)", count($ids), $counter));
			return false;
		}

		// OK kita juga harus buat link MR - R/R yang baru...
		$this->connector->CommandText =
			"INSERT INTO ic_link_mr_pr
			SELECT DISTINCT b.mr_master_id, ?newRrId, 0
			-- SELECT a.id, a.mr_detail_id, b.id, b.mr_master_id
			FROM ic_pr_detail AS a
				JOIN ic_mr_detail AS b ON a.mr_detail_id = b.id
			WHERE a.pr_master_id = ?newRrId";
		$this->connector->AddParameter("?newRrId", $newPr->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal migrasi link MR - R/R. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// OK remove redundant link MR - R/R pada dokumen yang lama
		$this->connector->CommandText =
			"UPDATE ic_link_mr_pr SET is_deleted = 1 WHERE pr_id = ?oldRrId AND mr_id NOT IN (
				SELECT DISTINCT b.mr_master_id
				-- SELECT a.id, a.mr_detail_id, b.id, b.mr_master_id
				FROM ic_pr_detail AS a
					JOIN ic_mr_detail AS b ON a.mr_detail_id = b.id
				WHERE a.pr_master_id = ?oldRrId
			)";
		$this->connector->AddParameter("?oldRrId", $rr->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal hapus link MR - R/R pada dokumen R/R yang lama. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// SUkses... (BErhubung data R/R barunya ada disini maka kita akan set messagenya disini.
		$this->persistence->SaveState("info", sprintf("Dokumen R/R: %s sudah di split menjadi 2. Dokumen yang baru: %s.", $rr->DocumentNo, $newPr->DocumentNo));
		return true;
	}

	//buat halaman search data
	public function overview() {
        require_once(MODEL . "master/project.php");
		require_once(MODEL . "status_code.php");

		if (count($this->getData) > 0) {
			$status = $this->GetGetValue("status");
			$startDate = strtotime($this->GetGetValue("startDate"));
			$endDate = strtotime($this->GetGetValue("endDate"));
			$output = $this->GetGetValue("output", "web");

			$this->connector->CommandText = "SELECT a.*, b.entity_cd AS entity, c.short_desc AS status_name
                                            FROM ic_pr_master AS a
                                            JOIN cm_company AS b ON a.entity_id = b.entity_id
                                            JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'pr_status'
                                            WHERE a.is_deleted = 0";

			if ($this->userCompanyId != 7) {
				$this->connector->CommandText .= " AND a.entity_id = ?entity";
				$this->connector->AddParameter("?entity", $this->userCompanyId);
			}
			if ($status != -1) {
				$this->connector->CommandText .= " AND a.status = ?status";
				$this->connector->AddParameter("?status", $status);
			}

			$this->connector->CommandText .= " AND a.pr_date >= ?start
                                               AND a.pr_date <= ?end
                                               ORDER BY a.pr_date ASC";
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			$report = $this->connector->ExecuteQuery();

		} else {
			$status = null;
			$output = "web";
			$startDate = time();
			$endDate = time();
			$report = null;
		}

		$syscode = new StatusCode();
		$this->Set("pr_status", $syscode->LoadPrStatus());

		$temp = $syscode->FindBy("pr_status", $status);
		$statusName = $temp != null ? $temp->ShortDesc : "SEMUA STATUS";
		$this->Set("statusName", $statusName);

		$this->Set("report", $report);
		$this->Set("startDate", $startDate);
		$this->Set("endDate", $endDate);
		$this->Set("output", $output);
        //load project
        $rroject = new Project();
        if ($this->userLevel < 5) {
            $rrojects = $rroject->LoadAllowedProject($this->userProjectIds);
        }else{
            $rrojects = $rroject->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $rrojects);
	}

	//proses cetak form MR
	public function doc_print($output) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
		require_once(MODEL . "purchase/pr.php");

		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
			redirect_url("purchase.rr");
			return;
		}

		$report = array();
		require_once(MODEL . "master/user_admin.php");

		foreach ($ids as $id) {

			$rr = new Rr();
			$rr = $rr->LoadById($id);
			$rr->LoadDetails();
			$rr->LoadUsers();

			$report[] = $rr;
		}
		$company = new Company($this->userCompanyId);
		$this->Set("company", $company);
        $this->Set("report", $report);
		$this->Set("output", $output);
	}

    public function add($rrId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $rr = new Rr();
        if ($rrId > 0 ) {
            $rr = $rr->LoadById($rrId);
            if ($rr == null) {
                $this->persistence->SaveState("error", "Maaf Data Request dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.rr");
            }
            if ($rr->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $rr->DocumentNo,$rr->GetStatus()));
                redirect_url("purchase.rr");
            }
        }else{
            $rr->Date = date('d-m-Y');
        }

        // load details
        $rr->LoadDetails();
        //load project
        $rroject = new Project();
        if ($this->userLevel < 5) {
            $rrojects = $rroject->LoadAllowedProject($this->userProjectIds);
        }else{
            $rrojects = $rroject->LoadByEntityId($this->userCompanyId);
        }
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $rrojects);
        $this->Set("rr", $rr);
    }

    public function proses_master($rrId = 0) {
        $rr = new Rr();
        if (count($this->postData) > 0) {
            $rr->Id = $rrId;
            $rr->EntityId = $this->userCompanyId;
            $rr->ProjectId = $this->GetPostValue("ProjectId");
            $rr->Date = strtotime($this->GetPostValue("PrDate"));
            $rr->DeptId = $this->GetPostValue("DeptId");
            $rr->Note = $this->GetPostValue("Note");
            $rr->DocumentNo = $this->GetPostValue("PrNo");
            $rr->CreatedById = $this->userUid;
            if ($rr->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $rr->DocumentNo = $docCounter->AutoDocNoPr($rr->EntityId, $rr->Date, 1);
                $rs = $rr->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$rr->Id,$rr->DocumentNo);
                }else{
                    printf("ER|A|%d",$rr->Id);
                }
            }else{
                $rs = $rr->Update($rr->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$rr->Id,$rr->DocumentNo);
                }else{
                    printf("ER|U|%d",$rr->Id);
                }
            }
        }else{
            printf("ER|X|%d",$rrId);
        }
    }

    public function add_detail($rrId = null) {
        $rst = null;
        $rr = new Pr($rrId);
        $rrdetail = new RrDetail();
        $rrdetail->RrId = $rrId;
        $rr_item_exist = false;
        if (count($this->postData) > 0) {
            $rrdetail->ItemId = $this->GetPostValue("aItemId");
            $rrdetail->Qty = $this->GetPostValue("aPrQty");
            $rrdetail->ItemDescription = '-';
            $rrdetail->MrDetailId = $this->GetPostValue("aMrDetailId");
            $rrdetail->UomCd = $this->GetPostValue("aUomCd");
            // item baru simpan
            $rs = $rrdetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$rrdetail->Id);
                //creat mr link
                if ($rrdetail->MrDetailId > 0) {
                    //create link to mr
                    $this->connector->CommandText = "INSERT INTO ic_link_mr_pr(mr_id, pr_id) VALUES (?mr, ?rr)";
                    $this->connector->AddParameter("?mr", $rrdetail->MrDetailId);
                    $this->connector->AddParameter("?rr", $rrId);
                    $rs = $this->connector->ExecuteNonQuery();
                    //update mr qty
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $rrdetail->Qty);
                    $this->connector->AddParameter("?id", $rrdetail->MrDetailId);
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
        $rrdetail = new RrDetail();
        $rrdetail = $rrdetail->LoadById($id);
        if ($rrdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $mri = $rrdetail->MrDetailId;
        $rri = $rrdetail->RrId;
        $qty = $rrdetail->Qty;
        if ($rrdetail->Delete($id) == 1) {
            if ($mri > 0) {
                //delete link to mr
                $this->connector->CommandText = "Delete From ic_link_mr_pr Where mr_id = ?mr And pr_id = ?rr";
                $this->connector->AddParameter("?mr", $mri);
                $this->connector->AddParameter("?rr", $rri);
                $rs = $this->connector->ExecuteNonQuery();
                //update mr qty
                $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty - ?qty Where a.id = ?id";
                $this->connector->AddParameter("?qty", $qty);
                $this->connector->AddParameter("?id", $mri);
                $rs = $this->connector->ExecuteNonQuery();
            }
            printf("Data Detail R/R ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail R/R ID: %d gagal dihapus!",$id);
        }
    }

    public function getjson_mritems($rrojectId = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $mritems = new Rr();
        $mritems = $mritems->GetJSonUnfinishedMrItems($rrojectId,$filter);
        echo json_encode($mritems);
    }

    public function view($rrId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $rr = new Rr();
        if ($rrId > 0 ) {
            $rr = $rr->LoadById($rrId);
            if ($rr == null) {
                $this->persistence->SaveState("error", "Maaf Data Request dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.rr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Request tidak ditemukan!");
            redirect_url("purchase.rr");
        }

        // load details
        $rr->LoadDetails();
        //load project
        $rroject = new Project();
        if ($this->userLevel < 5) {
            $rrojects = $rroject->LoadAllowedProject($this->userProjectIds);
        }else{
            $rrojects = $rroject->LoadByEntityId($this->userCompanyId);
        }
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $rrojects);
        $this->Set("rr", $rr);
    }

    public function process($rrId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/creditor.php");

        $loader = null;
        $rr = new Rr();
        if ($rrId > 0 ) {
            $rr = $rr->LoadById($rrId);
            if ($rr == null) {
                $this->persistence->SaveState("error", "Maaf Data Request dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.rr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Request tidak ditemukan!");
            redirect_url("purchase.rr");
        }

        if (count($this->postData) > 0) {
            $rr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;

            $data = $this->GetPostValue("data", array());
            $rrices = $this->GetPostValue("Price1", array());
            $suppls = $this->GetPostValue("SupplierId1", array());
            $max = count($rrices);

            for ($i = 0; $i < $max; $i++) {
                $tokens = explode("|", $data[$i]);
                $rrdetail = new RrDetail();
                $rrdetail->RrId = $rrId;
                $rrdetail->Id = $tokens[0];
                $rrdetail->MrDetailId = $tokens[1];
                $rrdetail->ItemId = $tokens[2];
                $rrdetail->Qty = $tokens[3];
                $rrdetail->UomCd = $tokens[4];
                $rrdetail->Price1 = $rrices[$i];
                $rrdetail->SupplierId1 = $suppls[$i];

                $rr->Details[] = $rrdetail;
            }

            $this->connector->BeginTransaction();
            if ($this->doApprove($rr)) {
                $this->connector->CommitTransaction();
                $this->persistence->SaveState("info", sprintf("Dokumen R/R: %s sudah berhasil di approve", $rr->DocumentNo));
                redirect_url("purchase.rr");
            } else {
                if ($this->connector->GetHasError()) {
                    $this->Set("error", "Unknown Database Error: " . $this->connector->GetErrorMessage());
                }
                $this->connector->RollbackTransaction();
            }
        } else {
            // load details
            $rr->LoadDetails();
        }
        //load project
        $rroject = new Project();
        if ($this->userLevel < 5) {
            $rrojects = $rroject->LoadAllowedProject($this->userProjectIds);
        }else{
            $rrojects = $rroject->LoadByEntityId($this->userCompanyId);
        }
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $rrojects);
        $this->Set("rr", $rr);
        //load supplier
        $suppliers = new Creditor();
        $suppliers = $suppliers->LoadByEntity($this->userCompanyId);
        $this->Set("suppliers", $suppliers);
    }

    private function doApprove(Rr $rr) {
        // OK... let approve IT
        $rs = $rr->Approve($rr->Id);
        if ($rs == -1) {
            $this->Set("error", sprintf("Gagal approve master R/R: %s. Message: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
            return false;
        }

        foreach ($rr->Details as $detail) {
            $rs = $detail->Approve($detail->Id);
            if ($rs == -1) {
                $this->Set("error", sprintf("Gagal Approve Item R/R: %s (Kode: %s). Message: %s", $detail->ItemName, $detail->ItemCode, $this->connector->GetErrorMessage()));
                return false;
            }
        }

        return true;
    }
}


// End of File: pr_controller.php
