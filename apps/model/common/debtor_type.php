<?php

class DebtorType extends EntityBase {
	public $Id;
	public $EntityId;
	public $EntityCd;
	public $UserUid;
	public $DebtorTypeCd;
	public $DebtorTypeDesc;
	public $DebtorTypeClass;
	public $AccCtl;
	public $AccControlId;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->DebtorTypeCd = $row["debtortype_cd"];
		$this->DebtorTypeDesc = $row["debtortype_desc"];
		$this->DebtorTypeClass = $row["debtortype_class"];
		$this->AccCtl = $row["acc_no"];
		$this->AccControlId = $row["acc_control_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return DebtorType[]
	 */
	public function LoadAll($orderBy = "a.debtortype_cd", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.acc_no
FROM ar_debtortype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_acc_detail AS c ON a.acc_control_id = c.id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.acc_no
FROM ar_debtortype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_acc_detail AS c ON a.acc_control_id = c.id
WHERE a.is_deleted = 0
ORDER BY $orderBy";
		}

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new DebtorType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $companyId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return DebtorType[]
	 */
	public function LoadByEntity($companyId, $orderBy = "a.debtortype_cd", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.acc_no
FROM ar_debtortype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_acc_detail AS c ON a.acc_control_id = c.id
WHERE a.entity_id = ?sbu
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.acc_no
FROM ar_debtortype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_acc_detail AS c ON a.acc_control_id = c.id
WHERE a.is_deleted = 0 AND a.entity_id = ?sbu
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?sbu", $companyId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new DebtorType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return DebtorType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return DebtorType
	 */
	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.acc_no
FROM ar_debtortype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_acc_detail AS c ON a.acc_control_id = c.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);

		return $this;
	}

	/**
	 * @param string $cd
	 * @param int $entityId
	 * @return DebtorType
	 */
	public function FindByCd($cd, $entityId) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.acc_no
FROM ar_debtortype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_acc_detail AS c ON a.acc_control_id = c.id
WHERE a.debtortype_cd = ?cd and a.entity_id = ?entid";
		$this->connector->AddParameter("?cd", $cd);
		$this->connector->AddParameter("?entid", $entityId);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);

		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO ar_debtortype (entity_id, debtortype_cd, debtortype_desc, debtortype_class, acc_control_id)
VALUES(?entity_id, ?debtortype_cd, ?debtortype_desc, ?debtortype_class, ?acc_control_id)';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?debtortype_cd", $this->DebtorTypeCd);
		$this->connector->AddParameter("?debtortype_desc", $this->DebtorTypeDesc);
		$this->connector->AddParameter("?debtortype_class", $this->DebtorTypeClass);
		$this->connector->AddParameter("?acc_control_id", $this->AccControlId);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE ar_debtortype SET
	entity_id = ?entity_id
	, debtortype_cd = ?debtortype_cd
	, debtortype_desc = ?debtortype_desc
	, debtortype_class = ?debtortype_class
	, acc_control_id = ?acc_control_id
WHERE id = ?id';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?debtortype_cd", $this->DebtorTypeCd);
		$this->connector->AddParameter("?debtortype_desc", $this->DebtorTypeDesc);
		$this->connector->AddParameter("?debtortype_class", $this->DebtorTypeClass);
		$this->connector->AddParameter("?acc_control_id", $this->AccControlId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From ar_debtortype WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}
