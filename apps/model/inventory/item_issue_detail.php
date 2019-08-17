<?php

class ItemIssueDetail extends EntityBase {
	public $Id;
	public $IsId;
	public $Sequence;
	public $ItemId;
	public $ItemDescription;
	public $Qty;
	public $UomCd;
	public $MrDetailId;
	public $TotalCost = null;
	public $UnitId;
	public $Hm;
	public $DayShift;
	public $Operator;
	public $DeptId = 0;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;
	public $PartNo;
	public $UnitCode;
	public $MrNo;
	public $MrDate;
    public $DeptCode;
    public $DeptName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsId = $row["is_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->ItemId = $row["item_id"];
		$this->ItemDescription = $row["item_description"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->MrDetailId = $row["mr_detail_id"];
		$this->TotalCost = $row["total_cost"];
        $this->UnitId = $row["unit_id"];
        $this->Hm = $row["hm"];
        $this->DayShift = $row["day_shift"];
        $this->Operator = $row["operator"];
        $this->DeptId = $row["dept_id"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->UnitCode = $row["unit_code"];
        $this->MrNo = $row["mr_no"];
        $this->MrDate = $row["mr_date"];
        $this->DeptCode = $row["dept_code"];
        $this->DeptName = $row["dept_name"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* From vw_ic_is_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByIsId($isId, $indexByItemId = true, $orderBy = "b.item_code") {
		$this->connector->CommandText = "SELECT a.* From vw_ic_is_detail AS a WHERE a.is_master_id = ?id";
		$this->connector->AddParameter("?id", $isId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemIssueDetail();
				$temp->FillProperties($row);
                $result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_is_detail(is_master_id, item_id, item_description, qty, uom_cd, mr_detail_id, unit_id, hm, day_shift, operator, dept_id) VALUES (?isId, ?itemId, ?itemDesc, ?qty, ?uomCd, ?mrDetId, ?unit_id, ?hm, ?day_shift, ?operator, ?dept_id)";
		$this->connector->AddParameter("?isId", $this->IsId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?mrDetId", $this->MrDetailId);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
        $this->connector->AddParameter("?hm", $this->Hm);
        $this->connector->AddParameter("?day_shift", $this->DayShift);
        $this->connector->AddParameter("?operator", $this->Operator);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText  = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_is_detail SET
	is_master_id = ?isId
	, item_id = ?itemId
	, item_description = ?itemDesc
	, qty = ?qty
	, uom_cd = ?uomCd
	, mr_detail_id = ?mrDetId
	, unit_id = ?unit_id
	, hm = ?hm
	, day_shift = ?day_shift
	, operator = ?operator
	, dept_id = ?dept_id
WHERE id = ?id";
		$this->connector->AddParameter("?isId", $this->IsId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?mrDetId", $this->MrDetailId);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
        $this->connector->AddParameter("?hm", $this->Hm);
        $this->connector->AddParameter("?day_shift", $this->DayShift);
        $this->connector->AddParameter("?operator", $this->Operator);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_is_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function UpdateCost($id) {
		$this->connector->CommandText = "UPDATE ic_is_detail SET total_cost = ?cost WHERE id = ?id";
		$this->connector->AddParameter("?cost", $this->TotalCost);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: item_issue_detail.php
