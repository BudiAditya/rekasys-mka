<?php
class UnitBrand extends EntityBase {
	public $Id;
	public $EntityId = 1;
    public $BrandCode;
	public $BrandName;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
        $this->BrandCode = $row["brand_code"];
		$this->BrandName = $row["brand_name"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return UnitBrand[]
	 */
	public function LoadAll($eti = 1,$orderBy = "a.brand_code") {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_brand AS a Where a.entity_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti",$eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UnitBrand();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return UnitBrand
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_unit_brand AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByBrand($eti = 1,$brand) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_brand AS a WHERE a.entity_id = ?eti And a.brand_name = ?brand";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?brand", $brand);
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
	 * @return UnitBrand
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

    public function FindByCode($eti = 1,$code) {
        $this->connector->CommandText = "SELECT a.* FROM cm_unit_brand AS a WHERE a.entity_id = ?eti And a.brand_code = ?code";
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
		$this->connector->CommandText = 'INSERT INTO cm_unit_brand(entity_id, brand_code, brand_name) VALUES(?entity_id, ?brand_code, ?brand_name)';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?brand_code", $this->BrandCode,"varchar");
        $this->connector->AddParameter("?brand_name", $this->BrandName);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_unit_brand SET
	entity_id = ?entity_id,
	brand_code = ?brand_code,
	brand_name = ?brand_name
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?brand_code", $this->BrandCode,"varchar");
        $this->connector->AddParameter("?brand_name", $this->BrandName);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From cm_unit_brand WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}
