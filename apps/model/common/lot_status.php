<?php
class LotStatus extends EntityBase {
	public $Id;
	public $StatusCd;
    public $Status;

	public function FillProperties(array $row) {
		$this->Id = $row["code"];
        $this->StatusCd = trim($row["desc"]);
        $this->Status = $row["short_desc"];
	}

	 public function LoadAll($orderBy = "a.urutan") {
        $this->connector->CommandText = "SELECT a.* FROM sys_status_code AS a Where a.key = 'lot_status' ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new LotStatus();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM sys_status_code AS a Where a.key = 'lot_status' and a.code = ?id";
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
