<?php

require_once("stock_opname_detail.php");
class StockOpname extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $DocumentNo;
	public $Date;
	public $WarehouseId;
	public $StatusCode;
	public $Note;
	public $CreatedDate;
	public $CreatedById;
	public $UpdatedDate;
	public $UpdatedById;
	public $ApprovedDate;
	public $ApprovedById;

	/** @var StockOpnameDetail[] */
	public $Details = array();

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->DocumentNo = $row["doc_no"];
		$this->Date = strtotime($row["so_date"]);
		$this->WarehouseId = $row["warehouse_id"];
		$this->StatusCode = $row["status"];
		$this->Note = $row["note"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->CreatedById = $row["createby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->ApprovedById = $row["approveby_id"];
	}

	public function GetStatus() {
		if ($this->StatusCode == null) {
			return null;
		}

		switch ($this->StatusCode) {
			case 1:
				return "DRAFT";
			case 2:
				return "ACKNOWLEDGED";
			case 3:
				return "APPROVED";
			case 4:
				return "APPROVED GM";
			default:
				return "N.A.";
		}
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	public function LoadDetails($orderBy = "b.item_code") {
		if ($this->Id == null) {
			return $this->Details;
		}

		$detail = new StockOpnameDetail();
		$this->Details = $detail->LoadByStockOpnameId($this->Id, $orderBy);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return StockOpname
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*
FROM ic_so_master AS a
WHERE a.id = ?id";
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
"INSERT INTO ic_so_master(entity_id, doc_no, so_date, warehouse_id, status, note, create_time, createby_id)
VALUES(?sbu, ?docNo, ?date, ?warehouse, ?status, ?note, NOW(), ?user)";

		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?warehouse", $this->WarehouseId);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?user", $this->CreatedById);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_so_master SET
	doc_no = ?docNo
	, so_date = ?date
	, warehouse_id = ?warehouse
	, note = ?note
	, update_time = NOW()
	, updateby_id = ?user
WHERE id = ?id";

		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?warehouse", $this->WarehouseId);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ic_so_master SET
	is_deleted = 1
	, update_time = NOW()
	, updateby_id = ?user
WHERE id = ?id";

		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteQuery();
	}

	public function Approve($id) {
		$this->connector->CommandText =
"UPDATE ic_so_master SET
	status = 3
	, approveby_id = ?user
	, approve_time = NOW()
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->ApprovedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id) {
		$this->connector->CommandText =
"UPDATE ic_so_master SET
	status = 1
	, approveby_id = NULL
	, approve_time = NULL
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function SetStatus($id) {
		$this->connector->CommandText =
"UPDATE ic_so_master SET
	status = ?status
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}


// End of File: stock_opname.php
