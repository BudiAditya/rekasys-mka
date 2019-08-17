<?php

class Bank extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $Name;
	public $Branch;
	public $Address;
	public $NoRekening;
	public $CurrencyCode = "IDR";
	public $AccId;
	public $CostAccId;
	public $RevAccId;
	public $UpdatedById;
	public $UpdatedDate;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->Name = $row["bank_name"];
		$this->Branch = $row["branch"];
		$this->Address = $row["address"];
		$this->NoRekening = $row["rek_no"];
		$this->CurrencyCode = $row["currency_cd"];
		$this->AccId = $row["acc_id"];
		$this->CostAccId = $row["cost_acc_id"];
		$this->RevAccId = $row["rev_acc_id"];
		$this->UpdatedById = $row["update_by"];
		$this->UpdatedDate = $row["update_date"];
	}

	/**
	 * @param string $orderBy
	 * @return Bank[]
	 */
	public function LoadAll($orderBy = "a.bank_name") {
		$this->connector->CommandText =
"SELECT a.*
FROM cm_bank_account AS a
WHERE a.is_deleted = 0
ORDER BY $orderBy";

		$rs = $this->connector->ExecuteQuery();
		$result = array();

		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Bank();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $sbu
	 * @param string $orderBy
	 * @return Bank[]
	 */
	public function LoadByEntityId($sbu, $orderBy = "a.bank_name") {
		$this->connector->CommandText =
"SELECT a.*
FROM cm_bank_account AS a
WHERE a.is_deleted = 0 AND a.entity_id = ?sbu
ORDER BY $orderBy";
		$this->connector->AddParameter("?sbu", $sbu);

		$rs = $this->connector->ExecuteQuery();
		$result = array();

		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Bank();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $id
	 * @return Bank
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_bank_account AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * Mencari data bank berdasarkan akun CoA nya
	 *
	 * @param int $sbu
	 * @param int $accId
	 * @return Bank
	 */
	public function LoadByAccId($sbu, $accId) {
		$this->connector->CommandText = "SELECT a.* FROM cm_bank_account AS a WHERE a.entity_id = ?sbu AND a.acc_id = ?id";
		$this->connector->AddParameter("?sbu", $sbu);
		$this->connector->AddParameter("?id", $accId);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO cm_bank_account(entity_id, bank_name, branch, address, rek_no, currency_cd, acc_id, cost_acc_id, rev_acc_id, createby_id, create_time)
VALUES(?sbu, ?name, ?branch, ?address, ?noRek, ?currency, ?accId, ?costAccId, ?revAccId, ?user, NOW())";

		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?name", $this->Name);
		$this->connector->AddParameter("?branch", $this->Branch);
		$this->connector->AddParameter("?address", $this->Address);
		$this->connector->AddParameter("?noRek", $this->NoRekening, "varchar");
		$this->connector->AddParameter("?currency", $this->CurrencyCode, "varchar");
		$this->connector->AddParameter("?accId", $this->AccId);
		$this->connector->AddParameter("?costAccId", $this->CostAccId);
		$this->connector->AddParameter("?revAccId", $this->RevAccId);
		$this->connector->AddParameter("?user", $this->UpdatedById);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE cm_bank_account SET
	bank_name = ?name
	, branch = ?branch
	, address = ?address
	, rek_no = ?noRek
	, currency_cd = ?currency
	, acc_id = ?accId
	, cost_acc_id = ?costAccId
	, rev_acc_id = ?revAccId
	, update_by = ?user
	, update_date = NOW()
WHERE id = ?id";

		$this->connector->AddParameter("?name", $this->Name);
		$this->connector->AddParameter("?branch", $this->Branch);
		$this->connector->AddParameter("?address", $this->Address);
		$this->connector->AddParameter("?noRek", $this->NoRekening, "varchar");
		$this->connector->AddParameter("?currency", $this->CurrencyCode, "varchar");
		$this->connector->AddParameter("?accId", $this->AccId);
		$this->connector->AddParameter("?costAccId", $this->CostAccId);
		$this->connector->AddParameter("?revAccId", $this->RevAccId);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE cm_bank_account SET
	is_deleted = 1
	, update_by = ?user
	, update_date = NOW()
WHERE id = ?id";

		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// End of file: bank.php
