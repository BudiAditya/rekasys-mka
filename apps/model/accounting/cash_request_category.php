<?php

class CashRequestCategory extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $EntityId = 1;
	public $ProjectId = 0;
	public $Code;
	public $Name;
	public $AccountControlId;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
        $this->ProjectId = $row["project_id"];
		$this->Code = $row["code"];
		$this->Name = $row["name"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->AccountControlId = $row["acc_control_id"];
	}

	/**
	 * @param int $id
	 * @return CashRequestCategory
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ac_cash_request_category AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByProjectId($id) {
        $this->connector->CommandText = "SELECT a.* FROM ac_cash_request_category AS a WHERE a.project_id = ?id";
        $this->connector->AddParameter("?id", $id);

        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }

        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadByAllowedProject($allowedProjectIds = 0) {
        $this->connector->CommandText = "SELECT a.* FROM ac_cash_request_category AS a WHERE a.is_deleted = 0 And Locate(a.id,?allow) Order By a.code";
        $this->connector->AddParameter("?allow", $allowedProjectIds);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new CashRequestCategory();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $entId
	 * @param string $orderBy
	 * @return CashRequestCategory[]
	 */
	public function LoadByEntity($entId, $orderBy = "a.code") {
		$this->connector->CommandText = "SELECT a.* FROM ac_cash_request_category AS a WHERE a.is_deleted = 0 AND a.entity_id = ?entId ORDER BY $orderBy";
		$this->connector->AddParameter("?entId", $entId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CashRequestCategory();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ac_cash_request_category(createby_id, create_time, entity_id, code, name, acc_control_id,project_id) VALUES (?userId, NOW(), ?sbu, ?code, ?name, ?accId,?project_id)";
		$this->connector->AddParameter("?userId", $this->CreatedById);
		$this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?code", $this->Code, "varchar");
		$this->connector->AddParameter("?name", $this->Name);
		$this->connector->AddParameter("?accId", $this->AccountControlId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_cash_request_category SET
	updateby_id = ?userId
	, update_time = NOW()
	, code = ?code
	, name = ?name
	, acc_control_id = ?accId
	, entity_id = ?sbu
	, project_id = ?project_id
WHERE id = ?id";
		$this->connector->AddParameter("?userId", $this->UpdatedById);
		$this->connector->AddParameter("?code", $this->Code, "varchar");
		$this->connector->AddParameter("?name", $this->Name);
		$this->connector->AddParameter("?accId", $this->AccountControlId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = "UPDATE ac_cash_request_category SET updateby_id = ?userId, is_deleted = 1 WHERE id = ?id";
		$this->connector->AddParameter("?userId", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = "Delete From ac_cash_request_category WHERE id = ?id";
        $this->connector->AddParameter("?userId", $this->UpdatedById);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }
}


// End of File: cash_request_category.php
