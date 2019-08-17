<?php

class TaxType extends EntityBase {
	public $Id;
    public $EntityId;
	public $TaxCode;
    public $TaxType;
    public $TaxRate = 0;
	public $PostAccId = 0;
    public $TempAccId = 0;
    public $TaxMode;
    public $IsDeductable = 0;
    public $CreatebyId;
    public $UpdatebyId;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->TaxCode = $row["tax_code"];
		$this->TaxType = $row["tax_type"];
        $this->TaxRate = $row["tax_rate"];
        $this->PostAccId = $row["post_acc_id"];
        $this->TempAccId = $row["temp_acc_id"];
        $this->TaxMode = $row["tax_mode"];
        $this->IsDeductable = $row["is_deductable"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return TaxType[]
	 */
	public function LoadAll($orderBy = "a.tax_type") {
		$this->connector->CommandText = "SELECT a.* FROM cm_taxtype AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxType();
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
	 * @return TaxType[]
	 */
	public function LoadByEntity($companyId, $orderBy = "a.tax_type") {
		$this->connector->CommandText = "SELECT a.* FROM cm_taxtype AS a WHERE a.entity_id = ?sbu ORDER BY $orderBy";
		$this->connector->AddParameter("?sbu", $companyId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByMode($companyId, $taxMode, $orderBy = "a.tax_type") {
        $this->connector->CommandText = "SELECT a.* FROM cm_taxtype AS a WHERE a.entity_id = ?sbu And a.tax_mode = ?mode ORDER BY $orderBy";
        $this->connector->AddParameter("?sbu", $companyId);
        $this->connector->AddParameter("?mode", $taxMode);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new TaxType();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $id
	 * @return TaxType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return TaxType
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_taxtype AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO cm_taxtype (temp_acc_id, entity_id, tax_code, tax_type, tax_rate, post_acc_id, tax_mode, is_deductable, createby_id, create_time) VALUES(?temp_acc_id, ?entity_id, ?tax_code, ?tax_type, ?tax_rate, ?post_acc_id, ?tax_mode, ?is_deductable, ?createby_id, now())';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?tax_code", $this->TaxCode);
        $this->connector->AddParameter("?tax_type", $this->TaxType);
		$this->connector->AddParameter("?tax_rate", $this->TaxRate);
        $this->connector->AddParameter("?post_acc_id", $this->PostAccId);
        $this->connector->AddParameter("?temp_acc_id", $this->TempAccId);
        $this->connector->AddParameter("?tax_mode", $this->TaxMode);
        $this->connector->AddParameter("?is_deductable", $this->IsDeductable);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE cm_taxtype 
SET entity_id = ?entity_id
	, tax_type = ?tax_type
	, tax_rate = ?tax_rate
	, post_acc_id = ?post_acc_id
	, temp_acc_id = ?temp_acc_id
	, tax_mode = ?tax_mode
	, tax_code = ?tax_code
	, is_deductable = ?is_deductable
	, updateby_id = ?updateby_id
	, update_time = now()
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?tax_code", $this->TaxCode);
        $this->connector->AddParameter("?tax_type", $this->TaxType);
        $this->connector->AddParameter("?tax_rate", $this->TaxRate);
        $this->connector->AddParameter("?post_acc_id", $this->PostAccId);
        $this->connector->AddParameter("?temp_acc_id", $this->TempAccId);
        $this->connector->AddParameter("?tax_mode", $this->TaxMode);
        $this->connector->AddParameter("?is_deductable", $this->IsDeductable);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From cm_taxtype WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

}
