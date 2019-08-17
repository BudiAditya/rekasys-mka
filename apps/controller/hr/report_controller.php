<?php

namespace Hr;

/**
 * Class ReportController
 * @package Hr
 *
 * Berfungsi untuk membuat laporan piutang karyawan. Pure query jadi tidak pakai model (serupa dengan laporan-laporan lainnya)
 */
class ReportController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}


	public function kartu_piutang() {
		require_once(MODEL . "hr/employee.php");

		$employee = new \Employee();
		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end") . " 23:59:59");
			$employeeId = $this->GetGetValue("employee");
			$output = $this->GetGetValue("output");
			$report = null;
			$saldoAwal = null;
			$employee->LoadById($employeeId, true);

			if (!is_int($start) || !is_int($end) || $employeeId == "") {
				$this->persistence->SaveState("error", "Maaf data yang dikirim tidak lengkap mohon ulangi proses.");
				\Dispatcher::RedirectUrl("hr.report/kartu_piutang");
			} else if ($employee->Id != $employeeId) {
				$this->persistence->SaveState("error", "Maaf data karyawan tidak dapat ditemukan mohon ulangi proses.");
				\Dispatcher::RedirectUrl("hr.report/kartu_piutang");
			} else if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($employee->CompanyId != $this->userCompanyId) {
					// SIMULATE NOT FOUND
					$this->persistence->SaveState("error", "Maaf data karyawan tidak dapat ditemukan mohon ulangi proses.");
					\Dispatcher::RedirectUrl("hr.report/kartu_piutang");
				}
			}

			// OK semua data yang diperlukan sudah selesai
			$this->connector->AddParameter("?empId", $employee->Id);
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));

			// #01: Cari Saldo awal dari data hr_opening_balance
			$this->connector->CommandText = "SELECT a.debit_amount FROM hr_opening_balance AS a WHERE a.employee_id = ?empId AND a.date = '2013-01-01'";
			$saldoAwal = $this->connector->ExecuteScalar();
			if ($saldoAwal == null) {
				$saldoAwal = 0;
			}

			// #02: Cari semua transaksi BKK (peminjaman) dan BKM (pelunasan) sebelum periode yang diminta untuk perubahan saldo awal
			//		Teorinya jika dokumen BKK (type = 2) maka itu adalah debet (karyawan meminjam uang) demikian sebaliknya jika dokumen BKM (type = 3)
			// mate..... ternyata ada piutang yang pake AJ. Jadi detect debet atau kreditnya mau tidak mau dilihat dari akun piutang karyawan
			// Akun Piutang Karyawan = 110.04.02.00 (ID = 78)
			$this->connector->CommandText =
"SELECT SUM(IF(b.acc_debit_id = 78, b.amount, 0)) AS debet, SUM(IF(b.acc_credit_id = 78, b.amount, 0)) AS kredit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.is_deleted = 0 AND b.employee_id = ?empId AND a.voucher_date < ?start";
			$rs = $this->connector->ExecuteQuery();
			if ($rs == null) {
				$this->persistence->SaveState("error", "Gagal mengambil data transaksi sebelum periode dimulai! Error: " . $this->connector->GetErrorMessage());
				\Dispatcher::RedirectUrl("hr.report/kartu_piutang");
			} else if ($rs->GetNumRows() > 0) {
				$row = $rs->FetchAssoc();
				$saldoAwal += ($row["debet"] - $row["kredit"]);
			}

			// #03: OK cari semua transaki pada periode diminta
			$this->connector->CommandText =
"SELECT a.id, a.doc_type_id, a.doc_no, a.voucher_date, b.acc_debit_id, b.acc_credit_id, b.note, b.amount
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.is_deleted = 0 AND b.employee_id = ?empId AND a.voucher_date BETWEEN ?start AND ?end
ORDER BY a.voucher_date, a.doc_type_id ASC";
			$report = $this->connector->ExecuteQuery();
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = time();
			$employeeId = null;
			$output = "web";

			$report = null;
			$saldoAwal = null;
		}

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			// Ada kemungkinan laporan ini datang dari rekap yang bisa melihat secara corporate
			if ($employee->CompanyId != null) {
				$this->Set("employees", $employee->LoadByCompany($employee->CompanyId));
			} else {
				$this->Set("employees", $employee->LoadAll());
			}
		} else {
			$this->Set("employees", $employee->LoadByCompany($this->userCompanyId));
		}
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("employeeId", $employeeId);
		$this->Set("output", $output);
		$this->Set("report", $report);
		$this->Set("saldoAwal", $saldoAwal);

		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function rekap_piutang() {
		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$output = $this->GetGetValue("output", "web");

			if (!is_int($start) || !is_int($end)) {
				$this->persistence->SaveState("error", "Maaf data yang dikirim tidak lengkap mohon ulangi proses.");
				\Dispatcher::RedirectUrl("hr.report/rekap_piutang");
			}

			// OK semua data yang diperlukan sudah selesai
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				// Dirty HACK: Kita mau agar query-nya e_company_id = e_company_id dan bukan e_company_id = 'e_company_id'
				$this->connector->AddParameter("?sbu", "e_company_id", "int");
			} else {
				$this->connector->AddParameter("?sbu", $this->userCompanyId);
			}

			// #01: Ambil semua data karyawan yang akan diproses
			//		NOTE: ternyata jika sub-query-nya lintas database akan sangat LAMAAA... akan lebih cepat jika di buffer di temporary table
			$this->connector->CommandText =
"DROP TABLE IF EXISTS temp_emp_id;
CREATE TEMPORARY TABLE temp_emp_id
SELECT bb.employee_id
FROM ac_voucher_master AS aa
	JOIN ac_voucher_detail AS bb ON aa.id = bb.voucher_master_id
WHERE bb.employee_id IS NOT NULL AND aa.voucher_date BETWEEN ?start AND ?end;";
			$this->connector->ExecuteQuery();	// Agar bisa multi query

			// #02: Cari Perubahan Saldo Awalnya
			//		Teorinya jika dokumen BKK (type = 2) maka itu adalah debet (karyawan meminjam uang) demikian sebaliknya jika dokumen BKM (type = 3)
			// mate..... ternyata ada piutang yang pake AJ. Jadi detect debet atau kreditnya mau tidak mau dilihat dari akun piutang karyawan
			// Akun Piutang Karyawan = 110.04.02.00 (ID = 78)
			$this->connector->CommandText =
"DROP TABLE IF EXISTS temp_prev;
CREATE TEMPORARY TABLE temp_prev
SELECT b.employee_id, SUM(IF(b.acc_debit_id = 78, b.amount, 0)) AS prev_debit, SUM(IF(b.acc_credit_id = 78, b.amount, 0)) AS prev_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.voucher_date < ?start AND b.employee_id IN (
	SELECT employee_id FROM temp_emp_id
)
GROUP BY b.employee_id;";
			$this->connector->ExecuteQuery();	// Agar bisa multi query

			// #03: Cari Sum Transaksi pada periode yang diminta
			//		ToDo: Digabung ke query #02 dengan mengabaikan filter a.voucher_date akankan lebih cepat ?
			$this->connector->CommandText =
"DROP TABLE IF EXISTS temp_current;
CREATE TEMPORARY TABLE temp_current
SELECT b.employee_id, SUM(IF(b.acc_debit_id = 78, b.amount, 0)) AS current_debit, SUM(IF(b.acc_credit_id = 78, b.amount, 0)) AS current_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE a.voucher_date BETWEEN ?start AND ?end AND b.employee_id IN (
	SELECT employee_id FROM temp_emp_id
)
GROUP BY b.employee_id;";
			$this->connector->ExecuteQuery();	// Agar bisa multi query

			// #04: Bikin Reportnya
			$this->connector->CommandText =
"SELECT a.e_id, a.e_name, a.e_nik, a.e_company_id, a.e_dept_id, e.entity_cd, b.debit_amount AS opening_balance, c.prev_debit, c.prev_credit, d.current_debit, d.current_credit
FROM db_hris.ms_employee AS a
	LEFT JOIN hr_opening_balance AS b ON a.e_id = b.employee_id AND b.date = '2013-01-01'
	LEFT JOIN temp_prev AS c ON a.e_id = c.employee_id
	LEFT JOIN temp_current AS d ON a.e_id = d.employee_id
	JOIN db_hris.sys_company AS e ON a.e_company_id = e.company_id
WHERE a.e_company_id = ?sbu AND a.e_id IN (
	SELECT employee_id FROM temp_emp_id
)
ORDER BY a.e_name;";

			$report = $this->connector->ExecuteQuery();
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = time();
			$output = "web";
			$report = null;
		}

		require_once(MODEL . "master/company.php");
		$company = new \Company();

		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("output", $output);
		$this->Set("company", $company->LoadById($this->userCompanyId));
		$this->Set("report", $report);

		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}
}

// EoF: report_controller.php