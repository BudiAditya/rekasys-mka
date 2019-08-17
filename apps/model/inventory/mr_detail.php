<?php

class MrDetail extends EntityBase {
	public $Id;
	public $MrId;
	public $Sequence;
	public $ItemId;
	public $RequestedQty;
	public $ApprovedQty;
	public $ItemDescription;
	public $UomCd;
	public $UnitId;
	public $StsItem = 1;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;
	public $PartNo;
    public $UnitCode;
    public $UnitName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->MrId = $row["mr_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->ItemId = $row["item_id"];
		$this->RequestedQty = $row["req_qty"];
		$this->ApprovedQty = $row["app_qty"];
		$this->ItemDescription = $row["item_description"];
		$this->UomCd = $row["uom_cd"];
        $this->UnitId = $row["unit_id"];
        $this->StsItem = $row["sts_item"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->UnitCode = $row["unit_code"];
        $this->UnitName = $row["unit_name"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*, b.item_code, b.item_name, c.unit_code, c.unit_name, b.part_no FROM ic_mr_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
	LEFT JOIN cm_units AS c ON a.unit_id = c.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*, b.item_code, b.item_name, c.unit_code, c.unit_name, b.part_no FROM ic_mr_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
	LEFT JOIN cm_units AS c ON a.unit_id = c.id
WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);

        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByMrId($mrId, $orderBy = "b.item_code") {
		$this->connector->CommandText =
"SELECT a.*, b.item_code, b.item_name, c.unit_code, c.unit_name, b.part_no
FROM ic_mr_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
	LEFT JOIN cm_units AS c ON a.unit_id = c.id
WHERE a.mr_master_id = ?id
ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $mrId);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new MrDetail();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

    public function FindDuplicate($mrId,$itemId,$unitId,$itemDesc){
	    $sql = "SELECT a.*, b.item_code, b.item_name, c.unit_code, c.unit_name, b.part_no FROM ic_mr_detail AS a JOIN ic_item_master AS b ON a.item_id = b.id LEFT JOIN cm_units AS c ON a.unit_id = c.id";
        $sql.= " WHERE a.mr_master_id = $mrId And a.item_id = $itemId And a.unit_id = $unitId And a.item_description = '".$itemDesc."'";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_mr_detail(mr_master_id, item_id, req_qty, item_description, uom_cd, unit_id, sts_item) VALUES (?mrId, ?itemId, ?qty, ?itemDesc, ?uomCd, ?unitId, ?stsItem)";
		$this->connector->AddParameter("?mrId", $this->MrId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->RequestedQty);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
        $this->connector->AddParameter("?unitId", $this->UnitId);
        $this->connector->AddParameter("?stsItem", $this->StsItem);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText  = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_mr_detail SET
	mr_master_id = ?mrId
	, item_id = ?itemId
	, req_qty = ?qty
	, item_description = ?itemDesc
	, uom_cd = ?uomCd
	, unit_id = ?unitId
	, sts_item = ?stsItem
WHERE id = ?id";
		$this->connector->AddParameter("?mrId", $this->MrId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->RequestedQty);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
        $this->connector->AddParameter("?unitId", $this->UnitId);
        $this->connector->AddParameter("?stsItem", $this->StsItem);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_mr_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Approve($id) {
		$this->connector->CommandText = "UPDATE ic_mr_detail SET app_qty = ?qty WHERE id = ?id";
		$this->connector->AddParameter("?qty", $this->ApprovedQty);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: mr_detail.php
