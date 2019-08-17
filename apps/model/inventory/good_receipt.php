<?php
require_once("good_receipt_detail.php");

class GoodReceipt extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $DocumentNo;
	public $Date;
	public $SupplierId;
	public $IsVat;
	public $IsIncludeVat;
	public $StatusCode;
	public $CreatedById;
	public $CreatedDate;
	public $ApprovedById;
	public $ApprovedDate;
	public $PaymentModeCode;
	public $PaymentAccountId;		// Jika PaymentModeCode = 1 (CASH) maka ini akan link ke nomor akun
	public $CreditTerms = 0;
	public $UpdatedById;
	public $UpdatedDate;
	public $Note;
	public $WarehouseId = 0;
	public $PostedDate;
	public $PostedById;
	public $ProjectId;
	public $InvoiceNo;

	/** @var GoodReceiptDetail[] */
	public $Details = array();
	/** @var int[] */
	public $PoIds = array();
	/** @var string[] */
	public $PoCodes = array();
	/** @var null|UserAdmin */
    public $CreatedUser = null;
	/** @var null|UserAdmin */
    public $ApprovedUser = null;

	// Helper untuk cari hutang dll
	public $TotalAmount;
	public $TotalPaid;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row, $haveAmount = false, $havePaid = false) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->DocumentNo = $row["doc_no"];
		$this->Date = strtotime($row["gn_date"]);
		$this->SupplierId = $row["supplier_id"];
		$this->IsVat = $row["is_vat"];
		$this->IsIncludeVat = $row["is_inc_vat"];
		$this->StatusCode = $row["status"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->ApprovedById = $row["approveby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->PaymentModeCode = $row["pay_mode"];
		$this->PaymentAccountId = $row["pay_acc_id"];
		$this->CreditTerms = $row["credit_terms"];
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->Note = $row["note"];
		$this->WarehouseId = $row["warehouse_id"];
        $this->ProjectId = $row["project_id"];
		$this->PostedById = $row["posted_by"];
        $this->InvoiceNo = $row["invoice_no"];
		$this->PostedDate = strtotime($row["posted_date"]);
		if ($haveAmount) {
			$this->TotalAmount = $row["sum_amount"];
		}
		if ($havePaid) {
			$this->TotalPaid = $row["sum_paid"];
		}
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
				return "GM APPROVED";
			case 5:
				return "POSTED";
			default:
				return "N.A.";
		}
	}

	public function GetPaymentMode() {
		if ($this->PaymentModeCode == null) {
			return null;
		}
		switch ($this->PaymentModeCode) {
			case 1:
				return "CASH";
			case 2:
				return "KREDIT";
			default:
				return "N.A.";
		}
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : date($format);
	}

	/**
	 * @param bool $indexByItemId
	 * @param string $orderBy
	 * @return GoodReceiptDetail[]
	 */
	public function LoadDetails($indexByItemId = true, $orderBy = "a.item_code") {
		if ($this->Id == null) {
			return $this->Details;
		}

		$detail = new GoodReceiptDetail();
		$this->Details = $detail->LoadByGnId($this->Id, $indexByItemId, $orderBy);
		return $this->Details;
	}

    public function LoadUsers() {
        require_once(MODEL . "master/user_admin.php");
        $this->CreatedUser = new UserAdmin();
        $this->CreatedUser->FindById($this->CreatedById);
        if ($this->ApprovedById != null) {
            $this->ApprovedUser = new UserAdmin();
            $this->ApprovedUser->FindById($this->ApprovedById);
        }
    }

	/**
	 * Function ini berfungsi untuk meload semua PO yang link dengan GN ini
	 *
	 * @see GoodReceipt::PoIds
	 * @see GoodReceipt::PoCodes
	 * @return bool
	 */
	public function LoadAssociatedPo() {
		if ($this->Id == null) {
			return false;
		}

		// Reset data
		$this->PoIds = array();
		$this->PoCodes = array();

		$this->connector->CommandText =
"SELECT a.po_id, b.doc_no
FROM ic_link_po_gn AS a
	JOIN ic_po_master AS b ON a.po_id = b.id
WHERE a.gn_id = ?id AND a.is_deleted = 0";
		$this->connector->AddParameter("?id", $this->Id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			return false;
		}


		while ($row = $rs->FetchAssoc()) {
			$this->PoIds[] = $row["po_id"];
			$this->PoCodes[] = $row["doc_no"];
		}

		return true;
	}

	/**
	 * @param int $id
	 * @return GoodReceipt
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*
FROM ic_gn_master AS a
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
"INSERT INTO ic_gn_master(project_id,entity_id, doc_no, gn_date, supplier_id, is_vat, is_inc_vat, status, createby_id, create_time, note, pay_mode, credit_terms, warehouse_id, pay_acc_id)
VALUES(?pri,?sbu, ?docNo, ?date, ?supplier, ?vat, ?incVat, ?status, ?user, NOW(), ?note, ?payMode, ?terms, ?warehouse, ?payAcc)";
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?supplier", $this->SupplierId);
		$this->connector->AddParameter("?vat", $this->IsVat);
		$this->connector->AddParameter("?incVat", $this->IsIncludeVat);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?payMode", $this->PaymentModeCode);
		$this->connector->AddParameter("?terms", $this->CreditTerms);
		$this->connector->AddParameter("?warehouse", $this->WarehouseId);
        $this->connector->AddParameter("?pri", $this->ProjectId);
		$this->connector->AddParameter("?payAcc", $this->PaymentAccountId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	entity_id = ?sbu
	, doc_no = ?docNo
	, gn_date = ?date
	, supplier_id = ?supplier
	, is_vat = ?vat
	, is_inc_vat = ?incVat
	, status = ?status
	, note = ?note
	, pay_mode = ?payMode
	, credit_terms = ?terms
	, warehouse_id = ?warehouse
	, updateby_id = ?user
	, update_time = NOW()
	, pay_acc_id = ?payAcc
	, project_id = ?pri
WHERE id = ?id";
		$this->connector->AddParameter("?sbu", $this->EntityId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?supplier", $this->SupplierId);
		$this->connector->AddParameter("?vat", $this->IsVat);
		$this->connector->AddParameter("?incVat", $this->IsIncludeVat);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?payMode", $this->PaymentModeCode);
		$this->connector->AddParameter("?terms", $this->CreditTerms);
		$this->connector->AddParameter("?warehouse", $this->WarehouseId);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?payAcc", $this->PaymentAccountId);
        $this->connector->AddParameter("?pri", $this->ProjectId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Approve($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	status = 3
	, approveby_id = ?user
	, approve_time = NOW()
	, updateby_id = ?user
	, update_time = NOW()
	, invoice_no = ?ivn
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->ApprovedById);
        $this->connector->AddParameter("?ivn", $this->InvoiceNo);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	status = 1
	, approveby_id = null
	, approve_time = null
	, updateby_id = ?user
	, update_time = NOW()
	, invoice_no = null
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Post($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	status = 5
	, posted_by = ?user
	, posted_date = NOW()
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->PostedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function UnPost($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	status = 3
	, posted_by = NULL
	, posted_date = NULL
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function SetStatus($id) {
		$this->connector->CommandText =
"UPDATE ic_gn_master SET
	status = ?status
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	/**
	 * Digunakan untuk mencari data-data Good receipt yang dibayar secara KREDIT dan belum lunas
	 * QUERY-nya ajaib karena ini pake sub-query dan tidak pake kolom reference....
	 *
	 * ToDo: Kalau berat atau lama nanti baru kita rubah pake tehnik referensi dan bukan subquery
	 *
	 * @param int $supplierId
	 * @param null|int $excludedPaymentId
	 * @param null|int $maxDate
	 * @return GoodReceipt[]
	 */
	public function LoadUnPainGn($supplierId, $excludedPaymentId = null, $maxDate = null) {
		$query =
"SELECT a.*, b.sum_amount, c.sum_paid
FROM ic_gn_master AS a
	JOIN (
		-- Cari jumlah harga dari detail GN
		SELECT aa.gn_master_id, SUM(aa.qty * aa.price) AS sum_amount
		FROM ic_gn_detail AS aa
		GROUP BY aa.gn_master_id
	) AS b ON a.id = b.gn_master_id
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui Payment Voucher baik yang sudah posting atau yang masih draft
		SELECT bb.gn_id, SUM(bb.amount) AS sum_paid
		FROM ap_payment_master AS aa
			JOIN ap_payment_detail AS bb ON aa.id = bb.payment_id
		WHERE aa.is_deleted = 0 %s -- AND aa.doc_status = 2
		GROUP BY bb.gn_id
	) AS c ON a.id = c.gn_id
-- Untuk mencari GN yang belum lunas (TIPE BAYAR KREDIT !) hanya yang sudah berstatus posting
-- ToDo: Jika status posted atau status bayar KREDIT pada GN berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.pay_mode = 2 AND a.status = 5 AND b.sum_amount - COALESCE(c.sum_paid, 0) > 0 AND a.supplier_id = ?supplier;";

		if ($excludedPaymentId == null) {
			// Tidak ada receipt yang akan di-exclude (kemungkinan ada pada mode add)
			$this->connector->CommandText = sprintf($query, "");
		} else {
			// Ada receipt id yang akan di exclude (mode edit kah ?)
			$this->connector->CommandText = sprintf($query, " AND aa.id <> ?excId");
			$this->connector->AddParameter("?excId", $excludedPaymentId);
		}
		$this->connector->AddParameter("?supplier", $supplierId);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new GoodReceipt();
				$temp->FillProperties($row, true, true);
				if (is_int($maxDate) && $temp->Date > $maxDate) {
					continue;
				}

				$result[] = $temp;
			}
		}

		return $result;
	}

    public function GetJSonUnfinishedPoItems($projectId = 0, $supplierId = 0, $filter = null,$sort = 'c.item_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.item_id,c.item_code,c.part_no,c.item_name,a.uom_cd,a.qty - a.rec_qty as po_qty,a.price,b.doc_no as po_no,b.po_date,b.supplier_id";
        $sql.= " FROM ic_po_detail as a Join ic_po_master as b On a.po_master_id = b.id";
        $sql.= " JOIN vw_ic_item_master AS c ON a.item_id = c.id Where b.is_deleted = 0 And b.status = 3 And (a.qty - a.rec_qty > 0)";
        if ($projectId > 0){
            $sql.= " and b.project_id = $projectId";
        }
        if ($supplierId > 0){
            $sql.= " and b.supplier_id = $supplierId";
        }
        if ($filter != null){
            $sql.= " And (c.item_code Like '%$filter%' Or c.part_no Like '%$filter%' Or c.item_name Like '%$filter%')";
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

    public function GetSubTotal(){
	    $subtotal = 0;
	    $sql = "Select sum(a.qty * a.price) as subTotal From ic_gn_detail AS a Where a.gn_master_id = ?id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?id", $this->Id);
        $subtotal = $this->connector->ExecuteScalar();
        return $subtotal;
    }
}


// End of File: good_receipt.php
