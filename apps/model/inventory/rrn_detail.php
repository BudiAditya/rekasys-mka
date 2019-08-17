<?php

class RrnDetail extends EntityBase {
	public $Id;
	public $RnId;
	public $Sequence;
	public $RoDetailId;
	public $ItemId;
	public $ItemDescription;
	public $Qty;
	public $UomCd;
	public $Price;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;
	public $PartNo;
	public $RoNo;
	public $RoDate;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->RnId = $row["rn_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->RoDetailId = $row["ro_detail_id"];
		$this->ItemId = $row["item_id"];
		$this->ItemDescription = $row["item_description"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->Price = $row["price"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->RoDate = $row["ro_date"];
        $this->RoNo = $row["ro_no"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_rn_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByRnId($RnId, $indexByItemId = true, $orderBy = "a.item_code") {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_rn_detail AS a WHERE a.rn_master_id = ?id ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $RnId);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new RrnDetail();
				$temp->FillProperties($row);

				if ($indexByItemId) {
					$result[$temp->ItemId] = $temp;
				} else {
					$result[] = $temp;
				}
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_rn_detail(rn_master_id, ro_detail_id, item_id, item_description, qty, uom_cd, price) VALUES (?gnId, ?roDetId, ?itemId, ?itemDesc, ?qty, ?uomCd, ?price)";
		$this->connector->AddParameter("?gnId", $this->RnId);
		$this->connector->AddParameter("?roDetId", $this->RoDetailId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText  = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_rn_detail SET
	rn_master_id = ?gnId
	, ro_detail_id = ?roDetId
	, item_id = ?itemId
	, item_description = ?itemDesc
	, qty = ?qty
	, uom_cd = ?uomCd
	, price = ?price
WHERE id = ?id";
		$this->connector->AddParameter("?gnId", $this->RnId);
		$this->connector->AddParameter("?roDetId", $this->RoDetailId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_rn_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: good_receipt_detail.php
