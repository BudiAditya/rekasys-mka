<?php
class Units extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $EntityId = 1;
    public $TypeCode;
    public $ClassCode;
    public $BrandCode;
	public $UnitCode;
	public $UnitName;
	public $UnitModel;
	public $NoPol;
	public $NoMesin;
	public $NoChasis;
	public $KmPosition = 0;
	public $HmPosition = 0;
	public $KmRate = 0;
	public $HmRate = 0;
	public $UnitStatus = 1;
	public $ProdYear = 0;
	public $SnNo = '-';
	public $CreatebyId = 0;
    public $UpdatebyId = 0;
    public $AssetId;

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
        $this->TypeCode = $row["type_code"];
        $this->ClassCode = $row["class_code"];
        $this->BrandCode = $row["brand_code"];
        $this->UnitCode = $row["unit_code"];
        $this->UnitName = $row["unit_name"];
        $this->UnitModel = $row["unit_model"];
        $this->NoPol = $row["no_pol"];
        $this->NoMesin = $row["no_mesin"];
        $this->NoChasis = $row["no_chasis"];
        $this->KmPosition = $row["km_position"];
        $this->HmPosition = $row["hm_position"];
        $this->KmRate = $row["km_rate"];
        $this->HmRate = $row["hm_rate"];
        $this->UnitStatus = $row["unit_status"];
        $this->ProdYear = $row["prod_year"];
        $this->SnNo = $row["sn_no"];
        $this->AssetId = $row["asset_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Units[]
	 */
	public function LoadAll($eti = 1,$orderBy = "a.type_code") {
		$this->connector->CommandText = "SELECT a.* FROM cm_units AS a Where a.entity_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti",$eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Units();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return Units
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_units AS a WHERE a.id = ?id";
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
	 * @return Units
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

    public function FindByCode($eti = 1,$code) {
        $this->connector->CommandText = "SELECT a.* FROM cm_units AS a WHERE a.entity_id = ?eti And a.unit_code = ?code";
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
		$this->connector->CommandText = 'INSERT INTO cm_units(asset_id,sn_no,class_code,prod_year,entity_id, type_code, unit_code, unit_name, unit_model, brand_code, no_pol, no_mesin, no_chasis, km_position, hm_position, km_rate, hm_rate, unit_status, createby_id, create_time) VALUES(?asset_id,?sn_no,?class_code,?prod_year,?entity_id, ?type_code, ?unit_code, ?unit_name, ?unit_model, ?brand_code, ?no_pol, ?no_mesin, ?no_chasis, ?km_position, ?hm_position, ?km_rate, ?hm_rate, ?unit_status, ?createby_id, now())';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?type_code", $this->TypeCode,"varchar");
        $this->connector->AddParameter("?class_code", $this->ClassCode,"varchar");
        $this->connector->AddParameter("?prod_year", $this->ProdYear,"varchar");
        $this->connector->AddParameter("?unit_code", $this->UnitCode,"varchar");
        $this->connector->AddParameter("?unit_name", $this->UnitName);
        $this->connector->AddParameter("?unit_model", $this->UnitModel,"varchar");
        $this->connector->AddParameter("?brand_code", $this->BrandCode,"varchar");
        $this->connector->AddParameter("?no_mesin", $this->NoMesin,"varchar");
        $this->connector->AddParameter("?no_pol", $this->NoPol,"varchar");
        $this->connector->AddParameter("?no_chasis", $this->NoChasis,"varchar");
        $this->connector->AddParameter("?km_position", $this->KmPosition);
        $this->connector->AddParameter("?hm_position", $this->HmPosition);
        $this->connector->AddParameter("?km_rate", $this->KmRate);
        $this->connector->AddParameter("?hm_rate", $this->HmRate);
        $this->connector->AddParameter("?sn_no", $this->SnNo,"varchar");
        $this->connector->AddParameter("?unit_status", $this->UnitStatus);
        $this->connector->AddParameter("?asset_id", $this->AssetId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_units SET
	entity_id = ?entity_id,
	type_code = ?type_code,
	unit_code = ?unit_code,
	unit_name = ?unit_name,
	unit_model = ?unit_model,
	brand_code = ?brand_code,
	unit_status = ?unit_status,
	no_mesin = ?no_mesin,
	no_pol = ?no_pol,
	km_position = ?km_position,
	hm_position = ?hm_position,
	km_rate = ?km_rate,
	hm_rate = ?hm_rate,
	updateby_id = ?updateby_id,
	class_code = ?class_code,
	prod_year = ?prod_year,
	sn_no = ?sn_no,
	asset_id = ?asset_id
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?type_code", $this->TypeCode,"varchar");
        $this->connector->AddParameter("?class_code", $this->ClassCode,"varchar");
        $this->connector->AddParameter("?prod_year", $this->ProdYear,"varchar");
        $this->connector->AddParameter("?unit_code", $this->UnitCode,"varchar");
        $this->connector->AddParameter("?unit_name", $this->UnitName);
        $this->connector->AddParameter("?unit_model", $this->UnitModel,"varchar");
        $this->connector->AddParameter("?brand_code", $this->BrandCode,"varchar");
        $this->connector->AddParameter("?no_mesin", $this->NoMesin,"varchar");
        $this->connector->AddParameter("?no_pol", $this->NoPol,"varchar");
        $this->connector->AddParameter("?no_chasis", $this->NoChasis,"varchar");
        $this->connector->AddParameter("?km_position", $this->KmPosition);
        $this->connector->AddParameter("?hm_position", $this->HmPosition);
        $this->connector->AddParameter("?km_rate", $this->KmRate);
        $this->connector->AddParameter("?hm_rate", $this->HmRate);
        $this->connector->AddParameter("?sn_no", $this->SnNo,"varchar");
        $this->connector->AddParameter("?unit_status", $this->UnitStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?asset_id", $this->AssetId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
		return $rs;
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Update cm_units a Set a.is_deleted = 1,updateby_id = ?updateby_id,update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}
