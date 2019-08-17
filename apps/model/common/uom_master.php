<?php
class UomMaster extends EntityBase {
	public $Id;
	public $UomCd;
	public $UomDesc;
    public $Dimension;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->UomCd = $row["uom_cd"];
		$this->UomDesc = $row["uom_desc"];
        $this->Dimension = $row["dimension"];
	}

    public function LoadAll($orderBy = "a.uom_cd") {
		$this->connector->CommandText = "SELECT a.* FROM cm_uomaster AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UomMaster();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_uomaster AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO cm_uomaster(uom_cd,uom_desc,dimension) VALUES(?uom_cd,?uom_desc,?dimension)';
		$this->connector->AddParameter("?uom_cd", $this->UomCd);
        $this->connector->AddParameter("?uom_desc", $this->UomDesc);
        $this->connector->AddParameter("?dimension", $this->Dimension);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE cm_uomaster SET uom_cd = ?uom_cd, uom_desc = ?uom_desc, dimension = ?dimension WHERE id = ?id';
		$this->connector->AddParameter("?uom_cd", $this->UomCd);
        $this->connector->AddParameter("?uom_desc", $this->UomDesc);
        $this->connector->AddParameter("?dimension", $this->Dimension);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM cm_uomaster WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}

