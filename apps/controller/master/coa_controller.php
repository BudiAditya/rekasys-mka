<?php
class CoaController extends AppController {
    private  $userCompanyId;
	protected function Initialize() {
		require_once(MODEL . "master/coa.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
        $settings["columns"][] = array("name" => "a.acc_no", "display" => "Account No", "width" => 100);
		$settings["columns"][] = array("name" => "a.acc_name", "display" => "Account Name", "width" => 250);
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Level", "width" => 100);
        $settings["columns"][] = array("name" => "d.acc_no", "display" => "Parent No", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.dc_saldo = 'D','Debit','Credit')", "display" => "D/C Position", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.acc_status = 1,'Active','Inactive')", "display" => "Status", "width" => 100);

		$settings["filters"][] = array("name" => "a.acc_no", "display" => "Account No");
        $settings["filters"][] = array("name" => "a.acc_name", "display" => "Account Name");
        $settings["filters"][] = array("name" => "c.short_desc", "display" => "Account Level");
        $settings["filters"][] = array("name" => "if(a.dc_saldo = 'D','Debit','Credit')", "display" => "D/C Position");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"]  = "Master Chart of Account (CoA)";

			if ($acl->CheckUserAccess("master.coa", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.coa/add", "Class" => "bt_add", "ReqId" => 0);
			}
            if($acl->CheckUserAccess("coa", "edit", "master")){
                $settings["actions"][] = array("Text" => "Edit", "Url" => "master.coa/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Please choose one data to edit process !",
                    "Confirm" => "");
            }
            if($acl->CheckUserAccess("coa", "delete", "master")){
                $settings["actions"][] = array("Text" => "Delete", "Url" => "master.coa/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
                    "Error" => "Please choose one data to delete process !",
                    "Confirm" => "Are you sure delete choosen data ?");
            }
            $settings["def_filter"] = 0;
            $settings["def_order"] = 2;
            $settings["singleSelect"] = true;
		} else {
			//$settings["dBasePool"] = "corp";
			$settings["from"] = "cm_acc_detail AS a Join cm_company AS b On a.entity_id = b.entity_id Left Join sys_status_code AS c On a.acc_level = c.`code` And c.`key` = 'acc_level' Left Join cm_acc_detail AS d On a.acc_parent_id = d.id";
			$settings["where"] = "a.entity_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Coa $coa) {
		if ($coa->AccLevel == 0) {
			$coa->AccParentId = null;
		} else if ($coa->AccParentId == null) {
			$this->Set("error", "Anda harus memilih akun utama terlebih dahulu karena anda tidak membuat akun dengan type 0");
			return false;
		}
		if ($coa->AccNo == null) {
			$this->Set("error", "No. Akun masih kosong !");
			return false;
		}
		if ($coa->AccName == null) {
			$this->Set("error", "Deskripsi akun masih kosong !");
			return false;
		}

		return true;
	}

	public function add() {
		$coa = new Coa();
		if (count($this->postData) > 0) {
		    $coa->EntityId = $this->userCompanyId;
			$coa->AccNo = $this->getPostValue("AccNo");
			$coa->AccName = $this->getPostValue("AccName");
			$coa->AccLevel = $this->getPostValue("AccLevel");
			$coa->AccParentId = $this->getPostValue("AccParentId");
			$coa->DcSaldo = $this->GetPostValue("DcSaldo", "D");
            $coa->AccStatus = $this->GetPostValue("AccStatus", 1);
			if ($this->ValidateData($coa)) {
				$rs = $coa->Insert();
				if ($rs == 1) {
					$this->persistence->SaveState("info", sprintf("COA : '%s' sudah berhasil disimpan", $coa->AccNo));
					redirect_url("master.coa");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf No Akun yang akan dibuat sudah terdaftar ! Pastikan No Akun dan Jenis Akunnya");
					} else {
						$this->Set("error", "DBase error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		}
		$jsCoa = array();
		$buff = $coa->LoadByLevel($this->userCompanyId,0);
		foreach ($buff as $account) {
			$jsCoa[0][] = array("Id" => $account->Id, "AccNo" => $account->AccNo, "AccName" => $account->AccName);
		}
		$buff = $coa->LoadByLevel($this->userCompanyId,1);
		foreach ($buff as $account) {
			$jsCoa[1][] = array("Id" => $account->Id, "AccNo" => $account->AccNo, "AccName" => $account->AccName);
		}
		$buff = $coa->LoadByLevel($this->userCompanyId,2);
		foreach ($buff as $account) {
			$jsCoa[2][] = array("Id" => $account->Id, "AccNo" => $account->AccNo, "AccName" => $account->AccName);
		}

		$this->Set("jsCoa", $jsCoa);
		$this->Set("coa", $coa);
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih Akun terlebih dahulu !");
			redirect_url("master.coa");
		}

		$coa = new Coa();
		if (count($this->postData) > 0) {
			$coa->Id = $id;
            $coa->EntityId = $this->userCompanyId;
            $coa->AccNo = $this->getPostValue("AccNo");
            $coa->AccName = $this->getPostValue("AccName");
            $coa->AccLevel = $this->getPostValue("AccLevel");
            $coa->AccParentId = $this->getPostValue("AccParentId");
            $coa->DcSaldo = $this->GetPostValue("DcSaldo", "D");
            $coa->AccStatus = $this->GetPostValue("AccStatus", 1);
			if ($this->ValidateData($coa)) {
				$rs = $coa->Update($coa->Id);
				if ($rs != -1) {
					$this->persistence->SaveState("info", sprintf("Perubahan COA : '%s' sudah berhasil disimpan", $coa->AccNo));
					redirect_url("master.coa");
				} else {
					if ($this->connector->IsDuplicateError()) {
						$this->Set("error", "Maaf No Akun yang diminta sudah terdaftar ! Pastikan No Akun dan Jenis Akunnya");
					} else {
						$this->Set("error", "DBase error: " . $this->connector->GetErrorMessage());
					}
				}
			}
		} else {
			$coa = $coa->LoadById($id);
			if ($coa == null) {
				$this->persistence->SaveState("error", "Akun yang diminta tidak dapat ditemukan atau sudah dihapus !");
				redirect_url("master.coa");
			}
		}

		$jsCoa = array();
		$buff = $coa->LoadByLevel($this->userCompanyId,0);
		foreach ($buff as $account) {
			$jsCoa[0][] = array("Id" => $account->Id, "AccNo" => $account->AccNo, "AccName" => $account->AccName);
		}
		$buff = $coa->LoadByLevel($this->userCompanyId,1);
		foreach ($buff as $account) {
			$jsCoa[1][] = array("Id" => $account->Id, "AccNo" => $account->AccNo, "AccName" => $account->AccName);
		}
		$buff = $coa->LoadByLevel($this->userCompanyId,2);
		foreach ($buff as $account) {
			$jsCoa[2][] = array("Id" => $account->Id, "AccNo" => $account->AccNo, "AccName" => $account->AccName);
		}

		$this->Set("jsCoa", $jsCoa);
		$this->Set("coa", $coa);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Mohon memilih Akun terlebih dahulu !");
			redirect_url("master.coa");
		}

		$coa = new Coa();
		$coa = $coa->LoadById($id);
		if ($coa == null) {
			$this->persistence->SaveState("error", "Akun yang diminta tidak dapat ditemukan atau sudah dihapus !");
			redirect_url("master.coa");
		}

		$rs = $coa->Delete($coa->Id);
		if ($rs == 1) {
			$this->persistence->SaveState("info", sprintf("COA : '%s' sudah dihapus.", $coa->AccNo));
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal hapus COA : '%s'. Error: %s", $coa->AccNo, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.coa");
	}
}

// End of file: coa_controller.php
