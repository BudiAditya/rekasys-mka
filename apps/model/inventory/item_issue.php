<?php
require_once("item_issue_detail.php");

class ItemIssue extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $DocumentNo;
	public $Date;
	public $DepartmentId;
	public $StatusCode = 1;
	public $CreatedById;
	public $CreatedDate;
	public $ApprovedById;
	public $ApprovedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $Note;
	public $PostedById;
	public $PostedDate;
	public $ProjectId = 0;
	public $ActivityId = 0;
	public $UnitId = 0;

	// Helper
	public $EntityId;
	/** @var ItemIssueDetail[] */
	public $Details = array();
	/** @var int[] */
	public $MrIds = array();
	/** @var string[] */
	public $MrCodes = array();
	/** @var null|UserAdmin */
    public $CreatedUser = null;
	/** @var null|UserAdmin */
    public $ApprovedUser = null;
	/** @var null|Department */
    public $Department = null;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->DocumentNo = $row["doc_no"];
		$this->Date = strtotime($row["issue_date"]);
		$this->DepartmentId = $row["dept_id"];
		$this->StatusCode = $row["status"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->ApprovedById = $row["approveby_id"];
		$this->ApprovedDate = strtotime($row["approve_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->Note = $row["notes"];
		$this->PostedById = $row["posted_by"];
		$this->PostedDate = strtotime($row["posted_date"]);

		$this->EntityId = $row["entity_id"];
        $this->ProjectId = $row["project_id"];
        $this->ActivityId = $row["activity_id"];
        $this->UnitId = $row["unit_id"];
	}

	/**
	 * @return null|string
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
				return "PM APPROVED";
			default:
				return "N.A.";
		}
	}

	/**
	 * @param string $format
	 * @return bool|null|string
	 */
	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : date($format);
	}

	/**
	 * @param bool $indexByItemId
	 * @param string $orderBy
	 * @return ItemIssueDetail[]
	 */
	public function LoadDetails($indexByItemId = true, $orderBy = "b.item_code") {
		if ($this->Id == null) {
			return $this->Details;
		}

		$detail = new ItemIssueDetail();
		$this->Details = $detail->LoadByIsId($this->Id, $indexByItemId, $orderBy);
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

    public  function LoadDepartment() {
        require_once(MODEL . "master/department.php");
        $this->Department = new Department();
        $this->Department->FindById($this->DepartmentId);
    }

	/**
	 * Function ini berfungsi untuk meload semua MR yang link dengan ItemIssue ini
	 *
	 * @return bool
	 */
	public function LoadAssociatedMr() {
		if ($this->Id == null) {
			return false;
		}
		// Reset data
		$this->MrIds = array();
		$this->MrCodes = array();
		$this->connector->CommandText = "SELECT a.mr_id, b.doc_no FROM ic_link_mr_is AS a JOIN ic_mr_master AS b ON a.mr_id = b.id WHERE a.is_id = ?id AND a.is_deleted = 0";
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
	 * @return ItemIssue
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ic_is_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO ic_is_master(entity_id,project_id,activity_id,unit_id,doc_no, issue_date, dept_id, status, createby_id, create_time, notes) VALUES(?entity_id,?project_id,?activity_id,?unit_id,?docNo, ?date, ?dept, ?status, ?user, NOW(), ?note)";
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATEONLY));
		$this->connector->AddParameter("?dept", $this->DepartmentId);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?activity_id", $this->ActivityId);
        $this->connector->AddParameter("?unit_id", $this->UnitId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ic_is_master SET
	doc_no = ?docNo
	, issue_date = ?date
	, dept_id = ?dept
	, status = ?status
	, notes = ?note
	, updateby_id = ?user
	, update_time = NOW()
	, entity_id = ?entity_id
	, project_id = ?project_id
	, activity_id = ?activity_id
	, unit_id = ?unit_id
WHERE id = ?id";
		$this->connector->AddParameter("?docNo", $this->DocumentNo);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?dept", $this->DepartmentId);
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?activity_id", $this->ActivityId);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE ic_is_master SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Approve($id) {
		$this->connector->CommandText = "UPDATE ic_is_master SET status = 3, approveby_id = ?user, approve_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->ApprovedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function DisApprove($id) {
		$this->connector->CommandText = "UPDATE ic_is_master SET status = 1, approveby_id = null, approve_time = null, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Post($id) {
		$this->connector->CommandText = "UPDATE ic_is_master SET status = 5, posted_by = ?user, posted_date = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->PostedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function UnPost($id) {
		$this->connector->CommandText = "UPDATE ic_is_master SET status = 3, posted_by = NULL, posted_date = NULL, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function SetStatus($id) {
		$this->connector->CommandText = "UPDATE ic_is_master SET status = ?status, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?status", $this->StatusCode);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function GetJSonUnfinishedMrItems($projectId = 0, $filter = null,$sort = 'c.item_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.item_id,c.item_code,c.part_no,c.item_name,a.uom_cd,c.brand_name,c.type_desc,a.app_qty - a.iss_qty as qty,b.doc_no as mr_no,b.mr_date,a.unit_id";
        $sql.= " FROM ic_mr_detail as a Join ic_mr_master as b On a.mr_master_id = b.id";
        $sql.= " JOIN vw_ic_item_master AS c ON a.item_id = c.id Where b.is_deleted = 0 And b.status = 4 And (a.app_qty - a.iss_qty > 0)";
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


// End of File: item_issue.php
