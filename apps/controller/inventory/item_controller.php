<?php

class ItemController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/item.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "c.category_desc", "display" => "Category", "width" => 100);
        $settings["columns"][] = array("name" => "COALESCE(a.brand_name, '-')", "display" => "Unit Brand", "width" => 80);
        $settings["columns"][] = array("name" => "COALESCE(a.type_desc, '-')", "display" => "Unit Type", "width" => 80);
        //$settings["columns"][] = array("name" => "COALESCE(a.comp_name, '-')", "display" => "Part Of", "width" => 150);
		$settings["columns"][] = array("name" => "a.item_code", "display" => "Item Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.item_name", "display" => "Item Name & Specification", "width" => 200);
        $settings["columns"][] = array("name" => "COALESCE(a.part_no,'-')", "display" => "Part Number", "width" => 100);
        $settings["columns"][] = array("name" => "COALESCE(a.sn_no,'-')", "display" => "Serial Number", "width" => 100);
		$settings["columns"][] = array("name" => "a.uom_cd", "display" => "UOM", "width" => 60);
        $settings["columns"][] = array("name" => "a.gclass", "display" => "QClass", "width" => 60);
        $settings["columns"][] = array("name" => "COALESCE(a.icx_code,'-')", "display" => "Interchange Of", "width" => 100);
		//$settings["columns"][] = array("name" => "a.max_qty", "display" => "Max Qty", "width" => 60, "align" => "right");
		//$settings["columns"][] = array("name" => "a.min_qty", "display" => "Min Qty", "width" => 60, "align" => "right");
		$settings["columns"][] = array("name" => "CASE WHEN a.is_discontinue = 0 THEN 'Active' ELSE 'InActive' END", "display" => "Status", "width" => 60);

		$settings["filters"][] = array("name" => "a.item_code", "display" => "Item Code");
		$settings["filters"][] = array("name" => "a.item_name", "display" => "Item Name");
		$settings["filters"][] = array("name" => "c.category_desc", "display" => "Category");
        $settings["filters"][] = array("name" => "a.type_desc", "display" => "Unit Type");
        $settings["filters"][] = array("name" => "a.brand_name", "display" => "Unit Brand");
        $settings["filters"][] = array("name" => "a.comp_name", "display" => "Component");
        $settings["filters"][] = array("name" => "a.part_no", "display" => "Part Number");
        $settings["filters"][] = array("name" => "a.sn_no", "display" => "Serial Number");
        $settings["filters"][] = array("name" => "a.gclass", "display" => "Quality");

		if (!$router->IsAjaxRequest) {
			// UI Settings
			$acl = AclManager::GetInstance();
			$settings["title"] = "Items List Master";
			if ($acl->CheckUserAccess("item", "add", "inventory")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.item/add", "Class" => "bt_add", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "Upload Data", "Url" => "inventory.item/upload", "Class" => "bt_upload1", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("item", "edit", "inventory")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.item/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih item terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "Apakah anda mau merubah data item yang dipilih ?");
			}
			if ($acl->CheckUserAccess("item", "delete", "inventory")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.item/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih item terlebih dahulu sebelum melakukan proses delete !",
											   "Confirm" => "Apakah anda mau menghapus data item yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("stock", "track", "inventory")) {
				$settings["actions"][] = array("Text" => "Track Item", "Url" => "inventory.stock/track?item=%s", "Class" => "bt_verify", "ReqId",
											   "Error" => "Anda harus memilih item terlebih dahulu sebelum melakukan proses tracking !\nPERHATIAN: Pilih tepat 1 item.",
											   "Confirm" => "");
			}

			$settings["def_filter"] = 1;
			$settings["def_order"] = 5;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "vw_ic_item_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id Join ic_item_category AS c On a.category_id = c.id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/uom_master.php");
		require_once(MODEL . "inventory/item_category.php");
		require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unittype.php");
        require_once(MODEL . "master/unitcomp.php");
		$item = new Item();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$item->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
			$item->EntityId = $this->userCompanyId;
			$item->ItemCode = $this->GetPostValue("Code");
			$item->ItemName = $this->GetPostValue("Name");
			$item->CategoryId = $this->GetPostValue("CategoryId");
            $item->UnitTypeCode = $this->GetPostValue("UnitTypeCode");
            $item->UnitBrandCode = $this->GetPostValue("UnitBrandCode");
            $item->UnitCompCode = $this->GetPostValue("UnitCompCode");
            $item->SnNo = $this->GetPostValue("SnNo");
			//$item->AssetCategoryId = $this->GetPostValue("AssetCategoryId");
			$item->PartNo = $this->GetPostValue("PartNo");
			$item->Barcode = $this->GetPostValue("Barcode");
			$item->Note = $this->GetPostValue("Note");
            $item->Qclass = $this->GetPostValue("Qclass");
            $item->IcxCode = $this->GetPostValue("IcxCode");
			$item->MaxQty = str_replace(",", "", $this->GetPostValue("MaxQty"));
			$item->MinQty = str_replace(",", "", $this->GetPostValue("MinQty"));
			$item->UomCode = $this->GetPostValue("Uom");
            $item->LUomCode = $this->GetPostValue("LUom");
            $item->UomConversion = $this->GetPostValue("UomConversion");
            $item->IsDiscontinued = $this->GetPostValue("Obsolete", false);
            $item->IsDiscontinued = $item->IsDiscontinued == "1";
			if (empty($item->AssetCategoryId)) {
				$item->AssetCategoryId = null;
			}
			if ($this->DoInsert($item)) {
				$this->persistence->SaveState("info", sprintf("Data Item: '%s' Dengan Kode: %s telah berhasil disimpan.", $item->ItemName, $item->ItemCode));
				redirect_url("inventory.item");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $item->ItemCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// load data company for combo box
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$measurement = new UomMaster();
		$measurements = $measurement->LoadAll();
		$category = new ItemCategory();
		$categories = $category->LoadByEntityId($this->userCompanyId);
		$ubrand = new UnitBrand();
        $ubrand = $ubrand->LoadAll($this->userCompanyId);
        $utype = new UnitType();
        $utype = $utype->LoadAll($this->userCompanyId);
        $ucomp = new UnitComp();
        $ucomp = $ucomp->LoadAll($this->userCompanyId);
        $this->Set("ucomp", $ucomp);
        //items for interchange
        $items = new Item();
        $items = $items->LoadByQclass($this->userCompanyId,0);
        $this->Set("items", $items);
		// untuk kirim variable ke view
		$this->Set("item", $item);
		$this->Set("company", $company);
		$this->Set("measurements", $measurements);
		$this->Set("categories", $categories);
		$this->Set("ubrand", $ubrand);
        $this->Set("utype", $utype);
	}

	private function doInsert(Item $item) {
		if ($item->ItemCode == "") {
			$item->ItemCode = $item->GetAutoStockCode();
		}
		if ($item->ItemName == "") {
			$this->Set("error", "Nama Item masih kosong !");
			return false;
		}
		if ($item->CategoryId == "") {
			$this->Set("error", "Mohon memilih kategori item terlebih dahulu !");
			return false;
		}
		if ($item->UomCode == "") {
			$this->Set("error", "Nama satuan masih kosong");
			return false;
		}
		if (!is_numeric($item->MaxQty)) {
			$this->Set("error", "Stock Maximum harus berupa angka");
			return false;
		}
		if (!is_numeric($item->MinQty)) {
			$this->Set("error", "Stock Minimum harus berupa angka");
			return false;
		}

		if ($item->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/uom_master.php");
		require_once(MODEL . "inventory/item_category.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unittype.php");
        require_once(MODEL . "master/unitcomp.php");
		$item = new Item();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$item->Id = $id;
			$item->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
			$item->EntityId = $this->userCompanyId;
			$item->ItemCode = $this->GetPostValue("Code");
			$item->ItemName = $this->GetPostValue("Name");
			$item->CategoryId = $this->GetPostValue("CategoryId");
			//$item->AssetCategoryId = $this->GetPostValue("AssetCategoryId");
            $item->UnitTypeCode = $this->GetPostValue("UnitTypeCode");
            $item->UnitBrandCode = $this->GetPostValue("UnitBrandCode");
            $item->UnitCompCode = $this->GetPostValue("UnitCompCode");
            $item->SnNo = $this->GetPostValue("SnNo");
			$item->PartNo = $this->GetPostValue("PartNo");
			$item->Barcode = $this->GetPostValue("Barcode");
			$item->Note = $this->GetPostValue("Note");
            $item->Qclass = $this->GetPostValue("Qclass");
            $item->IcxCode = $this->GetPostValue("IcxCode");
			$item->MaxQty = str_replace(",", "", $this->GetPostValue("MaxQty"));
			$item->MinQty = str_replace(",", "", $this->GetPostValue("MinQty"));
			$item->UomCode = $this->GetPostValue("Uom");
            $item->LUomCode = $this->GetPostValue("LUom");
            $item->UomConversion = $this->GetPostValue("UomConversion");
            $item->IsDiscontinued = $this->GetPostValue("Obsolete", false);
            $item->IsDiscontinued = $item->IsDiscontinued == "1";
			if (empty($item->AssetCategoryId)) {
				$item->AssetCategoryId = null;
			}
			if ($this->DoUpdate($item)) {
				$this->persistence->SaveState("info", sprintf("Data Item: '%s' Dengan Kode: %s telah berhasil diupdate.", $item->ItemName, $item->ItemCode));
				redirect_url("inventory.item");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $item->ItemCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data Item sebelum melakukan edit data !");
				redirect_url("inventory.item");
			}
			$item = $item->FindById($id);
			if ($item == null) {
				$this->persistence->SaveState("error", "Data Item yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("inventory.item");
			}
		}

        // load data company for combo box
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $measurement = new UomMaster();
        $measurements = $measurement->LoadAll();
        $category = new ItemCategory();
        $categories = $category->LoadByEntityId($this->userCompanyId);
        $ubrand = new UnitBrand();
        $ubrand = $ubrand->LoadAll($this->userCompanyId);
        $utype = new UnitType();
        $utype = $utype->LoadAll($this->userCompanyId);
        $ucomp = new UnitComp();
        $ucomp = $ucomp->LoadAll($this->userCompanyId);
        $this->Set("ucomp", $ucomp);
        //items for interchange
        $items = new Item();
        $items = $items->LoadByQclass($this->userCompanyId,0);
        $this->Set("items", $items);
        // untuk kirim variable ke view
        $this->Set("item", $item);
        $this->Set("company", $company);
        $this->Set("measurements", $measurements);
        $this->Set("categories", $categories);
        $this->Set("ubrand", $ubrand);
        $this->Set("utype", $utype);
	}

	private function doUpdate(Item $item) {
		if ($item->ItemCode == "") {
			$this->Set("error", "Kode Item masih kosong !");
			return false;
		}
		if ($item->ItemName == "") {
			$this->Set("error", "Nama Item masih kosong !");
			return false;
		}
		if ($item->CategoryId == "") {
			$this->Set("error", "Mohon memilih kategori item terlebih dahulu !");
			return false;
		}
		if ($item->UomCode == "") {
			$this->Set("error", "Nama satuan masih kosong");
			return false;
		}
		if (!is_numeric($item->MaxQty)) {
			$this->Set("error", "Stock Maximum harus berupa angka");
			return false;
		}
		if (!is_numeric($item->MinQty)) {
			$this->Set("error", "Stock Minimum harus berupa angka");
			return false;
		}
		// Tambahan jika dia edit kategori assetnya menjadi null tetapi masih ada referensi di asset harus dibatalkan
        /*
		if ($item->AssetCategoryId == null) {
			$this->connector->CommandText = "SELECT COUNT(a.id) FROM ac_asset_master WHERE a.is_deleted = 0 AND a.item_id = ?id";
			$this->connector->AddParameter("?id", $item->Id);
			$rs = $this->connector->ExecuteScalar();
			if ($rs > 0) {
				$this->Set("error", "Refensi kategori asset barang ini masih digunakan. Tidak dapat menghapus kategori asset !");
				return false;
			}
		}
        */
		if ($item->Update($item->Id) == -1) {
			return false;
		} else {
			return true;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data Item sebelum melakukan hapus data !");
			redirect_url("inventory.item");
		}

		$item = new Item();
		$item = $item->FindById($id);
		if ($item == null) {
			$this->persistence->SaveState("error", "Data Item yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("inventory.item");
		}

		if ($item->Delete($item->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Item: '%s' Dengan Kode: %s telah berhasil dihapus.", $item->ItemName, $item->ItemCode));
			redirect_url("inventory.item");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data Item: '%s'. Message: %s", $item->ItemName, $this->connector->GetErrorMessage()));
		}
		redirect_url("inventory.item");
	}

    public function upload(){
        // untuk melakukan upload dan update data sparepart
        if (count($this->postData) > 0) {
            // Ada data yang di upload...
            $this->doUpload();
            redirect_url("inventory.item");
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
        require_once(MODEL . "common/uom_master.php");
        require_once(MODEL . "inventory/item_category.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unittype.php");

        // Step #01: Baca mapping kode
        $sheet = $phpExcel->getSheetByName("Items List");
        $maxRow = $sheet->getHighestRow();
        $startFrom = 4;
        $sql = null;
        $nmr = 0;
        for ($i = $startFrom; $i <= $maxRow; $i++) {
            $items = new Item();
            $nmr++;
            // OK kita lihat apakah User berbaik hati menggunakan ID atau tidak
            $iCode = trim($sheet->getCellByColumnAndRow(1, $i)->getCalculatedValue());
            $iItemName = trim($sheet->getCellByColumnAndRow(2, $i)->getCalculatedValue());
            $iCategory = trim($sheet->getCellByColumnAndRow(3, $i)->getCalculatedValue());
            $iBrand = trim($sheet->getCellByColumnAndRow(4, $i)->getCalculatedValue());
            $iType = trim($sheet->getCellByColumnAndRow(5, $i)->getCalculatedValue());
            $iPartNo = trim($sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue());
            $iSnNo = trim($sheet->getCellByColumnAndRow(7, $i)->getCalculatedValue());
            $iSatuan = trim($sheet->getCellByColumnAndRow(8, $i)->getCalculatedValue());
            $iDescription = $sheet->getCellByColumnAndRow(9, $i)->getCalculatedValue();

            if (strlen($iCode) > 0 && strlen($iCode) < 11){
                $infoMessages[] = sprintf("[%d] Item Code: -%s- tidak valid! Pastikan Item Code pada template sudah benar!",$nmr,$iCode);
                continue;
            }
            if ($iItemName == "" || $iItemName == null || $iItemName == '-'){
                $infoMessages[] = sprintf("[%d] Item Name: -%s- tidak valid! Pastikan Item Name pada template sudah benar!",$nmr,$iItemName);
                continue;
            }
            if ($iCategory == "" || $iCategory == null || $iCategory == '-'){
                $infoMessages[] = sprintf("[%d] Item Category: -%s- tidak valid! Pastikan Item Category pada template sudah benar!",$nmr,$iCategory);
                continue;
            }
            if ($iBrand == "" || $iBrand == null || $iBrand == '-'){
                $infoMessages[] = sprintf("[%d] Unit Brand: -%s- tidak valid! Pastikan Unit Brand pada template sudah benar!",$nmr,$iBrand);
                continue;
            }
            if ($iType == "" || $iType == null || $iType == '-'){
                $infoMessages[] = sprintf("[%d] Unit Type: -%s- tidak valid! Pastikan Unit Type pada template sudah benar!",$nmr,$iType);
                continue;
            }
            if ($iSatuan == "" || $iSatuan == null || $iSatuan == '-'){
                $infoMessages[] = sprintf("[%d] UOM: -%s- tidak valid! Pastikan Satuan Barang pada template sudah benar!",$nmr,$iSatuan);
                continue;
            }
            //periksa jenis barang jika tidak ada batalkan
            $bcategory = new ItemCategory();
            $bcategory = $bcategory->LoadByCode($this->userCompanyId,$iCategory);
            if($bcategory == null){
                $infoMessages[] = sprintf("[%d] Item Category: -%s- tidak valid! Pastikan Item Category pada template sudah benar!",$nmr,$iCategory);
                continue;
            }else{
                $bcategory = $bcategory->Id;
            }
            //periksa brand unit jika tidak ada batalkan
            $bbrand = new UnitBrand();
            $bbrand = $bbrand->FindByBrand($this->userCompanyId,$iBrand);
            if($bbrand == null){
                $bbrand = new UnitBrand();
                $bbrand = $bbrand->FindByCode($this->userCompanyId,$iBrand);
                if($bbrand == null) {
                    $infoMessages[] = sprintf("[%d] Unit Brand: -%s- tidak valid! Pastikan Unit Brand pada template sudah benar!", $nmr, $iBrand);
                    continue;
                }else{
                    $bbrand = $bbrand->BrandCode;
                }
            }else{
                $bbrand = $bbrand->BrandCode;
            }
            //periksa type unit jika tidak ada batalkan
            $btype = new UnitType();
            $btype = $btype->FindByName($this->userCompanyId,$iType);
            if($btype == null){
                $btype = new UnitType();
                $btype = $btype->FindByCode($this->userCompanyId,$iType);
                if($btype == null) {
                    $infoMessages[] = sprintf("[%d] Unit Type: -%s- tidak valid! Pastikan Unit Type pada template sudah benar!", $nmr, $iType);
                    continue;
                }else{
                    $btype = $btype->TypeCode;
                }
            }else{
                $btype = $btype->TypeCode;
            }
            $xitems = null;
            $isnew = true;
            $isoke = true;
            $iBid = 0;
            $items->EntityId = $this->userCompanyId;
            $items->ItemCode = $iCode;
            $items->CategoryId = $bcategory;
            $items->ItemName = $iItemName;
            $items->UnitTypeCode = $btype;
            $items->UnitBrandCode = $bbrand;
            $items->PartNo = $iPartNo;
            $items->SnNo = $iSnNo;
            $items->UomCode = $iSatuan;
            $items->Note = $iDescription;
            $xitems = new Item();
            $xitems = $xitems->FindByCode($this->userCompanyId,$iCode);
            if ($xitems != null){
                $isnew = false;
            }
            // mulai proses update
            $this->connector->BeginTransaction();
            $hasError = false;
            $iBid = 0;
            if ($isnew) {
                if ($items->ItemCode == "" || $items->ItemCode == null || $items->ItemCode == '-'){
                    $items->ItemCode = $items->GetAutoStockCode();
                }
                $rs = $items->Insert();
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal upload Data Barang-> Kode: %s - Nama: %s Message: %s",$nmr,$iCode,$iItemName,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $iBid = $items->Id;
                }
            }else{
                $rs = $items->Update($xitems->Id);
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal Update Data Barang-> Kode: %s - Nama: %s Message: %s",$nmr,$iCode,$iItemName,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $iBid = $xitems->Id;
                }
            }
            // Step #06: Commit/Rollback transcation per karyawan...
            if ($hasError) {
                $this->connector->RollbackTransaction();
            } else {
                $this->connector->CommitTransaction();
                $processedData++;
            }
        }

        // Step #07: Sudah selesai.... semua karyawan sudah diproses
        if (count($errorMessages) > 0) {
            $this->persistence->SaveState("error", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $errorMessages)));
            $infoMessages[] = "Data Barang yang ERROR tidak di-entry ke system sedangkan yang lainnya tetap dimasukkan.";
        }
       $infoMessages[] = "Proses Upload Data Barang selesai. Jumlah data yang diproses: " . $processedData;
        $this->persistence->SaveState("info", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $infoMessages)));

        // Completed...
    }

    public function template(){
        // untuk melakukan download template
        require_once(MODEL . "common/uom_master.php");
        require_once(MODEL . "inventory/item_category.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unittype.php");
        $isatuan = new UomMaster();
        $isatuan = $isatuan->LoadAll();
        $this->Set("isatuan",$isatuan);
        $icategory = new ItemCategory();
        $icategory = $icategory->LoadByEntityId($this->userCompanyId);
        $this->Set("icategory",$icategory);
        $ibrand = new UnitBrand();
        $ibrand = $ibrand->LoadAll($this->userCompanyId);
        $this->Set("ibrand",$ibrand);
        $itype = new UnitType();
        $itype = $itype->LoadAll($this->userCompanyId);
        $this->Set("itype",$itype);
    }

    public function getjson_items(){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $items = new Item();
        $items = $items->GetJSonItems($this->userCompanyId,$filter);
        echo json_encode($items);
    }
}


// End of File: item_controller.php
