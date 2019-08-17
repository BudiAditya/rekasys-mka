<?php
/**
 * Class FundingController
 *
 * IMPORTANT: Class ini tidak akan mengfilter data berdasarkan Company karena ini hanya berlaku pada corporate
 */
class FundingController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "accounting/cash_request.php");
		require_once(MODEL . "accounting/npkp_funding.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");

		// Paksa agar hanya MSN dan Corporate
		//$allowedSbuCode = array(3, 7, null);
		//if (!in_array($this->userCompanyId, $allowedSbuCode)) {
		//	$this->persistence->SaveState("error", "Maaf pencairan dana hanya bisa dilakukan oleh MSN / CORP");
		//	redirect_url("home");
		//}
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "d.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "b.doc_no", "display" => "NPKP No.", "width" => 120);
		$settings["columns"][] = array("name" => "DATE_FORMAT(b.cash_request_date, '%d-%m-%Y')", "display" => "NPKP Date", "width" => 90, "overrideSort" => "b.cash_request_date");
		$settings["columns"][] = array("name" => "c.doc_no", "display" => "Accounting Voucher", "width" => 120);
		$settings["columns"][] = array("name" => "FORMAT(a.amount, 2)", "display" => "Apr Amount", "width" => 80, "align" => "right", "overrideSort" => "a.amount");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.funding_date, '%d-%m-%Y')", "display" => "Funding Date", "width" => 90, "overrideSort" => "a.funding_date");

		$settings["filters"][] = array("name" => "b.doc_no", "display" => "No. NPKP");
		$settings["filters"][] = array("name" => "c.doc_no", "display" => "No. Voucher");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "NPKP Funding List";

			if($acl->CheckUserAccess("accounting.funding", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "accounting.funding/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Mohon memilih pencairan NPKP terlebih dahulu.\nPERHATIAN: Mohon memilih tepat 1 data.",
					"Confirm" => "");
			}
			if($acl->CheckUserAccess("accounting.funding", "delete")) {
				$settings["actions"][] = array("Text" => "delete", "Url" => "accounting.funding/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih pencairan NPKP terlebih dahulu.\nPERHATIAN: Mohon memilih tepat 1 data.",
					"Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?\nPERHATIAN: Voucher yang bersangkutan juga akan dihapus.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 6;
			$settings["def_direction"] = "desc";
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ac_npkp_funding AS a
	JOIN ac_cash_request_master AS b ON a.npkp_id = b.id
	JOIN ac_voucher_master AS c ON a.voucher_id = c.id
	JOIN cm_company AS d ON b.entity_id = d.entity_id";
			$settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function _list() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "NPKP No.", "width" => 120);
		$settings["columns"][] = array("name" => "d.name", "display" => "Category", "width" => 100);
		$settings["columns"][] = array("name" => "a.objective", "display" => "Purpose", "width" => 250);
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.cash_request_date, '%d-%m-%Y')", "display" => "NPKP Date", "width" => 80, "overrideSort" => "a.cash_request_date");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.eta_date, '%d-%m-%Y')", "display" => "Request Date", "width" => 90, "overrideSort" => "a.eta_date");
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "FORMAT(e.amount, 2)", "display" => "Req Amount", "width" => 80, "align" => "right", "overrideSort" => "e.amount");
		$settings["columns"][] = array("name" => "FORMAT(COALESCE(f.funded, 0), 2)", "display" => "Apr Amount", "width" => 80, "align" => "right", "overrideSort" => "f.funded");
		$settings["columns"][] = array("name" => "FORMAT(e.amount - COALESCE(f.funded, 0), 2)", "display" => "Outstanding", "width" => 80, "align" => "right", "overrideSort" => "f.funded");

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No Dokumen");
		$settings["filters"][] = array("name" => "a.objective", "display" => "Tujuan NPKP");
		$settings["filters"][] = array("name" => "c.short_desc", "display" => "Status");
		$settings["filters"][] = array("name" => "date_format(a.eta_date, '%d-%m-%Y')", "display" => "Prakiraan Terima");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "NPKP Ready for Funding Process";

			if($acl->CheckUserAccess("accounting.funding", "add")) {
				$settings["actions"][] = array("Text" => "Proses Pencairan", "Url" => "accounting.funding/add/%s", "Class" => "bt_money", "ReqId" => 1,
					"Error" => "Mohon memilih dokumen NPKP terlebih dahulu sebelum proses pencairan dana NPKP.\nPERHATIAN: Mohon memilih tepat 1 data.",
					"Confirm" => "");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] =
"ac_cash_request_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'npkp_status'
	JOIN ac_cash_request_category AS d ON a.category_id = d.id
	JOIN (
		SELECT aa.cash_request_master_id, SUM(aa.amount) AS amount
		FROM ac_cash_request_detail AS aa
		GROUP BY aa.cash_request_master_id
	) AS e ON a.id = e.cash_request_master_id
	LEFT JOIN (
		SELECT aa.npkp_id, SUM(aa.amount) AS funded
		FROM ac_npkp_funding AS aa
		WHERE aa.is_deleted = 0
		GROUP BY aa.npkp_id
	) AS f ON a.id = f.npkp_id";

			// Khusus ini hanya yang status type 4 dan bisa akses semua NPKP (karena ini pencairan dari corporate ke masing-masing Company)
			$settings["where"] = "a.is_deleted = 0 AND a.status = 4 AND e.amount - COALESCE(f.funded, 0) > 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(NpkpFunding $funding) {
		if ($funding->NpkpId == null) {
			$this->Set("error", "Dokumen NPKP masih kosong.");
			return false;
		}
		if (!is_int($funding->FundingDate)) {
			$this->Set("error", "Tanggal pencairan masih kosong");
			return false;
		}
		if ($funding->Amount <= 0) {
			$this->Set("error", "Jumlah dana tidak bisa <= 0");
			return false;
		}

		return true;
	}

	public function add($npkpId = null) {
		if ($npkpId == null) {
			$this->persistence->SaveState("error", "Mohon memilih NPKP yang akan dicairkan terlebih dahulu.");
			redirect_url("accounting.funding/list");
		}
		require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        require_once(MODEL . "accounting/cash_request_category.php");
		require_once(LIBRARY . "dot_net_tools.php");

		$npkp = new CashRequest($npkpId);
		if ($npkpId != $npkp->Id) {
			$this->persistence->SaveState("error", "NPKP yang diminta tidak dapat ditemukan.");
			redirect_url("accounting.funding/list");
		}

		$npkp->LoadDetails();
		$npkp->LoadFunds();
		$funding = new NpkpFunding();
		if (count($this->postData) > 0) {
			$funding->NpkpId = $npkp->Id;
			$funding->FundingDate = strtotime($this->GetPostValue("date"));
			$funding->Amount = str_replace(",", "", $this->GetPostValue("amount"));
			$funding->CashSourceAccId = $this->GetPostValue("cash_source");
			$funding->Note = $this->GetPostValue("note");
			if ($this->ValidateData($funding)) {
				$funding->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				$this->connector->BeginTransaction();
				if ($this->doAdd($funding, $npkp)) {
					$this->connector->CommitTransaction();
					redirect_url("accounting.funding");
				} else {
					$this->connector->RollbackTransaction();
				}
			}
		} else {
			$funding->FundingDate = mktime(0, 0, 0);
		}
		//get npkp account control
        $loader = new CashRequestCategory();
		$loader = $loader->LoadById($npkp->CategoryId);
        $accdest = $loader->AccountControlId;
        $coa = new Coa();
		$accounts = $coa->LoadLevel3ByLevel1($this->userCompanyId,6);
		$this->Set("company", new Company($npkp->EntityId));
		$this->Set("npkp", $npkp);
		$this->Set("funding", $funding);
        $this->Set("accounts", $accounts);
        $this->Set("accdest", $accdest);
	}

	private function doAdd(NpkpFunding $funding, CashRequest $npkp) {
		require_once(MODEL . "accounting/opening_balance.php");
		require_once(MODEL . "accounting/voucher.php");
		require_once(MODEL . "accounting/cash_request_category.php");
		require_once(MODEL . "common/doc_counter.php");

        // #01.1: Cek kesiapan dana pada kas utama
		$obal = new OpeningBalance();
		$obal->LoadByAccount($funding->CashSourceAccId, date("Y"));	// Cari kesiapan dana pada hari ini
		if ($obal == null || $obal->Id == null) {
			$this->Set("error", $funding->CashSourceAccId." - Opening balance untuk kas besar tidak dapat ditemukan ! Hubungi pihak accounting untuk proses tutup buku tahun lalu.");
			return false;
		}
		$transData = $obal->CalculateTransaction();

		if ($transData["saldo"] < $funding->Amount) {
			$this->Set("error", "Dana pada kas besar tidak mencukupi. Mohon periksa cash flow.");
			return false;
		}

		// #01.2: Ga boleh keluarin lebih dari angka NPKP
		$npkp->LoadFunds();
		$npkp->LoadDetails();
		$totalFunded = DotNetTools::ArraySum($npkp->Funds, function(NpkpFunding $lhs) {
			return $lhs->Amount;
		});
		$totalNpkp = DotNetTools::ArraySum($npkp->Details, function(CashRequestDetail $lhs) {
			return $lhs->Amount;
		});

		if ($totalFunded + $funding->Amount > $totalNpkp) {
			$this->Set("error", sprintf("Maaf pencairan dana NPKP sejumlah %s akan melebihi permintaan NPKP yang sebesar %s.<br />NPKP tersebut sudah dicairkan sebesar %s", number_format($funding->Amount), number_format($totalNpkp), number_format($totalFunded)));
			return false;
		}

		// #02: Buat Vouchernya
		$docCounter = new DocCounter();

		$voucher = new Voucher();
		$voucher->DocumentTypeId = 2;	// BKK
		$voucher->DocumentNo = $docCounter->AutoDocNoBk($this->userCompanyId, $funding->FundingDate, 1);
		$voucher->Date = $funding->FundingDate;		// Sama dengan tanggal funded
		$voucher->EntityId = $this->userCompanyId;
		$voucher->Note = "Proses perpindahan dana NPKP: " . $npkp->DocumentNo . "\nKeterangan NPKP: " . $npkp->Note;
		$voucher->StatusCode = 4;		// POSTED
		$voucher->CreatedById = $funding->CreatedById;
		$voucher->VoucherSource = "NPKP";

		if ($voucher->DocumentNo == null) {
			$this->Set("error", "Maaf tidak dapat membuat voucher BKK karena tanggal dokumen sudah ter-lock. Tidak dapat menggunakan tanggal: " . $funding->FormatFundingDate());
			return false;
		}

		// #03: Buat Detail Voucher
		$category = new CashRequestCategory();
		$category = $category->LoadById($npkp->CategoryId);

		$voucherDetail = new VoucherDetail();
		$voucherDetail->AccDebitId = $category->AccountControlId;
		$voucherDetail->AccCreditId = $funding->CashSourceAccId;		// ini nanti diset pada cm_company
		$voucherDetail->Amount = $funding->Amount;
		$voucherDetail->Note = "Perpindahan dana NPKP: " . $npkp->DocumentNo;
		$voucherDetail->ProjectId = $category->ProjectId;

		// #04: Simpan Voucher dan detailnya
		$rs = $voucher->Insert();
		if ($rs != 1) {
			$this->Set("error", "Gagal simpan data voucher BKK. Error: " . $this->connector->GetErrorMessage());
			return false;
		}

		$voucherDetail->VoucherId = $voucher->Id;
		$rs = $voucherDetail->Insert();
		if ($rs != 1) {
			$this->Set("error", "Gagal simpan detail voucher BKK. Error: " . $this->connector->GetErrorMessage());
			return false;
		}

		// #05: Insert data master ketika semua voucher sudah masuk
		$funding->VoucherId = $voucher->Id;
		$rs = $funding->Insert();
		if ($rs != 1) {
			$this->Set("error", "Gagal entry data pencairan. Error: " . $this->connector->GetErrorMessage());
			return false;
		}

		$this->persistence->SaveState("info", sprintf("Pencairan dana NPKP: %s sejumlah Rp. %s telah disimpan<br />Voucher BKK: %s", $npkp->DocumentNo, number_format($funding->Amount, 2), $voucher->DocumentNo));
		return true;
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih data pencairan dana terlebih dahulu.");
			redirect_url("accounting.funding/list");
		}
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "accounting/voucher.php");
		require_once(LIBRARY . "dot_net_tools.php");

		$funding = new NpkpFunding($id);
		if ($funding->Id != $id || $funding->IsDeleted) {
			$this->persistence->SaveState("error", "Data pencairan dana yang diminta tidak dapat ditemukan.");
			redirect_url("accounting.funding/list");
		}

		$npkp = new CashRequest($funding->NpkpId);
		$npkp->LoadDetails();

		$this->Set("company", new Company($npkp->EntityId));
		$this->Set("npkp", $npkp);
		$this->Set("funding", $funding);
		$this->Set("voucher", new Voucher($funding->VoucherId));
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih data pencairan dana terlebih dahulu.");
			redirect_url("accounting.funding");
		}

		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		$funding = new NpkpFunding($id);
		if ($funding->Id != $id || $funding->IsDeleted) {
			$this->persistence->SaveState("error", "Data pencairan dana yang diminta tidak dapat ditemukan.");
			redirect_url("accounting.funding");
		}

		require_once(MODEL . "accounting/voucher.php");

		$flagDelete = true;
		$npkp = new CashRequest($funding->NpkpId);
		$voucher = new Voucher($funding->VoucherId);

		$this->connector->BeginTransaction();
		// #01: Hapus data voucher nya terlebih dahulu
		if ($voucher->Id == $funding->VoucherId) {
			// OK bisa kita hapus...
			$rs = $voucher->Delete($voucher->Id);
			if ($rs != 1) {
				$this->persistence->SaveState("error", sprintf("[Step 1] Gagal menghapus pencairan dana NPKP %s. Error: %s", $npkp->DocumentNo, $this->connector->GetErrorMessage()));
				$this->connector->RollbackTransaction();
				redirect_url("accounting.funding");
			}
		} else {
			// Aneh... kok bisa-bisanya tidak ada voucher
			$flagDelete = false;
		}

		// #02: Hapus data pencairan
		$funding->UpdatedById = $userId;
		$rs = $funding->Delete($funding->Id);
		if ($rs == 1) {
			$this->connector->CommitTransaction();
			// OK sukses hapus
			if ($flagDelete) {
				$this->persistence->SaveState("info", sprintf("Data pencairan NPKP %s sejumlah %s telah dihapus. Voucher yang bersangkutan juga dihapus", $npkp->DocumentNo, number_format($funding->Amount)));
			} else {
				$this->persistence->SaveState("info", sprintf("Data pencairan NPKP %s sejumlah %s telah dihapus.<br />INFO: Voucher yang bersangkutan tidak dapat ditemukan sehingga tidak dapat dihapus.", $npkp->DocumentNo, number_format($funding->Amount)));
			}
		} else {
			$this->persistence->SaveState("error", sprintf("[Step 2] Gagal menghapus pencairan dana NPKP %s. Error: %s", $npkp->DocumentNo, $this->connector->GetErrorMessage()));
			$this->connector->RollbackTransaction();
		}

		redirect_url("accounting.funding");
	}
}

// EoF: funding_controller.php