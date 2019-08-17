<?php

/**
 * Untuk membuat beberapa laporan accounting yang diminta.
 * Ini berisi laporan bantuan dari buku besar. Detail untuk buku besar. Beberapa caranya mirip dengan cashflow
 *
 * @see CashFlowController
 */
class WorksheetBalanceController extends AppController {
    private $userCompanyId;

    protected function Initialize() {
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
    }

    public function recap() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(LIBRARY . "dot_net_tools.php");
        $sql = null;
        if (count($this->getData) > 0) {
            set_time_limit(300);
            $noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

            $rekapMethod = $this->GetGetValue("rekapMethod");
            $isIncOb = $this->GetGetValue("isIncOb");
            $month = $this->GetGetValue("month");
            $year = $this->GetGetValue("year");
            $projectId = $this->GetPostValue("projectId");
            $noOfDay = $noOfDays[$month];
            if ($month == 2 && $year % 4 == 0) {
                $noOfDay = 29;	// Leap Year
            }
            $output = $this->GetGetValue("output", "web");
            $firstJanuary = mktime(0,0, 0, 1, 1, $year);
            if ($rekapMethod == 0){
                $startDate = $firstJanuary;
            }else{
                $startDate = mktime(0, 0, 0, $month, 1, $year);
            }
            $endDate = mktime(0,0, 0, $month, $noOfDay, $year);

            // Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
            // Setting global parameter (Jgn panggil ClearParameters() OK !)
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
            if ($month > 1) {
                $this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
            }

            // OK dafuq ini... mari kita query multi step
            // #01: Filter account yang akan digunakan pada report (Hanya yang parent ID nya 3, 8 alias kas dan pendapatan)
            $sql = "CREATE TEMPORARY TABLE acc_id AS SELECT a.id, a.acc_no, a.acc_name, a.dc_saldo FROM cm_acc_detail AS a WHERE a.is_deleted = 0 AND a.acc_level = 3;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            // #02: Ambil sum semua debit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_debit AS
                SELECT b.acc_debit_id, SUM(b.amount) AS 'total_debit', SUM(IF(d.wbs_post = 'MTC', b.amount, 0)) AS 'mtc_debit', SUM(IF(d.wbs_post = 'ACR', b.amount, 0)) AS 'acr_debit', SUM(IF(d.wbs_post = 'ADJ', b.amount, 0)) AS 'adj_debit'
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                    JOIN cm_doctype as c On a.doc_type_id = c.id
                    JOIN ac_voucher_type as d on c.accvoucher_id = d.id WHERE a.status = 4";
            if ($projectId > 0){
                $sql.= " And b.project_id = ".$projectId;
            }
            $sql.= " And a.rstatus = 1 And a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND b.acc_debit_id IN (SELECT id FROM acc_id) GROUP BY b.acc_debit_id;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            // #03: Ambil sum semua credit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_credit AS
                SELECT b.acc_credit_id, SUM(b.amount) AS 'total_credit', SUM(IF(d.wbs_post = 'MTC', b.amount, 0)) AS 'mtc_credit', SUM(IF(d.wbs_post = 'ACR', b.amount, 0)) AS 'acr_credit', SUM(IF(d.wbs_post = 'ADJ', b.amount, 0)) AS 'adj_credit'
                FROM ac_voucher_master AS a
                    JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                    JOIN cm_doctype as c On a.doc_type_id = c.id
                    JOIN ac_voucher_type as d on c.accvoucher_id = d.id
                WHERE a.status = 4";
            if ($projectId > 0){
                $sql.= " And b.project_id = ".$projectId;
            }
            $sql.= " AND a.rstatus = 1 And a.is_deleted = 0 AND a.voucher_date BETWEEN ?start AND ?end AND b.acc_credit_id IN (SELECT id FROM acc_id) GROUP BY b.acc_credit_id;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            if ($rekapMethod == 1 && $month > 1) {
                // kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
                // #04: Ambil data bulan-bulan sebelumnya (debet)
                $sql = "CREATE TEMPORARY TABLE sum_debit_prev AS
                    SELECT b.acc_debit_id, SUM(b.amount) AS total_debit_prev
                    FROM ac_voucher_master AS a
                        JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id
                    WHERE a.status = 4";
                if ($projectId > 0){
                    $sql.= " And b.project_id = ".$projectId;
                }
                $sql.= " AND a.rstatus = 1 And a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND b.acc_debit_id IN (SELECT id FROM acc_id) GROUP BY b.acc_debit_id;";
                $this->connector->CommandText = $sql;
                $this->connector->ExecuteNonQuery();

                // #05: Ambil data bulan-bulan sebelumnya (kredit)
                $sql = "CREATE TEMPORARY TABLE sum_credit_prev AS
                    SELECT b.acc_credit_id, SUM(b.amount) AS total_credit_prev
                    FROM ac_voucher_master AS a JOIN ac_voucher_detail AS b ON a.id = b.voucher_master_id WHERE a.status = 4";
                if ($projectId > 0){
                    $sql.= " And b.project_id = ".$projectId;
                }
                $sql.= " AND a.rstatus = 1 And a.is_deleted = 0 AND a.voucher_date BETWEEN ?firstJan AND ?prev AND b.acc_credit_id IN (SELECT id FROM acc_id) GROUP BY b.acc_credit_id;";
                $this->connector->CommandText = $sql;
                $this->connector->ExecuteNonQuery();

                // #06: OK final query...
                $this->connector->CommandText =
                    "SELECT a.*, b.total_debit, b.mtc_debit,c.mtc_credit,b.acr_debit,c.acr_credit,b.adj_debit,c.adj_credit, d.total_debit_prev, e.total_credit_prev, f.bal_debit_amt, f.bal_credit_amt
                    FROM acc_id AS a
                        LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
                        LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
                        LEFT JOIN sum_debit_prev AS d ON a.id = d.acc_debit_id
                        LEFT JOIN sum_credit_prev AS e ON a.id = e.acc_credit_id
                        LEFT JOIN ac_opening_balance1 AS f ON a.id = acc_id AND bal_date = ?firstJan
                    ORDER BY a.acc_no";
            } else {
                // Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
                // Untuk data bulan-bulan sebelumnya selalu 0
                $this->connector->CommandText =
                    "SELECT a.*, b.total_debit, b.mtc_debit,c.mtc_credit,b.acr_debit,c.acr_credit,b.adj_debit,c.adj_credit, 0 AS total_debit_prev, 0 AS total_credit_prev, f.bal_debit_amt, f.bal_credit_amt
                    FROM acc_id AS a
                        LEFT JOIN sum_debit AS b ON a.id = b.acc_debit_id
                        LEFT JOIN sum_credit AS c ON a.id = c.acc_credit_id
                        LEFT JOIN ac_opening_balance1 AS f ON a.id = acc_id AND bal_date = ?firstJan
                    ORDER BY a.acc_no";
            }

            $report = $this->connector->ExecuteQuery();
        } else {
            $isIncOb = 1;
            $rekapMethod = 0;
            $month = (int)date("n");
            $year = (int)date("Y");
            $projectId = 0;
            $report = null;
            $output = "web";
        }
        //get companies data
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        //get projects data
        $projects = new Project();
        $projects = $projects->LoadByEntityId($this->userCompanyId);
        $this->Set("isIncOb", $isIncOb);
        $this->Set("rekapMethod", $rekapMethod);
        $this->Set("month", $month);
        $this->Set("year", $year);
        $this->Set("report", $report);
        $this->Set("output", $output);
        $this->Set("projectId", $projectId);
        $this->Set("projects", $projects);
        $this->Set("company", $company);
        $this->Set("monthNames", array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
    }
}

// End of file: trialbalance_controller.php
