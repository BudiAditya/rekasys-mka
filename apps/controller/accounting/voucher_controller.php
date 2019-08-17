<?php
require_once(LIBRARY . "accounting_controller.php");

class VoucherController extends AccountingController {
	private $userCompanyId;

	protected function Initialize() {
		parent::Initialize();

		require_once(MODEL . "accounting/voucher.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "e.project", "display" => "Project", "width" =>100);
		$settings["columns"][] = array("name" => "c.doc_code", "display" => "Type", "width" => 40);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "Voucher No.", "width" => 120);
		$settings["columns"][] = array("name" => "a.voucher_source", "display" => "Source", "width" => 80);
		$settings["columns"][] = array("name" => "date_format(a.voucher_date, '%d-%m-%Y')", "display" => "Voucher Date", "width" => 80);
		$settings["columns"][] = array("name" => "a.note", "display" => "Description", "width" => 300);
		$settings["columns"][] = array("name" => "FORMAT(e.total_amount, 2)", "display" => "Amount", "width" => 80, "align" => "right");
		$settings["columns"][] = array("name" => "d.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "date_format(a.update_time, '%d-%m-%Y')", "display" => "Last Update", "width" => 80);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "Voucher No.");
		$settings["filters"][] = array("name" => "c.doc_code", "display" => "Type");
		$settings["filters"][] = array("name" => "date_format(a.voucher_date, '%d-%m-%Y')", "display" => "Voucher Date");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "Status");
		$settings["filters"][] = array("name" => "a.voucher_source", "display" => "Source");
        $settings["filters"][] = array("name" => "e.project", "display" => "Project");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Accounting Voucher List";

			if ($acl->CheckUserAccess("accounting.voucher", "add_master")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "accounting.voucher/add_master", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("accounting.voucher", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "accounting.voucher/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Anda harus memilih 1 dokumen voucher terlebih dahulu !\nPERHATIAN: Pastikan anda memilih tepat 1 dokumen.",
					"Confirm" => "");
			}
            if ($acl->CheckUserAccess("accounting.voucher", "print")) {
                $settings["actions"][] = array("Text" => "Preview", "Url" => "accounting.voucher/print", "Class" => "bt_pdf", "ReqId" => 2,
                    "Error" => "Anda harus memilih dokumen voucher terlebih dahulu !",
                    "Confirm" => "");
            }
			if ($acl->CheckUserAccess("accounting.voucher", "print")) {
				$settings["actions"][] = array("Text" => "Preview", "Url" => "accounting.voucher/print/output:xls", "Class" => "bt_excel", "ReqId" => 2,
					"Error" => "Anda harus memilih dokumen voucher terlebih dahulu !",
					"Confirm" => "");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.voucher", "edit_master")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "accounting.voucher/edit_master/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Anda harus memilih 1 dokumen voucher terlebih dahulu !\nPERHATIAN: Pastikan anda memilih tepat 1 dokumen.",
					"Confirm" => "Apakah anda mau mengedit voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.voucher", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "accounting.voucher/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Anda harus memilih 1 dokumen voucher terlebih dahulu !\nPERHATIAN: Pastikan anda memilih tepat 1 dokumen.",
					"Confirm" => "Apakah anda mau menghapus voucher yang dipilih ?");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.voucher", "batch_approve")) {
				$settings["actions"][] = array("Text" => "Approve Voucher", "Url" => "accounting.voucher/batch_approve/", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-approve semua semua dokumen Voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.voucher", "batch_disapprove")) {
				$settings["actions"][] = array("Text" => "Dis-Approve Voucher", "Url" => "accounting.voucher/batch_disapprove/", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen Voucher yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.voucher", "verify")) {
				$settings["actions"][] = array("Text" => "Verify Voucher", "Url" => "accounting.voucher/verify", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-verify semua semua dokumen Voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.voucher", "unverify")) {
				$settings["actions"][] = array("Text" => "Unverify Voucher", "Url" => "accounting.voucher/unverify", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-UNVERIFY semua semua dokumen Voucher yang dipilih ?");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.voucher", "posting")) {
				$settings["actions"][] = array("Text" => "Posting Voucher", "Url" => "accounting.voucher/posting", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-posting semua semua dokumen Voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.voucher", "unposting")) {
				$settings["actions"][] = array("Text" => "Unposting Voucher", "Url" => "accounting.voucher/unposting", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-UNPOSTING semua semua dokumen Voucher yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;

			// Kill Session
			$this->persistence->DestroyState("accounting.voucher.master");
		} else {
			$settings["from"] =
"ac_voucher_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_doctype AS c ON a.doc_type_id = c.id
	JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'voucher_status'
	JOIN (
		SELECT aa.voucher_master_id, SUM(aa.amount) AS total_amount, group_concat(bb.project_name) AS project
		FROM ac_voucher_detail AS aa
		LEFT JOIN cm_project AS bb ON aa.project_id = bb.id
		GROUP BY aa.voucher_master_id
	) AS e ON a.id = e.voucher_master_id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;

			//tampilkan hanya data dg status "UNPOSTED", tapi bisa dicari juga status "POSTED"
			if ($_GET["query"] == "") {
//                $_GET["qtype"] = 2;
//                $_GET["query"] = "UNPOSTED";
//                $_GET["condition"] = "";
				$_GET["query"] = null;
				$settings["where"] = "a.is_deleted = 0 AND a.status <> 4 AND a.entity_id = " . $this->userCompanyId;
			}
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings);
        //print($this->connector->GenerateLastQuery());
	}

	/**
	 * Untuk entry data voucher ke accounting. Beberapa dokumen akan otomatis masuk ketika diposting dari modul yang bersangkutan
	 * Yang boleh manual entry: Bank Masuk, Bank Keluar, Adjustment Journal
	 */
	public function add_master() {
		require_once(MODEL . "master/company.php");

		// Check session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.voucher.master")) {
			$voucher = $this->persistence->LoadState("accounting.voucher.master");
		} else {
			$voucher = new Voucher();
			$voucher->Date = time();

			$voucher->VoucherSource = "GENERAL";
			$voucher->DocumentNo = "[AUTO]";
			$voucher->EntityId = $this->userCompanyId;
		}

		if (count($this->postData) > 0) {
			$voucher->DocumentTypeId = $this->GetPostValue("DocumentType");
			$voucher->DocumentNo = $this->GetPostValue("DocumentNo");
			$voucher->Date = strtotime($this->GetPostValue("Date"));
			$voucher->Note = $this->GetPostValue("Note");
            $voucher->RStatus = $this->GetPostValue("RStatus");
			$voucher->StatusCode = 1;

			if ($this->ValidateMaster($voucher)) {
				// Ambil no dokumen sementara (Jenis dokumen sudah ditentukan dari user... jadi kita pake yang 'generic' bukan yang sudah pasti :p)
				require_once(MODEL . "common/doc_counter.php");
				$docCounter = new DocCounter();
				$voucher->DocumentNo = $docCounter->AutoDocNo($this->userCompanyId, $voucher->DocumentTypeId, $voucher->Date, 0);
				if ($voucher->DocumentNo != null) {
					$this->persistence->SaveState("accounting.voucher.master", $voucher);
					redirect_url("accounting.voucher/add_detail");
				} else {
					$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
				}
			}
		} else {
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->persistence->SaveState("info", "Maaf saat ini login anda masih terditeksi sebagai CORP. Entry voucher harus menggunakan login Company / Menggunakan fasilitas Impersonate");
				redirect_url("accounting.voucher");
			}
		}

		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("voucher", $voucher);
	}

	private function ValidateMaster(Voucher $voucher) {
		if ($voucher->DocumentTypeId == null) {
			$this->Set("error", "Maaf harap memilih jenis voucher terlebih dahulu");
			return false;
		}
		if ($voucher->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu !");
			return false;
		}
		if ($voucher->DocumentNo == null) {
			$this->Set("error", "Mohon masukkan nomor Voucher terlebih dahulu !");
			return false;
		}
		if (!is_int($voucher->Date)) {
			$this->Set("error", "Mohon masukkan tanggal Voucher terlebih dahulu !");
			return false;
		}
		if ($this->forcePeriode) {
			if ($voucher->FormatDate("Ym") != ($this->accYear * 100) + $this->accMonth) {
				$this->Set("error", sprintf("Maaf anda tidak dapat menginput Voucher dengan tanggal: %s karena Periode Akun yang diset adalah: %s %s", $voucher->FormatDate(), $this->accMonthName, $this->accYear));
				return false;
			}
		}
		return true;
	}

	public function add_detail() {
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/units.php");

		// Cek session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.voucher.master")) {
			$voucher = $this->persistence->LoadState("accounting.voucher.master");
		} else {
			// Hmm ga ada session ???
			redirect_url("accounting.voucher/add_master");
			return;
		}

		if (count($this->postData) > 0) {
			// Reset detail apapun ang terjadi
			$voucher->Details = array();

			// Proses data yang dikirim
			$accDebitIds = $this->GetPostValue("Debit", array());
			$accCreditIds = $this->GetPostValue("Credit", array());
			$deptIds = $this->GetPostValue("Department", array());
			$actIds = $this->GetPostValue("Activity", array());
			$amounts = $this->GetPostValue("Amount", array());
			$debtorIds = $this->GetPostValue("Debtor", array());
			$creditorIds = $this->GetPostValue("Creditor", array());
			$employeeIds = $this->GetPostValue("Employee", array());
			$projectIds = $this->GetPostValue("Project", array());
            $unitIds = $this->GetPostValue("Unit", array());
			$notes = $this->GetPostValue("Note", array());

			$max = count($accDebitIds);
			for ($i = 0; $i < $max; $i++) {
				$detail = new VoucherDetail();

				$detail->AccDebitId = $accDebitIds[$i];
				$detail->AccCreditId = $accCreditIds[$i];
				$detail->Amount = str_replace(",", "", $amounts[$i]);
				$detail->DepartmentId = $deptIds[$i];
				$detail->ActivityId = $actIds[$i];
				$detail->DebtorId = $debtorIds[$i] != "" ? $debtorIds[$i] : null;
				$detail->CreditorId = $creditorIds[$i] != "" ? $creditorIds[$i] : null;
				$detail->EmployeeId = $employeeIds[$i] != "" ? $employeeIds[$i] : null;
				$detail->ProjectId = $projectIds[$i] != "" ? $projectIds[$i] : null;
                $detail->UnitId = $projectIds[$i] != "" ? $unitIds[$i] : null;
                
				$detail->Note = $notes[$i];

				$voucher->Details[] = $detail;
			}

			if ($this->ValidateDetail($voucher)) {
				$this->persistence->SaveState("accounting.voucher.master", $voucher);
				redirect_url("accounting.voucher/add_confirm");
			}
		}

		$account = new Coa();
		$department = new Department();
		$activity = new Activity();
        $activitys = $activity->LoadByEntityId($voucher->EntityId);
		$debtor = new Debtor();
		$creditor = new Creditor();
		$employee = new Employee();
		$project = new Project();
		$units = new Units();
		$details = array();
		foreach ($voucher->Details as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}

		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("docType", new DocType($voucher->DocumentTypeId));
		$this->Set("accounts", $account->LoadByLevel($this->userCompanyId,3));
		$this->Set("departments", $department->LoadByEntityId($voucher->EntityId));
		$this->Set("activitys", $activitys);
		$this->Set("debtors", $debtor->LoadAll("b.entity_cd, a.debtor_cd"));
		$this->Set("creditors", $creditor->LoadAll("b.entity_cd, a.creditor_name"));
		$this->Set("employees", $employee->LoadByEntityId($voucher->EntityId));
		$this->Set("projects", $project->LoadByEntityId($voucher->EntityId));
        $this->Set("units", $units->LoadAll($voucher->EntityId));

		$this->Set("voucher", $voucher);
		$this->Set("details", $details);
	}

	private function ValidateDetail(Voucher $voucher, $totalDeleted = 0) {
		if (count($voucher->Details) - $totalDeleted == 0) {
			$this->Set("error", "Maaf anda harus memasukkan detail voucher sekurang-kurangnya 1 detail");
			return false;
		}

		foreach ($voucher->Details as $idx => $detail) {
			if ($detail->AccDebitId == null) {
				$this->Set("error", "Maaf akun debet no. " . ($idx + 1) . " masih kosong ! Harap mengisi akun debet");
				return false;
			}
			if ($detail->AccCreditId == null) {
				$this->Set("error", "Maaf akun kredit no. " . ($idx + 1) . " masih kosong ! Harap mengisi akun kredit");
				return false;
			}
			if ($detail->AccDebitId == $detail->AccCreditId) {
				$this->Set("error", "Maaf akun debit no. " . ($idx + 1) . " sama dengan akun kreditnya ! Harap memilih akun kredit yang berbeda");
				return false;
			}
//			if ($detail->ActivityId == null) {
//				$this->Set("error", "Maaf divisi no. " . ($idx + 1) . " masih kosong ! Harap memilih divisi");
//				return false;
//			}
			if ($detail->Amount == 0) {
				$this->Set("error", "Maaf jumlah no. " . ($idx + 1) . " tidak boleh bernilai nol ! Harap mengisi jumlah terlebih dahulu.");
				return false;
			}
			if ($detail->Note == null) {
				$this->Set("error", "Maaf keterangan no. " . ($idx + 1) . " masih kosong ! Harap mengisi keterangan.");
				return false;
			}

			// force null value
			if ($detail->DepartmentId == "") {
				$detail->DepartmentId = null;
			}
			if ($detail->ActivityId == "") {
				$detail->ActivityId = null;
			}
			if ($detail->DebtorId == "") {
				$detail->DebtorId = null;
			}
			if ($detail->CreditorId == "") {
				$detail->CreditorId = null;
			}
		}
		return true;
	}

	public function add_confirm() {
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/units.php");

		// Cek session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.voucher.master")) {
			$voucher = $this->persistence->LoadState("accounting.voucher.master");
		} else {
			// Hmm ga ada session ???
			redirect_url("accounting.voucher/add_master");
			return;
		}

		$docType = new DocType();
		$docType = $docType->FindById($voucher->DocumentTypeId);

		// Check apakah user ada kirim data atau tidak
		if (count($this->postData) > 0) {
			$voucher->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

			$this->connector->BeginTransaction();
			if ($this->doAdd($voucher)) {
				$this->connector->CommitTransaction();

				// YEAHHH success
				$this->persistence->SaveState("info", sprintf("Voucher %s nomor: %s telah berhasil disimpan.", $docType->DocCode, $voucher->DocumentNo));
				$this->persistence->DestroyState("accounting.voucher.master");
				redirect_url("accounting.voucher/view/" . $voucher->Id);
			} else {
				$this->connector->RollbackTransaction();
			}
		}

		// Untuk Detail yang lainnya kita dynamic loading saja....
		$accounts = array();
		$departments = array();
		$activitys = array();
		$debtors = array();
		$creditors = array();
		$employees = array();
		$projects = array();
        $units = array();
		foreach ($voucher->Details as $detail) {
			if (!array_key_exists($detail->AccDebitId, $accounts)) {
				$accounts[$detail->AccDebitId] = new Coa($detail->AccDebitId);
			}
			if (!array_key_exists($detail->AccCreditId, $accounts)) {
				$accounts[$detail->AccCreditId] = new Coa($detail->AccCreditId);
			}
			if ($detail->DepartmentId != null && !array_key_exists($detail->DepartmentId, $departments)) {
				$departments[$detail->DepartmentId] = new Department($detail->DepartmentId);
			}
			if ($detail->ActivityId != null && !array_key_exists($detail->ActivityId, $activitys)) {
				$activitys[$detail->ActivityId] = new Activity($detail->ActivityId);
			}
			if ($detail->DebtorId != null && !array_key_exists($detail->DebtorId, $debtors)) {
				$debtors[$detail->DebtorId] = new Debtor($detail->DebtorId);
			}
			if ($detail->CreditorId != null && !array_key_exists($detail->CreditorId, $creditors)) {
				$creditors[$detail->CreditorId] = new Creditor($detail->CreditorId);
			}
			if ($detail->EmployeeId != null && !array_key_exists($detail->EmployeeId, $employees)) {
				$employees[$detail->EmployeeId] = new Employee($detail->EmployeeId);
			}
			if ($detail->ProjectId != null && !array_key_exists($detail->ProjectId, $projects)) {
				$projects[$detail->ProjectId] = new Project($detail->ProjectId);
			}
            if ($detail->UnitId != null && !array_key_exists($detail->UnitId, $units)) {
                $units[$detail->UnitId] = new Units($detail->UnitId);
            }
		}

		// Set variable :p
		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("docType", $docType);
		$this->Set("accounts", $accounts);
		$this->Set("departments", $departments);
		$this->Set("activitys", $activitys);
		$this->Set("projects", $projects);
		$this->Set("debtors", $debtors);
        $this->Set("units", $units);
		$this->Set("creditors", $creditors);
		$this->Set("employees", $employees);

		$this->Set("voucher", $voucher);
	}

	private function doAdd(Voucher $voucher) {
		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$voucher->DocumentNo = $docCounter->AutoDocNo($voucher->EntityId, $voucher->DocumentTypeId, $voucher->Date, 1);
		if ($voucher->DocumentNo == null) {
			$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
			return false;
		}

		$rs = $voucher->Insert();
		if ($rs != 1) {
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
			}
			return false;
		}

		$counter = 0;
		foreach ($voucher->Details as $detail) {
			$counter++;
			$detail->VoucherId = $voucher->Id;
			$detail->Sequence = $counter;
			$rs = $detail->Insert();

			if ($rs != 1) {
				// Ada DBase error karena kita tidak enforce unique key untuk detailnya
				$this->Set("error", "Maaf error saat simpan detail No. $counter. Message: " . $this->connector->GetErrorMessage());
				return false;
			}
		}

		return true;
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Voucher Accounting terlebih dahulu !");
			redirect_url("accounting.voucher");
			return;
		}

		$voucher = new Voucher();
		$voucher = $voucher->LoadById($id);
		if ($voucher == null || $voucher->IsDeleted) {
			$this->persistence->SaveState("error", "Dokumen Voucher yang diminta tidak dapat ditemukan / sudah dihapus !");
			redirect_url("accounting.voucher");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($voucher->EntityId != $this->userCompanyId) {
				// WOW coba akses data lintas Company ? Simulate not found !
				$this->persistence->SaveState("error", "Dokumen Voucher yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url("accounting.voucher");
				return;
			}
		}

		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/units.php");
		// Load details data
		$voucher->LoadDetails();

		// Untuk Detail yang lainnya kita dynamic loading saja....
		$accounts = array();
		$departments = array();
		$activitys = array();
		$debtors = array();
		$creditors = array();
		$employees = array();
		$projects = array();
        $units = array();
		foreach ($voucher->Details as $detail) {
			if (!array_key_exists($detail->AccDebitId, $accounts)) {
				$accounts[$detail->AccDebitId] = new Coa($detail->AccDebitId);
			}
			if (!array_key_exists($detail->AccCreditId, $accounts)) {
				$accounts[$detail->AccCreditId] = new Coa($detail->AccCreditId);
			}
			if ($detail->DepartmentId != null && !array_key_exists($detail->DepartmentId, $departments)) {
				$departments[$detail->DepartmentId] = new Department($detail->DepartmentId);
			}
			if ($detail->ActivityId != null && !array_key_exists($detail->ActivityId, $activitys)) {
				$activitys[$detail->ActivityId] = new Activity($detail->ActivityId);
			}
			if ($detail->DebtorId != null && !in_array($detail->DebtorId, $debtors)) {
				$debtors[$detail->DebtorId] = new Debtor($detail->DebtorId);
			}
			if ($detail->CreditorId != null && !in_array($detail->CreditorId, $creditors)) {
				$creditors[$detail->CreditorId] = new Creditor($detail->CreditorId);
			}
			if ($detail->EmployeeId != null && !in_array($detail->EmployeeId, $employees)) {
				$employees[$detail->EmployeeId] = new Employee($detail->EmployeeId);
			}
			if ($detail->ProjectId != null && !in_array($detail->ProjectId, $projects)) {
				$projects[$detail->ProjectId] = new Project($detail->ProjectId);
			}
            if ($detail->UnitId != null && !array_key_exists($detail->UnitId, $units)) {
                $units[$detail->UnitId] = new Units($detail->UnitId);
            }
		}

		// Set variable :p
		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("docType", new DocType($voucher->DocumentTypeId));
		$this->Set("accounts", $accounts);
		$this->Set("departments", $departments);
		$this->Set("activitys", $activitys);
		$this->Set("projects", $projects);
		$this->Set("debtors", $debtors);
		$this->Set("creditors", $creditors);
		$this->Set("employees", $employees);
        $this->Set("units", $units);
		$this->Set("voucher", $voucher);
		$this->Set("previous", $voucher->SeekPreviousVoucher());
		$this->Set("next", $voucher->SeekNextVoucher());

		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function edit_master($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Voucher Accounting terlebih dahulu !");
			redirect_url("accounting.voucher");
			return;
		}

		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/doc_type.php");

		// Check session
		$sessionValid = false;
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.voucher.master")) {
			$voucher = $this->persistence->LoadState("accounting.voucher.master");
			if ($voucher->Id != $id) {
				$voucher = $voucher->LoadById($id);
				$this->persistence->DestroyState("accounting.voucher.master");
			} else {
				$sessionValid = true;
			}
		} else {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);
		}

		if (count($this->postData) > 0) {
			$voucher->DocumentTypeId = $this->GetPostValue("DocumentType");
			$voucher->DocumentNo = $this->GetPostValue("DocumentNo");
			$voucher->Date = strtotime($this->GetPostValue("Date"));
			$voucher->Note = $this->GetPostValue("Note");
            $voucher->RStatus = $this->GetPostValue("RStatus");
			$voucher->StatusCode = 1;

			if ($this->ValidateMaster($voucher)) {
				if (!$sessionValid) {
					$voucher->LoadDetails();
				}

				$this->persistence->SaveState("accounting.voucher.master", $voucher);
				redirect_url("accounting.voucher/edit_detail/" . $id);
			}
		} else {
			// Checking....
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Ops.. different Company try to access ! Simulate not found !
					$this->persistence->SaveState("error", "Maaf dokumen Voucher yang diminta tidak dapat ditemukan / sudah dihapus !");
					redirect_url("accounting.voucher");
				}
			}
			if ($voucher == null || $voucher->IsDeleted) {
				$this->persistence->SaveState("error", "Maaf dokumen Voucher yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url("accounting.voucher");
			}
			if (!$voucher->IsVoucherEditable()) {
				$this->persistence->SaveState("info", "Maaf jenis dokumen Voucher yang diminta tidak boleh diedit secara manual karena hasil posting modul yang lain.");
				redirect_url("accounting.voucher/view/" . $voucher->Id);
			}
			if ($voucher->StatusCode > 1) {
				$this->persistence->SaveState("info", "Maaf status dokumen Voucher yang diminta sudah bukan DRAFT ! Editing hanya untuk dokumen status DRAFT.");
				redirect_url("accounting.voucher/view/" . $voucher->Id);
			}
			if ($this->forcePeriode) {
				if ($voucher->FormatDate("Ym") != ($this->accYear * 100) + $this->accMonth) {
					$this->persistence->SaveState("error", sprintf("Maaf anda tidak dapat mengedit Voucher dengan tanggal: %s karena Periode Akun yang diset adalah: %s %s", $voucher->FormatDate(), $this->accMonthName, $this->accYear));
					redirect_url("accounting.voucher/view/" . $voucher->Id);
				}
			}

			// Kita cek apakah masih boleh edit dokumen atau tidak...
			require_once(MODEL . "common/doc_counter.php");
			$docCounter = new DocCounter();
			$docCounter = $docCounter->LoadByDocType($voucher->DocumentTypeId, $voucher->EntityId, $voucher->Date);

			if ($docCounter == null || $docCounter->IsLocked) {
				if ($docCounter == null) {
					$this->persistence->SaveState("error", "DocCounter not found ! Please contact your system administrator !");
				}
				$this->persistence->SaveState("info", "Dokumen yang diminta sudah tidak dapat di-edit ! Periode Dokumen sudah ter-LOCK !");
				redirect_url("accounting.voucher/view/" . $voucher->Id);
			}
		}

		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("docType", new DocType($voucher->DocumentTypeId));
		$this->Set("voucher", $voucher);
	}

	public function edit_detail($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Voucher Accounting terlebih dahulu !");
			redirect_url("accounting.voucher");
			return;
		}

		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/units.php");

		// Cek session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.voucher.master")) {
			$voucher = $this->persistence->LoadState("accounting.voucher.master");
			if ($voucher->Id != $id) {
				redirect_url("accounting.voucher/edit_master/" . $id);
				return;
			}
		} else {
			// Hmm ga ada session ???
			redirect_url("accounting.voucher/edit_master/" . $id);
			return;
		}

		if (count($this->postData) > 0) {
			// Reset detail apapun ang terjadi
			$voucher->Details = array();

			// Proses data yang dikirim
			$ids = $this->GetPostValue("Id", array());
			$accDebitIds = $this->GetPostValue("Debit", array());
			$accCreditIds = $this->GetPostValue("Credit", array());
			$deptIds = $this->GetPostValue("Department", array());
			$actIds = $this->GetPostValue("Activity", array());
			$amounts = $this->GetPostValue("Amount", array());
			$debtorIds = $this->GetPostValue("Debtor", array());
			$creditorIds = $this->GetPostValue("Creditor", array());
			$employeeIds = $this->GetPostValue("Employee", array());
			$projectIds = $this->GetPostValue("Project", array());
            $unitIds = $this->GetPostValue("Unit", array());
			$notes = $this->GetPostValue("Note", array());
			// Reference data yang dihapus
			$markDeletes = $this->GetPostValue("markDelete", array());

			$max = count($accDebitIds);
			for ($i = 0; $i < $max; $i++) {
				$detail = new VoucherDetail();

				$detail->Id = $ids[$i];
				$detail->AccDebitId = $accDebitIds[$i];
				$detail->AccCreditId = $accCreditIds[$i];
				$detail->Amount = str_replace(",", "", $amounts[$i]);
				$detail->DepartmentId = $deptIds[$i];
				$detail->ActivityId = $actIds[$i];
				$detail->DebtorId = $debtorIds[$i] != "" ? $debtorIds[$i] : null;
				$detail->CreditorId = $creditorIds[$i] != "" ? $creditorIds[$i] : null;
				$detail->EmployeeId = $employeeIds[$i] != "" ? $employeeIds[$i] : null;
				$detail->ProjectId = $projectIds[$i] != "" ? $projectIds[$i] : null;
                $detail->UnitId = $unitIds[$i] != "" ? $unitIds[$i] : null;
				$detail->Note = $notes[$i];
				$detail->MarkedForDeletion = in_array($detail->Id, $markDeletes);

				$voucher->Details[] = $detail;
			}

			if ($this->ValidateDetail($voucher, count($markDeletes))) {
				$this->persistence->SaveState("accounting.voucher.master", $voucher);
				redirect_url("accounting.voucher/edit_confirm/" . $id);
			}
		}

		$account = new Coa();
		$department = new Department();
		$activity = new Activity();
        $activitys = $activity->LoadByEntityId($voucher->EntityId);
		$debtor = new Debtor();
		$creditor = new Creditor();
		$employee = new Employee();
		$project = new Project();
		$unit = new Units();
		$details = array();
		foreach ($voucher->Details as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}

		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("docType", new DocType($voucher->DocumentTypeId));
		$this->Set("accounts", $account->LoadByLevel($voucher->EntityId,3));
		$this->Set("departments", $department->LoadByEntityId($voucher->EntityId));
		$this->Set("activitys", $activitys);
		$this->Set("debtors", $debtor->LoadAll("b.entity_cd, a.debtor_cd"));
		$this->Set("creditors", $creditor->LoadAll("b.entity_cd, a.creditor_name"));
		$this->Set("employees", $employee->LoadByEntityId($voucher->EntityId));
		$this->Set("projects", $project->LoadByEntityId($voucher->EntityId));
        $this->Set("units", $unit->LoadAll($voucher->EntityId));
		$this->Set("voucher", $voucher);
		$this->Set("details", $details);
	}

	public function edit_confirm($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Voucher Accounting terlebih dahulu !");
			redirect_url("accounting.voucher");
			return;
		}

		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/units.php");

		// Cek session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.voucher.master")) {
			$voucher = $this->persistence->LoadState("accounting.voucher.master");
		} else {
			// Hmm ga ada session ???
			redirect_url("accounting.voucher/edit_master/" . $id);
			return;
		}

		$docType = new DocType();
		$docType = $docType->FindById($voucher->DocumentTypeId);

		// Check apakah user ada kirim data atau tidak
		if (count($this->postData) > 0) {
			$voucher->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

			$this->connector->BeginTransaction();
			if ($this->doEdit($voucher)) {
				$this->connector->CommitTransaction();

				// YEAHHH success
				$this->persistence->SaveState("info", sprintf("Perubahan Voucher %s nomor: %s telah berhasil disimpan.", $docType->DocCode, $voucher->DocumentNo));
				$this->persistence->DestroyState("accounting.voucher.master");
				redirect_url("accounting.voucher/view/" . $voucher->Id);
			} else {
				$this->connector->RollbackTransaction();
			}
		}

		// Untuk Detail yang lainnya kita dynamic loading saja....
		$accounts = array();
		$departments = array();
		$activitys = array();
		$debtors = array();
		$creditors = array();
		$employees = array();
		$projects = array();
        $units = array();

		foreach ($voucher->Details as $detail) {
			if (!array_key_exists($detail->AccDebitId, $accounts)) {
				$accounts[$detail->AccDebitId] = new Coa($detail->AccDebitId);
			}
			if (!array_key_exists($detail->AccCreditId, $accounts)) {
				$accounts[$detail->AccCreditId] = new Coa($detail->AccCreditId);
			}
			if ($detail->DepartmentId != null && !array_key_exists($detail->DepartmentId, $departments)) {
				$departments[$detail->DepartmentId] = new Department($detail->DepartmentId);
			}
			if ($detail->ActivityId != null && !array_key_exists($detail->ActivityId, $activitys)) {
				$activitys[$detail->ActivityId] = new Activity($detail->ActivityId);
			}
			if ($detail->DebtorId != null && !array_key_exists($detail->DebtorId, $debtors)) {
				$debtors[$detail->DebtorId] = new Debtor($detail->DebtorId);
			}
			if ($detail->CreditorId != null && !array_key_exists($detail->CreditorId, $creditors)) {
				$creditors[$detail->CreditorId] = new Creditor($detail->CreditorId);
			}
			if ($detail->EmployeeId != null && !array_key_exists($detail->EmployeeId, $employees)) {
				$employees[$detail->EmployeeId] = new Employee($detail->EmployeeId);
			}
			if ($detail->ProjectId != null && !array_key_exists($detail->ProjectId, $projects)) {
				$projects[$detail->ProjectId] = new Project($detail->ProjectId);
			}
            if ($detail->UnitId != null && !array_key_exists($detail->UnitId, $units)) {
                $units[$detail->UnitId] = new Units($detail->UnitId);
            }
		}

		// Set variable :p
		$this->Set("company", new Company($voucher->EntityId));
		$this->Set("docType", $docType);
		$this->Set("accounts", $accounts);
		$this->Set("departments", $departments);
		$this->Set("activitys", $activitys);
		$this->Set("projects", $projects);
		$this->Set("debtors", $debtors);
		$this->Set("creditors", $creditors);
		$this->Set("employees", $employees);
        $this->Set("units", $units);
		$this->Set("voucher", $voucher);
	}

	private function doEdit(Voucher $voucher) {
		$rs = $voucher->Update($voucher->Id);
		if ($rs != 1) {
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf No. Dokumen Voucher sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat merubah master Voucher. Message: " . $errMsg);
			}
			return false;
		}

		$counter = 0;
		foreach ($voucher->Details as $detail) {
			$counter++;
			// OK Cek untuk penghapusan data dulu
			if ($detail->MarkedForDeletion) {
				$rs = $detail->Delete($detail->Id);
				if ($rs == -1) {
					$this->Set("error", "Gagal hapus detail dengan ID: " . $detail->Id . ". Mohon hubungi system admin.");
					return false;
				}
			} else {
				$detail->VoucherId = $voucher->Id;
				$detail->Sequence = $counter;
				if ($detail->Id == null) {
					$rs = $detail->Insert();
				} else {
					$rs = $detail->Update($detail->Id);
				}
			}

			// Untuk update gagal kalau -1 (0 = no error and nothing updated)
			if ($rs == 1 || $rs == 0) {
				// Lanjutttt
				continue;
			}

			// Gagal Insert Detail
			$this->Set("error", "Maaf error saat simpan/update detail No. $counter. Message: " . $this->connector->GetErrorMessage());
			return false;
		}

		return true;
	}

	public function delete($id) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Voucher terlebih dahulu.");
			redirect_url("inventory.is");
			return;
		}

		$voucher = new Voucher();
		$voucher = $voucher->LoadById($id);
		if ($voucher == null || $voucher->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen Voucher yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url("accounting.voucher");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($voucher->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen Voucher yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url("accounting.voucher");
				return;
			}
		}
		if (!$voucher->IsVoucherEditable()) {
			$this->persistence->SaveState("info", "Maaf jenis dokumen Voucher yang diminta tidak boleh dihapus secara manual karena hasil posting modul yang lain.");
			redirect_url("accounting.voucher/view/" . $voucher->Id);
			return;
		}
		if ($voucher->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen Voucher yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("accounting.voucher/view/" . $voucher->Id);
			return;
		}
		// Kita cek apakah masih boleh edit dokumen atau tidak...
		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$docCounter = $docCounter->LoadByDocType($voucher->DocumentTypeId, $voucher->EntityId, $voucher->Date);

		if ($docCounter == null || $docCounter->IsLocked) {
			if ($docCounter == null) {
				$this->persistence->SaveState("error", "DocCounter not found ! Please contact your system administrator !");
			}
			$this->persistence->SaveState("info", "Dokumen yang diminta sudah tidak dapat di-delete ! Periode Dokumen sudah ter-LOCK !");
			redirect_url("accouting.voucher/view/" . $voucher->Id);
			return;
		}

		// Everything is green
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		$voucher->UpdatedById = $userId;
		if ($voucher->Delete($voucher->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Dokumen Voucher: %s sudah berhasil dihapus.", $voucher->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Voucher: %s ! Harap hubungi system administrator.<br />Error: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage()));
		}

		redirect_url("accounting.voucher");
	}

	public function batch_approve() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher yang akan di approve !");
			redirect_url("accounting.voucher");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != 1) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena status sudah bukan DRAFT ! Status Dokumen: %s", $voucher->DocumentNo, $voucher->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$voucher->ApprovedById = $userId;
			$rs = $voucher->Approve($voucher->Id);
			if ($rs) {
				$infos[] = sprintf("Dokumen Voucher: %s sudah berhasil di approve", $voucher->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Approve Dokumen Voucher: %s. Message: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
        if (count($ids) == 1) {
            redirect_url("accounting.voucher/view/" . $ids[0]);
        } else {
            redirect_url("accounting.voucher");
        }
	}

	public function batch_disapprove() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher yang akan di batalkan !");
			redirect_url("accounting.voucher");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != 2) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $voucher->DocumentNo, $voucher->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$voucher->UpdatedById = $userId;
			$rs = $voucher->DisApprove($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen Voucher: %s sudah berhasil di dibatalkan (disapprove)", $voucher->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Membatalkan / Disapprove Dokumen Voucher: %s. Message: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
        if (count($ids) == 1) {
            redirect_url("accounting.voucher/view/" . $ids[0]);
        } else {
            redirect_url("accounting.voucher");
        }
	}

	public function verify() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher yang akan di verify !");
			redirect_url("accounting.voucher");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != 2) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena status sudah bukan APPROVED ! Status Dokumen: %s", $voucher->DocumentNo, $voucher->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$voucher->VerifiedById = $userId;
			$rs = $voucher->Verify($voucher->Id);
			if ($rs) {
				$infos[] = sprintf("Dokumen Voucher: %s sudah berhasil di verifikasi", $voucher->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Verifikasi Dokumen Voucher: %s. Message: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
        if (count($ids) == 1) {
            redirect_url("accounting.voucher/view/" . $ids[0]);
        } else {
            redirect_url("accounting.voucher");
        }
	}

	public function unverify() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher yang akan di unverify !");
			redirect_url("accounting.voucher");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena status sudah bukan VERIFIED ! Status Dokumen: %s", $voucher->DocumentNo, $voucher->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$voucher->UpdatedById = $userId;
			$rs = $voucher->UnVerify($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen Voucher: %s sudah berhasil di unverify", $voucher->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Unposting Dokumen Voucher: %s. Message: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
        if (count($ids) == 1) {
            redirect_url("accounting.voucher/view/" . $ids[0]);
        } else {
            redirect_url("accounting.voucher");
        }
	}

	public function posting() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher yang akan di posting !");
			redirect_url("accounting.voucher");
			return;
		}

		require_once(MODEL . "common/doc_counter.php");
		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != 3) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena status sudah bukan VERIFIED ! Status Dokumen: %s", $voucher->DocumentNo, $voucher->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}
			// Kita cek apakah masih boleh edit dokumen atau tidak...
			$docCounter = new DocCounter();
			$docCounter = $docCounter->LoadByDocType($voucher->DocumentTypeId, $voucher->EntityId, $voucher->Date);
			if ($docCounter == null) {
				$errors[] = sprintf("Voucher : %s tidak diproses karena tidak ditemukan DocCounter-nya.", $voucher->DocumentNo);
				continue;
			}
			if ($docCounter->IsLocked) {
				$errors[] = sprintf("Voucher : %s tidak diproses karena sudah ter-LOCK !.", $voucher->DocumentNo);
				continue;
			}

			$voucher->PostedById = $userId;
			$rs = $voucher->Post($voucher->Id);
			if ($rs) {
				$infos[] = sprintf("Dokumen Voucher: %s sudah berhasil di posting", $voucher->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Posting Dokumen Voucher: %s. Message: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
        if (count($ids) == 1) {
            redirect_url("accounting.voucher/view/" . $ids[0]);
        } else {
            redirect_url("accounting.voucher");
        }
	}

	public function unposting() {
		$ids = $this->GetGetValue("id", array());

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher yang akan di unposting !");
			redirect_url("accounting.voucher");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != 4) {
				$errors[] = sprintf("Dokumen Voucher: %s tidak diproses karena status sudah bukan POSTED ! Status Dokumen: %s", $voucher->DocumentNo, $voucher->GetStatus());
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}

			$voucher->UpdatedById = $userId;
			$rs = $voucher->UnPost($id);
			if ($rs != -1) {
				$infos[] = sprintf("Dokumen Voucher: %s sudah berhasil di unposting", $voucher->DocumentNo);
			} else {
				$errors[] = sprintf("Gagal Unposting Dokumen Voucher: %s. Message: %s", $voucher->DocumentNo, $this->connector->GetErrorMessage());
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}
        if (count($ids) == 1) {
            redirect_url("accounting.voucher/view/" . $ids[0]);
        } else {
            redirect_url("accounting.voucher");
        }
	}

    public function _print() {
        $ids = $this->GetGetValue("id", array());
		$output = $this->GetNamedValue("output", "pdf");
		set_time_limit(600);

        $result = array();
        foreach ($ids as $id) {
            $voucher = new Voucher();
            $voucher->LoadById($id);
            $voucher->LoadDetails();
            $voucher->LoadCompany();
            $voucher->LoadVoucherType();

            foreach($voucher->Details as $detail) {
                $detail->LoadAccount();
                $detail->LoadDept();
            }

            $result[] = $voucher;
        }

		$this->Set("output", $output);
        $this->Set("report", $result);
    }

	public function print_all() {
		$sortableColumns = array(
			array("column" => "b.doc_code", "display" => "Jenis Dokumen"),
			array("column" => "a.doc_no", "display" => "Nomor Dokumen"),
			array("column" => "a.voucher_date", "display" => "Tgl Dokumen")
		);

		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$status = $this->GetGetValue("status", -1);
			$sort1 = $this->GetGetValue("sort1", 0);
			$sort2 = $this->GetGetValue("sort2", 0);
			$sort3 = $this->GetGetValue("sort3", 0);

			// Pastikan tidak ada yang iseng
			$sort1 = min($sort1, 2);
			$sort2 = min($sort2, 2);
			$sort3 = min($sort3, 2);

			$where = "";
			if ($status != -1) {
				$where .= " AND a.status = ?status";
				$this->connector->AddParameter("?status", $status);
			}

			$orderBy[] = $sortableColumns[$sort1]["column"];
			if ($sort2 != -1) {
				$orderBy[] = $sortableColumns[$sort2]["column"];
			}
			if ($sort3 != -1) {
				$orderBy[] = $sortableColumns[$sort3]["column"];
			}

			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$query =
"SELECT a.id, d.entity_cd, b.doc_code, b.description, a.doc_no, a.voucher_date, a.status, c.short_desc, a.note, b.accvoucher_id
FROM ac_voucher_master AS a
	JOIN cm_doctype AS b ON a.doc_type_id = b.id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'voucher_status'
	JOIN cm_company AS d ON a.entity_id = d.entity_id
WHERE a.voucher_date BETWEEN ?start AND ?end %s
ORDER BY %s";
			} else {
				$query =
"SELECT a.id, d.entity_cd, b.doc_code, b.description, a.doc_no, a.voucher_date, a.status, c.short_desc, a.note, b.accvoucher_id
FROM ac_voucher_master AS a
	JOIN cm_doctype AS b ON a.doc_type_id = b.id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'voucher_status'
	JOIN cm_company AS d ON a.entity_id = d.entity_id
WHERE a.entity_id = ?sbu AND a.voucher_date BETWEEN ?start AND ?end %s
ORDER BY %s";
				$this->connector->AddParameter("?sbu", $this->userCompanyId);
			}
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));

			$this->connector->CommandText = sprintf($query, $where, implode(", ", $orderBy));
			$reader = $this->connector->ExecuteQuery();
		} else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = mktime(0, 0, 0);
			$status = 4;
			$sort1 = 0;
			$sort2 = 2;
			$sort3 = -1;

			$reader = null;
		}

		$this->Set("sortableColumns", $sortableColumns);
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("status", $status);
		$this->Set("sort1", $sort1);
		$this->Set("sort2", $sort2);
		$this->Set("sort3", $sort3);
		$this->Set("reader", $reader);
		if ($reader !== null) {
			require_once(MODEL . "accounting/voucher_type.php");
			$type = new VoucherType();
			$this->Set("types", $type->LoadAll());
		} else {
			$this->Set("types", null);
		}
	}
}


// End of File: voucher_controller.php
