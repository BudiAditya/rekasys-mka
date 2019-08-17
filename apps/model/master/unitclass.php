<?php
class UnitClass extends EntityBase {
	public $Id;
	public $EntityId = 1;
    public $ClassCode;
	public $ClassName;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
        $this->ClassCode = $row["class_code"];
		$this->ClassName = $row["class_name"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return UnitClass[]
	 */
	public function LoadAll($eti = 1,$orderBy = "a.class_code") {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_class AS a Where a.entity_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti",$eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UnitClass();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return UnitClass
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_class AS a WHERE a.id = ?id";
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
	 * @return UnitClass
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

    public function FindByCode($eti = 1,$code) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_class AS a WHERE a.entity_id = ?eti And a.class_code = ?code";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?code", $code);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

    public function FindByName($eti = 1,$code) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_class AS a WHERE a.entity_id = ?eti And a.class_name = ?code";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?code", $code);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO cm_unit_class(entity_id, class_code, class_name) VALUES(?entity_id, ?class_code, ?class_name)';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?class_code", $this->ClassCode,"varchar");
        $this->connector->AddParameter("?class_name", $this->ClassName);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_unit_class SET
	entity_id = ?entity_id,
	class_code = ?class_code,
	class_name = ?class_name
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?class_code", $this->ClassCode,"varchar");
        $this->connector->AddParameter("?class_name", $this->ClassName);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From cm_unit_class WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}
