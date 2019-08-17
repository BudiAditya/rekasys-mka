<?php
require_once("cash_request_detail.php");

class CashRequest extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $EntityId;
	public $DocumentNo;
	public $CategoryId;
	public $Date;
	public $StatusCode;
	public $Objective;
	public $Note;
	public $UpdatedById;
	public $UpdateDate;
	public $ApprovedById;
	public $ApprovedDate;
	public $EtaDate;
	public $VerifiedById;
	public $VerifiedDate;
	public $ApprovedLv2ById;
	public $ApprovedLv2Date;
	public $FundedById;
	public $FundedDate;

	// Helper
	/** @var CashRequestDetail[] */
	public $Details = array();
	/** @var NpkpFunding[] */
	public $Funds = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->EntityId = $row["entity_id"];
		$this->DocumentNo = $row["doc_no"];
		$this->CategoryId = $row["category_id"];
		$this->Date = strtotime($row["cash_request_date"]);
		$this->StatusCode = $row["status"];
		$this->Objective = $row["objective"];
		$this->Note = $row["note"];
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdateDate = strtotime($row["update_time"]);
		$this->ApprovedById = $row["approveby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->EtaDate = strtotime($row["eta_date"]);
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	public function FormatEtaDate($format = HUMAN_DATE) {
		return is_int($this->EtaDate) ? date($format, $this->EtaDate) : null;
	}

	public function GetStatus() {
		if ($this->StatusCode == null) {
			return null;
		}

		switch ($this->StatusCode) {
			case 0:
				return "INCOMPLETE";
			case 1:
				return "DRAFT";
			case 2:
				return "VERIFIED";
			case 3:
				return "APPROVED";
			case 4:
				return "APPROVED LV 2";
			case 5:
				return "PENDING";
			case 6:
				return "DANA CAIR";
			default:
				return "N.A.";
		}
	}

	/**
	 * @return CashRequestDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return null;
		}

		$detail = new CashRequestDetail();
		$this->Details = $detail->LoadByCashRequestId($this->Id);
		return $this->Details;
	}

	/**
	 * @return NpkpFunding[]
	 */
	public function LoadFunds() {
		if ($this->Id == null) {
			return null;
		}

		require_once(MODEL . "accounting/npkp_funding.php");
		$fund = new NpkpFunding();
		$this->Funds = $fund->LoadByNpkpId($this->Id);

		return $this->Funds;
	}

	/**
	 * @param int $id
	 * @return CashRequest
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*
FROM ac_cash_request_master AS a
WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_cash_request_master(createby_id, create_time, entity_id, doc_no, cash_request_date, status, objective, note, eta_date, category_id)
VALUES(?user, NOW(), ?sbu, ?docNo, ?date, ?status, ?objective, ?note, ?eta, ?category)";
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?objective", $this->Objective);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?eta", $this->FormatEtaDate(SQL_DATETIME));
		$this->connector->AddParameter("?category", $this->CategoryId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		if (!in_array($this->StatusCode, array (0, 1))) {
			throw new Exception("Update CashRequest only allow status code 0 or 1. Otherwise use respective method.");
		}

		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	updateby_id = ?user
	, update_time = NOW()
	, status = ?status
	, doc_no = ?docNo
	, cash_request_date = ?date
	, objective = ?objective
	, note = ?note
	, eta_date = ?eta
	, category_id = ?category
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?objective", $this->Objective);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?eta", $this->FormatEtaDate(SQL_DATETIME));
		$this->connector->AddParameter("?category", $this->CategoryId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = "UPDATE ac_cash_request_master SET updateby_id = ?user, update_time = NOW(), is_deleted = 1 WHERE id = ?id AND (status = 1 OR status = 5)";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = "Delete From ac_cash_request_master WHERE id = ?id AND (status = 1 OR status = 5)";
        $this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

	public function Approve($id) {
		$this->connector->CommandText = "UPDATE ac_cash_request_master SET status = 2, updateby_id = ?user, update_time = NOW(), approveby_id = ?user, approve_time = NOW() WHERE id = ?id AND (status = 1 OR status = 5)";
		$this->connector->AddParameter("?user", $this->ApprovedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 1
	, updateby_id = ?user
	, update_time = NOW()
	, approveby_id = NULL
	, approve_time = NULL
WHERE id = ?id AND (status = 2 OR status = 5)";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Verify($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 3
	, updateby_id = ?user
	, update_time = NOW()
	, verifiedby_id = ?user
	, verified_time = NOW()
WHERE id = ?id AND status = 2";
		$this->connector->AddParameter("?user", $this->VerifiedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	/**
	 * Don't complaint with method NAME !!! this the opposite of Verify !!! Take It Or Leave It !
	 *
	 * @param $id
	 * @return int
	 */
	public function Disprove($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 2
	, updateby_id = ?user
	, update_time = NOW()
	, verifiedby_id = NULL
	, verified_time = NULL
WHERE id = ?id AND status = 3";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function ApproveLv2($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 4
	, updateby_id = ?user
	, update_time = NOW()
	, approve2by_id = ?user
	, approve2_time = NOW()
WHERE id = ?id AND status = 3";
		$this->connector->AddParameter("?user", $this->ApprovedLv2ById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function DisApproveLv2($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 3
	, updateby_id = ?user
	, update_time = NOW()
	, approve2by_id = NULL
	, approve2_time = NULL
WHERE id = ?id AND status = 4";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Pending($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 5
	, updateby_id = ?user
	, update_time = NOW()
	, eta_date = ?date
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?date", $this->FormatEtaDate(SQL_DATETIME));
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	/**
	 * Proses pendanaan NPKP ke SBU yang bersangkutan. Proses ini juga otomatis membuat BKK
	 * Yang ini dibikin dimodel agar controllernya tidak bertambah complex... itu controllernya uda COMPLEX !!!
	 *
	 * @see FundingController
	 * @see NpkpFunding
	 *
	 * @param $id
	 * @throws Exception
	 * @return bool
	 */
	public function Funds($id) {
		throw new Exception("This method is not allowed ! Please use FundingController");

		require_once(MODEL . "accounting/opening_balance.php");
		require_once(MODEL . "accounting/voucher.php");
		require_once(MODEL . "accounting/cash_request_category.php");

		// Step #01: Load Details dan cek kesiapan dana yang ada pada Kas Holding (110.01.01.00 / id = 4)
		$this->LoadDetails();
		$amount = 0;
		foreach ($this->Details as $cashDetail) {
			$amount += $cashDetail->Amount;
		}

		$obal = new OpeningBalance();
		$obal->LoadByAccount(4, date("Y"));	// Cari kesiapan dana pada hari ini
		if ($obal == null || $obal->Id == null) {
			// Data opening balance tidak ditemukan. Batalkan saja, akan dibilang dana tidak cukup dan disuruh untuk mengecek laporan cash flow
			return false;
		}
		$transData = $obal->CalculateTransaction();

		if ($transData["saldo"] < $amount) {
			// Dana ticak cukup dan harusnya tidak ada error message
			return false;
		}

		// Step #02: set status dahulu
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 6
	, updateby_id = ?user
	, update_time = NOW()
	, funded_by = ?user
	, funded_date = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->FundedById);
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs != 1) {
			return false;
		}

		// Step #03: Buat Vouchernya
		$voucher = new Voucher();
		$voucher->DocumentTypeId = 2;	// BKK
		$voucher->DocumentNo = $this->DocumentNo;
		$voucher->Date = time();		// Sama dengan tanggal funded
		$voucher->EntityId = $this->EntityId;
		$voucher->Note = "Proses perpindahan dana NPKP: " . $this->DocumentNo . "\nKeterangan NPKP: " . $this->Note;
		$voucher->StatusCode = 4;		// POSTED
		$voucher->CreatedById = $this->FundedById;
		$voucher->VoucherSource = "NPKP";

		// Step #04: Buat Detail Voucher
		$category = new CashRequestCategory();
		$category = $category->LoadById($this->CategoryId);

		$voucherDetail = new VoucherDetail();
		$voucherDetail->AccDebitId = $category->AccountControlId;
		$voucherDetail->AccCreditId = 4;		// Sudah pasti dari kas utama
		$voucherDetail->Amount = $amount;
		$voucherDetail->Note = $voucher->Note;

		// Step #05: Simpan Voucher dan detailnya
		$rs = $voucher->Insert();
		if ($rs != 1) {
			return false;
		}

		$voucherDetail->VoucherId = $voucher->Id;
		$rs = $voucherDetail->Insert();
		if ($rs != 1) {
			return false;
		}

		return true;
	}

	/**
	 * Proses penarikan dana yang sudah dicairkan. Tidak perlu validasi sebanyak pencairan dana.
	 * Disini kita hanya akan menghapus referensi dari voucher saja cukup
	 *
	 * @param $id
	 * @return bool
	 */
	public function UnFunds($id) {
		require_once(MODEL . "accounting/voucher.php");
		// Step 01: Hapus voucher
		$voucher = new Voucher();
		$rs = $voucher->DeleteByDocNo($this->DocumentNo);
		if ($rs == -1) {
			// Gagal hapus voucher
			return false;
		}

		// Step 02: Ganti Reference ke Approve Lv 2
		$this->connector->CommandText =
"UPDATE ac_cash_request_master SET
	status = 4
	, updateby_id = ?user
	, update_time = NOW()
	, funded_by = NULL
	, funded_date = NULL
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteNonQuery();

		return $rs == 1;
	}
}


// End of File: cash_request.php
