<?php

class CashRequestController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userProjectId;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "accounting/cash_request.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
        $this->userProjectId = $this->persistence->LoadState("project_id");
	}

	public function index() {
		$router = Router::GetInstance()->GetRouteData();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		//$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
        $settings["columns"][] = array("name" => "d.name", "display" => "Project", "width" => 80);
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "NPKP No.", "width" => 120);
		$settings["columns"][] = array("name" => "a.objective", "display" => "NPKP Purpose", "width" => 100);
        $settings["columns"][] = array("name" => "a.note", "display" => "Description", "width" => 180);
		$settings["columns"][] = array("name" => "FORMAT(e.amount, 2)", "display" => "Amount Request", "width" => 100, "align" => "right");
		$settings["columns"][] = array("name" => "FORMAT(f.funded, 2)", "display" => "Amount Approved", "width" => 100, "align" => "right");
		$settings["columns"][] = array("name" => "date_format(a.cash_request_date, '%d-%m-%Y')", "display" => "NPKP Date", "width" => 80, "overrideSort" => "a.cash_request_date");
		$settings["columns"][] = array("name" => "date_format(a.eta_date, '%d-%m-%Y')", "display" => "Request Date", "width" => 90, "overrideSort" => "a.eta_date");
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status", "width" => 80);
		$settings["columns"][] = array("name" => "date_format(a.update_time, '%d-%m-%Y')", "display" => "Last Upate", "width" => 80, "overrideSort" => "a.update_time");
		$settings["columns"][] = array("name" => "date_format(a.approve_time, '%d-%m-%Y')", "display" => "Verify Date", "width" => 80, "overrideSort" => "a.approve_time");
		$settings["columns"][] = array("name" => "date_format(a.verified_time, '%d-%m-%Y')", "display" => "Approved Date", "width" => 80, "overrideSort" => "a.verified_time");
		$settings["columns"][] = array("name" => "date_format(a.approve2_time, '%d-%m-%Y')", "display" => "Lvl 2 App Date", "width" => 80, "overrideSort" => "a.approve2_time");

		$settings["filters"][] = array("name" => "a.doc_no", "display" => "No NPKP");
		$settings["filters"][] = array("name" => "a.objective", "display" => "NPKP Purpose");
        $settings["filters"][] = array("name" => "a.note", "display" => "Description");
		$settings["filters"][] = array("name" => "c.short_desc", "display" => "Status");
		$settings["filters"][] = array("name" => "date_format(a.eta_date, '%d-%m-%Y')", "display" => "Request Date");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Cash Request (NPKP)";

			if($acl->CheckUserAccess("accounting.cashrequest", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "accounting.cashrequest/add?step=master", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("accounting.cashrequest", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "accounting.cashrequest/view/%s", "Class" => "bt_view", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen NPKP terlebih dahulu sebelum melihat data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
            if ($acl->CheckUserAccess("accounting.cashrequest", "doc_print")) {
                $settings["actions"][] = array("Text" => "Print", "Url" => "accounting.cashrequest/doc_print/pdf", "Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "");
            }
			if ($acl->CheckUserAccess("accounting.cashrequest", "doc_print")) {
				$settings["actions"][] = array("Text" => "Print", "Url" => "accounting.cashrequest/doc_print/xls", "Class" => "bt_excel", "ReqId" => 2, "Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if($acl->CheckUserAccess("accounting.cashrequest", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "accounting.cashrequest/edit/%s?step=master", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen NPKP terlebih dahulu sebelum mengupdate data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda mau melakukan proses update data NPKP ?");
			}
			if($acl->CheckUserAccess("accounting.cashrequest", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "accounting.cashrequest/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih dokumen NPKP terlebih dahulu sebelum menghapus data.\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakan anda yakin mau menghapus dokumen NPKP yang dipilih ?\nKlik OK untuk menghapus data yang dipilih.");
			}
			// Button approval
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.cashrequest", "approve")) {
				$settings["actions"][] = array("Text" => "Verify", "Url" => "accounting.cashrequest/approve", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen NPKP !",
											   "Confirm" => "Apakah anda mau meng-VERIFY semua semua dokumen NPKP yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.cashrequest", "dis_approve")) {
				$settings["actions"][] = array("Text" => "Dis-Verify", "Url" => "accounting.cashrequest/dis_approve", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen NPKP !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen NPKP yang dipilih ?");
			}
			// Button verify
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.cashrequest", "verify")) {
				$settings["actions"][] = array("Text" => "Approve", "Url" => "accounting.cashrequest/verify", "Class" => "bt_verify", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen NPKP !",
											   "Confirm" => "Apakah anda mau meng-APPROVE semua semua dokumen NPKP yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.cashrequest", "dis_verify")) {
				$settings["actions"][] = array("Text" => "Dis-Approve", "Url" => "accounting.cashrequest/dis_verify", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen NPKP !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen NPKP yang dipilih ?");
			}
			// Button approval lv 2
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("accounting.cashrequest", "post")) {
				$settings["actions"][] = array("Text" => "Approve Lv 2", "Url" => "accounting.cashrequest/post", "Class" => "bt_approve", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen NPKP !",
											   "Confirm" => "Apakah anda mau meng-APPROVE LV2 semua semua dokumen NPKP yang dipilih ?");
			}
			if ($acl->CheckUserAccess("accounting.cashrequest", "un_post")) {
				$settings["actions"][] = array("Text" => "Dis-Approve Lv 2", "Url" => "accounting.cashrequest/un_post", "Class" => "bt_reject", "ReqId" => 2,
											   "Error" => "Mohon memilih sekurang-kurangnya satu dokumen NPKP !",
											   "Confirm" => "Apakah anda mau mem-BATAL-kan semua semua dokumen NPKP yang dipilih ?");
			}
            if($acl->CheckUserAccess("accounting.cashrequest", "view")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "accounting.cashrequest/overview", "Class" => "bt_report", "ReqId" => 0);
            }

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;

			// Hapus Cache apapun yang terjadi
			$this->persistence->DestroyState("accounting.cashrequest.master");
		} else {
			$settings["from"] =
"ac_cash_request_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'npkp_status'
	JOIN ac_cash_request_category AS d ON a.category_id = d.id
	JOIN (
		SELECT aa.cash_request_master_id, SUM(aa.amount) AS amount
		FROM ac_cash_request_detail AS aa
		GROUP BY aa.cash_request_master_id
	) AS e ON a.id = e.cash_request_master_id
	LEFT JOIN (
		SELECT aa.npkp_id, SUM(aa.amount) AS funded
		FROM ac_npkp_funding AS aa Where aa.is_deleted = 0
		GROUP BY aa.npkp_id
	) AS f ON a.id = f.npkp_id";

			//filtering
            if ($this->userLevel < 5){
                $settings["where"] = "a.is_deleted = 0 And Locate(d.project_id,".$this->userProjectIds.")";
            }else {
                $settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "accounting/cash_request_category.php");
		$step = strtolower($this->GetGetValue("step"));
		// Jangan pakai tehnik pass by reference

		switch ($step) {
			case "master":
				$this->ProcessMaster();
				break;
			case "detail":
				$this->ProcessDetail();
				break;
			case "confirm":
				$this->ProcessConfirm();
				break;
			default:
				redirect_url("accounting.cashrequest/add?step=master");
				return;
		}

		require_once(MODEL . "master/company.php");
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("step", $step);
		$this->Set("company", $company);
	}

	private function ProcessMaster($id = null) {
		$sessionValid = false;
		if ($this->persistence->StateExists("accounting.cashrequest.master")) {
			$cashRequest = $this->persistence->LoadState("accounting.cashrequest.master");
			if ($cashRequest->Id != $id) {
				$this->persistence->DestroyState("accounting.cashrequest.master");
				// Reset semua dari awal karena session invalid
				if ($id == null) {
					redirect_url("accounting.cashrequest/add?step=master");
				} else {
					redirect_url("accounting.cashrequest/edit/$id?step=master");
				}
			} else {
				$sessionValid = true;
			}
		} else {
			$cashRequest = new CashRequest();

			if ($id == null) {
				// Mode add
				$cashRequest->EntityId = $this->userCompanyId;
				$cashRequest->Date = time();
				$cashRequest->EtaDate = $cashRequest->Date + 604800;	// Margin 1 minggu
				$cashRequest->DocumentNo = "[AUTO]";
			} else {
				$cashRequest = $cashRequest->LoadById($id);
				if ($cashRequest == null || $cashRequest->IsDeleted) {
					$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
					redirect_url("accounting.cashrequest");
					return;
				}
				if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
					if ($cashRequest->EntityId != $this->userCompanyId) {
						// Access data beda Company ! Simulate not Found !
						$this->persistence->SaveState("error", "Mohon memilih dokumen cash request (NPKP) terlebih dahulu.");
						redirect_url("accounting.cashrequest");
						return;
					}
				}
				if ($cashRequest->StatusCode > 1) {
					$this->persistence->SaveState("error", "Maaf dokumen yang diminta sudah tidak bersifat DRAFT !");
					redirect_url("accounting.cashrequest/view/" . $cashRequest->Id);
					return;
				}
			}
		}

		if (count($this->postData) > 0) {
			// Proses data yang dikirim

			$cashRequest->EntityId = $this->userCompanyId;
			$cashRequest->DocumentNo = $this->GetPostValue("DocumentNo");
			$cashRequest->CategoryId = $this->GetPostValue("CategoryId");
			$cashRequest->Date = strtotime($this->GetPostValue("Date"));
			$cashRequest->Objective = $this->GetPostValue("Objective");
			$cashRequest->Note = $this->GetPostValue("Note");
			$cashRequest->EtaDate = strtotime($this->GetPostValue("Eta"));
			$cashRequest->StatusCode = 1;

			if ($this->ValidateMaster($cashRequest)) {
				if ($id == null) {
					require_once(MODEL . "common/doc_counter.php");
					$docCounter = new DocCounter();
					$cashRequest->DocumentNo = $docCounter->AutoDocNoNpkp($this->userCompanyId, $cashRequest->Date, 0);
					if ($cashRequest->DocumentNo != null) {
						$this->persistence->SaveState("accounting.cashrequest.master", $cashRequest);
						redirect_url("accounting.cashrequest/add?step=detail");
					} else {
						$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
						$cashRequest->DocumentNo = "[LOCKED]";
					}
				} else {
					if (!$sessionValid) {
						$cashRequest->LoadDetails();
					}

					$this->persistence->SaveState("accounting.cashrequest.master", $cashRequest);
					redirect_url("accounting.cashrequest/edit/$id?step=detail");
				}
			}
		}

		$category = new CashRequestCategory();
		if ($this->userLevel < 5) {
            $categories = $category->LoadByAllowedProject($this->userProjectIds);
        }else{
            $categories = $category->LoadByEntity($this->userCompanyId);
        }
		$this->Set("categories", $categories);
		$this->Set("cashRequest", $cashRequest);
	}

	private function ValidateMaster(CashRequest $cashRequest) {
		if ($cashRequest->EntityId == null) {
			$this->Set("error", "Mohon memilih Company terlebih dahulu");
			return false;
		}
		if ($cashRequest->DocumentNo == null) {
			$this->Set("error", "Mohon memasukkan no dokumen terlebih dahulu");
			return false;
		}
		if ($cashRequest->CategoryId == null) {
			$this->Set("error", "Mohon memasukkan kategori NPKP terlebih dahulu");
			return false;
		}
		if (!is_int($cashRequest->Date)) {
			$this->Set("error", "Mohon masukkan tanggal NPKP terlebih dahulu");
			return false;
		}
		if ($cashRequest->Objective == null) {
			$this->Set("error", "Mohon memasukkan tujuan NPKP terlebih dahulu");
			return false;
		}
		if (!is_int($cashRequest->EtaDate)) {
			$this->Set("error", "Mohon masukkan tanggal prakiraan pencairan dana terlebih dahulu");
			return false;
		}

		return true;
	}

	private function ProcessDetail($id = null) {
		require_once(MODEL . "master/coa.php");

		if ($this->persistence->StateExists("accounting.cashrequest.master")) {
			$cashRequest = $this->persistence->LoadState("accounting.cashrequest.master");
			if ($cashRequest->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("accounting.cashrequest/add?step=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("accounting.cashrequest/edit/$id?step=master");
				}
				return;
			}
		} else {
			// Tidak ada session ???
			if ($id == null) {
				redirect_url("accounting.cashrequest/add?step=master");
			} else {
				redirect_url("accounting.cashrequest/edit/$id?step=master");
			}
			return;
		}

		// Untuk mode ADD ditambah hidden field yang nilainya selalu "" untuk ID nya
		// Sedangkan untuk yang mark delete hanya ada di edit mode
		if (count($this->postData) > 0) {
			// Reset detail apapun ang terjadi
			$cashRequest->Details = array();

			// Proses data yang dikirim
			$ids = $this->GetPostValue("Id", array());
			$accountIds = $this->GetPostValue("Account", array());
			$notes = $this->GetPostValue("Note", array());
			$amounts = $this->GetPostValue("Amount", array());
			// Reference data yang dihapus
			$markDeletes = $this->GetPostValue("markDelete", array());

			$max = count($accountIds);
			for ($i = 0; $i < $max; $i++) {
				$detail = new CashRequestDetail();

				$detail->Id = $ids[$i];
				$detail->AccountId = $accountIds[$i] != "" ? $accountIds[$i] : null;
				$detail->Note = $notes[$i];
				$detail->Amount = str_replace(",","", $amounts[$i]);
				$detail->MarkedForDeletion = in_array($detail->Id, $markDeletes);

				$cashRequest->Details[] = $detail;
			}

			if ($this->ValidateDetail($cashRequest, count($markDeletes))) {
				$this->persistence->SaveState("accounting.cashrequest.master", $cashRequest);
				if ($id == null) {
					redirect_url("accounting.cashrequest/add?step=confirm");
				} else {
					redirect_url("accounting.cashrequest/edit/$id?step=confirm");
				}
			}
		}

		$category = new CashRequestCategory();
		$category = $category->LoadById($cashRequest->CategoryId);

		$coa = new Coa();
		$accounts = $coa->LoadByLevel($this->userCompanyId,3);
		$details = array();
		foreach ($cashRequest->Details as $detail) {
			$details[] = $detail->ToJsonFriendly();
		}

		$this->Set("accounts", $accounts);
		$this->Set("cashRequest", $cashRequest);
		$this->Set("category", $category);
		$this->Set("details", $details);
	}

	public function ValidateDetail(CashRequest $cashRequest) {
		if (count($cashRequest->Details) == 0) {
			$this->Set("error", "Maaf anda harus memasukkan detail NPKP sekurang-kurangnya 1 detail.");
			return false;
		}
		$cashRequest->StatusCode = 1;	// Anggap status draft

		foreach ($cashRequest->Details as $idx => $detail) {
//			if ($detail->AccountId == null) {
//				$cashRequest->StatusCode = 0;	// Bikin jadi incomplete
//			}
			if ($detail->Note == null) {
				$this->Set("error", "Maaf anda harus memasukkan detail NPKP no. " . ($idx + 1));
				return false;
			}
			if ($detail->Amount <= 0) {
				$this->Set("error", "Maaf jumlah biaya harus > 0. Mohon periksa detail NPKP no. " . ($idx + 1));
				return false;
			}
		}

		return true;
	}

	private function ProcessConfirm($id = null) {
		require_once(MODEL . "master/coa.php");

		if ($this->persistence->StateExists("accounting.cashrequest.master")) {
			$cashRequest = $this->persistence->LoadState("accounting.cashrequest.master");
			if ($cashRequest->Id != $id) {
				if ($id == null) {
					// Dari edit datang ke add ?
					redirect_url("accounting.cashrequest/add?step=master");
				} else {
					// Datang dari add atau tulis ID manual ?
					redirect_url("accounting.cashrequest/edit/$id?step=master");
				}
				return;
			}
		} else {
			// Tidak ada session ???
			if ($id == null) {
				redirect_url("accounting.cashrequest/add?step=master");
			} else {
				redirect_url("accounting.cashrequest/edit/$id?step=master");
			}
			return;
		}

		if (count($this->postData) > 0) {
			$this->connector->BeginTransaction();
			if ($id == null) {
				$cashRequest->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doAdd($cashRequest);
			} else {
				$cashRequest->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $this->doEdit($cashRequest);
			}

			if ($rs) {
				// Sukses
				$this->connector->CommitTransaction();

				if ($id == null) {
					$this->persistence->SaveState("info", sprintf("Dokumen NPKP: %s telah berhasil disimpan", $cashRequest->DocumentNo));
				} else {
					$this->persistence->SaveState("info", sprintf("Perubahan data Dokumen NPKP: %s telah disimpan", $cashRequest->DocumentNo));
				}

				$this->persistence->DestroyState("accounting.cashrequest.master");
				redirect_url("accounting.cashrequest/index");
			} else {
				// Swt gagal...
				$this->connector->RollbackTransaction();
			}
		}

		// Untuk Detail yang lainnya kita dynamic loading saja....
		$category = new CashRequestCategory();
		$category = $category->LoadById($cashRequest->CategoryId);

		$accounts = array();
		foreach ($cashRequest->Details as $detail) {
			if (!array_key_exists($detail->AccountId, $accounts)) {
				$account = new Coa();
				$account = $account->FindById($detail->AccountId);
				$accounts[$detail->AccountId] = $account;
			}
		}

		$this->Set("accounts", $accounts);
		$this->Set("category", $category);
		$this->Set("cashRequest", $cashRequest);
	}

	private function doAdd(CashRequest $cashRequest) {
		require_once(MODEL . "common/doc_counter.php");
		$docCounter = new DocCounter();
		$cashRequest->DocumentNo = $docCounter->AutoDocNoNpkp($cashRequest->EntityId, $cashRequest->Date, 1);
		if ($cashRequest->DocumentNo == null) {
			$this->Set("error", "Maaf anda tidak dapat membuat dokumen pada tanggal yang diminta ! Tanggal Dokumen sudah ter-lock oleh system");
			return false;
		}

		$rs = $cashRequest->Insert();
		if ($rs != 1) {
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
			}
			return false;
		}

		$counter = 0;
		foreach ($cashRequest->Details as $detail) {
			$counter++;
			$detail->CashRequestId = $cashRequest->Id;
			$rs = $detail->Insert();

			if ($rs != 1) {
				// Ada DBase error karena kita tidak enforce unique key untuk detailnya maka lihat error messagenya
				$this->Set("error", "Maaf error saat simpan detail No. $counter. Message: " . $this->connector->GetErrorMessage());
				return false;
			}
		}

		return true;
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen cash request (NPKP) terlebih dahulu.");
			redirect_url("accounting.cashrequest");
			return;
		}

		$cashRequest = new CashRequest();
		$cashRequest = $cashRequest->LoadById($id);
		if ($cashRequest == null || $cashRequest->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("accounting.cashrequest");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($cashRequest->EntityId != $this->userCompanyId) {
				// Access data beda Company ! Simulate not Found !
				$this->persistence->SaveState("error", "Mohon memilih dokumen cash request (NPKP) terlebih dahulu.");
				redirect_url("accounting.cashrequest");
				return;
			}
		}

		require_once(MODEL . "accounting/cash_request_category.php");
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coa.php");
		$cashRequest->LoadDetails();

		$category = new CashRequestCategory();
		$category = $category->LoadById($cashRequest->CategoryId);
		$company = new Company();
		$company = $company->LoadById($cashRequest->EntityId);
		$accounts = array();
		foreach ($cashRequest->Details as $detail) {
			if (!array_key_exists($detail->AccountId, $accounts)) {
				$account = new Coa();
				$account = $account->FindById($detail->AccountId);
				$accounts[$detail->AccountId] = $account;
			}
		}

		$this->Set("company", $company);
		$this->Set("accounts", $accounts);
		$this->Set("category", $category);
		$this->Set("cashRequest", $cashRequest);

		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen cash request (NPKP) terlebih dahulu.");
			redirect_url("accounting.cashrequest");
			return;
		}

		require_once(MODEL . "accounting/cash_request_category.php");
		$step = strtolower($this->GetGetValue("step"));
		// Jangan pakai tehnik pass by reference

		switch ($step) {
			case "master":
				$this->ProcessMaster($id);
				break;
			case "detail":
				$this->ProcessDetail($id);
				break;
			case "confirm":
				$this->ProcessConfirm($id);
				break;
			default:
				redirect_url("accounting.cashrequest/edit/$id?step=master");
				return;
		}

		require_once(MODEL . "master/company.php");
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);

		$this->Set("step", $step);
		$this->Set("company", $company);
	}

	private function doEdit(CashRequest $cashRequest) {
		$rs = $cashRequest->Update($cashRequest->Id);
		if ($rs != 1) {
			$errMsg = $this->connector->GetErrorMessage();
			if ($this->connector->IsDuplicateError()) {
				$this->Set("error", "Maaf No. Dokumen Cash Request (NPKP) sudah ada pada database.");
			} else {
				$this->Set("error", "Maaf error saat merubah master Cash Request (NPKP). Message: " . $errMsg);
			}
			return false;
		}

		$counter = 0;
		foreach ($cashRequest->Details as $detail) {
			// OK Cek untuk penghapusan data dulu
			if ($detail->MarkedForDeletion) {
				$rs = $detail->Delete($detail->Id);
				if ($rs == -1) {
					$this->Set("error", "Gagal hapus detail dengan ID: " . $detail->Id . ". Mohon hubungi system admin.");
					return false;
				}
			} else {
				$counter++;
				$detail->CashRequestId = $cashRequest->Id;
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

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih dokumen cash request (NPKP) terlebih dahulu sebelum proses penghapusan.");
			redirect_url("accounting.cashrequest");
			return;
		}

		$cashRequest = new CashRequest();
		$cashRequest = $cashRequest->LoadById($id);
		if ($cashRequest == null || $cashRequest->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf dokumen yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("accounting.cashrequest");
			return;
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($cashRequest->EntityId != $this->userCompanyId) {
				// Access data beda Company ! Simulate not Found !
				$this->persistence->SaveState("error", "Mohon memilih dokumen cash request (NPKP) terlebih dahulu.");
				return;
			}
		}
		if ($cashRequest->StatusCode > 1) {
			$this->persistence->SaveState("error", sprintf("Maaf dokumen cash request (NPKP) %s sudah bukan berstatus DRAFT.", $cashRequest->DocumentNo));
			redirect_url("accounting.cashrequest/view/" . $cashRequest->Id);
			return;
		}

		$cashRequest->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;
		if ($cashRequest->Void($cashRequest->Id)) {
			$this->persistence->SaveState("info", sprintf("Dokumen cash request (NPKP) %s telah dihapus dari system.", $cashRequest->DocumentNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus dokumen NPKP %s. Message: %s", $cashRequest->DocumentNo, $this->connector->GetErrorMessage()));
		}

		redirect_url("accounting.cashrequest");
	}

	private function ProcessDocuments(array $ids, $processName, $requiredStatusCode, $requiredStatus, $fieldName, $methodName) {
		if (count($ids) == 0) {
			$this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen cash request (NPKP) yang akan di " . $processName);
			redirect_url("accounting.cashrequest");
			return;
		}

		$infos = array();
		$errors = array();
		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($ids as $id) {
			$cashRequest = new CashRequest();
			$cashRequest = $cashRequest->LoadById($id);

			if ($cashRequest == null || $cashRequest->IsDeleted) {
				continue;
			}
			if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
				if ($cashRequest->EntityId != $this->userCompanyId) {
					// Access data beda Company ! Simulate not Found !
					continue;
				}
			}
			if ($cashRequest->StatusCode != $requiredStatusCode) {
				$errors[] = sprintf("Maaf dokumen NPKP %s sudah bukan berstatus %s. Status dokumen: %s", $cashRequest->DocumentNo, $requiredStatus, $cashRequest->GetStatus());
				continue;
			}

			$this->connector->BeginTransaction();
			$cashRequest->$fieldName = $userId;
			$rs = $cashRequest->$methodName($cashRequest->Id);
			if ($rs) {
				$this->connector->CommitTransaction();
				$infos[] = sprintf("Dokumen NPKP %s telah berhasil di %s", $cashRequest->DocumentNo, $processName);
			} else {
				$msg = $this->connector->GetErrorMessage();
				if ($msg == null && $methodName == "Funds") {
					// Tidak error ? Kemungkinan ga cukup dana kalau ini
					$msg = "Dana pada kas tidak mencukupi. Mohon cek laporan cash flow";
				}
				$errors[] = sprintf("Gagal %s Dokumen NPKP: %s. Error: %s", $processName, $cashRequest->DocumentNo, $msg);
				$this->connector->RollbackTransaction();
			}
		}

		if (count($infos) > 0) {
			$this->persistence->SaveState("info", "<ul><li>". implode("</li><li>", $infos) ."</li></ul>");
		}
		if (count($errors) > 0) {
			$this->persistence->SaveState("error", "<ul><li>". implode("</li><li>", $errors) ."</li></ul>");
		}

		if (count($ids) == 1) {
			redirect_url("accounting.cashrequest/view/" . $ids[0]);
		} else {
			redirect_url("accounting.cashrequest");
		}
	}

	public function approve() {
		$ids = $this->GetGetValue("id", array());
		$this->ProcessDocuments($ids, "approve", 1, "DRAFT", "ApprovedById", "Approve");
	}

	public function dis_approve() {
		$ids = $this->GetGetValue("id", array());
		$this->ProcessDocuments($ids, "dis-approve", 2, "APPROVED", "UpdatedById", "DisApprove");
	}

	public function verify() {
		$ids = $this->GetGetValue("id", array());
		$this->ProcessDocuments($ids, "verify", 2, "APPROVED", "VerifiedById", "Verify");
	}

	public function dis_verify() {
		$ids = $this->GetGetValue("id", array());
		$this->ProcessDocuments($ids, "dis-verify", 3, "VERIFIED", "UpdatedById", "Disprove");
	}

	public function post() {
		$ids = $this->GetGetValue("id", array());
		$this->ProcessDocuments($ids, "approve lv 2", 3, "VERIFIED", "ApprovedLv2ById", "ApproveLv2");
	}

	public function un_post() {
		$ids = $this->GetGetValue("id", array());
		$this->ProcessDocuments($ids, "dis-verify", 4, "APPROVED LV 2", "UpdatedById", "DisApproveLv2");
	}

//	public function batch_funds() {
//		$ids = $this->GetGetValue("id", array());
//		$this->ProcessDocuments($ids, "pencairan dana", 4, "APPROVED LV 2", "FundedById", "Funds");
//	}
//
//	public function batch_unfunds() {
//		$ids = $this->GetGetValue("id", array());
//		$this->ProcessDocuments($ids, "penarikan dana", 6, "DANA CAIR", "UpdatedById", "UnFunds");
//	}

    //proses cetak NPKP
    public function doc_print($output = "xls"){
	    require_once (MODEL . "master/company.php");
        require_once(MODEL. "accounting/cash_request.php");
        require_once(MODEL. "accounting/cash_request_detail.php");
        require_once(LIBRARY. "gen_functions.php");

        $ids = $this->GetGetValue("id", array());

        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih dokumen yang akan di print !");
            redirect_url("purchase.pr");
            return;
        }

        $report = array();
        foreach ($ids as $id) {

            $cash = new CashRequest();
            $cash = $cash->LoadById($id);
            $cash->LoadDetails();

            $report[] = $cash;
        }
        $company = new Company($this->userCompanyId);
		$this->Set("company", $company);
        $this->Set("output", $output);
        $this->Set("report", $report);
    }

    //buat halaman search data
    public function overview() {
        require_once(MODEL. "accounting/cash_request.php");
        require_once(MODEL. "accounting/cash_request_category.php");
        require_once(MODEL. "status_code.php");

        if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
            $status = $this->GetGetValue("status");
            $category = $this->GetGetValue("category");
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText =
"SELECT a.*, b.jumlah, c.entity_cd AS entity, d.short_desc AS status_name, e.code AS project
FROM ac_cash_request_master AS a
	JOIN (
		SELECT cash_request_master_id, SUM(amount) AS jumlah
		FROM ac_cash_request_detail
		GROUP BY cash_request_master_id
	) AS b ON a.id = b.cash_request_master_id
JOIN cm_company AS c ON a.entity_id = c.entity_id
JOIN sys_status_code AS d ON a.status = d.code AND d.key = 'npkp_status'
JOIN ac_cash_request_category AS e ON a.category_id = e.id
WHERE a.is_deleted = 0 AND a.cash_request_date BETWEEN ?start AND ?end";

            if ($this->userCompanyId != 7){
                $this->connector->CommandText .= " AND a.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }
            if($status != -1) {
                $this->connector->CommandText .= " AND a.status = ?status";
                $this->connector->AddParameter("?status", $status);
            }
            if($category > 0) {
                $this->connector->CommandText .= " AND a.category_id = ?category";
                $this->connector->AddParameter("?category", $category);
            }
            $this->connector->CommandText .= " ORDER BY a.cash_request_date DESC";
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
            $report = $this->connector->ExecuteQuery();

        } else {
			$start = mktime(0, 0, 0, date("n"), 1);
			$end = mktime(0, 0, 0);
            $status = null;
            $output = "web";
            $report = null;
            if ($this->userLevel < 5){
                $category = $this->userProjectId;
            }else{
                $category = 0;
            }
        }

        $sysCode = new StatusCode();
        $category = new CashRequestCategory();
        if ($this->userLevel < 5) {
            $categories = $category->LoadByAllowedProject($this->userProjectIds);
        }else{
            $categories = $category->LoadByEntity($this->userCompanyId);
        }
        $this->Set("categories", $categories);
        $this->Set("categoryId", $category);
		$this->Set("start", $start);
		$this->Set("end", $end);
        $this->Set("statuses", $sysCode->LoadNpkpStatus());
		$this->Set("statusCode", $status);
        $this->Set("report", $report);
        $this->Set("output", $output);
        $this->Set("uLevel", $this->userLevel);
    }
}


// End of File: cashrequest_controller.php
