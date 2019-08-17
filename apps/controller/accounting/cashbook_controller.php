<?php
require_once(LIBRARY . "accounting_controller.php");

/**
 * Disini juga akan ada banyak multi step... Mau coba pattern baru untuk multi step nya
 * NEW PATTERN: public methodnya tetap sama untuk menentukan stepnya diambil dari parameter GET dan data diambil dari POST
 */
class CashBookController extends AccountingController {
	private $userCompanyId;
	private $isCorporate;
	protected  $lockDocId = null;

	protected function Initialize() {
		parent::Initialize();

		require_once(MODEL . "accounting/voucher.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
		$this->isCorporate = $this->persistence->LoadState("is_corporate");

		$this->lockDocId = $this->GetNamedValue("lockDocId");
	}

	public function index() {
		if ($this->lockDocId == 2) {
			$title = "BKK";
			$controller = "accounting.bkk";
		} else if ($this->lockDocId == 3) {
			$title = "BKM";
			$controller = "accounting.bkm";
		} else {
			$title = "Bukti Kas";
			$controller = "accounting.cashbook";
		}

		$router = Router::GetInstance()->GetRouteData();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "c.doc_code", "display" => "Type", "width" => 60);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "Doc No.", "width" => 120);
		$settings["columns"][] = array("name" => "a.voucher_source", "display" => "Source", "width" => 80);
		$settings["columns"][] = array("name" => "date_format(a.voucher_date, '%d-%m-%Y')", "display" => "Date", "width" => 80);
		$settings["columns"][] = array("name" => "a.note", "display" => "Description", "width" => 300);
		$settings["columns"][] = array("name" => "FORMAT(e.total_amount, 2)", "display" => "Amount", "width" => 80, "align" => "right");
		$settings["columns"][] = array("name" => "d.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "date_format(a.update_time, '%d-%m-%Y')", "display" => "Last Update", "width" => 80);

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No. Dokumen");
		$settings["filters"][] = array("name" => "c.doc_code", "display" => "Jenis");
		$settings["filters"][] = array("name" => "date_format(a.voucher_date, '%d-%m-%Y')", "display" => "Tgl. Dokumen");
		$settings["filters"][] = array("name" => "d.short_desc", "display" => "status");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Voucher " . $title;

			if ($acl->CheckUserAccess($controller, "add")) {
				$settings["actions"][] = array("Text" => "Add " . $title, "Url" => "$controller/add?which=master", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess($controller, "view")) {
				$settings["actions"][] = array("Text" => "View " . $title, "Url" => "$controller/view/%s", "Class" => "bt_view", "ReqId" => 1,
											   "Error" => "Anda harus memilih 1 dokumen voucher terlebih dahulu !\nPERHATIAN: Pastikan anda memilih tepat 1 dokumen.",
											   "Confirm" => "");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess($controller, "edit")) {
				$settings["actions"][] = array("Text" => "Edit " . $title, "Url" => "$controller/edit/%s?which=master", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Anda harus memilih 1 dokumen voucher terlebih dahulu !\nPERHATIAN: Pastikan anda memilih tepat 1 dokumen.",
											   "Confirm" => "Apakah anda mau mengedit voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess($controller, "delete")) {
				$settings["actions"][] = array("Text" => "Delete " . $title, "Url" => "$controller/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Anda harus memilih 1 dokumen voucher terlebih dahulu !\nPERHATIAN: Pastikan anda memilih tepat 1 dokumen.",
											   "Confirm" => "Apakah anda mau menghapus voucher yang dipilih ?");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess($controller, "approve")) {
				$settings["actions"][] = array("Text" => "Approve " . $title, "Url" => "$controller/approve/", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
											   "Confirm" => "Apakah anda mau meng-approve semua semua dokumen Voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess($controller, "disapprove")) {
				$settings["actions"][] = array("Text" => "Dis-Approve " . $title, "Url" => "$controller/disapprove/", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen Voucher yang dipilih ?");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess($controller, "verify")) {
				$settings["actions"][] = array("Text" => "Verify " . $title, "Url" => "$controller/verify/", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-verify semua semua dokumen Voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess($controller, "unverify")) {
				$settings["actions"][] = array("Text" => "Un-Verify " . $title, "Url" => "$controller/unverify/", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-UNVERIFY semua semua dokumen Voucher yang dipilih ?");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess($controller, "posting")) {
				$settings["actions"][] = array("Text" => "Posting " . $title, "Url" => "$controller/posting/", "Class" => "bt_approve", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-posting semua semua dokumen Voucher yang dipilih ?");
			}
			if ($acl->CheckUserAccess($controller, "unposting")) {
				$settings["actions"][] = array("Text" => "Un-Posting " . $title, "Url" => "$controller/unposting/", "Class" => "bt_reject", "ReqId" => 2,
					"Error" => "Mohon memilih sekurang-kurangnya satu dokumen Voucher !",
					"Confirm" => "Apakah anda mau meng-UNPOSTING semua semua dokumen Voucher yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = false;

			// Hapus Cache apapun yang terjadi
			$this->persistence->DestroyState("accounting.cashbook.master");
		} else {
			$settings["from"] =
"ac_voucher_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN cm_doctype AS c ON a.doc_type_id = c.id
	JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'voucher_status'
	JOIN (
		SELECT aa.voucher_master_id, SUM(aa.amount) AS total_amount
		FROM ac_voucher_detail AS aa
		GROUP BY aa.voucher_master_id
	) AS e ON a.id = e.voucher_master_id";
			// Special Filter applied

			if ($_GET["query"] == "") {
				$_GET["query"] == null;
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$settings["where"] = "a.is_deleted = 0 AND a.status <> 4";
				} else {
					$settings["where"] = "a.is_deleted = 0 AND a.status <> 4 AND a.entity_id = " . $this->userCompanyId;
				}
			} else {
				if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
					$settings["where"] = "a.is_deleted = 0";
				} else {
					$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
				}
			}
			switch ($this->lockDocId) {
				case 2:
					$settings["where"] .= " AND LEFT(a.doc_no, 3) = 'BK/'";
					break;
				case 3:
					$settings["where"] .= " AND LEFT(a.doc_no, 3) = 'BM/'";
					break;
				default:
					$settings["where"] .= " AND LEFT(a.doc_no, 3) IN ('BK/', 'BM/')";
					break;
			}
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings);
		$dispatcher->SuppressNextSequence();
	}

	protected function add() {
		if ($this->lockDocId == 2) {
			$title = "BKK";
			$controller = "accounting.bkk";
		} else if ($this->lockDocId == 3) {
			$title = "BKM";
			$controller = "accounting.bkm";
		} else {
			$title = "Bukti Kas";
			$controller = "accounting.cashbook";
		}

		$which = $this->GetGetValue("which", "master");
		switch ($which) {
			case "master":
				$this->ProcessMaster($controller);
				break;
			case "detail":
				$this->ProcessDetail($controller);
				break;
			case "confirm":
				$this->ProcessConfirm($controller);
				break;
			default:
				redirect_url("$controller/add?which=master");
				return;
		}

		$this->Set("lockDocId", $this->lockDocId);
		$this->Set("which", $which);
		$this->Set("title", $title);
		$this->Set("controller", $controller);
	}

	/**
	 * Untuk proses data master voucher. Jika ID tidak berupa null maka akan proses edit
	 *
	 * @param string $controller	=> Controller yang digunakan apakah BKK / BKM / Cashbook (Bisa 2 2 nya)
	 * @param null|int $id			=> Id dokumen yang akan diedit jika ada
	 * @return Voucher
	 */
	private function ProcessMaster($controller, $id = null) {
		require_once(MODEL . "master/company.php");

		// Check session
		$sessionValid = false;
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.cashbook.master")) {
			$voucher = $this->persistence->LoadState("accounting.cashbook.master");
			$validType = $this->lockDocId == null || ($voucher->DocumentTypeId == $this->lockDocId);
			if ($voucher->Id != $id || !$validType) {
				$this->persistence->DestroyState("accounting.cashbook.master");
				$voucher = null;
			} else {
				$sessionValid = true;
			}
		} else {
			$voucher = null;
		}

		// Jika data voucher tidak ada maka di init sesuai dengan kondisi ID
		if ($voucher == null) {
			$voucher = new Voucher();
			if ($id == null) {
				$voucher->EntityId = $this->userCompanyId;
				$voucher->Date = time();
				$voucher->VoucherSource = "CASHBOOK";
				$voucher->DocumentNo = "[AUTO]";
			} else {
				$voucher = $voucher->LoadById($id);

				// Kita cek apakah masih boleh edit dokumen atau tidak...
				require_once(MODEL . "common/doc_counter.php");
				$docCounter = new DocCounter();
				$docCounter = $docCounter->LoadByDocType($voucher->DocumentTypeId, $voucher->EntityId, $voucher->Date);

				if ($docCounter == null || $docCounter->IsLocked) {
					if ($docCounter == null) {
						$this->persistence->SaveState("error", "DocCounter not found ! Please contact your system administrator !");
					}
					$this->persistence->SaveState("info", "Dokumen yang diminta sudah tidak dapat di-edit ! Periode Dokumen sudah ter-LOCK !");
					redirect_url("$controller/view/" . $voucher->Id);
				}
			}
		}

		if (count($this->postData) > 0) {
			// Untuk yang datang dari edit mode ada hidden field untuk document type id nya
			// Jika tidak ada maka akan coba ambil dari lock documentnya (jika tidak di lock maka akan selalu ada)
			$voucher->DocumentTypeId = $this->GetPostValue("DocumentType", $this->lockDocId);
			$voucher->DocumentNo = $this->GetPostValue("DocumentNo");
			$voucher->Date = strtotime($this->GetPostValue("Date"));
			$voucher->Note = $this->GetPostValue("Note");
			$voucher->StatusCode = 1;

			if ($this->ValidateMaster($voucher)) {
				if ($id == null) {
					// Ambil no dokumen sementara (Jenis dokumen sudah ditentukan dari user... jadi kita pake yang 'generic' bukan yang sudah pasti :p)
					require_once(MODEL . "common/doc_counter.php");
					$docCounter = new DocCounter();
					$voucher->DocumentNo = $docCounter->AutoDocNo($this->userCompanyId, $voucher->DocumentTypeId, $voucher->Date, 0);
					if ($voucher->DocumentNo != null) {
						$this->persistence->SaveState("accounting.cashbook.master", $voucher);
						redirect_url("$controller/add?which=detail");
					} else {
						$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
						$voucher->DocumentNo = "[LOCKED]";
					}
				} else {
					if (!$sessionValid) {
						$voucher->LoadDetails();
					}

					$this->persistence->SaveState("accounting.cashbook.master", $voucher);
					redirect_url("$controller/edit/" . $id . "?which=detail");
				}
			}
		} else {
			// CORP tidak boleh entry data
			if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
				$this->persistence->SaveState("error", "Maaf Login CORP tidak dapat melakukan entry data.");
				redirect_url($controller);
				return null;
			}
		}

		$company = new Company();
		$company = $company->LoadById($voucher->EntityId);

		$this->Set("company", $company);
		$this->Set("voucher", $voucher);

		return $voucher;
	}

	private function ValidateMaster(Voucher $voucher) {
		if ($voucher->DocumentTypeId == null) {
			$this->Set("error", "Maaf harap memilih jenis Voucher Cash/Bank terlebih dahulu");
			return false;
		}
		if ($voucher->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu !");
			return false;
		}
		if ($voucher->DocumentNo == null) {
			$this->Set("error", "Mohon masukkan nomor Item Issue terlebih dahulu !");
			return false;
		}
		if (!is_int($voucher->Date)) {
			$this->Set("error", "Mohon masukkan tanggal Item Issue terlebih dahulu !");
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

	/**
	 * Proses detail data voucher. Jika ada ID voucher yang dikirim maka akan mengaktifkan mode edit.
	 *
	 * @param string $controller	=> Controller yang digunakan apakah BKK / BKM / Cashbook (Bisa 2 2 nya)
	 * @param null|int $id			=> Jika tidak null maka akan mengaktifkan mode edit
	 *
	 * @throws Exception
	 * @return void
	 */
	private function ProcessDetail($controller, $id = null) {
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "master/project.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "common/trx_type.php");
		require_once(MODEL . "master/bank.php");
        require_once(MODEL . "master/units.php");

		// Cek session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.cashbook.master")) {
			$voucher = $this->persistence->LoadState("accounting.cashbook.master");
			if ($id != null && $voucher->Id != $id) {
				// KOK ada ID tetapi beda dengan ID yang ada di session ? KICK ke master dengan ID yang didapat dari parameter
				redirect_url("$controller/edit/" . $id . "?which=master");
				return;
			}
		} else {
			// Hmm ga ada session ???
			if ($id == null) {
				redirect_url("$controller/add?which=master");
			} else {
				redirect_url("$controller/edit/" . $id . "?which=master");
			}
			return;
		}

		// Untuk mode ADD ditambah hidden field yang nilainya selalu "" untuk ID nya
		// Sedangkan untuk yang mark delete hanya ada di edit mode
		if (count($this->postData) > 0) {
			// Reset detail apapun ang terjadi
			$voucher->Details = array();

			// Proses data yang dikirim
			$ids = $this->GetPostValue("Id", array());
			$deptIds = $this->GetPostValue("Department", array());
			$actIds = $this->GetPostValue("Activity", array());
			$amounts = $this->GetPostValue("Amount", array());
			$debtorIds = $this->GetPostValue("Debtor", array());
			$creditorIds = $this->GetPostValue("Creditor", array());
			$employeeIds = $this->GetPostValue("Employee", array());
			$projectIds = $this->GetPostValue("Project", array());
			$notes = $this->GetPostValue("Note", array());
			$trxTypeIds = $this->GetPostValue("TrxType", array());
			$bankIds = $this->GetPostValue("Bank", array());
            $unitIds = $this->GetPostValue("Unit", array());
			// Reference data yang dihapus
			$markDeletes = $this->GetPostValue("markDelete", array());

			$max = count($ids);
			for ($i = 0; $i < $max; $i++) {
				$detail = new VoucherDetail();

				$detail->Id = $ids[$i];
				$detail->Amount = str_replace(",","", $amounts[$i]);
				$detail->DepartmentId = $deptIds[$i];
				$detail->ActivityId = $actIds[$i];
				$detail->DebtorId = $debtorIds[$i] != "" ? $debtorIds[$i] : null;
				$detail->CreditorId = $creditorIds[$i] != "" ? $creditorIds[$i] : null;
				$detail->EmployeeId = $employeeIds[$i] != "" ? $employeeIds[$i] : null;
				$detail->ProjectId = $projectIds[$i] != "" ? $projectIds[$i] : null;
				$detail->Note = $notes[$i];
				$detail->TrxTypeId = $trxTypeIds[$i];
				$detail->BankId = $bankIds[$i];
                $detail->UnitId = $unitIds[$i];
				$detail->MarkedForDeletion = in_array($detail->Id, $markDeletes);

				$voucher->Details[] = $detail;
			}

			if ($this->ValidateDetail($voucher, count($markDeletes))) {
				$this->persistence->SaveState("accounting.cashbook.master", $voucher);
				if ($id == null) {
					redirect_url("$controller/add?which=confirm");
				} else {
					redirect_url("$controller/edit/" . $id . "?which=confirm");
				}
			}
		}

		$company = new Company();
		$company = $company->FindById($voucher->EntityId);
		$docType = new DocType();
		$docType = $docType->FindById($voucher->DocumentTypeId);
		$transType = new TrxType();
		switch ($voucher->DocumentTypeId) {
			case 2:		// BANK KELUAR
				$debtors = array();
				$creditor = new Creditor();
				$creditors = $creditor->LoadAll("b.entity_cd, a.creditor_name");
				// Ambil semua transaksi BKK (Module AP)
				$transTypes = $transType->LoadByModuleId($this->userCompanyId, 2, "a.description");
				break;
			case 3:		// BANK MASUK
				$debtor = new Debtor();
				$debtors = $debtor->LoadAll("b.entity_cd, a.debtor_cd");
				$creditors = array();
				// Ambil semua transaksi BKM (Module AR)
				$transTypes = $transType->LoadByModuleId($this->userCompanyId, 3, "a.description");
				break;
			default:
				throw new Exception("Unsupported DocumentTypeId !");
		}
		$employee = new Employee();

		$department = new Department();
		$departments = $department->LoadByEntityId($voucher->EntityId);
		/** @var $activity Activity */
		$activity = new Activity();
        $activitys = $activity->LoadByEntityId($voucher->EntityId);

		$details = array();
		foreach ($voucher->Details as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}
		$project = new Project();

		$jsTransTypes = array();
		foreach ($transTypes as $transType) {
			$jsTransTypes[$transType->Id] = array(
				"Debit" => $transType->AccDebitId,
				"Credit" => $transType->AccCreditId,
				"RequireWhich" => $transType->RequireWhich,
				"DebitName" => $transType->GetDebitName(),
				"CreditName" => $transType->GetCreditName(),
				"Description" => $transType->Description
			);
		}
		$bank = new Bank();
        $unit = new Units();
		$this->Set("company", $company);
		$this->Set("docType", $docType);
		$this->Set("departments", $departments);
		$this->Set("activitys", $activitys);
		$this->Set("debtors", $debtors);
		$this->Set("creditors", $creditors);
		$this->Set("employees", $employee->LoadByEntityId($voucher->EntityId));
		$this->Set("projects", $project->LoadAll());
		$this->Set("transTypes", $transTypes);
		if ($this->userCompanyId == 3 || $this->isCorporate) {
			$this->Set("banks", $bank->LoadAll());
		} else {
			$this->Set("banks", $bank->LoadByEntityId($voucher->EntityId));
		}
        $this->Set("units", $unit->LoadAll($voucher->EntityId));
		$this->Set("voucher", $voucher);
		$this->Set("details", $details);
		$this->Set("jsTransTypes", $jsTransTypes);
	}

	private function ValidateDetail(Voucher $voucher, $totalDeleted = 0) {
		if (count($voucher->Details) - $totalDeleted == 0) {
			$this->Set("error", "Maaf anda harus memasukkan detail Voucher Cash/Bank sekurang-kurangnya 1 detail");
			return false;
		}

		$trxType = new TrxType();
		foreach ($voucher->Details as $idx => $detail) {
			if ($detail->TrxTypeId == null) {
				$this->Set("error", "Maaf jenis transaksi no. " . ($idx + 1) . " masih kosong ! Harap memilih jenis transaksi terlebih dahulu");
				return false;
			}
//			if ($detail->ActivityId == null) {
//				$this->Set("error", "Maaf divisi no. " . ($idx + 1) . " masih kosong ! Harap memilih divisi");
//				return false;
//			}
			if ($detail->Amount == 0) {
				$this->Set("error", "Maaf jumlah no. " . ($idx + 1) . " Tidak boleh bernilai nol ! Harap mengisi jumlah terlebih dahulu.");
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
			if ($detail->EmployeeId == "") {
				$detail->EmployeeId = null;
			}
            if ($detail->UnitId == "") {
                $detail->UnitId = null;
            }

			// Sekarang validasi jenis transaksi
			$trxType = $trxType->FindById($detail->TrxTypeId);
			if ($trxType == null) {
				$this->Set("error", "Maaf jenis transaksi no. " . ($idx + 1) . " tidak dikenal ! Mohon ulangi kembali.");
				return false;
			}

			// Untuk validasi trxType tidak boleh kosong untuk field debet dan kredit nya dihandle oleh controller yang bersangkutan
			if ($trxType->AccDebitId == null || $trxType->AccCreditId == null) {
				// Wajib isi BankId
				if ($detail->BankId == null) {
					$this->Set("error", "Maaf transaksi no. " . ($idx + 1) . " mengharuskan anda memilih bank/kas ! Mohon memilihnya terlebih dahulu.");
					return false;
				}

				$bank = new Bank();
				$bank = $bank->LoadById($detail->BankId);
				if ($trxType->AccDebitId == null) {
					// Debit nya kosong bearti ambil dari bank
					$detail->AccDebitId = $bank->AccId;
					$detail->AccCreditId = $trxType->AccCreditId;
				} else {
					// OK Debit tidak kosong tapi block ini masuk jika salah 1 kosong jika 2 2 nya ada isi tidak mungkin masuk blok ini.
					// Jadi dapat dipastikan disini kredit nya kosong
					$detail->AccDebitId = $trxType->AccDebitId;
					$detail->AccCreditId = $bank->AccId;
				}
			} else {
				$detail->AccDebitId = $trxType->AccDebitId;
				$detail->AccCreditId = $trxType->AccCreditId;
				$detail->BankId = null;
			}

			// OK penambahan validasi data
			if ($trxType->IsRequireDebtor() && $detail->DebtorId == null) {
				$this->Set("error", "Maaf transaksi no. " . ($idx + 1) . " mengharuskan anda memilih DEBTOR ! Mohon memilihnya terlebih dahulu.");
				return false;
			}
			if ($trxType->IsRequireCreditor() && $detail->CreditorId == null) {
				$this->Set("error", "Maaf transaksi no. " . ($idx + 1) . " mengharuskan anda memilih CREDITOR ! Mohon memilihnya terlebih dahulu.");
				return false;
			}
			if ($trxType->IsRequireEmployee() && $detail->EmployeeId == null) {
				$this->Set("error", "Maaf transaksi no. " . ($idx + 1) . " mengharuskan anda memilih KARYAWAN ! Mohon memilihnya terlebih dahulu.");
				return false;
			}
			if ($trxType->IsRequireAsset() && $detail->AssetId == null) {
				$this->Set("error", "Maaf transaksi no. " . ($idx + 1) . " mengharuskan anda memilih ASSET ! Mohon memilihnya terlebih dahulu.");
				return false;
			}
		}
		return true;
	}

	/**
	 * Untuk handling proses terakhir sebelum save data (insert / update). Jika id tidak null maka akan invoke update
	 *
	 * @param string $controller	=> Controller yang digunakan apakah BKK / BKM / Cashbook (Bisa 2 2 nya)
	 * @param null|int $id
	 */
	private function ProcessConfirm($controller, $id = null) {
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		require_once(MODEL . "master/department.php");
		require_once(MODEL . "master/activity.php");
		require_once(MODEL . "master/debtor.php");
		require_once(MODEL . "master/creditor.php");
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/project.php");
		require_once(MODEL . "common/trx_type.php");
        require_once(MODEL . "master/units.php");

		// Cek session
		/** @var $voucher Voucher */
		if ($this->persistence->StateExists("accounting.cashbook.master")) {
			$voucher = $this->persistence->LoadState("accounting.cashbook.master");
			if ($voucher->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("$controller/add?which=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("$controller/edit/$id?which=master");
				}
				return;
			}
		} else {
			// Hmm ga ada session ???
			if ($id == null) {
				redirect_url("$controller/add?which=master");
			} else {
				redirect_url("$controller/edit/$id?which=master");
			}
			return;
		}

		$docType = new DocType();
		$docType = $docType->FindById($voucher->DocumentTypeId);

		// Check apakah user ada kirim data atau tidak
		if (count($this->postData) > 0) {

			$this->connector->BeginTransaction();
			if ($id == null) {
				$voucher->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doAdd($voucher);
			} else {
				$voucher->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doEdit($voucher);
			}

			if ($rs) {
				$this->connector->CommitTransaction();

				// YEAHHH success
				if ($id == null) {
					$this->persistence->SaveState("info", sprintf("Voucher %s nomor: %s telah berhasil disimpan.", $docType->DocCode, $voucher->DocumentNo));
				} else {
					$this->persistence->SaveState("info", sprintf("Perubahan data voucher %s nomor: %s telah disimpan.", $docType->DocCode, $voucher->DocumentNo));
				}
				$this->persistence->DestroyState("accounting.cashbook.master");
				redirect_url($controller . "/view/" . $voucher->Id);
			} else {
				$this->connector->RollbackTransaction();
			}
		}

		$company = new Company();
		$company = $company->FindById($voucher->EntityId);
		// Untuk Detail yang lainnya kita dynamic loading saja....
		$accounts = array();
		$departments = array();
		$activitys = array();
        $units = array();
		$debtors = array();
		$creditors = array();
		$employees = array();
		$projects = array();
		$trxTypes = array();
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
            if ($detail->UnitId != null && !array_key_exists($detail->UnitId, $units)) {
                $units[$detail->UnitId] = new Units($detail->UnitId);
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
			if (!array_key_exists($detail->TrxTypeId, $trxTypes)) {
				$trxTypes[$detail->TrxTypeId] = new TrxType($detail->TrxTypeId);
			}
		}

		// Set variable :p
		$this->Set("company", $company);
		$this->Set("docType", $docType);
		$this->Set("accounts", $accounts);
		$this->Set("departments", $departments);
		$this->Set("activitys", $activitys);
        $this->Set("units", $units);
		$this->Set("projects", $projects);
		$this->Set("debtors", $debtors);
		$this->Set("creditors", $creditors);
		$this->Set("employees", $employees);
		$this->Set("trxTypes", $trxTypes);

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

	protected function view($id = null) {
		if ($this->lockDocId == 2) {
			$title = "BKK";
			$controller = "accounting.bkk";
		} else if ($this->lockDocId == 3) {
			$title = "BKM";
			$controller = "accounting.bkm";
		} else {
			$title = "Bukti Kas";
			$controller = "accounting.cashbook";
		}

		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Voucher $title terlebih dahulu !");
			redirect_url($controller);
			return;
		}

		$voucher = new Voucher();
		$voucher = $voucher->LoadById($id);
		if ($voucher == null || $voucher->IsDeleted) {
			$this->persistence->SaveState("error", "Dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
			redirect_url($controller);
			return;
		}
		if ($this->lockDocId != null && $voucher->DocumentTypeId != $this->lockDocId) {
			// Hmm coba akses apa anda ? Dokumen yang tidak boleh ???
			$this->persistence->SaveState("error", "Maaf dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
			redirect_url($controller);
		}
		if (!in_array($voucher->DocumentTypeId, array(2, 3))) {
			// Coba akses yang bukan BM / BK
			$this->persistence->SaveState("error", "Dokumen Voucher $title yang diminta bukan BM / BK !");
			redirect_url($controller);
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($voucher->EntityId != $this->userCompanyId) {
				// WOW coba akses data lintas Company ? Simulate not found !
				$this->persistence->SaveState("error", "Dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
				redirect_url($controller);
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
		require_once(MODEL . "common/trx_type.php");
        require_once(MODEL . "master/units.php");
		// Load details data
		$voucher->LoadDetails();

		$company = new Company();
		$company = $company->FindById($voucher->EntityId);
		$docType = new DocType();
		$docType = $docType->FindById($voucher->DocumentTypeId);
		// Untuk Detail yang lainnya kita dynamic loading saja....
		$accounts = array();
		$departments = array();
		$activitys = array();
		$debtors = array();
		$creditors = array();
		$employees = array();
		$projects = array();
		$trxTypes = array();
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
            if ($detail->UnitId != null && !in_array($detail->UnitId, $units)) {
                $units[$detail->UnitId] = new Units($detail->UnitId);
            }
			if (!array_key_exists($detail->TrxTypeId, $trxTypes)) {
				$trxTypes[$detail->TrxTypeId] = new TrxType($detail->TrxTypeId);
			}
		}

		// Set variable :p
		$this->Set("company", $company);
		$this->Set("docType", $docType);
		$this->Set("accounts", $accounts);
		$this->Set("departments", $departments);
		$this->Set("activitys", $activitys);
		$this->Set("projects", $projects);
        $this->Set("units", $units);
		$this->Set("debtors", $debtors);
		$this->Set("creditors", $creditors);
		$this->Set("employees", $employees);
		$this->Set("trxTypes", $trxTypes);

		$this->Set("voucher", $voucher);
		$this->Set("previous", $voucher->SeekPreviousVoucher());
		$this->Set("next", $voucher->SeekNextVoucher());
		$this->Set("controller", $controller);
		$this->Set("title", $title);

		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	protected function edit($id = null) {
		if ($this->lockDocId == 2) {
			$title = "BKK";
			$controller = "accounting.bkk";
		} else if ($this->lockDocId == 3) {
			$title = "BKM";
			$controller = "accounting.bkm";
		} else {
			$title = "Bukti Kas";
			$controller = "accounting.cashbook";
		}

		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih Voucher $title terlebih dahulu !");
			redirect_url($controller);
			return;
		}

		$which = $this->GetGetValue("which", "master");
		switch ($which) {
			case "master":
				$voucher = $this->ProcessMaster($controller, $id);
				if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
					if ($voucher->EntityId != $this->userCompanyId) {
						// Ops.. different Company try to access ! Simulate not found !
						$this->persistence->SaveState("error", "Maaf dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
						redirect_url($controller);
					}
				}
				if ($voucher == null || $voucher->IsDeleted) {
					$this->persistence->SaveState("error", "Maaf dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
					redirect_url($controller);
				}
				if ($this->lockDocId != null && $voucher->DocumentTypeId != $this->lockDocId) {
					// Hmm coba akses apa anda ? Dokumen yang tidak boleh ???
					$this->persistence->SaveState("error", "Maaf dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
					redirect_url($controller);
				}
//				if ($voucher->VoucherSource != "CASHBOOK" && $voucher->VoucherSource != "CASHBOOK-PORTED") {
//					// Wah ini dia coba edit yang bukan di entry dari module CASHBOOK
//					$this->persistence->SaveState("error", "Dokumen Voucher $title yang diminta tidak dientry melalui module CASHBOOK. Harap edit menggunakan module: " . $voucher->VoucherSource);
//					redirect_url($controller);
//					return;
//				}
				if (!$voucher->IsVoucherEditable()) {
					$this->persistence->SaveState("info", "Maaf jenis dokumen Voucher $title yang diminta tidak boleh diedit secara manual karena hasil posting modul yang lain.");
					redirect_url("$controller/view/" . $voucher->Id);
				}
				if ($voucher->StatusCode > 1) {
					$this->persistence->SaveState("info", "Maaf status dokumen Voucher $title yang diminta sudah bukan DRAFT ! Editing hanya untuk dokumen status DRAFT.");
					redirect_url("$controller/view/" . $voucher->Id);
				}
				if ($this->forcePeriode) {
					if ($voucher->FormatDate("Ym") != ($this->accYear * 100) + $this->accMonth) {
						$this->persistence->SaveState("error", sprintf("Maaf anda tidak dapat mengedit Voucher dengan tanggal: %s karena Periode Akun yang diset adalah: %s %s", $voucher->FormatDate(), $this->accMonthName, $this->accYear));
						redirect_url("$controller/view/" . $voucher->Id);
					}
				}
				break;
			case "detail":
				$this->ProcessDetail($controller, $id);
				break;
			case "confirm":
				$this->ProcessConfirm($controller, $id);
				break;
			default:
				redirect_url("$controller/edit/" . $id . "?which=master");
				return;
		}

		$this->Set("lockDocId", $this->lockDocId);
		$this->Set("which", $which);
		$this->Set("title", $title);
		$this->Set("controller", $controller);
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
				if ($detail->Id == null) {
					$detail->Sequence = $counter;
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

	protected function delete($id = null) {
		if ($this->lockDocId == 2) {
			$title = "BKK";
			$controller = "accounting.bkk";
		} else if ($this->lockDocId == 3) {
			$title = "BKM";
			$controller = "accounting.bkm";
		} else {
			$title = "Bukti Kas";
			$controller = "accounting.cashbook";
		}


		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Voucher $title terlebih dahulu.");
			redirect_url($controller);
			return;
		}

		$voucher = new Voucher();
		$voucher = $voucher->LoadById($id);
		if ($voucher == null || $voucher->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf Dokumen Voucher $title yang diminta tidak ditemukan / sudah dihapus.");
			redirect_url($controller);
			return;
		}
		if ($this->lockDocId != null && $voucher->DocumentTypeId != $this->lockDocId) {
			// Hmm coba akses apa anda ? Dokumen yang tidak boleh ???
			$this->persistence->SaveState("error", "Maaf dokumen Voucher $title yang diminta tidak dapat ditemukan / sudah dihapus !");
			redirect_url($controller);
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			// OK Checking Company
			if ($voucher->EntityId != $this->userCompanyId) {
				// OK KICK ! Simulate not Found !
				$this->persistence->SaveState("error", "Maaf Dokumen Voucher $title yang diminta tidak ditemukan / sudah dihapus.");
				redirect_url($controller);
				return;
			}
		}
		if (!in_array($voucher->DocumentTypeId, array(2, 3))) {
			// Coba akses yang bukan BM / BK
			$this->persistence->SaveState("error", "Dokumen Voucher $title yang diminta bukan BM / BK !");
			redirect_url($controller);
			return;
		}
		if ($voucher->VoucherSource != "CASHBOOK") {
			// Wah ini dia coba edit yang bukan di entry dari module CASHBOOK
			$this->persistence->SaveState("error", "Dokumen Voucher $title yang diminta tidak dientry melalui module CASHBOOK. Harap edit menggunakan module: " . $voucher->VoucherSource);
			redirect_url($controller);
			return;
		}
		if (!$voucher->IsVoucherEditable()) {
			$this->persistence->SaveState("info", "Maaf jenis dokumen Voucher $title yang diminta tidak boleh dihapus secara manual karena hasil posting modul yang lain.");
			redirect_url("$controller/view/" . $voucher->Id);
			return;
		}
		if ($voucher->StatusCode > 1) {
			$this->persistence->SaveState("error", "Maaf Dokumen Voucher $title yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
			redirect_url("$controller/view/" . $voucher->Id);
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
			redirect_url("$controller/view/" . $voucher->Id);
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

		redirect_url($controller);
	}

	public function approve() {
		$ids = $this->GetGetValue("id", array());

		$this->ProcessDocument($ids, "APPROVE", 1, "ApprovedById", "Approve");
	}

	public function disapprove() {
		$ids = $this->GetGetValue("id", array());

		$this->ProcessDocument($ids, "DIS-APPROVE", 2, "UpdatedById", "DisApprove");
	}

	// Aduh... ini gw angkat tangan klo ga curang... bikinnya bisa teler... harus ada verify, unverify, posting, unposting 2x masing-masing...
	/**
	 * Tehnik curang untuk proses dokumen voucher (approve, dis-approve, verify, un-verify, posting, un-posting)
	 * Berhubung prosedurnya mirip hanya beda di syarat, field, method maka kita bikin generic methodnya yang mana syarat dll dipassing via parameter
	 *
	 * @param int[] $ids => array ID dokumen yang akan diproses
	 * @param $processName => Human readable process name
	 * @param $requiredStatusCode => Status dokumen yang diperlukan untuk proses yang bersangkutan. Status code ini sama dengan status yang ada pada voucher
	 * @param $fieldName => nama field untuk user update
	 * @param $methodName => nama method yang akan dipanggil untuk proses yang diinginkan
	 */
	protected function ProcessDocument(array $ids, $processName, $requiredStatusCode, $fieldName, $methodName) {
		if ($this->lockDocId == 2) {
			$title = "BKK";
			$controller = "accounting.bkk";
		} else if ($this->lockDocId == 3) {
			$title = "BKM";
			$controller = "accounting.bkm";
		} else {
			$title = "Bukti Kas";
			$controller = "accounting.cashbook";
		}

		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen Voucher $title yang akan di " . $processName);
			redirect_url($controller);
			return;
		}

		require_once(MODEL . "common/doc_counter.php");
		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;

		foreach ($ids as $id) {
			$voucher = new Voucher();
			$voucher = $voucher->LoadById($id);

			if ($this->lockDocId != null && $voucher->DocumentTypeId != $this->lockDocId) {
				// Hmm coba akses apa anda ? Dokumen yang tidak boleh ???
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($voucher->EntityId != $this->userCompanyId) {
					// Trying to access other Company data ! Bypass it..
					continue;
				}
			}
			if (!$voucher->IsVoucherEditable()) {
				$errors[] = sprintf("Dokumen Voucher $title: %s tidak diproses karena hasil posting modul lain", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->StatusCode != $requiredStatusCode) {
				$errors[] = sprintf("Gagal %s Voucher $title: %s karena status sudah bukan %s ! Status Dokumen: %s", $processName, $voucher->DocumentNo, Voucher::$StatusCodes[$requiredStatusCode], $voucher->GetStatus());
				continue;
			}
			if (!in_array($voucher->DocumentTypeId, array(2, 3))) {
				// Coba akses yang bukan BM / BK
				$errors[] = sprintf("Voucher $title: %s bukan BM / BK !", $voucher->DocumentNo);
				continue;
			}
			if ($voucher->VoucherSource != "CASHBOOK") {
				// Wah ini dia coba edit yang bukan di entry dari module CASHBOOK
				$errors[] = sprintf("Voucher $title: %s tidak dientry melalui module CASHBOOK.", $voucher->DocumentNo);
				continue;
			}
			// Kita cek apakah masih boleh edit dokumen atau tidak...
			$docCounter = new DocCounter();
			$docCounter = $docCounter->LoadByDocType($voucher->DocumentTypeId, $voucher->EntityId, $voucher->Date);
			if ($docCounter == null) {
				$errors[] = sprintf("Voucher $title: %s tidak diproses karena tidak ditemukan DocCounter-nya.", $voucher->DocumentNo);
				continue;
			}
			if ($docCounter->IsLocked) {
				$errors[] = sprintf("Voucher $title: %s tidak diproses karena sudah ter-LOCK !.", $voucher->DocumentNo);
				continue;
			}

			// OK semua tahapan validasi lolos...
			$this->connector->BeginTransaction();

			$voucher->$fieldName = $userId;
			$rs = $voucher->$methodName($voucher->Id);
			if ($rs == 1) {
				// Sukses...
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen Voucher $title: %s sudah berhasil di %s", $voucher->DocumentNo, $processName);
			} else {
				// Gagal...
				$errors[] = sprintf("Gagal %s Voucher: %s. Message: %s", $processName, $voucher->DocumentNo, $this->connector->GetErrorMessage());
				$this->connector->RollbackTransaction();
			}
		}

		// Ok keluar dari loop foreach... redirect ke halaman index
		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}
		redirect_url($controller);
	}

	public function verify() {
		$ids = $this->GetGetValue("id", array());

		$this->ProcessDocument($ids, "VERIFY", 2, "VerifiedById", "Verify");
	}

	public function unverify() {
		$ids = $this->GetGetValue("id", array());

		$this->ProcessDocument($ids, "UN-VERIFY", 3, "UpdatedById", "UnVerify");
	}

	public function posting() {
		$ids = $this->GetGetValue("id", array());

		$this->ProcessDocument($ids, "POSTING", 3, "PostedById", "Post");
	}

	public function unposting() {
		$ids = $this->GetGetValue("id", array());

		$this->ProcessDocument($ids, "UN-POSTING", 4, "UpdatedById", "UnPost");
	}
}

// End of File: cashbook_controller.php
