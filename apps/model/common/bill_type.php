<?php
class BillType extends EntityBase {
	public $Id;
	public $BillTypeCd;
	public $BillTypeDesc;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->BillTypeCd = $row["billtype_cd"];
		$this->BillTypeDesc = $row["billtype_desc"];
	}

    public function LoadAll($orderBy = "a.billtype_cd") {
		$this->connector->CommandText = "SELECT a.* FROM sys_billtype AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new BillType();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM sys_billtype AS a WHERE a.id = ?id";
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
