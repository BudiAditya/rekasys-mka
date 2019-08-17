<?php

class NpkpFunding extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $NpkpId;
	public $FundingDate;
	public $Amount;
	public $Note;
	public $VoucherId;
	public $CashSourceAccId;

	public $CashSourceAccNo;
    public $CashSourceAccName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->NpkpId = $row["npkp_id"];
		$this->FundingDate = strtotime($row["funding_date"]);
		$this->Amount = $row["amount"];
		$this->Note = $row["note"];
		$this->VoucherId = $row["voucher_id"];
        $this->CashSourceAccId = $row["cash_source_acc_id"];
        $this->CashSourceAccNo = $row["acc_no"];
        $this->CashSourceAccName = $row["acc_name"];
	}

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FormatFundingDate($format = HUMAN_DATE) {
		return is_int($this->FundingDate) ? date($format, $this->FundingDate) : null;
	}

	/**
	 * @param int $id
	 * @return NpkpFunding
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.acc_no,b.acc_name FROM ac_npkp_funding AS a Left Join cm_acc_detail AS b On a.cash_source_acc_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * @param int $npkpId
	 * @return NpkpFunding[]
	 */
	public function LoadByNpkpId($npkpId) {
		$this->connector->CommandText = "SELECT a.*,b.acc_no,b.acc_name FROM ac_npkp_funding AS a Left Join cm_acc_detail AS b On a.cash_source_acc_id = b.id WHERE a.is_deleted = 0 AND a.npkp_id = ?id";
		$this->connector->AddParameter("?id", $npkpId);

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new NpkpFunding();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_npkp_funding(createby_id, create_time, npkp_id, funding_date, amount, note, voucher_id,cash_source_acc_id)
VALUES(?user, NOW(), ?npkp, ?date, ?amount, ?note, ?voucher, ?source)";
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?npkp", $this->NpkpId);
		$this->connector->AddParameter("?date", $this->FormatFundingDate(SQL_DATEONLY));
		$this->connector->AddParameter("?amount", $this->Amount);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?voucher", $this->VoucherId);
        $this->connector->AddParameter("?source", $this->CashSourceAccId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_npkp_funding SET
	updateby_id = ?user,
	update_time = NOW(),
	npkp_id = ?npkp,
	funding_date = ?date,
	amount = ?amount,
	note = ?note,
	cash_source_acc_id = ?source
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?npkp", $this->NpkpId);
		$this->connector->AddParameter("?date", $this->FormatFundingDate(SQL_DATEONLY));
		$this->connector->AddParameter("?amount", $this->Amount);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?source", $this->CashSourceAccId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ac_npkp_funding SET
	updateby_id = ?user,
	update_time = NOW(),
	is_deleted = 1
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// EoF: npkp_funding.php