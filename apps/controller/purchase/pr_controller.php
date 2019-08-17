<?php

class PrController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "purchase/pr.php");
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
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "PR Number", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.pr_date, '%d %M %Y')", "display" => "PR Date", "width" => 100, "sortable" => false);
		$settings["columns"][] = array("name" => "b.short_desc", "display" => "Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d %M %Y')", "display" => "Last Update", "width" => 100, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "PR Number");
		$settings["filters"][] = array("name" => "b.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Purchase Request List";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("pr", "view", "purchase")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "purchase.pr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "purchase.pr/overview", "Class" => "bt_report", "ReqId" => 0);
			}

			if ($acl->CheckUserAccess("pr", "doc_print", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "XLS Print", "Url" => "purchase.pr/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
				$settings["actions"][] = array("Text" => "PDF Print", "Url" => "purchase.pr/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
			}

            if ($acl->CheckUserAccess("pr", "process", "purchase")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Input Harga", "Url" => "purchase.pr/process/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Mohon memilih Dokumen PR terlebih dahulu sebelum melakukan proses!\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
                    "Confirm" => "Apakah anda mau memproses Dokumen PR yang dipilih ?");
            }

			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;

		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
			$settings["from"] = "vw_ic_pr_master AS a JOIN sys_status_code AS b ON a.status = b.code AND b.key = 'pr_status'";
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
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen PR terlebih dahulu.");
			redirect_url("purchase.pr");
			return;
		}

		$pr = new Pr();
		$pr = $pr->LoadById($id);
		if ($pr == null || $pr->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen PR yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("purchase.pr");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($pr->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen PR yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("purchase.pr");
				return;
			}
		}
		if ($pr->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen PR yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("purchase.pr");
			return;
		}
        $details = $pr->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "PR No: ". $pr->DocumentNo." Harap hapus dulu Detail Item Issuenya");
            redirect_url("purchase.pr");
            return;
        }
		// Everything is green
		// ToDo: Kalau Referensi MR nya status bukan 5 bagaimana ?
		$this->connector->BeginTransaction();
		// Step 1: OK Hapus referensi MR jika ada...
		//	NOTE: Walau saat ini tidak dimungkinkan 1 MR akan terbit > 1 PR tetapi kita siapkan querynya jika terjadi
		//		  Yang mungkin saat ini > 1 MR terbit hanya 1 PR jika departemennya sama
		$this->connector->CommandText =
			"UPDATE ic_mr_master SET
				status = 3
				, updateby_id = ?user
				, update_time = NOW()
			WHERE id IN (
				-- LOGIC: cari semua MR id (self join berdasarkan mr_id) yang mana tidak boleh sama dengan PR yang dihapus dan statusnnya belum di delete
				--        Jika ketemu pasangannya bearti masih ada referensinya. CARI YANG REFERENSINYA NULL
				SELECT a.mr_id -- AS del_mr_id, a.pr_id AS del_pr_id, a.is_deleted AS del_is_deleted, b.*
				FROM ic_link_mr_pr AS a
					LEFT JOIN ic_link_mr_pr AS b ON a.mr_id = b.mr_id AND b.pr_id <> ?prId AND b.is_deleted = 0
				WHERE a.pr_id = ?prId AND b.mr_id IS NULL
			)";
		$this->connector->AddParameter("?user", AclManager::GetInstance()->GetCurrentUser()->Id);
		$this->connector->AddParameter("?prId", $pr->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PR: %s ! Gagal Hapus Referensi MR<br /> Harap hubungi system administrator.<br />Error: %s", $pr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.pr");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_mr_pr SET is_deleted = 1 WHERE pr_id = ?prId";
		$this->connector->AddParameter("?prId", $pr->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PR: %s ! Gagal Hapus Link MR-PR<br /> Harap hubungi system administrator.<br />Error: %s", $pr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("purchase.pr");
		}

		// Step 3: Hapus dokumen PR
		$pr->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($pr->Delete($pr->Id) == 1) {
			$this->connector->CommitTransaction();
			$this->persistence->SaveState("info", sprintf("Dokumen PR: %s sudah berhasil dihapus.", $pr->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PR: %s ! Harap hubungi system administrator.<br />Error: %s", $pr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("purchase.pr");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
			redirect_url("purchase.pr");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$pr = new Pr();
			$pr = $pr->LoadById($id);

			if ($pr->StatusCode != 1) {
				$errors[] = sprintf("Dokumen PR: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $pr->DocumentNo, $pr->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($pr->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$pr->ApprovedById = $userId;
			$rs = $pr->Approve($pr->Id);
			if ($rs == 1) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen PR: %s sudah berhasil di approve", $pr->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Approve Dokumen PR: %s. Message: %s", $pr->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("purchase.pr");
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen PR yang akan di batalkan !");
			redirect_url("purchase.pr");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$pr = new Pr();
			$pr = $pr->LoadById($id);

			if ($pr->StatusCode != 2) {
				$errors[] = sprintf("Dokumen PR: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $pr->DocumentNo, $pr->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($pr->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$pr->UpdatedById = $userId;
			$rs = $pr->DisApprove($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen PR: %s sudah berhasil di dibatalkan (disapprove)", $pr->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen PR: %s. Message: %s", $pr->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
		redirect_url("purchase.pr");
	}

	public function split($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Dokumen PR terlebih dahulu !");
			redirect_url("purchase.pr");
			return;
		}

		$pr = new Pr();
		$pr = $pr->LoadById($id); // Tetap load data master (baik first time atau POST)

		if (count($this->postData) > 0) {
			$ids = $this->GetPostValue("id", array());
			if ($this->ValidateSplit($pr, $ids)) {
				$this->connector->BeginTransaction();
				if ($this->doSplit($pr, $ids)) {
					// YES sukses :D
					$this->connector->CommitTransaction();
					redirect_url("purchase.pr");
				} else {
					$this->connector->RollbackTransaction();
				}
			}
		} else {
			if ($pr == null || $pr->IsDeleted) {
				$this->persistence->SaveState("error", "Harap memilih Dokumen PR yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url("purchase.pr");
				return;
			}
            if ($pr->EntityId != $this->userCompanyId) {
                // WOW coba akses data lintas Company ? Simulate not found !
                $this->persistence->SaveState("error", "Dokumen PR yang diminta tidak dapat ditemukan / sudah dihapus !");
                redirect_url("purchase.pr");
                return;
            }
			if ($pr->StatusCode > 1) {
				$this->persistence->SaveState("error", "Maaf Status Dokumen PR yang diminta sudah bukan DRAFT / INCOMPLETE.");
				redirect_url("purchase.pr/view/" . $pr->Id);
			}
		}

		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
		require_once(MODEL . "master/creditor.php");

		// Load PR Details kalau PR memang bisa dilihat
		$pr->LoadDetails();
		$pr->LoadAssociatedMr();

		$company = new Company();
		$company = $company->FindById($pr->EntityId);
		// Cara bego.. buat cari supplier name tapi uda ga nemu cara laen... gw ga mau join 3x ke table creditor...
		$suppliers = array();
		foreach ($pr->Details as $detail) {
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
        $project = new Project($pr->ProjectId);
        $this->Set("project", $project);
		$this->Set("company", $company);
		$this->Set("pr", $pr);
		$this->Set("suppliers", $suppliers);
	}

	private function ValidateSplit(Pr $pr, array $ids) {
		if (count($ids) == 0) {
			$this->Set("error", "Mohon pilih barang yang akan di split terlebih dahulu");
			return false;
		}

		// Berhubung sudah masuk method POST maka semua validasi awal sudah lewat...
		$pr->LoadDetails();

		// Jangan sampe dia buang semua item / di split ke no dokumen yang lain
		if (count($ids) == count($pr->Details)) {
			$this->Set("error", "Maaf anda tidak dapat memindahkan semua detail ke dokumen lain tanpa menyisakan satu detail pada dokumen yang lama");
			return false;
		}
		$buff = array();
		foreach ($pr->Details as $detail) {
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

	private function doSplit(Pr $pr, array $ids) {
		$newPr = new Pr();
		$newPr->Date = time();
		$newPr->EntityId = $pr->EntityId;
		$newPr->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		$newPr->Note = "PR Baru Hasil Split dari PR: " . $pr->DocumentNo . "\nKeterangan Lama:\n" . $pr->Note;
		$newPr->StatusCode = 1; // By Default Draft
		foreach ($pr->Details as $detail) {
			if ($detail->SelectedSupplier == -1) {
				$newPr->StatusCode = 0; // wakakak jadi INCOMPLETE LOE !!!!
				break;
			}
		}

		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$newPr->DocumentNo = $docCounter->AutoDocNoPr($pr->EntityId, $pr->Date, 1);
		if ($newPr->DocumentNo == null) {
			$this->Set("error", "Maaf proses split PR tidak dapat dilakukan. Tanggal hari ini sudah terlocked oleh system. Hubungi system admin.");
			return false;
		}

		// OK gw sih mau pake cara curang... cukup entry PR baru tapi detail mah kita migrasikan saja
		$rs = $newPr->Insert();
		if ($rs != 1) {
			$this->Set("error", "Proses split gagal karena dokumen baru gagal entry. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		$counter = 0;
		foreach ($pr->Details as $detail) {
			// OK proses migrasi dimulai....
			if (!in_array($detail->Id, $ids)) {
				continue; // Detail yang ini tidak akan di migrasi
			}

			$counter++;
			$detail->PrId = $newPr->Id;
			$rs = $detail->Update($detail->Id);
			if ($rs != 1) {
				$this->Set("error", "Gagal migrasi detail PR ke yang baru. Detail ID: " . $detail->Id);
				return false;
			}
		}

		// Another integrity check
		if ($counter != count($ids)) {
			$this->Set("error", sprintf("Integrity Check Failed ! Jumlah yang dipilih dengan yang dimigrasikan tidak sama ! Checked (%d) != Migrated (%d)", count($ids), $counter));
			return false;
		}

		// OK kita juga harus buat link MR - PR yang baru...
		$this->connector->CommandText =
			"INSERT INTO ic_link_mr_pr
			SELECT DISTINCT b.mr_master_id, ?newPrId, 0
			-- SELECT a.id, a.mr_detail_id, b.id, b.mr_master_id
			FROM ic_pr_detail AS a
				JOIN ic_mr_detail AS b ON a.mr_detail_id = b.id
			WHERE a.pr_master_id = ?newPrId";
		$this->connector->AddParameter("?newPrId", $newPr->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal migrasi link MR - PR. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// OK remove redundant link MR - PR pada dokumen yang lama
		$this->connector->CommandText =
			"UPDATE ic_link_mr_pr SET is_deleted = 1 WHERE pr_id = ?oldPrId AND mr_id NOT IN (
				SELECT DISTINCT b.mr_master_id
				-- SELECT a.id, a.mr_detail_id, b.id, b.mr_master_id
				FROM ic_pr_detail AS a
					JOIN ic_mr_detail AS b ON a.mr_detail_id = b.id
				WHERE a.pr_master_id = ?oldPrId
			)";
		$this->connector->AddParameter("?oldPrId", $pr->Id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->Set("error", "Gagal hapus link MR - PR pada dokumen PR yang lama. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		// SUkses... (BErhubung data PR barunya ada disini maka kita akan set messagenya disini.
		$this->persistence->SaveState("info", sprintf("Dokumen PR: %s sudah di split menjadi 2. Dokumen yang baru: %s.", $pr->DocumentNo, $newPr->DocumentNo));
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
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
	}

	//proses cetak form MR
	public function doc_print($output) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
		require_once(MODEL . "purchase/pr.php");

		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
			redirect_url("purchase.pr");
			return;
		}

		$report = array();
		require_once(MODEL . "master/user_admin.php");

		foreach ($ids as $id) {

			$pr = new Pr();
			$pr = $pr->LoadById($id);
			$pr->LoadDetails();
			$pr->LoadUsers();

			$report[] = $pr;
		}
		$company = new Company($this->userCompanyId);
		$this->Set("company", $company);
        $this->Set("report", $report);
		$this->Set("output", $output);
	}

    public function add($prId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $pr = new Pr();
        if ($prId > 0 ) {
            $pr = $pr->LoadById($prId);
            if ($pr == null) {
                $this->persistence->SaveState("error", "Maaf Data Issue dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.pr");
            }
            if ($pr->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $pr->DocumentNo,$pr->GetStatus()));
                redirect_url("purchase.pr");
            }
        }else{
            $pr->Date = date('d-m-Y');
        }

        // load details
        $pr->LoadDetails();
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("pr", $pr);
    }

    public function proses_master($prId = 0) {
        $pr = new Pr();
        if (count($this->postData) > 0) {
            $pr->Id = $prId;
            $pr->EntityId = $this->userCompanyId;
            $pr->ProjectId = $this->GetPostValue("ProjectId");
            $pr->Date = strtotime($this->GetPostValue("PrDate"));
            $pr->DeptId = $this->GetPostValue("DeptId");
            $pr->Note = $this->GetPostValue("Note");
            $pr->DocumentNo = $this->GetPostValue("PrNo");
            $pr->CreatedById = $this->userUid;
            if ($pr->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $pr->DocumentNo = $docCounter->AutoDocNoPr($pr->EntityId, $pr->Date, 1);
                $rs = $pr->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$pr->Id,$pr->DocumentNo);
                }else{
                    printf("ER|A|%d",$pr->Id);
                }
            }else{
                $rs = $pr->Update($pr->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$pr->Id,$pr->DocumentNo);
                }else{
                    printf("ER|U|%d",$pr->Id);
                }
            }
        }else{
            printf("ER|X|%d",$prId);
        }
    }

    public function add_detail($prId = null) {
        $rst = null;
        $pr = new Pr($prId);
        $prdetail = new PrDetail();
        $prdetail->PrId = $prId;
        $pr_item_exist = false;
        if (count($this->postData) > 0) {
            $prdetail->ItemId = $this->GetPostValue("aItemId");
            $prdetail->Qty = $this->GetPostValue("aPrQty");
            $prdetail->ItemDescription = '-';
            $prdetail->MrDetailId = $this->GetPostValue("aMrDetailId");
            $prdetail->UomCd = $this->GetPostValue("aUomCd");
            // item baru simpan
            $rs = $prdetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$prdetail->Id);
                //creat mr link
                if ($prdetail->MrDetailId > 0) {
                    //create link to mr
                    $this->connector->CommandText = "INSERT INTO ic_link_mr_pr(mr_id, pr_id) VALUES (?mr, ?pr)";
                    $this->connector->AddParameter("?mr", $prdetail->MrDetailId);
                    $this->connector->AddParameter("?pr", $prId);
                    $rs = $this->connector->ExecuteNonQuery();
                    //update mr qty
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $prdetail->Qty);
                    $this->connector->AddParameter("?id", $prdetail->MrDetailId);
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
        $prdetail = new PrDetail();
        $prdetail = $prdetail->LoadById($id);
        if ($prdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $mri = $prdetail->MrDetailId;
        $pri = $prdetail->PrId;
        $qty = $prdetail->Qty;
        if ($prdetail->Delete($id) == 1) {
            if ($mri > 0) {
                //delete link to mr
                $this->connector->CommandText = "Delete From ic_link_mr_pr Where mr_id = ?mr And pr_id = ?pr";
                $this->connector->AddParameter("?mr", $mri);
                $this->connector->AddParameter("?pr", $pri);
                $rs = $this->connector->ExecuteNonQuery();
                //update mr qty
                $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty - ?qty Where a.id = ?id";
                $this->connector->AddParameter("?qty", $qty);
                $this->connector->AddParameter("?id", $mri);
                $rs = $this->connector->ExecuteNonQuery();
            }
            printf("Data Detail PR ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail PR ID: %d gagal dihapus!",$id);
        }
    }

    public function getjson_mritems($projectId = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $mritems = new Pr();
        $mritems = $mritems->GetJSonUnfinishedMrItems($projectId,$filter);
        echo json_encode($mritems);
    }

    public function view($prId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $pr = new Pr();
        if ($prId > 0 ) {
            $pr = $pr->LoadById($prId);
            if ($pr == null) {
                $this->persistence->SaveState("error", "Maaf Data Issue dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.pr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Issue tidak ditemukan!");
            redirect_url("purchase.pr");
        }

        // load details
        $pr->LoadDetails();
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("pr", $pr);
    }

    public function process($prId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/creditor.php");

        $loader = null;
        $pr = new Pr();
        if ($prId > 0 ) {
            $pr = $pr->LoadById($prId);
            if ($pr == null) {
                $this->persistence->SaveState("error", "Maaf Data Issue dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("purchase.pr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Issue tidak ditemukan!");
            redirect_url("purchase.pr");
        }

        if (count($this->postData) > 0) {
            $pr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;

            $data = $this->GetPostValue("data", array());
            $prices = $this->GetPostValue("Price1", array());
            $suppls = $this->GetPostValue("SupplierId1", array());
            $max = count($prices);

            for ($i = 0; $i < $max; $i++) {
                $tokens = explode("|", $data[$i]);
                $prdetail = new PrDetail();
                $prdetail->PrId = $prId;
                $prdetail->Id = $tokens[0];
                $prdetail->MrDetailId = $tokens[1];
                $prdetail->ItemId = $tokens[2];
                $prdetail->Qty = $tokens[3];
                $prdetail->UomCd = $tokens[4];
                $prdetail->Price1 = $prices[$i];
                $prdetail->SupplierId1 = $suppls[$i];

                $pr->Details[] = $prdetail;
            }

            $this->connector->BeginTransaction();
            if ($this->doApprove($pr)) {
                $this->connector->CommitTransaction();
                $this->persistence->SaveState("info", sprintf("Dokumen PR: %s sudah berhasil di approve", $pr->DocumentNo));
                redirect_url("purchase.pr");
            } else {
                if ($this->connector->GetHasError()) {
                    $this->Set("error", "Unknown Database Error: " . $this->connector->GetErrorMessage());
                }
                $this->connector->RollbackTransaction();
            }
        } else {
            // load details
            $pr->LoadDetails();
        }
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        //load department
        $department = new Department();
        $departments = $department->LoadByEntityId($this->userCompanyId);
        //send to view
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("pr", $pr);
        //load supplier
        $suppliers = new Creditor();
        $suppliers = $suppliers->LoadByEntity($this->userCompanyId);
        $this->Set("suppliers", $suppliers);
    }

    private function doApprove(Pr $pr) {
        // OK... let approve IT
        $rs = $pr->Approve($pr->Id);
        if ($rs == -1) {
            $this->Set("error", sprintf("Gagal approve master PR: %s. Message: %s", $pr->DocumentNo, $this->connector->GetErrorMessage()));
            return false;
        }

        foreach ($pr->Details as $detail) {
            $rs = $detail->Approve($detail->Id);
            if ($rs == -1) {
                $this->Set("error", sprintf("Gagal Approve Item PR: %s (Kode: %s). Message: %s", $detail->ItemName, $detail->ItemCode, $this->connector->GetErrorMessage()));
                return false;
            }
        }

        return true;
    }
}


// End of File: pr_controller.php
