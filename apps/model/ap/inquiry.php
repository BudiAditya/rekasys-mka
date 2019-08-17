<?php

class Inquiry extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreateById;
	public $CreateTime;
	public $UpdateById;
	public $UpdateTime;
	public $SupplierId;
	public $ItemId;
	public $Price = 0;
	public $ValidStart;
	public $ValidEnd;
	public $ReffNo;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"];
		$this->CreateById = $row["createby_id"];
		$this->CreateTime = strtotime($row["create_time"]);
		$this->UpdateById = $row["updateby_id"];
		$this->UpdateTime = strtotime($row["update_time"]);
		$this->SupplierId = $row["supplier_id"];
		$this->ItemId = $row["item_id"];
		$this->Price = $row["price"];
        $this->ReffNo = $row["reff_no"];
		$this->ValidStart = strtotime($row["valid_start"]);
		$this->ValidEnd = strtotime($row["valid_end"]);
	}

	public function FormatValidStart($format = HUMAN_DATE) {
		return is_int($this->ValidStart) ? date($format, $this->ValidStart) : null;
	}

	public function FormatValidEnd($format = HUMAN_DATE) {
		return is_int($this->ValidEnd) ? date($format, $this->ValidEnd) : null;
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ap_item_inquiry AS a WHERE a.id = ?id";
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
"INSERT INTO ap_item_inquiry(createby_id, create_time, supplier_id, item_id, price, valid_start, valid_end, reff_no)
VALUES (?user, NOW(), ?supp, ?item, ?price, ?start, ?end, ?reff_no)";
		$this->connector->AddParameter("?user", $this->CreateById);
		$this->connector->AddParameter("?supp", $this->SupplierId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
		$this->connector->AddParameter("?start", $this->FormatValidStart(SQL_DATETIME));
		$this->connector->AddParameter("?end", $this->FormatValidEnd(SQL_DATETIME));

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ap_item_inquiry SET
	updateby_id = ?user
	, update_time = NOW()
	, supplier_id = ?supp
	, item_id = ?item
	, price = ?price
	, reff_no = ?reff_no
	, valid_start = ?start
	, valid_end = ?end
WHERE id = ?id";

		$this->connector->AddParameter("?user", $this->CreateById);
		$this->connector->AddParameter("?supp", $this->SupplierId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
		$this->connector->AddParameter("?start", $this->FormatValidStart(SQL_DATETIME));
		$this->connector->AddParameter("?end", $this->FormatValidEnd(SQL_DATETIME));
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE ap_item_inquiry SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";

		$this->connector->AddParameter("?user", $this->CreateById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: item_inquiry.php
