<?php

namespace Accounting;

/**
 * Untuk membuat laporan-laporan accounting
 *
 * Class ReportController
 * @package Accounting
 */
class ReportController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	/**
	 * Akan membuat laporan jurnal voucher berdasarkan jenis-jenis dokumen yang dipilih.
	 */
	public function journal() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "accounting/voucher_type.php");
        require_once(MODEL . "master/project.php");

		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
            $projectId = $this->GetGetValue("projectId");
			$docIds = $this->GetGetValue("docType", array());
			$status = $this->GetGetValue("status");
			$output = $this->GetGetValue("output", "web");
			$showNo = $this->GetGetValue("showNo", "0") == "1";
			$showAdditionalColumn = $this->GetGetValue("showCol", "0") == "1";
			$orientation = strtoupper($this->GetGetValue("orientation", "p"));
			if (!in_array($orientation, array("P", "L"))) {
				$orientation = "P";
			}
			if (count($docIds) == 0) {
				$this->persistence->SaveState("error", "Mohon pilih jenis dokumen terlebih dahulu. Sekurang-kurangnya pilih 1.");
				redirect_url("accounting.report/journal");
				return;
			}


			// OK bikin querynya... (Untuk Bank masuk dilihat dari jenis dokumen BM dan OR)
			if ($showAdditionalColumn) {
				$query =
                "SELECT
                    a.id, a.voucher_date, a.doc_no, b.note, e.entity_cd, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit,
                    dept.dept_code, activity.act_code, project.project_name, debtor.debtor_name, creditor.creditor_name, employee.nama
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                    JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
                    JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
                    JOIN cm_company AS e ON a.entity_id = e.entity_id
                    LEFT JOIN cm_dept AS dept ON b.dept_id = dept.id
                    LEFT JOIN cm_activity AS activity ON b.activity_id = activity.id
                    LEFT JOIN cm_project AS project ON b.project_id = project.id
                    LEFT JOIN ar_debtor_master AS debtor ON b.debtor_id = debtor.id
                    LEFT JOIN ap_creditor_master AS creditor ON b.creditor_id = creditor.id
                    LEFT JOIN hr_employee_master AS employee ON b.employee_id = employee.id
                WHERE a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN ?docTypes %s";
                if ($projectId > 0){
                    $query.= " And b.project_id = ?projectId";
                }
                $query.= " ORDER BY a.voucher_date, a.doc_no";
			} else {
				$query =
                "SELECT a.id, a.voucher_date, a.doc_no, b.note, e.entity_cd, c.acc_name AS acc_debit, c.acc_no AS acc_no_debit, b.amount, d.acc_name AS acc_credit, d.acc_no AS acc_no_credit
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                    JOIN cm_acc_detail AS c ON b.acc_debit_id = c.id
                    JOIN cm_acc_detail AS d ON b.acc_credit_id = d.id
                    JOIN cm_company AS e ON a.entity_id = e.entity_id
                WHERE a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN ?docTypes %s";
                if ($projectId > 0){
                    $query.= " And b.project_id = ?projectId";
                }
                $query.= " ORDER BY a.voucher_date, a.doc_no";

			}

			$extendedWhere = "";
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				$extendedWhere .= "  AND a.entity_id = ?sbu";
				$this->connector->AddParameter("?sbu", $this->userCompanyId);
			}
			if ($status != null) {
				$extendedWhere .= " AND a.status = ?status";
				$this->connector->AddParameter("?status", $status);
			}

			$this->connector->CommandText = sprintf($query, $extendedWhere);
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
			$this->connector->AddParameter("?projectId", $projectId);
            $this->connector->AddParameter("?docTypes", $docIds);
			$report = $this->connector->ExecuteQuery();
		} else {
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
            $projectId = null;
			$docIds = array();
			$status = null;
			$showNo = true;
			$showAdditionalColumn = false;
			$report = null;
			$output = "web";
			$orientation = "P";

			if ($this->persistence->StateExists("error")) {
				$this->Set("error", $this->persistence->LoadState("error"));
				$this->persistence->DestroyState("error");
			}
		}

		$company = new \Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}

		$docType = new \DocType();
		$vocType = new \VoucherType();

        $project = new \Project();
        $this->Set("projectList", $project->LoadByEntityId($this->userCompanyId));
        $this->Set("projectId", $projectId);
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("docTypes", $docType->LoadHaveVoucher());
		$this->Set("vocTypes", $vocType->LoadAll());
		$this->Set("docIds", $docIds);
		$this->Set("status", $status);
		$this->Set("showNo", $showNo);
		$this->Set("showCol", $showAdditionalColumn);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("orientation", $orientation);
		$this->Set("company", $company);
	}

	public function recap() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "accounting/voucher_type.php");
        require_once(MODEL . "master/project.php");

		if (count($this->getData) > 0) {
			$month = $this->GetGetValue("month");
			$year = $this->GetGetValue("year");
			$docIds = $this->GetGetValue("docType", array());
            $projectId = $this->GetGetValue("projectId");
			$status = $this->GetGetValue("status");
			$output = $this->GetGetValue("output", "web");
			$orientation = strtoupper($this->GetGetValue("orientation", "p"));
			if (!in_array($orientation, array("P", "L"))) {
				$orientation = "P";
			}
			if (count($docIds) == 0) {
				$this->persistence->SaveState("error", "Mohon pilih jenis dokumen terlebih dahulu. Sekurang-kurangnya pilih 1.");
				redirect_url("accounting.report/recap");
				return;
			}

			$firstJanuary = mktime(0,0, 0, 1, 1, $year);
			$startDate = mktime(0, 0, 0, $month, 1, $year);
			$endDate = mktime(0,0, 0, $month + 1, 0, $year);	// Bulan berikutnya kurangin 1 (pake tehnik tanggal 0 == hari sebelumnya)

			// Setting global parameter (Jgn panggil ClearParameters() OK !)
			$this->connector->AddParameter("?status", $status);
			$this->connector->AddParameter("?sbu", $this->userCompanyId);
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			$this->connector->AddParameter("?docIds", $docIds);
            $this->connector->AddParameter("?projectId", $projectId);
			if ($month > 1) {
				// Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
				$this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
				$this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
			}

			// OK dafuq ini... mari kita query multi step
			// Disini kita akan mengambil data dari semua dokumen yang diminta user ($docIds)

			// #01: Ambil sum semua debit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_debit AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE [status] [entity] [project_id] a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN ?docIds
GROUP BY b.acc_debit_id;";
            if ($projectId > 0){
               $query = str_replace("[project_id]", "b.project_id = ?projectId AND", $query);
            }else{
                $query = str_replace("[project_id]", "", $query);
            }
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$query = str_replace("[entity]", "", $query);
			} else {
				$query = str_replace("[entity]", "a.entity_id = ?sbu AND", $query);
			}
			if ($status == null) {
				$query = str_replace("[status]", "", $query);
			} else {
				$query = str_replace("[status]", "a.status = ?status AND", $query);
			}
			$this->connector->CommandText = $query;
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua credit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_credit AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE [status] [entity] [project_id] a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND a.doc_type_id IN ?docIds
GROUP BY b.acc_credit_id;";
            if ($projectId > 0){
                $query = str_replace("[project_id]", "b.project_id = ?projectId AND", $query);
            }else{
                $query = str_replace("[project_id]", "", $query);
            }
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$query = str_replace("[entity]", "", $query);
			} else {
				$query = str_replace("[entity]", "a.entity_id = ?sbu AND", $query);
			}
			if ($status == null) {
				$query = str_replace("[status]", "", $query);
			} else {
				$query = str_replace("[status]", "a.status = ?status AND", $query);
			}
			$this->connector->CommandText = $query;
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #03: Ambil data bulan-bulan sebelumnya (debet)
				$query =
"CREATE TEMPORARY TABLE sum_debit_prev AS
SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE [status] [entity] [project_id] a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND a.doc_type_id IN ?docIds
GROUP BY b.acc_debit_id;";
                if ($projectId > 0){
                    $query = str_replace("[project_id]", "b.project_id = ?projectId AND", $query);
                }else{
                    $query = str_replace("[project_id]", "", $query);
                }
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$query = str_replace("[entity]", "", $query);
				} else {
					$query = str_replace("[entity]", "a.entity_id = ?sbu AND", $query);
				}
				if ($status == null) {
					$query = str_replace("[status]", "", $query);
				} else {
					$query = str_replace("[status]", "a.status = ?status AND", $query);
				}
				$this->connector->CommandText = $query;
				$this->connector->ExecuteNonQuery();

				// #04: Ambil data bulan-bulan sebelumnya (kredit)
				$query =
"CREATE TEMPORARY TABLE sum_credit_prev AS
SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
FROM ac_voucher_master AS a
	JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
WHERE [status] [entity] [project_id] a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND a.doc_type_id IN ?docIds
GROUP BY b.acc_credit_id;";
                if ($projectId > 0){
                    $query = str_replace("[project_id]", "b.project_id = ?projectId AND", $query);
                }else{
                    $query = str_replace("[project_id]", "", $query);
                }
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$query = str_replace("[entity]", "", $query);
				} else {
					$query = str_replace("[entity]", "a.entity_id = ?sbu AND", $query);
				}
				if ($status == null) {
					$query = str_replace("[status]", "", $query);
				} else {
					$query = str_replace("[status]", "a.status = ?status AND", $query);
				}
				$this->connector->CommandText = $query;
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
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) <> 0
ORDER BY a.acc_no";
			}

			$report = $this->connector->ExecuteQuery();
		} else {
			$month = (int)date("n");
			$year = (int)date("Y");
			$docIds = array();
            $projectId = null;
			$status = null;
			$report = null;
			$output = "web";
			$orientation = "P";

			if ($this->persistence->StateExists("error")) {
				$this->Set("error", $this->persistence->LoadState("error"));
				$this->persistence->DestroyState("error");
			}
		}

		$company = new \Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}

		$docType = new \DocType();
		$vocType = new \VoucherType();
        $project = new \Project();
        $this->Set("projectList", $project->LoadByEntityId($this->userCompanyId));
        $this->Set("projectId", $projectId);
		$this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("docTypes", $docType->LoadHaveVoucher());
		$this->Set("vocTypes", $vocType->LoadAll());
		$this->Set("docIds", $docIds);
		$this->Set("status", $status);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("orientation", $orientation);
		$this->Set("company", $company);

		$this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
	}
}

// EoF: report_controller.php