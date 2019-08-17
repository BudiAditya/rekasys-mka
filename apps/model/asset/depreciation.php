<?php

/**
 * Depresiasi asset/aktiva yang sudah terdaftar.
 * NOTE: Untuk tanggal depresiasi selalu menggunakan tanggal terakhir pada bulan depresiasi.
 *       Misal: Tanggal beli asset/aktiva adalah tgl 3 Mei 2012 (< tgl 7 maka akan lsg di depresiasi)
 *              Depresiasi Mei 2012 akan di input tanggal 2012-05-31 (31 Mei 2012) --> Depresiasi bulan mei
 *              Depresiasi Juni 2012 akan di input tanggal 2012-06-30 (30 Juni 2012) --> Depresiasi bulan juni
 *
 * NOTE: Jika asset dibeli pada tanggal <= 7 maka asset tersebut akan lsg di depresiasi pada bulan berjalan. Jika tidak maka akan di depresiasi bulan berikutnya.
 */
class Depreciation extends EntityBase {
	public $Id;
	public $IsDeleted;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $AssetId;
	public $DepreciationDate;
	public $BookValue = 0;
    public $Amount = 0;
	public $MethodCode;
	public $Percentage;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->AssetId = $row["asset_id"];
		$this->DepreciationDate = strtotime($row["depreciation_date"]);
		$this->BookValue = $row["book_value"];
        $this->Amount = $row["amount"];
		$this->MethodCode = $row["method"];
		$this->Percentage = $row["percentage"];
	}

	public function FormatDepreciationDate($format = HUMAN_DATE) {
		return is_int($this->DepreciationDate) ? date($format, $this->DepreciationDate) : null;
	}

	public function GetDepreciationMethod() {
		if ($this->MethodCode == null) {
			return null;
		}
		switch ($this->MethodCode) {
			case 1:
				return "STRAIGHT LINE";
			case 2:
				return "DOUBLE DECLINING";
			default:
				throw new Exception("Unknown MethodCode ! Given Code: " . $this->MethodCode);
		}
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ac_asset_depreciation AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * Untuk mencari data penyusutan specific per asset dan periode.
	 *
	 * @param $assetId
	 * @param $year
	 * @param $month
	 * @return Depreciation|null
	 */
	public function LoadByAssetAndDate($assetId, $year, $month) {
		$this->connector->CommandText =
"SELECT a.*
FROM ac_asset_depreciation AS a
WHERE a.is_deleted = 0 AND a.asset_id = ?id AND YEAR(a.depreciation_date) = ?year AND MONTH(a.depreciation_date) = ?month";
		$this->connector->AddParameter("?id", $assetId);
		$this->connector->AddParameter("?year", $year);
		$this->connector->AddParameter("?month", $month);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadHistoriesByAssetId($assetId) {
		$this->connector->CommandText = "SELECT a.* FROM ac_asset_depreciation AS a WHERE a.is_deleted = 0 AND a.asset_id = ?assetId ORDER BY a.depreciation_date";
		$this->connector->AddParameter("?assetId", $assetId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Depreciation();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadDepreciationByPeriod($entityId,$categoryId = 0,$depMonth,$depYear) {
	    $sql = "Select c.`code` as category_code, b.asset_code,b.asset_name,a.depreciation_date,a.amount From ac_asset_depreciation AS a Join ac_asset_master AS b On a.asset_id = b.id Join ac_asset_category AS c On b.category_id = c.id";
	    $sql.= " WHERE b.entity_id = ?entityId And a.is_deleted = 0 AND Year(a.depreciation_date) = ?depYear AND Month(a.depreciation_date) = ?depMonth";
	    if ($categoryId > 0){
	        $sql.= " And b.category_id = ?categoryId";
        }
        $sql.= " Order By c.code,b.asset_code,b.asset_name";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?entityId", $entityId);
        $this->connector->AddParameter("?depYear", $depYear);
        $this->connector->AddParameter("?depMonth", $depMonth);
        $this->connector->AddParameter("?categoryId", $categoryId);
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ac_asset_depreciation(createby_id, create_time, asset_id, depreciation_date, amount, method, percentage, book_value) VALUES(?user, NOW(), ?asset, ?date, ?amount, ?method, ?percentage, ?bvalue)";
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?asset", $this->AssetId);
		$this->connector->AddParameter("?date", $this->FormatDepreciationDate(SQL_DATEONLY));
		$this->connector->AddParameter("?amount", $this->Amount);
		$this->connector->AddParameter("?method", $this->MethodCode);
		$this->connector->AddParameter("?percentage", $this->Percentage);
        $this->connector->AddParameter("?bvalue", $this->BookValue);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText = "UPDATE ac_asset_depreciation SET updateby_id = ?user
	, update_time = NOW()
	, asset_id = ?asset
	, depreciation_date = ?date
	, amount = ?amount
	, method = ?method
	, book_value = ?bvalue
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?asset", $this->AssetId);
		$this->connector->AddParameter("?date", $this->FormatDepreciationDate(SQL_DATETIME));
		$this->connector->AddParameter("?amount", $this->Amount);
		$this->connector->AddParameter("?method", $this->MethodCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?bvalue", $this->BookValue);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE ac_asset_depreciation SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: depreciation.php
