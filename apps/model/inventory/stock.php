<?php
/**
 * Bertugas mengatur dan meng-query data mengenai stock barang yang ada di gudang.
 * Pada table stock akan berisi keluar masuk barang. Jika Qty bernilai (+) artinya barang masuk. Jika (-) maka barang keluar
 *
 * PENTING: Source dokumen dibedakan bedasarkan stock_type !
 * KODE YANG DIGUNAKAN:
 *  - Jika xxx / Dibawah 100 bearti barang masuk
 *  - Jika 1xx / Diatas 100 bearti barang keluar (TIDAK ADA KODE 100)
 *
 * PENTING: Reference Id akan berbeda-beda berdasarkan stock_type. Misal jika stock type GN maka reference ID adalah ID dari detail GN ! Jika type IS maka reference ID adalah ID detail IS dan stock ID akan berisi nilai
 *
 * PENTING: UseStockId hanya digunakan untuk barang keluar (tracking stock yang dikeluarkan) dan berisi NULL untuk stock masuk
 *
 * PENTING: QtyBalance hanya digunakan untuk barang masuk yang sudah dikeluarkan. Jika QtyBalance sudah 0 bearti semya barang sudah dikeluarkan !
 */
class Stock extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;

	public $StockTypeCode;
	public $ReferenceId;
	public $Date;
	public $WarehouseId = 0;
	public $ProjectId = 0;
	public $ItemId;
	public $Qty;
	public $UomCd;
	public $Price;
	public $UseStockId = null;
	public $QtyBalance = null;

	// Variable-variable dokumen referensi
	public $DocumentId = null;
	public $DocumentType = null;
	public $DocumentNo = null;
	public $DocumentDate = null;	// Harusnya untuk yang ini akan sama dengan Stock Date....

	// Special query
	const QUERY_STOCK_BY_SBU = "SELECT a.item_id, SUM(CASE WHEN a.stock_type < 100 THEN a.qty ELSE a.qty * -1 END) AS qty_stock, a.uom_cd FROM ic_stock AS a WHERE a.is_deleted = 0 AND a.date < ?date AND a.project_id IN (SELECT id FROM cm_project WHERE entity_id = ?sbu) GROUP BY a.item_id, a.uom_cd";

	const QUERY_STOCK_BY_WAREHOUSE = "SELECT a.item_id, SUM(CASE WHEN a.stock_type < 100 THEN a.qty ELSE a.qty * -1 END) AS qty_stock, a.uom_cd FROM ic_stock AS a WHERE a.is_deleted = 0 AND a.date < ?date AND a.project_id = ?projectId GROUP BY a.item_id, a.uom_cd";

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);

		$this->StockTypeCode = $row["stock_type"];
		$this->ReferenceId = $row["reference_id"];
		$this->Date = strtotime($row["date"]);
		$this->WarehouseId = $row["warehouse_id"];
        $this->ProjectId = $row["project_id"];
		$this->ItemId = $row["item_id"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->Price = $row["price"];
		$this->UseStockId = $row["use_stock_id"];
		$this->QtyBalance = $row["qty_balance"];
	}

	public function LoadReferencedDocument() {
		if ($this->Id == null || $this->StockTypeCode == null || $this->ReferenceId == null) {
			throw new Exception("DataNotComplete Exception ! Loading a referenced document must be loading required data first !");
		}

		// Disini kita akan mengambil data dokumen referensi.
		switch ($this->StockTypeCode) {
			case 1:
				$this->DocumentType = "OP";
				$this->connector->CommandText = "SELECT a.id, concat(lpad(a.project_id,2,0),'-',a.item_code) AS doc_no, a.opn_date AS doc_date FROM ic_opening_balance AS a WHERE a.id = ?refId";
				break;
			case 2:
                $this->DocumentType = "GN";
                $this->connector->CommandText = "SELECT a.id, a.doc_no, a.gn_date AS doc_date FROM ic_gn_master AS a WHERE a.id = (SELECT gn_master_id FROM ic_gn_detail WHERE id = ?refId)";
                break;
			case 102:
				$this->DocumentType = "SO";
				$this->connector->CommandText = "SELECT a.id, a.doc_no, a.so_date AS doc_date FROM ic_so_master AS a WHERE a.id = (SELECT so_master_id FROM ic_so_detail WHERE id = ?refId)";
			break;
			case 103:
				$this->DocumentType = "BS";
				$this->connector->CommandText = "SELECT a.id, a.doc_no, a.adjustment_date AS doc_date FROM ic_adjustment_master AS a WHERE a.id = (SELECT adjustment_master_id FROM ic_adjustment_detail WHERE id = ?refId)";
				break;
			case 101:
				$this->DocumentType = "IS";
				$this->connector->CommandText = "SELECT a.id, a.doc_no, a.issue_date AS doc_date FROM ic_is_master AS a WHERE a.id = (SELECT is_master_id FROM ic_is_detail WHERE id = ?refId)";
				break;
			default:
				throw new Exception("NotImplemented Exception ! StockTypeCode: " . $this->StockTypeCode . " is not yet implemented for acquiring referenced document ! Please contact system admin !");
		}

		$this->connector->AddParameter("?refId", $this->ReferenceId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			throw new Exception("Failed to retrieve referenced document data ! Stock Id: " . $this->Id);
		}
		$row = $rs->FetchAssoc();
		$this->DocumentId = $row["id"];
		$this->DocumentNo = $row["doc_no"];
		$this->DocumentDate = strtotime($row["doc_date"]);
	}

	public function GenerateReferenceLink() {
		if ($this->DocumentId == null) {
			throw new Exception("DataNotComplete Exception ! Generating Link require DocumentId to be loaded first !");
		}

		switch ($this->StockTypeCode) {
			case 1:
				return "inventory.icobal/view/" . $this->DocumentId;
			case 2:
                return "inventory.gn/view/" . $this->DocumentId;
			case 102:
				return "inventory.so/view/" . $this->DocumentId;
			case 103:
				return "inventory.adjustment/view/" . $this->DocumentId;
			case 101:
				return "inventory.is/view/" . $this->DocumentId;
			default:
				throw new Exception("NotImplemented Exception ! StockTypeCode: " . $this->StockTypeCode . " is not yet implemented for generate reference link ! Please contact system admin !");
		}
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	public function FormatDocumentDate($format = HUMAN_DATE) {
		return is_int($this->DocumentDate) ? date($format, $this->DocumentDate) : null;
	}

	/**
	 * Berfungsi untuk mencari stock barang secara FIFO berdasarkan gudang yang dipilih.
	 * Jika filter gudang bernilai null artinya semua gudang berdasarkan SBU
	 *
	 * @param $itemId			=> ID barang yang akan dicari... (INGAT Barang sudah specific per SBU)
	 * @param $uomCode			=> Satuan Barang
	 * @param null $projectId	=> Jika perlu filter gudang
	 * @return array
	 */
	public function LoadStocksFifo($itemId, $uomCode, $projectId = null) {
		if ($projectId == null) {
			// Walau pada query kita tidak memberikan filter gudang tetapi kita harus ingat bahwa barang sudah di lock per SBU
			// Jadi jika barang 'A' di lock pada SBU 'Mall' maka jika kita filter hanya berdasarkan barang A tidak mungkin muncul di gudang lain selain gudang Mall
			// Kecuali ada yang tembak data barang 'A' ke gudang SBU lain yang mana secara system tidak mungkin terjadi
			$query =
"SELECT a.*
FROM ic_stock AS a
WHERE a.is_deleted = 0 AND a.item_id = ?itemId AND uom_cd = ?uomCd AND qty_balance > 0
ORDER BY a.date ASC";
		} else {
			$query =
"SELECT a.*
FROM ic_stock AS a
WHERE a.is_deleted = 0 AND a.project_id = ?projectId AND a.item_id = ?itemId AND uom_cd = ?uomCd AND qty_balance > 0
ORDER BY a.date ASC";
			$this->connector->AddParameter("?projectId", $projectId);
		}

		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?itemId", $itemId);
		$this->connector->AddParameter("?uomCd", $uomCode);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Stock();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * Berfungsi untuk mengload history barang yang diminta.
	 * Jika filter gudang bernilai null artinya semua gudang berdasarkan SBU
	 *
	 * @param $itemId				=> ID barang yang akan dicari... (INGAT Barang sudah specific per SBU)
	 * @param $uomCode				=> Satuan Barang
	 * @param $startDate
	 * @param $endDate
	 * @param null $projectId		=> Jika perlu filter gudang
	 * @return array
	 */
	public function LoadHistoriesBetween($itemId, $startDate, $endDate, $projectId = null) {
		if ($projectId == null) {
			// Walau pada query kita tidak memberikan filter gudang tetapi kita harus ingat bahwa barang sudah di lock per SBU
			// Jadi jika barang 'A' di lock pada SBU 'Mall' maka jika kita filter hanya berdasarkan barang A tidak mungkin muncul di gudang lain selain gudang Mall
			// Kecuali ada yang tembak data barang 'A' ke gudang SBU lain yang mana secara system tidak mungkin terjadi
			$query = "SELECT a.* FROM ic_stock AS a WHERE a.is_deleted = 0 AND a.item_id = ?itemId AND a.date BETWEEN ?start AND ?end ORDER BY a.date ASC";
		    } else {
			$query = "SELECT a.* FROM ic_stock AS a WHERE a.is_deleted = 0 AND a.project_id = ?projectId AND a.item_id = ?itemId AND a.date BETWEEN ?start AND ?end ORDER BY a.date ASC";
			$this->connector->AddParameter("?projectId", $projectId);
		}

		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?itemId", $itemId);
		$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
		$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Stock();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_stock(project_id,stock_type, reference_id, date, warehouse_id, item_id, qty, uom_cd, price, use_stock_id, qty_balance, createby_id, create_time)
VALUES(?project,?type, ?ref, ?date, ?warehouse, ?item, ?qty, ?uom, ?price, ?useStockId, ?balance, ?user, NOW())";
		$this->connector->AddParameter("?type", $this->StockTypeCode);
		$this->connector->AddParameter("?ref", $this->ReferenceId);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?warehouse", $this->WarehouseId);
        $this->connector->AddParameter("?project", $this->ProjectId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uom", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?useStockId", $this->UseStockId);
		$this->connector->AddParameter("?balance", $this->QtyBalance);
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
"UPDATE ic_stock SET
	stock_type = ?type
	, reference_id = ?ref
	, date = ?date
	, warehouse_id = ?warehouse
	, item_id = ?item
	, qty = ?qty
	, uom_cd = ?uom
	, price = ?price
	, use_stock_id = ?useStockId
	, qty_balance = ?balance
	, updateby_id = ?user
	, update_time = NOW()
	, project_id = ?project
WHERE id = ?id";
		$this->connector->AddParameter("?type", $this->StockTypeCode);
		$this->connector->AddParameter("?ref", $this->ReferenceId);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?warehouse", $this->WarehouseId);
        $this->connector->AddParameter("?project", $this->ProjectId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uom", $this->UomCd);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?useStockId", $this->UseStockId);
		$this->connector->AddParameter("?balance", $this->QtyBalance);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE ic_stock SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: stock.php
