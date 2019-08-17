<?php

class RoDetail extends EntityBase {
	public $Id;
	public $RoId;
	public $Sequence;
	public $RrDetailId;
	public $ItemId;
	public $ItemDescription;
	public $Qty;
	public $UomCd;
	public $Price;
	public $RecQty;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;
	public $PartNo;
    public $RrId;
    public $RrCode;
    public $RrNo;
    public $RrDate;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->RoId = $row["ro_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->RrDetailId = $row["rr_detail_id"];
		$this->ItemId = $row["item_id"];
		$this->ItemDescription = $row["item_description"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->Price = $row["price"];
        $this->RecQty = $row["rec_qty"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->RrId = $row["rr_id"];
        $this->RrCode = $row["rr_no"];
        $this->RrNo = $row["rr_no"];
        $this->RrDate = $row["rr_date"];
	}

	/**
	 * @param int $id
	 * @return RoDetail
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* From vw_ic_ro_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * @param int $poId
	 * @param bool $loadPrCode
	 * @param string $orderBy
	 * @return RoDetail[]
	 */
	public function LoadByRoId($poId, $orderBy = "a.item_code") {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_ro_detail AS a WHERE a.ro_master_id = ?id ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $poId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new RoDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_ro_detail(ro_master_id, rr_detail_id, item_id, item_description, qty, uom_cd, price) VALUES (?poId, ?prDetId, ?itemId, ?itemDesc, ?qty, ?uomCd, ?price)";
		$this->connector->AddParameter("?poId", $this->RoId);
		$this->connector->AddParameter("?prDetId", $this->RrDetailId);
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
"UPDATE ic_ro_detail SET
	ro_master_id = ?poId
	, rr_detail_id = ?prDetId
	, item_id = ?itemId
	, item_description = ?itemDesc
	, qty = ?qty
	, uom_cd = ?uomCd
	, price = ?price
WHERE id = ?id";
		$this->connector->AddParameter("?poId", $this->RoId);
		$this->connector->AddParameter("?prDetId", $this->RrDetailId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_ro_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: ro_detail.php
