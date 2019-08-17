<?php
class Activity extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
    public $EntityCd;
    public $DeptId;
	public $ActCode;
	public $ActName;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
        $this->EntityCd = $row["entity_cd"];
		$this->DeptId = $row["dept_id"];
        $this->ActCode = $row["act_code"];
		$this->ActName = $row["act_name"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Activity[]
	 */
	public function LoadAll($orderBy = "a.act_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Activity();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $sbu
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Activity[]
	 */
	public function LoadByEntityId($sbu, $orderBy = "a.act_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS c ON a.entity_id = b.entity_id
WHERE a.entity_id = ?id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.is_deleted = 0 AND a.entity_id = ?id
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?id", $sbu);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Activity();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $divId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Activity[]
	 */
	public function LoadByDeptId($divId, $orderBy = "a.act_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*,b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.dept_id = ?dpi
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.dept_id = ?dpi
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?dpi", $divId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Activity();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return Activity
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd FROM cm_activity AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.id = ?id";
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
	 * @param int $id
	 * @return Activity
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param string $entityCode
	 * @param string $deptCode
	 * @param string $divCode
	 * @return Activity
	 */
	public function FindByCode($entityCode, $deptCode, $divCode) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_activity AS a
	JOIN cm_company AS c ON a.entity_id = b.entity_id
WHERE b.entity_cd = ?etc AND a.dept_cd = ?dpc AND a.act_code = ?dvc";
        $this->connector->AddParameter("?etc", $entityCode);
		$this->connector->AddParameter("?dpc", $deptCode);
        $this->connector->AddParameter("?dvc", $divCode);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO cm_activity(entity_id,act_code,act_name) VALUES(?entity_id,?act_code,?act_name)';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?act_code", $this->ActCode);
        $this->connector->AddParameter("?act_name", $this->ActName);
		
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_activity SET
	entity_id = ?entity_id,
	act_code = ?act_code,
	act_name = ?act_name
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?act_code", $this->ActCode);
        $this->connector->AddParameter("?act_name", $this->ActName);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'UPDATE cm_activity SET is_deleted = 1 WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}
