<?php
class StockLocation extends EntityBase {
	public $Id;
	public $ProjectId;
	public $LocName;
    public $BinCode;
    public $Description;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->ProjectId = $row["project_id"];
		$this->LocName = $row["loc_name"];
        $this->BinCode = $row["bin_code"];
        $this->Description = $row["description"];
	}

    public function LoadAll($orderBy = "a.project_id") {
		$this->connector->CommandText = "SELECT a.* FROM cm_stock_location AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new StockLocation();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByProjectId($projectId = 0) {
        $this->connector->CommandText = "SELECT a.* FROM cm_stock_location AS a Where a.project_id = $projectId ORDER BY a.bin_code";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new StockLocation();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_stock_location AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO cm_stock_location(project_id,loc_name,bin_code,description) VALUES(?project_id,?loc_name,?bin_code,?description)';
		$this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?loc_name", $this->LocName);
        $this->connector->AddParameter("?bin_code", $this->BinCode);
        $this->connector->AddParameter("?description", $this->Description);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE cm_stock_location SET project_id = ?project_id, loc_name = ?loc_name, bin_code = ?bin_code, description = ?description WHERE id = ?id';
		$this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?loc_name", $this->LocName);
        $this->connector->AddParameter("?bin_code", $this->BinCode);
        $this->connector->AddParameter("?description", $this->Description);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM cm_stock_location WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}

