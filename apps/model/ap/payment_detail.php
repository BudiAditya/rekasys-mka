<?php

class PaymentDetail extends EntityBase {
	public $Id;
	public $PaymentId;
	public $InvoiceId;
    public $InvoiceNo;
    public $InvoiceOutstanding = 0;
    public $AllocateAmount = 0;
    public $InvoiceAmount = 0;
    public $PotPph = 0;
    public $PotLain = 0;
    public $PotRetur = 0;
    public $InvoiceDate;
    public $DueDate;
    public $ReffNo;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->PaymentId = $row["payment_id"];
		$this->InvoiceId = $row["invoice_id"];
        $this->InvoiceNo = $row["invoice_no"];
        $this->InvoiceOutstanding = $row["invoice_outstanding"];
        $this->AllocateAmount = $row["allocate_amount"];
        $this->InvoiceAmount = $row["invoice_amount"];
        $this->PotPph = $row["pot_pph"];
        $this->PotLain = $row["pot_lain"];
        $this->PotRetur = $row["pot_retur"];
        $this->InvoiceDate = $row["invoice_date"];
        $this->DueDate = $row["due_date"];
        $this->ReffNo = $row["reff_no"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.invoice_no,b.invoice_date,b.due_date,b.reff_no FROM t_ap_payment_detail AS a Join vw_ap_invoice_master AS b On a.invoice_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.invoice_no,b.invoice_date,b.due_date,b.reff_no FROM t_ap_payment_detail AS a Join vw_ap_invoice_master AS b On a.invoice_id = b.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByPaymentId($paymentId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,b.invoice_no,b.invoice_date,b.due_date,b.reff_no FROM t_ap_payment_detail AS a Join vw_ap_invoice_master AS b On a.invoice_id = b.id WHERE a.payment_id = ?paymentId ORDER BY $orderBy";
		$this->connector->AddParameter("?paymentId", $paymentId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new PaymentDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ap_payment_detail(payment_id, invoice_id, invoice_outstanding, allocate_amount, pot_pph, pot_lain)
VALUES(?payment_id, ?invoice_id,?invoice_outstanding, ?allocate_amount, ?pot_pph, ?pot_lain)";
        $this->connector->AddParameter("?payment_id", $this->PaymentId);
		$this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?invoice_outstanding", $this->InvoiceOutstanding);
        $this->connector->AddParameter("?allocate_amount", $this->AllocateAmount);
        $this->connector->AddParameter("?pot_pph", $this->PotPph);
        $this->connector->AddParameter("?pot_lain", $this->PotLain);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $this->UpdatePaymentMaster($this->PaymentId);
            $this->UpdateInvoicePaidAmount($this->InvoiceId);
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ap_payment_detail SET
	payment_id = ?payment_id
	, invoice_id = ?invoice_id
	, invoice_outstanding = ?invoice_outstanding
	, allocate_amount = ?allocate_amount
	, pot_pph = ?pot_pph
	, pot_lain = ?pot_lain
WHERE id = ?id";
        $this->connector->AddParameter("?payment_id", $this->PaymentId);
        $this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?invoice_outstanding", $this->InvoiceOutstanding);
        $this->connector->AddParameter("?allocate_amount", $this->AllocateAmount);
        $this->connector->AddParameter("?pot_pph", $this->PotPph);
        $this->connector->AddParameter("?pot_lain", $this->PotLain);
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdatePaymentMaster($this->PaymentId);
            $this->UpdateInvoicePaidAmount($this->InvoiceId);
        }
        return $rs;
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM t_ap_payment_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdatePaymentMaster($this->PaymentId);
            $this->UpdateInvoicePaidAmount($this->InvoiceId);
        }
        return $rs;
	}

    public function UpdatePaymentMaster($paymentId){
        $sql = "Update t_ap_payment_master a Set a.payment_amount = 0,a.payment_descs = '' Where a.id = ?paymentId;";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?paymentId", $paymentId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = "Update t_ap_payment_master a Join (Select c.payment_id, If(count(*) > 1,GROUP_CONCAT(d.reff_no),d.reff_no) as keterangan From t_ap_payment_detail c JOIN t_ap_invoice_master AS d On c.invoice_id = d.id Group By c.payment_id) b";
        $sql.= " On a.id = b.payment_id Set a.payment_descs = concat('Pembayaran: ',b.keterangan) Where a.id = ?paymentId;";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?paymentId", $paymentId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ap_payment_master a
Left Join (Select c.payment_id, coalesce(sum(c.allocate_amount),0) As sumAlloc, coalesce(sum(c.invoice_amount),0) As sumInvoice, coalesce(sum(c.pot_pph),0) As sumPph, coalesce(sum(c.pot_lain),0) As sumLain From t_ap_payment_detail c Group By c.payment_id) b
On a.id = b.payment_id Set a.payment_amount = coalesce(b.sumAlloc,0), a.allocate_amount = coalesce(b.sumAlloc,0), a.invoice_amount = coalesce(b.sumInvoice,0), a.pot_pph = coalesce(b.sumPph,0), a.pot_lain = coalesce(b.sumLain,0) Where a.id = ?paymentId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?paymentId", $paymentId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }



    public function UpdateInvoicePaidAmount($invoiceId){
        $val = $this->GetInvoicePaidAmount($invoiceId);
        $sql = 'Update t_ap_invoice_master a Set a.paid_amount = ?sumAlloc Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $this->connector->AddParameter("?sumAlloc", $val);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetInvoicePaidAmount($invoiceId){
        $sql = 'Select coalesce(sum(c.allocate_amount),0) as sumAlloc From t_ap_payment_detail c ';
        $sql.= 'Join t_ap_payment_master d On c.payment_id = d.id';
        $sql.= ' where d.is_deleted = 0 and d.payment_status <> 3 and c.invoice_id = ?invoice_id;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoice_id", $invoiceId);
        $rs = $this->connector->ExecuteQuery();
        $val = 0;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["sumAlloc"];
        }
        return $val;
    }
}
// End of File: estimasi_detail.php
