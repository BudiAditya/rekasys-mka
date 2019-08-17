<?php

class DepreciationController extends AppController {
	private $userCompanyId = null;
	const NOT_FOUND_OR_DELETED = 1;
	const YEAR_TOO_EARLY = 2;
	const MONTH_TOO_EARLY = 3;
	const DAYS_TOO_LATE = 4;
	const ALREADY_EXISTS = 5;
	const PROCESS_SUCCESS = 6;
	const PROCESS_FAILED = 7;

	protected function Initialize() {
		require_once(MODEL . "asset/asset.php");
		require_once(MODEL . "asset/depreciation.php");

		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function history($assetId = null) {
        require_once (MODEL . "master/company.php");
        $company = new Company($this->userCompanyId);
        $startYear = date('Y',strtotime($company->StartDate));
		if ($assetId == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih asset terlebih dahulu sebelum melihat data penyusutannya");
			redirect_url("asset.asset");
		}

		require_once(MODEL . "asset/asset_category.php");

		$asset = new Asset();
		$asset = $asset->LoadById($assetId);
		if ($asset == null || $asset->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf asset yang diminta tidak dapat ditemukan atau sudah dihapus");
			redirect_url("asset.asset");
		}

		$assetCategory = new AssetCategory();
		$assetCategory = $assetCategory->LoadById($asset->CategoryId);
		$history = new Depreciation();
		$histories = $history->LoadHistoriesByAssetId($asset->Id);

		$this->Set("asset", $asset);
		$this->Set("assetCategory", $assetCategory);
		$this->Set("histories", $histories);

		// OK coba cari data yang dikirim dari session...
		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
        $this->Set("startYear",$startYear);
	}

	public function process($assetId = null) {
		if ($assetId == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih asset terlebih dahulu sebelum melakukan proses penyusutan");
			redirect_url("asset.asset");
		}

		$asset = new Asset();
		$asset = $asset->LoadById($assetId);
		if ($asset == null || $asset->IsDeleted) {
			$message = "Maaf asset yang diminta tidak dapat ditemukan atau sudah dihapus";
			$this->persistence->SaveState("error", $message);
			redirect_url("asset.asset");
		}

		// OK kita coba proses penyusutan
		$year = $this->GetGetValue("year");
		$month = $this->GetGetValue("month");
		$lowerBound = mktime(0, 0, 0, $month, 1, $year);
		$asset->CalculateTotalDepreciation($lowerBound);

		$this->ProcessAssetDepreciation($year, $month, $asset);
	}

	private function ProcessAssetDepreciation($year, $month, Asset $asset, $useRedirect = true) {
		// Step 01: Coba cari apakah sudah ada data penyusutan atau belum + Validasi
        require_once (MODEL . "master/company.php");
        $company = new Company($this->userCompanyId);
        $stYear = date("Y",strtotime($company->StartDate));
        $stMonth = date("n",strtotime($company->StartDate));
		$assetYear = (int)$asset->FormatPurchaseDate("Y");
		$assetMonth = (int)$asset->FormatPurchaseDate("n");
        $ldYear = (int)$asset->FormatLastDep("Y");
        $ldMonth = (int)$asset->FormatLastDep("n");
		if ($year < $assetYear) {
			// Tahun penyusutan < tahun pembelian asset
			$message = "Tidak bisa depresiasi asset ! Tahun depresiasi < Tahun pembelian asset !";
			if ($useRedirect) {
				$this->persistence->SaveState("error", $message);
				redirect_url("asset.depreciation/history/" . $asset->Id);
			} else {
				return array(DepreciationController::YEAR_TOO_EARLY, $message);
			}
		} else if ($year == $assetYear && $month < $assetMonth) {
			// Case untuk tahun yang sama... jika bulan masih sama atau lebih kecil tolak
			$message = "Tidak bisa depresiasi asset ! Periode depresiasi < Tanggal pembelian asset !";
			if ($useRedirect) {
				$this->persistence->SaveState("error", $message);
				redirect_url("asset.depreciation/history/" . $asset->Id);
			} else {
				return array(DepreciationController::MONTH_TOO_EARLY, $message);
			}
		} else if ($year == $ldYear && $month < $ldMonth) {
            // Case untuk tahun yang sama... jika bulan masih sama atau lebih kecil tolak
            $message = "Tidak bisa depresiasi asset ! Periode Depresiasi < Tanggal depresiasi terakhir !";
            if ($useRedirect) {
                $this->persistence->SaveState("error", $message);
                redirect_url("asset.depreciation/history/" . $asset->Id);
            } else {
                return array(DepreciationController::MONTH_TOO_EARLY, $message);
            }
        }


		$depreciation = new Depreciation();
		$depreciation = $depreciation->LoadByAssetAndDate($asset->Id, $year, $month);
		if ($depreciation != null) {
			$message = sprintf("Depresiasi Asset Kode: %s sudah ada!", $asset->AssetCode);
			if ($useRedirect) {
				$this->persistence->SaveState("error", $message);
				redirect_url("asset.depreciation/detail/" . $depreciation->Id);
			} else {
				return array(DepreciationController::ALREADY_EXISTS, $message);
			}
		}

		// Step 02: Let's the magic begin...
		$depreciation = $asset->CalculateDepreciation($year, $month,$stYear,$stMonth);
		if ($depreciation->Amount <= 0) {
			$message = sprintf("Gagal menyimpan depresiasi asset bulan %d tahun %d. Error: Asset sudah tidak ada secara pembukuan !", $month, $year);
			if ($useRedirect) {
				$this->persistence->SaveState("error", $message);
				redirect_url("asset.depreciation/history/" . $asset->Id);
			} else {
				return array(DepreciationController::NOT_FOUND_OR_DELETED, $message);
			}
		}

		$depreciation->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		$rs = $depreciation->Insert();
		if ($rs == 1) {
		    //$rs = $asset->Update()
			$message = sprintf("Data depresiasi asset: %s telah berhasil disimpan.", $asset->AssetCode);
			if ($useRedirect) {
				$this->persistence->SaveState("info", $message);
				redirect_url("asset.depreciation/detail/" . $depreciation->Id);
			} else {
				return array(DepreciationController::PROCESS_SUCCESS, $message, $asset);
			}
		} else {
			$message = sprintf("Gagal menyimpan depresiasi asset bulan %d tahun %d. Error: %s", $month, $year, $this->connector->GetErrorMessage());
			if ($useRedirect) {
				$this->persistence->SaveState("error", $message);
				redirect_url("asset.depreciation/history/" . $asset->Id);
			} else {
				return array(DepreciationController::PROCESS_FAILED, $message);
			}
		}

		// To comply return type....
		// Seharusnya uda kena redirect klo ga uda lengkap semua returnya
		return array(null, null);
	}

	public function detail($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda belum memilih detail penyusutan yang akan dilihat. Mohon pilih asset lalu pilih history penyusutan");
			redirect_url("asset.asset");
		}
        require_once(MODEL . "asset/asset_category.php");

		$depreciation = new Depreciation();
		$depreciation = $depreciation->LoadById($id);
		if ($depreciation == null || $depreciation->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf data penyusutan yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("asset.depreciation/history/" . $depreciation->AssetId);
		}

		$asset = new Asset();
		$asset = $asset->LoadById($depreciation->AssetId);

		// Cari-cari data pendukung....
		$month = $depreciation->FormatDepreciationDate("n");
		$year = $depreciation->FormatDepreciationDate("Y");
		// Data 1: Total semua penyusutan sebelum tahun yang ada pada detail...
		$temp = mktime(0, 0, 0, 1, 1, $year);
		$this->connector->CommandText =
"SELECT SUM(a.amount) AS total
FROM ac_asset_depreciation AS a
WHERE a.is_deleted = 0 AND a.asset_id = ?id AND a.depreciation_date < ?lowerBound";
		$this->connector->AddParameter("?id", $asset->Id);
		$this->connector->AddParameter("?lowerBound", date(SQL_DATETIME, $temp));
		$totalPrevYear = $this->connector->ExecuteScalar();
		// Data 2: Total penyusutan dari awal tahun s.d. sebelum bulan pada depresiasi yang dipilih
		$temp = mktime(0, 0, 0, $month, 1, $year);
		$this->connector->CommandText =
"SELECT SUM(a.amount) AS total
FROM ac_asset_depreciation AS a
WHERE a.is_deleted = 0 AND a.asset_id = ?id AND a.depreciation_date BETWEEN ?lowerBound AND ?upperBound";
		$this->connector->AddParameter("?upperBound", date(SQL_DATETIME, $temp));
		$totalRunningYear = $this->connector->ExecuteScalar();

		$this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
		$this->Set("asset", $asset);
		$this->Set("depreciation", $depreciation);

		$this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("totalPrevYear", $totalPrevYear);
		$this->Set("totalRunningYear", $totalRunningYear);

        $assetCategory = new AssetCategory();
        $assetCategory = $assetCategory->LoadById($asset->CategoryId);
        $this->Set("assetCategory", $assetCategory);
		// OK coba cari data yang dikirim dari session...
		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda belum memilih detail penyusutan yang akan dilihat. Mohon pilih asset lalu pilih history penyusutan");
			redirect_url("asset.asset");
		}
        require_once(MODEL . "asset/asset_category.php");

		$depreciation = new Depreciation();
		$depreciation = $depreciation->LoadById($id);
		if ($depreciation == null || $depreciation->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf data penyusutan yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("asset.depreciation/history/" . $depreciation->AssetId);
		}

		if (count($this->postData) > 0) {
			$depreciation->Amount = $this->GetPostValue("Amount");
			if ($this->ValidateData($depreciation)) {
				$rs = $depreciation->Update($depreciation->Id);
				if ($rs == 1) {
					$this->persistence->SaveState("info", "Perubahan jumlah depresiasi asset sudah disimpan.");
					redirect_url("asset.depreciation/detail/" . $depreciation->Id);
				} else {
					$this->Set("error", "Gagal merubah jumlah depresiasi ! Message: " . $this->connector->GetErrorMessage());
				}
			}
		}

		$asset = new Asset();
		$asset = $asset->LoadById($depreciation->AssetId);
		$month = $depreciation->FormatDepreciationDate("n");
		$year = $depreciation->FormatDepreciationDate("Y");

		$this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
		$this->Set("asset", $asset);
		$this->Set("depreciation", $depreciation);

        $assetCategory = new AssetCategory();
        $assetCategory = $assetCategory->LoadById($asset->CategoryId);
        $this->Set("assetCategory", $assetCategory);

		$this->Set("month", $month);
		$this->Set("year", $year);
	}

	private function ValidateData(Depreciation $depreciation) {
		if ($depreciation->Amount == null || $depreciation->Amount <= 0) {
			$this->Set("error", "Maaf jumlah depresiasi tidak bisa kosong atau <= 0");
			return false;
		}

		return true;
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda belum memilih detail penyusutan yang akan dihapus. Mohon pilih asset lalu pilih history penyusutan");
			redirect_url("asset.asset");
		}

		$depreciation = new Depreciation();
		$depreciation = $depreciation->LoadById($id);
		if ($depreciation == null || $depreciation->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf data penyusutan yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("asset.depreciation/history/" . $depreciation->AssetId);
		}

		// Checking... walau tidak dipakai pada tampilan
		$asset = new Asset();
		$asset = $asset->LoadById($depreciation->AssetId);
		$month = $depreciation->FormatDepreciationDate("n");
		$year = $depreciation->FormatDepreciationDate("Y");
		$monthNames = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
		$rs = $depreciation->Delete($depreciation->Id);
		if ($rs == 1) {
			$this->Set("info", sprintf("Data penyusutan %s %d telah dihapus.", $monthNames[$month], $year));
		} else {
			$this->Set("error", sprintf("Gagal menghapus data penyusutan %s %d ! Message: %s", $monthNames[$month], $year, $this->connector->GetErrorMessage()));
		}

		redirect_url("asset.depreciation/history/" . $depreciation->AssetId);
	}

	public function process_all() {
	    require_once (MODEL . "master/company.php");
	    $company = new Company($this->userCompanyId);
	    $startYear = date('Y',strtotime($company->StartDate));
		if (count($this->postData) > 0) {
			$month = $this->GetPostValue("month");
			$year = $this->GetPostValue("year");
			$lowerBound = mktime(0, 0, 0, $month, 1, $year);

			// OK kita ambil semua asset Company yang bersangkutan yang mana jumlah depresiasinya belum 0
			$this->connector->CommandText =
"SELECT a.id, a.qty * a.price AS price , SUM(b.amount) AS total_depreciation
FROM ac_asset_master AS a
	LEFT JOIN (
		SELECT aa.asset_id, SUM(aa.amount) AS amount
		FROM ac_asset_depreciation AS aa
		WHERE aa.is_deleted = 0 AND aa.depreciation_date < ?lowerBound
		GROUP BY aa.asset_id
	) AS b ON a.id = b.asset_id
WHERE a.is_deleted = 0 AND a.entity_id = ?sbu AND (a.qty * a.price) - COALESCE(b.amount, 0) > 0
GROUP BY a.id, (a.qty * a.price)";
			$this->connector->AddParameter("?lowerBound", date(SQL_DATETIME, $lowerBound));
			$this->connector->AddParameter("?sbu", $this->userCompanyId);
			$rs = $this->connector->ExecuteQuery();
			if ($rs != null) {
				$successCount = 0;
				$skippedCount = 0;
				$failedCount = 0;
				$duplicateCount = 0;
				$skipped = array();
				$failed = array();
				$duplicate = array();
				while ($row = $rs->FetchAssoc()) {
					$asset = new Asset();
					$asset = $asset->LoadById($row["id"]);
					$asset->TotalDepreciation = $row["total_depreciation"] == null ? 0 : $row["total_depreciation"];
					list($returnCode, $message) = $this->ProcessAssetDepreciation($year, $month, $asset, false);
					switch ($returnCode) {
						case DepreciationController::NOT_FOUND_OR_DELETED:
							$failedCount++;
							break;
						case DepreciationController::YEAR_TOO_EARLY:
						case DepreciationController::MONTH_TOO_EARLY:
						case DepreciationController::DAYS_TOO_LATE:
							$skippedCount++;
							$skipped[] = $asset->AssetCode;
							break;
						case DepreciationController::ALREADY_EXISTS:
							$duplicateCount++;
							$duplicate[] = $asset->AssetCode;
							break;
						case DepreciationController::PROCESS_SUCCESS:
							$successCount++;
							break;
						case DepreciationController::PROCESS_FAILED:
							$failedCount++;
							$failed[] = $asset->AssetCode;
							break;
					}
				}

				// OK semua asset sudah terproses apapun hasilnya mari kita
				$message = "Proses penyusutan semua asset Bulan $month Tahun $year selesai. Hasil:<br /><br />";
				$message .= "Sukses : " . $successCount . " asset";
				if ($skippedCount > 0) {
					$message .= "<br />Dilewat : " . $skippedCount . " asset (" . implode(", ", $skipped) . ")";
				}
				if ($duplicateCount > 0) {
					$message .= "<br />Duplikat : " . $duplicateCount . " asset (" . implode(", ", $duplicate) . ")";
				}
				if ($failedCount > 0) {
					$message .= "<br />Gagal : " . $failedCount . " asset (" . implode(", ", $failed) . ")";
				}

				$this->persistence->SaveState("info", $message);
			} else {
				// Error ???
				$this->persistence->SaveState("error", "Encountered Database Error: " . $this->connector->GetErrorMessage());
			}

			// Apapun hasilnya redirect...
			redirect_url("asset.depreciation/process_all");

		} else {
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->Set("info", "Modul ini diperuntukkan bagi login Company. Penggunaan pada login CORP tidak akan berjalan.");
			}
		}

		// OK coba cari data yang dikirim dari session...
		// Funtion ini akan sering redirect agar tidak ada insiden ke proses 2x karena refresh
		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
		$this->Set("startYear",$startYear);
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
            $depreciation = new Depreciation();
            $report = $depreciation->LoadDepreciationByPeriod($this->userCompanyId,$categoryId,$depMonth,$depYear);
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


// End of File: depreciation_controller.php
