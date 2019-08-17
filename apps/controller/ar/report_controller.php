<?php
namespace Ar;
/**
 * Class ReportController
 * @package Ar
 *
 * Class ini akan berfungsi untuk membuat laporan yang berhubungan dengan Piutang (AR)
 * Tidak menggunakan model (lsg pakai query karena complex).
 *
 * ToDo: Semua perubahan mengenai status posted pada OR, Invoice akan berpengaruh pada query-query yang digunakan disini
 */
class ReportController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function kartu_piutang() {
		require_once(MODEL . "master/debtor.php");

		$debtor =  new \Debtor();
		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$debtorId = $this->GetGetValue("debtor");
			$output = $this->GetGetValue("output");
			$report = null;
			$saldoAwal = null;

			if (!is_int($start) || !is_int($end) || $debtorId == "") {
				$this->Set("error", "Maaf data yang dikirim tidak lengkap mohon ulangi proses.");
			} else {
				$flagOk = true;
				$debtor = $debtor->LoadById($debtorId);

				// Validate data....
				if ($debtor == null || $debtor->IsDeleted) {
					$this->Set("error", "Debtor yang diminta tidak dapat ditemukan mohon ulangi proses.");
					$flagOk = false;
				} else if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
					if ($debtor->EntityId != $this->userCompanyId) {
						// Beuh... simulate not found
						$this->Set("error", "Debtor yang diminta tidak dapat ditemukan mohon ulangi proses.");
						$flagOk = false;
					}
				}

				// OK kita proses kalau ada data yang OK
				if ($flagOk) {
					// Step #01: Ambil data Saldo Awal dari table saldo awal
					require_once(MODEL . "ar/opening_balance.php");
					$obal = new OpeningBalance();
					$obal->LoadByDebtor($debtorId);
					if ($obal->Id != null) {
						$result = $obal->CalculateTransaction($start);
						$saldoAwal = $obal->DebitAmount - $obal->CreditAmount + $result["debet"] - $result["kredit"];
					} else {
						$saldoAwal = 0;
					}

					$firstJanuary = mktime(0, 0, 0, 1, 1, date("Y", $start));
					// Setting parameter query
					$this->connector->ClearParameter();
					$this->connector->AddParameter("?debtorId", $debtorId);
					$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
					$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
					$this->connector->AddParameter("?firstJanuary", date(SQL_DATETIME, $firstJanuary));
					$this->connector->AddParameter("?beforeStart", date(SQL_DATETIME, $start - 1));

					// ToDo: Perubahan status posting dari Voucher harus merubah query ini juga
					// Step #02: Cari data saldo awal (debet).... (Driver PostgreSql tidak support multiple result set spt Mysqli)
					if ($start > $firstJanuary) {
						$this->connector->CommandText =
"SELECT SUM(IF(b.acc_debit_id = d.acc_control_id, b.amount, 0)) AS debet, SUM(IF(b.acc_credit_id = d.acc_control_id, b.amount, 0)) AS kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN ar_debtor_master AS c ON b.debtor_id = c.id
	JOIN ar_debtortype AS d ON c.debtortype_id = d.id
WHERE a.is_deleted = 0 AND a.status = 4 AND b.debtor_id = ?debtorId AND a.voucher_date BETWEEN ?firstJanuary AND ?beforeStart";
						$rs = $this->connector->ExecuteQuery();
						if ($rs == null) {
							$this->Set("error", "Terjadi error saat mengambil data saldo awal ! Harap segera menghubungi system administrator anda.<br />Message: " . $this->connector->GetErrorMessage());
						} else {
							$row = $rs->FetchAssoc();
							$saldoAwal += $row["debet"] - $row["kredit"];
						}
					}

					// Step #03: Cari data transaksi berdasarkan periode
					// ToDo: Perubahan status posting Voucher juga harus merubah ini
					$this->connector->CommandText =
"SELECT a.id, a.doc_no, a.voucher_date, b.debtor_id, b.note, IF(b.acc_debit_id = d.acc_control_id, b.amount, 0) AS debet, IF(b.acc_credit_id = d.acc_control_id, b.amount, 0) AS kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN ar_debtor_master AS c ON b.debtor_id = c.id
	JOIN ar_debtortype AS d ON c.debtortype_id = d.id
WHERE a.is_deleted = 0 AND a.status = 4 AND b.debtor_id = ?debtorId AND a.voucher_date BETWEEN ?start AND ?end
ORDER BY a.voucher_date ASC	;";
					$report = $this->connector->ExecuteQuery();
				}
			}
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = time();
			$debtorId = null;
			$output = "web";

			$report = null;
			$saldoAwal = null;
		}

		$this->Set("debtors", $debtor->LoadByEntity($this->userCompanyId));

		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("debtorId", $debtorId);
		$this->Set("output", $output);
		$this->Set("report", $report);
		$this->Set("saldoAwal", $saldoAwal);
	}

	public function rekap_piutang() {
		$accId = $this->GetGetValue("accId", -1);

		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$output = $this->GetGetValue("output");

			// Untuk generate rekap kita pakai beberapa temporary table...
			$subQueryWhere = "";
//			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
//				$subQueryWhere .= " AND a.entity_id = ?sbu";
//			}
			if ($accId != null) {
				$subQueryWhere .= " AND d.acc_control_id = ?accId";
			}
			// ToDo: Perubahan status posting dari Voucher harus merubah query ini juga
			$firstJanuary = mktime(0, 0, 0, 1, 1, date("Y", $start));
			// Setting parameter query
			$this->connector->AddParameter("?sbu", $this->userCompanyId);
			$this->connector->AddParameter("?accId", $accId);
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
			$this->connector->AddParameter("?firstJanuary", date(SQL_DATETIME, $firstJanuary));
			$this->connector->AddParameter("?beforeStart", date(SQL_DATETIME, $start - 1));

			// Query #1 : Cari perubahan saldo dari awal januari sampai dengan sebelum periode + perubahan saldo pada periode dalam 1 query
			//	prev_xxx	=> Saldo Dari awal tahun s.d. Sebelum periode awal
			//	current_xxx	=> Saldo selama periode yang diminta
			$this->connector->CommandText =
"CREATE TEMPORARY TABLE rekap_transaksi AS
SELECT
	b.debtor_id,
	SUM(IF(b.acc_debit_id = d.acc_control_id AND a.voucher_date < ?start, b.amount, 0)) AS prev_debet,
	SUM(IF(b.acc_credit_id = d.acc_control_id AND a.voucher_date < ?start, b.amount, 0)) AS prev_kredit,
	SUM(IF(b.acc_debit_id = d.acc_control_id AND a.voucher_date >= ?start, b.amount, 0)) AS current_debet,
	SUM(IF(b.acc_credit_id = d.acc_control_id AND a.voucher_date >= ?start, b.amount, 0)) AS current_kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN ar_debtor_master AS c ON b.debtor_id = c.id
	JOIN ar_debtortype AS d ON c.debtortype_id = d.id
WHERE a.is_deleted = 0 AND a.status = 4 AND a.voucher_date BETWEEN ?firstJanuary AND ?end $subQueryWhere
GROUP BY b.debtor_id, c.debtor_cd, c.debtor_name;";
			$this->connector->ExecuteNonQuery();

			// Query #2 : Beres... Agar semua debtor muncul maka query nya harus dimulai dari debtor master kembali
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				$query =
"SELECT a.id, a.debtor_cd, a.debtor_name, b.*, c.debit_amount AS saldo_debet, c.credit_amount AS saldo_kredit
FROM ar_debtor_master AS a
 	JOIN rekap_transaksi AS b ON a.id = b.debtor_id
	LEFT JOIN ar_opening_balance AS c ON a.id = c.debtor_id
WHERE a.entity_id = ?sbu
ORDER BY a.debtor_cd";
			} else {
				$query =
"SELECT a.id, a.debtor_cd, a.debtor_name, b.*, c.debit_amount AS saldo_debet, c.credit_amount AS saldo_kredit
FROM ar_debtor_master AS a
	JOIN rekap_transaksi AS b ON a.id = b.debtor_id
	LEFT JOIN ar_opening_balance AS c ON a.id = c.debtor_id
ORDER BY a.debtor_cd";
			}
			$report = $this->connector->ExecuteQuery($query);
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = time();
			$output = "web";
			$report = null;

//			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
//				$this->Set("info", "Laporan Rekap Piutang Debtor ditujukan untuk Login Company. Harap impersonate terlebih dahulu.");
//			}
		}

		require_once(MODEL . "master/company.php");
		$company = new \Company();

		require_once(MODEL . "common/debtor_type.php");
		$debtorType = new \DebtorType();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$types = $debtorType->LoadAll("c.acc_no");
		} else {
			$types = $debtorType->LoadByEntity($this->userCompanyId, "c.acc_no");
		}

		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("accId", $accId);
		$this->Set("types", $types);
		$this->Set("output", $output);
		$this->Set("company", $company->LoadById($this->userCompanyId));
		$this->Set("report", $report);
	}

	public function rekap_aging() {
		if (count($this->getData) > 0) {
			$date = strtotime($this->GetGetValue("date") . " 23:59:59");
			$output = $this->GetGetValue("output");

			// Cara mencari aging = Cari Invoice berserta pembayarannya Jika belum lunas bearti piutang
			// NOTE: Query ini mirip dengan yang ada pada Invoice::LoadUnPaidInvoice()
			// Tetapi query di model tidak digunakan karena yang ini akan lebih ajaib hehehe
			// ToDo: Jika perubahan data pada model Invoice::LoadUnPaidInvoice() harus cek ini juga.

			// Step #01: Buat table temporary untuk menyimpan data Invoice belum lunasnya
			$this->connector->CommandText =
"CREATE TEMPORARY TABLE unpaid_invoices AS
SELECT a.*, (a.base_amount + a.vat_amount - a.wht_amount) - COALESCE(c.sum_allocated, 0) AS sum_piutang, c.sum_allocated AS sum_paid, DATEDIFF(?date, a.invoice_date) AS age
FROM t_ar_invoice_master AS a
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui OR baik yang sudah posting ! (dan juga ada batas max tanggal OR)
		-- ToDo: Jika status posting pada OR berubah yang ini juga harus ikut dirubah
		SELECT bb.invoice_id, SUM(bb.allocate_amount) AS sum_allocated
		FROM t_ar_receipt_master AS aa
			JOIN t_ar_receipt_detail AS bb ON aa.id = bb.receipt_id
		WHERE aa.is_deleted = 0 AND aa.receipt_status = 2 AND aa.receipt_date <= ?date
		GROUP BY bb.invoice_id
	) AS c ON a.id = c.invoice_id
-- Untuk mencari invoice yang belum lunas hanya yang sudah berstatus posting
-- ToDo: Jika status posted pada invoice berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.invoice_status > 0 AND (a.base_amount + a.vat_amount - a.wht_amount) - COALESCE(c.sum_allocated, 0) > 0 AND a.entity_id = ?sbu;";
			$this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
			$this->connector->AddParameter("?sbu", $this->userCompanyId);

			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				throw new \Exception("Error rekap_aging ! Step #01: temp table invoice belum lunas. Message: " . $this->connector->GetErrorMessage());
			}


			$this->connector->CommandText =
"SELECT a.*, b.sum_piutang_1, b.sum_piutang_2, b.sum_piutang_3, b.sum_piutang_4, b.sum_piutang_5, b.sum_piutang_6
FROM ar_debtor_master AS a
	LEFT JOIN (
		SELECT aa.debtor_id, SUM(IF(aa.age BETWEEN 0 AND 30, aa.sum_piutang, 0)) AS sum_piutang_1, SUM(IF(aa.age BETWEEN 31 AND 60, aa.sum_piutang, 0)) AS sum_piutang_2, SUM(IF(aa.age BETWEEN 61 AND 90, aa.sum_piutang, 0)) AS sum_piutang_3, SUM(IF(aa.age BETWEEN 91 AND 120, aa.sum_piutang, 0)) AS sum_piutang_4, SUM(IF(aa.age BETWEEN 121 AND 150, aa.sum_piutang, 0)) AS sum_piutang_5, SUM(IF(aa.age > 150, aa.sum_piutang, 0)) AS sum_piutang_6
		FROM unpaid_invoices AS aa
		GROUP BY aa.debtor_id
	) AS b ON a.id = b.debtor_id
WHERE a.entity_id = ?sbu
ORDER BY a.debtor_name ASC;";

			$report = $this->connector->ExecuteQuery();
		} else {
			$date = time();
			$output = "web";
			$report = null;

			if ($this->userCompanyId == 7 | $this->userCompanyId == null) {
				$this->Set("info", "Laporan ini hanya untuk login Company. Anda login CORP harap melakukan inpersonate terlebih dahulu.");
			}
		}

		require_once(MODEL . "master/company.php");
		$company = new \Company();

		$this->Set("date", $date);
		$this->Set("output", $output);
		$this->Set("company", $company->LoadById($this->userCompanyId));
		$this->Set("report", $report);
	}

	public function detail_aging() {
		require_once(MODEL . "master/debtor.php");

		$debtor =  new \Debtor();

		if (count($this->getData) > 0) {
			$debtorId = $this->GetGetValue("debtor");
			$date = strtotime($this->GetGetValue("date") . " 23:59:59");
			$output = $this->GetGetValue("output");

			// Ini query detail sih mirip dengan yang rekap bedanya tidak ada proses grouping

			$query =
"SELECT a.*, c.sum_allocated AS sum_paid, DATEDIFF(?date, a.invoice_date) AS age
FROM vw_ar_invoice_master AS a
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui OR baik yang sudah posting ! (dan juga ada batas max tanggal OR)
		-- ToDo: Jika status posting pada OR berubah yang ini juga harus ikut dirubah
		SELECT bb.invoice_id, SUM(bb.allocate_amount) AS sum_allocated
		FROM t_ar_receipt_master AS aa
			JOIN t_ar_receipt_detail AS bb ON aa.id = bb.receipt_id
		WHERE aa.is_deleted = 0 AND aa.receipt_status = 2 AND aa.receipt_date <= ?date
		GROUP BY bb.invoice_id
	) AS c ON a.id = c.invoice_id
	-- JOIN dengan debtor untuk cari nama dll
-- Untuk mencari invoice yang belum lunas hanya yang sudah berstatus posting
-- ToDo: Jika status posted pada invoice berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.invoice_status = 2 AND a.total_amount - COALESCE(c.sum_allocated, 0) > 0 AND a.entity_id = ?sbu %s
ORDER BY a.debtor_name ASC, a.invoice_date ASC;";
			if ($debtorId != null) {
				// Mari Kita Filter per debtor juga
				$this->connector->CommandText = sprintf($query, " AND a.debtor_id = ?debtorId");
				$this->connector->AddParameter("?debtorId", $debtorId);
			} else {
				$this->connector->CommandText = sprintf($query, "");
			}
			$this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
			$this->connector->AddParameter("?sbu", $this->userCompanyId);

			$report = $this->connector->ExecuteQuery();
		} else {
			$debtorId = null;
			$date = time();
			$output = "web";
			$report = null;

			if ($this->userCompanyId == 7 | $this->userCompanyId == null) {
				$this->Set("info", "Laporan ini hanya untuk login Company. Anda login CORP harap melakukan inpersonate terlebih dahulu.");
			}
		}

		require_once(MODEL . "master/company.php");
		$company = new \Company();

		$this->Set("debtorId", $debtorId);
		$this->Set("debtors", $debtor->LoadByEntity($this->userCompanyId));
		$this->Set("date", $date);
		$this->Set("output", $output);
		$this->Set("company", $company->LoadById($this->userCompanyId));
		$this->Set("report", $report);
	}
}

// End of file: report_controller.php
