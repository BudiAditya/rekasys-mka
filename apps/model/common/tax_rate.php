<?php
require_once("tax_rate_detail.php");

class TaxRate extends EntityBase {
	// master variables
	public $Id;
	public $EntityId;
	public $EntityCd;
	public $TaxSchCd;
	public $TaxSchDesc;
	public $InclExcl;
    public $TaxMode;

	// Helper
	/** @var TaxRateDetail[] */
	public $TaxRateDetails = array();

	public function __construct($id = null) {
		parent::__construct();

		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillMasterProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->TaxSchCd = $row["taxsch_cd"];
		$this->TaxSchDesc = $row["taxsch_desc"];
		$this->InclExcl = $row["incl_excl"];
        $this->TaxMode = $row["tax_mode"];
	}

	/**
	 * @return TaxRateDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->TaxRateDetails;
		}

		$detail = new TaxRateDetail();
		$this->TaxRateDetails = $detail->LoadByTaxRateId($this->Id);
		return $this->TaxRateDetails;
	}

	/**
	 * @param string $orderBy
	 * @return TaxRate[]
	 */
	public function LoadAll($orderBy = "a.taxsch_cd") {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_taxschmaster AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxRate();
				$temp->FillMasterProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $id
	 * @return TaxRate
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return TaxRate
	 */
	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_taxschmaster AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillMasterProperties($row);
		return $this;
	}

	/**
	 * @param int $eti
	 * @return TaxRate[]
	 */
	public function LoadByEntityId($eti) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_taxschmaster AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.entity_id = ?eti
ORDER BY a.taxsch_cd";
		$this->connector->AddParameter("?eti", $eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxRate();
				$temp->FillMasterProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $txm
	 * @return TaxRate[]
	 */
	public function LoadByMode($txm) {
        $this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_taxschmaster AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.tax_mode = ?tax_mode
ORDER BY a.taxsch_cd";
        $this->connector->AddParameter("?tax_mode", $txm);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new TaxRate();
                $temp->FillMasterProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO cm_taxschmaster(entity_id,taxsch_cd,taxsch_desc,incl_excl,tax_mode)
VALUES(?entity_id,?taxsch_cd,?taxsch_desc,?incl_excl,?tax_mode)';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?taxsch_cd", $this->TaxSchCd, "varchar");
		$this->connector->AddParameter("?taxsch_desc", $this->TaxSchDesc);
		$this->connector->AddParameter("?incl_excl", $this->InclExcl);
        $this->connector->AddParameter("?tax_mode", $this->TaxMode);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID()";
			$this->Id = $this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_taxschmaster SET
	entity_id = ?entity_id,
	taxsch_cd = ?taxsch_cd,
	taxsch_desc = ?taxsch_desc,
	incl_excl = ?incl_excl,
	tax_mode = ?tax_mode
WHERE id = ?id';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?taxsch_cd", $this->TaxSchCd, "varchar");
		$this->connector->AddParameter("?taxsch_desc", $this->TaxSchDesc);
		$this->connector->AddParameter("?incl_excl", $this->InclExcl);
        $this->connector->AddParameter("?tax_mode", $this->TaxMode);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM cm_taxschmaster WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}
