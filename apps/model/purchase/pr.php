<?php
require_once("pr_detail.php");

class Pr extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $DocumentNo;
	public $Date;		// Tanggal Dokumen PR (belum tentu sama dengan tanggal buat. Bisa jadi buat nya telat)
	public $StatusCode = 1;
	public $EntityId;
	public $CreatedById;
	public $CreatedDate;
	public $ApprovedById;
	public $ApprovedDate;
	public $Note;
	public $UpdatedById;
	public $UpdatedDate;
	public $ProjectId;
    public $ProjectCd;
    public $ProjectName;
    public $DeptId;
    public $DeptCd;
    public $DeptName;
    public $SupplierId = 0;
    public $ReqLevel = 1;

    public $Approve2byId;
    public $Approve2Time;

	// Helper
    public $QtyStatus = 0;
    public $PrcStatus = 0;
    public $SupStatus = 0;
	/** @var PrDetail[] */
	public $Details = array();
	/** @var int[] */
	public $MrIds = array();
	/** @var int[] */
	public $MrCodes = array();
	/** @var UserAdmin */
	public $CreatedUser = null;
	/** @var UserAdmin */
	public $ApprovedUser = null;
    public $Approved2User = null;

	// Field Tambahan dari table lain
	public $EntityCode;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->DocumentNo = $row["doc_no"];
		$this->Date = $row["pr_date"] != null ? strtotime($row["pr_date"]) : null;
		$this->StatusCode = $row["status"];
		$this->EntityId = $row["entity_id"];
        $this->ProjectId = $row["project_id"];
        $this->SupplierId = $row["supplier_id"];
        $this->ProjectCd = $row["project_cd"];
        $this->ProjectName = $row["project_name"];
        $this->DeptId = $row["dept_id"];
        $this->DeptCd = $row["dept_code"];
        $this->DeptName = $row["dept_name"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->ApprovedById = $row["approveby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->Note = $row["note"];
        $this->ReqLevel = $row["req_level"];
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);

        $this->Approve2byId = $row["approve2by_id"];
        $this->Approve2Time = strtotime($row["approve2_time"]);

		$this->EntityCode = $row["entity_cd"];

		$this->QtyStatus = $row["qty_status"];
        $this->PrcStatus = $row["prc_status"];
        $this->SupStatus = $row["sup_status"];
	}

	public function GetStatus() {
		if ($this->StatusCode == null) {
			return null;
		}

		switch ($this->StatusCode) {
			case 0:
				return "INCOMPLETE";
			case 1:
				return "DRAFT";
			case 2:
				return "DH APPROVED";
			case 3:
				return "PM APPROVED";
			case 4:
				return "PROSES PO";
			default:
				return "N.A.";
		}
	}

    public function GetReqLevel() {
        if ($this->ReqLevel == null) {
            return null;
        }
        switch ($this->ReqLevel) {
            case 1:
                return "NORMAL";
            case 2:
                return "MEDIUM";
            case 3:
                return "URGENT";
            default:
                return "N.A.";
        }
    }

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : date($format);
	}

	/**
	 * @param string $orderBy
	 * @return PrDetail[]
	 */
	public function LoadDetails($orderBy = "a.item_code") {
		if ($this->Id == null) {
			return $this->Details;
		}

		$detail = new PrDetail();
		$this->Details = $detail->LoadByPrId($this->Id, $orderBy);
		return $this->Details;
	}

    public function LoadUsers() {
        require_once(MODEL . "master/user_admin.php");
        $this->CreatedUser = new UserAdmin();
        $this->CreatedUser->FindById($this->CreatedById);
        if ($this->ApprovedById != null) {
            $this->ApprovedUser = new UserAdmin();
            $this->ApprovedUser->FindById($this->ApprovedById);
            $this->Approved2User = new UserAdmin();
            $this->Approved2User->FindById($this->Approve2byId);
        }
    }

	public function LoadAssociatedMr() {
		if ($this->Id == null) {
			return false;
		}

		// Reset data
		$this->MrIds = array();
		$this->MrCodes = array();

		$this->connector->CommandText = "SELECT a.mr_id, b.doc_no FROM ic_link_mr_pr AS a JOIN ic_mr_master AS b ON a.mr_id = b.id WHERE a.pr_id = ?id AND a.is_deleted = 0";
		$this->connector->AddParameter("?id", $this->Id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			return false;
		}
		while ($row = $rs->FetchAssoc()) {
			$this->MrIds[] = $row["mr_id"];
			$this->MrCodes[] = $row["doc_no"];
		}
		return true;
	}

	/**
	 * @param int $id
	 * @return Pr
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_pr_master AS a  WHERE a.id = ?id";
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
"INSERT INTO ic_pr_master(req_level,supplier_id,dept_id,project_id,doc_no, pr_date, status, entity_id, createby_id, create_time, note)
VALUES(?req_level,?supplier_id,?dept_id,?project,?docNo, ?date, ?status, ?sbu, ?user, NOW(), ?note)";
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?project", $this->ProjectId);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?req_level", $this->ReqLevel);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	/**
	 * Update data PR berdasarkan ID.
	 * NOTE: yang updatable pada method ini sedikit karena field-field yang lain hanya akan diupdate berdasarkan method yang lain
	 *
	 * @param $id
	 * @throws Exception
	 * @return int
	 */
	public function Update($id) {
		if ($this->StatusCode > 1) {
			throw new Exception("Unable to change PR status code Above 1 ! Please use other method to change if status code is above than 1");
		}

		$this->connector->CommandText =
"UPDATE ic_pr_master SET
	doc_no = ?docNo
	, pr_date = ?date
	, status = ?status
	, entity_id = ?sbu
	, note = ?note
	, updateby_id = ?user
	, update_time = NOW()
	, project_id = ?project
	, dept_id = ?dept_id
	, supplier_id = ?supplier_id
	, req_level = ?req_level
WHERE id = ?id";
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?sbu", $this->EntityId);
        $this->connector->AddParameter("?project", $this->ProjectId);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?req_level", $this->ReqLevel);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ic_pr_master SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Approve($id,$lvl = 0) {
	    if ($lvl == 1) {
            $this->connector->CommandText = "UPDATE ic_pr_master SET status = 2, approveby_id = ?user, approve_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        }else{
            $this->connector->CommandText = "UPDATE ic_pr_master SET status = 3, approve2by_id = ?user, approve2_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        }
		$this->connector->AddParameter("?user", $this->ApprovedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id,$lvl = 0) {
	    if ($lvl == 1) {
            $this->connector->CommandText = "UPDATE ic_pr_master SET status = 1, approveby_id = null, approve_time = null, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        }else{
            $this->connector->CommandText = "UPDATE ic_pr_master SET status = 2, approve2by_id = null, approve2_time = null, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        }
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function SetStatus($id) {
		$this->connector->CommandText =
"UPDATE ic_pr_master SET
	status = ?status
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function GetJSonUnfinishedMrItems($projectId = 0, $filter = null,$sort = 'c.item_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.item_id,c.item_code,c.part_no,c.item_name,a.uom_cd,c.brand_name,c.type_desc,a.app_qty - a.pr_qty as qty,b.doc_no as mr_no,b.mr_date,a.unit_id";
        $sql.= " FROM ic_mr_detail as a Join ic_mr_master as b On a.mr_master_id = b.id";
        $sql.= " JOIN vw_ic_item_master AS c ON a.item_id = c.id Where b.is_deleted = 0 And b.status = 4 And a.sts_item = 1 And (a.app_qty - a.pr_qty > 0)";
        if ($projectId > 0){
            $sql.= " and b.project_id = $projectId";
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


// End of File: pr.php
