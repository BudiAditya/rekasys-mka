<?php
class LotLevel extends EntityBase {
	public $Id;
	public $IsDeleted = false;
    public $EntityId;
	public $EntityCd;
    public $ProjectId;
	public $LevelCd;
    public $LevelName;
    public $LevelSeq;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->ProjectId = $row["project_id"];
        $this->LevelCd = $row["level_cd"];
        $this->LevelName = $row["level_name"];
        $this->LevelSeq = $row["level_seq"];
	}

	public function LoadAll($orderBy = "a.level_seq", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_lotlevel AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_lotlevel AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.is_deleted = 0
ORDER BY $orderBy";
		}

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new LotLevel();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function LoadByEntityId($entityId, $orderBy = "a.level_seq", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_lotlevel AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?entity_id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_lotlevel AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?entity_id AND a.is_deleted = 0
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?entity_id", $entityId);
		$rs = $this->connector->ExecuteQuery();

		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new LotLevel();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function FindById($id) {
        $this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM pm_lotlevel AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.id = ?id";
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO pm_lotlevel(entity_id, level_cd, level_name, level_seq)
VALUES(?entity_id, ?level_cd, ?level_name, ?level_seq)";
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?level_cd", $this->LevelCd);
		$this->connector->AddParameter("?level_name", $this->LevelName);
		$this->connector->AddParameter("?level_seq", $this->LevelSeq);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE pm_lotlevel SET
	entity_id = ?entity_id,
	level_cd = ?level_cd,
	level_name = ?level_name,
	level_seq = ?level_seq
WHERE id = ?id";
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?level_cd", $this->LevelCd);
		$this->connector->AddParameter("?level_name", $this->LevelName);
		$this->connector->AddParameter("?level_seq", $this->LevelSeq);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE pm_lotlevel SET is_deleted = 1 WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}
