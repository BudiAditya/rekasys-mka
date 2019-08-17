<?php
class UnitType extends EntityBase {
	public $Id;
	public $EntityId = 1;
    public $TypeCode;
	public $TypeDesc;
	public $TypeInitial;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
        $this->TypeCode = $row["type_code"];
		$this->TypeInitial = $row["type_initial"];
        $this->TypeDesc = $row["type_desc"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return UnitType[]
	 */
	public function LoadAll($eti = 1,$orderBy = "a.type_code") {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_type AS a Where a.entity_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti",$eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UnitType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return UnitType
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_type AS a WHERE a.id = ?id";
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
	 * @return UnitType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

    public function FindByCode($eti = 1,$code) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_type AS a WHERE a.entity_id = ?eti And a.type_code = ?code";
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

    public function FindByInitial($eti = 1,$init) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_type AS a WHERE a.entity_id = ?eti And a.type_initial = ?init";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?init", $init);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

    public function FindByName($eti = 1,$name) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_type AS a WHERE a.entity_id = ?eti And a.type_desc = ?name";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?name", $name);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO cm_unit_type(type_initial,entity_id, type_code, type_desc) VALUES(?type_initial,?entity_id, ?type_code, ?type_desc)';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?type_code", $this->TypeCode,"varchar");
        $this->connector->AddParameter("?type_initial", $this->TypeInitial);
        $this->connector->AddParameter("?type_desc", $this->TypeDesc);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_unit_type SET
	entity_id = ?entity_id,
	type_code = ?type_code,
	type_desc = ?type_desc,
	type_initial = ?type_initial
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?type_code", $this->TypeCode,"varchar");
        $this->connector->AddParameter("?type_initial", $this->TypeInitial);
        $this->connector->AddParameter("?type_desc", $this->TypeDesc);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From cm_unit_type WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}
