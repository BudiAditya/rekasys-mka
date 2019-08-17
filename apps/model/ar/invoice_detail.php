<?php

class InvoiceDetail extends EntityBase {
	public $Id;
	public $InvoiceId;
	public $ItemId = 0;
	public $ItemDescs;
    public $ItemCode;
    public $ItemName;
	public $Qty = 0;
	public $Price = 0;
	public $UomCd;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->InvoiceId = $row["invoice_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemName = $row["item_name"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_descs"];
		$this->Qty = $row["qty"];
		$this->Price = $row["price"];
        $this->UomCd = $row["uom_cd"];
    }

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->LoadById($id);
        return $this;
    }

	public function LoadByInvoiceId($invoiceId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.* FROM t_ar_invoice_detail AS a WHERE a.invoice_id = ?invoiceId ORDER BY $orderBy";
		$this->connector->AddParameter("?invoiceId", $invoiceId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new InvoiceDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}


	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ar_invoice_detail(item_id,invoice_id, item_name, item_code, item_descs, qty, price, uom_cd) VALUES(?item_id,?invoice_id, ?item_name, ?item_code, ?item_descs, ?qty, ?price, ?uom_cd)";
		$this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_name", $this->ItemName);
		$this->connector->AddParameter("?item_code", $this->ItemCode, "char");
        $this->connector->AddParameter("?item_descs", $this->ItemDescs);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?uom_cd", $this->UomCd);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $this->UpdateInvoiceMaster($this->InvoiceId);
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText = "UPDATE t_ar_invoice_detail SET item_id = ?item_id, invoice_id = ?invoice_id, item_descs = ?item_descs, qty = ?qty, price = ?price, item_code = ?item_code, item_name = ?item_name,uom_cd = ?uom_cd WHERE id = ?id";
        $this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_name", $this->ItemName);
        $this->connector->AddParameter("?item_code", $this->ItemCode, "char");
        $this->connector->AddParameter("?item_descs", $this->ItemDescs);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?uom_cd", $this->UomCd);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdateInvoiceMaster($this->InvoiceId);
        }
        return $rs;
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM t_ar_invoice_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdateInvoiceMaster($this->InvoiceId);
        }
        return $rs;
	}

    public function UpdateInvoiceMaster($invoiceId){
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
}
// End of File: estimasi_detail.php
