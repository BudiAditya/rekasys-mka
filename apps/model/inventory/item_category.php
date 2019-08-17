<?php

class ItemCategory extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $UpdatedUserId;
	public $UpdatedDate;

	public $EntityId;
	public $Code;
	public $Description;
	public $InventoryAccountId;
	public $CostAccountId;
	public $IsStock = false;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->UpdatedUserId = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->EntityId = $row["entity_id"];
		$this->Code = $row["category_code"];
		$this->Description = $row["category_desc"];
		$this->InventoryAccountId = $row["invt_acc_id"];
		$this->CostAccountId = $row["cost_acc_id"];
		$this->IsStock = $row["is_stock"] == 1;
	}

	/**
	 * @param int $entityId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return ItemCategory[]
	 */
	public function LoadByEntityId($entityId, $orderBy = "a.category_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*
FROM ic_item_category AS a
WHERE a.entity_id = ?entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*
FROM ic_item_category AS a
WHERE a.entity_id = ?entity_id AND a.is_deleted = 0
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?entity_id", $entityId);
		$rs = $this->connector->ExecuteQuery();

		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemCategory();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return ItemCategory
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ic_item_category AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCode($entityId,$catCode) {
        $this->connector->CommandText = "SELECT a.* FROM ic_item_category AS a WHERE a.entity_id = ?entityId And a.category_code = ?catCode";
        $this->connector->AddParameter("?entityId", $entityId);
        $this->connector->AddParameter("?catCode", $catCode);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }

        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }
	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_item_category(entity_id, category_code, category_desc, invt_acc_id, cost_acc_id, updateby_id, update_time, is_stock)
VALUES(?sbu, ?code, ?desc, ?invt, ?cost, ?user, NOW(), ?stock)";
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?code", $this->Code);
		$this->connector->AddParameter("?desc", $this->Description);
		$this->connector->AddParameter("?invt", $this->InventoryAccountId);
		$this->connector->AddParameter("?cost", $this->CostAccountId);
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?stock", $this->IsStock);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_item_category SET
	entity_id = ?sbu
	, category_code = ?code
	, category_desc = ?desc
	, invt_acc_id = ?invt
	, cost_acc_id = ?cost
	, updateby_id = ?user
	, update_time = NOW()
	, is_stock = ?stock
WHERE id = ?id";
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?code", $this->Code);
		$this->connector->AddParameter("?desc", $this->Description);
		$this->connector->AddParameter("?invt", $this->InventoryAccountId);
		$this->connector->AddParameter("?cost", $this->CostAccountId);
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?stock", $this->IsStock);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ic_item_category SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: item_category.php
