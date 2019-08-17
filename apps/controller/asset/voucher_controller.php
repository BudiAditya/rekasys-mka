<?php

namespace Asset;

/**
 * Class VoucherController
 * @package Asset
 *
 * Untuk membuat voucher yang akan diposting ke jurnal accounting... Awalnya data yang dibuat belum berupa voucher sehingga hanya berisi angka dll
 * Sedikit tricky:
 * 	- Karena disini kita akan mengambil data berdasarkan akun bukan kategori asset...
 * 	- Akun yang digunakan harus bisa dynamic berdasarkan kategori asset >_<"
 */
class VoucherController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "asset/depreciation_journal.php");
		require_once(MODEL . "asset/asset_category.php");

		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = \Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.depreciation_date, '%d-%m-%Y')", "display" => "Tgl. Depresiasi", "width" => 100, "overrideSort" => "a.depreciation_date");
		$settings["columns"][] = array("name" => "c.name", "display" => "Kategori Asset", "width" => 150);
		$settings["columns"][] = array("name" => "FORMAT(a.total_depreciation, 2)", "display" => "Total Depresiasi", "width" => 100, "align" => "right", "overrideSort" => "a.total_depreciation");
		$settings["columns"][] = array("name" => "e.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "d.doc_no", "display" => "No. Voucher", "width" => 100);

		$settings["filters"][] = array("name" => "DATE_FORMAT(a.depreciation_date, '%d-%m-%Y')", "display" => "Tgl. Depresiasi");
		$settings["filters"][] = array("name" => "c.name", "display" => "Kategori Asset");
		$settings["filters"][] = array("name" => "COALESCE(d.doc_no, '')", "display" => "No. Voucher");

		if (!$router->IsAjaxRequest) {
			$acl = \AclManager::GetInstance();
			$settings["title"] = "Daftar Jurnal Penyusutan Asset";

			if ($acl->CheckUserAccess("asset.voucher", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "asset.voucher/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("asset.voucher", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "asset.voucher/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Mohon memilih data jurnal penyusutan asset terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data",
					"Confirm" => "");
			}
			if (count($settings["actions"]) > 0) {
				$settings["actions"][] = array("Text" => "separator", "Url" => null);
			}
			if ($acl->CheckUserAccess("asset.voucher", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "asset.voucher/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih data jurnal penyusutan asset terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("asset.voucher", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "asset.voucher/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih data jurnal penyusutan asset terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data",
					"Confirm" => "Apakah anda yakin mau menghapus data yang dipilih?\nKlik 'OK' untuk melanjutkan prosedur.");
			}
			if (count($settings["actions"]) > 0) {
				$settings["actions"][] = array("Text" => "separator", "Url" => null);
			}
			if ($acl->CheckUserAccess("asset.voucher", "post")) {
				$settings["actions"][] = array("Text" => "Post", "Url" => "asset.voucher/post", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih data jurnal penyusutan asset terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data",
					"Confirm" => "Apakah anda yakin mau mem-posting data yang dipilih?\nKlik 'OK' untuk melanjutkan prosedur.");
			}
			if ($acl->CheckUserAccess("asset.voucher", "unpost")) {
				$settings["actions"][] = array("Text" => "Un-Post", "Url" => "asset.voucher/unpost", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih data jurnal penyusutan asset terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data",
					"Confirm" => "Apakah anda yakin mau meng-unpost data yang dipilih?\nKlik 'OK' untuk melanjutkan prosedur.");
			}

			$settings["def_filter"] = 2;
			$settings["def_order"] = 2;
			$settings["def_direction"] = "desc";
			$settings["singleSelect"] = false;
		} else {
			$settings["from"] =
"ac_depreciation_journal AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ac_asset_category AS c ON a.asset_category_id = c.id
	LEFT JOIN ac_voucher_master AS d ON a.voucher_id = d.id
	JOIN sys_status_code AS e ON a.status = e.code AND e.key = 'depreciation_journal'";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		\Dispatcher::CreateInstance()->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "asset/depreciation.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/activity.php");
		$journal = new DepreciationJournal();
		$assetCategory = new \AssetCategory();
        $loader = null;
		if (count($this->postData) > 0) {
			$year = $this->GetPostValue("year");
			$month = $this->GetPostValue("month");
			$isCommit = $this->GetPostValue("commit", "0") == "1";
			$catId = $this->GetPostValue("category");
            $journal->ProjectId = $this->GetPostValue("projectId");
            $journal->DeptId = $this->GetPostValue("deptId");
            $journal->ActivityId = $this->GetPostValue("actId");

			$assetCategory = $assetCategory->LoadById($catId);
			if ($assetCategory->Id == null) {
				// Uda ah.. ga mau pusing sama nested...
				redirect_url("asset.voucher/add");
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($assetCategory->EntityId != $this->userCompanyId) {
					// Uda ah.. ga mau pusing sama nested...
					redirect_url("asset.voucher/add");
				}
			}
			$journal->EntityId = $assetCategory->EntityId;
			$journal->AssetCategoryId = $assetCategory->Id;
			$journal->DepreciationDate = mktime(0, 0, 0, $month + 1, 1, $year) - 1;	// ^_^ bulan-nya plus 1 tapi detiknya kurangi 1 lsg deh jadi tanggal terakhir bulan yang dipilih
			$journal->CalculateTotalDepreciation();

			if ($isCommit) {
				$journal->CreatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;
				if ($journal->Insert() == 1) {
					$this->persistence->SaveState("info", "Jurnal Memorial berhasil disimpan. Proses approval masih diperlukan.");
					redirect_url("asset.voucher/view/" . $journal->Id);
				} else {
					$this->Set("error", "Gagal simpan data memorial. Error: " . $this->connector->GetErrorMessage());
				}
			}
            $depreciation = new \Depreciation();
            $report = $depreciation->LoadDepreciationByPeriod($journal->EntityId,$catId,$month,$year);
		} else {
		    $report = null;
			$year = date("Y");
			$month = date("n");
			$month--;
			if ($month == 0) {
				$month = 12;
				$year--;
			}
			$isCommit = false;
		}
        $loader = new \Project();
        $this->Set("projects", $loader->LoadByEntityId($this->userCompanyId));
        $loader = new \Department();
        $this->Set("depts", $loader->LoadByEntityId($this->userCompanyId));
        $loader = new \Activity();
        $this->Set("acts", $loader->LoadByEntityId($this->userCompanyId));
		$this->Set("categories", $assetCategory->LoadByEntityId($this->userCompanyId));
		$this->Set("year", $year);
		$this->Set("month", $month);
		$this->Set("isCommit", $isCommit);
		$this->Set("journal", $journal);
        $this->Set("report",$report);
	}

	public function view($id = null) {
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/activity.php");
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih data jurnal memorial terlebih dahulu");
			redirect_url("asset.voucher");
		}

		$journal = new DepreciationJournal($id);
		if ($journal->Id != $id) {
			$this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat ditemukan");
			redirect_url("asset.voucher");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($journal->EntityId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat ditemukan");
				redirect_url("asset.voucher");
			}
		}

		$assetCategory = new \AssetCategory($journal->AssetCategoryId);
		if ($journal->VoucherId != null) {
			require_once(MODEL . "accounting/voucher.php");
			$voucher = new \Voucher($journal->VoucherId);
		} else {
			$voucher = null;
		}

        $loader = new \Project();
        $this->Set("projects", $loader->LoadByEntityId($this->userCompanyId));
        $loader = new \Department();
        $this->Set("depts", $loader->LoadByEntityId($this->userCompanyId));
        $loader = new \Activity();
        $this->Set("acts", $loader->LoadByEntityId($this->userCompanyId));
		$this->Set("category", $assetCategory);
		$this->Set("journal", $journal);
		$this->Set("voucher", $voucher);

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
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/activity.php");
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih data jurnal memorial terlebih dahulu");
			redirect_url("asset.voucher");
		}

		$journal = new DepreciationJournal($id);
		if ($journal->Id != $id) {
			$this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat ditemukan");
			redirect_url("asset.voucher");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($journal->EntityId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat ditemukan");
				redirect_url("asset.voucher");
			}
		}

		// OK hanya boleh edit status UNPOSTED
		if ($journal->StatusCode != 1 || $journal->VoucherId != null) {
			$this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat diedit karena sudah ada voucher nya");
			redirect_url("asset.voucher/view/" . $journal->Id);
		}

		if (count($this->postData) > 0) {
            $journal->ProjectId = $this->GetPostValue("projectId");
            $journal->DeptId = $this->GetPostValue("deptId");
            $journal->ActivityId = $this->GetPostValue("actId");
			$journal->UpdatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;
			$journal->TotalDepreciation = str_replace(",", "", $this->GetPostValue("amount"));

			if ($journal->Update($journal->Id)) {
				$this->persistence->SaveState("info", "Data jurnal memorial sudah berhasil diupdate.");
				redirect_url("asset.voucher/view/" . $journal->Id);
			} else {
				$this->Set("error", "Gagal update total depresiasi. Error: " . $this->connector->GetErrorMessage());
			}
		}
        $loader = new \Project();
        $this->Set("projects", $loader->LoadByEntityId($this->userCompanyId));
        $loader = new \Department();
        $this->Set("depts", $loader->LoadByEntityId($this->userCompanyId));
        $loader = new \Activity();
        $this->Set("acts", $loader->LoadByEntityId($this->userCompanyId));
		$this->Set("category", new \AssetCategory($journal->AssetCategoryId));
		$this->Set("journal", $journal);
	}

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Mohon memilih data jurnal memorial terlebih dahulu");
            redirect_url("asset.voucher");
        }

        $journal = new DepreciationJournal($id);
        if ($journal->Id != $id) {
            $this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat ditemukan");
            redirect_url("asset.voucher");
        }
        if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
            if ($journal->EntityId != $this->userCompanyId) {
                $this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat ditemukan");
                redirect_url("asset.voucher");
            }
        }

        // OK hanya boleh edit status UNPOSTED
        if ($journal->StatusCode != 1 || $journal->VoucherId != null) {
            $this->persistence->SaveState("error", "Data jurnal memorial yang diminta tidak dapat dihapus karena sudah terposting!");
            redirect_url("asset.voucher/view/" . $journal->Id);
        }

        if ($journal->Delete($journal->Id)) {
            $this->persistence->SaveState("info", "Data jurnal memorial sudah berhasil dihapus..");
        } else {
            $this->Set("error", "Gagal hapus jurnal depresiasi. Error: " . $this->connector->GetErrorMessage());
        }
        redirect_url("asset.voucher");
    }

	private function doPosting($journalId, array &$infoMessages, array &$errorMessages) {
		$indonesianMonths = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

		$journal = new DepreciationJournal($journalId);
		if ($journal->Id != $journalId) {
			// (-_-)a... WTF
			return true;	// Skip tanpa error
		}

		$assetCategory = new \AssetCategory($journal->AssetCategoryId);
		$periode = sprintf("%s %s", $indonesianMonths[$journal->FormatDepreciationDate("n")], $journal->FormatDepreciationDate("Y"));

		if ($journal->StatusCode != 1) {
			$errorMessages[] = sprintf("Jurnal memorial '%s' periode %s tidak diproses ! Status sudah bukan UNPOSTED.", $assetCategory->Name, $periode);
			return false;
		}

		$docCouter = new \DocCounter();
		$temp = $docCouter->AutoDocNoAj($journal->EntityId, $journal->DepreciationDate, 1);
		if ($temp === null) {
			$errorMessages[] = sprintf("Tidak dapat membuat VOUCHER ADJUSTMENT ! Periode %s sudah terkunci.", $periode);
			return false;
		}

		// Bikin Voucher Adjustment nya
		$voucher = new \Voucher();
		$voucher->DocumentTypeId = 1;	// Adjustment Voucher
		$voucher->DocumentNo = $temp;
		$voucher->Date = $journal->DepreciationDate;
		$voucher->EntityId = $journal->EntityId;
		$voucher->Note = "Penyusutan '" . $assetCategory->Name . "' periode: " . $periode;
		$voucher->StatusCode = 4;
		$voucher->CreatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;
		$voucher->VoucherSource = "DEPRECIATION";

		$detail = new \VoucherDetail();
		$detail->AccDebitId = $assetCategory->CostAccountId;
		$detail->AccCreditId = $assetCategory->DepreciationAccountId;
		$detail->Amount = $journal->TotalDepreciation;
		$detail->ProjectId = $journal->ProjectId;
		$detail->DepartmentId = $journal->DeptId;
		$detail->ActivityId = $journal->ActivityId;
		$voucher->Details[] = $detail;

		// OK Entry Voucher nya
		if ($voucher->Insert() != 1) {
			$errorMessages[] = sprintf("Jurnal memorial '%s' periode %s tidak diproses ! Gagal membuat VOUCHER. Error: %s", $assetCategory->Name, $periode, $this->connector->GetErrorMessage());
			return false;
		}

		// Next is voucher details
		/** @var $detail \VoucherDetail */
		foreach ($voucher->Details as $detail) {
			$detail->VoucherId = $voucher->Id;
			if ($detail->Insert() != 1) {
				$errorMessages[] = sprintf("Jurnal memorial '%s' periode %s tidak diproses ! Gagal membuat DETAIL VOUCHER. Error: %s", $assetCategory->Name, $periode, $this->connector->GetErrorMessage());
				return false;
			}
		}

		// OK Rubah status jurnal memorial
		$journal->UpdatedById = $voucher->CreatedById;
		if ($journal->Post($journal->Id, $voucher->Id) != 1) {
			$errorMessages[] = sprintf("Jurnal memorial '%s' periode %s tidak diproses ! Gagal ganti STATUS JURNAL MEMORIAL. Error: %s", $assetCategory->Name, $periode, $this->connector->GetErrorMessage());
			return false;
		}

		$infoMessages[] = sprintf("Jurnal memorial '%s' periode %s sudah diposting. No. Voucher: %s", $assetCategory->Name, $periode, $voucher->DocumentNo);
		return true;
	}

	public function post() {
		$ids = $this->GetGetValue("id", array());
		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda belum memilih jurnal memorial yang akan diposting");
			redirect_url("asset.voucher");
		}

		$infoMessages = array();
		$errorMessages = array();

		// OK Begin Transaction
		require_once(MODEL . "accounting/voucher.php");
		require_once(MODEL . "common/doc_counter.php");

		$this->connector->BeginTransaction();
		foreach ($ids as $id) {
			$this->doPosting($id, $infoMessages, $errorMessages);
		}

		// Cek apakah ada error atau tidak
		if (count($errorMessages) > 0) {
			$this->connector->RollbackTransaction();
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errorMessages) . "</li></ul>");

			$infoMessages[] = "Ditemukan error ketika proses posting. Proses dibatalkan. Mohon perbaiki kesalahan terlebih dahulu.";
		} else {
			$this->connector->CommitTransaction();
		}
		$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infoMessages) . "</li></ul>");

		if (count($ids) == 1) {
			redirect_url("asset.voucher/view/" . $ids[0]);
		} else {
			redirect_url("asset.voucher");
		}
	}

	private function doUnPosting($journalId, array &$infoMessages, array &$errorMessages) {
		$indonesianMonths = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

		$journal = new DepreciationJournal($journalId);
		if ($journal->Id != $journalId) {
			// (-_-)a... WTF
			return true;	// Skip tanpa error
		}

		// Cari Voucher Adjustment nya
		$voucher = new \Voucher($journal->VoucherId);
		$periode = sprintf("%s %s", $indonesianMonths[$journal->FormatDepreciationDate("n")], $journal->FormatDepreciationDate("Y"));

		if ($journal->StatusCode != 2) {
			$errorMessages[] = sprintf("Voucher jurnal memorial '%s' periode %s tidak diproses ! Status sudah bukan POSTED.", $voucher->DocumentNo, $periode);
			return false;
		}

		// OK Hapus Voucher nya (Harusnya return 1 sih... tapi klo gagal hapus selama tidak error gpp)
		$voucher->UpdatedById = \AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($voucher->Delete($voucher->Id) == -1) {
			$errorMessages[] = sprintf("Voucher jurnal memorial '%s' periode %s tidak diproses ! Gagal menghapus VOUCHER. Error: %s", $voucher->DocumentNo, $periode, $this->connector->GetErrorMessage());
			return false;
		}

		// OK Rubah status jurnal memorial
		$journal->UpdatedById = $voucher->UpdatedById;
		if ($journal->UnPost($journal->Id) != 1) {
			$errorMessages[] = sprintf("Voucher Jurnal memorial '%s' periode %s tidak diproses ! Gagal ganti STATUS JURNAL MEMORIAL. Error: %s", $voucher->DocumentNo, $periode, $this->connector->GetErrorMessage());
			return false;
		}

		$infoMessages[] = sprintf("Voucher Jurnal memorial '%s' periode %s sudah dibatalkan.", $voucher->DocumentNo, $periode);
		return true;
	}

	public function unpost() {
		$ids = $this->GetGetValue("id", array());
		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda belum memilih jurnal memorial yang akan dibatalkan");
			redirect_url("asset.voucher");
		}

		$infoMessages = array();
		$errorMessages = array();

		// OK Begin Transaction
		require_once(MODEL . "accounting/voucher.php");

		$this->connector->BeginTransaction();
		foreach ($ids as $id) {
			$this->doUnPosting($id, $infoMessages, $errorMessages);
		}

		// Cek apakah ada error atau tidak
		if (count($errorMessages) > 0) {
			$this->connector->RollbackTransaction();
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errorMessages) . "</li></ul>");

			$infoMessages[] = "Ditemukan error ketika proses posting. Proses dibatalkan. Mohon perbaiki kesalahan terlebih dahulu.";
		} else {
			$this->connector->CommitTransaction();
		}
		$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infoMessages) . "</li></ul>");


		if (count($ids) == 1) {
			redirect_url("asset.voucher/view/" . $ids[0]);
		} else {
			redirect_url("asset.voucher");
		}
	}
}

// EoF: voucher_controller.php