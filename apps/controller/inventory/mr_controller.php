<?php

class MrController extends AppController {
	private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;
    private $userProjectId;

	protected function Initialize() {
		require_once(MODEL . "inventory/mr.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
        $this->userProjectId = $this->persistence->LoadState("project_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		//$settings["columns"][] = array("name" => "c.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a1.project_name", "display" => "Project", "width" => 100);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "MR Number", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.mr_date, '%d-%m-%Y')", "display" => "MR Date", "width" => 80, "sortable" => false);
        $settings["columns"][] = array("name" => "b.dept_name", "display" => "Dept", "width" => 150);
        $settings["columns"][] = array("name" => "a.request_by", "display" => "Request By", "width" => 80);
        $settings["columns"][] = array("name" => "e.short_desc", "display" => "Level", "width" => 100);
        $settings["columns"][] = array("name" => "d.short_desc", "display" => "Progress Status", "width" => 100);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.update_time, '%d-%m-%Y')", "display" => "Last Update", "width" => 80, "sortable" => false);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "Doc Number");
		$settings["filters"][] = array("name" => "b.dept_codo", "display" => "Dept");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			// UI Settings karena kita baru bikin tampilan di Client (agar tidak makan bny CPU cycle di pisah saja)
			$acl = AclManager::GetInstance();
			$settings["title"] = "MR List";
			if ($acl->CheckUserAccess("mr", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.mr/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("mr", "edit", "inventory")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.mr/add/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen MR terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "Apakah anda mau merubah data Dokumen MR yang dipilih ?");
			}
			if ($acl->CheckUserAccess("mr", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.mr/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih Dokumen MR terlebih dahulu sebelum melakukan proses delete !",
											   "Confirm" => "Apakah anda mau menghapus Dokumen MR yang dipilih ?");
			}
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("mr", "view", "inventory")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.mr/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.mr/overview", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("mr", "print", "inventory")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.mr/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
            }
            if ($acl->CheckUserAccess("mr", "print", "inventory")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "inventory.mr/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }
			if ($acl->CheckUserAccess("mr", "approve", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "APPROVAL 1", "Url" => "inventory.mr/approve/1/%s", "Class" => "bt_approve", "ReqId" => 1,
											   "Error" => "Mohon pilih TEPAT satu dokumen MR !\nTidak boleh memilih lebih dari 1 dokumen.",
											   "Confirm" => "Proses Approval Level 1?");
                $settings["actions"][] = array("Text" => "BATAL APPROVAL 1", "Url" => "inventory.mr/batch_disapprove/1/", "Class" => "bt_reject", "ReqId" => 2,
                                                "Error" => "Mohon memilih sekurang-kurangnya satu MR !",
                                                "Confirm" => "Proses Batal Approval Level 1?");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "APPROVAL 2", "Url" => "inventory.mr/approve/2/%s", "Class" => "bt_approve", "ReqId" => 1,
                                                "Error" => "Mohon pilih TEPAT satu dokumen MR !\nTidak boleh memilih lebih dari 1 dokumen.",
                                                "Confirm" => "Proses Approval Level 2?");
                $settings["actions"][] = array("Text" => "BATAL APPROVAL 2", "Url" => "inventory.mr/batch_disapprove/2/", "Class" => "bt_reject", "ReqId" => 2,
                                                "Error" => "Mohon memilih sekurang-kurangnya satu MR !",
                                                "Confirm" => "Proses Batal Approval Level 2?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Kill Session
			$this->persistence->DestroyState("inventory.mr.mr");
		} else {
			// Client sudah meminta data / querying data jadi kita kasi settings untuk pencarian data
			$settings["from"] =
"ic_mr_master AS a
    JOIN cm_project AS a1 ON a.project_id = a1.id
	LEFT JOIN cm_dept AS b ON a.dept_id = b.id
	JOIN cm_company AS c ON b.entity_id = c.entity_id
	JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'mr_status'
	JOIN sys_status_code AS e ON a.req_level = e.code AND e.key = 'mr_req_level'";
			if ($this->userLevel < 5){
			    $settings["where"] = "a.is_deleted = 0 And Locate(a.project_id,".$this->userProjectIds.")";
            }else {
                $settings["where"] = "a.is_deleted = 0 AND b.entity_id = " . $this->userCompanyId;
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}


    public function add($mrId = 0) {
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/units.php");

        $loader = null;
        $mr = new Mr();
        if ($mrId > 0 ) {
            $mr = $mr->LoadById($mrId);
            if ($mr == null) {
                $this->persistence->SaveState("error", "Maaf Data MR dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("inventory.mr");
            }
            if ($mr->StatusCode > 2) {
                $this->persistence->SaveState("error", sprintf("Maaf Mr No. %s sudah berstatus -%s- Tidak boleh diubah lagi..", $mr->DocumentNo,$mr->GetStatus()));
                redirect_url("inventory.mr");
            }
        }else{
            $mr->Date = date('d-m-Y');
        }

        // load details
        $mr->LoadDetails();
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
        $this->Set("projectId", $this->userProjectId);
        $this->Set("activities", $activities);
        $this->Set("units", $units);
        $this->Set("mr", $mr);
    }

    public function proses_master($mrId = 0) {
        $mr = new Mr();
        if (count($this->postData) > 0) {
            $mr->Id = $mrId;
            $mr->EntityId = $this->userCompanyId;
            $mr->ProjectId = $this->GetPostValue("ProjectId");
            $mr->Date = strtotime($this->GetPostValue("MrDate"));
            $mr->DepartmentId = $this->GetPostValue("DepartmentId");
            $mr->ActivityId = $this->GetPostValue("ActivityId");
            $mr->Note = $this->GetPostValue("Note");
            $mr->RequestBy = $this->GetPostValue("RequestBy");
            $mr->DocumentNo = $this->GetPostValue("MrNo");
            $mr->RequestBy = $this->GetPostValue("RequestBy");
            $mr->ReqLevel = $this->GetPostValue("ReqLevel");
            $mr->CreatedById = $this->userUid;
            if ($mrId == 0){
                $mr->StatusCode = 1;
            }else{
                $mr->StatusCode = 2;
            }
            if ($mr->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $mr->DocumentNo = $docCounter->AutoDocNoMr($mr->EntityId, $mr->Date, 1);
                $rs = $mr->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$mr->Id,$mr->DocumentNo);
                }else{
                    printf("ER|A|%d",$mr->Id);
                }
            }else{
                $rs = $mr->Update($mr->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$mr->Id,$mr->DocumentNo);
                }else{
                    printf("ER|U|%d",$mr->Id);
                }
            }
        }else{
            printf("ER|X|%d",$mrId);
        }
    }

    public function add_detail($mrId = null) {
	    $rst = null;
        $mr = new Mr($mrId);
        $mrdetail = new MrDetail();
        $mrdetail->MrId = $mrId;
        $is_item_exist = false;
        if (count($this->postData) > 0) {
            $mrdetail->ItemId = $this->GetPostValue("aItemId");
            $mrdetail->RequestedQty = $this->GetPostValue("aReqQty");
            $mrdetail->UnitId = $this->GetPostValue("aUnitId");
            if ($mrdetail->UnitId == '' || $mrdetail->UnitId == null){
                $mrdetail->UnitId = 0;
            }
            $mrdetail->StsItem = $this->GetPostValue("aStsItem");
            if ($mrdetail->StsItem == '' || $mrdetail->StsItem == null){
                $mrdetail->StsItem = 1;
            }
            $mrdetail->ItemDescription = '-';
            $mrdetail->UomCd = $this->GetPostValue("aUomCd");
            // periksa apa sudah ada item yang sama, kalo ada gabungkan saja
            $mrdetail_exists = new MrDetail();
            $mrdetail_exists = $mrdetail_exists->FindDuplicate($mrdetail->MrId,$mrdetail->ItemId,$mrdetail->UnitId,$mrdetail->ItemDescription);
            if ($mrdetail_exists != null){
                // proses penggabungan disini
                /** @var $mrdetail_exists MrDetail */
                $is_item_exist = true;
                $mrdetail->RequestedQty+= $mrdetail_exists->RequestedQty;
            }
            // insert ke table
            if ($is_item_exist){
                // sudah ada item yg sama gabungkan..
                $rs = $mrdetail->Update($mrdetail_exists->Id);
                if ($rs > 0) {
                    $rst = 'OK|Proses simpan update berhasil!';
                } else {
                    $rst = 'ER|Gagal proses update data!';
                }
            }else {
                // item baru simpan
                $rs = $mrdetail->Insert() == 1;
                if ($rs > 0) {
                    $rst = printf('OK|%s|Proses simpan data berhasil!',$mrdetail->Id);
                } else {
                    $rst = 'ER|Gagal proses simpan data!';
                }
            }
        }else{
            $rst = "ER|No Data posted!";
        }
        print($rst);
    }

    public function edit_detail($dId = null) {
        $rst = null;
        $mrdetail = new MrDetail();
        $mrdetail = $mrdetail->LoadById($dId);
        if (count($this->postData) > 0) {
            $mrdetail->ItemId = $this->GetPostValue("aItemId");
            $mrdetail->RequestedQty = $this->GetPostValue("aReqQty");
            $mrdetail->UnitId = $this->GetPostValue("aUnitId");
            if ($mrdetail->UnitId == '' || $mrdetail->UnitId == null) {
                $mrdetail->UnitId = 0;
            }
            $mrdetail->StsItem = $this->GetPostValue("aStsItem");
            if ($mrdetail->StsItem == '' || $mrdetail->StsItem == null){
                $mrdetail->StsItem = 1;
            }
            $mrdetail->ItemDescription = '-';
            $mrdetail->UomCd = $this->GetPostValue("aUomCd");
            // update ke table
            $rs = $mrdetail->Update($dId);
            if ($rs > 0) {
                $rst = 'OK|Proses update data berhasil!';
            } else {
                $rst = 'ER|Gagal update data!';
            }
        }else{
            $rst = "ER|No Data updated!";
        }
        print($rst);
    }


    public function delete_detail($id) {
        // Cek datanya
        $mrdetail = new MrDetail();
        $mrdetail = $mrdetail->FindById($id);
        if ($mrdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($mrdetail->Delete($id) == 1) {
            printf("Data Detail Mr ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail Mr ID: %d gagal dihapus!",$id);
        }
    }

    public function delete($id) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Maaf anda harus memilih dokumen MR terlebih dahulu.");
            redirect_url("inventory.mr");
            return;
        }

        $mr = new Mr();
        $mr = $mr->LoadById($id);
        if ($mr == null || $mr->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf Dokumen MR yang diminta tidak ditemukan / sudah dihapus.");
            redirect_url("inventory.mr");
            return;
        }
        if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
            // OK Checking Company
            require_once(MODEL . "master/department.php");
            $dept = new Department();
            $dept->FindById($mr->DepartmentId);
            if ($dept->EntityId != $this->userCompanyId) {
                // OK KICK ! Simulate not Found !
                $this->persistence->SaveState("error", "Maaf Dokumen MR yang diminta tidak ditemukan / sudah dihapus.");
                redirect_url("inventory.mr");
                return;
            }
        }
        if ($mr->StatusCode != 1) {
            $this->persistence->SaveState("error", "Maaf Dokumen MR yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
            redirect_url("inventory.mr/view/" . $mr->Id);
            return;
        }

        // Everything is green
        $mr->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
        if ($mr->Delete($mr->Id)) {
            $this->persistence->SaveState("info", sprintf("Dokumen MR: %s sudah berhasil dihapus.", $mr->DocumentNo));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen MR: %s ! Harap hubungi system administrator.<br />Error: %s", $mr->DocumentNo, $this->connector->GetErrorMessage()));
        }

        redirect_url("inventory.mr");
    }

    public function approve($lvl = 0, $id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih Dokumen MR terlebih dahulu sebelum proses verifikasi approval !");
            redirect_url("inventory.mr");
            return;
        }

        $mr = new Mr();
        $mr = $mr->LoadById($id);
        if ($mr == null || $mr->IsDeleted) {
            $this->persistence->SaveState("error", "Harap memilih Dokumen MR yang diminta tidak dapat ditemukan / sudah dihapus !");
            redirect_url("inventory.mr");
            return;
        }

        if ($lvl == 1){
            if ($mr->StatusCode > 2) {
                $this->persistence->SaveState("error", "MR No. ".$mr->DocumentNo." sudah berstatus -".$mr->GetStatus()."-");
                redirect_url("inventory.mr");
                return;
            }
        }

        if ($lvl == 2){
            if ($mr->StatusCode > 3) {
                $this->persistence->SaveState("error", "MR No. ".$mr->DocumentNo." sudah berstatus -".$mr->GetStatus()."-");
                redirect_url("inventory.mr");
                return;
            }
        }

        if ($mr->StatusCode > 3) {
            $this->persistence->SaveState("error", "MR No. ".$mr->DocumentNo." sudah berstatus -".$mr->GetStatus()."-");
            redirect_url("inventory.mr");
            return;
        }

        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/activity.php");

        if (count($this->postData) > 0) {
            if ($lvl == 1) {
                $mr->ApprovebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            }else{
                $mr->Approve2byId = AclManager::GetInstance()->GetCurrentUser()->Id;
            }

            $data = $this->GetPostValue("data", array());
            $quantities = $this->GetPostValue("QtyApprove", array());
            $max = count($quantities);

            for ($i = 0; $i < $max; $i++) {
                $tokens = explode("|", $data[$i]);
                $detail = new MrDetail();
                $detail->Id = $tokens[0];
                $detail->ItemCode = $tokens[1];
                $detail->ItemName = $tokens[2];
                $detail->RequestedQty = $tokens[3];
                $detail->UomCd = $tokens[4];
                $detail->ItemDescription = $tokens[5];
                $detail->ApprovedQty = $quantities[$i];

                $mr->MrDetails[] = $detail;
            }

            $this->connector->BeginTransaction();
            if ($this->doApprove($mr, $lvl)) {
                $this->connector->CommitTransaction();
                $this->persistence->SaveState("info", sprintf("Dokumen MR: %s sudah berhasil di approve", $mr->DocumentNo));
                redirect_url("inventory.mr");
            } else {
                if ($this->connector->GetHasError()) {
                    $this->Set("error", "Unknown Database Error: " . $this->connector->GetErrorMessage());
                }
                $this->connector->RollbackTransaction();
            }
        } else {
            $mr->LoadDetails();
            if ($mr->StatusCode == 1) {
                foreach ($mr->MrDetails as $detail) {
                    $detail->ApprovedQty = $detail->RequestedQty;
                }
            }
        }

        $company = new Company();
        $company = $company->FindById($mr->EntityId);
        $project = new Project($mr->ProjectId);
        $department = new Department($mr->DepartmentId);
        $activity = new Activity($mr->ActivityId);

        $this->Set("company", $company);
        $this->Set("project", $project);
        $this->Set("department", $department);
        $this->Set("activity", $activity);
        $this->Set("mr", $mr);
        $this->Set("level", $lvl);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
    }

    private function doApprove(Mr $mr, $lvl) {
        // OK... let approve IT
        $rs = $mr->Approve($mr->Id, $lvl);
        if ($rs == -1) {
            $this->Set("error", sprintf("Gagal approve master MR: %s. Message: %s", $mr->DocumentNo, $this->connector->GetErrorMessage()));
            return false;
        }

        foreach ($mr->MrDetails as $detail) {
            $rs = $detail->Approve($detail->Id);
            if ($rs == -1) {
                $this->Set("error", sprintf("Gagal Approve Item MR: %s (Kode: %s). Message: %s", $detail->ItemName, $detail->ItemCode, $this->connector->GetErrorMessage()));
                return false;
            }
        }

        return true;
    }

    public function batch_approve() {
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di approve !");
            redirect_url("inventory.mr");
            return;
        }

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $mr = new Mr();
            $mr = $mr->LoadById($id);

            if ($mr->StatusCode != 1) {
                $errors[] = sprintf("Dokumen MR: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $mr->DocumentNo, $mr->GetStatus());
                continue;
            }
            if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
                if ($mr->EntityId != $this->userCompanyId) {
                    // Trying to access other Company data ! Bypass it..
                    continue;
                }
            }

            $mr->LoadDetails();
            // OK Secara otomatis ApprovedQty = RequestedQty
            foreach ($mr->MrDetails as $detail) {
                $detail->ApprovedQty = $detail->RequestedQty;
            }

            $this->connector->BeginTransaction();
            $rs = $this->doApprove($mr);
            if ($rs) {
                $this->connector->CommitTransaction();
                $infos[] = sprintf("Dokumen MR: %s sudah berhasil di approve", $mr->DocumentNo);
            } else {
                $errors[] =  sprintf("Gagal Approve Dokumen MR: %s. Message: %s", $mr->DocumentNo, $this->connector->GetErrorMessage());
                $this->connector->RollbackTransaction();
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
        }
        redirect_url("inventory.mr");
    }

    public function view($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih Dokumen MR terlebih dahulu sebelum proses verifikasi approval !");
            redirect_url("inventory.mr");
            return;
        }

        $mr = new Mr();
        $mr = $mr->LoadById($id);
        if ($mr == null || $mr->IsDeleted) {
            $this->persistence->SaveState("error", "Harap memilih Dokumen MR yang diminta tidak dapat ditemukan / sudah dihapus !");
            redirect_url("inventory.mr");
            return;
        }
        if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
            if ($mr->EntityId != $this->userCompanyId) {
                // WOW coba akses data lintas Company ? Simulate not found !
                $this->persistence->SaveState("error", "Dokumen Item Issue yang diminta tidak dapat ditemukan / sudah dihapus !");
                redirect_url("inventory.mr");
                return;
            }
        }

        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/activity.php");
        // Load MR Details kalau MR memang bisa dilihat
        $mr->LoadDetails();

        $company = new Company();
        $company = $company->FindById($mr->EntityId);
        $project = new Project($mr->ProjectId);
        $department = new Department($mr->DepartmentId);
        $activity = new Activity($mr->ActivityId);

        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("company", $company);
        $this->Set("project", $project);
        $this->Set("department", $department);
        $this->Set("activity", $activity);
        $this->Set("mr", $mr);

        if ($this->persistence->StateExists("info")) {
            $this->Set("info", $this->persistence->LoadState("info"));
            $this->persistence->DestroyState("info");
        }
        if ($this->persistence->StateExists("error")) {
            $this->Set("error", $this->persistence->LoadState("error"));
            $this->persistence->DestroyState("error");
        }
    }

    public function batch_disapprove($lvl) {
        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen MR yang akan di batalkan !");
            redirect_url("inventory.mr");
            return;
        }

        $infos = array();
        $errors = array();
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        foreach ($ids as $id) {
            $mr = new Mr();
            $mr = $mr->LoadById($id);

            if ($mr->StatusCode > 4) {
                $errors[] = sprintf("Dokumen MR: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $mr->DocumentNo, $mr->GetStatus());
                continue;
            }

            if ($lvl == 1){
                if ($mr->StatusCode != 3) {
                    $errors[] = sprintf("Dokumen MR: %s tidak diproses karena status bukan APPROVED 1! Status Dokumen: %s", $mr->DocumentNo, $mr->GetStatus());
                    continue;
                }
            }else{
                if ($mr->StatusCode != 4) {
                    $errors[] = sprintf("Dokumen MR: %s tidak diproses karena status bukan APPROVED 2! Status Dokumen: %s", $mr->DocumentNo, $mr->GetStatus());
                    continue;
                }
            }

            $mr->UpdatedById = $userId;
            $rs = $mr->DisApprove($id,$lvl);
            if ($rs != -1) {
                $infos[] = sprintf("Dokumen MR: %s sudah berhasil di dibatalkan (disapprove)", $mr->DocumentNo);
            } else {
                $errors[] =  sprintf("Gagal Membatalkan / Disapprove Dokumen MR: %s. Message: %s", $mr->DocumentNo, $this->connector->GetErrorMessage());
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
        }
        redirect_url("inventory.mr");
    }

    public function unfinished() {
        require_once(MODEL . "master/project.php");
        $mr = new Mr();
        if (count($this->postData) > 0) {
            $projectId = $this->GetPostValue("projectId");
            $processDate = $this->GetPostValue("processDate");
            $processType = $this->GetPostValue("processType");
            if ($projectId == 0) {
                $projectId = $this->userProjectId;
            }
            if ($processType == 1){
                $report = $mr->GetUnfinisedMrDetails($projectId);
            }else{
                $report = $mr->GetUnfinisedMrSummary($projectId);
            }
        } else {
            $projectId = $this->userProjectId;
            $processDate = date('d-m-Y');
            $processType = 1;
            $report = $mr->GetUnfinisedMrDetails($projectId);
        }
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }

        $this->Set("projectId", $projectId);
        $this->Set("processDate", $processDate);
        $this->Set("processType", $processType);
        $this->Set("projects", $projects);
        $this->Set("report", $report);
        $this->Set("userLevel", $this->userLevel);
    }

    public function search_unfinished() {
        require_once(MODEL . "master/project.php");
        $project = new Project();
        if (count($this->getData) > 0) {
            $projectId = $this->GetGetValue("projectId");
            if ($projectId > 0) {
                $project = new Project();
                $project = $project->LoadById($projectId);
                $mr = new Mr();
                $report = $mr->LoadUnfinishedMr($projectId, true);
            }else {
                $mr = new Mr();
                $report = $mr->LoadUnfinishedMr($this->userCompanyId, false);
            }
        } else {
            $report = null;
            $projectId = 0;
        }
        //load project
        $project = new Project();
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }

        $this->Set("projectId", $projectId);
        $this->Set("projects", $projects);
        $this->Set("report", $report);
        $this->Set("userLevel", $this->userLevel);
    }

    public function rpt_recap_item() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Mohon memilih dokumen MR terlebih dahulu");
            redirect_url("inventory.mr/search_unfinished");
            return;
        }

        require_once(MODEL . "inventory/stock.php");
        $warehouseId = $this->GetGetValue("warehouse");
        $output = $this->GetGetValue("output", "web");

        $excelLink = sprintf("inventory.mr/rpt_recap_item/?id[]=%s&warehouse=%s&output=xls", implode("&id[]=", $ids), $warehouseId);
        if ($warehouseId == "") {
            $warehouse = null;
        } else {
            require_once(MODEL . "inventory/warehouse.php");
            $warehouse = new Warehouse();
            $warehouse = $warehouse->LoadById($warehouseId);
        }

        $query =
            "SELECT a.item_id, a.item_description, a.app_qty, a.uom_cd, b.doc_no, b.mr_date, c.item_code, c.item_name, d.qty_stock, d.uom_cd AS stock_uom_cd
FROM ic_mr_detail AS a
	JOIN ic_mr_master AS b ON a.mr_master_id = b.id
	JOIN ic_item_master AS c ON a.item_id = c.id
	LEFT JOIN (
		%s
	) AS d ON a.item_id = d.item_id
WHERE b.id IN ?id
ORDER BY a.item_id, b.mr_date";

        if ($warehouseId == "") {
            // Cari di semua gudang Company
            $this->connector->CommandText = sprintf($query, Stock::QUERY_STOCK_BY_SBU);
            $this->connector->AddParameter("?sbu", $this->userCompanyId);
        } else {
            // Cari di gudang specific
            $this->connector->CommandText = sprintf($query, Stock::QUERY_STOCK_BY_WAREHOUSE);
            $this->connector->AddParameter("?warehouseId", $warehouseId);
        }
        $this->connector->AddParameter("?date", date(SQL_DATETIME));
        $this->connector->AddParameter("?id", $ids);
        $rs = $this->connector->ExecuteQuery();

        $this->Set("output", $output);
        $this->Set("warehouse", $warehouse);
        $this->Set("rs", $rs);
        $this->Set("excelLink", $excelLink);
    }

    //buat halaman search data
    public function overview() {

        require_once(MODEL. "inventory/mr.php");
        require_once(MODEL. "master/project.php");
        require_once(MODEL. "status_code.php");

        $mr = new Mr();

        if (count($this->getData) > 0) {
            $projectId = $this->GetGetValue("projectId");
            $status = $this->GetGetValue("status");
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText = "SELECT a.*, b.dept_name, c.entity_cd AS entity, d.short_desc AS status_name,a1.project_cd,a1.project_name
                                            FROM ic_mr_master AS a
                                            LEFT JOIN cm_project AS a1 ON a.project_id = a1.id
                                            LEFT JOIN cm_dept AS b ON a.dept_id = b.id
                                            JOIN cm_company AS c ON a.entity_id = c.entity_id
                                            JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'mr_status'
                                            WHERE a.is_deleted = 0";

            $this->connector->CommandText .= " AND c.entity_id = ?entity";
            $this->connector->AddParameter("?entity", $this->userCompanyId);

            if ($projectId > 0) {
                $this->connector->CommandText .= " AND a.project_id = ?project";
                $this->connector->AddParameter("?project", $projectId);
            }
            if ($status != -1) {
                $this->connector->CommandText .= " AND a.status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.mr_date >= ?start
                                               AND a.mr_date <= ?end
                                               ORDER BY a.mr_date ASC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $projectId = null;
            $status = null;
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

        $syscode = new StatusCode();
        $this->Set("mr_status", $syscode->LoadMrStatus());

        $temp = $syscode->FindBy("mr_status", $status);
        $statusName = $temp != null ? $temp->ShortDesc : "ALL STATUS";
        $this->Set("statusName", $statusName);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
        $this->Set("userLevel", $this->userLevel);
    }

    //proses cetak form MR
    public function doc_print($output){
        require_once(MODEL. "inventory/mr.php");

        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
            redirect_url("inventory.mr");
            return;
        }

        $report = array();
        foreach ($ids as $id) {
            $mr = new Mr();
            $mr = $mr->LoadById($id);
            $mr->LoadDetails();
            $mr->LoadUsers();

            $report[] = $mr;
        }
        require_once(MODEL . "master/company.php");
        $company = new Company($this->userCompanyId);
        $this->Set("company", $company);
        $this->Set("report", $report);
        $this->Set("output", $output);
    }

    public function process(){
        if (count($this->postData) > 0) {
            $pri = $this->GetPostValue("projectId");
            $dte = $this->GetPostValue("processDate");
            $ipr = $this->GetPostValue("cpr", array());
            $iis = $this->GetPostValue("cis", array());
            if (count($ipr) + count($iis) == 0) {
                $this->persistence->SaveState("error", "Tidak ada data MR yang diproses !");
                redirect_url("inventory.mr/unfinished");
            } else {
                //process generate PR
                if (count($ipr) > 0) {
                    require_once(MODEL . "purchase/pr.php");
                    require_once(MODEL . "common/doc_counter.php");
                    $pr = new Pr();
                    $pr->ProjectId = $pri;
                    $pr->Date = $dte;
                    $pr->EntityId = $this->userCompanyId;
                    $pr->DeptId = 2;
                    $pr->Note = '* Generate From MR Process';
                    $pr->CreatedById = $this->userUid;
                    $docCounter = new DocCounter();
                    $pr->DocumentNo = $docCounter->AutoDocNoPr($this->userCompanyId, $dte, 1);
                    if ($pr->Insert() == 1) {
                        //generate detail PR
                        $seq = 0;
                        foreach ($ipr as $dtx) {
                            $dta = explode('|',$dtx);
                            $id = $dta[0];
                            $qs = $dta[1];
                            $dmr = new Mr();
                            $dmr = $dmr->GetUnfinisedMrDetail($id);
                            if ($dmr != null) {
                                $seq++;
                                $prd = new PrDetail();
                                $prd->PrId = $pr->Id;
                                $prd->Sequence = $seq;
                                if($qs == 'G') {
                                    $prd->ItemId = $dmr["item_id"];
                                }else{
                                    $prd->ItemId = $dmr["stock_item_id"];
                                }
                                $prd->MrDetailId = $dmr["id"];
                                $prd->Qty = $dmr["mr_qty"];
                                $prd->UomCd = $dmr["uom_cd"];
                                $prd->ItemDescription = '-';
                                if ($prd->Insert()){
                                    //update mr qty
                                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.pr_qty = a.pr_qty + ?qty Where a.id = ?id";
                                    $this->connector->AddParameter("?qty", $prd->Qty);
                                    $this->connector->AddParameter("?id", $prd->MrDetailId);
                                    $rs = $this->connector->ExecuteNonQuery();
                                }
                            }
                        }
                        $this->persistence->SaveState("info", "Berhasil generate P/R otomatis!");
                        redirect_url("inventory.mr/unfinished");
                    }else{
                        $this->persistence->SaveState("error", "Gagal generate P/R otomatis!");
                        redirect_url("inventory.mr/unfinished");
                    }
                }

                //process generate Issue
                if (count($iis) > 0) {
                    require_once(MODEL . "inventory/item_issue.php");
                    require_once(MODEL . "common/doc_counter.php");
                    $is = new ItemIssue();
                    $is->ProjectId = $pri;
                    $is->Date = $dte;
                    $is->EntityId = $this->userCompanyId;
                    $is->DepartmentId = 2;
                    $is->ActivityId = 3;
                    $is->Note = '* Generate From MR Process';
                    $is->CreatedById = $this->userUid;
                    $docCounter = new DocCounter();
                    $is->DocumentNo = $docCounter->AutoDocNoIs($this->userCompanyId, $dte, 1);
                    if ($is->Insert() == 1) {
                        //generate detail IS
                        $seq = 0;
                        foreach ($iis as $dtx) {
                            $dta = explode('|',$dtx);
                            $id = $dta[0];
                            $qs = $dta[1];
                            $dmr = new Mr();
                            $dmr = $dmr->GetUnfinisedMrDetail($id);
                            if ($dmr != null) {
                                $seq++;
                                $isd = new ItemIssueDetail();
                                $isd->IsId = $is->Id;
                                $isd->Sequence = $seq;
                                if($qs == 'G') {
                                    $isd->ItemId = $dmr["item_id"];
                                }else{
                                    $isd->ItemId = $dmr["stock_item_id"];
                                }
                                $isd->MrDetailId = $dmr["id"];
                                $isd->Qty = $dmr["mr_qty"];
                                $isd->UomCd = $dmr["uom_cd"];
                                $isd->ItemDescription = '-';
                                $isd->UnitId = $dmr["unit_id"];
                                if ($isd->Insert()){
                                    //update mr qty
                                    $this->connector->CommandText = "Update ic_mr_detail AS a Set a.iss_qty = a.iss_qty + ?qty Where a.id = ?id";
                                    $this->connector->AddParameter("?qty", $isd->Qty);
                                    $this->connector->AddParameter("?id", $isd->MrDetailId);
                                    $rs = $this->connector->ExecuteNonQuery();
                                }
                            }
                        }
                        $this->persistence->SaveState("info", "Berhasil generate Item Issue otomatis!");
                        redirect_url("inventory.mr/unfinished");
                    }else{
                        $this->persistence->SaveState("error", "Gagal generate Item Issue otomatis!");
                        redirect_url("inventory.mr/unfinished");
                    }
                }
            }
        }else{
            $this->persistence->SaveState("error", "Tidak ada data MR yang diproses !");
            redirect_url("inventory.mr/unfinished");
        }
    }
}


// End of File: mr_controller.php
