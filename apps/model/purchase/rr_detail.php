<?php

class RrDetail extends EntityBase {
	public $Id;
	public $RrId;
	public $Sequence;
	public $ItemId;
	public $MrDetailId;
	public $ItemDescription;
	public $Qty;
	public $UomCd;
	public $SupplierId1;
	public $Price1 = 0;
	public $Date1;
	public $SupplierId2;
	public $Price2 = 0;
	public $Date2;
	public $SupplierId3;
	public $Price3 = 0;
	public $Date3;
	public $SelectedSupplier = 1;

	public $SuppplierCode;
	public $SupplierName;

	// Helper
	public $MarkedForDeletion = false;
	public $ItemCode;
	public $ItemName;
    public $PartNo;
    public $MrNo;
    public $MrDate;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->RrId = $row["rr_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->ItemId = $row["item_id"];
		$this->MrDetailId = $row["mr_detail_id"];
		$this->ItemDescription = $row["item_description"];
		$this->Qty = $row["qty"];
		$this->UomCd = $row["uom_cd"];
		$this->SupplierId1 = $row["supplier_id_1"];
		$this->Price1 = $row["price_1"];
		$this->Date1 = strtotime($row["date_1"]);
		$this->SupplierId2 = $row["supplier_id_2"];
		$this->Price2 = $row["price_2"];
		$this->Date2 = strtotime($row["date_2"]);
		$this->SupplierId3 = $row["supplier_id_3"];
		$this->Price3 = $row["price_3"];
		$this->Date3 = strtotime($row["date_3"]);
		$this->SelectedSupplier = $row["selected_supplier"];

		$this->ItemCode = $row["item_code"];
		$this->ItemName = $row["item_name"];
        $this->PartNo = $row["part_no"];
        $this->MrNo = $row["mr_no"];
        $this->MrDate = $row["mr_date"];
        $this->SuppplierCode = $row["supplier_code"];
        $this->SupplierName = $row["supplier_name"];
	}

	public function FormatDate1($format = HUMAN_DATE) {
		return is_int($this->Date1) ? date($format, $this->Date1) : null;
	}

	public function FormatDate2($format = HUMAN_DATE) {
		return is_int($this->Date2) ? date($format, $this->Date2) : null;
	}

	public function FormatDate3($format = HUMAN_DATE) {
		return is_int($this->Date3) ? date($format, $this->Date3) : null;
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* From vw_ic_rr_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByRrId($prId, $orderBy = "a.item_code") {
		$this->connector->CommandText = "SELECT a.* From vw_ic_rr_detail AS a WHERE a.rr_master_id = ?id ORDER BY $orderBy";
		$this->connector->AddParameter("?id", $prId);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new RrDetail();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_rr_detail(rr_master_id, item_id, mr_detail_id, qty, item_description, uom_cd, supplier_id_1, price_1, supplier_id_2, price_2, supplier_id_3, price_3, selected_supplier, date_1, date_2, date_3)
VALUES (?prId, ?itemId, ?mrDetId, ?qty, ?itemDesc, ?uomCd, ?supp1, ?price1, ?supp2, ?price2, ?supp3, ?price3, ?selSupp, ?date1, ?date2, ?date3)";

		$this->connector->AddParameter("?prId", $this->RrId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?mrDetId", $this->MrDetailId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?supp1", $this->SupplierId1);
		$this->connector->AddParameter("?price1", $this->Price1);
		$this->connector->AddParameter("?supp2", $this->SupplierId2);
		$this->connector->AddParameter("?price2", $this->Price2);
		$this->connector->AddParameter("?supp3", $this->SupplierId3);
		$this->connector->AddParameter("?price3", $this->Price3);
		$this->connector->AddParameter("?selSupp", $this->SelectedSupplier);
		$this->connector->AddParameter("?date1", $this->FormatDate1(SQL_DATETIME));
		$this->connector->AddParameter("?date2", $this->FormatDate2(SQL_DATETIME));
		$this->connector->AddParameter("?date3", $this->FormatDate3(SQL_DATETIME));

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText  = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_rr_detail SET
	rr_master_id = ?prId
	, item_id = ?itemId
	, mr_detail_id = ?mrDetId
	, qty = ?qty
	, item_description = ?itemDesc
	, uom_cd = ?uomCd
	, supplier_id_1 = ?supp1
	, price_1 = ?price1
	, supplier_id_2 = ?supp2
	, price_2 = ?price2
	, supplier_id_3 = ?supp3
	, price_3 = ?price3
	, selected_supplier = ?selSupp
	, date_1 = ?date1
	, date_2 = ?date2
	, date_3 = ?date3
WHERE id = ?id";
		$this->connector->AddParameter("?prId", $this->RrId);
		$this->connector->AddParameter("?itemId", $this->ItemId);
		$this->connector->AddParameter("?mrDetId", $this->MrDetailId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?itemDesc", $this->ItemDescription);
		$this->connector->AddParameter("?uomCd", $this->UomCd);
		$this->connector->AddParameter("?supp1", $this->SupplierId1);
		$this->connector->AddParameter("?price1", $this->Price1);
		$this->connector->AddParameter("?supp2", $this->SupplierId2);
		$this->connector->AddParameter("?price2", $this->Price2);
		$this->connector->AddParameter("?supp3", $this->SupplierId3);
		$this->connector->AddParameter("?price3", $this->Price3);
		$this->connector->AddParameter("?selSupp", $this->SelectedSupplier);
		$this->connector->AddParameter("?date1", $this->FormatDate1(SQL_DATETIME));
		$this->connector->AddParameter("?date2", $this->FormatDate2(SQL_DATETIME));
		$this->connector->AddParameter("?date3", $this->FormatDate3(SQL_DATETIME));
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ic_rr_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

    public function Approve($id) {
        $this->connector->CommandText = "UPDATE ic_rr_detail a SET a.price_1 = ?price, a.supplier_id_1 = ?supplier, a.selected_supplier = 1 WHERE id = ?id";
        $this->connector->AddParameter("?price", $this->Price1);
        $this->connector->AddParameter("?supplier", $this->SupplierId1);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }
}


// End of File: pr_detail.php
