<?php
class Coa extends EntityBase {
	private $openingBalance = array();

	public $Id;
	public $EntityId = false;
    public $AccLevel = 0;
    public $AccParentId = 0;
	public $AccNo;
    public $AccName;
    public $DcSaldo;
	public $AccStatus = 1;
    
	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->AccNo = $row["acc_no"];
        $this->AccStatus = $row["acc_status"];
        $this->AccName = $row["acc_name"];
        $this->AccLevel = $row["acc_level"];
		$this->AccParentId = $row["acc_parent_id"];
		$this->DcSaldo = $row["dc_saldo"];
	}

	public function IsOpeningBalanceRequired() {
		if ($this->AccNo == null) {
			throw new Exception("MissingPropertyException ! AccNo to be filled !");
		}
		// Untuk semua acc yang kepala 1xx, 2xx, 3xx WAJIB ADA
		return $this->AccNo[0] < 4;
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Coa[]
	 */
	public function LoadAll($eti = 1,$orderBy = "a.acc_no") {
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a Where a.entity_id = ?eti ORDER BY $orderBy";
        $this->connector->AddParameter("?eti", $eti);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Coa();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * Untuk mengambil semua CoA berdasarkan jenis CoA nya...
	 *
	 * @param $level
	 * @param bool $includeDeleted
	 * @param bool $indexById
	 * @return Coa[]
	 */
	public function LoadByLevel($eti = 1,$level, $indexById = false) {
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.entity_id = ?eti And a.acc_level = ?type ORDER BY a.acc_no";
        $this->connector->AddParameter("?eti", $eti);
		$this->connector->AddParameter("?type", $level);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Coa();
				$temp->FillProperties($row);
				if ($indexById) {
					$result[$temp->Id] = $temp;
				} else {
					$result[] = $temp;
				}
			}
		}
		return $result;
	}

	/**
	 * Untuk mencari semua akun berdasarkan parent id akun.
	 * Untuk parameter pertama special karena bisa menerima int[] atau int. Jika int[] maka semua parent id yang ada pada param akan di include
	 *
	 * @param int|int[] $parentId
	 * @param bool $includeDeleted
	 * @return Coa[]
	 */
	public function LoadByAccParentId($eti = 1,$parentId) {
		$operator = is_array($parentId) ? "IN" : "=";
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.entity_id = ?eti And a.acc_parent_id $operator ?parentId ORDER BY a.acc_no";
        $this->connector->AddParameter("?eti", $eti);
		$this->connector->AddParameter("?parentId", $parentId);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Coa();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * Untuk mengambil semua akun type 3 berdasarkan akun type 2.
	 * Sebenarnya kita bisa gunakan Coa::LoadByAccParentId() tapi method tersebut tidak fail-safe...
	 * Kita bisa kirim id type 1 pada parameter dan akan return hasil acc dengan type 2 bukan type 3
	 * Oleh karena itu kita bikin method ini yang fail-safe
	 *
	 * @param int|int[] $level2Id
	 * @param bool $includeDeleted
	 * @return Coa[]
	 */
	public function LoadLevel3ByLevel2($eti = 1,$level2Id) {
		$operator = is_array($level2Id) ? "IN" : "=";
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.entity_id = ?eti And a.acc_parent_id IN (
	-- Sedikit aneh kenapa tdk lsg query berdasarkan acc_parent_id ?
	-- Pakai method ini agar kita bisa paksa ambil berdasarkan type 2 jika ada parameter id type 1 akan terfilter disini
	SELECT aa.id FROM cm_acc_detail AS aa WHERE a.entity_id = ?eti And aa.acc_level = 2 AND aa.id $operator ?parentId
)
ORDER BY a.acc_no";
        $this->connector->AddParameter("?eti", $eti);
		$this->connector->AddParameter("?parentId", $level2Id);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Coa();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}


	/**
	 * Untuk mengambil semua akun type 3 berdasarkan akun type 1.
	 * THIS METHOD IS FAIL-SAFE
	 *
	 * @param int|int[] $level1Id
	 * @param bool $includeDeleted
	 * @return Coa[]
	 */
	public function LoadLevel3ByLevel1($eti = 1,$level1Id) {
		$operator = is_array($level1Id) ? "IN" : "=";
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.entity_id = ?eti And a.acc_parent_id IN (
	-- Disini kita mau load berdasarkan type 1 tapi kenapa tetap filter type 2 ?
	-- Ingat parent type 2 adalah type 1 jadi disini kita enforce ke type 1
	SELECT aa.id FROM cm_acc_detail AS aa WHERE a.entity_id = ?eti And aa.acc_level = 2 AND aa.acc_parent_id $operator ?parentId
)
ORDER BY a.acc_no";
        $this->connector->AddParameter("?eti", $eti);
		$this->connector->AddParameter("?parentId", $level1Id);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Coa();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * Ini berfungsi untuk mengambil semua berdasarkan 3 kode pertama pada akun...
	 *
	 * @param string[] $codes
	 * @param string $orderBy
	 * @return Coa[]
	 */
	public function LoadLevel3ByFirstCode($eti = 1,array $codes, $orderBy = "a.acc_no") {
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.entity_id = ?eti And a.acc_level = 3 AND LEFT(a.acc_no, 1) IN ?codes ORDER BY $orderBy";
		$this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?codes", $codes, "varchar");
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Coa();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param $id
	 * @return Coa|null
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param $id
	 * @return Coa|null
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.id = ?id";
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
	 * @param $code
	 * @return Coa|null
	 */
	public function FindByCode($eti = 1,$code) {
		$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.entity_id = ?eti And a.acc_no = ?code";
		$this->connector->AddParameter("?eti", $eti);
        $this->connector->AddParameter("?code", $code);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO cm_acc_detail(entity_id, acc_level, acc_parent_id, acc_no, acc_name, acc_status,dc_saldo) VALUES(?entity_id, ?acc_level, ?acc_parent_id, ?acc_no, ?acc_name, ?acc_status,?dc_saldo)';
		$this->connector->AddParameter("?acc_no", $this->AccNo, "varchar");
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?acc_status",$this->AccStatus);
        $this->connector->AddParameter("?acc_name", $this->AccName);
        $this->connector->AddParameter("?acc_level", $this->AccLevel);
        $this->connector->AddParameter("?dc_saldo", $this->DcSaldo);
		$this->connector->AddParameter("?acc_parent_id", $this->AccParentId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE cm_acc_detail SET dc_saldo = ?dc_saldo, acc_no = ?acc_no, entity_id = ?entity_id, acc_status = ?acc_status, acc_name = ?acc_name, acc_level = ?acc_level, acc_parent_id = ?acc_parent_id WHERE id = ?id';
        $this->connector->AddParameter("?acc_no", $this->AccNo, "varchar");
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?acc_status", $this->AccStatus);
        $this->connector->AddParameter("?acc_name", $this->AccName);
        $this->connector->AddParameter("?acc_level", $this->AccLevel);
        $this->connector->AddParameter("?acc_parent_id", $this->AccParentId);
        $this->connector->AddParameter("?dc_saldo", $this->DcSaldo);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From cm_acc_detail WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	/**
	 * Untuk mencari opening balance specific per account. Jika (+) artinya ada di debet jika (-) artinya ada di kredit.
	 *
	 * @deprecated please use OpeningBalance Model
	 * @param $date
	 * @param $sbu
	 * @param bool $forceReCalculate
	 * @throws Exception
	 * @return float
	 */
	public function GetOpeningBalance($date, $sbu, $forceReCalculate = false) {
		if ($this->Id == null) {
			throw new Exception("Call Coa::GetOpeningBalance() failed ! Coa Id not specified !");
		}

		$key = sprintf("%s-%s", $sbu, $date);
		if (isset($this->openingBalance[$key]) && !$forceReCalculate) {
			return $this->openingBalance[$key];
		}

		// Saat ini kita asumsikan opening balance akan menghitung semua transaksi voucher....
		$query =
"SELECT SUM(CASE WHEN b.acc_debit_id = ?accId THEN b.amount ELSE 0 END) AS amount_debit, SUM(CASE WHEN b.acc_credit_id = ?accId THEN b.amount ELSE 0 END) AS amount_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.entity_id = 0 AND a.entity_id = ?sbu AND CAST(a.voucher_date AS TIMESTAMP) < ?date
AND (b.acc_debit_id = ?accId OR b.acc_credit_id = ?accId)";
			$this->connector->AddParameter("?sbu", $sbu);

		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?accId", $this->Id);
		$this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			throw new Exception("Failed to retrieve data opening balance ! Message: " . $this->connector->GetErrorMessage());
		}

		$row = $rs->FetchAssoc();
		$this->openingBalance[$key] = $row["amount_debit"] - $row["amount_credit"];

		return $this->openingBalance[$key];
	}
}
