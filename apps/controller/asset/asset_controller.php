<?php

class AssetController extends AppController {
	private $userCompanyId = null;

	protected function Initialize() {
		require_once(MODEL . "asset/asset.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.category_name", "display" => "Category", "width" => 100);
		$settings["columns"][] = array("name" => "a.asset_code", "display" => "Asset Code", "width" => 100);
        $settings["columns"][] = array("name" => "a.asset_name", "display" => "Asset Name", "width" => 250);
		$settings["columns"][] = array("name" => "a.purchase_date", "display" => "Purchase Date", "width" => 80);
        $settings["columns"][] = array("name" => "format(a.qty,0)", "display" => "QTY", "width" => 30, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.price,2)", "display" => "Price", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.qty * a.price,2)", "display" => "Amount", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "a.last_dep", "display" => "Last Depr", "width" => 90);
        $settings["columns"][] = array("name" => "format(a.dep_accumulate,2)", "display" => "Depr Accumulate", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "format((a.qty * a.price) - a.dep_accumulate,2)", "display" => "Book Value", "width" => 90, "align" => "right");

		$settings["filters"][] = array("name" => "a.asset_code", "display" => "Asset Code");
		$settings["filters"][] = array("name" => "a.asset_name", "display" => "Asset Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
            $settings["title"] = "Company Assets List";
			if ($acl->CheckUserAccess("asset", "add", "asset")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "asset.asset/add", "Class" => "bt_add", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "Upload Data", "Url" => "asset.asset/upload", "Class" => "bt_upload1", "ReqId" => 0);
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("asset", "edit", "asset")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "asset.asset/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih aktiva/asset terlebih dahulu sebelum melakukan proses edit !",
											   "Confirm" => "Apakah anda mau merubah data aktiva/asset yang dipilih ?");
			}
			if ($acl->CheckUserAccess("asset", "delete", "asset")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "asset.asset/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih aktiva/asset terlebih dahulu sebelum melakukan proses delete !",
											   "Confirm" => "Apakah anda mau menghapus aktiva/asset yang dipilih ?");
			}
            if ($acl->CheckUserAccess("asset", "view", "asset")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "View", "Url" => "asset.asset/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Mohon meilih aktiva/asset sebelum anda dapat melihat detailnya.", "Confirm" => null);
                $settings["actions"][] = array("Text" => "Asset Report", "Url" => "asset.asset/report", "Class" => "bt_report", "ReqId" => 0);
            }
			if ($acl->CheckUserAccess("depreciation", "history", "asset")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
				$settings["actions"][] = array("Text" => "Depreciation History", "Url" => "asset.depreciation/history/%s", "Class" => "bt_depreciation", "ReqId" => 1,
											   "Error" => "Mohon memilih aktiva/asset terlebih dahulu sebelum melihat riwayat penyusutan !\nPERHATIAN: Mohon memilih tepat satu aktiva/asset.",
											   "Confirm" => "");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "vw_ac_asset_master AS a";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

    public function add() {
        require_once(MODEL . "asset/asset_category.php");
        require_once(MODEL . "asset/depreciation.php");
        $asset = new Asset();
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $asset->EntityId = $this->userCompanyId;
            $asset->AssetCode = $this->GetPostValue("AssetCode");
            $asset->AssetName = $this->GetPostValue("AssetName");
            $asset->CategoryId = $this->GetPostValue("CategoryId");
            $asset->PurchaseDate = $this->GetPostValue("PurchaseDate");
            $asset->Qty = str_replace(',','',$this->GetPostValue("Qty"));
            $asset->Price = str_replace(',','',$this->GetPostValue("Price"));
            $asset->DepAccumulate = str_replace(',','',$this->GetPostValue("DepAccumulate"));
            if ($this->GetPostValue("LastDep") == ''){
                $asset->LastDep = null;
            }else {
                $asset->LastDep = $this->GetPostValue("LastDep");
            }
            $asset->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateData($asset)) {
                if ($asset->Insert() == 1) {
                    if ($asset->DepAccumulate > 0){
                        $depreciation = new Depreciation();
                        $depreciation->AssetId = $asset->Id;
                        $depreciation->DepreciationDate = strtotime($asset->LastDep);
                        $depreciation->Amount = $asset->DepAccumulate;
                        $depreciation->BookValue = round($asset->Qty * $asset->Price,2) - $asset->DepAccumulate;
                        $acat = new AssetCategory($asset->CategoryId);
                        $depreciation->MethodCode = $acat->DepreciationMethodId;
                        $depreciation->Percentage = $acat->DepreciationPercentage;
                        $depreciation->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
                        $rs = $depreciation->Insert();
                    }
                    $this->persistence->SaveState("info", sprintf("Data Asset : '%s - %s' telah berhasil disimpan.",$asset->AssetCode, $asset->AssetName));
                    redirect_url("asset.asset");
                } else {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", sprintf("Data Asset: '%s - %s' telah ada pada database !",$asset->AssetCode, $asset->AssetName));
                    } else {
                        $this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
                    }
                }
            }
        }
        // load data company for combo box
        $category = new AssetCategory();
        $categorys = $category->LoadByEntityId($this->userCompanyId);

        // untuk kirim variable ke view
        $this->Set("categorys", $categorys);
        $this->Set("asset", $asset);
    }

    public function edit($id) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Maaf anda harus memilih asset terlebih dahulu !");
            redirect_url("asset.asset");
            return;
        }
        require_once(MODEL . "asset/asset_category.php");
        $asset = new Asset();
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $asset->EntityId = $this->userCompanyId;
            $asset->AssetCode = $this->GetPostValue("AssetCode");
            $asset->AssetName = $this->GetPostValue("AssetName");
            $asset->CategoryId = $this->GetPostValue("CategoryId");
            $asset->PurchaseDate = $this->GetPostValue("PurchaseDate");
            $asset->Qty = str_replace(',','',$this->GetPostValue("Qty"));
            $asset->Price = str_replace(',','',$this->GetPostValue("Price"));
            $asset->DepAccumulate = str_replace(',','',$this->GetPostValue("DepAccumulate"));
            if ($this->GetPostValue("LastDep") == ''){
                $asset->LastDep = null;
            }else {
                $asset->LastDep = $this->GetPostValue("LastDep");
            }
            $asset->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateData($asset)) {
                if ($asset->Update($id) > -1) {
                    $this->persistence->SaveState("info", sprintf("Data Asset : '%s - %s' telah berhasil diupdate.",$asset->AssetCode, $asset->AssetName));
                    redirect_url("asset.asset");
                } else {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", sprintf("Data Asset: '%s - %s' telah ada pada database !",$asset->AssetCode, $asset->AssetName));
                    } else {
                        $this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
                    }
                }
            }
        }else{
            $asset = $asset->LoadById($id);
            if ($asset == null){
                $this->persistence->SaveState("error", "Maaf data asses tidak dassetukan atau sudah dihapus!");
                redirect_url("asset.asset");
            }
        }
        // load data company for combo box
        $category = new AssetCategory();
        $categorys = $category->LoadByEntityId($this->userCompanyId);
        //init
        $cat = new AssetCategory();
        $cat = $cat->LoadById($asset->CategoryId);
        $dcat = $cat->Id.'|'.$cat->GetDepreciationMethod().'|'.$cat->MaxAge.'|'.$cat->DepreciationPercentage;
        // untuk kirim variable ke view
        $this->Set("categorys", $categorys);
        $this->Set("asset", $asset);
        $this->Set("dcat", $dcat);
    }

	private function ValidateData(Asset $asset) {
		if ($asset->EntityId == null) {
			$this->Set("error", "Mohon memasukkan data Company terlebih dahulu.");
			return false;
		}
		if ($asset->AssetCode == null) {
			// AssetCode diperlukan jika bukan mode batch
			$this->Set("error", "Mohon memasukkan data Nomor / Kode Aktiva terlebih dahulu.");
			return false;
		}
		if ($asset->PurchaseDate == null) {
			$this->Set("error", "Mohon memasukkan data Tanggal Pembelian Aktiva terlebih dahulu.");
			return false;
		}
		if ($asset->Price == null || $asset->Price <= 0) {
			$this->Set("error", "Mohon memasukkan data Harga Pembelian Aktiva terlebih dahulu.");
			return false;
		}
		return true;
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih asset terlebih dahulu !");
			redirect_url("asset.asset");
			return;
		}
        require_once(MODEL . "asset/asset_category.php");
        $asset = new Asset();
        $asset = $asset->LoadById($id);
        if ($asset == null){
            $this->persistence->SaveState("error", "Maaf data asses tidak dassetukan atau sudah dihapus!");
            redirect_url("asset.asset");
        }
        // load data company for combo box
        $category = new AssetCategory();
        $categorys = $category->LoadByEntityId($this->userCompanyId);
        //init
        $cat = new AssetCategory();
        $cat = $cat->LoadById($asset->CategoryId);
        $dcat = $cat->Id.'|'.$cat->GetDepreciationMethod().'|'.$cat->MaxAge.'|'.$cat->DepreciationPercentage;
        // untuk kirim variable ke view
        $this->Set("categorys", $categorys);
        $this->Set("asset", $asset);
        $this->Set("dcat", $dcat);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih asset terlebih dahulu !");
			redirect_url("asset.asset");
			return;
		}

		$asset = new Asset();
		$asset = $asset->LoadById($id);

		// ToDo: Hmm untuk hapus asset apakah perlu prosedur khusus ?
		$rs = $asset->Delete($asset->Id);
		if ($rs == 1) {
			$this->persistence->SaveState("info", sprintf("Asset: %s sudah berhasil dihapus !", $asset->AssetCode));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Asset: %s ! Message: %s", $asset->AssetCode, $this->connector->GetErrorMessage()));
		}
        redirect_url("asset.asset");
	}

    public function upload(){
        // untuk melakukan upload dan update data sparepart
        if (count($this->postData) > 0) {
            // Ada data yang di upload...
            $this->doUpload();
            redirect_url("asset.asset");
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

        // OK baca file excelnya sekarang....;
        require_once(MODEL . "asset/asset_category.php");
        require_once(MODEL . "asset/depreciation.php");

        // Step #01: Baca mapping kode
        $sheet = $phpExcel->getSheetByName("Asset List");
        $maxRow = $sheet->getHighestRow();
        $startFrom = 4;
        $sql = null;
        $nmr = 0;
        for ($i = $startFrom; $i <= $maxRow; $i++) {
            $assets = new Asset();
            $nmr++;
            // OK kita lihat apakah User berbaik hati menggunakan ID atau tidak
            $iCode = trim($sheet->getCellByColumnAndRow(1, $i)->getCalculatedValue());
            $iCategory = trim($sheet->getCellByColumnAndRow(2, $i)->getCalculatedValue());
            $iAssetName = trim($sheet->getCellByColumnAndRow(3, $i)->getCalculatedValue());
            $iPurDate = trim($sheet->getCellByColumnAndRow(4, $i)->getCalculatedValue());
            $iQty = trim($sheet->getCellByColumnAndRow(5, $i)->getCalculatedValue());
            $iPrice = trim($sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue());
            $iLastDep = trim($sheet->getCellByColumnAndRow(8, $i)->getCalculatedValue());
            $iDepAccum = $sheet->getCellByColumnAndRow(9, $i)->getCalculatedValue();

            if ($iCode == '' && $iCode == null){
                $infoMessages[] = sprintf("[%d] Asset Code: -%s- tidak valid! Pastikan Asset Code pada template sudah benar!",$nmr,$iCode);
                continue;
            }
            if ($iCategory == "" || $iCategory == null || $iCategory == '-'){
                $infoMessages[] = sprintf("[%d] Asset Category: -%s- tidak valid! Pastikan Asset Category pada template sudah benar!",$nmr,$iCategory);
                continue;
            }
            if ($iAssetName == "" || $iAssetName == null || $iAssetName == '-'){
                $infoMessages[] = sprintf("[%d] Asset Name: -%s- tidak valid! Pastikan Asset Name pada template sudah benar!",$nmr,$iAssetName);
                continue;
            }
            if ($iPurDate == "" || $iPurDate == null){
                $infoMessages[] = sprintf("[%d] Asset Purchase Date: -%s- tidak valid! Pastikan Asset Purchase Date pada template sudah benar!",$nmr,$iPurDate);
                continue;
            }
            if ($iQty == "" || $iQty == null || $iQty == 0){
                $infoMessages[] = sprintf("[%d] Asset Qty: -%s- tidak valid! Pastikan Asset Qty pada template sudah benar!",$nmr,$iQty);
                continue;
            }
            if ($iPrice == "" || $iPrice == null || $iPrice == 0){
                $infoMessages[] = sprintf("[%d] Asset Price: -%s- tidak valid! Pastikan Asset Price pada template sudah benar!",$nmr,$iPrice);
                continue;
            }
            if ($iLastDep == "" || $iLastDep == null){
                $infoMessages[] = sprintf("[%d] Last Depreciation: -%s- tidak valid! Pastikan Tanggal Depresiasi terakhir pada template sudah benar!",$nmr,$iLastDep);
                continue;
            }
            if ($iDepAccum == "" || $iDepAccum == null || $iDepAccum == 0){
                $infoMessages[] = sprintf("[%d] Depreciation Accumulate: -%s- tidak valid! Pastikan Akumulasi Depresiasi pada template sudah benar!",$nmr,$iDepAccum);
                continue;
            }
            //periksa jenis barang jika tidak ada batalkan
            $acategory = new AssetCategory();
            $acategory = $acategory->LoadByCode($this->userCompanyId,$iCategory);
            if($acategory == null){
                $infoMessages[] = sprintf("[%d] Asset Category: -%s- tidak valid! Pastikan Asset Category pada template sudah benar!",$nmr,$iCategory);
                continue;
            }else{
                $acategory = $acategory->Id;
            }
            $xassets = null;
            $isnew = true;
            $isoke = true;
            $iBid = 0;
            $assets->EntityId = $this->userCompanyId;
            $assets->AssetCode = $iCode;
            $assets->CategoryId = $acategory;
            $assets->AssetName = $iAssetName;
            $val = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($iPurDate));
            $assets->PurchaseDate = $val;
            $assets->Qty = $iQty;
            $assets->Price = $iPrice;
            $val = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($iLastDep));
            $assets->LastDep = $val;
            $assets->DepAccumulate = $iDepAccum;
            $assets->UpdatedUserId = AclManager::GetInstance()->GetCurrentUser()->Id;
            $xassets = new Asset();
            $xassets = $xassets->LoadByCode($this->userCompanyId,$iCode);
            if ($xassets != null){
                $isnew = false;
            }
            // mulai proses update
            $this->connector->BeginTransaction();
            $hasError = false;
            $iBid = 0;
            if ($isnew) {
                $rs = $assets->Insert();
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal upload Data Asset-> Kode: %s - Nama: %s Message: %s",$nmr,$iCode,$iAssetName,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $iBid = $assets->Id;
                    if ($assets->DepAccumulate > 0){
                        $depreciation = new Depreciation();
                        $depreciation->AssetId = $assets->Id;
                        $depreciation->DepreciationDate = strtotime($assets->LastDep);
                        $depreciation->Amount = $assets->DepAccumulate;
                        $depreciation->BookValue = round($assets->Qty * $assets->Price,2) - $assets->DepAccumulate;
                        $acat = new AssetCategory($assets->CategoryId);
                        $depreciation->MethodCode = $acat->DepreciationMethodId;
                        $depreciation->Percentage = $acat->DepreciationPercentage;
                        $depreciation->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
                        $rs = $depreciation->Insert();
                    }
                }
            }else{
                $rs = $assets->Update($xassets->Id);
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal Update Data Asset-> Kode: %s - Nama: %s Message: %s",$nmr,$iCode,$iAssetName,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $iBid = $xassets->Id;
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
            $infoMessages[] = "Data Asset yang ERROR tidak di-entry ke system sedangkan yang lainnya tetap dimasukkan.";
        }
        $infoMessages[] = "Proses Upload Data Asset selesai. Jumlah data yang diproses: " . $processedData;
        $this->persistence->SaveState("info", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $infoMessages)));

        // Completed...
    }

    public function template(){
        // untuk melakukan download template
        require_once(MODEL . "asset/asset_category.php");
        $acat = new AssetCategory();
        $acat = $acat->LoadByEntityId($this->userCompanyId);
        $this->Set("acats",$acat);
    }

    public function report(){
        require_once(MODEL . "asset/asset_category.php");
        require_once (MODEL . "master/company.php");
        $company = new Company($this->userCompanyId);
        $startYear = date('Y',strtotime($company->StartDate));
        if (count($this->postData) > 0) {
            $categoryId = $this->GetPostValue("CategoryId");
            $depMonth = $this->GetPostValue("DepMonth");
            $depYear = $this->GetPostValue("DepYear");
            $outPut = $this->GetPostValue("OutPut");
            $asset = new Asset();
            $report = $asset->LoadByCategory($this->userCompanyId,$categoryId);
        }else{
            $categoryId = 0;
            $depMonth = (int) date('n');
            $depYear = (int) date('Y');
            $outPut = 1;
            $report = null;
        }
        $acat = new AssetCategory();
        $acat = $acat->LoadByEntityId($this->userCompanyId);
        $this->Set("acats",$acat);
        $this->Set("startYear",$startYear);
        $this->Set("CategoryId",$categoryId);
        $this->Set("DepMonth",$depMonth);
        $this->Set("DepYear",$depYear);
        $this->Set("OutPut",$outPut);
        $this->Set("report",$report);
    }
}


// End of File: asset_controller.php
