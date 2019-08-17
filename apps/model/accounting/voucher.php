<?php

require_once("voucher_detail.php");
/**
 * Class yang berfungsi membuat dokumen/voucher accounting.
 * Semua dokumen accounting akan dibuat oleh model ini
 */
class Voucher extends EntityBase {
	private $editableDocId = array(1, 2, 3, 13);

	public static $StatusCodes = array(
		1 => "DRAFT",
		2 => "APPROVED",
		3 => "VERIFIED",
		4 => "POSTED"
	);

	public $Id;
	public $IsDeleted = false;
	public $DocumentTypeId;
	public $DocumentNo;
	public $Date;
	public $EntityId;
	public $Note;
	public $StatusCode;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $ApprovedById;
	public $ApprovedDate;
	public $VoucherSource;		// Isinya hanya pembeda sumber entry datanya... Kita ada beberapa sumber entry untuk entry Voucher
	public $VerifiedById;
	public $VerifiedDate;
	public $PostedById;
	public $PostedDate;
	public $RStatus = 1;

	/** @var VoucherDetail[] */
	public $Details = array();
	/** @var VoucherType */
	public $VoucherType;
	/** @var Company */
	public $Company;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->DocumentTypeId = $row["doc_type_id"];
		$this->DocumentNo = $row["doc_no"];
		$this->Date = strtotime($row["voucher_date"]);
		$this->EntityId = $row["entity_id"];
		$this->Note = $row["note"];
		$this->StatusCode = $row["status"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->ApprovedById = $row["approveby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->VoucherSource = $row["voucher_source"];
		$this->VerifiedById = $row["verifiedby_id"];
		$this->VerifiedDate = strtotime($row["verified_time"]);
		$this->PostedById = $row["posted_by"];
		$this->PostedDate = strtotime($row["posted_date"]);
        $this->RStatus = $row["rstatus"];
	}

	public function GetStatus() {
		if ($this->StatusCode == null) {
			return null;
		} else if (array_key_exists($this->StatusCode, Voucher::$StatusCodes)) {
			return Voucher::$StatusCodes[$this->StatusCode];
		} else {
			return "N.A.";
		}
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	/**
	 * @return VoucherDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}

		$detail = new VoucherDetail();
		$this->Details = $detail->LoadByVoucherId($this->Id);
		return $this->Details;
	}

	/**
	 * @return bool
	 */
	public function IsVoucherEditable() {
		if ($this->DocumentTypeId == null) {
			return true;
		} else {
			// Dikarenakan ada voucher hasil porting dari database lama maka untuk semua dokumen yang dibawah tanggal 1 Juni Boleh diedit
			if ($this->Date < 1370016000) {
				// Dokumen hasil porting
				return true;
			} else {
				// Yang boleh diedit manual hanya Bank Masuk, Bank Keluar, Adjustment Journal
				return in_array($this->DocumentTypeId, $this->editableDocId);
			}
		}
	}

	/**
	 * @return Company
	 */
	public function LoadCompany() {
        require_once(MODEL . "master/company.php");
		if ($this->Id == null || $this->EntityId == null) {
			$this->Company = null;
			return null;
		}

		$this->Company = new Company($this->EntityId);
		return $this->Company;
	}

	/**
	 * @return VoucherType
	 */
	public function LoadVoucherType() {
        require_once("voucher_type.php");
		if ($this->Id == null || $this->DocumentTypeId == null) {
			$this->VoucherType = null;
			return null;
		}

		$this->VoucherType = new VoucherType();
		$this->VoucherType->LoadByDocumentType($this->DocumentTypeId);

		return $this->VoucherType;
	}

	/**
	 * @param int $id
	 * @return Voucher
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*
FROM ac_voucher_master AS a
WHERE a.id = ?id";

		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByDocNo($docNo) {
		$this->connector->CommandText =
"SELECT a.*
FROM ac_voucher_master AS a
WHERE a.doc_no = ?docNo";

		$this->connector->AddParameter("?docNo", $docNo);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function SeekNextVoucher() {
		if ($this->Id == null || $this->DocumentNo == null) {
			throw new BadMethodCallException("SeekNextVoucher should be only called after ID and document no loaded !");
		}

		$tokens = explode("/", $this->DocumentNo);
		$counter = $tokens[3];

		unset($tokens[3]);
		$pattern = implode("/", $tokens);

		$this->connector->ClearParameter();
		$this->connector->CommandText =
"SELECT a.*
FROM ac_voucher_master AS a
WHERE LEFT(a.doc_no, 10) = ?pattern AND CAST(RIGHT(a.doc_no, 6) AS SIGNED) > ?counter
ORDER BY CAST(RIGHT(a.doc_no, 6) AS SIGNED) ASC
LIMIT 1";
		$this->connector->AddParameter("?pattern", $pattern);
		$this->connector->AddParameter("?counter", $counter);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		} else {
			$voucher = new Voucher();
			$voucher->FillProperties($rs->FetchAssoc());

			return $voucher;
		}
	}

	public function SeekPreviousVoucher() {
		if ($this->Id == null || $this->DocumentNo == null) {
			throw new BadMethodCallException("SeekNextVoucher should be only called after ID and document no loaded !");
		}

		$tokens = explode("/", $this->DocumentNo);
		$counter = (int)$tokens[3];
		if ($counter <= 0) {
			return null;
		}

		unset($tokens[3]);
		$pattern = implode("/", $tokens);

		$this->connector->ClearParameter();
		$this->connector->CommandText =
"SELECT a.*
FROM ac_voucher_master AS a
WHERE LEFT(a.doc_no, 10) = ?pattern AND CAST(RIGHT(a.doc_no, 6) AS SIGNED) < ?counter
ORDER BY CAST(RIGHT(a.doc_no, 6) AS SIGNED) DESC
LIMIT 1";
		$this->connector->AddParameter("?pattern", $pattern);
		$this->connector->AddParameter("?counter", $counter);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		} else {
			$voucher = new Voucher();
			$voucher->FillProperties($rs->FetchAssoc());

			return $voucher;
		}
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_voucher_master(doc_type_id, doc_no, voucher_date, entity_id, note, status, createby_id, create_time, voucher_source, rstatus)
VALUES(?docType, ?docNo, ?date, ?sbu, ?note, ?status, ?user, NOW(), ?source, ?rstatus)";
		$this->connector->AddParameter("?docType", $this->DocumentTypeId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?source", $this->VoucherSource);
        $this->connector->AddParameter("?rstatus", $this->RStatus);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_voucher_master SET
	doc_type_id = ?docType
	, doc_no = ?docNo
	, voucher_date = ?date
	, entity_id = ?sbu
	, note = ?note
	, updateby_id = ?user
	, update_time = NOW()
	, rstatus = ?rstatus
WHERE id = ?id";
		$this->connector->AddParameter("?docType", $this->DocumentTypeId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?rstatus", $this->RStatus);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "Delete From ac_voucher_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        $this->connector->CommandText = "UPDATE ac_voucher_master SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        $this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

	public function Approve($id) {
		$this->connector->CommandText = "UPDATE ac_voucher_master SET status = 2, updateby_id = ?user, update_time = NOW(), approveby_id = ?user , approve_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->ApprovedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id) {
		$this->connector->CommandText = "UPDATE ac_voucher_master SET status = 1, updateby_id = ?user, update_time = NOW(), approveby_id = NULL, approve_time = NULL WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Verify($id) {
		$this->connector->CommandText = "UPDATE ac_voucher_master SET status = 3, updateby_id = ?user, update_time = NOW(), verifiedby_id = ?user, verified_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->VerifiedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function UnVerify($id) {
		$this->connector->CommandText = "UPDATE ac_voucher_master SET status = 2, updateby_id = ?user, update_time = NOW(), verifiedby_id = NULL, verified_time = NULL WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Post($id) {
		$this->connector->CommandText = "UPDATE ac_voucher_master SET status = 4, updateby_id = ?user, update_time = NOW(), posted_by = ?user, posted_date = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->PostedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function UnPost($id) {
		$this->connector->CommandText = "UPDATE ac_voucher_master SET status = 3, updateby_id = ?user, update_time = NOW(), posted_by = NULL, posted_date = NULL WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function DeleteByDocNo($documentNo){
        $this->connector->CommandText = "DELETE FROM ac_voucher_master WHERE doc_no = ?doc";
        $this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?doc", $documentNo);
        return $this->connector->ExecuteNonQuery();
    }
}


// End of File: voucher.php
