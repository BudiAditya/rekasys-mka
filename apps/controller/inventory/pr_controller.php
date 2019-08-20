<?php
//namespace Inventory;

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
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.pr_date, '%d-%m-%Y')", "display" => "PR Date", "width" => 80, "sortable" => false);
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Req Level", "width" => 100);
        $settings["columns"][] = array("name" => "b.short_desc", "display" => "Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d-%m-%Y')", "display" => "Last Update", "width" => 80, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "PR Number");
		$settings["filters"][] = array("name" => "b.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$settings["title"] = "Purchase Request List";
			$acl = AclManager::GetInstance();

			if ($acl->CheckUserAccess("pr", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.pr/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("pr", "edit", "inventory")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.pr/add/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih Dokumen PR terlebih dahulu sebelum melakukan proses edit !",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("pr", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.pr/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih Dokumen PR terlebih dahulu sebelum melakukan proses delete !\nHarap memilih tepat 1 dokumen dan jangan lebih dari 1.",
					"Confirm" => "Apakah anda mau menghapus Dokumen PR yang dipilih ?");
			}
            if ($acl->CheckUserAccess("pr", "view", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.pr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.pr/overview", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("pr", "doc_print", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "XLS Print", "Url" => "inventory.pr/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
                $settings["actions"][] = array("Text" => "PDF Print", "Url" => "inventory.pr/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }
            if ($acl->CheckUserAccess("pr", "approve", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "APPROVAL DEPT HEAD", "Url" => "inventory.pr/approve/1/%s", "Class" => "bt_approve", "ReqId" => 1,
                    "Error" => "Mohon pilih TEPAT satu dokumen PR !\nTidak boleh memilih lebih dari 1 dokumen.",
                    "Confirm" => "Proses Approval Level 1?");
                $settings["actions"][] = array("Text" => "BATAL APPROVAL DEPT HEAD", "Url" => "inventory.pr/disapprove/1/", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih sekurang-kurangnya satu PR !",
                    "Confirm" => "Proses Batal Approval Level 1?");
            }
            if ($acl->CheckUserAccess("pr", "verify", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "APPROVAL PM", "Url" => "inventory.pr/verify/2/%s", "Class" => "bt_approve", "ReqId" => 1,
                    "Error" => "Mohon pilih TEPAT satu dokumen PR !\nTidak boleh memilih lebih dari 1 dokumen.",
                    "Confirm" => "Proses Approval Level 2?");
                $settings["actions"][] = array("Text" => "BATAL APPROVAL PM", "Url" => "inventory.pr/unverify/2/", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih sekurang-kurangnya satu PR !",
                    "Confirm" => "Proses Batal Approval Level 2?");
            }
			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;

		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
			$settings["from"] = "vw_ic_pr_master AS a JOIN sys_status_code AS b ON a.status = b.code AND b.key = 'pr_status' LEFT JOIN sys_status_code AS c ON a.req_level = c.code AND c.key = 'mr_req_level'";
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
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen PR terlebih dahulu.");
			redirect_url("inventory.pr");
			return;
		}

		$pr = new Pr();
		$pr = $pr->LoadById($id);
		if ($pr == null || $pr->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen PR yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("inventory.pr");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($pr->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen PR yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("inventory.pr");
				return;
			}
		}
		if ($pr->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen PR yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("inventory.pr");
			return;
		}
        $details = $pr->LoadDetails();
        if (count($details) > 0){
            $this->persistence->SaveState("error", "PR No: ". $pr->DocumentNo." Harap hapus dulu Detail Item Requestnya");
            redirect_url("inventory.pr");
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
			redirect_url("inventory.pr");
		}

		// Step 2: Hapus Link
		$this->connector->CommandText = "UPDATE ic_link_mr_pr SET is_deleted = 1 WHERE pr_id = ?prId";
		$this->connector->AddParameter("?prId", $pr->Id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == -1) {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen PR: %s ! Gagal Hapus Link MR-PR<br /> Harap hubungi system administrator.<br />Error: %s", $pr->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
			redirect_url("inventory.pr");
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

		redirect_url("inventory.pr");
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
			redirect_url("inventory.pr");
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
                $this->persistence->SaveState("error", "Maaf Data Request dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.pr");
            }
            if ($pr->StatusCode > 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $pr->DocumentNo,$pr->GetStatus()));
                redirect_url("inventory.pr");
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
            $pr->ReqLevel = $this->GetPostValue("ReqLevel");
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

    public function edit_detail($dId = null) {
        $rst = null;
        $prdetail = new PrDetail();
        $prdetail = $prdetail->LoadById($dId);
        if (count($this->postData) > 0) {
            $prdetail->PrId = $this->GetPostValue("aPrId");
            $prdetail->ItemId = $this->GetPostValue("aItemId");
            $prdetail->Qty = $this->GetPostValue("aPrQty");
            $prdetail->ItemDescription = '-';
            $prdetail->MrDetailId = $this->GetPostValue("aMrDetailId");
            $prdetail->UomCd = $this->GetPostValue("aUomCd");
            $xQty = $this->GetPostValue("xPrQty");
            // item baru simpan
            $rs = $prdetail->Update($dId);
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses update data berhasil!',$prdetail->Id);
                //creat mr link
                if ($prdetail->MrDetailId > 0) {
                    //update mr qty
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty - ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $xQty);
                    $this->connector->AddParameter("?id", $prdetail->MrDetailId);
                    $rs = $this->connector->ExecuteNonQuery();
                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty + ?qty Where a.id = ?id";
                    $this->connector->AddParameter("?qty", $prdetail->Qty);
                    $this->connector->AddParameter("?id", $prdetail->MrDetailId);
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
                $this->persistence->SaveState("error", "Maaf Data Request dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.pr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Request tidak ditemukan!");
            redirect_url("inventory.pr");
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
                $this->persistence->SaveState("error", "Maaf Data Request dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.pr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf,Data Item Request tidak ditemukan!");
            redirect_url("inventory.pr");
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
                redirect_url("inventory.pr");
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

    public function approve($lvl = 1, $prId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $pr = new Pr();
        if ($prId > 0 ) {
            $pr = $pr->LoadById($prId);
            if ($pr == null) {
                $this->persistence->SaveState("error", "Maaf, Data P/R dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.pr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf, Data P/R tidak ditemukan!");
            redirect_url("inventory.pr");
        }

        if (count($this->postData) > 0) {
            $applvl = $this->GetPostValue("AppLevel");
            $pr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($pr->Approve($prId,$applvl)){
                $this->persistence->SaveState("info", "Proses Approval berhasil!");
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval gagal!");
            }
            redirect_url("inventory.pr");
        }else{
            if ($lvl == 1){
                if ($pr->StatusCode <> 1 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DRAFT-, tidak boleh di-Approve Level-1!");
                    redirect_url("inventory.pr");
                }
            }elseif ($lvl == 2){
                if ($pr->StatusCode <> 2 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DH APPROVED-, tidak boleh di-Approve Level-2!");
                    redirect_url("inventory.pr");
                }
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval tidak valid!");
                redirect_url("inventory.pr");
            }

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
        $this->Set("level", $lvl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("pr", $pr);
    }

    public function verify($lvl = 2, $prId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");

        $loader = null;
        $pr = new Pr();
        if ($prId > 0 ) {
            $pr = $pr->LoadById($prId);
            if ($pr == null) {
                $this->persistence->SaveState("error", "Maaf, Data P/R dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.pr");
            }
        }else{
            $this->persistence->SaveState("error", "Maaf, Data P/R tidak ditemukan!");
            redirect_url("inventory.pr");
        }

        if (count($this->postData) > 0) {
            $applvl = $this->GetPostValue("AppLevel");
            $pr->ApprovedById = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($pr->Approve($prId,$applvl)){
                $this->persistence->SaveState("info", "Proses Approval berhasil!");
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval gagal!");
            }
            redirect_url("inventory.pr");
        }else{
            if ($lvl == 1){
                if ($pr->StatusCode <> 1 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DRAFT-, tidak boleh di-Approve Level-1!");
                    redirect_url("inventory.pr");
                }
            }elseif ($lvl == 2){
                if ($pr->StatusCode <> 2 ){
                    $this->persistence->SaveState("error", "P/R Tidak berstatus -DH APPROVED-, tidak boleh di-Approve Level-2!");
                    redirect_url("inventory.pr");
                }
            }else{
                $this->persistence->SaveState("error", "Maaf, Proses Approval tidak valid!");
                redirect_url("inventory.pr");
            }

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
        $this->Set("level", $lvl);
        $this->Set("departments", $departments);
        $this->Set("projects", $projects);
        $this->Set("pr", $pr);
    }

    public function disapprove($lvl = 0) {
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen PR yang akan di batalkan !");
            redirect_url("inventory.pr");
            return;
        }

        $infos = array();
        $errors = array();
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        foreach ($ids as $id) {
            $pr = new Pr();
            $pr = $pr->LoadById($id);

            if ($lvl == 1) {
                if ($pr->StatusCode != 2) {
                    $errors[] = sprintf("Dokumen PR: %s tidak diproses karena status sudah bukan APPROVED1 ! Status : %s", $pr->DocumentNo, $pr->GetStatus());
                    continue;
                }
            }elseif ($lvl == 2){
                if ($pr->StatusCode != 3) {
                    $errors[] = sprintf("Dokumen PR: %s tidak diproses karena status sudah bukan APPROVED2 ! Status : %s", $pr->DocumentNo, $pr->GetStatus());
                    continue;
                }
            }else{
                $errors[] = sprintf("Dokumen PR: %s tidak diproses ! Status : %s", $pr->DocumentNo, $pr->GetStatus());
                continue;
            }

            $pr->UpdatedById = $userId;
            $rs = $pr->DisApprove($id,$lvl);
            if ($rs != -1) {
                $infos[] = sprintf("Dokumen PR: %s sudah berhasil di dibatalkan (Disapprove-%d)", $pr->DocumentNo,$lvl);
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
        redirect_url("inventory.pr");
    }

    public function unverify($lvl = 2) {
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen PR yang akan di batalkan !");
            redirect_url("inventory.pr");
            return;
        }

        $infos = array();
        $errors = array();
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        foreach ($ids as $id) {
            $pr = new Pr();
            $pr = $pr->LoadById($id);

            if ($lvl == 1) {
                if ($pr->StatusCode != 2) {
                    $errors[] = sprintf("Dokumen PR: %s tidak diproses karena status sudah bukan APPROVED1 ! Status : %s", $pr->DocumentNo, $pr->GetStatus());
                    continue;
                }
            }elseif ($lvl == 2){
                if ($pr->StatusCode != 3) {
                    $errors[] = sprintf("Dokumen PR: %s tidak diproses karena status sudah bukan APPROVED2 ! Status : %s", $pr->DocumentNo, $pr->GetStatus());
                    continue;
                }
            }else{
                $errors[] = sprintf("Dokumen PR: %s tidak diproses ! Status : %s", $pr->DocumentNo, $pr->GetStatus());
                continue;
            }

            $pr->UpdatedById = $userId;
            $rs = $pr->DisApprove($id,$lvl);
            if ($rs != -1) {
                $infos[] = sprintf("Dokumen PR: %s sudah berhasil di dibatalkan (Disapprove-%d)", $pr->DocumentNo,$lvl);
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
        redirect_url("inventory.pr");
    }
}


// End of File: pr_controller.php
