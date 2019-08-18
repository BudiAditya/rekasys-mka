<?php
class ItemLocation extends EntityBase {
	public $Id;
	public $ItemId;
    public $LocationId;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->ItemId = $row["item_id"];
        $this->LocationId = $row["location_id"];
	}

    public function LoadAll($orderBy = "a.item_id") {
		$this->connector->CommandText = "SELECT a.* FROM ic_item_location AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemLocation();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function LoadByItemProjectId($itemId,$projectId) {
    	$this->connector->CommandText = "SELECT a.* FROM ic_item_location AS a WHERE a.item_id = ?itemid And a.project_id = ?projectid";
		$this->connector->AddParameter("?itemid", $itemId);
        $this->connector->AddParameter("?projectid", $projectId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM ic_item_location AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO ic_item_location(item_id,location_id) VALUES(?item_id,?location_id)';
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?location_id", $this->LocationId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE ic_item_location SET item_id = ?item_id, location_id = ?location_id WHERE id = ?id';
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?location_id", $this->LocationId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM ic_item_location WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

}

