<?php

class VoucherDetail extends EntityBase {
	public $Id;
	public $VoucherId;
	public $Sequence = 0;
	public $AccDebitId = 0;
	public $AccCreditId = 0;
	public $Amount = 0;
	public $ActivityId = 0;
	public $DebtorId = 0;
	public $CreditorId = 0;
	public $ProjectId = 0;
	public $EmployeeId = 0;		// Field Ini akan otomatis diisi oleh modul pelunasan hutang karyawan
	public $AssetId = 0;			// Hmmm dulu fungsinya untuk penyusutan asset. Tapi berhubung penyusutan asset diposting secara rekap maka ini tidak terpakai
	public $Note = null;
	public $TrxTypeId = 0;
	public $BankId = 0;
	public $UnitId = 0;

	// Helper Variable;
	public $DepartmentId;
	public $MarkedForDeletion = false;
	/** @var Coa */
	public $Debit;
	/** @var Coa */
	public $Credit;
    /** @var Department */
    public $Department;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->VoucherId = $row["voucher_master_id"];
		$this->Sequence = $row["seq_no"];
		$this->AccDebitId = $row["acc_debit_id"];
		$this->AccCreditId = $row["acc_credit_id"];
		$this->Amount = $row["amount"];
		$this->ActivityId = $row["activity_id"];
		$this->DebtorId = $row["debtor_id"];
		$this->CreditorId = $row["creditor_id"];
		$this->ProjectId = $row["project_id"];
		$this->EmployeeId = $row["employee_id"];
		$this->AssetId = $row["asset_id"];
		$this->Note = $row["note"];
		$this->TrxTypeId = $row["trx_type_id"];
		$this->BankId = $row["bank_id"];
        $this->UnitId = $row["unit_id"];

		$this->DepartmentId = $row["dept_id"];
	}

	public function LoadAccount() {
        require_once(MODEL . "master/coa.php");
		if ($this->AccDebitId != null) {
			$this->Debit = new Coa($this->AccDebitId);
		} else {
			$this->Debit = null;
		}

		if ($this->AccCreditId != null) {
			$this->Credit = new Coa($this->AccCreditId);
		} else {
			$this->Credit = null;
		}
	}

	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*
FROM ac_voucher_detail AS a
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByVoucherId($vocId, $orderBy = "a.id") {
		$this->connector->CommandText =
"SELECT a.*
FROM ac_voucher_detail AS a
WHERE a.voucher_master_id = ?vocId
ORDER BY $orderBy";
		$this->connector->AddParameter("?vocId", $vocId);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new VoucherDetail();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_voucher_detail(unit_id,voucher_master_id, seq_no, acc_debit_id, acc_credit_id, amount, dept_id, activity_id, debtor_id, creditor_id, project_id, employee_id, asset_id, note, trx_type_id, bank_id)
VALUES(?unitId,?masterId, ?seqNo, ?debit, ?credit, ?amount, ?department, ?activity, ?debtor, ?creditor, ?project, ?employee, ?asset, ?note, ?trxType, ?bank)";
		$this->connector->AddParameter("?masterId", $this->VoucherId);
		$this->connector->AddParameter("?seqNo", $this->Sequence);
		$this->connector->AddParameter("?debit", $this->AccDebitId == '' ? 0 : $this->AccDebitId);
		$this->connector->AddParameter("?credit", $this->AccCreditId == '' ? 0 : $this->AccCreditId);
		$this->connector->AddParameter("?amount", $this->Amount == '' ? 0 : $this->Amount);
		$this->connector->AddParameter("?department", $this->DepartmentId == '' ? 0 : $this->DepartmentId);
		$this->connector->AddParameter("?activity", $this->ActivityId == '' ? 0 : $this->ActivityId );
		$this->connector->AddParameter("?debtor", $this->DebtorId == '' ? 0 : $this->DebtorId);
		$this->connector->AddParameter("?creditor", $this->CreditorId == '' ? 0 : $this->CreditorId);
		$this->connector->AddParameter("?project", $this->ProjectId == '' ? 0 : $this->ProjectId);
		$this->connector->AddParameter("?employee", $this->EmployeeId == '' ? 0 : $this->EmployeeId);
		$this->connector->AddParameter("?asset", $this->AssetId == '' ? 0 : $this->AssetId);
		$this->connector->AddParameter("?note", $this->Note);
		$this->connector->AddParameter("?trxType", $this->TrxTypeId == '' ? 0 : $this->TrxTypeId);
		$this->connector->AddParameter("?bank", $this->BankId == '' ? 0 : $this->BankId);
        $this->connector->AddParameter("?unitId", $this->UnitId == '' ? 0 : $this->UnitId);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_voucher_detail SET
	acc_debit_id = ?debit
	, acc_credit_id = ?credit
	, amount = ?amount
	, dept_id = ?department
	, activity_id = ?activity
	, debtor_id = ?debtor
	, creditor_id = ?creditor
	, project_id = ?project
	, employee_id = ?employee
	, asset_id = ?asset
	, note = ?note
	, trx_type_id = ?trxType
	, unit_id = ?unitId
WHERE id = ?id";
        $this->connector->AddParameter("?debit", $this->AccDebitId == '' ? 0 : $this->AccDebitId);
        $this->connector->AddParameter("?credit", $this->AccCreditId == '' ? 0 : $this->AccCreditId);
        $this->connector->AddParameter("?amount", $this->Amount == '' ? 0 : $this->Amount);
        $this->connector->AddParameter("?department", $this->DepartmentId == '' ? 0 : $this->DepartmentId);
        $this->connector->AddParameter("?activity", $this->ActivityId == '' ? 0 : $this->ActivityId );
        $this->connector->AddParameter("?debtor", $this->DebtorId == '' ? 0 : $this->DebtorId);
        $this->connector->AddParameter("?creditor", $this->CreditorId == '' ? 0 : $this->CreditorId);
        $this->connector->AddParameter("?project", $this->ProjectId == '' ? 0 : $this->ProjectId);
        $this->connector->AddParameter("?employee", $this->EmployeeId == '' ? 0 : $this->EmployeeId);
        $this->connector->AddParameter("?asset", $this->AssetId == '' ? 0 : $this->AssetId);
        $this->connector->AddParameter("?note", $this->Note);
        $this->connector->AddParameter("?trxType", $this->TrxTypeId == '' ? 0 : $this->TrxTypeId);
        $this->connector->AddParameter("?bank", $this->BankId == '' ? 0 : $this->BankId);
        $this->connector->AddParameter("?unitId", $this->UnitId == '' ? 0 : $this->UnitId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ac_voucher_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

    public function LoadDept() {
        require_once(MODEL . "master/department.php");
        if ($this->DepartmentId != null) {
            $this->Department = new Department($this->DepartmentId);
        } else {
            $this->Department = null;
        }
    }
}


// End of File: voucher_detail.php
