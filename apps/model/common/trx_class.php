<?php
class TrxClass extends EntityBase {
	public $Id;
	public $TrxClassCd;
	public $TrxClassDesc;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->TrxClassCd = $row["trxclass_cd"];
		$this->TrxClassDesc = $row["trxclass_desc"];
	}

    public function LoadAll($orderBy = "a.trxclass_cd") {
		$this->connector->CommandText = "SELECT a.* FROM sys_trxclass AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TrxClass();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM sys_trxclass AS a WHERE a.id = ?id";
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
'INSERT INTO sys_trxclass(trxclass_cd,trxclass_desc) VALUES(?trxclass_cd,?trxclass_desc)';
		$this->connector->AddParameter("?trxclass_cd", $this->TrxClassCd);
        $this->connector->AddParameter("?trxclass_desc", $this->TrxClassDesc);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE sys_trxclass SET trxclass_cd = ?trxclass_cd, trxclass_desc = ?trxclass_desc WHERE id = ?id';
		$this->connector->AddParameter("?trxclass_cd", $this->TrxClassCd);
        $this->connector->AddParameter("?trxclass_desc", $this->TrxClassDesc);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From sys_trxclass WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}
