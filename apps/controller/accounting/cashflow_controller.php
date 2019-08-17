<?php

/**
 * Hmmm sepertinya class ini isinya berupa report saja deh...
 * Beberapa tehnik disini ada yang digunakan di BukuTambahanController.
 *
 * @see BukuTambahanController
 */
class CashFlowController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function rpt_detail() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "accounting/opening_balance.php");

		if (count($this->getData) > 0) {
			$accountId = $this->GetGetValue("account");
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$output = $this->GetGetValue("output", "web");

			if ($accountId == "") {
				// Ga pilih akun
				$openingBalance = null;
				$transaction = null;
				$report = null;
				$this->Set("error", "Mohon pilih akun cash flow terlebih dahulu.");
			} else {
				// OK Data utama ada mari kita proses....

				$openingBalance = new OpeningBalance();
				$openingBalance->LoadByAccount($accountId, date("Y", $start));
				if ($openingBalance->Id == null && $openingBalance->GetCoa()->IsOpeningBalanceRequired()) {
					$this->Set("info", "Akun yang dipilih diharuskan memiliki Opening Balance tetapi data Tidak ditemukan !");
				}
				$temp = $start - 86400;
				$transaction = $openingBalance->CalculateTransaction($temp);

				// Laporan cash flow tidak akan di filter Company karena bila difilter maka datanya tidak realistic
				$query =
"SELECT a.id, a.voucher_date, a.doc_no, b.note, b.acc_debit_id, b.acc_credit_id, b.amount, e.entity_cd, c.act_code, c.act_name, d.dept_code, d.dept_name, f.project_cd,f.project_name,g.unit_code,g.unit_name
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
	LEFT JOIN cm_activity AS c ON b.activity_id = c.id
	LEFT JOIN cm_dept AS d ON c.dept_id = d.id
	JOIN cm_company AS e ON a.entity_id = e.entity_id
	LEFT JOIN cm_project AS f ON b.project_id = f.id
	LEFT JOIN cm_units AS g ON b.unit_id = g.id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
	AND (b.acc_debit_id = ?accId OR b.acc_credit_id = ?accId)
ORDER BY a.voucher_date, a.doc_no";

				$this->connector->CommandText = $query;
				$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
				$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
				$this->connector->AddParameter("?accId", $accountId);

				$report = $this->connector->ExecuteQuery();
			}
		} else {
			$accountId = null;
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$openingBalance = null;
			$transaction = null;
			$report = null;
			$output = "web";
		}

		// Cari data login companynya
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		// OK cari data CoA
		$parentIds = array(40, 41);
		$accounts = array();
		foreach ($parentIds as $id) {
			$account = new Coa();
			$account->FindById($id);
			$accounts[] = array("Parent" => $account, "SubAccounts" => $account->LoadByAccParentId($this->userCompanyId,$account->Id));
		}

		$this->Set("accountId", $accountId);
		$this->Set("accounts", $accounts);
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("openingBalance", $openingBalance);
		$this->Set("transaction", $transaction);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("company", $company);
	}

	public function rpt_recap() {
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
			// #01: Filter account yang akan digunakan pada report (Hanya yang parent ID nya 3, 8 alias kas dan pendapatan)
			$this->connector->CommandText =
"CREATE TEMPORARY TABLE acc_id AS
SELECT a.id, a.acc_no, a.acc_name
FROM cm_acc_detail AS a
WHERE a.is_deleted = 0 AND a.acc_parent_id IN (40, 41)";
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua debit pada periode yang diminta
			$this->connector->CommandText =
"CREATE TEMPORARY TABLE sum_debit AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
	AND b.acc_debit_id IN (SELECT id FROM acc_id)
GROUP BY b.acc_debit_id;";
			$this->connector->ExecuteNonQuery();

			// #03: Ambil sum semua credit pada periode yang diminta
			$this->connector->CommandText =
"CREATE TEMPORARY TABLE sum_credit AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
	AND b.acc_credit_id IN (SELECT id FROM acc_id)
GROUP BY b.acc_credit_id;";
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #04: Ambil data bulan-bulan sebelumnya (debet)
				$this->connector->CommandText =
"CREATE TEMPORARY TABLE sum_debit_prev AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev
	AND b.acc_debit_id IN (SELECT id FROM acc_id)
GROUP BY b.acc_debit_id;";
				$this->connector->ExecuteNonQuery();

				// #05: Ambil data bulan-bulan sebelumnya (kredit)
				$this->connector->CommandText =
"CREATE TEMPORARY TABLE sum_credit_prev AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.status = 4 AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev
	AND b.acc_credit_id IN (SELECT id FROM acc_id)
GROUP BY b.acc_credit_id;";
				$this->connector->ExecuteNonQuery();

				// #06: OK final query...
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev
FROM acc_id AS a
	LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
	LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
	LEFT JOIN sum_debit_prev AS d ON a.id = d.acc_debit_id
	LEFT JOIN sum_credit_prev AS e ON a.id = e.acc_credit_id
ORDER BY a.acc_no";
			} else {
				// Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
				// Untuk data bulan-bulan sebelumnya selalu 0
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev
FROM acc_id AS a
	LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
	LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
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
}


// End of File: cashflow_controller.php
