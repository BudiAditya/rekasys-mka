<?php

// ToDo: Jika status POSTED berubah maka ini harus ikut diganti
class CashBookReportController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function rpt_in() {
		require_once(MODEL . "master/company.php");

		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$output = $this->GetGetValue("output", "web");
			$showNo = $this->GetGetValue("showNo", false);
			$showNo = $showNo == "1";

			// OK bikin querynya... (Untuk Bank masuk dilihat dari jenis dokumen BM dan OR)
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$query =
"SELECT a.id, a.voucher_date, a.doc_no, b.note, e.entity_cd, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
	JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (3, 12) -- AND c.parent_id IN (3, 8)
ORDER BY a.voucher_date, a.doc_no";
			} else {
				$query =
"SELECT a.id, a.voucher_date, a.doc_no, b.note, e.entity_cd, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
	JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.entity_id = ?sbu AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (3, 12) -- AND c.parent_id IN (3, 8)
ORDER BY a.voucher_date, a.doc_no";
				$this->connector->AddParameter("?sbu", $this->userCompanyId);
			}

			$this->connector->CommandText = $query;
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
			$report = $this->connector->ExecuteQuery();
		} else {
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$showNo = false;
			$report = null;
			$output = "web";
		}

		$company = new Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}

		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("showNo", $showNo);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("company", $company);
	}

	// Jangan terlalu curang deh... ntar bingung sendiri... untuk yang report outnya copas aja ga perlu bikin di 1 function
	public function rpt_out() {
		require_once(MODEL . "master/company.php");

		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$output = $this->GetGetValue("output", "web");
			$showNo = $this->GetGetValue("showNo", false);
			$showNo = $showNo == "1";

			// OK bikin querynya... (sama kaya yang diatas hanya beda disini kita cari untuk parent ID dari table alias kredit / 'd')
			// UPDATED: Untuk bank keluar hanya BK dan PV
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$query =
"SELECT a.id, a.voucher_date, a.doc_no, b.note, e.entity_cd, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
	JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (2, 14) -- AND d.parent_id IN (3, 8)
ORDER BY a.voucher_date, a.doc_no";
			} else {
				$query =
"SELECT a.id, a.voucher_date, a.doc_no, b.note, e.entity_cd, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
	JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.entity_id = ?sbu AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (2, 14) -- d.parent_id IN (3, 8)
ORDER BY a.voucher_date, a.doc_no";
				$this->connector->AddParameter("?sbu", $this->userCompanyId);
			}

			$this->connector->CommandText = $query;
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
			$report = $this->connector->ExecuteQuery();
		} else {
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$showNo = false;
			$report = null;
			$output = "web";
		}

		$company = new Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}

		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("showNo", $showNo);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("company", $company);
	}

	public function recap_in() {
		require_once(MODEL . "master/company.php");

		if (count($this->getData) > 0) {
			$noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

			$month = $this->GetGetValue("month");
			$year = $this->GetGetValue("year");
			$noOfDay = $noOfDays[$month];
			if ($month == 2 && $year % 4 == 0) {
				$noOfDay = 29;	// Leap Year
			}
			$output = $this->GetGetValue("output", "web");
			$firstJanuary = mktime(0,0, 0, 1, 1, $year);
			$startDate = mktime(0, 0, 0, $month, 1, $year);
			$endDate = mktime(0,0, 0, $month, $noOfDay, $year);

			// Setting global parameter (Jgn panggil ClearParameters() OK !)
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			if ($month > 1) {
				// Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
				$this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
				$this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
			}

			// OK dafuq ini... mari kita query multi step
			// Disini kita akan mengambil data dari semua dokumen BM dan OR

			// #01: Ambil sum semua debit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_debit AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (3, 12) %s
GROUP BY b.acc_debit_id;";
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->connector->CommandText = sprintf($query, "");
			} else {
				$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
			}
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua credit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_credit AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (3, 12) %s
GROUP BY b.acc_credit_id;";
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->connector->CommandText = sprintf($query, "");
			} else {
				$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
			}
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #03: Ambil data bulan-bulan sebelumnya (debet)
				$query =
"CREATE TEMPORARY TABLE sum_debit_prev AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND a.doc_type_id IN (3, 12) %s
GROUP BY b.acc_debit_id;";
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$this->connector->CommandText = sprintf($query, "");
				} else {
					$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
				}
				$this->connector->ExecuteNonQuery();

				// #04: Ambil data bulan-bulan sebelumnya (kredit)
				$query =
"CREATE TEMPORARY TABLE sum_credit_prev AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND a.doc_type_id IN (3, 12) %s
GROUP BY b.acc_credit_id;";
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$this->connector->CommandText = sprintf($query, "");
				} else {
					$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
				}
				$this->connector->ExecuteNonQuery();

				// #05: OK final query...
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev
FROM cm_acc_detail AS a
	LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
	LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
	LEFT JOIN sum_debit_prev AS d ON a.id = d.acc_debit_id
	LEFT JOIN sum_credit_prev AS e ON a.id = e.acc_credit_id
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) + COALESCE(d.total_debit_prev, 0) + COALESCE(e.total_credit_prev, 0) <> 0
ORDER BY a.acc_no";
			} else {
				// Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
				// Untuk data bulan-bulan sebelumnya selalu 0
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev
FROM cm_acc_detail AS a
	LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
	LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) > 0
ORDER BY a.acc_no";
			}

			$report = $this->connector->ExecuteQuery();
		} else {
			$month = (int)date("n");
			$year = (int)date("Y");
			$report = null;
			$output = "web";
		}

		$company = new Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}

		$this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("report", $report);
		$this->Set("output", $output);

		$this->Set("company", $company);
		$this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
	}

	public function recap_out() {
		require_once(MODEL . "master/company.php");

		if (count($this->getData) > 0) {
			$noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

			$month = $this->GetGetValue("month");
			$year = $this->GetGetValue("year");
			$noOfDay = $noOfDays[$month];
			if ($month == 2 && $year % 4 == 0) {
				$noOfDay = 29;	// Leap Year
			}
			$output = $this->GetGetValue("output", "web");
			$firstJanuary = mktime(0,0, 0, 1, 1, $year);
			$startDate = mktime(0, 0, 0, $month, 1, $year);
			$endDate = mktime(0,0, 0, $month, $noOfDay, $year);

			// Setting global parameter (Jgn panggil ClearParameters() OK !)
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			if ($month > 1) {
				// Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
				$this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
				$this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
			}

			// OK dafuq ini... mari kita query multi step
			// Disini kita akan mengambil data dari semua dokumen BM dan OR

			// #01: Ambil sum semua debit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_debit AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (2, 14) %s
GROUP BY b.acc_debit_id;";
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->connector->CommandText = sprintf($query, "");
			} else {
				$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
			}
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua credit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_credit AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (2, 14) %s
GROUP BY b.acc_credit_id;";
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->connector->CommandText = sprintf($query, "");
			} else {
				$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
			}
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #03: Ambil data bulan-bulan sebelumnya (debet)
				$query =
"CREATE TEMPORARY TABLE sum_debit_prev AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND a.doc_type_id IN (2, 14) %s
GROUP BY b.acc_debit_id;";
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$this->connector->CommandText = sprintf($query, "");
				} else {
					$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
				}
				$this->connector->ExecuteNonQuery();

				// #04: Ambil data bulan-bulan sebelumnya (kredit)
				$query =
"CREATE TEMPORARY TABLE sum_credit_prev AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND a.doc_type_id IN (2, 14) %s
GROUP BY b.acc_credit_id;";
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$this->connector->CommandText = sprintf($query, "");
				} else {
					$this->connector->CommandText = sprintf($query, "AND a.entity_id = " . $this->userCompanyId);
				}
				$this->connector->ExecuteNonQuery();

				// #05: OK final query...
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev
FROM cm_acc_detail AS a
	LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
	LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
	LEFT JOIN sum_debit_prev AS d ON a.id = d.acc_debit_id
	LEFT JOIN sum_credit_prev AS e ON a.id = e.acc_credit_id
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) + COALESCE(d.total_debit_prev, 0) + COALESCE(e.total_credit_prev, 0) <> 0
ORDER BY a.acc_no";
			} else {
				// Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
				// Untuk data bulan-bulan sebelumnya selalu 0
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev
FROM cm_acc_detail AS a
	LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
	LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) > 0
ORDER BY a.acc_no";
			}

			$report = $this->connector->ExecuteQuery();
		} else {
			$month = (int)date("n");
			$year = (int)date("Y");
			$report = null;
			$output = "web";
		}

		$company = new Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}

		$this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("report", $report);
		$this->Set("output", $output);

		$this->Set("company", $company);
		$this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
	}

	/**
	 * Ini laporan untuk menampilkan semua BKK dan BKM yang sudah di input
	 */
	public function bkk_bkm() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "accounting/opening_balance.php");

		if (count($this->getData) > 0) {
			$accountId = $this->GetGetValue("account");
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$output = $this->GetGetValue("output", "web");
			$orientation = strtoupper($this->GetGetValue("orientation", "p"));
			$status = $this->GetGetValue("status", "");
			if (!in_array($orientation, array("P", "L"))) {
				$orientation = "P";
			}

			if ($accountId == "") {
				$obal = null;
				$obalTransaction = null;
				$report = null;
				$this->Set("error", "Mohon pilih akun terlebih dahulu.");
			} else {
				$info = array();
				// OK kasi tahu user kalau status dokumen bukan POSTED akan mempengaruhi saldo akhir
				if ($status != 4) {
					$this->Set("info", "Perhitungan Saldo Awal menggunakan semua voucher yang berstatus POSTED.");
				}

				// Cari data Opening balance
				$obal = new OpeningBalance();
				$obal->LoadByAccount($accountId, date("Y", $start));
				if ($obal->Id == null && $obal->GetCoa()->IsOpeningBalanceRequired()) {
					$info[] = "Akun yang dipilih diharuskan memiliki Opening Balance tetapi data Tidak ditemukan !";
				}
				$temp = $start - 86400;
				if ($status != 4) {
					$obalTransaction = $obal->CalculateTransaction($temp, -1);
					$info[] = "Perhitungan Saldo Awal menggunakan semua voucher karena anda tidak memilih status POSTED";
				} else {
					$obalTransaction = $obal->CalculateTransaction($temp);
				}
				if (count($info) > 0) {
					$this->Set("info", implode("<br />", $info));
				}

				$query =
"SELECT a.id, a.voucher_date, a.doc_no, a.doc_type_id, b.note, e.entity_cd, b.acc_debit_id, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, b.acc_credit_id, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit, g.dept_code,g.dept_name,f.act_code,f.act_name,h.project_cd,h.project_name
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
	JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
	LEFT JOIN cm_activity AS f ON b.activity_id = f.id
	LEFT JOIN cm_dept AS g ON b.dept_id = g.id
	LEFT JOIN cm_project AS h ON b.project_id = h.id
WHERE a.is_deleted = 0 AND (b.acc_debit_id = ?acc OR b.acc_credit_id = ?acc) AND a.entity_id = ?sbu AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN (1, 2, 3, 12, 14) %s
ORDER BY a.voucher_date, a.doc_no";
				$this->connector->AddParameter("?sbu", $this->userCompanyId);

				if ($status == "") {
					$this->connector->CommandText = sprintf($query, "");
				} else {
					$this->connector->CommandText = sprintf($query, " AND a.status = ?status");
					$this->connector->AddParameter("?status", $status);
				}
				$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
				$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
				$this->connector->AddParameter("?acc", $accountId);
				$rs = $this->connector->ExecuteQuery();
				$report = array();
				$prevId = null;
				$master = null;
				while ($row = $rs->FetchAssoc()) {
					// Convert to native format
					$row["voucher_date"] = strtotime($row["voucher_date"]);

					if ($prevId != $row["id"]) {
						if ($master != null) {
							// -_-a... ini array seharunya refernce tetapi si PHP tidak meng-treat sebagai reference ketika di assign malah copy by value instead of by refernce
							$report[] = $master;
						}

						$prevId = $row["id"];
						$tokens = explode("/", $row["doc_no"]);
						$master = array(
							"id" => $row["id"],
							"date" => $row["voucher_date"],
							"counter" => (int)end($tokens),
							"debit" => 0,
							"credit" => 0,
							"details" => array()
						);
					}
					// Proses data
					if ($accountId == $row["acc_debit_id"]) {
						// Akun yang diminta ada pada posisi debit
						$master["debit"] += $row["amount"];
						$row["debit"] = $row["amount"];
						$row["credit"] = 0;
						$row["opposite_no"] = $row["acc_no_credit"];
						$row["opposite_name"] = $row["acc_credit"];
					} else {
						// Karena bukan pada posisi debit maka kita asumsikan ada pada posisi credit
						$master["credit"] += $row["amount"];
						$row["debit"] = 0;
						$row["credit"] = $row["amount"];
						$row["opposite_no"] = $row["acc_no_debit"];
						$row["opposite_name"] = $row["acc_debit"];
					}
					// Tambahkan detail ke master harus paling akhir karena PHP TIDAK meng-treat array dengan copy by reference ! AHO !
					$master["details"][] = $row;
				}
				// Ia yang terakhir lupa di add... -_-a...
				if ($master != null) {
					// -_-a... ini array seharunya refernce tetapi si PHP tidak meng-treat sebagai reference ketika di assign malah copy by value instead of by refernce
					$report[] = $master;
				}

				// OK disini harusnya datanya sudah beres  mari kita sorting
				usort($report, function($lhs, $rhs) {
					// Sorting utama hanya dilakukan jika tanggal sama
					if ($lhs["date"] == $rhs["date"]) {
						// Tanggal sama mari kita mulai logic sorting dimana debet harus muncul diatas dan akan kita urut berdasarkan nomor
						if ($lhs["debit"] > 0) {
							// OK debet ada nilainya
							if ($rhs["debit"] > 0) {
								// OK pada sisi kanan juga ada debet. Jika counter lebih kecil maka harus naik
								return $lhs["counter"] < $rhs["counter"] ? -1 : 1;
							} else if ($rhs["credit"] > 0) {
								// Sisi kanan tidak memiliki debet harusnya punya kredit. Harus naik
								return -1;
							} else {
								throw new Exception("DEBUG 1");
							}
						} else {
							// OK Debet kosong mari kita lihat dari sisi kredit
							if ($rhs["credit"] > 0) {
								// OK pada sisi kanan juga ada kredit. Jika counter lebih kecil maka harus naik
								return $lhs["counter"] < $rhs["counter"] ? -1 : 1;
							} else if ($rhs["debit"] > 0) {
								// Sisi kanan memiliki debit sehingga ini harus turun
								return 1;
							} else {
								throw new Exception("DEBUG 2");
							}
						}

					} else if ($lhs["date"] < $rhs["date"]) {
						// Tanggal kiri lebih kecil... ya uda bearti harus ber ada di atas...
						return -1;
					} else {
						// Sisanya tanggal kiri lebih besar daripada kanan ya harus diturunin
						return 1;
					}
				});
			}
		} else {
			$obal = null;
			$obalTransaction = null;

			$accountId = null;
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$status = "";
			$report = null;
			$output = "web";
			$orientation = "p";
		}

		// Cari data login companynya
		$company = new Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}
		// OK cari data CoA
		require_once(MODEL . "master/coa.php");
		$account = new Coa();

		$this->Set("obal", $obal);
		$this->Set("obalTransaction", $obalTransaction);
		$this->Set("accountId", $accountId);
		$this->Set("accounts", $account->LoadLevel3ByLevel2($this->userCompanyId,array(40, 41)));
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("status", $status);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("orientation", $orientation);
		$this->Set("company", $company);
	}

    public function pettycash() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "accounting/opening_balance.php");
        require_once(MODEL . "master/project.php");
        $cprojects = null;
        if (count($this->postData) > 0) {
            $accountId = $this->GetPostValue("account");
            $start = strtotime($this->GetPostValue("start"));
            $end = strtotime($this->GetPostValue("end"));
            $output = $this->GetPostValue("output", "web");
            $orientation = strtoupper($this->GetPostValue("orientation", "p"));
            $status = $this->GetPostValue("status", "");
            $cproids = $this->GetPostValue("cProjectId",array());
            if (!in_array($orientation, array("P", "L"))) {
                $orientation = "P";
            }

            if ($accountId == "") {
                $obal = null;
                $obalTransaction = null;
                $report = null;
                $this->Set("error", "Mohon pilih akun terlebih dahulu.");
            } else {
                $info = array();
                // OK kasi tahu user kalau status dokumen bukan POSTED akan mempengaruhi saldo akhir
                if ($status != 4) {
                    $this->Set("info", "Perhitungan Saldo Awal menggunakan semua voucher yang berstatus POSTED.");
                }

                // Cari data Opening balance
                $obal = new OpeningBalance();
                $obal->LoadByAccount($accountId, date("Y", $start));
                if ($obal->Id == null && $obal->GetCoa()->IsOpeningBalanceRequired()) {
                    $info[] = "Akun yang dipilih diharuskan memiliki Opening Balance tetapi data Tidak ditemukan !";
                }
                $temp = $start - 86400;
                if ($status != 4) {
                    $obalTransaction = $obal->CalculateTransaction($temp, -1);
                    $info[] = "Perhitungan Saldo Awal menggunakan semua voucher karena anda tidak memilih status POSTED";
                } else {
                    $obalTransaction = $obal->CalculateTransaction($temp);
                }
                if (count($info) > 0) {
                    $this->Set("info", implode("<br />", $info));
                }

                $query =
                    "SELECT a.id, a.voucher_date, a.doc_no, a.doc_type_id, b.note, e.entity_cd, b.acc_debit_id, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, b.acc_credit_id, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit, g.dept_code,g.dept_name,f.act_code,f.act_name,h.project_cd,h.project_name
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
	JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
	LEFT JOIN cm_activity AS f ON b.activity_id = f.id
	LEFT JOIN cm_dept AS g ON b.dept_id = g.id
	LEFT JOIN cm_project AS h ON b.project_id = h.id
WHERE a.is_deleted = 0 AND (b.acc_debit_id = ?acc OR b.acc_credit_id = ?acc) AND a.entity_id = ?sbu AND a.voucher_date BETWEEN ?start AND ?end ";
                if (count($cproids) > 0) {
                    foreach ($cproids as $proid){
                        $cprojects.= $proid[0].',';
                    }
                    $cprojects = substr($cprojects, 0, -1);
                    $query.= " AND b.project_id IN ($cprojects)";
                }

                $query.= "%s ORDER BY a.voucher_date, a.doc_no";
                $this->connector->AddParameter("?sbu", $this->userCompanyId);

                if ($status == "") {
                    $this->connector->CommandText = sprintf($query, "");
                } else {
                    $this->connector->CommandText = sprintf($query, " AND a.status = ?status");
                    $this->connector->AddParameter("?status", $status);
                }

                $this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
                $this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
                $this->connector->AddParameter("?acc", $accountId);
                $rs = $this->connector->ExecuteQuery();
                $report = array();
                $prevId = null;
                $master = null;
                while ($row = $rs->FetchAssoc()) {
                    // Convert to native format
                    $row["voucher_date"] = strtotime($row["voucher_date"]);

                    if ($prevId != $row["id"]) {
                        if ($master != null) {
                            // -_-a... ini array seharunya refernce tetapi si PHP tidak meng-treat sebagai reference ketika di assign malah copy by value instead of by refernce
                            $report[] = $master;
                        }

                        $prevId = $row["id"];
                        $tokens = explode("/", $row["doc_no"]);
                        $master = array(
                            "id" => $row["id"],
                            "date" => $row["voucher_date"],
                            "counter" => (int)end($tokens),
                            "debit" => 0,
                            "credit" => 0,
                            "details" => array()
                        );
                    }
                    // Proses data
                    if ($accountId == $row["acc_debit_id"]) {
                        // Akun yang diminta ada pada posisi debit
                        $master["debit"] += $row["amount"];
                        $row["debit"] = $row["amount"];
                        $row["credit"] = 0;
                        $row["opposite_no"] = $row["acc_no_credit"];
                        $row["opposite_name"] = $row["acc_credit"];
                    } else {
                        // Karena bukan pada posisi debit maka kita asumsikan ada pada posisi credit
                        $master["credit"] += $row["amount"];
                        $row["debit"] = 0;
                        $row["credit"] = $row["amount"];
                        $row["opposite_no"] = $row["acc_no_debit"];
                        $row["opposite_name"] = $row["acc_debit"];
                    }
                    // Tambahkan detail ke master harus paling akhir karena PHP TIDAK meng-treat array dengan copy by reference ! AHO !
                    $master["details"][] = $row;
                }
                // Ia yang terakhir lupa di add... -_-a...
                if ($master != null) {
                    // -_-a... ini array seharunya refernce tetapi si PHP tidak meng-treat sebagai reference ketika di assign malah copy by value instead of by refernce
                    $report[] = $master;
                }

                // OK disini harusnya datanya sudah beres  mari kita sorting
                usort($report, function($lhs, $rhs) {
                    // Sorting utama hanya dilakukan jika tanggal sama
                    if ($lhs["date"] == $rhs["date"]) {
                        // Tanggal sama mari kita mulai logic sorting dimana debet harus muncul diatas dan akan kita urut berdasarkan nomor
                        if ($lhs["debit"] > 0) {
                            // OK debet ada nilainya
                            if ($rhs["debit"] > 0) {
                                // OK pada sisi kanan juga ada debet. Jika counter lebih kecil maka harus naik
                                return $lhs["counter"] < $rhs["counter"] ? -1 : 1;
                            } else if ($rhs["credit"] > 0) {
                                // Sisi kanan tidak memiliki debet harusnya punya kredit. Harus naik
                                return -1;
                            } else {
                                throw new Exception("DEBUG 1");
                            }
                        } else {
                            // OK Debet kosong mari kita lihat dari sisi kredit
                            if ($rhs["credit"] > 0) {
                                // OK pada sisi kanan juga ada kredit. Jika counter lebih kecil maka harus naik
                                return $lhs["counter"] < $rhs["counter"] ? -1 : 1;
                            } else if ($rhs["debit"] > 0) {
                                // Sisi kanan memiliki debit sehingga ini harus turun
                                return 1;
                            } else {
                                throw new Exception("DEBUG 2");
                            }
                        }

                    } else if ($lhs["date"] < $rhs["date"]) {
                        // Tanggal kiri lebih kecil... ya uda bearti harus ber ada di atas...
                        return -1;
                    } else {
                        // Sisanya tanggal kiri lebih besar daripada kanan ya harus diturunin
                        return 1;
                    }
                });
            }
        } else {
            $obal = null;
            $obalTransaction = null;
            $accountId = null;
            $end = time();
            $start = mktime(0, 0, 0, date("m"), 1, date("Y"));
            $status = "";
            $report = null;
            $output = "web";
            $orientation = "p";
            $cprojects = 0;
        }

        // Cari data login companynya
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        //load project
        $projects = new Project();
        $projects = $projects->LoadByEntityId($this->userCompanyId);
        // OK cari data CoA
        require_once(MODEL . "master/coa.php");
        $account = new Coa();

        $this->Set("obal", $obal);
        $this->Set("obalTransaction", $obalTransaction);
        $this->Set("accountId", $accountId);
        $this->Set("accounts", $account->LoadLevel3ByLevel2($this->userCompanyId,array(40, 41)));
        $this->Set("start", $start);
        $this->Set("end", $end);
        $this->Set("status", $status);
        $this->Set("report", $report);
        $this->Set("output", $output);
        $this->Set("orientation", $orientation);
        $this->Set("company", $company);
        $this->Set("projects", $projects);
        $this->Set("cprojects", $cprojects);
    }
}

// End of file: cashbookreport_controller.php
