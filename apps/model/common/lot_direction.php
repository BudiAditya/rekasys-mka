<?php
class LotDirection extends EntityBase {
	public $Id;
	public $DirectionCd;
    public $Direction;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
        $this->DirectionCd = $row["direction_cd"];
        $this->Direction = $row["direction"];
	}

	 public function LoadAll($orderBy = "a.direction_cd") {
        $this->connector->CommandText = "SELECT a.* FROM pm_lotdirection AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new LotDirection();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM pm_lotdirection AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

}
