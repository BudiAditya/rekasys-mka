<?php

require_once("invoice_detail.php");

class Invoice extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $InvoiceStatusCodes = array(
		0 => "DRAFT",
		1 => "APPROVED",
        2 => "POSTED",
		3 => "VOID"
	);

    public static $CollectStatusCodes = array(
        0 => "ON HOLD",
        1 => "ON PROCESS",
        2 => "PAID",
        3 => "VOID"
    );

	public $Id;
    public $IsDeleted = false;
    public $EntityId;
    public $ProjectId;
	public $InvoiceNo;
	public $InvoiceDate;
    public $DebtorId = 0;
    public $InvoiceType = 1;
	public $InvoiceDescs;
	public $ExSoNo;
	public $ReffNo;
	public $BaseAmount = 0;
    public $Disc1Pct = 0;
    public $Disc1Amount = 0;
    public $Disc2Pct = 0;
    public $Disc2Amount = 0;
    public $VatPct = 10;
	public $VatAmount = 0;
    public $WhtPct = 0;
    public $WhtAmount = 0;
    public $OtherCosts = 0;
    public $OtherCostsAmount = 0;
	public $PaidAmount = 0;
	public $DueDate;
    public $CreditTerms = 0;
    public $InvoiceStatus = 0;
	public $CreatebyId = 0;
	public $CreateTime;
	public $UpdatebyId = 0;
	public $UpdateTime;
    public $PaymentType = 0;
    public $ApproveTime;
    public $ApprovebyId = 0;
    public $ApproveStatus = 0;
    public $ApproveReason = '-';
    public $TaxInvoiceNo;

	/** @var InvoiceDetail[] */
	public $Details = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
        $this->Id = $row["id"];
        $this->IsDeleted = $row["is_deleted"] == 1;
        $this->EntityId = $row["entity_id"];
        $this->ProjectId = $row["project_id"];
        $this->InvoiceNo = $row["invoice_no"];
        $this->InvoiceDate = strtotime($row["invoice_date"]);
        $this->DebtorId = $row["debtor_id"];
        $this->InvoiceType = $row["invoice_type"];
        $this->InvoiceDescs = $row["invoice_descs"];
        $this->ExSoNo = $row["ex_so_no"];
        $this->ReffNo = $row["reff_no"];
        $this->BaseAmount = $row["base_amount"];
        $this->Disc1Pct = $row["disc1_pct"];
        $this->Disc1Amount = $row["disc1_amount"];
        $this->Disc2Pct = $row["wht_pct"];
        $this->Disc2Amount = $row["wht_amount"];
        $this->VatPct = $row["vat_pct"];
        $this->VatAmount = $row["vat_amount"];
        $this->WhtPct = $row["wht_pct"];
        $this->WhtAmount = $row["wht_amount"];
        $this->OtherCosts = $row["other_costs"];
        $this->OtherCostsAmount = $row["other_costs_amount"];
        $this->CreditTerms = $row["credit_terms"];
        $this->DueDate = strtotime($row["due_date"]);
        $this->InvoiceStatus = $row["invoice_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
        $this->PaymentType = $row["payment_type"];
        $this->ApprovebyId = $row["approveby_id"];
        $this->ApproveTime = $row["approve_time"];
        $this->ApproveStatus = $row["approve_status"];
        $this->ApproveReason = $row["approve_reason"];
        $this->PaidAmount = $row["paid_amount"];
        $this->TaxInvoiceNo = $row["taxinvoice_no"];
	}

	public function FormatInvoiceDate($format = HUMAN_DATE) {
		return is_int($this->InvoiceDate) ? date($format, $this->InvoiceDate) : date($format, strtotime(date('Y-m-d')));
	}

    public function FormatDueDate($format = HUMAN_DATE) {
        return is_int($this->DueDate) ? date($format, $this->DueDate) : date($format, strtotime(date('Y-m-d')));
    }

    public function GetStatus() {
        if ($this->InvoiceStatus == null) {
            return null;
        }
        switch ($this->InvoiceStatus) {
            case 0:
                return "DRAFT";
            case 1:
                return "APPROVED";
            case 2:
                return "POSTED";
            case 3:
                return "VOID";
            default:
                return "N.A.";
        }
    }

	/**
	 * @return InvoiceDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new InvoiceDetail();
		$this->Details = $detail->LoadByInvoiceId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Invoice
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByInvoiceNo($invNo) {
		$this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_master AS a WHERE a.invoice_no = ?invNo";
		$this->connector->AddParameter("?invNo", $invNo);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByReffNo($invNo) {
        $this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_master AS a WHERE a.reff_no = ?invNo";
        $this->connector->AddParameter("?invNo", $invNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadByEntityId($entityId) {
        $this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_master AS a WHERE a.entity_id = ?entityId";
        $this->connector->AddParameter("?entityId", $entityId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Invoice();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByProjectId($projectId) {
        $this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_master AS a.project_id = ?projectId";
        $this->connector->AddParameter("?projectId", $projectId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Invoice();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    //$reports = $invoice->Load4Reports($sProjectId,$sDebtorId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate);
    public function Load4Reports($entityId, $projectId = 0, $debtorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ar_invoice_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($debtorId > 0){
            $sql.= " and a.debtor_id = ".$debtorId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Order By a.invoice_date,a.invoice_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($entityId, $projectId = 0, $debtorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,b.item_code,b.item_descs,b.qty,b.price,b.disc_formula,b.disc_amount,b.sub_total FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail b On a.invoice_no = b.invoice_no";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($debtorId > 0){
            $sql.= " and a.debtor_id = ".$debtorId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Order By a.invoice_date,a.invoice_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($entityId, $projectId = 0, $debtorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT b.item_code,b.item_descs,c.bsatkecil as satuan,b.price, coalesce(sum(b.qty),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total, sum(Case When a.vat_pct > 0 Then Round(b.sub_total * (a.vat_pct/100),0) Else 0 End) as sum_tax";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.invoice_no = b.invoice_no Left Join m_barang AS c On b.item_code = c.bkode";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($debtorId > 0){
            $sql.= " and a.debtor_id = ".$debtorId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Group By b.item_code,b.item_descs,c.bsatkecil,b.price Order By b.item_descs,b.item_code,b.price";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem1($entityId, $projectId = 0, $debtorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT b.item_code,b.item_descs,c.bsatkecil as satuan, coalesce(sum(b.qty),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total, sum(Case When a.vat_pct > 0 Then Round(b.sub_total * (a.vat_pct/100),0) Else 0 End) as sum_tax";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.invoice_no = b.invoice_no Left Join m_barang AS c On b.item_code = c.bkode";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($debtorId > 0){
            $sql.= " and a.debtor_id = ".$debtorId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Group By b.item_code,b.item_descs,c.bsatkecil Order By b.item_descs,b.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetUnpaidInvoices($debtorId = 0,$invoiceNo = null) {
        $sql = "SELECT a.* FROM vw_ar_invoice_master AS a";
        $sql.= " Where a.invoice_status = 2 and a.is_deleted = 0 and a.balance_amount > 0 And a.invoice_no = ?invoiceNo";
        if ($debtorId > 0){
            $sql.= " And a.debtor_id = ?debtorId";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?debtorId", $debtorId);
        $this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ar_invoice_master (taxinvoice_no,reff_no,invoice_type,entity_id, project_id, invoice_no, invoice_date, debtor_id, invoice_descs, ex_so_no, base_amount, disc1_pct, disc1_amount, vat_pct, vat_amount, wht_pct, wht_amount, other_costs, other_costs_amount, payment_type, credit_terms, invoice_status, createby_id, create_time, due_date) ";
        $sql.= "VALUES(?taxinvoice_no,?reff_no,?invoice_type,?entity_id, ?project_id, ?invoice_no, ?invoice_date, ?debtor_id, ?invoice_descs, ?ex_so_no, ?base_amount, ?disc1_pct, ?disc1_amount, ?vat_pct, ?vat_amount, ?wht_pct, ?wht_amount, ?other_costs, ?other_costs_amount, ?payment_type, ?credit_terms, ?invoice_status, ?createby_id, now(), ?due_date)";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?invoice_no", $this->InvoiceNo, "char");
		$this->connector->AddParameter("?invoice_date", $this->FormatInvoiceDate(SQL_DATEONLY));
        $this->connector->AddParameter("?due_date", $this->FormatDueDate(SQL_DATEONLY));
        $this->connector->AddParameter("?debtor_id", $this->DebtorId);
		$this->connector->AddParameter("?invoice_descs", $this->InvoiceDescs);
        $this->connector->AddParameter("?ex_so_no", $this->ExSoNo);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?base_amount", str_replace(",","",$this->BaseAmount));
        $this->connector->AddParameter("?disc1_pct", str_replace(",","",$this->Disc1Pct));
        $this->connector->AddParameter("?disc1_amount", str_replace(",","",$this->Disc1Amount));
        $this->connector->AddParameter("?wht_pct", str_replace(",","",$this->WhtPct));
        $this->connector->AddParameter("?wht_amount", str_replace(",","",$this->WhtAmount));
        $this->connector->AddParameter("?vat_pct", str_replace(",","",$this->VatPct));
        $this->connector->AddParameter("?vat_amount", str_replace(",","",$this->VatAmount));
        $this->connector->AddParameter("?other_costs", str_replace(",","",$this->OtherCosts));
        $this->connector->AddParameter("?other_costs_amount", str_replace(",","",$this->OtherCostsAmount));
        $this->connector->AddParameter("?paid_amount", str_replace(",","",$this->PaidAmount));
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?invoice_status", $this->InvoiceStatus);
        $this->connector->AddParameter("?invoice_type", $this->InvoiceType);
        $this->connector->AddParameter("?taxinvoice_no", $this->TaxInvoiceNo);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ar_invoice_master SET
	project_id = ?project_id
	, entity_id = ?entity_id
	, invoice_no = ?invoice_no
	, invoice_date = ?invoice_date
	, debtor_id = ?debtor_id
	, invoice_descs = ?invoice_descs
	, ex_so_no = ?ex_so_no
	, base_amount = ?base_amount
	, disc1_pct = ?disc1_pct
	, disc1_amount = ?disc1_amount
	, wht_pct = ?wht_pct
	, wht_amount = ?wht_amount
	, vat_pct = ?vat_pct
	, vat_amount = ?vat_amount
	, other_costs = ?other_costs
	, other_costs_amount = ?other_costs_amount
	, paid_amount = ?paid_amount
	, payment_type = ?payment_type
	, credit_terms = ?credit_terms
	, invoice_status = ?invoice_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
	, invoice_type = ?invoice_type
	, due_date = ?due_date
	, reff_no = ?reff_no
	, taxinvoice_no = ?taxinvoice_no
WHERE id = ?id";
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?invoice_no", $this->InvoiceNo, "char");
        $this->connector->AddParameter("?invoice_date", $this->FormatInvoiceDate(SQL_DATEONLY));
        $this->connector->AddParameter("?due_date", $this->FormatDueDate(SQL_DATEONLY));
        $this->connector->AddParameter("?debtor_id", $this->DebtorId);
        $this->connector->AddParameter("?invoice_descs", $this->InvoiceDescs);
        $this->connector->AddParameter("?ex_so_no", $this->ExSoNo);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?base_amount", str_replace(",","",$this->BaseAmount));
        $this->connector->AddParameter("?disc1_pct", str_replace(",","",$this->Disc1Pct));
        $this->connector->AddParameter("?disc1_amount", str_replace(",","",$this->Disc1Amount));
        $this->connector->AddParameter("?wht_pct", str_replace(",","",$this->WhtPct));
        $this->connector->AddParameter("?wht_amount", str_replace(",","",$this->WhtAmount));
        $this->connector->AddParameter("?vat_pct", str_replace(",","",$this->VatPct));
        $this->connector->AddParameter("?vat_amount", str_replace(",","",$this->VatAmount));
        $this->connector->AddParameter("?other_costs", str_replace(",","",$this->OtherCosts));
        $this->connector->AddParameter("?other_costs_amount", str_replace(",","",$this->OtherCostsAmount));
        $this->connector->AddParameter("?paid_amount", str_replace(",","",$this->PaidAmount));
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?invoice_status", $this->InvoiceStatus);
        $this->connector->AddParameter("?invoice_type", $this->InvoiceType);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?taxinvoice_no", $this->TaxInvoiceNo);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1){
            $this->RecalculateInvoiceMaster($id);
        }
        return $rs;
	}

	public function Delete($id) {
        //baru hapus invoicenya
		$this->connector->CommandText = "Delete From t_ar_invoice_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        //baru hapus invoicenya
        $this->connector->CommandText = "Update t_ar_invoice_master a Set a.is_deleted = 1, a.invoice_status = 3, a.updateby_id = ?updater WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updater", $this->UpdatebyId);
        $rsz =  $this->connector->ExecuteNonQuery();
        return $rsz;
    }

    public function Approve($id = 0, $uid = 0){
        $this->connector->CommandText = "Update t_ar_invoice_master a Set a.invoice_status = 1, a.updateby_id = ?updater, a.update_time = now(), a.approveby_id = ?updater, a.approve_time = now() WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updater", $uid);
        $rsz =  $this->connector->ExecuteNonQuery();
        return $rsz;
    }

    public function Unapprove($id = 0, $uid = 0){
        $this->connector->CommandText = "Update t_ar_invoice_master a Set a.invoice_status = 0, a.updateby_id = ?updater, a.update_time = now(), a.approveby_id = null, a.approve_time = null WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updater", $uid);
        $rsz =  $this->connector->ExecuteNonQuery();
        return $rsz;
    }

    public function Posting($id = 0, $uid = 0){
        $this->connector->CommandText = "SELECT fcArInvoicePosting(?id,?uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unposting($id = 0, $uid = 0){
        $this->connector->CommandText = "SELECT fcArInvoiceUnposting(?id,?uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

   public function RecalculateInvoiceMaster($invoiceId){
        //kosongkan dulu
        $sql = 'Update t_ar_invoice_master a Set a.paid_amount = 0, a.base_amount = 0, a.vat_amount = 0, a.disc1_amount = 0, a.wht_amount = 0 Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //isi base amount
        $sql = 'Update t_ar_invoice_master a Join (Select c.invoice_id, sum(c.qty * c.price) As sumPrice From t_ar_invoice_detail c Group By c.invoice_id) b On a.id = b.invoice_id Set a.base_amount = b.sumPrice Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //hitung ppn
        $sql = 'Update t_ar_invoice_master a Set a.vat_amount = if(a.vat_pct > 0 And (a.base_amount - a.disc1_amount) > 0,round((a.base_amount - a.disc1_amount)  * (a.vat_pct/100),0),0) Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //hitung pph
        $sql = 'Update t_ar_invoice_master a Set a.wht_amount = if(a.wht_pct > 0 And (a.base_amount - a.disc1_amount) > 0,round((a.base_amount - a.disc1_amount)  * (a.wht_pct/100),0),0) Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //hitung sisa pembayaran jika ada
        $sql = 'Update t_ar_invoice_master a Set a.paid_amount = ((a.base_amount - a.disc1_amount) + a.vat_amount + a.other_costs_amount) - a.wht_amount Where a.id = ?invoiceId And a.payment_type = 0;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetInvoiceItemRow($invoiceId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ar_invoice_detail as a Where a.invoice_id = ?invoiceId;";
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function GetJSonInvoices($projectId,$debtorId) {
        $sql = "SELECT a.id,a.invoice_no,a.invoice_date,a.vat_pct FROM t_ar_invoice_master as a Where a.invoice_status <> 3 And a.is_deleted = 0 And a.project_id = ".$projectId." And a.debtor_id = ".$debtorId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.invoice_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetJSonInvoiceItems($invoiceId = 0) {
        $sql = "SELECT a.id,a.item_id,a.item_code,a.item_descs,a.qty - a.qty_return as qty_jual,b.bsatbesar as satuan,round(a.sub_total/a.qty,0) as price,a.vat_pct,c.entity_id FROM t_ar_invoice_detail AS a";
        $sql.= " JOIN m_barang AS b ON a.item_code = b.bkode JOIN t_ar_invoice_master AS c ON a.invoice_id = c.id Where (a.qty - a.qty_return) > 0 And a.invoice_id = ".$invoiceId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.invoice_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function UpdatePrintCounter($invoiceId = 0,$userId = 0){
        $sql = "Update t_ar_invoice_master a Set a.print_count = a.print_count +1,a.lastprintby_id = $userId,a.lastprint_time = now() Where a.id = $invoiceId;";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function Load4ProfitTransaksi($entityId, $projectId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.cabang_code,a.invoice_date,a.invoice_no,a.debtor_name,a.invoice_descs,a.total_amount,a.real_total_hpp as total_hpp,a.total_return FROM vw_ar_invoice_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_status <> 3 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        $sql.= " Order By a.invoice_date,a.invoice_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ProfitTanggal($entityId, $projectId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.project_id,a.cabang_code,a.invoice_date,sum(a.total_amount) as sumSale,sum(a.real_total_hpp) as sumHpp,sum(a.total_return) as sumReturn FROM vw_ar_invoice_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_status <> 3 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        $sql.= " Group By a.invoice_date";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ProfitBulan($entityId, $projectId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.project_id,a.cabang_code,Year(a.invoice_date) as tahun,Month(a.invoice_date) as bulan,sum(a.total_amount) as sumSale,sum(a.real_total_hpp) as sumHpp,sum(a.total_return) as sumReturn FROM vw_ar_invoice_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_status <> 3 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        $sql.= " Group By Year(a.invoice_date),Month(a.invoice_date)";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ProfitDetail($entityId, $projectId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,b.item_code,b.item_descs,b.qty,b.price,b.disc_formula,b.disc_amount,b.sub_total,b.item_hpp FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail b On a.invoice_no = b.invoice_no";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_status <> 3 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        $sql.= " Order By a.invoice_date,a.invoice_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ProfitItem($entityId, $projectId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT b.item_code,b.item_descs,c.bsatkecil as satuan,coalesce(sum(b.qty),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total,coalesce(sum(b.qty_return * (b.sub_total/b.qty)),0) as sum_return,coalesce(sum((b.qty - b.qty_return) * b.item_hpp),0) as sum_hpp";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.invoice_no = b.invoice_no Left Join m_barang AS c On b.item_code = c.bkode";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($projectId > 0){
            $sql.= " and a.project_id = ".$projectId;
        }else{
            $sql.= " and a.entity_id = ".$entityId;
        }
        $sql.= " Group By b.item_code,b.item_descs,c.bsatkecil Order By b.item_descs,b.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    //function post so detail into sales invoice
    public function PostSoDetail2Invoice($id,$invno,$sono){
        $nrt = 0;
        $sql = "Update t_ar_invoice_master a Join t_ar_so_master b On a.ex_so_no = b.so_no";
        $sql.= " Set a.base_amount = b.base_amount, a.vat_pct = b.vat_pct, a.vat_amount = b.vat_amount, a.disc1_pct = b.disc1_pct, a.disc1_amount = b.disc1_amount, a.wht_pct = b.wht_pct, a.wht_amount = b.wht_amount, a.other_costs = b.other_costs, a.other_costs_amount = b.other_costs_amount";
        $sql.= " Where a.id = $id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        $sql = "Insert Into t_ar_invoice_detail (ex_so_no,invoice_id,project_id,invoice_no,item_id,item_code,item_descs,qty,price,disc_formula,disc_amount,sub_total)";
        $sql.= " Select a.so_no,$id,a.project_id,'".$invno."',a.item_id,a.item_code,a.item_descs,a.order_qty-a.send_qty,a.price,a.disc_formula,a.disc_amount,a.sub_total From t_ar_so_detail AS a Where a.so_no = '".$sono."' And a.order_qty > a.send_qty Order By a.id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs){
            $nrt = 1;
            #Post detailnya
            $this->connector->CommandText = "SELECT fc_ar_invoicedetail_all_post($id) As valresult;";
            if ($this->connector->ExecuteQuery()) {
                $nrt = 2;
            }
            /* -- revised 2018-08-17 --
            #Update SO Qty received
            $this->connector->CommandText = "Update t_ar_so_detail AS a Set a.send_qty = a.order_qty Where a.so_no = '".$sono."'";
            if ($this->connector->ExecuteQuery()){
                $nrt = 3;
            }
            #Update PO status
            $sql = "Update t_ar_so_master AS a Set a.so_status = 2 Where a.so_no = '".$sono."'";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();
            if ($this->connector->ExecuteQuery()){
                $nrt = 4;
            }
            */
            #Autohecking sales order sended qty and update so status
            $this->connector->CommandText = "SELECT fc_ar_so_checkstatus('".$sono."') As valresult;";
            if ($this->connector->ExecuteQuery()) {
                $nrt = 3;
            }
        }
        return $nrt;
    }

    public function UpdateCustomerOutstanding($projectId = 0,$debtorId = 0) {
        $this->connector->CommandText = "SELECT fcArUpdateCustomerOutstanding($projectId,$debtorId) As valresult;";
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function GetJSonInvoiceNonFakturPajak($debtorId = 0) {
        $sql = "SELECT a.reff_no,a.invoice_date,a.invoice_no FROM t_ar_invoice_master AS a Where (Length(trim(a.taxinvoice_no)) < 5 Or isnull(a.taxinvoice_no)) And a.is_deleted = 0 And a.invoice_status = 2 And a.debtor_id = ?debtorId";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?debtorId", $debtorId);
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.reff_no";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        //$result = array('total'=>$data['count'],'rows'=>$rows);
        return $rows;
    }

}


// End of File: estimasi.php
