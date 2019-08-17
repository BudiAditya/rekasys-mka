<?php

namespace Asset;

/**
 * Class DepreciationJournal
 * @package Asset
 *
 * Ini berfungsi untuk menyimpan data jurnal voucher yang akan dibentuk...
 * Depresiasi asset akan dijurnal secara rekap (TOTAL DEPRESIASI PER AKUN) daripada posting per assetnya
 * Model ini berfungsi untuk menyimpan data sementara sebelum diposting ke table ac_voucher_master
 */
class DepreciationJournal extends \EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $EntityId;
	public $StatusCode = 1;
	public $AssetCategoryId;
	public $DepreciationDate;
	public $TotalDepreciation;
	public $VoucherId;
	public $ProjectId;
	public $DeptId;
	public $ActivityId;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->EntityId = $row["entity_id"];
		$this->AssetCategoryId = $row["asset_category_id"];
		$this->DepreciationDate = strtotime($row["depreciation_date"]);
		$this->TotalDepreciation = $row["total_depreciation"];
		$this->VoucherId = $row["voucher_id"];
		$this->StatusCode = $row["status"];
        $this->ProjectId = $row["project_id"];
        $this->DeptId = $row["dept_id"];
        $this->ActivityId = $row["activity_id"];
	}

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FormatDepreciationDate($format = HUMAN_DATE) {
		return is_int($this->DepreciationDate) ? date($format, $this->DepreciationDate) : null;
	}

	public function GetStatus() {
		if ($this->StatusCode == null) {
			return null;
		}

		switch ($this->StatusCode) {
			case 1:
				return "DRAFT";
			case 2:
				return "POSTED";
			default:
				return "N.A.";
		}
	}

	/**
	 * Untuk mencari total depresiasi asset per kategori dan tanggal
	 * NOTE: Untuk saat ini tanggal disini akan selalu sama dengan tanggal proses depresiasi (akhir bulan berjalan jam 23:59:59)
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function CalculateTotalDepreciation() {
		if ($this->AssetCategoryId == null) {
			throw new \Exception("InvalidArgumentException ! AssetCategoryId is null. This property is required to calculate total deprecation");
		}
		if (!is_int($this->DepreciationDate)) {
			throw new \Exception("InvalidArgumentException ! DepreciationDate is null. This property is required to calculate total deprecation");
		}

		$this->connector->CommandText =
"SELECT SUM(a.amount) AS total_amount
FROM ac_asset_depreciation AS a
	JOIN ac_asset_master AS b ON a.asset_id = b.id
WHERE a.is_deleted = 0 AND a.depreciation_date = ?date AND b.category_id = ?category";
		$this->connector->AddParameter("?date", $this->FormatDepreciationDate(SQL_DATEONLY));
		$this->connector->AddParameter("?category", $this->AssetCategoryId);

		$this->TotalDepreciation = $this->connector->ExecuteScalar();
		if ($this->TotalDepreciation == null) {
			$this->TotalDepreciation = 0;
		}

		return $this->TotalDepreciation;
	}

	/**
	 * @param int $id
	 * @return DepreciationJournal
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ac_depreciation_journal AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_depreciation_journal(project_id,dept_id,activity_id,createby_id, create_time, entity_id, status, asset_category_id, depreciation_date, total_depreciation)
VALUES(?project_id,?dept_id,?activity_id,?user, NOW(), ?sbu, ?status, ?category, ?date, ?total)";

		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?category", $this->AssetCategoryId);
		$this->connector->AddParameter("?date", $this->FormatDepreciationDate(SQL_DATETIME));
		$this->connector->AddParameter("?total", $this->TotalDepreciation);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?activity_id", $this->ActivityId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_depreciation_journal SET
	updateby_id = ?user,
	update_time = NOW(),
	asset_category_id = ?category,
	depreciation_date = ?date,
	total_depreciation = ?total,
	project_id = ?project_id,
	dept_id = ?dept_id,
	activity_id = ?activity_id
WHERE id = ?id";

		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?category", $this->AssetCategoryId);
		$this->connector->AddParameter("?date", $this->FormatDepreciationDate(SQL_DATETIME));
		$this->connector->AddParameter("?total", $this->TotalDepreciation);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?activity_id", $this->ActivityId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "Delete From ac_depreciation_journal WHERE `status` = 1 And id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Post($id, $voucherId) {
		$this->connector->CommandText =
"UPDATE ac_depreciation_journal SET
	updateby_id = ?user,
	update_time = NOW(),
	status = 2,
	voucher_id = ?voucher
WHERE id = ?id AND status = 1";

		$this->connector->AddParameter("?voucher", $voucherId);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function UnPost($id) {
		$this->connector->CommandText =
"UPDATE ac_depreciation_journal SET
	updateby_id = ?user,
	update_time = NOW(),
	status = 1,
	voucher_id = NULL
WHERE id = ?id AND status = 2";

		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// EoF: depreciation_journal.php