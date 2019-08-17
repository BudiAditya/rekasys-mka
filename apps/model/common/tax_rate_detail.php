<?php
class TaxRateDetail extends EntityBase {
	public $Id;
	public $TaxSchId;
	public $TaxCd;
	public $TaxType;
	public $TaxTarif;
	public $AccNoId;
	public $AccNo;
	public $ReversalAccNoId;
	public $ReversalAccNo;
	public $Deductable;

	// Helper
	public $MarkedForDeletion = false;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->TaxSchId = $row["taxsch_id"];
		$this->TaxCd = $row["tax_cd"];
		$this->TaxType = $row["tax_type"];
		$this->TaxTarif = $row["tax_tarif"];
		$this->AccNoId = $row["acc_no_id"];
		$this->AccNo = $row["acc_no"];
		$this->ReversalAccNoId = $row["reversal_acc_no_id"];
		$this->ReversalAccNo = $row["reversal_acc_no"];
		$this->Deductable = $row["deductable"];
	}

	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.acc_no, c.acc_no AS reversal_acc_no
FROM cm_taxschdetail AS a
 	LEFT JOIN cm_acc_detail AS b ON a.acc_no_id = b.id
 	LEFT JOIN cm_acc_detail AS c ON a.acc_no_id = c.id
WHERE a.id = ?id";
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
	 * Mencari Tax Rate Detail berdasarkan Tax Rate Id
	 *
	 * @param $tsi
	 * @return TaxRateDetail[]
	 */
	public function LoadByTaxRateId($tsi) {
		$this->connector->CommandText =
"SELECT a.*, b.acc_no, c.acc_no AS reversal_acc_no
FROM cm_taxschdetail AS a
 	LEFT JOIN cm_acc_detail AS b ON a.acc_no_id = b.id
 	LEFT JOIN cm_acc_detail AS c ON a.acc_no_id = c.id
WHERE a.taxsch_id = ?tsi ORDER BY a.tax_cd";
		$this->connector->AddParameter("?tsi", $tsi);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxRateDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO cm_taxschdetail(taxsch_id,tax_cd,tax_type,tax_tarif,deductable,acc_no_id,reversal_acc_no_id)
VALUES(?taxsch_id,?tax_cd,?tax_type,?tax_tarif,?deductable,?acc_no,?acc_no_reversal)';
		$this->connector->AddParameter("?taxsch_id", $this->TaxSchId);
		$this->connector->AddParameter("?tax_cd", $this->TaxCd);
		$this->connector->AddParameter("?tax_type", $this->TaxType);
		$this->connector->AddParameter("?tax_tarif", $this->TaxTarif);
		$this->connector->AddParameter("?acc_no", $this->AccNoId);
		$this->connector->AddParameter("?acc_no_reversal", $this->ReversalAccNoId);
		$this->connector->AddParameter("?deductable", $this->Deductable);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_taxschdetail SET
		taxsch_id = ?taxsch_id,
		tax_cd = ?tax_cd,
		tax_type = ?tax_type,
		tax_tarif = ?tax_tarif,
		acc_no_id = ?acc_no,
		reversal_acc_no_id = ?acc_no_reversal,
		deductable = ?deductable
WHERE id = ?id';
		$this->connector->AddParameter("?taxsch_id", $this->TaxSchId);
		$this->connector->AddParameter("?tax_cd", $this->TaxCd);
		$this->connector->AddParameter("?tax_type", $this->TaxType);
		$this->connector->AddParameter("?tax_tarif", $this->TaxTarif);
		$this->connector->AddParameter("?acc_no", $this->AccNoId);
		$this->connector->AddParameter("?acc_no_reversal", $this->ReversalAccNoId);
		$this->connector->AddParameter("?deductable", $this->Deductable);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM cm_taxschdetail WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// End of file: tax_rate_detail.php
