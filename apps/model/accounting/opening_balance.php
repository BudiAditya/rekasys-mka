<?php
require_once(MODEL . "master/coa.php");

class OpeningBalance extends EntityBase {
	public $Id;
	public $EntityId;
	public $AccountId;
	public $Date;
	public $DebitAmount = 0;
	public $CreditAmount = 0;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	/** @var  Coa */
	private $coa;

	// Helper
	public $AccountNo;
	public $AccountName;
	public $AccountDcSaldo;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
        $this->AccountId = $row["acc_id"];
		$this->Date = strtotime($row["bal_date"]);
		$this->DebitAmount = $row["bal_debit_amt"];
		$this->CreditAmount = $row["bal_credit_amt"];
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);

		$this->AccountNo = $row["acc_no"];
		$this->AccountName = $row["acc_name"];
		$this->AccountDcSaldo = $row["dc_saldo"];
	}

	public function FormatDate($format = HUMAN_DATE) {
		return is_int($this->Date) ? date($format, $this->Date) : null;
	}

	/**
	 * @return Coa
	 */
	public function GetCoa() {
		return $this->coa;
	}

	/**
	 * @param int $id
	 * @return OpeningBalance
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.acc_no, b.acc_name, b.dc_saldo
FROM ac_opening_balance2 AS a
	JOIN cm_acc_detail AS b ON a.acc_id = b.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		$this->coa = new Coa($this->AccountId);
		return $this;
	}

	/**
	 * @param int $accId
	 * @param int $year
	 * @return OpeningBalance
	 */
	public function LoadByAccount($accId, $year) {
		// Khusus load by account maka COA lsg diload apapun yang terjadi
		$this->coa = new Coa($accId);

		$this->connector->CommandText =
"SELECT a.*, b.acc_no, b.acc_name, b.dc_saldo
FROM ac_opening_balance2 AS a
	JOIN cm_acc_detail AS b ON a.acc_id = b.id
WHERE a.acc_id = ?accId AND year(a.bal_date) = ?year";
		$this->connector->AddParameter("?accId", $accId);
		$this->connector->AddParameter("?year", $year);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByAccountNo($entityId,$accNo, $year) {
        // Khusus load by account maka COA lsg diload apapun yang terjadi
        $this->coa = new Coa();
        $this->coa = $this->coa->FindByCode($entityId,$accNo);

        $this->connector->CommandText =
            "SELECT a.*, b.acc_no, b.acc_name, b.dc_saldo
FROM ac_opening_balance2 AS a
	JOIN cm_acc_detail AS b ON a.acc_id = b.id
WHERE b.acc_no = ?accNo And a.entity_id = ?entityId AND year(a.bal_date) = ?year";
        $this->connector->AddParameter("?accNo", $accNo);
        $this->connector->AddParameter("?entityId", $entityId);
        $this->connector->AddParameter("?year", $year);

        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }

        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	/**
	 * Digunakan untuk mencari pergerakan saldo awal (akibat transaksi) dari akun yang sudah diload dari DBase.
	 * Method ini hanya bisa dipanggil jika sudah diload terlebih dahulu oleh method yang ada pada model ini.
	 * Untuk parameter $currentDate harus berupa int jika di invoke jika tidak akan default ke tanggal hari ini.
	 *
	 * NOTE: Ini akan mencari semua transaksi voucher yang sudah di approve. TIDAK MAKE SENSE MENCARI DATA BERDASARKAN VOUCHER TIDAK DI APPROVE
	 *
	 * @reference OpeningBalance::LoadById()
	 * @reference OpeningBalance::LoadByAccount()
	 *
	 * @param null|int $currentDate
	 * @param int $status Digunakan untuk filter status voucher. By default harus menggunakan voucher berstatus POSTED
	 * @param null|int $projectId
	 * @throws Exception
	 * @return array("debet" => float, "kredit" => float, "transaksi" => float, "saldo" => float)
	 */
	public function CalculateTransaction($currentDate = null, $status = 4, $projectId = null) {
		if ($this->coa == null || $this->coa->Id == null) {
			throw new Exception("Tidak dapat mencari transaksi ! Data Account tidak ada !");
		}

		// Digunakan untuk mencari tanggal awal. Jika ada data maka gunakan tanggal pada OpeningBalan7ce
		// Karena ada beberapa account yang tidak memiliki data OpeningBalance maka untuk tanggal awal akan di auto-detect ke 1 Januari
		if (is_int($this->Date)) {
			// Ok kalau masuk sini bearti ada data opening balance
			$temp = $this->Date;
		} else {
			// Kalau tidak ada opening balance coba lihat apakah ada data $currentDate / tidak. Jika ada $currentDate maka gunakan tahun $currentDate
			$temp = is_int($currentDate) ? $currentDate : mktime(0, 0, 0);
		}

		// Cari tanggal awal dan akhir periode transaksi yang akan dicari
		$start = mktime(0, 0, 0, 1, 1, date("Y", $temp));
		if (is_int($currentDate)) {
			// Force ke jam 23:59:59 berdasarkan tanggal yang dikirim
			$end = mktime(23, 59, 59, date("n", $currentDate), date("j", $currentDate), date("Y", $currentDate));
		} else {
			// Karena tidak ada tanggal yang dikirim asumsikan hari ini s.d. jam 23:59:59
			$end = mktime(23, 59, 59);
		}

		// Sedikit validasi...
		if ($end < $start) {
			// Tanggal yang diminta kurang dari tanggal Opening Balance...
			// Ini akan kejadian pada report awal bulan yang mana start dimulai dari 1 Januari maka parameter $currentDate akan dikirim 31 Des Bulan sebelumnya
			// Dapat dipastikan tidak ada transaksi dll
			return array(
				"debet" => 0,
				"kredit" => 0,
				"transaksi" => 0,
				"saldo" => $this->coa->DcSaldo == "D" ? $this->DebitAmount - $this->CreditAmount : $this->CreditAmount - $this->DebitAmount
			);
		}
        $query = "SELECT SUM(CASE WHEN b.acc_debit_id = ?accId THEN b.amount ELSE 0 END) AS amount_debit, SUM(CASE WHEN b.acc_credit_id = ?accId THEN b.amount ELSE 0 END) AS amount_credit
        FROM ac_voucher_master AS a
            JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
        WHERE a.status = ?status AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
            AND (b.acc_debit_id = ?accId OR b.acc_credit_id = ?accId)";
        if($projectId > 0){
            $query.= " AND (b.project_id = ?projectId)";
        }
		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
		$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
		$this->connector->AddParameter("?accId", $this->coa->Id);
        $this->connector->AddParameter("?projectId", $projectId);
		if ($status == -1) {
			$this->connector->AddParameter("?status", "a.status", "int");	// Gw mau paksa agar querynya menjadi a.status = a.status (selalu true) bukan a.status = 'a.status'
		} else {
			if ($status > 0 && $status < 5) {
				$this->connector->AddParameter("?status", $status);
			} else {
				$this->connector->AddParameter("?status", 4);
			}
		}

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			throw new Exception("DBase error: " . $this->connector->GetErrorMessage());
		}

		// Berhubung si FetchAssoc pasti return 1 baris walau hasil sum tidak ada maka...
		$row = $rs->FetchAssoc();
		$row["amount_debit"] = $row["amount_debit"] == null ? 0 : $row["amount_debit"];
		$row["amount_credit"] = $row["amount_credit"] == null ? 0 : $row["amount_credit"];

		// Return result set
		$result = array();
		$result["debet"] = $row["amount_debit"];
		$result["kredit"] = $row["amount_credit"];
		$result["transaksi"] = $this->coa->DcSaldo == "D" ? $row["amount_debit"] - $row["amount_credit"] : $row["amount_credit"] - $row["amount_debit"];
		$result["saldo"] = $this->coa->DcSaldo == "D" ? $this->DebitAmount - $this->CreditAmount + $result["transaksi"] : $this->CreditAmount - $this->DebitAmount + $result["transaksi"];

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO ac_opening_balance2(entity_id,acc_id, bal_date, bal_debit_amt, bal_credit_amt, createby_id, create_time)
VALUES(?entity_id,?acc, ?date, ?debit, ?credit, ?user, NOW())";
		$this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?acc", $this->AccountId);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?debit", $this->DebitAmount);
		$this->connector->AddParameter("?credit", $this->CreditAmount);
		$this->connector->AddParameter("?user", $this->CreatedById);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE ac_opening_balance2 SET
	acc_id = ?acc
	, bal_date = ?date
	, bal_debit_amt = ?debit
	, bal_credit_amt = ?credit
	, updateby_id = ?user
	, update_time = NOW()
	, entity_id = ?entity_id
WHERE id = ?id";
        $this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?acc", $this->AccountId);
		$this->connector->AddParameter("?date", $this->FormatDate(SQL_DATETIME));
		$this->connector->AddParameter("?debit", $this->DebitAmount);
		$this->connector->AddParameter("?credit", $this->CreditAmount);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM ac_opening_balance2 WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}


// End of File: opening_balance.php
