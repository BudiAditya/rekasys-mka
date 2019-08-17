<?php
require_once("mr_detail.php");

class Mr extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $DocumentNo;
	public $Date;		// Tanggal Dokumen MR (belum tentu sama dengan tanggal buat. Bisa jadi buat nya telat)
	public $StatusCode = 1;
	public $EntityId;
	public $ProjectId;
	public $DepartmentId;
	public $ActivityId;
	//public $UnitId;
	public $CreatedById;
	public $CreatedDate;
	public $AcknowledgeById;
	public $AcknowledgeDate;
	public $ApprovebyId;
	public $ApproveTime;
    public $Approve2byId;
    public $Approve2Time;
	public $Note;
	public $RequestBy;
	public $UpdatedById;
	public $UpdatedDate;
	/** @var MrDetail[] */
	public $MrDetails = array();

	// Field Tambahan dari table lain
    public $ProjectCd;
    public $ProjectName;
    //public $UnitCode;
    //public $UnitName;
	public $DepartmentCode;
	public $EntityCode;
    public $CreatedUser = null;
    public $ApprovedUser = null;
    public $Approved2User = null;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->DocumentNo = $row["doc_no"];
		$this->Date = $row["mr_date"] != null ? strtotime($row["mr_date"]) : null;
		$this->StatusCode = $row["status"];
		$this->DepartmentId = $row["dept_id"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->AcknowledgeById = $row["acknowledge_by"];
		$this->AcknowledgeDate = strtotime($row["acknowledge_date"]);
		$this->ApprovebyId = $row["approveby_id"];
		$this->ApproveTime = strtotime($row["approve_time"]);
        $this->Approve2byId = $row["approve2by_id"];
        $this->Approve2Time = strtotime($row["approve2_time"]);
		$this->Note = $row["notes"];
        $this->RequestBy = $row["request_by"];
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);

		$this->DepartmentCode = $row["dept_code"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCode = $row["entity_cd"];
        $this->ProjectId = $row["project_id"];
        $this->ProjectCd = $row["project_cd"];
        $this->ProjectName = $row["project_name"];
        //$this->UnitId = $row["unit_id"];
        //$this->UnitCode = $row["unit_code"];
        //$this->UnitName = $row["unit_name"];
        $this->ActivityId = $row["activity_id"];
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
				return "APPROVED 1";
			case 4:
				return "APPROVED 2";
			case 5:
				return "PROSES PR";
			case 6:
				return "DELIVERY";
			case 7:
				return "COMPLETED";
			default:
				return "N.A.";
		}
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : date($format);
	}

	/**
	 * @param string $orderBy
	 * @return MrDetail[]
	 */
	public function LoadDetails($orderBy = "b.item_code") {
		if ($this->Id == null) {
			return $this->MrDetails;
		}

		$detail = new MrDetail();
		$this->MrDetails = $detail->LoadByMrId($this->Id, $orderBy);
		return $this->MrDetails;
	}

    public function LoadUsers() {
        require_once(MODEL . "master/user_admin.php");
        $this->CreatedUser = new UserAdmin();
        $this->CreatedUser->FindById($this->CreatedById);
        if ($this->ApprovebyId != null) {
            $this->ApprovedUser = new UserAdmin();
            $this->ApprovedUser->FindById($this->ApprovebyId);
        }
        if ($this->Approve2byId != null) {
            $this->Approved2User = new UserAdmin();
            $this->Approved2User->FindById($this->Approve2byId);
        }
    }

	/**
	 * @param int $id
	 * @return Mr
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.project_cd,b.project_name, c.entity_cd, d.dept_code
FROM ic_mr_master AS a
	JOIN cm_project AS b ON a.project_id = b.id
	JOIN cm_company AS c ON a.entity_id = c.entity_id
	LEFT JOIN cm_dept AS d ON a.dept_id = d.id
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
	 * Mencari semua MR yang masih gantung (APPROVED, GM APPROVED, PROSES PR, DELIVERY) yang akan berguna pada saat delivery
	 *
	 * @param int $id
	 * @param bool $byDepartment	=> Specify false if you want to search MR by entity id
	 * @return Mr[]
	 */
	public function  LoadUnfinishedMr($id, $byProject) {
		$query =
"SELECT a.*, b.project_cd,b.project_name, c.entity_cd, d.dept_code
FROM ic_mr_master AS a
	JOIN cm_project AS b ON a.project_id = b.id
	JOIN cm_company AS c ON a.entity_id = c.entity_id
	LEFT JOIN cm_dept AS d ON a.dept_id = d.id
WHERE %s = ?id AND a.status IN(3, 4, 5, 6)
ORDER BY a.mr_date ASC";

		$this->connector->CommandText = sprintf($query, $byProject ? "a.project_id" : "a.entity_id");
		$this->connector->AddParameter("?id", $id);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Mr();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ic_mr_master(request_by,entity_id,project_id,activity_id,doc_no, mr_date, status, dept_id, createby_id, create_time, notes)
VALUES(?request_by,?entityId,?projectId,?activityId,?docNo, ?date, ?status, ?deptId, ?user, NOW(), ?note)";
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?entityId", $this->EntityId);
        $this->connector->AddParameter("?projectId", $this->ProjectId);
        $this->connector->AddParameter("?deptId", $this->DepartmentId);
        $this->connector->AddParameter("?activityId", $this->ActivityId);
        //$this->connector->AddParameter("?unitId", $this->UnitId);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?request_by", $this->RequestBy);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	/**
	 * Update data MR berdasarkan ID.
	 * NOTE: yang updatable pada method ini sedikit karena field-field yang lain hanya akan diupdate berdasarkan method yang lain
	 *
	 * @param $id
	 * @return int
	 */
	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_mr_master SET
	doc_no = ?docNo
	, mr_date = ?date
	, dept_id = ?deptId
	, notes = ?note
	, updateby_id = ?user
	, update_time = NOW()
	, entity_id = ?entityId
	, project_id = ?projectId
	, activity_id = ?activityId
	, request_by = ?request_by
WHERE id = ?id";
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
        $this->connector->AddParameter("?entityId", $this->EntityId);
        $this->connector->AddParameter("?projectId", $this->ProjectId);
        $this->connector->AddParameter("?deptId", $this->DepartmentId);
        $this->connector->AddParameter("?activityId", $this->ActivityId);
        //$this->connector->AddParameter("?unitId", $this->UnitId);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?request_by", $this->RequestBy);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText =
"UPDATE ic_mr_master SET
	is_deleted = 1
	, updateby_id = ?user
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Approve($id,$lvl) {
	    if ($lvl == 1) {
            $sql = "UPDATE ic_mr_master SET status = 3, approveby_id = ?user, approve_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        }else{
            $sql = "UPDATE ic_mr_master SET status = 4, approve2by_id = ?user, approve2_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        }
		$this->connector->CommandText = $sql;
		$this->connector->AddParameter("?user", $this->ApprovebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id,$lvl) {
	    if ($lvl == 1){
            $sql = "UPDATE ic_mr_master SET status = 2, approveby_id = null, approve_time = null, updateby_id = ?user, update_time = NOW() WHERE id = ?id;";
        }else{
            $sql = "UPDATE ic_mr_master SET status = 3, approve2by_id = null, approve2_time = null, updateby_id = ?user, update_time = NOW() WHERE id = ?id;";
        }
		$this->connector->CommandText = $sql;
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function SetStatus($id) {
		$this->connector->CommandText = "UPDATE ic_mr_master SET status = ?status, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

    public function GetJSonUnfinishedMrItems($projectId = 0, $filter = null,$sort = 'c.item_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.item_id,c.item_code,c.part_no,c.item_name,a.uom_cd,c.brand_name,c.type_desc,a.app_qty - a.iss_qty as qty,b.doc_no as mr_no,b.mr_date";
        $sql.= " FROM ic_mr_detail as a Join ic_mr_master as b On a.mr_master_id = b.id";
        $sql.= " JOIN vw_ic_item_master AS c ON a.item_id = c.id Where b.is_deleted = 0 And b.status = 4";
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

    public function GetUnfinisedMrSummary($projectId){
	    $sql = "Select a.*,b.item_code,b.part_no,b.item_name,b.uom_cd,b.brand_name,b.type_desc From vw_ic_mr_vs_stock AS a";
	    $sql.= " Join vw_ic_item_master AS b ON a.item_id = b.id Where a.project_id = $projectId Order By b.item_code";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }else{
            return $rs;
        }
    }

    public function GetUnfinisedMrDetails($projectId){
        $sql = "Select a.*,b.part_no,b.item_name,b.uom_cd,b.brand_name,b.type_desc,c.dept_code,c.dept_name From vw_ic_mr_unfinished_detail_vs_stock AS a";
        $sql.= " Join vw_ic_item_master AS b ON a.item_id = b.id Join cm_dept AS c ON a.dept_id = c.id";
        $sql.= " Where a.project_id = $projectId Order By a.mr_date,a.mr_no,a.dept_id,b.item_code";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }else{
            return $rs;
        }
    }

    public function GetUnfinisedMrDetail($id){
        $sql = "Select a.*,b.part_no,b.item_name,b.uom_cd,b.brand_name,b.type_desc,c.dept_code,c.dept_name From vw_ic_mr_unfinished_detail_vs_stock AS a";
        $sql.= " Join vw_ic_item_master AS b ON a.item_id = b.id Join cm_dept AS c ON a.dept_id = c.id";
        $sql.= " Where a.id = $id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }else{
            return $rs->FetchAssoc();
        }
    }
}


// End of File: mr.php
