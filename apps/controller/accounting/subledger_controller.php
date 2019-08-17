<?php

/**
 * Untuk membuat beberapa laporan accounting yang diminta.
 * Ini berisi laporan bantuan dari buku besar. Detail untuk buku besar. Beberapa caranya mirip dengan cashflow
 *
 * @see CashFlowController
 */
class SubLedgerController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function recap() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/units.php");

        $sql = null;
		if (count($this->getData) > 0) {
			$noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

			$parentId = $this->GetGetValue("parentId");
            $projectId = $this->GetGetValue("projectId");
            $deptId = $this->GetGetValue("deptId");
            $actId = $this->GetGetValue("actId");
            $unitId = $this->GetGetValue("unitId");
			$month = $this->GetGetValue("month");
			$year = $this->GetGetValue("year");
			$status = $this->GetGetValue("status", 4);
			$noOfDay = $noOfDays[$month];
			if ($month == 2 && $year % 4 == 0) {
				$noOfDay = 29;	// Leap Year
			}
			$output = $this->GetGetValue("output", "web");
			$firstJanuary = mktime(0,0, 0, 1, 1, $year);
			$startDate = mktime(0, 0, 0, $month, 1, $year);
			$endDate = mktime(0,0, 0, $month, $noOfDay, $year);

			// Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
			// Setting global parameter (Jgn panggil ClearParameters() OK !)
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			$this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
            $this->connector->AddParameter("?project", $projectId);
            $this->connector->AddParameter("?dept", $deptId);
            $this->connector->AddParameter("?act", $actId);
            $this->connector->AddParameter("?unit", $unitId);
			if ($month > 1) {
				$this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
			}
			if ($status == -1) {
				$this->connector->AddParameter("?status", "a.status", "int");	// Gw mau paksa agar querynya menjadi a.status = a.status (selalu true) bukan a.status = 'a.status'
			} else {
				if ($status > 0 && $status < 5) {
					$this->connector->AddParameter("?status", $status);
				} else {
					$this->connector->AddParameter("?status", 4);
				}
			}
			if ($status == 4) {
				$this->connector->AddParameter("?obStatus", 4);
			} else {
				$this->connector->AddParameter("?obStatus", "a.status", "int");	// Gw mau paksa agar querynya menjadi a.status = a.status (selalu true) bukan a.status = 'a.status'
				$this->Set("info", "Saldo awal akan menggunakan semua voucher karena anda tidak memilih status POSTED");
			}

			// OK dafuq ini... mari kita query multi step
			// #01: Filter account yang akan digunakan pada report
			$this->connector->CommandText =
            "CREATE TEMPORARY TABLE acc_id AS
            SELECT a.id, a.acc_no, a.acc_name, a.dc_saldo
            FROM cm_acc_detail AS a
            WHERE a.is_deleted = 0 AND a.acc_parent_id = ?parentId";
			$this->connector->AddParameter("?parentId", $parentId);
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua debit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_debit AS
            SELECT b.acc_debit_id, SUM(b.amount) AS total_debit
            FROM ac_voucher_master AS a
                JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
            WHERE a.status = ?status AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
                AND b.acc_debit_id IN (SELECT id FROM acc_id) ";
            if ($projectId > 0){
                $sql.= " And b.project_id = ?project";
            }
            if ($deptId > 0){
                $sql.= " And b.dept_id = ?dept";
            }
            if ($actId > 0){
                $sql.= " And b.activity_id = ?act";
            }
            if ($unitId > 0){
                $sql.= " And b.unit_id = ?unit";
            }
            $sql.= " GROUP BY b.acc_debit_id;";
			$this->connector->CommandText = $sql;
			$this->connector->ExecuteNonQuery();

			// #03: Ambil sum semua credit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_credit AS
            SELECT b.acc_credit_id, SUM(b.amount) AS total_credit
            FROM ac_voucher_master AS a
                JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
            WHERE a.status = ?status AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
                AND b.acc_credit_id IN (SELECT id FROM acc_id)";
            if ($projectId > 0){
                $sql.= " And b.project_id = ?project";
            }
            if ($deptId > 0){
                $sql.= " And b.dept_id = ?dept";
            }
            if ($actId > 0){
                $sql.= " And b.activity_id = ?act";
            }
            if ($unitId > 0){
                $sql.= " And b.unit_id = ?unit";
            }
            $sql.= " GROUP BY b.acc_credit_id;";
			$this->connector->CommandText = $sql;
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #04: Ambil data bulan-bulan sebelumnya (debet)
                $sql = "CREATE TEMPORARY TABLE sum_debit_prev AS
                SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                WHERE a.status = ?obStatus AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev
                    AND b.acc_debit_id IN (SELECT id FROM acc_id)";
                if ($projectId > 0){
                    $sql.= " And b.project_id = ?project";
                }
                if ($deptId > 0){
                    $sql.= " And b.dept_id = ?dept";
                }
                if ($actId > 0){
                    $sql.= " And b.activity_id = ?act";
                }
                if ($unitId > 0){
                    $sql.= " And b.unit_id = ?unit";
                }
                $sql.= " GROUP BY b.acc_debit_id;";
				$this->connector->CommandText = $sql;
				$this->connector->ExecuteNonQuery();

				// #05: Ambil data bulan-bulan sebelumnya (kredit)
                $sql = "CREATE TEMPORARY TABLE sum_credit_prev AS
                SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                WHERE a.status = ?obStatus AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev
                    AND b.acc_credit_id IN (SELECT id FROM acc_id)";
                if ($projectId > 0){
                    $sql.= " And b.project_id = ?project";
                }
                if ($deptId > 0){
                    $sql.= " And b.dept_id = ?dept";
                }
                if ($actId > 0){
                    $sql.= " And b.activity_id = ?act";
                }
                if ($unitId > 0){
                    $sql.= " And b.unit_id = ?unit";
                }
                $sql.= " GROUP BY b.acc_credit_id;";
				$this->connector->CommandText = $sql;
				$this->connector->ExecuteNonQuery();

				// #06: OK final query...
				$this->connector->CommandText =
                "SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev, f.bal_debit_amt, f.bal_credit_amt
                FROM acc_id AS a
                    LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
                    LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
                    LEFT JOIN sum_debit_prev AS d ON a.id = d.acc_debit_id
                    LEFT JOIN sum_credit_prev AS e ON a.id = e.acc_credit_id
                    LEFT JOIN ac_opening_balance AS f ON a.id = acc_id AND bal_date = ?firstJan
                ORDER BY a.acc_no";
			} else {
				// Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
				// Untuk data bulan-bulan sebelumnya selalu 0
				$this->connector->CommandText =
                "SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev, f.bal_debit_amt, f.bal_credit_amt
                FROM acc_id AS a
                    LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
                    LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
                    LEFT JOIN ac_opening_balance AS f ON a.id = acc_id AND bal_date = ?firstJan
                ORDER BY a.acc_no";
			}

			$report = $this->connector->ExecuteQuery();
		} else {
			$parentId = null;
            $projectId = 0;
            $deptId = 0;
            $actId = 0;
            $unitId = 0;
			$month = (int)date("n");
			$year = (int)date("Y");
			$status = 4;
			$report = null;
			$output = "web";
		}

		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$account = new Coa();
        $project = new Project();
        $depts = new Department();
        $acts = new Activity();
        $units = new Units();
        $this->Set("projectList", $project->LoadByEntityId($this->userCompanyId));
        $this->Set("projectId", $projectId);
        $this->Set("deptList", $depts->LoadByEntityId($this->userCompanyId));
        $this->Set("deptId", $deptId);
        $this->Set("actList", $acts->LoadByEntityId($this->userCompanyId));
        $this->Set("actId", $actId);
        $this->Set("unitList", $units->LoadAll($this->userCompanyId));
        $this->Set("unitId", $unitId);
		$this->Set("parentAccounts", $account->LoadByLevel($this->userCompanyId,2));
		$this->Set("parentId", $parentId);
        $this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("status", $status);
		switch ($status) {
			case -1:
				$this->Set("statusName", "SEMUA DOKUMEN");
				break;
			case 1:
				$this->Set("statusName", "BELUM APPROVED");
				break;
			case 2:
				$this->Set("statusName", "SUDAH APPROVED");
				break;
			case 3:
				$this->Set("statusName", "VERIFIED");
				break;
			case 4:
				$this->Set("statusName", "POSTED");
				break;
			default:
				$this->Set("statusName", "N.A.");
				break;
		}
		$this->Set("report", $report);
		$this->Set("output", $output);

		$this->Set("company", $company);
		$this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
	}

	public function detail() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "accounting/opening_balance.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/units.php");

		if (count($this->getData) > 0) {
			$accountId = $this->GetGetValue("account");
            $projectId = $this->GetGetValue("projectId");
            $deptId = $this->GetGetValue("deptId");
            $actId = $this->GetGetValue("actId");
            $unitId = $this->GetGetValue("unitId");
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$status = $this->GetGetValue("status", 4);
			$output = $this->GetGetValue("output", "web");

			if ($accountId == "") {
				// Ga pilih akun
				$openingBalance = null;
				$transaction = null;
				$report = null;
				$this->Set("error", "Mohon pilih akun sub ledger terlebih dahulu.");
			} else {
				// OK Data utama ada mari kita proses....

				$openingBalance = new OpeningBalance();
				$openingBalance->LoadByAccount($accountId, date("Y", $start));
				if ($openingBalance->Id == null && $openingBalance->GetCoa()->IsOpeningBalanceRequired()) {
					$this->Set("info", "Akun yang dipilih diharuskan memiliki Opening Balance tetapi data Tidak ditemukan !");
				}
				$temp = $start - 86400;
				if ($status == 4) {
					$transaction = $openingBalance->CalculateTransaction($temp, 4,$projectId);
				} else {
					// Status dokumen bukan POSTED jadi kita FORCE menghitung semua transaksi
					$transaction = $openingBalance->CalculateTransaction($temp, -1,$projectId);
					$this->Set("info", "Saldo awal akan menggunakan semua voucher karena anda tidak memilih status POSTED");
				}

				$query =
                "SELECT a.id, a.voucher_date, a.doc_no, b.note, b.acc_debit_id, b.acc_credit_id, b.amount, e.entity_cd, c.act_code, c.act_name, d.dept_code, d.dept_name
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                    LEFT JOIN cm_activity AS c ON b.activity_id = c.id
                    LEFT JOIN cm_dept AS d ON c.dept_id = d.id
                    JOIN cm_company AS e ON a.entity_id = e.entity_id
                WHERE a.status = ?status AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
                    AND (b.acc_debit_id = ?accId OR b.acc_credit_id = ?accId)";
                if ($projectId > 0){
                    $query.= " And b.project_id = ?project";
                }
                if ($deptId > 0){
                    $query.= " And b.dept_id = ?dept";
                }
                if ($actId > 0){
                    $query.= " And b.activity_id = ?act";
                }
                if ($unitId > 0){
                    $query.= " And b.unit_id = ?unit";
                }
                $query.= " ORDER BY a.voucher_date, a.doc_no";

				$this->connector->CommandText = $query;
				$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
				$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
				$this->connector->AddParameter("?accId", $accountId);
                $this->connector->AddParameter("?project", $projectId);
                $this->connector->AddParameter("?dept", $deptId);
                $this->connector->AddParameter("?act", $actId);
                $this->connector->AddParameter("?unit", $unitId);
				if ($status == -1) {
					$this->connector->AddParameter("?status", "a.status", "int");	// Gw mau paksa agar querynya menjadi a.status = a.status (selalu true) bukan a.status = 'a.status'
				} else {
					if ($status > 0 && $status < 5) {
						$this->connector->AddParameter("?status", $status);
					} else {
						$this->connector->AddParameter("?status", 4);
					}
				}

				$report = $this->connector->ExecuteQuery();
			}
		} else {
			$accountId = null;
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$status = 4;
			$openingBalance = null;
			$transaction = null;
			$report = null;
            $projectId = 0;
            $deptId = 0;
            $actId = 0;
            $unitId = 0;
			$output = "web";
		}

		// Cari data login companynya
		$company = new Company();
		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$company = $company->LoadById(7);
		} else {
			$company = $company->LoadById($this->userCompanyId);
		}
		// OK cari data CoA
		$account = new Coa();
		$parentIds = array();
		foreach($account->LoadByLevel($this->userCompanyId,2) as $account) {
			$parentIds[] = $account->Id;
		}
		$accounts = array();
		foreach ($parentIds as $id) {
			$account = new Coa();
			$account->FindById($id);
			$accounts[] = array("Parent" => $account, "SubAccounts" => $account->LoadByAccParentId($this->userCompanyId,$account->Id));
		}
        $project = new Project();
        $depts = new Department();
        $acts = new Activity();
        $units = new Units();
        $this->Set("projectList", $project->LoadByEntityId($this->userCompanyId));
        $this->Set("projectId", $projectId);
        $this->Set("deptList", $depts->LoadByEntityId($this->userCompanyId));
        $this->Set("deptId", $deptId);
        $this->Set("actList", $acts->LoadByEntityId($this->userCompanyId));
        $this->Set("actId", $actId);
        $this->Set("unitList", $units->LoadAll($this->userCompanyId));
        $this->Set("unitId", $unitId);
		$this->Set("accountId", $accountId);
		$this->Set("accounts", $accounts);
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("status", $status);
		switch ($status) {
			case -1:
				$this->Set("statusName", "SEMUA DOKUMEN");
				break;
			case 1:
				$this->Set("statusName", "BELUM APPROVED");
				break;
			case 2:
				$this->Set("statusName", "SUDAH APPROVED");
				break;
			case 3:
				$this->Set("statusName", "VERIFIED");
				break;
			case 4:
				$this->Set("statusName", "POSTED");
				break;
			default:
				$this->Set("statusName", "N.A.");
				break;
		}
		$this->Set("openingBalance", $openingBalance);
		$this->Set("transaction", $transaction);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("company", $company);
	}

    public function costrevenue() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coa.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/units.php");

        $sql = null;
        if (count($this->getData) > 0) {
            $noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

            $parentId = $this->GetGetValue("parentId");
            $projectId = $this->GetGetValue("projectId");
            $deptId = $this->GetGetValue("deptId");
            $actId = $this->GetGetValue("actId");
            $unitId = $this->GetGetValue("unitId");
            $month = $this->GetGetValue("month");
            $year = $this->GetGetValue("year");
            $rtype = $this->GetGetValue("recaptype");
            $status = $this->GetGetValue("status", 4);
            $noOfDay = $noOfDays[$month];
            if ($month == 2 && $year % 4 == 0) {
                $noOfDay = 29;	// Leap Year
            }
            $output = $this->GetGetValue("output", "web");
            $firstJanuary = mktime(0,0, 0, 1, 1, $year);
            $startDate = mktime(0, 0, 0, $month, 1, $year);
            $endDate = mktime(0,0, 0, $month, $noOfDay, $year);

            // Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
            // Setting global parameter (Jgn panggil ClearParameters() OK !)
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
            $this->connector->AddParameter("?project", $projectId);
            $this->connector->AddParameter("?dept", $deptId);
            $this->connector->AddParameter("?act", $actId);
            $this->connector->AddParameter("?unit", $unitId);
            if ($month > 1) {
                $this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
            }
            if ($status == -1) {
                $this->connector->AddParameter("?status", "a.status", "int");	// Gw mau paksa agar querynya menjadi a.status = a.status (selalu true) bukan a.status = 'a.status'
            } else {
                if ($status > 0 && $status < 5) {
                    $this->connector->AddParameter("?status", $status);
                } else {
                    $this->connector->AddParameter("?status", 4);
                }
            }
            if ($status == 4) {
                $this->connector->AddParameter("?obStatus", 4);
            } else {
                $this->connector->AddParameter("?obStatus", "a.status", "int");	// Gw mau paksa agar querynya menjadi a.status = a.status (selalu true) bukan a.status = 'a.status'
                $this->Set("info", "Saldo awal akan menggunakan semua voucher karena anda tidak memilih status POSTED");
            }

            // OK dafuq ini... mari kita query multi step
            // #01: Filter account yang akan digunakan pada report
            if ($parentId == "5") {
                $this->connector->CommandText =
                    "CREATE TEMPORARY TABLE acc_id AS
                SELECT a.id, a.acc_no, a.acc_name, a.dc_saldo
                FROM cm_acc_detail AS a
                WHERE a.is_deleted = 0 AND (left(a.acc_no,1) = '5' Or left(a.acc_no,1) = '6')";
                $this->connector->AddParameter("?parentId", $parentId);
                $this->connector->ExecuteNonQuery();
            }else{
                $this->connector->CommandText =
                    "CREATE TEMPORARY TABLE acc_id AS
                SELECT a.id, a.acc_no, a.acc_name, a.dc_saldo
                FROM cm_acc_detail AS a
                WHERE a.is_deleted = 0 AND left(a.acc_no,1) = ?parentId";
                $this->connector->AddParameter("?parentId", $parentId);
                $this->connector->ExecuteNonQuery();
            }
            // #02: Ambil sum semua debit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_debit AS
            SELECT b.acc_debit_id, SUM(b.amount) AS total_debit
            FROM ac_voucher_master AS a
                JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
            WHERE a.status = ?status AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
                AND b.acc_debit_id IN (SELECT id FROM acc_id) ";
            if ($projectId > 0){
                $sql.= " And b.project_id = ?project";
            }
            if ($deptId > 0){
                $sql.= " And b.dept_id = ?dept";
            }
            if ($actId > 0){
                $sql.= " And b.activity_id = ?act";
            }
            if ($unitId > 0){
                $sql.= " And b.unit_id = ?unit";
            }
            $sql.= " GROUP BY b.acc_debit_id;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            // #03: Ambil sum semua credit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_credit AS
            SELECT b.acc_credit_id, SUM(b.amount) AS total_credit
            FROM ac_voucher_master AS a
                JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
            WHERE a.status = ?status AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end
                AND b.acc_credit_id IN (SELECT id FROM acc_id)";
            if ($projectId > 0){
                $sql.= " And b.project_id = ?project";
            }
            if ($deptId > 0){
                $sql.= " And b.dept_id = ?dept";
            }
            if ($actId > 0){
                $sql.= " And b.activity_id = ?act";
            }
            if ($unitId > 0){
                $sql.= " And b.unit_id = ?unit";
            }
            $sql.= " GROUP BY b.acc_credit_id;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            if ($month > 1) {
                // kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
                // #04: Ambil data bulan-bulan sebelumnya (debet)
                $sql = "CREATE TEMPORARY TABLE sum_debit_prev AS
                SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                WHERE a.status = ?obStatus AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev
                    AND b.acc_debit_id IN (SELECT id FROM acc_id)";
                if ($projectId > 0){
                    $sql.= " And b.project_id = ?project";
                }
                if ($deptId > 0){
                    $sql.= " And b.dept_id = ?dept";
                }
                if ($actId > 0){
                    $sql.= " And b.activity_id = ?act";
                }
                if ($unitId > 0){
                    $sql.= " And b.unit_id = ?unit";
                }
                $sql.= " GROUP BY b.acc_debit_id;";
                $this->connector->CommandText = $sql;
                $this->connector->ExecuteNonQuery();

                // #05: Ambil data bulan-bulan sebelumnya (kredit)
                $sql = "CREATE TEMPORARY TABLE sum_credit_prev AS
                SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                WHERE a.status = ?obStatus AND a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev
                    AND b.acc_credit_id IN (SELECT id FROM acc_id)";
                if ($projectId > 0){
                    $sql.= " And b.project_id = ?project";
                }
                if ($deptId > 0){
                    $sql.= " And b.dept_id = ?dept";
                }
                if ($actId > 0){
                    $sql.= " And b.activity_id = ?act";
                }
                if ($unitId > 0){
                    $sql.= " And b.unit_id = ?unit";
                }
                $sql.= " GROUP BY b.acc_credit_id;";
                $this->connector->CommandText = $sql;
                $this->connector->ExecuteNonQuery();

                // #06: OK final query...
                $this->connector->CommandText =
                    "SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev, f.bal_debit_amt, f.bal_credit_amt
                    FROM acc_id AS a
                        LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
                        LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
                        LEFT JOIN sum_debit_prev AS d ON a.id = d.acc_debit_id
                        LEFT JOIN sum_credit_prev AS e ON a.id = e.acc_credit_id
                        LEFT JOIN ac_opening_balance AS f ON a.id = acc_id AND bal_date = ?firstJan
                    ORDER BY a.acc_no";
            } else {
                // Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
                // Untuk data bulan-bulan sebelumnya selalu 0
                $this->connector->CommandText =
                    "SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev, f.bal_debit_amt, f.bal_credit_amt
                    FROM acc_id AS a
                        LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
                        LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
                        LEFT JOIN ac_opening_balance AS f ON a.id = acc_id AND bal_date = ?firstJan
                    ORDER BY a.acc_no";
            }

            $report = $this->connector->ExecuteQuery();
        } else {
            $parentId = null;
            $projectId = 0;
            $deptId = 0;
            $actId = 0;
            $unitId = 0;
            $month = (int)date("n");
            $year = (int)date("Y");
            $status = 4;
            $report = null;
            $output = "web";
            $rtype = 1;
        }

        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);

        $account = new Coa();
        $project = new Project();
        $depts = new Department();
        $acts = new Activity();
        $units = new Units();
        $this->Set("projectList", $project->LoadByEntityId($this->userCompanyId));
        $this->Set("projectId", $projectId);
        $this->Set("deptList", $depts->LoadByEntityId($this->userCompanyId));
        $this->Set("deptId", $deptId);
        $this->Set("actList", $acts->LoadByEntityId($this->userCompanyId));
        $this->Set("actId", $actId);
        $this->Set("unitList", $units->LoadAll($this->userCompanyId));
        $this->Set("unitId", $unitId);
        $this->Set("parentAccounts", $account->LoadByLevel($this->userCompanyId,2));
        $this->Set("parentId", $parentId);
        $this->Set("month", $month);
        $this->Set("year", $year);
        $this->Set("recaptype", $rtype);
        $this->Set("status", $status);
        switch ($status) {
            case -1:
                $this->Set("statusName", "SEMUA DOKUMEN");
                break;
            case 1:
                $this->Set("statusName", "BELUM APPROVED");
                break;
            case 2:
                $this->Set("statusName", "SUDAH APPROVED");
                break;
            case 3:
                $this->Set("statusName", "VERIFIED");
                break;
            case 4:
                $this->Set("statusName", "POSTED");
                break;
            default:
                $this->Set("statusName", "N.A.");
                break;
        }
        $this->Set("report", $report);
        $this->Set("output", $output);

        $this->Set("company", $company);
        $this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
    }
}

// End of file: bukutambahan_controller.php
