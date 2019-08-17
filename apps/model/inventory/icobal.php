<?php

class IcObal extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $ProjectId;
	public $OpnDate;
	public $ItemId;
	public $ItemCode;
	public $Price = 0;
	public $Qty = 0;
	public $OpnStatus = 0;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->ProjectId = $row["project_id"];
		$this->OpnDate = strtotime($row["opn_date"]);
		$this->ItemId = $row["item_id"];
		$this->ItemCode = $row["item_code"];
        $this->Qty = $row["qty"];
        $this->Price = $row["price"];
        $this->OpnStatus = $row["opn_status"];
		$this->CreatebyId = $row["createby_id"];
		$this->CreateTime = strtotime($row["create_time"]);
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = strtotime($row["update_time"]);
	}

    public function FormatOpnDate($format = HUMAN_DATE) {
        return is_int($this->OpnDate) ? date($format, $this->OpnDate) : null;
    }

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ic_opening_balance AS a WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindDuplicate($projectId,$itemId,$price) {
        $this->connector->CommandText = "SELECT a.* FROM ic_opening_balance AS a WHERE a.project_id = ?project_id And a.item_id = ?item_id And a.price = ?price";
        $this->connector->AddParameter("?project_id", $projectId);
        $this->connector->AddParameter("?item_id", $itemId);
        $this->connector->AddParameter("?price", $price);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByProjectId($project_id) {
		$this->connector->CommandText = "SELECT a.* FROM ic_opening_balance AS a WHERE a.project_id = ?project_id";
		$this->connector->AddParameter("?project_id", $project_id);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while($row = $rs->FetchAssoc()) {
				$temp = new IcObal();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_opening_balance(opn_status,project_id, opn_date, item_id, item_code, price, qty, createby_id, create_time) VALUES(?opn_status,?project_id, ?opn_date, ?item_id, ?item_code, ?price, ?qty, ?createby_id, NOW())";
		$this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?opn_date", $this->FormatOpnDate(SQL_DATEONLY));
		$this->connector->AddParameter("?item_id", $this->ItemId, "varchar");
		$this->connector->AddParameter("?item_code", $this->ItemCode,"varchar");
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?opn_status", $this->OpnStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_opening_balance SET
	project_id = ?project_id
	, opn_date = ?opn_date
	, item_id = ?item_id
	, item_code = ?item_code
	, price = ?price
	, qty = ?qty
	, updateby_id = ?updateby_id
	, update_time = NOW()
	, opn_status = ?opn_status
WHERE id = ?id";
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?opn_date", $this->FormatOpnDate(SQL_DATEONLY));
        $this->connector->AddParameter("?item_id", $this->ItemId, "varchar");
        $this->connector->AddParameter("?item_code", $this->ItemCode,"varchar");
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?opn_status", $this->OpnStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE From ic_opening_balance WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Load4Report($entityId = 1,$projectId = 0,$categoryId = 0){
	    $sql = "Select a.*,d.category_code,d.category_desc,b.item_name,b.part_no,b.uom_cd,c.entity_id,c.project_cd,c.project_name From ic_opening_balance AS a JOIN ic_item_master AS b ON a.item_code = b.item_code JOIN cm_project AS c ON a.project_id = c.id JOIN ic_item_category AS d ON b.category_id = d.id Where c.entity_id = $entityId";
	    if ($projectId > 0){
	        $sql.= " And a.project_id = ".$projectId;
        }
        if($categoryId > 0){
	        $sql.= " And b.category_id = ".$categoryId;
        }
        $sql.= " Order By d.category_desc,a.item_code";
        $this->connector->CommandText = $sql;
        return $this->connector->ExecuteQuery();
    }

    public function Posting($entityId,$userId){
        $this->connector->CommandText = "Select fcIcOpeningPosting($entityId,$userId) As ValOut";
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["ValOut"]);
    }

    public function Unposting($entityId,$userId){
        $this->connector->CommandText = "Select fcIcOpeningUnposting($entityId,$userId) As ValOut";
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["ValOut"]);
    }
}


// End of File: icobal.php
