<?php

class StockOpnameDetail extends EntityBase {
	public $Id;
	public $StockOpnameId;
	public $SequenceNo;
	public $ItemId;
	public $UomCd;
	public $QtySo;
	public $QtyComputed;
	public $Price = 0;			// Harga unit jika ada. Untuk kasus qty SO > qty Computed (stock lapangan lebih bny drpd komputer)
	public $TotalCost;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->StockOpnameId = $row["so_master_id"];
		$this->SequenceNo = $row["seq_no"];
		$this->ItemId = $row["item_id"];
		$this->UomCd = $row["uom_cd"];
		$this->QtySo = $row["qty_so"];
		$this->QtyComputed = $row["qty_computed"];
		$this->Price = $row["price"];
		$this->TotalCost = $row["total_cost"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
	}

	/**
	 * @param int $id
	 * @return StockOpnameDetail
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.item_code, b.item_name
FROM ic_so_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * @param int $soId
	 * @param string $orderBy
	 * @return StockOpnameDetail[]
	 */
	public function LoadByStockOpnameId($soId, $orderBy = "b.item_code") {
		$this->connector->CommandText =
"SELECT a.*, b.item_code, b.item_name
FROM ic_so_detail AS a
	JOIN ic_item_master AS b ON a.item_id = b.id
WHERE a.so_master_id = ?id
ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $soId);

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new StockOpnameDetail();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_so_detail(so_master_id, item_id, uom_cd, qty_so, qty_computed, price)
VALUES(?soId, ?item, ?uom, ?qtySo, ?qtyComputed, ?price)";

		$this->connector->AddParameter("?soId", $this->StockOpnameId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?uom", $this->UomCd);
		$this->connector->AddParameter("?qtySo", $this->QtySo);
		$this->connector->AddParameter("?qtyComputed", $this->QtyComputed);
		$this->connector->AddParameter("?price", $this->Price);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_so_detail SET
	so_master_id = ?soId
	, item_id = ?item
	, uom_cd = ?uom
	, qty_so = ?qtySo
	, qty_computed = ?qtyComputed
	, price = ?price
WHERE id = ?id";

		$this->connector->AddParameter("?soId", $this->StockOpnameId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?uom", $this->UomCd);
		$this->connector->AddParameter("?qtySo", $this->QtySo);
		$this->connector->AddParameter("?qtyComputed", $this->QtyComputed);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_so_detail WHERE id = ?id";

		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteQuery();
	}

	public function UpdateCost($id) {
		$this->connector->CommandText =
"UPDATE ic_so_detail SET
	total_cost = ?cost
WHERE id = ?id";
		$this->connector->AddParameter("?cost", $this->TotalCost);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: stock_opname_detail.php
