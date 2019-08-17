<?php
require_once("po_detail.php");

class Po extends EntityBase {
	public $Id;
	public $IsDeleted;
	public $EntityId;
	public $ProjectId;
	public $DocumentNo;
	public $Date;
	public $SupplierId;
	public $IsVat;
	public $IsIncludeVat;
	public $ExpectedDate;
	public $PaymentTerms = 0;
	public $Note;
	public $StatusCode = 1;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $ApprovedById;
	public $ApprovedDate;
	public $ParentPoId;

    public $Approve2ById;
    public $Approve2Date;
    public $Approve3ById;
    public $Approve3Date;

	/** @var PoDetail[] */
	public $Details = array();
	/** @var int[] */
	public $PrIds = array();
	/** @var int[] */
	public $PrCodes = array();

    //helper tambahan untuk ambil data supplier
	/** @var Creditor */
	public $Supplier = null;
    /** @var Project */
    public $Project = null;
    /** @var Company */
    public $Company = null;
	/** @var UserAdmin */
	public $CreatedUser = null;
	/** @var UserAdmin */
	public $ApprovedUser = null;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
        $this->ProjectId = $row["project_id"];
		$this->DocumentNo = $row["doc_no"];
		$this->Date = strtotime($row["po_date"]);
		$this->SupplierId = $row["supplier_id"];
		$this->IsVat = $row["is_vat"] == 1;
		$this->IsIncludeVat = $row["is_inc_vat"];
		$this->ExpectedDate = strtotime($row["expected_date"]);
		$this->PaymentTerms = $row["payment_terms"];
		$this->Note = $row["notes"];
		$this->StatusCode = $row["status"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->ApprovedById = $row["approveby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->ParentPoId = $row["parent_po"];
        $this->Approve2ById = $row["approve2by_id"];
        $this->Approve2Date = strtotime($row["approve2_time"]);
        $this->Approve3ById = $row["approve3by_id"];
        $this->Approve3Date = strtotime($row["approve3_time"]);
	}

	/**
	 * @return string
	 */
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
				return "PROSES GN";
			default:
				return "N.A.";
		}
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	public function FormatExpectedDate($format = HUMAN_DATE) {
		return is_int($this->ExpectedDate) ? date($format, $this->ExpectedDate) : null;
	}

	/**
	 * @param bool $loadPrCode
	 * @param string $orderBy
	 * @return Po[]
	 */
	public function LoadDetails($orderBy = "a.item_code") {
		if ($this->Id == null) {
			return $this->Details;
		}

		$detail = new PoDetail();
		$this->Details = $detail->LoadByPoId($this->Id, $orderBy);
		return $this->Details;
	}

	/**
	 * @return float
	 */
	public function GetTotalAmount() {
		if ($this->Details === null) {
			return null;
		}
		$sum = 0;
		foreach ($this->Details as $detail) {
			$sum += $detail->Qty * $detail->Price;
		}
		if ($this->IsVat && !$this->IsIncludeVat) {
			$sum = $sum * 1.1;
		}
		return $sum;
	}

    //get data supplier/creditor
    public function LoadSupplier() {
        require_once(MODEL . "master/creditor.php");
        $this->Supplier = new Creditor();
        $this->Supplier->FindById($this->SupplierId);
    }
    //get data project
    public function LoadProject() {
        require_once(MODEL . "master/project.php");
        $this->Project = new Project();
        $this->Project->FindById($this->ProjectId);
    }
    //get data company
    public function LoadCompany() {
        require_once(MODEL . "master/company.php");
        $this->Company = new Company();
        $this->Company->FindById($this->EntityId);
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
	 * @see Po::PrIds
	 * @see Po::PrCodes
	 * @return bool
	 */
	public function LoadAssociatedPr() {
		if ($this->Id == null) {
			return false;
		}

		// Reset data
		$this->PrIds = array();
		$this->PrCodes = array();

		$this->connector->CommandText =
"SELECT a.pr_id, b.doc_no
FROM ic_link_pr_po AS a
	JOIN ic_pr_master AS b ON a.pr_id = b.id
WHERE a.po_id = ?id AND a.is_deleted = 0";
		$this->connector->AddParameter("?id", $this->Id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			return false;
		}


		while ($row = $rs->FetchAssoc()) {
			$this->PrIds[] = $row["pr_id"];
			$this->PrCodes[] = $row["doc_no"];
		}

		return true;
	}

	/**
	 * @param int $id
	 * @return Po
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*
FROM ic_po_master AS a
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
	 * Mencari semua PO yang masih gantung (APPROVED, GM APPROVED, PROSES GN) yang akan berguna pada saat delivery
	 *
	 * @param $supplierId
	 * @return Po[]
	 */
	public function LoadUnfinishedPo($supplierId,$entityId = 0,$projectId = 0) {
	    $sqx = "SELECT a.* FROM ic_po_master AS a WHERE a.supplier_id = ?id AND a.status IN(3, 4, 5)";
	    if ($entityId > 0){
	        $sqx.= " And a.entity_id = $entityId";
        }
        if ($projectId > 0){
            $sqx.= " And a.project_id = $projectId";
        }
	    $sqx.= " ORDER BY a.po_date ASC";
		$this->connector->CommandText = $sqx;
		$this->connector->AddParameter("?id", $supplierId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Po();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_po_master(payment_terms,project_id, entity_id, doc_no, po_date, supplier_id, is_vat, is_inc_vat, expected_date, status, createby_id, create_time, notes, parent_po)
VALUES(?payment_terms,?project_id,?sbu, ?docNo, ?date, ?supplier, ?vat, ?incVat, ?eta, ?status, ?user, NOW(), ?note, ?parentPo)";
		$this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?supplier", $this->SupplierId);
        $this->connector->AddParameter("?payment_terms", $this->PaymentTerms);
		$this->connector->AddParameter("?vat", $this->IsVat);
		$this->connector->AddParameter("?incVat", $this->IsIncludeVat);
		$this->connector->AddParameter("?eta", $this->FormatExpectedDate(SQL_DATEONLY));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?parentPo", $this->ParentPoId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	/**
	 * Update data PO berdasarkan ID.
	 * NOTE: yang updatable pada method ini sedikit karena field-field yang lain hanya akan diupdate berdasarkan method yang lain
	 *
	 * @param int $id
	 * @return int
	 */
	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_po_master SET
	entity_id = ?sbu
	, project_id = ?project_id
	, doc_no = ?docNo
	, po_date = ?date
	, supplier_id = ?supplier
	, is_vat = ?vat
	, is_inc_vat = ?incVat
	, expected_date = ?eta
	, status = ?status
	, notes = ?note
	, updateby_id = ?user
	, update_time = NOW()
	, payment_terms = ?payment_terms
WHERE id = ?id";
		$this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?supplier", $this->SupplierId);
        $this->connector->AddParameter("?payment_terms", $this->PaymentTerms);
		$this->connector->AddParameter("?vat", $this->IsVat);
		$this->connector->AddParameter("?incVat", $this->IsIncludeVat);
		$this->connector->AddParameter("?eta", $this->FormatExpectedDate(SQL_DATEONLY));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ic_po_master SET
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
"UPDATE ic_po_master SET
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
"UPDATE ic_po_master SET
	status = 1
	, approveby_id = null
	, approve_time = null
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function SetStatus($id) {
		$this->connector->CommandText =
"UPDATE ic_po_master SET
	status = ?status
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

    public function GetJSonUnfinishedPrItems($projectId = 0, $supplierId = 0, $filter = null,$sort = 'c.item_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.item_id,c.item_code,c.part_no,c.item_name,a.uom_cd,a.qty - a.po_qty as pr_qty,a.price_1 as price,b.doc_no as pr_no,b.pr_date,a.supplier_id_1 as supplier_id";
        $sql.= " FROM ic_pr_detail as a Join ic_pr_master as b On a.pr_master_id = b.id";
        $sql.= " JOIN vw_ic_item_master AS c ON a.item_id = c.id Where b.is_deleted = 0 And b.status = 4 And (a.qty - a.po_qty > 0)";
        if ($projectId > 0){
            $sql.= " and b.project_id = $projectId";
        }
        if ($supplierId > 0){
            $sql.= " and a.supplier_id_1 = $supplierId";
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
}


// End of File: po.php
