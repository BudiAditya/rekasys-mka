<?php
namespace Ap;

require_once("invoice_detail.php");
/**
 * Namespace already supported by our LIVE SERVER
 */
class Invoice extends \EntityBase {
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
    public $CreditorId = 0;
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
    public $GrnNo;
    public $TaxInvoiceNo;
    public $TaxType1Id;
    public $TaxType2Id;
    public $TaxType3Id;
    public $Tax1Rate = 0;
    public $Tax2Rate = 0;
    public $Tax3Rate = 0;
    public $Tax1Amount = 0;
    public $Tax2Amount = 0;
    public $Tax3Amount = 0;

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
        $this->CreditorId = $row["creditor_id"];
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
        $this->GrnNo = $row["grn_no"];
        $this->TaxInvoiceNo = $row["taxinvoice_no"];
        $this->TaxType1Id = $row["taxtype1_id"];
        $this->TaxType2Id = $row["taxtype2_id"];
        $this->TaxType3Id = $row["taxtype3_id"];
        $this->Tax1Rate = $row["tax1_rate"];
        $this->Tax2Rate = $row["tax2_rate"];
        $this->Tax3Rate = $row["tax3_rate"];
        $this->Tax1Amount = $row["tax1_amount"];
        $this->Tax2Amount = $row["tax2_amount"];
        $this->Tax3Amount = $row["tax3_amount"];
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
		$this->connector->CommandText = "SELECT a.* FROM t_ap_invoice_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM t_ap_invoice_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByInvoiceNo($invNo) {
		$this->connector->CommandText = "SELECT a.* FROM t_ap_invoice_master AS a WHERE a.invoice_no = ?invNo";
		$this->connector->AddParameter("?invNo", $invNo);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByReffNo($invNo) {
        $this->connector->CommandText = "SELECT a.* FROM t_ap_invoice_master AS a WHERE a.reff_no = ?invNo";
        $this->connector->AddParameter("?invNo", $invNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadByEntityId($entityId) {
        $this->connector->CommandText = "SELECT a.* FROM t_ap_invoice_master AS a WHERE a.entity_id = ?entityId";
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
        $this->connector->CommandText = "SELECT a.* FROM t_ap_invoice_master AS a.project_id = ?projectId";
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

    //$reports = $invoice->Load4Reports($sProjectId,$sCreditorId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate);
    public function Load4Reports($entityId, $projectId = 0, $creditorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_t_ap_invoice_master AS a";
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
        if ($creditorId > 0){
            $sql.= " and a.creditor_id = ".$creditorId;
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

    public function Load4ReportsDetail($entityId, $projectId = 0, $creditorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,b.item_code,b.item_descs,b.qty,b.price,b.disc_formula,b.disc_amount,b.sub_total FROM vw_t_ap_invoice_master AS a Join t_ap_invoice_detail b On a.invoice_no = b.invoice_no";
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
        if ($creditorId > 0){
            $sql.= " and a.creditor_id = ".$creditorId;
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

    public function Load4ReportsRekapItem($entityId, $projectId = 0, $creditorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT b.item_code,b.item_descs,c.bsatkecil as satuan,b.price, coalesce(sum(b.qty),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total, sum(Case When a.vat_pct > 0 Then Round(b.sub_total * (a.vat_pct/100),0) Else 0 End) as sum_tax";
        $sql.= " FROM vw_t_ap_invoice_master AS a Join t_ap_invoice_detail AS b On a.invoice_no = b.invoice_no Left Join m_barang AS c On b.item_code = c.bkode";
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
        if ($creditorId > 0){
            $sql.= " and a.creditor_id = ".$creditorId;
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

    public function Load4ReportsRekapItem1($entityId, $projectId = 0, $creditorId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT b.item_code,b.item_descs,c.bsatkecil as satuan, coalesce(sum(b.qty),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total, sum(Case When a.vat_pct > 0 Then Round(b.sub_total * (a.vat_pct/100),0) Else 0 End) as sum_tax";
        $sql.= " FROM vw_t_ap_invoice_master AS a Join t_ap_invoice_detail AS b On a.invoice_no = b.invoice_no Left Join m_barang AS c On b.item_code = c.bkode";
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
        if ($creditorId > 0){
            $sql.= " and a.creditor_id = ".$creditorId;
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

    public function GetUnpaidInvoices($creditorId = 0,$invoiceNo = null) {
        $sql = "SELECT a.* FROM vw_t_ap_invoice_master AS a";
        $sql.= " Where a.invoice_status = 2 and a.is_deleted = 0 and a.balance_amount > 0 And a.invoice_no = ?invoiceNo";
        if ($creditorId > 0){
            $sql.= " And a.creditor_id = ?creditorId";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?creditorId", $creditorId);
        $this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ap_invoice_master (tax1_rate,tax2_rate,tax3_rate,taxtype1_id,taxtype2_id,taxtype3_id,tax1_amount,tax2_amount,tax3_amount,taxinvoice_no,grn_no,reff_no,invoice_type,entity_id, project_id, invoice_no, invoice_date, creditor_id, invoice_descs, ex_so_no, base_amount, disc1_pct, disc1_amount, vat_pct, vat_amount, wht_pct, wht_amount, other_costs, other_costs_amount, payment_type, credit_terms, invoice_status, createby_id, create_time, due_date) ";
        $sql.= "VALUES(?tax1_rate,?tax2_rate,?tax3_rate,?taxtype1_id,?taxtype2_id,?taxtype3_id,?tax1_amount,?tax2_amount,?tax3_amount,?taxinvoice_no,?grn_no,?reff_no,?invoice_type,?entity_id, ?project_id, ?invoice_no, ?invoice_date, ?creditor_id, ?invoice_descs, ?ex_so_no, ?base_amount, ?disc1_pct, ?disc1_amount, ?vat_pct, ?vat_amount, ?wht_pct, ?wht_amount, ?other_costs, ?other_costs_amount, ?payment_type, ?credit_terms, ?invoice_status, ?createby_id, now(), ?due_date)";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?invoice_no", $this->InvoiceNo, "char");
		$this->connector->AddParameter("?invoice_date", $this->FormatInvoiceDate(SQL_DATEONLY));
        $this->connector->AddParameter("?due_date", $this->FormatDueDate(SQL_DATEONLY));
        $this->connector->AddParameter("?creditor_id", $this->CreditorId);
		$this->connector->AddParameter("?invoice_descs", $this->InvoiceDescs);
        $this->connector->AddParameter("?ex_so_no", $this->ExSoNo);
        $this->connector->AddParameter("?reff_no", $this->ReffNo, "varchar");
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
        $this->connector->AddParameter("?grn_no", $this->GrnNo);
        $this->connector->AddParameter("?taxinvoice_no", $this->TaxInvoiceNo);
        $this->connector->AddParameter("?taxtype1_id", $this->TaxType1Id == '' || $this->TaxType1Id == null ? 0 : $this->TaxType1Id);
        $this->connector->AddParameter("?taxtype2_id", $this->TaxType2Id == '' || $this->TaxType2Id == null ? 0 : $this->TaxType2Id);
        $this->connector->AddParameter("?taxtype3_id", $this->TaxType3Id == '' || $this->TaxType3Id == null ? 0 : $this->TaxType3Id);
        $this->connector->AddParameter("?tax1_amount", $this->Tax1Amount);
        $this->connector->AddParameter("?tax2_amount", $this->Tax2Amount);
        $this->connector->AddParameter("?tax3_amount", $this->Tax3Amount);
        $this->connector->AddParameter("?tax1_rate", $this->Tax1Rate);
        $this->connector->AddParameter("?tax2_rate", $this->Tax2Rate);
        $this->connector->AddParameter("?tax3_rate", $this->Tax3Rate);
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
"UPDATE t_ap_invoice_master SET
	project_id = ?project_id
	, entity_id = ?entity_id
	, invoice_no = ?invoice_no
	, invoice_date = ?invoice_date
	, creditor_id = ?creditor_id
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
	, grn_no = ?grn_no
	, taxinvoice_no = taxinvoice_no
	, taxtype1_id = ?taxtype1_id
	, taxtype2_id = ?taxtype2_id
	, taxtype3_id = ?taxtype3_id
	, tax1_amount = ?tax1_amount
	, tax2_amount = ?tax2_amount
	, tax3_amount = ?tax3_amount
	, tax1_rate = ?tax1_rate
	, tax2_rate = ?tax2_rate
	, tax3_rate = ?tax3_rate
WHERE id = ?id";
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?project_id", $this->ProjectId);
        $this->connector->AddParameter("?invoice_no", $this->InvoiceNo, "char");
        $this->connector->AddParameter("?invoice_date", $this->FormatInvoiceDate(SQL_DATEONLY));
        $this->connector->AddParameter("?due_date", $this->FormatDueDate(SQL_DATEONLY));
        $this->connector->AddParameter("?creditor_id", $this->CreditorId);
        $this->connector->AddParameter("?invoice_descs", $this->InvoiceDescs);
        $this->connector->AddParameter("?ex_so_no", $this->ExSoNo);
        $this->connector->AddParameter("?reff_no", $this->ReffNo, "varchar");
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
        $this->connector->AddParameter("?grn_no", $this->GrnNo);
        $this->connector->AddParameter("?taxinvoice_no", $this->TaxInvoiceNo);
        $this->connector->AddParameter("?taxtype1_id", $this->TaxType1Id == '' || $this->TaxType1Id == null ? 0 : $this->TaxType1Id);
        $this->connector->AddParameter("?taxtype2_id", $this->TaxType2Id == '' || $this->TaxType2Id == null ? 0 : $this->TaxType2Id);
        $this->connector->AddParameter("?taxtype3_id", $this->TaxType3Id == '' || $this->TaxType3Id == null ? 0 : $this->TaxType3Id);
        $this->connector->AddParameter("?tax1_amount", $this->Tax1Amount);
        $this->connector->AddParameter("?tax2_amount", $this->Tax2Amount);
        $this->connector->AddParameter("?tax3_amount", $this->Tax3Amount);
        $this->connector->AddParameter("?tax1_rate", $this->Tax1Rate);
        $this->connector->AddParameter("?tax2_rate", $this->Tax2Rate);
        $this->connector->AddParameter("?tax3_rate", $this->Tax3Rate);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1){
            $this->RecalculateInvoiceMaster($id);
        }
        return $rs;
	}

	public function Delete($id) {
        //baru hapus invoicenya
		$this->connector->CommandText = "Delete From t_ap_invoice_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        //baru hapus invoicenya
        $this->connector->CommandText = "Update t_ap_invoice_master a Set a.invoice_status = 3, a.updateby_id = ?updater WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updater", $this->UpdatebyId);
        $rsz =  $this->connector->ExecuteNonQuery();
        return $rsz;
    }

    public function Approve($id = 0, $uid = 0){
        $this->connector->CommandText = "Update t_ap_invoice_master a Set a.invoice_status = 1, a.updateby_id = ?updater WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updater", $uid);
        $rsz =  $this->connector->ExecuteNonQuery();
        return $rsz;
    }

    public function Unapprove($id = 0, $uid = 0){
        $this->connector->CommandText = "Update t_ap_invoice_master a Set a.invoice_status = 0, a.updateby_id = ?updater WHERE a.id = ?id";
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
        //$sql = 'Update t_ap_invoice_master a Set a.paid_amount = 0, a.base_amount = 0, a.disc1_amount = 0, a.tax1_amount = 0, a.tax2_amount = 0, a.tax3_amount = 0 Where a.id = ?invoiceId;';
        $sql = 'Update t_ap_invoice_master a Set a.paid_amount = 0, a.base_amount = 0, a.tax1_amount = 0, a.tax2_amount = 0, a.tax3_amount = 0 Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //isi base amount
        $sql = 'Update t_ap_invoice_master a Join (Select c.invoice_id, sum(c.qty * c.price) As sumPrice From t_ap_invoice_detail c Group By c.invoice_id) b On a.id = b.invoice_id Set a.base_amount = b.sumPrice Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //hitung tax1
        $sql = 'Update t_ap_invoice_master a Set a.tax1_amount = if(a.tax1_rate <> 0 And (a.base_amount - a.disc1_amount) > 0,round((a.base_amount - a.disc1_amount)  * (a.tax1_rate/100),0),0) Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //hitung tax2
       $sql = 'Update t_ap_invoice_master a Set a.tax2_amount = if(a.tax2_rate <> 0 And (a.base_amount - a.disc1_amount) > 0,round((a.base_amount - a.disc1_amount)  * (a.tax2_rate/100),0),0) Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        //hitung sisa pembayaran jika ada
        $sql = 'Update t_ap_invoice_master a Set a.paid_amount = ((a.base_amount - a.disc1_amount) + a.tax1_amount + a.tax2_amount + a.other_costs_amount) Where a.id = ?invoiceId And a.payment_type = 0;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetInvoiceItemRow($invoiceId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ap_invoice_detail as a Where a.invoice_id = ?invoiceId;";
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function GetJSonInvoices($projectId,$creditorId) {
        $sql = "SELECT a.id,a.invoice_no,a.invoice_date,a.vat_pct FROM t_ap_invoice_master as a Where a.invoice_status <> 3 And a.is_deleted = 0 And a.project_id = ".$projectId." And a.creditor_id = ".$creditorId;
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
        $sql = "SELECT a.id,a.item_id,a.item_code,a.item_descs,a.qty - a.qty_return as qty_jual,b.bsatbesar as satuan,round(a.sub_total/a.qty,0) as price,a.vat_pct,c.entity_id FROM t_ap_invoice_detail AS a";
        $sql.= " JOIN m_barang AS b ON a.item_code = b.bkode JOIN t_ap_invoice_master AS c ON a.invoice_id = c.id Where (a.qty - a.qty_return) > 0 And a.invoice_id = ".$invoiceId;
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

    public function Post($id) {
        $this->connector->CommandText = "UPDATE t_ap_invoice_master SET invoice_status = 2, approveby_id = ?user, approve_time = NOW(), updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        $this->connector->AddParameter("?user", $this->UpdatebyId);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function UnPost($id) {
        $this->connector->CommandText = "UPDATE t_ap_invoice_master SET invoice_status = 1, approveby_id = NULL, approve_time = NULL, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
        $this->connector->AddParameter("?user", $this->UpdatebyId);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetDocumentType() {
        if ($this->DocumentTypeId == null) {
            return null;
        }

        switch ($this->DocumentTypeId) {
            case 7:
                return "INVOICE KONTRAKTOR";
            case 8:
                return "INVOICE SUPPLIER";
            default:
                return "N.A.";
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function IsPaymentInProgress() {
        if ($this->Id == null) {
            throw new \Exception("InvalidArgumentException ! ID is null");
        }
        if ($this->CreditorId == null) {
            throw new \Exception("InvalidArgumentException ! CreditorId is null");
        }
        $this->connector->CommandText = "SELECT COUNT(a.id) FROM ap_payment_master AS a WHERE a.is_deleted = 0 AND a.supplier_id = ?supId AND a.id IN (SELECT payment_id FROM ap_payment_detail WHERE invoice_id = ?invId)";
        $this->connector->AddParameter("?supId", $this->CreditorId);
        $this->connector->AddParameter("?invId", $this->Id);

        $rs = $this->connector->ExecuteScalar();
        if ($rs == null) {
            $rs = 0;
        }

        return $rs > 0;
    }

    /**
     * Digunakan untuk mencari semua data pembayaran yang sudah pernah di entry
     *
     * @return Payment[]
     * @throws \Exception
     */
    public function LoadPayments() {
        if ($this->Id == null) {
            throw new \Exception("InvalidArgumentException ! ID is null");
        }
        if ($this->CreditorId == null) {
            throw new \Exception("InvalidArgumentException ! CreditorId is null");
        }

        require_once(MODEL . "ap/payment.php");

        $payment = new Payment();
        $this->Payments = $payment->LoadByInvoiceId($this->Id);

        return $this->Payments;
    }

    public function GetJSonInvoiceNonFakturPajak($creditorId = 0) {
        $sql = "SELECT a.reff_no,a.invoice_date,a.invoice_no FROM t_ap_invoice_master AS a Where (Length(trim(a.taxinvoice_no)) < 5 Or isnull(a.taxinvoice_no)) And a.is_deleted = 0 And a.invoice_status = 2 And a.creditor_id = ?creditorId";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?creditorId", $creditorId);
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
