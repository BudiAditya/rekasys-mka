<?php

class AdjustmentDetail extends EntityBase {
	public $Id;
	public $AdjustmentId;
	public $SequenceNo;
	public $ItemId;
	public $Qty;
	public $UomCd;
	public $Price;
	public $Note;
	public $TotalCost;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->AdjustmentId = $row["adjustment_master_id"];
		$this->SequenceNo = $row["seq_no"];
		$this->ItemId = $row["item_id"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->Note = $row["note"];
		$this->Price = $row["price"];
		$this->TotalCost = $row["total_cost"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
	}

	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.item_code, b.item_name
FROM ic_adjustment_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByAdjustmentId($adjustmentId, $orderBy = "b.item_code") {
		$this->connector->CommandText =
"SELECT a.*, b.item_code, b.item_name
FROM ic_adjustment_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
WHERE a.adjustment_master_id = ?id
ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $adjustmentId);

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new AdjustmentDetail();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_adjustment_detail(adjustment_master_id, item_id, qty, uom_cd, note, price)
VALUES(?adjId, ?item, ?qty, ?uom, ?note, ?price)";

		$this->connector->AddParameter("?adjId", $this->AdjustmentId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uom", $this->UomCd);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?price", $this->Price);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_adjustment_detail SET
	adjustment_master_id = ?adjId
	, item_id = ?item
	, qty = ?qty
	, uom_cd = ?uom
	, note = ?note
	, price = ?price
WHERE id = ?id";

		$this->connector->AddParameter("?adjId", $this->AdjustmentId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uom", $this->UomCd);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_adjustment_detail WHERE id = ?id";

		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteQuery();
	}

	public function UpdateCost($id) {
		$this->connector->CommandText =
"UPDATE ic_adjustment_detail SET
	total_cost = ?cost
WHERE id = ?id";
		$this->connector->AddParameter("?cost", $this->TotalCost);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: adjustment_detail.php
