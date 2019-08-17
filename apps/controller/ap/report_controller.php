<?php
namespace Ap;

/**
 * Class ReportController
 * @package Ap
 *
 * Namespace supported now....
 */
class ReportController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function kartu_hutang() {
		require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "master/company.php");
        $company = new \Company($this->userCompanyId);
        $startDate = $company->StartDate;
		$creditor =  new \Creditor();
		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$supplierId = $this->GetGetValue("supplier");
			$output = $this->GetGetValue("output");
			$report = null;
			$saldoAwal = null;

			if (!is_int($start) || !is_int($end) || $supplierId == "") {
				$this->Set("error", "Maaf data yang dikirim tidak lengkap mohon ulangi proses.");
			} else {
				$flagOk = true;
				$creditor = $creditor->LoadById($supplierId);

				// Validate data....
				if ($creditor == null || $creditor->IsDeleted) {
					$this->Set("error", "Supplier yang diminta tidak dapat ditemukan mohon ulangi proses.");
					$flagOk = false;
				} else if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
					if ($creditor->EntityId != $this->userCompanyId) {
						// Beuh... simulate not found
						$this->Set("error", "Supplier yang diminta tidak dapat ditemukan mohon ulangi proses.");
						$flagOk = false;
					}
				}

				// OK kita proses kalau ada data yang OK
				if ($flagOk) {
					// Step #01: Ambil data Saldo Awal dari table saldo awal
					require_once(MODEL . "ap/opening_balance.php");
					$obal = new OpeningBalance();
					$obal->LoadByCreditor($supplierId,strtotime($startDate));
					if ($obal->Id != null) {
						$result = $obal->CalculateTransaction($start);
						$saldoAwal = $obal->CreditAmount - $obal->DebitAmount + $result["kredit"] - $result["debet"];
					} else {
						$saldoAwal = 0;
					}

					$firstJanuary = mktime(0, 0, 0, 1, 1, date("Y", $start));
					// Setting parameter query
					$this->connector->ClearParameter();
					$this->connector->AddParameter("?supplierId", $supplierId);
					$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
					$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
					$this->connector->AddParameter("?firstJanuary", date(SQL_DATETIME, $firstJanuary));
					$this->connector->AddParameter("?beforeStart", date(SQL_DATETIME, $start - 1));

					// ToDo: Perubahan status posting dari Voucher juga harus merubah ini
					// Step #02: Cari data perubahan saldo dari awal januari s.d. sebelum tanggal mulai jika diperlukan
					if ($start > $firstJanuary) {
						$this->connector->CommandText =
"SELECT SUM(IF(b.acc_debit_id = d.acc_control_id, b.amount, 0)) AS debet, SUM(IF(b.acc_credit_id = d.acc_control_id, b.amount, 0)) AS kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN ap_creditor_master AS c ON b.creditor_id = c.id
	JOIN ap_creditortype AS d ON c.creditortype_id = d.id
WHERE a.is_deleted = 0 AND a.status = 4 AND b.creditor_id = ?supplierId AND a.voucher_date BETWEEN ?firstJanuary AND ?beforeStart";
						$rs = $this->connector->ExecuteQuery();
						if ($rs == null) {
							$this->Set("error", "Terjadi error saat mengambil data saldo awal ! Harap segera menghubungi system administrator anda.<br />Message: " . $this->connector->GetErrorMessage());
						} else {
							$row = $rs->FetchAssoc();
							$saldoAwal += $row["kredit"] - $row["debet"];
						}
					}

					// Step #03: Cari data transaksi berdasarkan periode
					// ToDo: Perubahan status posting Voucher juga harus merubah ini
					$this->connector->CommandText =
"SELECT a.id, a.doc_no, a.voucher_date, a.note, Sum(IF(b.acc_debit_id = d.acc_control_id, b.amount, 0)) AS debet, Sum(IF(b.acc_credit_id = d.acc_control_id, b.amount, 0)) AS kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN ap_creditor_master AS c ON b.creditor_id = c.id
	JOIN ap_creditortype AS d ON c.creditortype_id = d.id
WHERE a.is_deleted = 0 AND a.status = 4 AND b.creditor_id = ?supplierId AND a.voucher_date BETWEEN ?start AND ?end
GROUP BY a.id, a.doc_no, a.voucher_date, a.note
ORDER BY a.voucher_date ASC	;";
					$report = $this->connector->ExecuteQuery();
				}
			}
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = time();
			$supplierId = null;
			$output = "web";

			$report = null;
			$saldoAwal = null;
		}


		if ($this->userCompanyId == null || $this->userCompanyId == 7) {
			$this->Set("suppliers", $creditor->LoadAll());
		} else {
			$this->Set("suppliers", $creditor->LoadByEntity($this->userCompanyId));
		}
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("supplierId", $supplierId);
		$this->Set("output", $output);
		$this->Set("report", $report);
		$this->Set("saldoAwal", $saldoAwal);
	}

	public function rekap_hutang() {
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
	b.creditor_id,
	SUM(IF(b.acc_debit_id = d.acc_control_id AND a.voucher_date < ?start, b.amount, 0)) AS prev_debet,
	SUM(IF(b.acc_credit_id = d.acc_control_id AND a.voucher_date < ?start, b.amount, 0)) AS prev_kredit,
	SUM(IF(b.acc_debit_id = d.acc_control_id AND a.voucher_date >= ?start, b.amount, 0)) AS current_debet,
	SUM(IF(b.acc_credit_id = d.acc_control_id AND a.voucher_date >= ?start, b.amount, 0)) AS current_kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN ap_creditor_master AS c ON b.creditor_id = c.id
	JOIN ap_creditortype AS d ON c.creditortype_id = d.id
WHERE a.is_deleted = 0 AND a.status = 4 AND a.voucher_date BETWEEN ?firstJanuary AND ?end $subQueryWhere
GROUP BY b.creditor_id, c.creditor_cd, c.creditor_name;";
			$this->connector->ExecuteNonQuery();

			// Query #2 : Beres... tinggal join ke table saldo awal tahun
			$query =
"SELECT a.id, a.creditor_cd, a.creditor_name, b.*, c.debit_amount AS saldo_debet, c.credit_amount AS saldo_kredit
FROM ap_creditor_master AS a
 	JOIN rekap_transaksi AS b ON a.id = b.creditor_id
	LEFT JOIN ap_opening_balance AS c ON a.id = c.creditor_id
WHERE a.entity_id = ?sbu
ORDER BY a.creditor_cd";
			$report = $this->connector->ExecuteQuery($query);
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = time();
			$output = "web";
			$report = null;

//			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
//				$this->Set("info", "Laporan Rekap Hutang Supplier ditujukan untuk Login Company. Harap impersonate terlebih dahulu.");
//			}
		}

		require_once(MODEL . "master/company.php");
		$company = new \Company();
		require_once(MODEL . "common/creditor_type.php");
		$creditorType = new \CreditorType();
		$types = $creditorType->LoadByEntity($this->userCompanyId);

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

			// Cara mencari aging = Cari Invoice Supplier berserta pembayarannya Jika belum lunas bearti hutang masih ada
			// NOTE: Query ini mirip dengan yang ada pada Invoice::LoadUnPaidInvoice()
			// Tetapi query di model tidak digunakan karena yang ini akan lebih ajaib hehehe
			// ToDo: Jika perubahan data pada model Invoice::LoadUnPaidInvoice() harus cek ini juga.

			// Step #01.1: Buat table temporary untuk menyimpan data Invoice belum lunasnya
			$this->connector->CommandText =
"-- #01: Cari data hutang dari Invoice AP yang di entry secara manual
CREATE TEMPORARY TABLE unpaid_invoices AS
SELECT a.entity_id, a.creditor_id, a.invoice_no, a.total_amount - COALESCE(c.sum_paid, 0) AS sum_hutang, DATEDIFF(?date, a.invoice_date) AS age
FROM vw_ap_invoice_master AS a
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui PV baik yang sudah posting ! (dan juga ada batas max tanggal PV)
		-- ToDo: Jika status posting pada PV berubah yang ini juga harus ikut dirubah
		SELECT bb.invoice_id, SUM(bb.allocate_amount) AS sum_paid
		FROM t_ap_payment_master AS aa
			JOIN t_ap_payment_detail AS bb ON aa.id = bb.payment_id
		WHERE aa.is_deleted = 0 AND aa.payment_status = 2 AND aa.payment_date <= ?date
		GROUP BY bb.invoice_id
	) AS c ON a.id = c.invoice_id
-- Untuk mencari invoice yang belum lunas hanya yang sudah berstatus posting
-- ToDo: Jika status posted pada invoice berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.invoice_status = 2 AND a.total_amount - COALESCE(c.sum_paid, 0) > 0 AND a.entity_id = ?sbu;";
			$this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
			$this->connector->AddParameter("?sbu", $this->userCompanyId);

			$rs = $this->connector->ExecuteNonQuery();
			if ($rs == -1) {
				throw new \Exception("Error rekap_aging ! Step #01.1: temp table invoice belum lunas. Message: " . $this->connector->GetErrorMessage());
			}

			$this->connector->CommandText =
"SELECT a.*, b.sum_hutang_1, b.sum_hutang_2, b.sum_hutang_3, b.sum_hutang_4, b.sum_hutang_5, b.sum_hutang_6
FROM ap_creditor_master AS a
	LEFT JOIN (
		SELECT aa.creditor_id, SUM(IF(aa.age BETWEEN 0 AND 30, aa.sum_hutang, 0)) AS sum_hutang_1, SUM(IF(aa.age BETWEEN 31 AND 60, aa.sum_hutang, 0)) AS sum_hutang_2, SUM(IF(aa.age BETWEEN 61 AND 90, aa.sum_hutang, 0)) AS sum_hutang_3, SUM(IF(aa.age BETWEEN 91 AND 120, aa.sum_hutang, 0)) AS sum_hutang_4, SUM(IF(aa.age BETWEEN 121 AND 150, aa.sum_hutang, 0)) AS sum_hutang_5, SUM(IF(aa.age > 150, aa.sum_hutang, 0)) AS sum_hutang_6
		FROM unpaid_invoices AS aa
		GROUP BY aa.creditor_id
	) AS b ON a.id = b.creditor_id
WHERE a.entity_id = ?sbu
ORDER BY a.creditor_name ASC;";

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
		require_once(MODEL . "master/creditor.php");

		$creditor =  new \Creditor();

		if (count($this->getData) > 0) {
			$creditorId = $this->GetGetValue("creditor");
			$date = strtotime($this->GetGetValue("date") . " 23:59:59");
			$output = $this->GetGetValue("output");

			// Ini query detail sih mirip dengan yang rekap bedanya tidak ada proses grouping

			$query =
"SELECT 'IV' AS source, a.id, a.entity_id, a.creditor_id as supplier_id, a.invoice_no as doc_no, a.invoice_date as doc_date, a.total_amount as sum_amount, c.sum_paid, DATEDIFF(?date, a.invoice_date) AS age, d.creditor_cd, d.creditor_name
FROM vw_ap_invoice_master AS a
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui PV baik yang sudah posting ! (dan juga ada batas max tanggal PV)
		-- ToDo: Jika status posting pada PV berubah yang ini juga harus ikut dirubah
		SELECT bb.invoice_id, SUM(bb.allocate_amount) AS sum_paid
		FROM t_ap_payment_master AS aa
			JOIN t_ap_payment_detail AS bb ON aa.id = bb.payment_id
		WHERE aa.is_deleted = 0 AND aa.payment_status = 2 AND aa.payment_date <= ?date
		GROUP BY bb.invoice_id
	) AS c ON a.id = c.invoice_id
	-- JOIN dengan creditor untuk cari nama dll
	JOIN ap_creditor_master AS d ON a.creditor_id = d.id
-- Untuk mencari invoice yang belum lunas hanya yang sudah berstatus posting
-- ToDo: Jika status posted pada invoice berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.invoice_status = 2 AND a.total_amount - COALESCE(c.sum_paid, 0) > 0 AND a.entity_id = ?sbu %s
ORDER BY d.creditor_name ASC, a.invoice_date ASC;";
			if ($creditorId != null) {
				// Mari Kita Filter per debtor juga
				$this->connector->CommandText = sprintf($query, " AND a.creditor_id = ?supplierId", " AND a.creditor_id = ?supplierId");
				$this->connector->AddParameter("?supplierId", $creditorId);
			} else {
				$this->connector->CommandText = sprintf($query, "", "");
			}
			$this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
			$this->connector->AddParameter("?sbu", $this->userCompanyId);

			$report = $this->connector->ExecuteQuery();
		} else {
			$creditorId = null;
			$date = time();
			$output = "web";
			$report = null;

			if ($this->userCompanyId == 7 | $this->userCompanyId == null) {
				$this->Set("info", "Laporan ini hanya untuk login Company. Anda login CORP harap melakukan inpersonate terlebih dahulu.");
			}
		}

		require_once(MODEL . "master/company.php");
		$company = new \Company();

		$this->Set("creditorId", $creditorId);
		$this->Set("creditors", $creditor->LoadByEntity($this->userCompanyId));
		$this->Set("date", $date);
		$this->Set("output", $output);
		$this->Set("company", $company->LoadById($this->userCompanyId));
		$this->Set("report", $report);
	}
}

// End of file: report_controller.php
