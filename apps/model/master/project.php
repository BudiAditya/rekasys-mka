<?php
class Project extends EntityBase {
	public $Id;
	public $EntityId;
	public $EntityCd;
	public $ProjectCd;
	public $ProjectName;
	public $ProjectLocation;
	public $Pic;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->ProjectCd = $row["project_cd"];
		$this->ProjectName = $row["project_name"];
		$this->ProjectLocation = $row["project_location"];
		$this->Pic = $row["pic"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Project[]
	 */
	public function LoadAll($orderBy = "a.project_cd", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd FROM cm_project AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Project();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadAllowedProject($allowedProjectIds = 0,$orderBy = "a.project_cd") {
        $this->connector->CommandText = "SELECT a.*, b.entity_cd FROM cm_project AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.is_deleted = 0 And Locate(a.id,".$allowedProjectIds.") ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Project();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $id
	 * @return Project
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd FROM cm_project AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByCode($entityId,$code) {
        $this->connector->CommandText = "SELECT a.*, b.entity_cd FROM cm_project AS a JOIN cm_company AS b ON a.entity_id = b.entity_id WHERE a.project_cd = ?code And a.entity_id = ?eti";
        $this->connector->AddParameter("?code", $code);
        $this->connector->AddParameter("?eti", $entityId);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

	/**
	 * @param int $id
	 * @return Project
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $eti
	 * @return Project[]
	 */
	public function LoadByEntityId($eti) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_project AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.is_deleted = 0 AND a.entity_id = ?eti
ORDER BY a.project_cd";
		$this->connector->AddParameter("?eti", $eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Project();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO cm_project(entity_id,project_cd,project_name,project_location,pic)
VALUES(?entity_id,?project_cd,?project_name,?project_location,?pic)';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?project_cd", $this->ProjectCd, "varchar");
		$this->connector->AddParameter("?project_name", $this->ProjectName);
		$this->connector->AddParameter("?project_location", $this->ProjectLocation);
		$this->connector->AddParameter("?pic", $this->Pic);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_project SET
	entity_id = ?entity_id,
	project_cd = ?project_cd,
	project_name = ?project_name,
	project_location = ?project_location,
	pic = ?pic
WHERE id = ?id';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?project_cd", $this->ProjectCd, "varchar");
		$this->connector->AddParameter("?project_name", $this->ProjectName);
		$this->connector->AddParameter("?project_location", $this->ProjectLocation);
		$this->connector->AddParameter("?pic", $this->Pic);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'UPDATE cm_project SET is_deleted = 1 WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

}
