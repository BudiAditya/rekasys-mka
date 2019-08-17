<?php

class PoDetail extends EntityBase {
	public $Id;
	public $PoId;
	public $Sequence;
	public $PrDetailId;
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
    public $PrId;
    public $PrCode;
    public $PrNo;
    public $PrDate;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->PoId = $row["po_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->PrDetailId = $row["pr_detail_id"];
		$this->ItemId = $row["item_id"];
		$this->ItemDescription = $row["item_description"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->Price = $row["price"];
        $this->RecQty = $row["rec_qty"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->PrId = $row["pr_id"];
        $this->PrCode = $row["pr_no"];
        $this->PrNo = $row["pr_no"];
        $this->PrDate = $row["pr_date"];
	}

	/**
	 * @param int $id
	 * @return PoDetail
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* From vw_ic_po_detail AS a WHERE a.id = ?id";
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
	 * @return PoDetail[]
	 */
	public function LoadByPoId($poId, $orderBy = "a.item_code") {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_po_detail AS a WHERE a.po_master_id = ?id ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $poId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new PoDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_po_detail(po_master_id, pr_detail_id, item_id, item_description, qty, uom_cd, price) VALUES (?poId, ?prDetId, ?itemId, ?itemDesc, ?qty, ?uomCd, ?price)";
		$this->connector->AddParameter("?poId", $this->PoId);
		$this->connector->AddParameter("?prDetId", $this->PrDetailId);
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
"UPDATE ic_po_detail SET
	po_master_id = ?poId
	, pr_detail_id = ?prDetId
	, item_id = ?itemId
	, item_description = ?itemDesc
	, qty = ?qty
	, uom_cd = ?uomCd
	, price = ?price
WHERE id = ?id";
		$this->connector->AddParameter("?poId", $this->PoId);
		$this->connector->AddParameter("?prDetId", $this->PrDetailId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_po_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: po_detail.php
