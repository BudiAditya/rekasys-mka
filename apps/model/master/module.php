<?php
/**
 * Created by PhpStorm.
 * User: Budi
 * Date: 05 Des 11
 * Time: 10:56:08
 * To change this template use File | Settings | File Templates.
 */
class Module extends EntityBase {
	public $Id;
	public $ModuleCd;
	public $ModuleName;

	// Helper Variable

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->ModuleCd = $row["module_cd"];
		$this->ModuleName = $row["module_name"];
	}

    public function LoadAll($orderBy = "a.module_cd", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM sys_module AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Module();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM sys_module AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO sys_module(module_cd,module_name) VALUES(?module_cd,?module_name)';
		$this->connector->AddParameter("?module_cd", $this->ModuleCd);
        $this->connector->AddParameter("?module_name", $this->ModuleName);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE sys_module SET module_cd = ?module_cd, module_name = ?module_name WHERE id = ?id';
		$this->connector->AddParameter("?module_cd", $this->ModuleCd);
        $this->connector->AddParameter("?module_name", $this->ModuleName);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From sys_module WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}

?>

