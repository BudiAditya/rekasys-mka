<?php

class ArInvoiceType extends EntityBase {
	public $Id;
	public $EntityId;
	public $InvoicePrefix;
    public $InvoiceType;
	public $InvoiceTypeDescs;
	public $RevAccId = 0;
	public $TaxSchemeId = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->InvoiceType = $row["invoice_type"];
        $this->InvoicePrefix = $row["invoice_prefix"];
		$this->InvoiceTypeDescs = $row["invoice_type_descs"];
		$this->RevAccId = $row["rev_acc_id"];
        $this->TaxSchemeId = $row["taxscheme_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return ArInvoiceType[]
	 */
	public function LoadAll($orderBy = "a.invoice_type") {
		$this->connector->CommandText = "SELECT a.* FROM ar_invoicetype AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ArInvoiceType();
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
	 * @return ArInvoiceType[]
	 */
	public function LoadByEntity($companyId, $orderBy = "a.invoice_type") {
		$this->connector->CommandText = "SELECT a.* FROM ar_invoicetype AS a WHERE a.entity_id = ?sbu ORDER BY $orderBy";
		$this->connector->AddParameter("?sbu", $companyId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ArInvoiceType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return ArInvoiceType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return ArInvoiceType
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM ar_invoicetype AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO ar_invoicetype (entity_id, invoice_prefix, invoice_type, invoice_type_descs, rev_acc_id, taxscheme_id) VALUES(?entity_id, ?invoice_prefix, ?invoice_type, ?invoice_type_descs, ?rev_acc_id, ?taxscheme_id)';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?invoice_type", $this->InvoiceType);
        $this->connector->AddParameter("?invoice_prefix", $this->InvoicePrefix);
		$this->connector->AddParameter("?invoice_type_descs", $this->InvoiceTypeDescs);
		$this->connector->AddParameter("?rev_acc_id", $this->RevAccId);
        $this->connector->AddParameter("?taxscheme_id", $this->TaxSchemeId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE ar_invoicetype SET entity_id = ?entity_id, invoice_prefix = ?invoice_prefix, invoice_type = ?invoice_type, invoice_type_descs = ?invoice_type_descs, rev_acc_id = ?rev_acc_id, taxscheme_id = ?taxscheme_id WHERE id = ?id';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?invoice_type", $this->InvoiceType);
        $this->connector->AddParameter("?invoice_prefix", $this->InvoicePrefix);
		$this->connector->AddParameter("?invoice_type_descs", $this->InvoiceTypeDescs);
		$this->connector->AddParameter("?rev_acc_id", $this->RevAccId);
        $this->connector->AddParameter("?taxscheme_id", $this->TaxSchemeId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From ar_invoicetype WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}
