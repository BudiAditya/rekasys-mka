<?php
class TaxRateController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "common/tax_rate.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 100);
		$settings["columns"][] = array("name" => "a.taxsch_cd", "display" => "Tax Code", "width" => 100);
		$settings["columns"][] = array("name" => "a.taxsch_desc", "display" => "Tax SCheme", "width" => 350);
		$settings["columns"][] = array("name" => "CASE a.incl_excl WHEN 1 THEN 'Include' ELSE 'Exclude' END", "display" => "Include / Exclue", "width" => 100);
        $settings["columns"][] = array("name" => "CASE a.tax_mode WHEN 1 THEN 'Masukan' ELSE 'Keluaran' END", "display" => "Tax Type", "width" => 100);

		$settings["filters"][] = array("name" => "a.taxsch_cd", "display" => "Tax Coce");
		$settings["filters"][] = array("name" => "a.taxsch_desc", "display" => "Tax Scheme");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Tax Scheme & Rate";
			if($acl->CheckUserAccess("taxrate", "add_master", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.taxrate/add_header", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("taxrate", "edit_master", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.taxrate/edit_header/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("taxrate", "deletemaster", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.taxrate/deletemaster/%s", "Class" => "bt_delete", "ReqId" => 1);
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_taxschmaster AS a JOIN cm_company AS b ON a.entity_id = b.entity_id";
			$settings["where"] = "a.entity_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	/**
	 * Untuk masukkin data kita perlu beberapa step agar lebih mudah...
	 * Step 1: Header Skema Pajak
	 */
	public function add_header() {
		require_once(MODEL . "master/company.php");
		// Periksa apakah ada data session ? Jika ada maka pakai yang dari session jika valid
		if ($this->persistence->StateExists("common.taxrate.taxRate")) {
			$taxRate = $this->persistence->LoadState("common.taxrate.taxRate");
		} else {
			$taxRate = null;
		}
		if (!is_a($taxRate, "TaxRate")) {
			$taxRate = new TaxRate();
		}

		// Kalau ada data yang dikirim kita proses
		if (count($this->postData) > 0) {
			$taxRate->EntityId = $this->userCompanyId;
			$taxRate->TaxSchCd = $this->GetPostValue("TaxSchCd");
			$taxRate->TaxSchDesc = $this->GetPostValue("TaxSchDesc");
            $taxRate->TaxMode = $this->GetPostValue("TaxMode");
			if (isset($this->postData["InclExcl"])) {
				$taxRate->InclExcl = "1";
			} else {
				$taxRate->InclExcl = "2";
			}

			if ($this->ValidateHeader($taxRate)) {
				$this->persistence->SaveState("common.taxrate.taxRate", $taxRate);
				redirect_url("common.taxrate/add_detail");
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("company", $company);
		$this->Set("taxRate", $taxRate);
	}

	private function ValidateHeader(TaxRate $taxRate) {
		if ($taxRate->EntityId == null) {
			$this->Set("error", "Mohon Pilih Company terlebih dahulu.");
			return false;
		}
		if ($taxRate->TaxSchCd == null) {
			$this->Set("error", "Mohon masukkan kode skema pajak terlebih dahulu.");
			return false;
		}
		if ($taxRate->TaxSchDesc == null) {
			$this->Set("error", "Mohon masukkan deskripsi skema pajak terlebih dahulu");
			return false;
		}
        if ($taxRate->TaxMode == null || $taxRate->TaxMode == 0) {
            $this->Set("error", "Mohon pilih jenis pajak terlebih dahulu");
            return false;
        }

		return true;
	}

	/**
	 * Step 2: Nah disini yang hueebooohhhh detail
	 * Syarat: Sudah ada Skema Pajak Header
	 */
	public function add_detail() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");

		//$taxRate = new TaxRate();
		$taxRate = $this->persistence->LoadState("common.taxrate.taxRate");
		if (!is_a($taxRate, "TaxRate")) {
			// BEEUUUHHHHH direct linking ??? KICK
			redirect_url("common.taxrate/add_header");
			return;
		}

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$codes = $this->GetPostValue("TaxCd", array());
			$types = $this->GetPostValue("TaxType", array());
			$tariffs = $this->GetPostValue("TaxTarif", array());
			$accIds = $this->GetPostValue("AccId", array());
			$reversalAccIds = $this->GetPostValue("ReversalAccId", array());
			$deductables = $this->GetPostValue("Deductable", array());

			// Berhubung ini add maka kita harus reset details sebelum memasukkan data baru
			// Berguna jika pada step confirm user balik untuk edit data
			$taxRate->TaxRateDetails = array();

			$max = count($codes);
			for ($i = 0; $i < $max; $i++) {
				$detail = new TaxRateDetail();
				$detail->TaxCd = $codes[$i];
				$detail->TaxType = $types[$i];
				$detail->TaxTarif = $tariffs[$i];
				$detail->AccNoId = $accIds[$i];
				$detail->ReversalAccNoId = $reversalAccIds[$i];
				$detail->Deductable = (int)$deductables[$i];

				if ($detail->AccNoId == "") {
					$detail->AccNoId = null;
				}
				if ($detail->ReversalAccNoId == "") {
					$detail->ReversalAccNoId = null;
				}

				$taxRate->TaxRateDetails[] = $detail;
			}

			// OK Now Checking...
			if ($this->ValidateDetails($taxRate)) {
				// OK simpan lagi data-datanya
				$this->persistence->SaveState("common.taxrate.taxRate", $taxRate);
				redirect_url("common.taxrate/add_confirm");
			}
		}

		$company = new Company();
		$company->LoadById($taxRate->EntityId);
		$loader = new Coa();
		$accounts = $loader->LoadByLevel($this->userCompanyId,3);
		// Convert details agar bisa diproses oleh JavaScript
		$details = array();
		foreach ($taxRate->TaxRateDetails as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}

		// OK Lanjut....
		$this->Set("company", $company);
		$this->Set("accounts", $accounts);
		$this->Set("taxRate", $taxRate);
		$this->Set("details", $details);
	}

	private function ValidateDetails(TaxRate $taxRate, $totalDeleted = 0) {
		//$detail = new TaxRateDetail();
		if (count($taxRate->TaxRateDetails) - $totalDeleted == 0) {
			$this->Set("error", "Maaf anda belum memasukkan detail skema pajak");
			return false;
		}

		foreach ($taxRate->TaxRateDetails as $idx => $detail) {
			if ($detail->TaxCd == null) {
				$this->Set("error", sprintf("Kode Pajak No. %d masih kosong", $idx + 1));
				return false;
			}
			if ($detail->TaxType == null) {
				$this->Set("error", sprintf("Type Pajak No. %d masih kosong", $idx + 1));
				return false;
			}
			if ($detail->TaxTarif == null) {
				$this->Set("error", sprintf("Tarif Pajak No. %d masih kosong", $idx + 1));
				return false;
			}
//			if ($detail->AccNoId == null) {
//				$this->Set("error", sprintf("No. Akun Pajak No. %d masih kosong", $idx + 1));
//				return false;
//			}
//			if ($detail->ReversalAccNoId == null) {
//				$this->Set("error", sprintf("No. Akun Pembalik Pajak No. %d masih kosong", $idx + 1));
//				return false;
//			}
		}

		// No problem detected
		return true;
	}

	/**
	 * Step 3 entry data skema pajak
	 * Fungsi untuk show data sebelum di commit ke database
	 */
	public function add_confirm() {
		require_once(MODEL . "master/company.php");

		//$taxRate = new TaxRate();
		$taxRate = $this->persistence->LoadState("common.taxrate.taxRate");
		if (!is_a($taxRate, "TaxRate")) {
			// BEEUUUHHHHH direct linking ??? KICK
			redirect_url("common.taxrate/add_header");
			return;
		}
		if (count($taxRate->TaxRateDetails) == 0) {
			// BEEUUUHHHHH direct linking ??? KICK
			redirect_url("common.taxrate/add_detail");
			return;
		}

		// OK klo ada Post Artinya COMMIT
		if (count($this->postData) > 0) {
			$this->connector->BeginTransaction();
			if ($this->doAdd($taxRate)) {
				$this->connector->CommitTransaction();

				// Sukses cuy...
				$this->persistence->SaveState("info", sprintf("Skema Pajak: %s (Kode: %s) telah berhasil disimpan", $taxRate->TaxSchDesc, $taxRate->TaxSchCd));
				$this->persistence->DestroyState("common.taxrate.taxRate");
				redirect_url("common.taxrate");
			} else {
				$this->connector->RollbackTransaction();
			}
		}

		$company = new Company();
		$company->LoadById($taxRate->EntityId);

		$this->Set("company", $company);
		$this->Set("taxRate", $taxRate);
	}

	private function doAdd(TaxRate $taxRate) {
		$rs = $taxRate->Insert();
		if ($rs != 1) {
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf kode pajak pada header skema pajak sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan header skema pajak. Message: " . $errMsg);
			}
			return false;
		}

		foreach ($taxRate->TaxRateDetails as $idx => $detail) {
			$detail->TaxSchId = $taxRate->Id;
			$rs = $detail->Insert();
			if ($rs == 1) {
				// Lanjutttt
				continue;
			}

			// Gagal Insert Detail
			$no = $idx + 1;
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf kode pajak pada detail skema pajak No. $no sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan detail header skema pajak No. $no. Message: " . $errMsg);
			}
			return false;
		}

		return true;
	}

	/**
	 * Sama seperti add maka editnya juga multiple step.
	 * Step 1: Edit data header
	 *
	 * @param int $id
	 */
	public function edit_header($id = null) {
		if ($id == null) {
			redirect_url("common.taxrate");
			return;
		}

		require_once(MODEL . "master/company.php");
		// Periksa apakah ada data yang diedit
		if ($this->persistence->StateExists("common.taxrate.taxRate")) {
			$taxRate = $this->persistence->LoadState("common.taxrate.taxRate");
			if ($taxRate->Id != $id) {
				$this->Set("info", "Old data found and cleaned because ID is mismatch!");
				$this->persistence->DestroyState("common.taxrate.taxRate");
				// Hwe... kok bisa ID nya ga sama dengan param? dateng dari add trus edit kah atau edit id 1 trus ganti id 2? INVALIDATE!
				$taxRate = null;
			}
		} else {
			$taxRate = null;
		}
		if (!is_a($taxRate, "TaxRate")) {
			$taxRate = new TaxRate();
			$taxRate = $taxRate->FindById($id);
			$taxRate->LoadDetails();
		}
		if ($taxRate == null) {
			// Sampai tahap ini masih null ?
			redirect_url("common.taxrate");
		}

		// Kalau ada data yang dikirim kita proses
		if (count($this->postData) > 0) {
			$taxRate->EntityId = $this->userCompanyId;
			$taxRate->TaxSchCd = $this->GetPostValue("TaxSchCd");
			$taxRate->TaxSchDesc = $this->GetPostValue("TaxSchDesc");
            $taxRate->TaxMode = $this->GetPostValue("TaxMode");
			if (isset($this->postData["InclExcl"])) {
				$taxRate->InclExcl = "1";
			} else {
				$taxRate->InclExcl = "2";
			}

			if ($this->ValidateHeader($taxRate)) {
				$this->persistence->SaveState("common.taxrate.taxRate", $taxRate);
				redirect_url("common.taxrate/edit_detail/" . $taxRate->Id);
			}
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("company", $company);
		$this->Set("taxRate", $taxRate);
	}

	/**
	 * Edit data detail skema pajak akan mirip dengan yang add_detail
	 * Step 2: Edit detail....
	 *
	 * @param int $id
	 */
	public function edit_detail($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");

		//$taxRate = new TaxRate();
		$taxRate = $this->persistence->LoadState("common.taxrate.taxRate");
		if (!is_a($taxRate, "TaxRate")) {
			// BEEUUUHHHHH direct linking ??? KICK
			redirect_url("common.taxrate");
			return;
		}
		if ($taxRate->Id != $id) {
			// Hwe... ajaib masa ID param sama ID yang di session beda...
			redirect_url("common.taxrate/edit_header/" . $taxRate->Id);
			return;
		}

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$ids = $this->GetPostValue("Id", array());
			$codes = $this->GetPostValue("TaxCd", array());
			$types = $this->GetPostValue("TaxType", array());
			$tariffs = $this->GetPostValue("TaxTarif", array());
			$accIds = $this->GetPostValue("AccId", array());
			$reversalAccIds = $this->GetPostValue("ReversalAccId", array());
			$deductables = $this->GetPostValue("Deductable", array());
			$markDeletes = $this->GetPostValue("markDelete", array());

			// Berhubung ini add maka kita harus reset details sebelum memasukkan data baru
			// Berguna jika pada step confirm user balik untuk edit data
			$taxRate->TaxRateDetails = array();

			$max = count($codes);
			for ($i = 0; $i < $max; $i++) {
				$detail = new TaxRateDetail();
				$detail->Id = $ids[$i];
				$detail->TaxCd = $codes[$i];
				$detail->TaxType = $types[$i];
				$detail->TaxTarif = $tariffs[$i];
				$detail->AccNoId = $accIds[$i];
				$detail->ReversalAccNoId = $reversalAccIds[$i];
				$detail->Deductable = (int)$deductables[$i];
				$detail->MarkedForDeletion = in_array($ids[$i], $markDeletes);

				if ($detail->AccNoId == "") {
					$detail->AccNoId = null;
				}
				if ($detail->ReversalAccNoId == "") {
					$detail->ReversalAccNoId = null;
				}

				$taxRate->TaxRateDetails[] = $detail;
			}

			// OK Now Checking...
			if ($this->ValidateDetails($taxRate, count($markDeletes))) {
				// OK simpan lagi data-datanya
				$this->persistence->SaveState("common.taxrate.taxRate", $taxRate);
				redirect_url("common.taxrate/edit_confirm/" . $taxRate->Id);
			}
		}

		$company = new Company();
		$company->LoadById($taxRate->EntityId);
		$loader = new Coa();
		$accounts = $loader->LoadByLevel($this->userCompanyId,3);
		// Convert details agar bisa diproses oleh JavaScript
		$details = array();
		foreach ($taxRate->TaxRateDetails as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}

		// OK Lanjut....
		$this->Set("company", $company);
		$this->Set("accounts", $accounts);
		$this->Set("taxRate", $taxRate);
		$this->Set("details", $details);
	}

	public function edit_confirm($id = null) {
		require_once(MODEL . "master/company.php");

		//$taxRate = new TaxRate();
		$taxRate = $this->persistence->LoadState("common.taxrate.taxRate");
		if (!is_a($taxRate, "TaxRate")) {
			// BEEUUUHHHHH direct linking ??? KICK
			redirect_url("common.taxrate/add_header");
			return;
		}
		if (count($taxRate->TaxRateDetails) == 0) {
			// BEEUUUHHHHH direct linking ??? KICK
			redirect_url("common.taxrate/add_detail");
			return;
		}
		if ($taxRate->Id != $id) {
			// Hwe... ajaib masa ID param sama ID yang di session beda...
			redirect_url("common.taxrate/edit_header/" . $taxRate->Id);
			return;
		}

		// OK klo ada Post Artinya COMMIT
		if (count($this->postData) > 0) {
			$this->connector->BeginTransaction();
			if ($this->doEdit($taxRate)) {
				$this->connector->CommitTransaction();

				// Sukses cuy...
				$this->persistence->SaveState("info", sprintf("Perubahan Skema Pajak: %s (Kode: %s) telah berhasil disimpan", $taxRate->TaxSchDesc, $taxRate->TaxSchCd));
				$this->persistence->DestroyState("common.taxrate.taxRate");
				redirect_url("common.taxrate");
			} else {
				$this->connector->RollbackTransaction();
			}
		}

		$company = new Company();
		$company->LoadById($taxRate->EntityId);

		$this->Set("company", $company);
		$this->Set("taxRate", $taxRate);
	}

	private function doEdit(TaxRate $taxRate) {
		$rs = $taxRate->Update($taxRate->Id);
		if ($rs == -1) {
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf kode pajak pada header skema pajak sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat merubah header skema pajak. Message: " . $errMsg);
			}
			return false;
		}

		$counter = 0;
		foreach ($taxRate->TaxRateDetails as $detail) {
			// OK Cek untuk penghapusan data dulu
			if ($detail->MarkedForDeletion) {
				$rs = $detail->Delete($detail->Id);
				if ($rs == -1) {
					$this->Set("error", "Gagal hapus detail dengan ID: " . $detail->Id . ". Mohon hubungi system admin.");
					return false;
				}
			} else {
				$counter++;
				$detail->TaxSchId = $taxRate->Id;
				if ($detail->Id == null) {
					$rs = $detail->Insert();
				} else {
					$rs = $detail->Update($detail->Id);
				}
			}

			// Untuk update gagal kalau -1 (0 = no error and nothing updated)
			if ($rs == 1 || $rs == 0) {
				// Lanjutttt
				continue;
			}

			// Gagal Insert Detail
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf kode pajak pada detail skema pajak No. $counter sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan detail header skema pajak No. $counter. Message: " . $errMsg);
			}
			return false;
		}

		return true;
	}

	public function deletemaster($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data skema pajak sebelum melakukan hapus data !");
			redirect_url("common.taxrate");
		}

		$taxrate = new TaxRate();
		$taxrate = $taxrate->FindById($id);
		if ($taxrate == null) {
			$this->persistence->SaveState("error", "Data skema pajak yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.taxrate");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($taxrate->EntityId != $this->userCompanyId) {
				// Simulate not found ! Access data which belong to other Company without CORPORATE access level
				$this->persistence->SaveState("error", "Data skema pajak yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.taxrate");
			}
		}

		if ($taxrate->Delete($taxrate->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data skema pajak: '%s' Dengan Kode: %s telah berhasil dihapus.", $taxrate->TaxSchDesc, $taxrate->TaxSchCd));
			redirect_url("common.taxrate");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data skema pajak: '%s'. Message: %s", $taxrate->TaxSchDesc, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.taxrate");
	}
}
