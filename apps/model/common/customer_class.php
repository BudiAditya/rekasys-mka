<?php
class CustomerClass extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $ClassCd;
	public $ClassDesc;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->ClassCd = $row["class_cd"];
		$this->ClassDesc = $row["class_desc"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return CustomerClass[]
	 */
	public function LoadAll($orderBy = "a.class_cd", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.* FROM sm_customerclass AS a ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.* FROM sm_customerclass AS a WHERE a.is_deleted = 0 ORDER BY $orderBy";
		}

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CustomerClass();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $id
	 * @return CustomerClass
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM sm_customerclass AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO sm_customerclass(class_cd,class_desc) VALUES(?class_cd,?class_desc)';
		$this->connector->AddParameter("?class_cd", $this->ClassCd);
        $this->connector->AddParameter("?class_desc", $this->ClassDesc);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE sm_customerclass SET class_cd = ?class_cd, class_desc = ?class_desc WHERE id = ?id';
		$this->connector->AddParameter("?class_cd", $this->ClassCd);
        $this->connector->AddParameter("?class_desc", $this->ClassDesc);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'UPDATE sm_customerclass SET is_deleted = 1 WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}
