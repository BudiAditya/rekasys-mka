<?php

namespace Hr;


class OpeningBalance extends \EntityBase {
	public $Id;
	public $EntityId;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $EmployeeId;
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
        $this->EntityId = $row["entity_id"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->EmployeeId = $row["employee_id"];
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
		$this->connector->CommandText = "SELECT a.* FROM hr_opening_balance AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}


	public function LoadByEmployee($empId, $date = null) {
		if ($date == null) {
			$date = mktime(0, 0, 0, 1, 1, 2013);
		}
		$this->connector->CommandText = "SELECT a.* FROM hr_opening_balance AS a WHERE a.employee_id = ?empId AND a.date = ?date";
		$this->connector->AddParameter("?empId", $empId);
		$this->connector->AddParameter("?date", date(SQL_DATEONLY, $date));

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO hr_opening_balance(entity_id,createby_id, create_time, employee_id, date, debit_amount, credit_amount)
VALUES(?eti,?user, NOW(), ?empId, ?date, ?debit, ?credit)";
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?empId", $this->EmployeeId);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?debit", $this->DebitAmount);
		$this->connector->AddParameter("?credit", $this->CreditAmount);
        $this->connector->AddParameter("?eti", $this->EntityId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE hr_opening_balance AS a SET
 	updateby_id = ?user,
 	update_time = NOW(),
 	debit_amount = ?debit,
 	credit_amount = ?credit,
 	entity_id = ?eti
WHERE a.id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?debit", $this->DebitAmount);
		$this->connector->AddParameter("?credit", $this->CreditAmount);
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?eti", $this->EntityId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM hr_opening_balance WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// EoF: opening_balance.php