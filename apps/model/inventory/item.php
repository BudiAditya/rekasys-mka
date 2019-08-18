<?php

class Item extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $UpdatedUserId;
	public $UpdatedDate;
	public $EntityId;
	public $ItemCode;
	public $ItemName;
	public $CategoryId = 0;
	public $AssetCategoryId = 0;
	public $PartNo;
	public $Barcode;
	public $Note;
	public $MaxQty = 0;
	public $MinQty = 0;
	public $UomCode;
	public $IsDiscontinued = false;
	public $UnitTypeCode;
	public $UnitBrandCode;
    public $UnitCompCode;
	public $SnNo;
	public $IcxCode;
	public $Qclass = 0;
    public $LUomCode;
    public $UomConversion = 1;
    public $OtherItemCode;
    public $OtherItemName;
    public $AddNotes;

    public $UnitTypeName;
    public $UnitBrandName;
    public $CompName;

	// Helper variable
    public $ProjectId;
	public $StockLocationId = 0;
	public $CategoryIsStock;

	public function FillProperties(array $row, $haveExtendedData = false) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->UpdatedUserId = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->EntityId = $row["entity_id"];
		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->CategoryId = $row["category_id"];
		$this->AssetCategoryId = $row["asset_category_id"];
		$this->UomCode = $row["uom_cd"];
		$this->PartNo = $row["part_no"];
		$this->Barcode = $row["barcode"];
		$this->MaxQty = $row["max_qty"];
		$this->MinQty = $row["min_qty"];
		$this->IsDiscontinued = $row["is_discontinue"] == 1;
		$this->Note = $row["notes"];
        $this->UnitTypeCode = $row["unit_type_code"];
        $this->UnitBrandCode = $row["unit_brand_code"];
        $this->UnitCompCode = $row["unit_comp_code"];
        $this->SnNo = $row["sn_no"];
        $this->UnitBrandName = $row["brand_name"];
        $this->UnitTypeName = $row["type_desc"];
        $this->CompName = $row["comp_name"];
        $this->IcxCode = $row["icx_code"];
        $this->Qclass = $row["qclass"];
        $this->LUomCode = $row["l_uom_cd"];
        $this->UomConversion = $row["uom_conversion"];
        $this->OtherItemCode = $row["other_item_code"];
        $this->OtherItemName = $row["other_item_name"];
        $this->AddNotes = $row["add_notes"];
	}

	/**
	 * @param int $id
	 * @param bool $loadExtendedData
	 * @return Item
	 */
	public function LoadById($id) {
		$query = "SELECT a.* FROM vw_ic_item_master AS a WHERE a.id = ?id";
		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * @param int $entityId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Item[]
	 */
	public function LoadByEntityId($entityId, $orderBy = "a.item_code") {
		$query = "SELECT a.* FROM vw_ic_item_master AS a WHERE a.entity_id = ?sbu ORDER BY %s";
		$this->connector->CommandText = sprintf($query, $orderBy);
		$this->connector->AddParameter("?sbu", $entityId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Item();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByQclass($entityId, $qClass = 0, $orderBy = "a.item_code") {
        $query = "SELECT a.* FROM vw_ic_item_master AS a WHERE a.entity_id = ?sbu And a.qclass = ?qcls ORDER BY %s";
        $this->connector->CommandText = sprintf($query, $orderBy);
        $this->connector->AddParameter("?sbu", $entityId);
        $this->connector->AddParameter("?qcls", $qClass);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Item();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $id
	 * @return Item
	 */
    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*, b.entity_cd FROM vw_ic_item_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);

        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }

        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function FindByCode($entityId,$itemCode) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd FROM vw_ic_item_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.entity_id = ?entityId And a.item_code = ?itemCode";
		$this->connector->AddParameter("?entityId", $entityId);
        $this->connector->AddParameter("?itemCode", $itemCode);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_item_master(other_item_code,other_item_name,add_notes,l_uom_cd,uom_conversion,icx_code,qclass,unit_comp_code,sn_no,unit_brand_code,is_discontinue,unit_type_code,entity_id, item_code, item_name, category_id, uom_cd, part_no, barcode, max_qty, min_qty, notes, updateby_id, update_time, asset_category_id)
VALUES(?other_item_code,?other_item_name,?add_notes,?l_uom_cd,?uom_conversion,?icx_code,?qclass,?unit_comp_code,?sn_no,?unit_brand_code,?is_discontinue,?unit_type_code,?sbu, ?code, ?name, ?category, ?uom, ?partNo, ?barcode, ?maxQty, ?minQty, ?notes, ?user, NOW(), ?assetCategory)";

		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?code", $this->ItemCode, "varchar");
		$this->connector->AddParameter("?name", $this->ItemName, "varchar");
		$this->connector->AddParameter("?category", $this->CategoryId);
		$this->connector->AddParameter("?uom", $this->UomCode);
		$this->connector->AddParameter("?partNo", $this->PartNo, "varchar");
		$this->connector->AddParameter("?barcode", $this->Barcode, "varchar");
		$this->connector->AddParameter("?maxQty", $this->MaxQty);
		$this->connector->AddParameter("?minQty", $this->MinQty);
		$this->connector->AddParameter("?notes", $this->Note);
        $this->connector->AddParameter("?unit_type_code", $this->UnitTypeCode, "varchar");
        $this->connector->AddParameter("?unit_brand_code", $this->UnitBrandCode, "varchar");
        $this->connector->AddParameter("?unit_comp_code", $this->UnitCompCode, "varchar");
        $this->connector->AddParameter("?sn_no", $this->SnNo, "varchar");
        $this->connector->AddParameter("?is_discontinue", $this->IsDiscontinued);
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?assetCategory", $this->AssetCategoryId);
        $this->connector->AddParameter("?icx_code", $this->IcxCode, "varchar");
        $this->connector->AddParameter("?qclass", $this->Qclass);
        $this->connector->AddParameter("?l_uom_cd", $this->LUomCode);
        $this->connector->AddParameter("?uom_conversion", $this->UomConversion);
        $this->connector->AddParameter("?other_item_code", $this->OtherItemCode);
        $this->connector->AddParameter("?other_item_name", $this->OtherItemName);
        $this->connector->AddParameter("?add_notes", $this->AddNotes);
		$result = $this->connector->ExecuteNonQuery();
		if ($result == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
			$this->UpdateStockLocation();
		}
		return $result;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_item_master SET
	entity_id = ?sbu
	, item_code = ?code
	, item_name = ?name
	, category_id = ?category
	, uom_cd = ?uom
	, part_no = ?partNo
	, barcode = ?barcode
	, max_qty = ?maxQty
	, min_qty = ?minQty
	, notes = ?notes
	, updateby_id = ?user
	, asset_category_id  = ?assetCategory
	, unit_type_code = ?unit_type_code
	, is_discontinue = ?is_discontinue
	, unit_brand_code = ?unit_brand_code
	, unit_comp_code = ?unit_comp_code
	, sn_no = ?sn_no
	, icx_code = ?icx_code
	, qclass = ?qclass
	, l_uom_cd = ?l_uom_cd
	, uom_conversion = ?uom_conversion
	, other_item_code = ?other_item_code
	, other_item_name = ?other_item_name
	, add_notes = ?add_notes
WHERE id = ?id";

		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?code", $this->ItemCode, "varchar");
		$this->connector->AddParameter("?name", $this->ItemName, "varchar");
		$this->connector->AddParameter("?category", $this->CategoryId);
		$this->connector->AddParameter("?uom", $this->UomCode);
		$this->connector->AddParameter("?partNo", $this->PartNo, "varchar");
		$this->connector->AddParameter("?barcode", $this->Barcode, "varchar");
		$this->connector->AddParameter("?maxQty", $this->MaxQty);
		$this->connector->AddParameter("?minQty", $this->MinQty);
		$this->connector->AddParameter("?notes", $this->Note);
        $this->connector->AddParameter("?unit_type_code", $this->UnitTypeCode, "varchar");
        $this->connector->AddParameter("?is_discontinue", $this->IsDiscontinued);
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?assetCategory", $this->AssetCategoryId);
        $this->connector->AddParameter("?unit_brand_code", $this->UnitBrandCode, "varchar");
        $this->connector->AddParameter("?unit_comp_code", $this->UnitCompCode, "varchar");
        $this->connector->AddParameter("?sn_no", $this->SnNo, "varchar");
        $this->connector->AddParameter("?icx_code", $this->IcxCode, "varchar");
        $this->connector->AddParameter("?qclass", $this->Qclass);
        $this->connector->AddParameter("?l_uom_cd", $this->LUomCode);
        $this->connector->AddParameter("?uom_conversion", $this->UomConversion);
        $this->connector->AddParameter("?other_item_code", $this->OtherItemCode);
        $this->connector->AddParameter("?other_item_name", $this->OtherItemName);
        $this->connector->AddParameter("?add_notes", $this->AddNotes);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
		$this->UpdateStockLocation();
        return $rs;
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE ic_item_master SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function GetAutoStockCode() {
        $this->connector->CommandText = "Select fcIcGenerateStockCode(?entityId,?brandCode,?typeCode) AS stock_code;";
        $this->connector->AddParameter("?entityId", $this->EntityId);
        $this->connector->AddParameter("?brandCode", $this->UnitBrandCode,"varchar");
        $this->connector->AddParameter("?typeCode", $this->UnitTypeCode,"varchar");
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["stock_code"]);
    }

    public function GetJSonItems($entityId = 0, $filter = null,$sort = 'a.item_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.item_code,a.part_no,a.item_name,a.uom_cd,a.sn_no,a.brand_name,a.type_desc FROM vw_ic_item_master as a Where a.is_deleted = 0";
        if ($entityId > 0){
            $sql.= " and a.entity_id = $entityId";
        }
        if ($filter != null){
            $sql.= " And (a.item_code Like '%$filter%' Or a.part_no Like '%$filter%' Or a.item_name Like '%$filter%')";
        }
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function UpdateStockLocation(){
        $itemId = $this->Id;
        $locationId = $this->StockLocationId;
        $projectId = $this->ProjectId;
        $this->connector->CommandText = "Delete From ic_item_location WHERE item_id = $itemId And project_id = $projectId";
        $rs = $this->connector->ExecuteNonQuery();
        if ($itemId > 0 && $locationId > 0) {
            $this->connector->CommandText = "Insert Into ic_item_location (project_id, item_id, location_id) Values ($projectId,$itemId,$locationId)";
            $rs = $this->connector->ExecuteNonQuery();
        }
        return $rs;
    }
}


// End of File: item.php
