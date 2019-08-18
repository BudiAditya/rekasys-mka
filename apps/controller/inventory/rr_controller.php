<?php
//namespace Inventory;

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
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "RR Number", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.rr_date, '%d %M %Y')", "display" => "RR Date", "width" => 100, "sortable" => false);
		$settings["columns"][] = array("name" => "b.short_desc", "display" => "Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d %M %Y')", "display" => "Last Update", "width" => 100, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "RR Number");
		$settings["filters"][] = array("name" => "b.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Repair Request List";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("rr", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.rr/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("rr", "edit", "inventory")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.rr/add/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih Dokumen RR terlebih dahulu sebelum melakukan proses edit !",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("rr", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.rr/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih Dokumen RR terlebih dahulu sebelum melakukan proses delete !\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
					"Confirm" => "Apakah anda mau menghapus Dokumen RR yang dipilih ?");
			}
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("rr", "view", "inventory")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.rr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.rr/overview", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("rr", "doc_print", "inventory")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.rr/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
            }
            if ($acl->CheckUserAccess("rr", "doc_print", "inventory")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.rr/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }
            if ($acl->CheckUserAccess("rr", "approve", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "APPROVAL DEPT HEAD", "Url" => "inventory.rr/approve/1/%s", "Class" => "bt_approve", "ReqId" => 1,
                    "Error" => "Mohon pilih TEPAT satu dokumen RR !\nTidak boleh memilih lebih dari 1 dokumen.",
                    "Confirm" => "Proses Approval Level 1?");
                $settings["actions"][] = array("Text" => "BATAL APPROVAL DEPT HEAD", "Url" => "inventory.rr/disapprove/1/", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih sekurang-kurangnya satu RR !",
                    "Confirm" => "Proses Batal Approval Level 1?");
            }
            if ($acl->CheckUserAccess("rr", "verify", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "APPROVAL PM", "Url" => "inventory.rr/verify/2/%s", "Class" => "bt_approve", "ReqId" => 1,
                    "Error" => "Mohon pilih TEPAT satu dokumen RR !\nTidak boleh memilih lebih dari 1 dokumen.",
                    "Confirm" => "Proses Approval Level 2?");
                $settings["actions"][] = array("Text" => "BATAL APPROVAL PM", "Url" => "inventory.rr/unverify/2/", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih sekurang-kurangnya satu RR !",
                    "Confirm" => "Proses Batal Approval Level 2?");
            }
			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;

		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
			$settings["from"] = "vw_ic_rr_master AS a JOIN sys_status_code AS b ON a.status = b.code AND b.key = 'pr_status'";
            if ($this->userLevel < 5){
                $settings["where"] = "a.is_deleted = 0 And Locate(a.project_id,".$this->userProjectIds.")";
            }else {
                $settings["where"] = "a.is_deleted = 0 And a.entity_id = " . $this->userCompanyId;
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen RR terlebih dahulu.");
			redirect_url("inventory.rr");
			return;
		}

		$rr = new Rr();
		$rr = $rr->LoadById($id);
		if ($rr == null || $rr->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen RR yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("inventory.rr");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($rr->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen RR yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("inventory.rr");
				return;
			}
		}
		if ($rr->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen RR yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("inventory.rr");
			return;
		}
        $details = $rr->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "RR No: ". $rr->DocumentNo." Harap hapus dulu Detail Item Requestnya");
            redirect_url("inventory.rr");
            return;
        }
		// Everything is green
		// ToDo: Kalau Referensi MR nya status bukan 5 bagaimana ?
		$this->connector->BeginTransaction();
		// Step 1: OK Hapus referensi MR jika ada...
		//	NOTE: Walau saat ini tidak dimungkinkan 1 MR akan terbit > 1 RR tetapi kita siapkan querynya jika terjadi
		//		  Yang mungkin saat ini > 1 MR terbit hanya 1 RR jika departemennya sama
		$this->connector->CommandText =
			"UPDATE ic_mr_master SET
				status = 3
				, updateby_id = ?user
				, update_time = NOW()
			WHERE id IN (
				-- LOGIC: cari semua MR id (self join berdasarkan mr_id) yang mana tidak boleh sama dengan RR yang dihapus dan statusnnya belum di delete
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
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen RR: %s ! Gagal Hapus Referensi MR<br /> Harap hubungi system administrator.<br />Error: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.rr");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_mr_pr SET is_deleted = 1 WHERE pr_id = ?prId";
		$this->connector->AddParameter("?prId", $rr->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen RR: %s ! Gagal Hapus Link MR-RR<br /> Harap hubungi system administrator.<br />Error: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.rr");
		}

		// Step 3: Hapus dokumen RR
		$rr->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($rr->Delete($rr->Id) == 1) {
			$this->connector->CommitTransaction();
			$this->persistence->SaveState("info", sprintf("Dokumen RR: %s sudah berhasil dihapus.", $rr->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen RR: %s ! Harap hubungi system administrator.<br />Error: %s", $rr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("inventory.rr");
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
                                            FROM ic_rr_master AS a
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

			$this->connector->CommandText .= " AND a.rr_date >= ?start
                                               AND a.rr_date <= ?end
                                               ORDER BY a.rr_date ASC";
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
			redirect_url("inventory.rr");
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
                redirect_url("inventory.rr");
            }
            if ($rr->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $rr->DocumentNo,$rr->GetStatus()));
                redirect_url("inventory.rr");
            }
        }else{
            $rr->Date = date('d-m-Y');
        }

        // load details
        $rr->LoadDetails();
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
        $this->Set("rr", $rr);
    }

    public function proses_master($rrId = 0) {
        $rr = new Rr();
        if (count($this->postData) > 0) {
            $rr->Id = $rrId;
            $rr->EntityId = $this->userCompanyId;
            $rr->ProjectId = $this->GetPostValue("ProjectId");
            $rr->Date = strtotime($this->GetPostValue("RrDate"));
            $rr->DeptId = $this->GetPostValue("DeptId");
            $rr->Note = $this->GetPostValue("Note");
            $rr->DocumentNo = $this->GetPostValue("PrNo");
            $rr->CreatedById = $this->userUid;
            if ($rr->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $rr->DocumentNo = $docCounter->AutoDocNoRr($rr->EntityId, $rr->Date, 1);
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
        $rr = new Rr($rrId);
        $rrdetail = new RrDetail();
        $rrdetail->RrId = $rrId;
        $pr_item_exist = false;
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
                    $this->connector->CommandText = "INSERT INTO ic_link_mr_pr(mr_id, pr_id) VALUES (?mr, ?pr)";
                    $this->connector->AddParameter("?mr", $rrdetail->MrDetailId);
                    $this->connector->AddParameter("?pr", $rrId);
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
        $pri = $rrdetail->RrId;
        $qty = $rrdetail->Qty;
        if ($rrdetail->Delete($id) == 1) {
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
            printf("Data Detail RR ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail RR ID: %d gagal dihapus!",$id);
        }
    }

    public function getjson_mritems($projectId = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $mritems = new Rr();
        $mritems = $mritems->GetJSonUnfinishedMrItems($projectId,$filter);
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
                redirect_url("inventory.rr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Request tidak ditemukan!");
            redirect_url("inventory.rr");
        }

        // load details
        $rr->LoadDetails();
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
                redirect_url("inventory.rr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Request tidak ditemukan!");
            redirect_url("inventory.rr");
        }

        if (count($this->postData) > 0) {
            $rr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;

            $data = $this->GetPostValue("data", array());
            $prices = $this->GetPostValue("Price1", array());
            $suppls = $this->GetPostValue("SupplierId1", array());
            $max = count($prices);

            for ($i = 0; $i < $max; $i++) {
                $tokens = explode("|", $data[$i]);
                $rrdetail = new RrDetail();
                $rrdetail->RrId = $rrId;
                $rrdetail->Id = $tokens[0];
                $rrdetail->MrDetailId = $tokens[1];
                $rrdetail->ItemId = $tokens[2];
                $rrdetail->Qty = $tokens[3];
                $rrdetail->UomCd = $tokens[4];
                $rrdetail->Price1 = $prices[$i];
                $rrdetail->SupplierId1 = $suppls[$i];

                $rr->Details[] = $rrdetail;
            }

            $this->connector->BeginTransaction();
            if ($this->doApprove($rr)) {
                $this->connector->CommitTransaction();
                $this->persistence->SaveState("info", sprintf("Dokumen RR: %s sudah berhasil di approve", $rr->DocumentNo));
                redirect_url("inventory.rr");
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
        $this->Set("rr", $rr);
        //load supplier
        $suppliers = new Creditor();
        $suppliers = $suppliers->LoadByEntity($this->userCompanyId);
        $this->Set("suppliers", $suppliers);
    }

    public function approve($lvl = 1, $rrId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $rr = new Rr();
        if ($rrId > 0 ) {
            $rr = $rr->LoadById($rrId);
            if ($rr == null) {
                $this->persistence->SaveState("error", "Maaf, Data P/R dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.rr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf, Data P/R tidak ditemukan!");
            redirect_url("inventory.rr");
        }

        if (count($this->postData) > 0) {
            $applvl = $this->GetPostValue("AppLevel");
            $rr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($rr->Approve($rrId,$applvl)){
                $this->persistence->SaveState("info", "Proses Approval berhasil!");
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval gagal!");
            }
            redirect_url("inventory.rr");
        }else{
            if ($lvl == 1){
                if ($rr->StatusCode <> 1 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DRAFT-, tidak boleh di-Approve Level-1!");
                    redirect_url("inventory.rr");
                }
            }elseif ($lvl == 2){
                if ($rr->StatusCode <> 2 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DH APPROVED-, tidak boleh di-Approve Level-2!");
                    redirect_url("inventory.rr");
                }
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval tidak valid!");
                redirect_url("inventory.rr");
            }

        }
        // load details
        $rr->LoadDetails();
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
        $this->Set("level", $lvl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("rr", $rr);
    }

    public function disapprove($lvl = 1) {
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen RR yang akan di batalkan !");
            redirect_url("inventory.rr");
            return;
        }

        $infos = array();
        $errors = array();
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        foreach ($ids as $id) {
            $rr = new Rr();
            $rr = $rr->LoadById($id);

            if ($lvl == 1) {
                if ($rr->StatusCode != 2) {
                    $errors[] = sprintf("Dokumen RR: %s tidak diproses karena status sudah bukan APPROVED1 ! Status : %s", $rr->DocumentNo, $rr->GetStatus());
                    continue;
                }
            }elseif ($lvl == 2){
                if ($rr->StatusCode != 3) {
                    $errors[] = sprintf("Dokumen RR: %s tidak diproses karena status sudah bukan APPROVED2 ! Status : %s", $rr->DocumentNo, $rr->GetStatus());
                    continue;
                }
            }else{
                $errors[] = sprintf("Dokumen RR: %s tidak diproses ! Status : %s", $rr->DocumentNo, $rr->GetStatus());
                continue;
            }

            $rr->UpdatedById = $userId;
            $rs = $rr->DisApprove($id,$lvl);
            if ($rs != -1) {
                $infos[] = sprintf("Dokumen RR: %s sudah berhasil di dibatalkan (Disapprove-%d)", $rr->DocumentNo,$lvl);
            } else {
                $errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen RR: %s. Message: %s", $rr->DocumentNo, $this->connector->GetErrorMessage());
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("inventory.rr");
    }

    public function verify($lvl = 2, $rrId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $rr = new Rr();
        if ($rrId > 0 ) {
            $rr = $rr->LoadById($rrId);
            if ($rr == null) {
                $this->persistence->SaveState("error", "Maaf, Data P/R dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.rr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf, Data P/R tidak ditemukan!");
            redirect_url("inventory.rr");
        }

        if (count($this->postData) > 0) {
            $applvl = $this->GetPostValue("AppLevel");
            $rr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($rr->Approve($rrId,$applvl)){
                $this->persistence->SaveState("info", "Proses Approval berhasil!");
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval gagal!");
            }
            redirect_url("inventory.rr");
        }else{
            if ($lvl == 1){
                if ($rr->StatusCode <> 1 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DRAFT-, tidak boleh di-Approve Level-1!");
                    redirect_url("inventory.rr");
                }
            }elseif ($lvl == 2){
                if ($rr->StatusCode <> 2 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DH APPROVED-, tidak boleh di-Approve Level-2!");
                    redirect_url("inventory.rr");
                }
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval tidak valid!");
                redirect_url("inventory.rr");
            }

        }
        // load details
        $rr->LoadDetails();
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
        $this->Set("level", $lvl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("rr", $rr);
    }

    public function unverify($lvl = 2) {
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen RR yang akan di batalkan !");
            redirect_url("inventory.rr");
            return;
        }

        $infos = array();
        $errors = array();
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        foreach ($ids as $id) {
            $rr = new Rr();
            $rr = $rr->LoadById($id);

            if ($lvl == 1) {
                if ($rr->StatusCode != 2) {
                    $errors[] = sprintf("Dokumen RR: %s tidak diproses karena status sudah bukan APPROVED1 ! Status : %s", $rr->DocumentNo, $rr->GetStatus());
                    continue;
                }
            }elseif ($lvl == 2){
                if ($rr->StatusCode != 3) {
                    $errors[] = sprintf("Dokumen RR: %s tidak diproses karena status sudah bukan APPROVED2 ! Status : %s", $rr->DocumentNo, $rr->GetStatus());
                    continue;
                }
            }else{
                $errors[] = sprintf("Dokumen RR: %s tidak diproses ! Status : %s", $rr->DocumentNo, $rr->GetStatus());
                continue;
            }

            $rr->UpdatedById = $userId;
            $rs = $rr->DisApprove($id,$lvl);
            if ($rs != -1) {
                $infos[] = sprintf("Dokumen RR: %s sudah berhasil di dibatalkan (Disapprove-%d)", $rr->DocumentNo,$lvl);
            } else {
                $errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen RR: %s. Message: %s", $rr->DocumentNo, $this->connector->GetErrorMessage());
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("inventory.rr");
    }
}


// End of File: pr_controller.php
