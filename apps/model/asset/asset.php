<?php

/**
 * Class ini akan mengatur mengenai asset/aktiva masing-masing SBU
 * NOTE: Akan berhubungan dengan depresiasi asset (refer to depreciation.php)
 */
class Asset extends EntityBase {
	public $Id;
	public $IsDeleted;
	public $UpdatedUserId;
	public $UpdatedDate;
	public $EntityId;
	public $ItemId;
    public $UnitId;
    public $CategoryId;
	public $AssetCode;
	public $AssetName;
	public $PurchaseDate;
	public $Price = 0;
    public $Qty = 0;
	public $GnDetailId = null;	// Jika asset ini datang dari GN maka akan ada isinya jika tidak selalu NULL
    public $DepCount = 0;
    public $DepAccumulate = 0;
    public $LastDep;
    public $InitDepAccumulate = 0;
    public $InitLastDep;

	public $EntityCd;
	public $CategoryCode;
	public $CategoryName;
	public $ItemCode;
	public $ItemName;
	public $UnitCode;
	public $UnitName;
	// Helper
	public $TotalDepreciation = 0;
    public $BookValue = 0;
    public $DeprAmount = 0;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->UpdatedUserId = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->EntityId = $row["entity_id"];
		$this->ItemId = $row["item_id"];
        $this->UnitId = $row["unit_id"];
        $this->CategoryId = $row["category_id"];
		$this->AssetCode = $row["asset_code"];
		$this->AssetName = $row["asset_name"];
		$this->PurchaseDate = strtotime($row["purchase_date"]);
		$this->Price = $row["price"];
        $this->Qty = $row["qty"];
		$this->GnDetailId = $row["gn_detail_id"];
		$this->DepCount = $row["dep_count"];
        $this->DepAccumulate = $row["dep_accumulate"];
        $this->LastDep = strtotime($row["last_dep"]);
        $this->InitDepAccumulate = $row["init_dep_accumulate"];
        $this->InitLastDep = strtotime($row["init_last_dep"]);

		$this->EntityCd = $row["entity_cd"];
		$this->CategoryCode = $row["category_code"];
        $this->CategoryName = $row["category_name"];
        $this->ItemCode = $row["item_code"];
        $this->ItemName = $row["item_name"];
        $this->UnitCode = $row["unit_code"];
        $this->UnitName = $row["unit_name"];
	}

	public function FormatPurchaseDate($format = HUMAN_DATE) {
		return is_int($this->PurchaseDate) ? date($format, $this->PurchaseDate) : null;
	}

    public function FormatLastDep($format = HUMAN_DATE) {
        return is_int($this->LastDep) ? date($format, $this->LastDep) : null;
    }

    public function FormatInitLastDep($format = HUMAN_DATE) {
        return is_int($this->InitLastDep) ? date($format, $this->InitLastDep) : null;
    }

	/**
	 * @param int $id
	 * @return Asset
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ac_asset_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCode($eti,$code) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ac_asset_master AS a WHERE a.entity_id = ?eti And a.asset_code = ?code";
        $this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?code", $code);
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
	 * @return Asset[]
	 */
	public function LoadByEntity($entityId, $orderBy = "a.asset_code") {
		$query = "SELECT a.* FROM vw_ac_asset_master AS a WHERE a.entity_id = ?sbu";
		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?sbu", $entityId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Asset();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCategory($entityId,$categoryId = 0,$orderBy = "a.category_code, a.asset_code") {
        $query = "SELECT a.* FROM vw_ac_asset_master AS a WHERE a.entity_id = ?sbu";
        if ($categoryId > 0){
            $query.= " And category_id = ".$categoryId;
        }
        $this->connector->CommandText = $query;
        $this->connector->AddParameter("?sbu", $entityId);
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_asset_master(init_dep_accumulate,init_last_dep,dep_count,dep_accumulate,last_dep,qty,category_id,unit_id,updateby_id, update_time, entity_id, item_id, asset_code, asset_name, purchase_date, price, gn_detail_id)
VALUES(?init_dep_accumulate,?init_last_dep,?dep_count,?dep_accumulate,?last_dep,?qty,?category_id,?unit_id,?user, NOW(), ?sbu, ?item, ?asset_code, ?desc, ?date, ?price, ?gnDetId)";
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?item", $this->ItemId);
        $this->connector->AddParameter("?asset_code", $this->AssetCode,"varchar");
		$this->connector->AddParameter("?desc", $this->AssetName);
		$this->connector->AddParameter("?date", $this->PurchaseDate);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?gnDetId", $this->GnDetailId);
        $this->connector->AddParameter("?category_id", $this->CategoryId);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
        $this->connector->AddParameter("?dep_count", $this->DepCount);
        $this->connector->AddParameter("?dep_accumulate", $this->DepAccumulate);
        $this->connector->AddParameter("?last_dep", $this->LastDep);
        $this->connector->AddParameter("?init_dep_accumulate", $this->DepAccumulate);
        $this->connector->AddParameter("?init_last_dep", $this->LastDep);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}
		return $rs;
	}

	/**
	 * NOTICE: untuk gn_detail_id tidak dapat di ganggu gugat karena ini link dari GN !
	 *
	 * @param int $id
	 * @return int
	 */
	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_asset_master SET
	updateby_id = ?user
	, update_time = NOW()
	, entity_id = ?sbu
	, item_id = ?item
	, asset_code = ?asset_code
	, asset_name = ?desc
	, purchase_date = ?date
	, price = ?price
	, category_id = ?category_id
	, unit_id = ?unit_id
	, qty = ?qty
	, dep_count = ?dep_count
	, dep_accumulate = ?dep_accumulate
	, last_dep = ?last_dep
	, init_dep_accumulate = ?init_dep_accumulate
	, init_last_dep = ?init_last_dep
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?asset_code", $this->AssetCode,"varchar");
		$this->connector->AddParameter("?desc", $this->AssetName);
		$this->connector->AddParameter("?date", $this->PurchaseDate);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?category_id", $this->CategoryId);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
        $this->connector->AddParameter("?dep_count", $this->DepCount);
        $this->connector->AddParameter("?dep_accumulate", $this->DepAccumulate);
        $this->connector->AddParameter("?last_dep", $this->LastDep);
        $this->connector->AddParameter("?init_dep_accumulate", $this->InitDepAccumulate);
        $this->connector->AddParameter("?init_last_dep", $this->InitLastDep);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function UpdateDeprAccumulate($id) {
        $this->connector->CommandText = "UPDATE ac_asset_master SET updateby_id = ?user, update_time = NOW(), dep_accumulate = ?dep_accumulate, last_dep = ?last_dep WHERE id = ?id";
        $this->connector->AddParameter("?user", $this->UpdatedUserId);
        $this->connector->AddParameter("?dep_accumulate", $this->DepAccumulate);
        $this->connector->AddParameter("?last_dep", $this->LastDep);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

	public function Delete($id) {
		$this->connector->CommandText = "Delete From ac_asset_master WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedUserId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function CalculateDepreciation($year, $month, $styear, $stmonth) {
		if ($this->Id == null || $this->Price == null) {
			throw new Exception("Missing Required Data for CalculateDepreciation !");
		}

		if (!class_exists("AssetCategory")) {
			require_once(MODEL . "asset/asset_category.php");
		}
		if (!class_exists("Depreciation")) {
			require_once(MODEL . "asset/depreciation.php");
		}

		$assetCategory = new AssetCategory();
		$assetCategory->LoadById($this->CategoryId);

		// Jika asset sudah umur maka harus langsung dihabiskan depresiasinya
		$maxAssetAge = mktime(0, 0, 0, $this->FormatPurchaseDate("n"), $this->FormatPurchaseDate("j"), $this->FormatPurchaseDate("Y") + $assetCategory->MaxAge);
		$lhs = ($year * 100) + $month;			// Kode tahun bulan pembelian asset
		$rhs = (int)date("Ym", $maxAssetAge);	// Kode max umur asset

		$depreciation = new Depreciation();
		$depreciation->AssetId = $this->Id;
		// Untuk tanggal penyusutan selalu tanggal akhir bulan yang diproses... jadi kita bikin tanggal 1 bulan berikutnya kurangi 1 detik
		$depreciation->DepreciationDate = mktime(0, 0, 0, $month + 1, 1, $year);
		$depreciation->DepreciationDate--;
		$this->LastDep = $depreciation->FormatDepreciationDate(SQL_DATEONLY);
		$depreciation->MethodCode = $assetCategory->DepreciationMethodId;
		$depreciation->Percentage = $assetCategory->DepreciationPercentage;

		if ($lhs >= $rhs) {
			// OK sudah mencapai umur asset maka akan lsg dihabiskan.
			// Ex: Beli tanggal 1 Jan 2013 dan umur misalkan 1 Tahun maka pada penyusutan januari 2014 harus habis
			//     Pada kasus diatas maka $lhs = 201401 dan $rhs = 201401 juga
			$depreciation->Amount = ($this->Price * $this->Qty) - $this->TotalDepreciation;
		} else {
			// OK untuk menghitung kalau metode garis lurus sih aman... klo saldo menurun rada hese...
			switch ($assetCategory->DepreciationMethodId) {
				case 1:
					$depreciation->Amount = $this->GarisLurusMethod($assetCategory, $year, $month, $styear, $stmonth);
					break;
				case 2:
					$depreciation->Amount = $this->SaldoMenurunMethod($assetCategory, $year, $month, $styear, $stmonth);
                    $depreciation->BookValue = $this->BookValue;
					break;
				default:
					throw new Exception("Depreciation Method not Supported yet !");
			}

			// OK ada checking tambahan agar tidak masalah....
			// Jika terjadi jumlah depresiasi melebihi nilai pembelian maka kita harus pakai yang lebih kecil.
			$temp = ($this->Price * $this->Qty) - $this->TotalDepreciation;
			$depreciation->Amount = min($temp, $depreciation->Amount);
		}

		return $depreciation;
	}

	private function GarisLurusMethod(AssetCategory $category, $year, $month, $styear, $stmonth) {
		//return round(((($this->Price * $this->Qty) * ($category->DepreciationPercentage / 100)) / 12),2);	// Sebenarnya tidak perlu pake parenthesis karena levelnya sama semua tetapi biar lebih mudah dimegerti maka pake parenthesis
        $firstJanuary = mktime(0, 0, 0, $month, 1, $year);
        // Cari total depresiasi tahun sebelumnya
        $this->connector->CommandText = "SELECT SUM(a.amount) AS total FROM ac_asset_depreciation AS a WHERE a.asset_id = ?id AND is_deleted = 0 AND a.depreciation_date < ?date";
        $this->connector->AddParameter("?id", $this->Id);
        $this->connector->AddParameter("?date", date(SQL_DATEONLY, $firstJanuary));
        $totalPrevDep = $this->connector->ExecuteScalar();
        $nilaiBuku = round(($this->Price * $this->Qty) - $totalPrevDep,2);
        $this->BookValue = $nilaiBuku;
        $deprAmount = round((($this->Price * $this->Qty)/ $category->MaxAge)/12,2);
        $this->DeprAmount = $deprAmount;
        $this->DepAccumulate = $totalPrevDep + $deprAmount;
        $this->UpdateDeprAccumulate($this->Id);
        return $this->DeprAmount;
	}

	private function SaldoMenurunMethod(AssetCategory $category, $year, $month, $styear, $stmonth) {
		// Khusus yang ini maka kita hitungnya dari nilai buku per tahunnya
		$firstJanuary = mktime(0, 0, 0, $month, 1, $year);
		// Cari total depresiasi tahun sebelumnya
		$this->connector->CommandText = "SELECT SUM(a.amount) AS total FROM ac_asset_depreciation AS a WHERE a.asset_id = ?id AND is_deleted = 0 AND a.depreciation_date < ?date";
		$this->connector->AddParameter("?id", $this->Id);
		$this->connector->AddParameter("?date", date(SQL_DATEONLY, $firstJanuary));
		$totalPrevDep = $this->connector->ExecuteScalar();

        //$ldYear = (int) $this->FormatLastDep("Y");
        //$ldMonth = (int) $this->FormatLastDep("n");
        //if ($year == $ldYear && $month < $ldMonth) {

		$nilaiBuku = round(($this->Price * $this->Qty) - $totalPrevDep,2);

		// Rounding pada PHP .5 akan di rounding ke atas. Ex: 10.995 akan menjadi 11, 10.945 akan menjadi 10.95
        $this->BookValue = $nilaiBuku;
        $this->DeprAmount = round(($nilaiBuku * $category->DepreciationPercentage / 100) / 12, 2);
        //update data asset
        $this->DepAccumulate = $totalPrevDep + $this->DeprAmount;
        $this->UpdateDeprAccumulate($this->Id);
		return $this->DeprAmount;
	}

	public function CalculateTotalDepreciation($lowerBound) {
		$this->connector->CommandText = "SELECT SUM(a.amount) AS total_depreciation FROM ac_asset_depreciation AS a WHERE a.is_deleted = 0 AND a.asset_id = ?id AND a.depreciation_date < ?lowerBound";
		$this->connector->AddParameter("?id", $this->Id);
		$this->connector->AddParameter("?lowerBound", date(SQL_DATETIME, $lowerBound));
		$this->TotalDepreciation = $this->connector->ExecuteScalar();
		if ($this->TotalDepreciation == null) {
			$this->TotalDepreciation = 0;
		}

		return $this->TotalDepreciation;
	}
}


// End of File: asset.php
