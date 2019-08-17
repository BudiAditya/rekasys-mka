<?php

class AssetCategory extends EntityBase {
    public $Id;
    public $IsDeleted = false;
    public $UpdatedUserId;
    public $UpdatedDate;

    public $EntityId;
    public $Code;
    public $Name;
	public $AssetAccountId;
	public $DepreciationAccountId;
	public $CostAccountId;
	public $RevenueAccountId;
	public $DepreciationMethodId;
	public $MaxAge;
	public $DepreciationPercentage;

	// Helper
	public $EntityCode;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

    public function FillProperties(array $row) {
        $this->Id = $row["id"];
        $this->IsDeleted = $row["is_deleted"] == 1;
        $this->UpdatedUserId = $row["updateby_id"];
        $this->UpdatedDate = strtotime($row["update_time"]);

        $this->EntityId = $row["entity_id"];
        $this->Code = $row["code"];
        $this->Name = $row["name"];
		$this->AssetAccountId = $row["ast_acc_id"];
		$this->DepreciationAccountId = $row["dep_acc_id"];
		$this->CostAccountId = $row["cos_acc_id"];
		$this->RevenueAccountId = $row["rev_acc_id"];
		$this->DepreciationMethodId = $row["dep_method"];
		$this->MaxAge = $row["max_age"];
		$this->DepreciationPercentage = $row["dep_percentage"];

		$this->EntityCode = $row["entity_cd"];
    }

	public function GetDepreciationMethod() {
		if ($this->DepreciationMethodId == null) {
			return null;
		}
		switch ($this->DepreciationMethodId) {
			case 1:
				return "STRAIGHT LINE";
			case 2:
				return "DOUBLE DECLINING";
			default:
				throw new Exception("Unknown DepreciationMethodId ! Given Code: " . $this->DepreciationMethodId);
		}
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return AssetCategory[]
	 */
	public function LoadAll($orderBy = "b.entity_cd, a.name", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM ac_asset_category AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM ac_asset_category AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.is_deleted = 0
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?entity_id", $entityId);
		$rs = $this->connector->ExecuteQuery();

		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new AssetCategory();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $entityId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return AssetCategory[]
	 */
	public function LoadByEntityId($entityId, $orderBy = "a.name", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM ac_asset_category AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?entity_id
ORDER BY $orderBy";
        } else {
            $this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM ac_asset_category AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?entity_id AND a.is_deleted = 0
ORDER BY $orderBy";
        }

        $this->connector->AddParameter("?entity_id", $entityId);
        $rs = $this->connector->ExecuteQuery();

        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new AssetCategory();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $id
	 * @return AssetCategory
	 */
	public function LoadById($id) {
        $this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM ac_asset_category AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);

        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }

        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadByCode($entityId,$aCode) {
        $this->connector->CommandText = "SELECT a.*, b.entity_cd FROM ac_asset_category AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.entity_id = ?entity_id And a.`code` = ?code";
        $this->connector->AddParameter("?entity_id", $entityId);
        $this->connector->AddParameter("?code", $aCode);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function Insert() {
        $this->connector->CommandText =
"INSERT INTO ac_asset_category(entity_id, code, name, ast_acc_id, dep_acc_id, cos_acc_id, rev_acc_id, dep_method, max_age, dep_percentage, updateby_id, update_time)
VALUES(?sbu, ?code, ?name, ?ast, ?dep, ?cos, ?rev, ?method, ?age, ?percentage, ?user, NOW())";
        $this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?code", $this->Code);
        $this->connector->AddParameter("?name", $this->Name);
		$this->connector->AddParameter("?ast", $this->AssetAccountId);
		$this->connector->AddParameter("?dep", $this->DepreciationAccountId);
		$this->connector->AddParameter("?cos", $this->CostAccountId);
		$this->connector->AddParameter("?rev", $this->RevenueAccountId);
		$this->connector->AddParameter("?method", $this->DepreciationMethodId);
		$this->connector->AddParameter("?age", $this->MaxAge);
		$this->connector->AddParameter("?percentage", $this->DepreciationPercentage);
        $this->connector->AddParameter("?user", $this->UpdatedUserId);

        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID()";
            $this->Id = $this->connector->ExecuteScalar();
        }

        return $rs;
    }

    public function Update($id) {
        $this->connector->CommandText =
"UPDATE ac_asset_category SET
	entity_id = ?sbu
	, code = ?code
	, name = ?name
	, ast_acc_id = ?ast
	, dep_acc_id = ?dep
	, cos_acc_id = ?cos
	, rev_acc_id = ?rev
	, dep_method = ?method
	, max_age = ?age
	, dep_percentage = ?percentage
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?code", $this->Code);
		$this->connector->AddParameter("?name", $this->Name);
		$this->connector->AddParameter("?ast", $this->AssetAccountId);
		$this->connector->AddParameter("?dep", $this->DepreciationAccountId);
		$this->connector->AddParameter("?cos", $this->CostAccountId);
		$this->connector->AddParameter("?rev", $this->RevenueAccountId);
		$this->connector->AddParameter("?method", $this->DepreciationMethodId);
		$this->connector->AddParameter("?age", $this->MaxAge);
		$this->connector->AddParameter("?percentage", $this->DepreciationPercentage);
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
        $this->connector->AddParameter("?id", $id);

        return $this->connector->ExecuteNonQuery();
    }

    public function Delete($id) {
        $this->connector->CommandText = "Delete From ac_asset_category WHERE id = ?id";
        $this->connector->AddParameter("?user", $this->UpdatedUserId);
        $this->connector->AddParameter("?id", $id);

        return $this->connector->ExecuteNonQuery();
    }
}


// End of File: asset_category.php
