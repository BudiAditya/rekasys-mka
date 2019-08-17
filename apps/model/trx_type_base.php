<?php
/**
 * Merupakan pindahan dari common/trx_type.php dan di rename dan dijadikan abstract class agar tidak bisa di instantiate dan harus di extends oleh class lain
 */
abstract class TrxTypeBase extends EntityBase {
	/**
	 * Digunakan untuk meng-lock ModuleCd variable. Jika null maka tidak ada lock. Jika diisi maka semua ModuleCd akan di force menjadi sama dengan isi variable ini
	 *
	 * @var null|string
	 */
	protected $lockModuleId = null;

	/**
	 * Untuk beberapa jenis transaksi kita akan memaksa user untuk mengisi field-field khusus (debtor/creditor/employee/asset)
	 * Karena kombinasinya cukup banyak akan sulit kalau pake kode biasa oleh karena itu akan digunakan tehnik bitwise operator (seperti enumeration pada .net)
	 */
	const REQUIRE_DEBTOR = 1;	// 2^0 = 1
	const REQUIRE_CREDITOR = 2;	// 2^1 = 2
	const REQUIRE_EMPLOYEE = 4;	// 2^2 = 4
	const REQUIRE_ASSET = 8;	// 2^3 = 8

	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $UpdateById;
	public $UpdateDate;
	public $ModuleId;
	public $Code;
	public $Description;
	public $TrxClassId;
	public $AccDebitId;
	public $AccCreditId;
	public $BillTypeId;
	public $TaxSchId;
	/**
	 * Jika is global aktif maka akan ditampilkan walaupun beda SBU query => (a.entity_id = ?sbu OR a.is_global = 1).
	 * Variable ini hanya akan dapat menjadi true jika di set TRUE dan dilakukan oleh ENTITY MSN (id = 3)
	 *
	 * @var bool
	 */
	public $IsGlobal = false;
	/**
	 * Hasil bitwise dari REQUIRE_XXX. Digunakan untuk mencari tahu apakah transaksi ini memerlukan data tertentu / tidak
	 * @see TrxTypeBase::REQUIRE_DEBTOR
	 * @see TrxTypeBase::REQUIRE_CREDITOR
	 * @see TrxTypeBase::REQUIRE_EMPLOYEE
	 * @see TrxTypeBase::REQUIRE_ASSET
	 *
	 * @var int
	 */
	public $RequireWhich = 0;
	public $ShowDebit = true;
	public $ShowCredit = true;

	// Helper
	private $haveExtendedData = false;
	public $AccDebitNo;
	public $AccCreditNo;

	public function __construct($id = null) {
		// If we overriding this in derived class then make sure that super constructor called
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	/**
	 * Karena class ini abstract maka untuk membuat instance nya akan dikerjakan specific per derived classnya.
	 * Agar pasti maka method ini akan di define abstract dan return type nya adalah derived class
	 *
	 * @return TrxTypeBase
	 */
	abstract protected function CreateInstance();

	public function FillProperties(array $row, $haveExtendedData = false) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->UpdateById = $row["update_by"];
		$this->UpdateDate = strtotime($row["update_date"]);
		$this->ModuleId = $row["module_id"];
		$this->Code = $row["code"];
		$this->Description = $row["description"];
		$this->TrxClassId = $row["trx_class_id"];
		$this->AccDebitId = $row["acc_debit_id"];
		$this->AccCreditId = $row["acc_credit_id"];
		$this->BillTypeId = $row["bill_type_id"];
		$this->TaxSchId = $row["taxsch_id"];
		$this->IsGlobal = $row["is_global"] == 1;
		$this->RequireWhich = $row["require_which"];
		$this->ShowDebit = $row["show_debit"];
		$this->ShowCredit = $row["show_credit"];

		if ($haveExtendedData) {
			$this->haveExtendedData = true;
			$this->AccDebitNo = $row["acc_debit_no"];
			$this->AccCreditNo = $row["acc_credit_no"];
		}
	}

	public function GetDebitName() {
		if ($this->ShowDebit) {
			return $this->AccDebitId == null ? "KAS / BANK" : $this->AccDebitNo;
		} else {
			return null;
		}
	}

	public function GetCreditName() {
		if ($this->ShowCredit) {
			return $this->AccCreditId == null ? "KAS / BANK" : $this->AccCreditNo;
		} else {
			return null;
		}
	}

	public function GetName() {
		if ($this->haveExtendedData) {
			if ($this->ShowDebit && $this->ShowCredit) {
				return sprintf('%s (D: %s, K: %s)', $this->Description, $this->AccDebitId == null ? "KAS / BANK" : $this->AccDebitNo, $this->AccCreditId == null ? "KAS / BANK" : $this->AccCreditNo);
			} else if ($this->ShowDebit) {
				return sprintf("%s (D: %s)", $this->Description, $this->AccDebitId == null ? "KAS / BANK" : $this->AccDebitNo);
			} else if ($this->ShowCredit) {
				return sprintf("%s (K: %s)", $this->Description, $this->AccCreditId == null ? "KAS / BANK" : $this->AccCreditNo);
			} else {
				return $this->Description;
			}
		} else {
			return $this->Description;
		}
	}

	/**
	 * Untuk mengecek apakah jenis transaksi ini memerlukan data tambahan atau tidak. Fungsi umum ini tidak disarankan untuk digunakan. Mohon gunakan IsRequireXxx()
	 *
	 *
	 * @see TrxTypeBase::REQUIRE_DEBTOR
	 * @see TrxTypeBase::REQUIRE_CREDITOR
	 * @see TrxTypeBase::REQUIRE_EMPLOYEE
	 * @see TrxTypeBase::REQUIRE_ASSET
	 *
	 * @param int $which
	 * @return bool
	 * @throws Exception
	 */
	public function IsRequire($which) {
		if ($this->RequireWhich === null) {
			throw new Exception("Data not ready yet. IsRequire() must be called after model is loaded");
		}

		// Jika bitwise AND menghasilkan kode yang sama dengan yang diminta bearti parameter require kena
		// OK gw tau ini bisa diganti ke single line code... tapi dengan alasan tertentu gw ga pake tehnik 1 line
		if (($this->RequireWhich & $which) == $which) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check apakah transaksi ini mewajibkan adanya debtor / tidak
	 *
	 * @return bool
	 */
	public function IsRequireDebtor() {
		return $this->IsRequire(TrxTypeBase::REQUIRE_DEBTOR);
	}

	/**
	 * Check apakah transaksi ini mewajibkan adanya creditor / tidak
	 *
	 * @return bool
	 */
	public function IsRequireCreditor() {
		return $this->IsRequire(TrxTypeBase::REQUIRE_CREDITOR);
	}

	/**
	 * Check apakah transaksi ini mewajibkan adanya karyawan / tidak
	 *
	 * @return bool
	 */
	public function IsRequireEmployee() {
		return $this->IsRequire(TrxTypeBase::REQUIRE_EMPLOYEE);
	}

	/**
	 * Check apakah transaksi ini mewajibkan adanya debtor / tidak
	 *
	 * @return bool
	 */
	public function IsRequireAsset() {
		return $this->IsRequire(TrxTypeBase::REQUIRE_ASSET);
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return TrxTypeBase[]
	 */
	public function LoadAll($orderBy = "a.code", $includeDeleted = false) {
		if ($includeDeleted) {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE 1=1 %s
ORDER BY $orderBy";
		} else {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.is_deleted = 0 %s
ORDER BY $orderBy";
		}

		if ($this->lockModuleId == null) {
			$this->connector->CommandText = sprintf($query, "");
		} else {
			$this->connector->CommandText = sprintf($query, "AND module_id = ?lockId");
			$this->connector->AddParameter("?lockId", $this->lockModuleId);
		}

		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = $this->CreateInstance();
				$temp->FillProperties($row, true);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $sbu
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return TrxTypeBase[]
	 */
	public function LoadByEntityId($sbu, $orderBy = "a.code", $includeDeleted = false) {
		if ($includeDeleted) {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE (a.entity_id = ?sbu OR a.is_global = 1) %s
ORDER BY $orderBy";
		} else {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.is_deleted = 0 AND (a.entity_id = ?sbu OR a.is_global = 1) %s
ORDER BY $orderBy";
		}

		if ($this->lockModuleId == null) {
			$this->connector->CommandText = sprintf($query, "");
		} else {
			$this->connector->CommandText = sprintf($query, "AND module_id = ?lockId");
			$this->connector->AddParameter("?lockId", $this->lockModuleId);
		}

		$this->connector->AddParameter("?sbu", $sbu);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = $this->CreateInstance();
				$temp->FillProperties($row, true);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $sbu
	 * @param int $trxClassId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return TrxTypeBase[]
	 */
	public function LoadByTrxClassId($sbu, $trxClassId, $orderBy = "a.code", $includeDeleted = false) {
		if ($includeDeleted) {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE (a.entity_id = ?sbu OR a.is_global = 1) AND a.trx_class_id = ?trxClass %s
ORDER BY $orderBy";
		} else {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.is_deleted = 0 AND (a.entity_id = ?sbu OR a.is_global = 1) AND a.trx_class_id = ?trxClass %s
ORDER BY $orderBy";
		}

		if ($this->lockModuleId == null) {
			$this->connector->CommandText = sprintf($query, "");
		} else {
			$this->connector->CommandText = sprintf($query, "AND module_id = ?lockId");
			$this->connector->AddParameter("?lockId", $this->lockModuleId);
		}

		$this->connector->AddParameter("?sbu", $sbu);
		$this->connector->AddParameter("?trxClass", $trxClassId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = $this->CreateInstance();
				$temp->FillProperties($row, true);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $sbu
	 * @param int|int[] $billTypeId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return TrxTypeBase[]
	 */
	public function LoadByBillTypeId($sbu, $billTypeId, $orderBy = "a.code", $includeDeleted = false) {
		if (!is_array($billTypeId)) {
			$billTypeId = array($billTypeId);
		}
		if ($includeDeleted) {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE (a.entity_id = ?sbu OR a.is_global = 1) AND a.bill_type_id IN ?billType %s
ORDER BY $orderBy";
		} else {
			$query =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.is_deleted = 0 AND (a.entity_id = ?sbu OR a.is_global = 1) AND a.bill_type_id IN ?billType %s
ORDER BY $orderBy";
		}

		if ($this->lockModuleId == null) {
			$this->connector->CommandText = sprintf($query, "");
		} else {
			$this->connector->CommandText = sprintf($query, "AND module_id = ?lockId");
			$this->connector->AddParameter("?lockId", $this->lockModuleId);
		}

		$this->connector->AddParameter("?sbu", $sbu);
		$this->connector->AddParameter("?billType", $billTypeId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = $this->CreateInstance();
				$temp->FillProperties($row, true);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $sbu
	 * @param int $moduleId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return TrxTypeBase[]
	 * @throws Exception
	 */
	public function LoadByModuleId($sbu, $moduleId, $orderBy = "a.code", $includeDeleted = false) {
		if ($this->lockModuleId != null) {
			throw new Exception("This method not intended to be called in module locked context !");
		}

		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE (a.entity_id = ?sbu OR a.is_global = 1) AND a.module_id = ?module
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.is_deleted = 0 AND (a.entity_id = ?sbu OR a.is_global = 1) AND a.module_id = ?module
ORDER BY $orderBy";
		}

		$this->connector->AddParameter("?sbu", $sbu);
		$this->connector->AddParameter("?module", $moduleId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = $this->CreateInstance();
				$temp->FillProperties($row, true);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return TrxTypeBase
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $id
	 * @return TrxTypeBase
	 */
	public function FindById($id) {
		if ($this->lockModuleId == null) {
			$this->connector->CommandText =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.id = ?id";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.acc_no AS acc_debit_no, c.acc_no AS acc_credit_no
FROM sys_trx_type AS a
	LEFT JOIN cm_acc_detail AS b ON a.acc_debit_id = b.id
	LEFT JOIN cm_acc_detail AS c ON a.acc_credit_id = c.id
WHERE a.id = ?id AND a.module_id = ?lockId";
			$this->connector->AddParameter("?lockId", $this->lockModuleId);
		}

		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row, true);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO sys_trx_type(entity_id, module_id, code, description, trx_class_id, acc_debit_id, acc_credit_id, bill_type_id, taxsch_id, is_global, update_date, update_by, require_which, show_debit, show_credit)
VALUES(?entity_id, ?module_id, ?code, ?description, ?trx_class_id, ?acc_debit_id, ?acc_credit_id, ?bill_type_id, ?taxsch_id, ?is_global, NOW(), ?update_by, ?require, ?showDebit, ?showCredit)';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?module_id", $this->lockModuleId == null ? $this->ModuleId : $this->lockModuleId);
		$this->connector->AddParameter("?code", $this->Code, "varchar");
		$this->connector->AddParameter("?description", $this->Description);
		$this->connector->AddParameter("?trx_class_id", $this->TrxClassId);
		$this->connector->AddParameter("?acc_debit_id", $this->AccDebitId);
		$this->connector->AddParameter("?acc_credit_id", $this->AccCreditId);
		$this->connector->AddParameter("?bill_type_id", $this->BillTypeId);
		$this->connector->AddParameter("?taxsch_id", $this->TaxSchId);
		$this->connector->AddParameter("?is_global", $this->IsGlobal);
		$this->connector->AddParameter("?update_by", $this->UpdateById);
		$this->connector->AddParameter("?require", $this->RequireWhich);
		$this->connector->AddParameter("?showDebit", $this->ShowDebit);
		$this->connector->AddParameter("?showCredit", $this->ShowCredit);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE sys_trx_type SET
	module_id = ?module_id
	, code = ?code
	, description = ?description
	, trx_class_id = ?trx_class_id
	, acc_debit_id = ?acc_debit_id
	, acc_credit_id = ?acc_credit_id
	, bill_type_id = ?bill_type_id
	, taxsch_id = ?taxsch_id
	, is_global = ?is_global
	, update_date = NOW()
	, update_by = ?update_by
	, require_which = ?require
	, show_debit = ?showDebit
	, show_credit = ?showCredit
WHERE id = ?id';
		$this->connector->AddParameter("?module_id", $this->lockModuleId == null ? $this->ModuleId : $this->lockModuleId);
		$this->connector->AddParameter("?code", $this->Code, "varchar");
		$this->connector->AddParameter("?description", $this->Description);
		$this->connector->AddParameter("?trx_class_id", $this->TrxClassId);
		$this->connector->AddParameter("?acc_debit_id", $this->AccDebitId);
		$this->connector->AddParameter("?acc_credit_id", $this->AccCreditId);
		$this->connector->AddParameter("?bill_type_id", $this->BillTypeId);
		$this->connector->AddParameter("?taxsch_id", $this->TaxSchId);
		$this->connector->AddParameter("?is_global", $this->IsGlobal);
		$this->connector->AddParameter("?update_by", $this->UpdateById);
		$this->connector->AddParameter("?require", $this->RequireWhich);
		$this->connector->AddParameter("?showDebit", $this->ShowDebit);
		$this->connector->AddParameter("?showCredit", $this->ShowCredit);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		if ($this->lockModuleId == null) {
			$this->connector->CommandText = 'UPDATE sys_trx_type SET is_deleted = 1 WHERE id = ?id';
		} else {
			$this->connector->CommandText = 'UPDATE sys_trx_type SET is_deleted = 1 WHERE id = ?id AND module_id = ?lockId';
			$this->connector->AddParameter("?lockId", $this->lockModuleId);
		}
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}
}

// End of File: trx_type_base.php
