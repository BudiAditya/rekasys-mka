<?php

class IcObalController extends AppController {
	private $userCompanyId;
	private $userProjectIds;
    private $userProjectId;
	private $userLevel;

	protected function Initialize() {
		require_once(MODEL . "inventory/icobal.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userProjectId = $this->persistence->LoadState("project_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.project_name", "display" => "Project", "width" => 100);
		$settings["columns"][] = array("name" => "c.item_code", "display" => "Item Code", "width" => 100);
        $settings["columns"][] = array("name" => "c.part_no", "display" => "Part Number", "width" => 120);
		$settings["columns"][] = array("name" => "c.item_name", "display" => "Item Name", "width" => 250);
		$settings["columns"][] = array("name" => "c.uom_cd", "display" => "UOM", "width" => 50);
        $settings["columns"][] = array("name" => "Format(a.qty,0)", "display" => "QTY", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "Format(a.price,0)", "display" => "Price", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.opn_date", "display" => "Opn Date", "width" => 60);
        $settings["columns"][] = array("name" => "CASE WHEN a.opn_status = 1 THEN 'Posted' ELSE 'Draft' END", "display" => "Status", "width" => 60);

		$settings["filters"][] = array("name" => "b.project_name", "display" => "Project");
		$settings["filters"][] = array("name" => "c.item_code", "display" => "Item Code");
		$settings["filters"][] = array("name" => "c.part_no", "display" => "Part Number");
        $settings["filters"][] = array("name" => "c.item_name", "display" => "Item Name");

		if (!$router->IsAjaxRequest) {
			// UI Settings
			$acl = AclManager::GetInstance();
			$settings["title"] = "Inventory Opening Balance";
			if ($acl->CheckUserAccess("icobal", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.icobal/add", "Class" => "bt_add", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "Upload Data", "Url" => "inventory.icobal/upload", "Class" => "bt_upload1", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("icobal", "edit", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.icobal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih data terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "Apakah anda mau merubah data yang dipilih ?");
			}

            if ($acl->CheckUserAccess("item", "view", "inventory")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.icobal/view/%s", "Class" => "bt_view", "ReqId" => 1, "Confirm" => "");
            }

			if ($acl->CheckUserAccess("icobal", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.icobal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih data terlebih dahulu sebelum melakukan proses delete !",
											   "Confirm" => "Apakah anda mau menghapus data yang dipilih ?");
			}
            if ($acl->CheckUserAccess("icobal", "posting", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Posting", "Url" => "inventory.icobal/posting/", "Class" => "bt_approve", "ReqId" => 0,
                    "Confirm" => "Proses ini akan mem-posting Saldo Awal Inventory -> Saldo Awal Account Persediaan\nLanjutkan proses?");
                $settings["actions"][] = array("Text" => "Un-Posting", "Url" => "inventory.icobal/unposting/", "Class" => "bt_reject", "ReqId" => 0,
                    "Confirm" => "Proses ini akan membatalkan posting Saldo Awal Inventory\nLanjutkan proses?");
            }
            if ($acl->CheckUserAccess("item", "view", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.icobal/overview", "Class" => "bt_report", "ReqId" => 0, "Confirm" => "");
            }
			$settings["def_filter"] = 1;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ic_opening_balance AS a JOIN cm_project AS b ON a.project_id = b.id JOIN ic_item_master AS c ON a.item_id = c.id";
			if ($this->userLevel < 5){
			    //And Locate(a.id,".$allowedProjectIds.")
                $settings["where"] = "a.is_deleted = 0 And Locate(a.project_id,".$this->userProjectIds.")";
            }else {
                $settings["where"] = "a.is_deleted = 0 And b.entity_id = " . $this->userCompanyId;
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
		require_once(MODEL . "inventory/item.php");
		$icobal = new IcObal();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$icobal->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
			$icobal->ProjectId = $this->GetPostValue("ProjectId");
            $icobal->OpnDate = strtotime($this->GetPostValue("OpnDate"));
			$icobal->ItemId = $this->GetPostValue("ItemId");
			$icobal->Price = $this->GetPostValue("Price");
            $icobal->Qty = $this->GetPostValue("Qty");

			if ($this->ValidateData($icobal)) {
			    $items = new Item();
			    $items = $items->FindById($icobal->ItemId);
			    if ($items == null){
                    $this->persistence->SaveState("error", sprintf("Data Item: %s tidak ditemukan!", $icobal->ItemId));
                }else {
			        $icobal->ItemCode = $items->ItemCode;
                    if ($icobal->Insert() == 1) {
                        $this->persistence->SaveState("info", sprintf("Data Inventory Opening: '%s - %s telah berhasil disimpan.", $items->ItemCode, $items->ItemName));
                        redirect_url("inventory.icobal");
                    } else {
                        if ($this->connector->GetHasError()) {
                            if ($this->connector->IsDuplicateError()) {
                                $this->Set("error", sprintf("Item Code: '%s' telah ada pada database !", $icobal->ItemCode));
                            } else {
                                $this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
                            }
                        }
                    }
                }
			}
		}else{
		    $company = new Company($this->userCompanyId);
            $icobal->ProjectId = $this->userProjectId;
		    $icobal->OpnDate = strtotime($company->StartDate);
        }

		// load data company for combo box
		$projects = new Project();
		if ($this->userLevel < 5) {
            $projects = $projects->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $projects->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
		$items = new Item();
		$items = $items->LoadByEntityId($this->userCompanyId);
        $this->Set("items", $items);
		// untuk kirim variable ke view
		$this->Set("icobal", $icobal);
	}

	private function ValidateData(IcObal $icobal) {
	    //validasi data
		if ($icobal->ItemId == null || $icobal->ItemId == 0) {
			$this->Set("error", "Item Code belum diisi!");
			return false;
		}

		if ($icobal->OpnDate == null) {
			$this->Set("error", "Tanggal Opname belum diisi!");
			return false;
		}
		if ($icobal->ProjectId == null) {
			$this->Set("error", "Project/Warehouse belum dipilih!");
			return false;
		}
        if ($icobal->Qty == null || $icobal->Qty == 0) {
            $this->Set("error", "Qty Opening belum diisi!");
            return false;
        }

		return true;
	}

    public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Maaf anda harus memilih data terlebih dahulu !");
            redirect_url("inventory.icobal");
            return;
        }

        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "inventory/item.php");
        $icobal = new IcObal();

        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $icobal->Id = $id;
            $icobal->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            $icobal->ProjectId = $this->GetPostValue("ProjectId");
            $icobal->OpnDate = strtotime($this->GetPostValue("OpnDate"));
            $icobal->ItemId = $this->GetPostValue("ItemId");
            $icobal->Price = $this->GetPostValue("Price");
            $icobal->Qty = $this->GetPostValue("Qty");

            if ($this->ValidateData($icobal)) {
                $items = new Item();
                $items = $items->FindById($icobal->ItemId);
                if ($items == null){
                    $this->persistence->SaveState("error", sprintf("Data Item: %s tiak ditemukan!", $icobal->ItemId));
                }else {
                    $icobal->ItemCode = $items->ItemCode;
                    if ($icobal->Update($id) > -1) {
                        $this->persistence->SaveState("info", sprintf("Perubahan Data Inventory Opening: '%s - %s telah berhasil disimpan.", $items->ItemCode, $items->ItemName));
                        redirect_url("inventory.icobal");
                    } else {
                        if ($this->connector->GetHasError()) {
                            if ($this->connector->IsDuplicateError()) {
                                $this->Set("error", sprintf("Item Code: '%s' telah ada pada database !", $icobal->ItemCode));
                            } else {
                                $this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
                            }
                        }
                    }
                }
            }
        } else {
            $icobal = $icobal->LoadById($id);
            if ($icobal == null || $icobal->IsDeleted) {
                $this->persistence->SaveState("info", "Maaf data opening inventory yang diminta tidak dapat ditemukan / sudah dihapus");
                redirect_url("inventory.icobal");
                return;
            }
            if ($icobal->OpnStatus == 1) {
                $this->persistence->SaveState("info", "Maaf data opening inventory sudah berstatus -Posted-");
                redirect_url("inventory.icobal");
                return;
            }
        }

        // load data company for combo box
        $projects = new Project();
        if ($this->userLevel < 5) {
            $projects = $projects->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $projects->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
        $items = new Item();
        $items = $items->LoadByEntityId($this->userCompanyId);
        $this->Set("items", $items);
        // untuk kirim variable ke view
        $this->Set("icobal", $icobal);
    }

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih data terlebih dahulu !");
			redirect_url("inventory.icobal");
			return;
		}

        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "inventory/item.php");
        $icobal = new IcObal();

        $icobal = $icobal->LoadById($id);
        if ($icobal == null || $icobal->IsDeleted) {
            $this->persistence->SaveState("info", "Maaf data opening inventory yang diminta tidak dapat ditemukan / sudah dihapus");
            redirect_url("inventory.icobal");
            return;
        }

        // load data company for combo box
        $projects = new Project();
        if ($this->userLevel < 5) {
            $projects = $projects->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $projects->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
        $items = new Item();
        $items = $items->LoadByEntityId($this->userCompanyId);
        $this->Set("items", $items);
        // untuk kirim variable ke view
        $this->Set("icobal", $icobal);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("info", "Maaf data yang diminta tidak dapat ditemukan / sudah dihapus");
			redirect_url("inventory.icobal");
			return;
		}
		$icobal = new IcObal();
		$icobal = $icobal->LoadById($id);
		if ($icobal->OpStatus == 0) {
            $rs = $icobal->Delete($icobal->Id);
            if ($rs == 1) {
                $this->persistence->SaveState("info", sprintf("Data Inventory Opening: %s (Item Code: %s) sudah berhasil dihapus !", $icobal->ItemId, $icobal->ItemCode));
            } else {
                $this->persistence->SaveState("error", sprintf("Gagal hapus Data Inventory Opening: %s (Item Code: %s) ! Message: %s", $icobal->ItemId, $icobal->ItemCode, $this->connector->GetErrorMessage()));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Data Inventory Opening: %s (Item Code: %s) sudah terposting!", $icobal->ItemId, $icobal->ItemCode));
        }
		redirect_url("inventory.icobal");
	}

    public function upload(){
        // untuk melakukan upload dan update data sparepart
        if (count($this->postData) > 0) {
            // Ada data yang di upload...
            $this->doUpload();
            redirect_url("inventory.icobal");
        }
    }

    public function doUpload(){
        //proses upload data excel
        $uploadedFile = $this->GetPostValue("fileUpload");
        $processedData = 0;
        $infoMessages = array();	// Menyimpan info message yang akan di print
        $errorMessages = array();	// Menyimpan error message yang akan di print

        if ($uploadedFile["error"] !== 0) {
            $this->persistence->SaveState("error", "Gagal Upload file ke server !");
            return;
        }

        $tokens = explode(".", $uploadedFile["name"]);
        $ext = end($tokens);

        if ($ext != "xls" && $ext != "xlsx") {
            $this->persistence->SaveState("error", "File yang diupload bukan berupa file excel !");
            return;
        }

        // Load libs Excel
        require_once(LIBRARY . "PHPExcel.php");
        if ($ext == "xls") {
            $reader = new PHPExcel_Reader_Excel5();
        } else {
            $reader = new PHPExcel_Reader_Excel2007();
        }
        $phpExcel = $reader->load($uploadedFile["tmp_name"]);

        // OK baca file excelnya sekarang....
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "inventory/item.php");

        $company = new Company($this->userCompanyId);
        $opnDate = $company->StartDate;

        // Step #01: Baca mapping kode
        $sheet = $phpExcel->getSheetByName("Opening Inventory");
        $maxRow = $sheet->getHighestRow();
        $startFrom = 4;
        $sql = null;
        $nmr = 0;
        for ($i = $startFrom; $i <= $maxRow; $i++) {
            $nmr++;
            // OK kita lihat apakah User berbaik hati menggunakan ID atau tidak
            $iProject = trim($sheet->getCellByColumnAndRow(1, $i)->getCalculatedValue());
            $iCode = trim($sheet->getCellByColumnAndRow(2, $i)->getCalculatedValue());
            $iQty = trim($sheet->getCellByColumnAndRow(5, $i)->getCalculatedValue());
            $iPrice = trim($sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue());

            if (strlen($iCode) > 0 && strlen($iCode) < 11){
                $infoMessages[] = sprintf("[%d] Item Code: -%s- tidak valid! Pastikan Item Code pada template sudah benar!",$nmr,$iCode);
                continue;
            }
            if ($iProject == "" || $iProject == null || $iProject == 0){
                $infoMessages[] = sprintf("[%d] Project Code: -%s- tidak valid! Pastikan Project Code pada template sudah benar!",$nmr,$iProject);
                continue;
            }
            if ($iQty == "" || $iQty == null || $iQty == 0){
                $infoMessages[] = sprintf("[%d] Qty: -%s- tidak valid! Pastikan QTY pada template sudah benar!",$nmr,$iQty);
                continue;
            }
            if ($iPrice == "" || $iPrice == null){
                $iPrice = 0;
            }

            //periksa jenis barang jika tidak ada batalkan
            $items = new Item();
            $items = $items->FindByCode($this->userCompanyId,$iCode);
            $itemId = 0;
            if($items == null){
                $infoMessages[] = sprintf("[%d] Item Code: -%s- tidak valid! Pastikan Item Code pada template sudah benar!",$nmr,$iCode);
                continue;
            }else{
                $itemId = $items->Id;
            }
            //periksa kode project
            $projects = new Project();
            $project = $projects->FindByCode($this->userCompanyId,$iProject);
            $projectId = 0;
            if($project == null){
                $infoMessages[] = sprintf("[%d] Project Code: -%s- tidak valid! Pastikan Project Code pada template sudah benar!",$nmr,$iProject);
                continue;
            }else{
                $projectId = $project->Id;
            }
            $icobal = new IcObal();
            $icobal->ProjectId = $projectId;
            $icobal->ItemId = $itemId;
            $icobal->ItemCode = $items->ItemCode;
            $icobal->Qty = $iQty;
            $icobal->Price = $iPrice;
            $icobal->OpnDate = strtotime($opnDate);
            $icobal->OpnStatus = 0;
            $icobal->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            //cek duplicate data, hapus kalo ada
            $xicobal = new IcObal();
            $xicobal = $xicobal->FindDuplicate($projectId,$itemId,$iPrice);
            $xid = 0;
            if ($xicobal != null){
                $xid = $xicobal->Id;
                $xid = $xicobal->Delete($xid);
            }
            // mulai proses update
            $this->connector->BeginTransaction();
            $hasError = false;
            $rs = $icobal->Insert();
            if ($rs != 1) {
                // Hmm error apa lagi ini ?? DBase related harusnya
                $errorMessages[] = sprintf("[%d] Gagal upload Data Inventory Opening-> Kode: %s - Nama: %s Message: %s",$nmr,$iCode,$items->ItemName,$this->connector->GetErrorMessage());
                $hasError = true;
                $isoke = false;
                break;
            }
            // Step #06: Commit/Rollback transcation per karyawan...
            if ($hasError) {
                $this->connector->RollbackTransaction();
            } else {
                $this->connector->CommitTransaction();
                $processedData++;
            }
        }

        // Step #07: Sudah selesai.... semua data sudah diproses
        if (count($errorMessages) > 0) {
            $this->persistence->SaveState("error", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $errorMessages)));
            $infoMessages[] = "Data yang ERROR tidak di-entry ke system sedangkan yang lainnya tetap dimasukkan.";
        }
        $infoMessages[] = "Proses Upload Data selesai. Jumlah data yang diproses: " . $processedData;
        $this->persistence->SaveState("info", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $infoMessages)));

        // Completed...
    }

    public function template(){
        // untuk melakukan download template
        require_once(MODEL . "inventory/item.php");
        require_once(MODEL . "master/project.php");
        $items = new Item();
        $items = $items->LoadByEntityId($this->userCompanyId);
        $this->Set("items",$items);
        $projects = new Project();
        if ($this->userLevel < 5) {
            $projects = $projects->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $projects->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
    }

    public function overview()
    {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "inventory/item_category.php");
        if (count($this->postData) > 0) {
            $projectId = $this->GetPostValue("ProjectId");
            $categoryId = $this->GetPostValue("CategoryId");
            $outPut = $this->GetPostValue("outPut");
            $icobal = new IcObal();
            $report = $icobal->Load4Report($this->userCompanyId,$projectId,$categoryId);
        }else{
            $report = null;
            $projectId = 0;
            $categoryId = 0;
            $outPut = 1;
        }
        $projects = new Project();
        if ($this->userLevel < 5) {
            $projects = $projects->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $projects->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
        $company = new Company($this->userCompanyId);
        $opnDate = $company->StartDate;
        $this->Set("opndate", strtotime($opnDate));
        //load item category
        $category = new ItemCategory();
        $category = $category->LoadByEntityId($this->userCompanyId);
        $this->Set("categorys", $category);
        $this->Set("report", $report);
        $this->Set("output", $outPut);
        $this->Set("projectId", $projectId);
        $this->Set("categoryId", $categoryId);
    }

    public function posting(){
        $icobal = new IcObal();
        $rs = $icobal->Posting($this->userCompanyId,AclManager::GetInstance()->GetCurrentUser()->Id);
        if ($rs > 0) {
            $this->persistence->SaveState("info", sprintf("%s Data berhasil diposting !", $rs));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal proses posting data! Error: %s", $this->connector->GetErrorMessage()));
        }
        redirect_url("inventory.icobal");
    }

    public function unposting(){
        $icobal = new IcObal();
        $rs = $icobal->Unposting($this->userCompanyId,AclManager::GetInstance()->GetCurrentUser()->Id);
        if ($rs > 0) {
            $this->persistence->SaveState("info", sprintf("%s Data berhasil di-unposting !", $rs));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal proses unposting data! Error: %s", $this->connector->GetErrorMessage()));
        }
        redirect_url("inventory.icobal");
    }
}


// End of File: icobal_controller.php
