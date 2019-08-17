<?php

class CreditorType extends EntityBase {
	public $Id;
    public $EntityId;
	public $EntityCd;
    public $ProjectId;
    public $UserUid;
    public $CreditorTypeCd;
    public $CreditorTypeDesc;
	public $AccControlId;
    public $AccControlNo;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->CreditorTypeCd = $row["creditortype_cd"];
        $this->CreditorTypeDesc = $row["creditortype_desc"];
        $this->AccControlId = $row["acc_control_id"];
        $this->AccControlNo = $row["acc_control_no"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return CreditorType[]
	 */
	public function LoadAll($orderBy = "a.creditortype_cd", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd, c.acc_no AS acc_control_no, c.acc_name AS acc_control_name FROM ap_creditortype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_acc_detail AS c ON a.acc_control_id = c.id ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CreditorType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $companyId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return CreditorType[]
	 */
	public function LoadByEntity($companyId, $orderBy = "a.creditortype_cd", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd, c.acc_no AS acc_control_no, c.acc_name AS acc_control_name FROM ap_creditortype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_acc_detail AS c ON a.acc_control_id = c.id WHERE a.entity_id = ?sbu ORDER BY $orderBy";
		$this->connector->AddParameter("?sbu", $companyId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CreditorType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return CreditorType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return CreditorType
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd, c.acc_no AS acc_control_no, c.acc_name AS acc_control_name FROM ap_creditortype AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_acc_detail AS c ON a.acc_control_id = c.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	/**
	 * @param int $id
	 * @return CreditorType
	 */
	public function LoadByCreditorId($id) {
		$this->connector->CommandText = "SELECT a.*, NULL AS entity_cd, c.acc_no AS acc_control_no, c.acc_name AS acc_control_name FROM ap_creditortype AS a LEFT JOIN cm_acc_detail AS c ON a.acc_control_id = c.id WHERE a.id = (SELECT aa.creditortype_id FROM ap_creditor_master AS aa WHERE aa.id = ?id)";
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
		$this->connector->CommandText = 'INSERT INTO ap_creditortype (entity_id, creditortype_cd, creditortype_desc, acc_control_id) VALUES(?entity_id, ?creditortype_cd, ?creditortype_desc, ?acc_control_id)';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?creditortype_cd", $this->CreditorTypeCd);
		$this->connector->AddParameter("?creditortype_desc", $this->CreditorTypeDesc);
        $this->connector->AddParameter("?acc_control_id", $this->AccControlId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE ap_creditortype 
SET entity_id = ?entity_id
	, creditortype_cd = ?creditortype_cd
	, creditortype_desc = ?creditortype_desc
	, acc_control_id = ?acc_control_id
WHERE id = ?id';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?creditortype_cd", $this->CreditorTypeCd);
		$this->connector->AddParameter("?creditortype_desc", $this->CreditorTypeDesc);
        $this->connector->AddParameter("?acc_control_id", $this->AccControlId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From ap_creditortype WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

}
