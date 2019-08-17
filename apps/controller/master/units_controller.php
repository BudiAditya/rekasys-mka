<?php
class UnitsController extends AppController {
	private $userCompanyId;
	private $userUid;

	protected function Initialize() {
		require_once(MODEL . "master/units.php");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        $settings["columns"][] = array("name" => "a.unit_code", "display" => "Unit Code", "width" => 80);
        $settings["columns"][] = array("name" => "a.unit_name", "display" => "Unit Name", "width" => 150);
        $settings["columns"][] = array("name" => "a.type_desc", "display" => "Type", "width" => 100);
        $settings["columns"][] = array("name" => "a.brand_name", "display" => "Brand", "width" => 100);
        $settings["columns"][] = array("name" => "a.unit_model", "display" => "Model", "width" => 100);
        $settings["columns"][] = array("name" => "a.class_name", "display" => "Class", "width" => 50);
        $settings["columns"][] = array("name" => "a.sn_no", "display" => "S/N", "width" => 150);
        $settings["columns"][] = array("name" => "a.prod_year", "display" => "Prod Year", "width" => 50);
        $settings["columns"][] = array("name" => "a.km_position", "display" => "K M", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "a.hm_position", "display" => "H M", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.unit_status = 1,'Active','InActive')", "display" => "Status", "width" => 90);

		$settings["filters"][] = array("name" => "a.type_desc", "display" => "Type");
        $settings["filters"][] = array("name" => "a.brand_name", "display" => "Brand");
        $settings["filters"][] = array("name" => "a.unit_model", "display" => "Model");
        $settings["filters"][] = array("name" => "a.class_name", "display" => "Class");
		$settings["filters"][] = array("name" => "a.unit_name", "display" => "Unit Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Unit & Equipment Master List";
			if($acl->CheckUserAccess("units", "add", "master")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.units/add", "Class" => "bt_add", "ReqId" => 0);
			}
            if ($acl->CheckUserAccess("units", "add", "master")) {
                $settings["actions"][] = array("Text" => "Upload Data", "Url" => "master.units/upload", "Class" => "bt_upload1", "ReqId" => 0);
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
			if($acl->CheckUserAccess("units", "edit", "master")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.units/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih units sebelum melakukan proses edit data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if($acl->CheckUserAccess("units", "delete", "master")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.units/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih units sebelum melakukan proses penghapusan data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?");
			}
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("units", "view", "master")) {
                $settings["actions"][] = array("Text" => "Report", "Url" => "master.units/report", "Class" => "bt_report", "ReqId" => 0);
            }

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "vw_cm_units AS a JOIN cm_company AS b ON a.entity_id = b.entity_id";
			$settings["where"] = "a.is_deleted = 0 And a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/unittype.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unitclass.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "asset/asset.php");
        $unit = new Units();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$unit->EntityId = $this->userCompanyId;
            $unit->TypeCode = $this->GetPostValue("TypeCode");
			$unit->UnitCode = $this->GetPostValue("UnitCode");
            $unit->UnitName = $this->GetPostValue("UnitName");
            $unit->UnitModel = $this->GetPostValue("UnitModel");
            $unit->BrandCode = $this->GetPostValue("BrandCode");
            $unit->ClassCode = $this->GetPostValue("ClassCode");
            $unit->UnitStatus = $this->GetPostValue("UnitStatus",1);
            $unit->AssetId = $this->GetPostValue("AssetId");
            $unit->NoChasis = $this->GetPostValue("NoChasis");
            $unit->NoMesin = $this->GetPostValue("NoMesin");
            $unit->KmPosition = $this->GetPostValue("KmPosition");
            if ($unit->KmPosition == ""){
                $unit->KmPosition = 0;
            }
            $unit->HmPosition = $this->GetPostValue("HmPosition");
            if ($unit->HmPosition == ""){
                $unit->HmPosition = 0;
            }
            $unit->ProdYear = $this->GetPostValue("ProdYear");
            $unit->SnNo = $this->GetPostValue("SnNo");
            $unit->CreatebyId = $this->userUid;
			if ($this->DoInsert($unit)) {
                $this->persistence->SaveState("info", sprintf("Data Units: '%s' Dengan Kode: %s telah berhasil disimpan.", $unit->UnitCode, $unit->UnitName));
                redirect_url("master.units");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unit->UnitCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		$loader = new UnitType();
        $utype = $loader->LoadAll($this->userCompanyId);
        $this->Set("unittype", $utype);
        $loader = new UnitBrand();
        $ubrand = $loader->LoadAll($this->userCompanyId);
        $this->Set("unitbrand", $ubrand);
        $loader = new UnitClass();
        $uclass = $loader->LoadAll($this->userCompanyId);
        $this->Set("unitclass", $uclass);
		$this->Set("units", $unit);
        $this->Set("company", new Company($this->userCompanyId));
        $loader = new Asset();
        $assets = $loader->LoadByEntity($this->userCompanyId);
        $this->Set("assets", $assets);
	}

	private function DoInsert(Units $unit) {
		if ($unit->EntityId == "") {
			$this->Set("error", "Kode Unit masih kosong");
			return false;
		}
        if ($unit->UnitCode == "") {
			$this->Set("error", "Kode units masih kosong");
			return false;
		}
		if ($unit->UnitName == "") {
			$this->Set("error", "Nama units masih kosong");
			return false;
		}
		if ($unit->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/unittype.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unitclass.php");
        require_once(MODEL . "asset/asset.php");
        $loader = null;
        $unit = new Units();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $unit->Id = $id;
            $unit->EntityId = $this->userCompanyId;
            $unit->TypeCode = $this->GetPostValue("TypeCode");
            $unit->UnitCode = $this->GetPostValue("UnitCode");
            $unit->UnitName = $this->GetPostValue("UnitName");
            $unit->UnitModel = $this->GetPostValue("UnitModel");
            $unit->BrandCode = $this->GetPostValue("BrandCode");
            $unit->ClassCode = $this->GetPostValue("ClassCode");
            $unit->UnitStatus = $this->GetPostValue("UnitStatus",1);
            $unit->AssetId = $this->GetPostValue("AssetId");
            $unit->NoChasis = $this->GetPostValue("NoChasis");
            $unit->NoMesin = $this->GetPostValue("NoMesin");
            $unit->KmPosition = $this->GetPostValue("KmPosition");
            if ($unit->KmPosition == ""){
                $unit->KmPosition = 0;
            }
            $unit->HmPosition = $this->GetPostValue("HmPosition");
            if ($unit->HmPosition == ""){
                $unit->HmPosition = 0;
            }
            $unit->ProdYear = $this->GetPostValue("ProdYear");
            $unit->SnNo = $this->GetPostValue("SnNo");
            $unit->UpdatebyId = $this->userUid;
			if ($this->DoUpdate($unit)) {
				$this->persistence->SaveState("info", sprintf("Data Units: '%s' Dengan Kode: %s telah berhasil diupdate.", $unit->UnitName, $unit->UnitCode));
                redirect_url("master.units");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $unit->UnitCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data Unit sebelum melakukan edit data !");
                redirect_url("master.units");
		    }
			$unit = $unit->FindById($id);
			if ($unit == null) {
				$this->persistence->SaveState("error", "Data Units yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.units");
			}
		}
        $loader = new UnitType();
        $utype = $loader->LoadAll($this->userCompanyId);
        $this->Set("unittype", $utype);
        $loader = new UnitBrand();
        $ubrand = $loader->LoadAll($this->userCompanyId);
        $this->Set("unitbrand", $ubrand);
        $loader = new UnitClass();
        $uclass = $loader->LoadAll($this->userCompanyId);
        $this->Set("unitclass", $uclass);
        $this->Set("units", $unit);
        $this->Set("company", new Company($this->userCompanyId));
        $loader = new Asset();
        $assets = $loader->LoadByEntity($this->userCompanyId);
        $this->Set("assets", $assets);
	}

	private function DoUpdate(Units $unit) {
		if ($unit->EntityId == "") {
			$this->Set("error", "Kode Unit masih kosong");
			return false;
		}

        if ($unit->UnitCode == "") {
			$this->Set("error", "Kode units masih kosong");
			return false;
		}

        if ($unit->UnitName == "") {
			$this->Set("error", "Nama units masih kosong");
			return false;
		}

		if ($unit->Update($unit->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data units sebelum melakukan hapus data !");
			redirect_url("master.units");
		}

		$unit = new Units();
		$unit = $unit->FindById($id);
        if ($unit == null) {
			$this->persistence->SaveState("error", "Data units yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.units");
		}

		if ($unit->Delete($unit->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Units: '%s' Dengan Kode: %s telah berhasil dihapus.", $unit->UnitCode, $unit->TypeCode));
            redirect_url("master.units");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data units: '%s'. Message: %s", $unit->UnitCode, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.units");
	}

    public function upload(){
        // untuk melakukan upload dan update data sparepart
        if (count($this->postData) > 0) {
            // Ada data yang di upload...
            $this->doUpload();
            redirect_url("master.units");
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
        require_once(MODEL . "master/unittype.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unitclass.php");

        // Step #01: Baca mapping kode shift
        $sheet = $phpExcel->getSheetByName("Unit List");
        $maxRow = $sheet->getHighestRow();
        $startFrom = 4;
        $sql = null;
        $nmr = 0;
        for ($i = $startFrom; $i <= $maxRow; $i++) {
            $units = new Units();
            $nmr++;
            // OK kita lihat apakah User berbaik hati menggunakan ID atau tidak
            $uCode = trim($sheet->getCellByColumnAndRow(1, $i)->getCalculatedValue());
            $uType = trim($sheet->getCellByColumnAndRow(2, $i)->getCalculatedValue());
            $uBrand = trim($sheet->getCellByColumnAndRow(3, $i)->getCalculatedValue());
            $uModel = trim($sheet->getCellByColumnAndRow(4, $i)->getCalculatedValue());
            $uClass = trim($sheet->getCellByColumnAndRow(5, $i)->getCalculatedValue());
            $uName = trim($sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue());
            $uSno = $sheet->getCellByColumnAndRow(7, $i)->getCalculatedValue();
            $uPrYear = $sheet->getCellByColumnAndRow(8, $i)->getCalculatedValue();

            if ($uCode == "" || $uCode == null || $uCode == '-'){
                $infoMessages[] = sprintf("[%d] Unit Code: -%s- tidak valid! Pastikan Unit Code pada template sudah benar!",$nmr,$uCode);
                continue;
            }
            if ($uType == "" || $uType == null || $uType == '-'){
                $infoMessages[] = sprintf("[%d] Unit Type: -%s- tidak valid! Pastikan Unit Type pada template sudah benar!",$nmr,$uType);
                continue;
            }
            if ($uBrand == "" || $uBrand == null || $uBrand == '-'){
                $infoMessages[] = sprintf("[%d] Unit Brand: -%s- tidak valid! Pastikan Unit Brand pada template sudah benar!",$nmr,$uBrand);
                continue;
            }
            if ($uName == "" || $uName == null || $uName == '-'){
                //$infoMessages[] = sprintf("[%d] Unit Name: -%s- tidak valid! Pastikan Unit Name pada template sudah benar!",$nmr,$uName);
                //continue;
                if ($uType == $uModel){
                    $uName = $uModel;
                }else{
                    $uName = $uType.' '.$uModel;
                }
            }
            //periksa unit type
            $ctype = new UnitType();
            $ctype = $ctype->FindByName($this->userCompanyId,$uType);
            if($ctype == null){
                $infoMessages[] = sprintf("[%d] Unit Type: -%s- tidak valid! Pastikan Unit Type pada template sudah benar!",$nmr,$uType);
                continue;
            }else{
                $ctype = $ctype->TypeCode;
            }
            //periksa unit brand
            $cbrand = new UnitBrand();
            $cbrand = $cbrand->FindByBrand($this->userCompanyId,$uBrand);
            if($cbrand == null){
                $infoMessages[] = sprintf("[%d] Unit Brand: -%s- tidak valid! Pastikan Unit Brand pada template sudah benar!",$nmr,$uBrand);
                continue;
            }else{
                $cbrand = $cbrand->BrandCode;
            }
            //periksa unit class
            $cclass = new UnitClass();
            $cclass = $cclass->FindByName($this->userCompanyId,$uClass);
            if($cclass == null){
                $cclass = "";
            }else{
                $cclass = $cclass->ClassCode;
            }
            $xunits = null;
            $isnew = true;
            $isoke = true;
            $uId = 0;
            $units->EntityId = $this->userCompanyId;
            $units->UnitCode = $uCode;
            $units->TypeCode = $ctype;
            $units->BrandCode = $cbrand;
            $units->ClassCode = $cclass;
            $units->UnitModel = $uModel;
            $units->UnitName = $uName;
            $units->SnNo = $uSno;
            $units->ProdYear = $uPrYear;
            $xunits = new Units();
            $xunits = $xunits->FindByCode($this->userCompanyId,$uCode);
            if ($xunits != null){
                $isnew = false;
            }
            // mulai proses update
            $this->connector->BeginTransaction();
            $hasError = false;
            $uId = 0;
            if ($isnew) {
                $rs = $units->Insert();
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal upload Data Unit -> Kode: %s - Nama: %s Message: %s",$nmr,$uCode,$uName,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $uId = $units->Id;
                }
            }else{
                $rs = $units->Update($xunits->Id);
                if ($rs < 0) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal Update Data Unit -> Kode: %s - Nama: %s Message: %s",$nmr,$uCode,$uName,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $uId = $xunits->Id;
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
            $infoMessages[] = "Data Unit yang ERROR tidak di-entry ke system sedangkan yang lainnya tetap dimasukkan.";
        }
        $infoMessages[] = "Proses Upload Data Unit selesai. Jumlah data yang diproses: " . $processedData;
        $this->persistence->SaveState("info", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $infoMessages)));

        // Completed...
    }

    public function template(){
        // untuk melakukan download template
        require_once(MODEL . "master/unittype.php");
        require_once(MODEL . "master/unitbrand.php");
        require_once(MODEL . "master/unitclass.php");
        $utype = new UnitType();
        $utype = $utype->LoadAll($this->userCompanyId);
        $this->Set("utype",$utype);
        $ubrand = new UnitBrand();
        $ubrand = $ubrand->LoadAll($this->userCompanyId);
        $this->Set("ubrand",$ubrand);
        $uclass = new UnitClass();
        $uclass = $uclass->LoadAll($this->userCompanyId);
        $this->Set("uclass",$uclass);
    }
}
