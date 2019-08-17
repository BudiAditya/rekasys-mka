<?php

class CashRequestDetail extends EntityBase {
	public $Id;
	public $CashRequestId;
	public $AccountId;
	public $Note;
	public $Amount;
	public $SequenceNo;
	public $PoId;

	// Helper Variable;
	public $MarkedForDeletion = false;
	public $AccountNo;
	public $AccountName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->CashRequestId = $row["cash_request_master_id"];
		$this->AccountId = $row["account_id"];
		$this->Note = $row["note"];
		$this->Amount = $row["amount"];
		$this->SequenceNo = $row["seq_no"];
		$this->PoId = $row["po_id"];

		$this->AccountNo = $row["acc_no"];
		$this->AccountName = $row["acc_name"];
	}

	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.acc_no, b.acc_name
FROM ac_cash_request_detail AS a
	LEFT JOIN cm_acc_detail AS b ON a.account_id = b.id
WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * Cari berdasarkan PO ID. Field PO ID ini akan unique jadi pasti cuma dapat 1 entry
	 *
	 * @param $id
	 * @return CashRequestDetail|null
	 */
	public function LoadByPoId($id) {
		$this->connector->CommandText =
"SELECT a.*, b.acc_no, b.acc_name
FROM ac_cash_request_detail AS a
	LEFT JOIN cm_acc_detail AS b ON a.account_id = b.id
WHERE po_id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByCashRequestId($cashRequestId) {
		$this->connector->CommandText =
"SELECT a.*, b.acc_no, b.acc_name
FROM ac_cash_request_detail AS a
	LEFT JOIN cm_acc_detail AS b ON a.account_id = b.id
WHERE cash_request_master_id = ?id";
		$this->connector->AddParameter("?id", $cashRequestId);

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CashRequestDetail();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_cash_request_detail(cash_request_master_id, account_id, note, amount, po_id)
VALUES(?crId, ?accId, ?note, ?amount, ?poId)";
		$this->connector->AddParameter("?crId", $this->CashRequestId);
		$this->connector->AddParameter("?accId", $this->AccountId);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?amount", $this->Amount);
		$this->connector->AddParameter("?poId", $this->PoId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	/**
	 * Untuk update data detail. Khusus untuk link PO ID tidak dapat di edit karena ini akan masuk otomatis dari proses PR -> PO
	 *
	 * @param $id
	 * @return int
	 */
	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_detail SET
	account_id = ?accId
	, note = ?note
	, amount = ?amount
WHERE id = ?id";

		$this->connector->AddParameter("?accId", $this->AccountId);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?amount", $this->Amount);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ac_cash_request_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: cash_request_detail.php
