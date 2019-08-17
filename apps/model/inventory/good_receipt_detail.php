<?php

class GoodReceiptDetail extends EntityBase {
	public $Id;
	public $GnId;
	public $Sequence;
	public $PoDetailId;
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
	public $PoNo;
	public $PoDate;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->GnId = $row["gn_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->PoDetailId = $row["po_detail_id"];
		$this->ItemId = $row["item_id"];
		$this->ItemDescription = $row["item_description"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->Price = $row["price"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->PoDate = $row["po_date"];
        $this->PoNo = $row["po_no"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_gn_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByGnId($GnId, $indexByItemId = true, $orderBy = "a.item_code") {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_gn_detail AS a WHERE a.gn_master_id = ?id ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $GnId);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new GoodReceiptDetail();
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
		$this->connector->CommandText = "INSERT INTO ic_gn_detail(gn_master_id, po_detail_id, item_id, item_description, qty, uom_cd, price) VALUES (?gnId, ?poDetId, ?itemId, ?itemDesc, ?qty, ?uomCd, ?price)";
		$this->connector->AddParameter("?gnId", $this->GnId);
		$this->connector->AddParameter("?poDetId", $this->PoDetailId);
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
"UPDATE ic_gn_detail SET
	gn_master_id = ?gnId
	, po_detail_id = ?poDetId
	, item_id = ?itemId
	, item_description = ?itemDesc
	, qty = ?qty
	, uom_cd = ?uomCd
	, price = ?price
WHERE id = ?id";
		$this->connector->AddParameter("?gnId", $this->GnId);
		$this->connector->AddParameter("?poDetId", $this->PoDetailId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_gn_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: good_receipt_detail.php
