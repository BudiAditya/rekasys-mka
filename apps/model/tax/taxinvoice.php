<?php

class TaxInvoice extends EntityBase {
	public $Id;
    public $EntityId;
	public $SourceFrom;
    public $ReffNo;
    public $TaxInvoiceNo;
	public $TaxInvoiceDate;
    public $DbCrId;
    public $TaxTypeId;
    public $DppAmount = 0;
    public $TaxAmount = 0;
    public $TaxivStatus = 0;
    public $CreatebyId;
    public $UpdatebyId;
    public $TglLapor;
    public $PostbyId;
    public $VoucherNo;

    //helper
    public $TaxMode = 0;
    public $TaxRate = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->SourceFrom = $row["source_from"];
		$this->ReffNo = $row["reff_no"];
        $this->TaxInvoiceNo = $row["taxinvoice_no"];
        $this->TaxInvoiceDate = strtotime($row["taxinvoice_date"]);
        $this->TglLapor = strtotime($row["tgl_lapor"]);
        $this->DbCrId = $row["dbcr_id"];
        $this->TaxTypeId = $row["taxtype_id"];
        $this->DppAmount = $row["dpp_amount"];
        $this->TaxAmount = $row["tax_amount"];
        $this->TaxivStatus = $row["taxiv_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->PostbyId = $row["postby_id"];
        $this->TaxMode = $row["tax_mode"];
        $this->TaxRate = $row["tax_rate"];
        $this->VoucherNo = $row["voucher_no"];
	}

    public function FormatTaxInvoiceDate($format = HUMAN_DATE) {
        return is_int($this->TaxInvoiceDate) ? date($format, $this->TaxInvoiceDate) : null;
    }

    public function FormatTglLapor($format = HUMAN_DATE) {
        return is_int($this->TglLapor) ? date($format, $this->TglLapor) : null;
    }

    public function GetStatus(){
        if ($this->TaxivStatus == null) {
            return "DRAFT";
        }
        switch ($this->TaxivStatus) {
            case 0:
                return "DRAFT";
            case 1:
                return "POSTED";
            default:
                return "N.A.";
        }
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return ReffNo[]
	 */
	public function LoadAll($orderBy = "a.taxinvoice_no") {
		$this->connector->CommandText = "SELECT a.*,b.tax_mode,b.tax_rate FROM t_tax_invoice AS a JOIN cm_taxtype AS b ON a.taxtype_id = b.id ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxInvoice();
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
	 * @return ReffNo[]
	 */
	public function LoadByEntity($companyId, $orderBy = "a.taxinvoice_no") {
		$this->connector->CommandText = "SELECT a.*,b.tax_mode,b.tax_rate FROM t_tax_invoice AS a JOIN cm_taxtype AS b ON a.taxtype_id = b.id WHERE a.entity_id = ?sbu ORDER BY $orderBy";
		$this->connector->AddParameter("?sbu", $companyId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TaxInvoice();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return ReffNo
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return ReffNo
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.*,b.tax_mode,b.tax_rate FROM t_tax_invoice AS a JOIN cm_taxtype AS b ON a.taxtype_id = b.id  WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO t_tax_invoice (voucher_no,tgl_lapor, entity_id, source_from, reff_no, taxinvoice_no, taxinvoice_date, dbcr_id, taxtype_id, dpp_amount, tax_amount, taxiv_status, createby_id, create_time) VALUES(?voucher_no,?tgl_lapor, ?entity_id, ?source_from, ?reff_no, ?taxinvoice_no, ?taxinvoice_date, ?dbcr_id, ?taxtype_id, ?dpp_amount, ?tax_amount, ?taxiv_status, ?createby_id, now())';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?source_from", $this->SourceFrom);
        $this->connector->AddParameter("?reff_no", $this->ReffNo,"varchar");
		$this->connector->AddParameter("?taxinvoice_no", $this->TaxInvoiceNo,"varchar");
        $this->connector->AddParameter("?taxinvoice_date", $this->TaxInvoiceDate);
        $this->connector->AddParameter("?tgl_lapor", $this->TglLapor);
        $this->connector->AddParameter("?dbcr_id", $this->DbCrId);
        $this->connector->AddParameter("?taxtype_id", $this->TaxTypeId);
        $this->connector->AddParameter("?dpp_amount", $this->DppAmount);
        $this->connector->AddParameter("?tax_amount", $this->TaxAmount);
        $this->connector->AddParameter("?taxiv_status", $this->TaxivStatus);
        $this->connector->AddParameter("?voucher_no", $this->VoucherNo);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE t_tax_invoice 
SET entity_id = ?entity_id
	, reff_no = ?reff_no
	, taxinvoice_no = ?taxinvoice_no
	, taxinvoice_date = ?taxinvoice_date
	, dbcr_id = ?dbcr_id
	, source_from = ?source_from
	, taxtype_id = ?taxtype_id
	, dpp_amount = ?dpp_amount
	, tax_amount = ?tax_amount
	, taxiv_status = ?taxiv_status
	, updateby_id = ?updateby_id
	, update_time = now()
	, tgl_lapor = ?tgl_lapor
	, voucher_no = ?voucher_no
WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?source_from", $this->SourceFrom);
        $this->connector->AddParameter("?reff_no", $this->ReffNo,"varchar");
        $this->connector->AddParameter("?taxinvoice_no", $this->TaxInvoiceNo,"varchar");
        $this->connector->AddParameter("?taxinvoice_date", $this->TaxInvoiceDate);
        $this->connector->AddParameter("?tgl_lapor", $this->TglLapor);
        $this->connector->AddParameter("?dbcr_id", $this->DbCrId);
        $this->connector->AddParameter("?taxtype_id", $this->TaxTypeId);
        $this->connector->AddParameter("?dpp_amount", $this->DppAmount);
        $this->connector->AddParameter("?tax_amount", $this->TaxAmount);
        $this->connector->AddParameter("?taxiv_status", $this->TaxivStatus);
        $this->connector->AddParameter("?voucher_no", $this->VoucherNo);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id,$reffNo,$taxMode) {
	    $rs = null;
	    $sql = null;
		$this->connector->CommandText = 'Delete From t_tax_invoice WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs){
            if ($taxMode == 1){
                $sql = "Update t_ap_invoice_master AS a Set a.taxinvoice_no = '' Where a.reff_no = ?reffNo";
            }elseif ($taxMode == 2){
                $sql = "Update t_ar_invoice_master AS a Set a.taxinvoice_no = '' Where a.reff_no = ?reffNo";
            }
            $this->connector->CommandText = $sql;
            $this->connector->AddParameter("?reffNo", $reffNo);
            $this->connector->ExecuteNonQuery();
        }
        return $rs;
	}

	public function UpdateTaxInvoice(){
	    if ($this->TaxMode == 1){
	        $sql = "Update t_ap_invoice_master AS a Set a.taxinvoice_no = ?taxIvNo Where a.reff_no = ?invNo";
        }elseif ($this->TaxMode == 2){
            $sql = "Update t_ar_invoice_master AS a Set a.taxinvoice_no = ?taxIvNo Where a.reff_no = ?invNo";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?taxIvNo", $this->TaxInvoiceNo);
        $this->connector->AddParameter("?invNo", $this->ReffNo);
        return $this->connector->ExecuteNonQuery();
    }

    public function Post($id) {
        $this->connector->CommandText = "UPDATE t_tax_invoice SET voucher_no = ?noVoucher, taxiv_status = 1, postby_id = ?user, post_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        $this->connector->AddParameter("?noVoucher", $this->VoucherNo);
        $this->connector->AddParameter("?user", $this->UpdatebyId);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function UnPost($id) {
        $this->connector->CommandText = "UPDATE t_tax_invoice SET voucher_no = '', taxiv_status = 0, postby_id = null, post_time = null, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        $this->connector->AddParameter("?user", $this->UpdatebyId);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
