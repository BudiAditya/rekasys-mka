<?php
class PropertyType extends EntityBase {
	public $Id;
	public $EntityId;
	public $EntityCd;
	public $PropertyCd;
	public $PropertyName;

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
		$this->PropertyCd = $row["property_cd"];
		$this->PropertyName = $row["property_name"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return PropertyType[]
	 */
	public function LoadAll($orderBy = "a.property_cd", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_propertytype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_propertytype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.is_deleted = 0
ORDER BY $orderBy";
		}

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new PropertyType();
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
	 * @return PropertyType[]
	 */
	public function LoadByEntityId($entityId, $orderBy = "a.property_cd", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_propertytype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_propertytype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?entity_id AND a.is_deleted = 0
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?entity_id", $entityId);
		$rs = $this->connector->ExecuteQuery();

		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new PropertyType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return PropertyType
	 */
	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_propertytype AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
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
	 * @param int $id
	 * @return PropertyType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO pm_propertytype(entity_id, property_cd, property_name)
VALUES(?entity_id, ?property_cd, ?property_name)";
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?property_cd", $this->PropertyCd);
		$this->connector->AddParameter("?property_name", $this->PropertyName);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE pm_propertytype SET
	entity_id = ?entity_id,
	property_cd = ?property_cd,
	property_name = ?property_name
WHERE id = ?id";
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?property_cd", $this->PropertyCd);
		$this->connector->AddParameter("?property_name", $this->PropertyName);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE pm_propertytype SET is_deleted = 1 WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}
