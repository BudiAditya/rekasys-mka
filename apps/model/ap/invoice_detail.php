<?php
namespace Ap;

/**
 * ROFL now namespace already supported. Bye bye PHP Storm error notifications
 */
class InvoiceDetail extends \EntityBase {
	public $Id;
	public $InvoiceId;
	public $ItemId = 0;
	public $ItemDescs;
    public $ItemCode;
    public $ItemName;
	public $Qty = 0;
	public $Price = 0;
	public $UomCd;
    public $TrxTypeId;
    public $TaxSchemeId;
	public $DeptId;
	public $DeptCode;
	public $DeptName;
	public $ActivityId;
	public $ActCode;
	public $ActName;
    public $AccountId = 0;
    public $UnitId  = 0;
    public $UnitCode;
    public $UnitName;

    public $Dpp = 0;
    public $Tax = 0;
    public $Deduction = 0;
    public $IsAuto = 0;

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
        $this->DeptId = $row["dept_id"];
        $this->DeptCode = $row["dept_code"];
        $this->DeptName = $row["dept_name"];
        $this->ActivityId = $row["activity_id"];
        $this->ActCode = $row["act_code"];
        $this->ActName = $row["act_name"];
        $this->TrxTypeId = $row["trx_type_id"];
        $this->TaxSchemeId = $row["tax_scheme_id"];
        $this->AccountId = $row["account_id"];
        $this->Dpp = $row["dpp"];
        $this->Tax = $row["tax"];
        $this->Deduction = $row["deduction"];
        $this->IsAuto = $row["is_auto"];
        $this->UnitId = $row["unit_id"];
        $this->UnitCode = $row["unit_code"];
        $this->UnitName = $row["unit_name"];
    }

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.act_code,b.act_name,c.dept_code,c.dept_name, d.unit_code, d.unit_name FROM t_ap_invoice_detail AS a 
LEFT JOIN cm_activity AS b ON a.activity_id = b.id 
LEFT JOIN cm_dept AS c ON a.dept_id = c.id
LEFT JOIN cm_units AS d ON a.unit_id = d.id 
WHERE a.id = ?id";
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
		$this->connector->CommandText = "SELECT a.*,b.act_code,b.act_name,c.dept_code,c.dept_name, d.unit_code, d.unit_name FROM t_ap_invoice_detail AS a 
LEFT JOIN cm_activity AS b ON a.activity_id = b.id 
LEFT JOIN cm_dept AS c ON a.dept_id = c.id
LEFT JOIN cm_units AS d ON a.unit_id = d.id 
WHERE a.invoice_id = ?invoiceId ORDER BY $orderBy";
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
"INSERT INTO t_ap_invoice_detail(is_auto,dept_id,activity_id,item_id,invoice_id, item_name, item_code, item_descs, qty, price, uom_cd, unit_id) VALUES(?is_auto,?dept_id,?activity_id,?item_id,?invoice_id, ?item_name, ?item_code, ?item_descs, ?qty, ?price, ?uom_cd, ?unit_id)";
		$this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_name", $this->ItemName);
		$this->connector->AddParameter("?item_code", $this->ItemCode, "char");
        $this->connector->AddParameter("?item_descs", $this->ItemDescs);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?uom_cd", $this->UomCd);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?activity_id", $this->ActivityId);
        $this->connector->AddParameter("?is_auto", $this->IsAuto);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
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
		$this->connector->CommandText = "UPDATE t_ap_invoice_detail SET is_auto = ?is_auto, activity_id = ?activity_id, dept_id = ?dept_id, item_id = ?item_id, invoice_id = ?invoice_id, item_descs = ?item_descs, qty = ?qty, price = ?price, item_code = ?item_code, item_name = ?item_name,uom_cd = ?uom_cd, unit_id = ?unit_id WHERE id = ?id";
        $this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_name", $this->ItemName);
        $this->connector->AddParameter("?item_code", $this->ItemCode, "char");
        $this->connector->AddParameter("?item_descs", $this->ItemDescs);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?uom_cd", $this->UomCd);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?activity_id", $this->ActivityId);
        $this->connector->AddParameter("?is_auto", $this->IsAuto);
        $this->connector->AddParameter("?unit_id", $this->UnitId);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdateInvoiceMaster($this->InvoiceId);
        }
        return $rs;
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM t_ap_invoice_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdateInvoiceMaster($this->InvoiceId);
        }
        return $rs;
	}

    public function UpdateInvoiceMaster($invoiceId){
	    //kosongkan dulu
        $sql = 'Update t_ap_invoice_master a Set a.paid_amount = 0, a.base_amount = 0, a.disc1_amount = 0, a.tax1_amount = 0, a.tax2_amount = 0, a.tax3_amount = 0 Where a.id = ?invoiceId;';
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
}
// End of File: estimasi_detail.php
