<?php
namespace Ap;

class OpeningBalance extends \EntityBase {
	public $Id;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $CreditorId;
	public $Date;
	public $DebitAmount = 0;
	public $CreditAmount = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->CreditorId = $row["creditor_id"];
		$this->Date = strtotime($row["date"]);
		$this->DebitAmount = $row["debit_amount"];
		$this->CreditAmount = $row["credit_amount"];
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	/**
	 * @param int $id
	 * @return OpeningBalance $this
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ap_opening_balance AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}


	public function LoadByCreditor($creditorId, $date = null) {
		if ($date == null) {
			$date = mktime(0, 0, 0, 1, 1, 2019);
		}
		$this->connector->CommandText = "SELECT a.* FROM ap_opening_balance AS a WHERE a.creditor_id = ?creditorId AND a.date = ?date";
		$this->connector->AddParameter("?creditorId", $creditorId);
		$this->connector->AddParameter("?date", date(SQL_DATEONLY, $date));

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * Menghitung transaksi dari tanggal saldo awal s.d. tanggal yang diminta
	 *
	 * @param null $currentDate
	 * @param int  $status
	 *
	 * @return array
	 * @throws \BadMethodCallException
	 */
	public function CalculateTransaction($currentDate = null, $status = 4) {
		if ($this->CreditorId == null) {
			throw new \BadMethodCallException("Unable to calculate transaction since creditor id is unknown.");
		}
		if (!is_int($currentDate)) {
			$currentDate = time();
		}

		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "common/creditor_type.php");

		$creditor = new \Creditor($this->CreditorId);
		$type = new \CreditorType($creditor->CreditorTypeId);

		$start = $this->Date;
		$end = $currentDate;

		$this->connector->CommandText =
"SELECT SUM(IF(b.acc_debit_id = ?accId, b.amount, 0)) AS debet, SUM(IF(b.acc_credit_id = ?accId, b.amount, 0)) AS kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.is_deleted = 0 AND a.status = ?status AND b.creditor_id = ?supplierId AND a.voucher_date >= ?start AND a.voucher_date < ?end";
		$this->connector->AddParameter("?accId", $type->AccControlId);
		if ($status == -1 || $status === null) {
			$this->connector->AddParameter("?status", "a.status", "int");
		} else {
			$this->connector->AddParameter("?status", $status);
		}
		$this->connector->AddParameter("?supplierId", $this->CreditorId);
		$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
		$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));

		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			return $rs->FetchAssoc();
		} else {
			return array("debet" => 0, "kredit");
		}
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ap_opening_balance(createby_id, create_time, creditor_id, date, debit_amount, credit_amount)
VALUES(?user, NOW(), ?creditorId, ?date, ?debit, ?credit)";
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?creditorId", $this->CreditorId);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?debit", $this->DebitAmount);
		$this->connector->AddParameter("?credit", $this->CreditAmount);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ap_opening_balance AS a SET
	 updateby_id = ?user,
	 update_time = NOW(),
	 debit_amount = ?debit,
	 credit_amount = ?credit
WHERE a.id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?debit", $this->DebitAmount);
		$this->connector->AddParameter("?credit", $this->CreditAmount);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ap_opening_balance WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// End of File: opening_balance.php 