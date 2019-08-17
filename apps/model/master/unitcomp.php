<?php
class UnitComp extends EntityBase {
	public $Id;
	public $EntityId = 1;
    public $CompCode;
	public $CompName;
	public $CompModel;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
        $this->CompCode = $row["comp_code"];
		$this->CompName = $row["comp_name"];
        $this->CompModel = $row["comp_model"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return UnitComp[]
	 */
	public function LoadAll($eti = 1,$orderBy = "a.comp_code") {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_component AS a Where a.entity_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti",$eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UnitComp();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return UnitComp
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_component AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByComp($eti = 1,$comp) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_component AS a WHERE a.entity_id = ?eti And a.comp_name = ?comp";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?comp", $comp);
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
	 * @return UnitComp
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

    public function FindByCode($eti = 1,$code) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_component AS a WHERE a.entity_id = ?eti And a.comp_code = ?code";
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
		$this->connector->CommandText = 'INSERT INTO cm_unit_component(entity_id, comp_code, comp_name, comp_model) VALUES(?entity_id, ?comp_code, ?comp_name, ?comp_model)';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?comp_code", $this->CompCode,"varchar");
        $this->connector->AddParameter("?comp_name", $this->CompName);
        $this->connector->AddParameter("?comp_model", $this->CompModel);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_unit_component SET
	entity_id = ?entity_id,
	comp_code = ?comp_code,
	comp_name = ?comp_name,
	comp_model = ?comp_model
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?comp_code", $this->CompCode,"varchar");
        $this->connector->AddParameter("?comp_name", $this->CompName);
        $this->connector->AddParameter("?comp_model", $this->CompModel);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From cm_unit_component WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}
