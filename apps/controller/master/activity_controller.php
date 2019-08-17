<?php
class ActivityController extends AppController {
	private  $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/activity.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "a.act_code", "display" => "Activity Code", "width" => 100);
		$settings["columns"][] = array("name" => "a.act_name", "display" => "Activity Name", "width" => 250);

		$settings["filters"][] = array("name" => "a.act_code", "display" => "Act Code");
		$settings["filters"][] = array("name" => "a.act_name", "display" => "Activity Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Activity Master";
			if($acl->CheckUserAccess("activity", "add", "master")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.activity/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("activity", "edit", "master")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.activity/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih activity sebelum melakukan proses edit data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "");
			}
			if($acl->CheckUserAccess("activity", "delete", "master")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.activity/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Maaf anda harus memilih activity sebelum melakukan proses penghapusan data !\nPERHATIAN: Mohon memilih tepat 1 data.",
											   "Confirm" => "Apakah anda yakin mau menghapus data yang dipilih ?");
			}
			$settings["def_filter"] = 0;
			$settings["def_order"] = 3;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_activity AS a JOIN cm_company AS b ON a.entity_id = b.entity_id";
			$settings["where"] = "a.is_deleted = 0 And a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/company.php");
        $activity = new Activity();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$activity->EntityId = $this->userCompanyId;
            $activity->ActCode = $this->GetPostValue("ActCode");
			$activity->ActName = $this->GetPostValue("ActName");

			if ($this->DoInsert($activity)) {
                $this->persistence->SaveState("info", sprintf("Data Activity: '%s' Dengan Kode: %s telah berhasil disimpan.", $activity->ActName, $activity->ActCode));
                redirect_url("master.activity");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $activity->ActCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} 

        // untuk kirim variable ke view
		$this->Set("activity", $activity);
        $this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(Activity $activity) {
		if ($activity->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
        if ($activity->ActCode == "") {
			$this->Set("error", "Kode activity masih kosong");
			return false;
		}
		if ($activity->ActName == "") {
			$this->Set("error", "Nama activity masih kosong");
			return false;
		}
		if ($activity->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        $loader = null;
        $activity = new Activity();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
            $activity->Id = $id;
			$activity->EntityId = $this->userCompanyId;
			$activity->ActCode = $this->GetPostValue("ActCode");
            $activity->ActName = $this->GetPostValue("ActName");
			if ($this->DoUpdate($activity)) {
				$this->persistence->SaveState("info", sprintf("Data Activity: '%s' Dengan Kode: %s telah berhasil diupdate.", $activity->ActName, $activity->ActCode));
                redirect_url("master.activity");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $activity->ActCode));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
            if ($id == null) {
                $this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
                redirect_url("master.activity");
		    }
			$activity = $activity->FindById($id);
			if ($activity == null) {
				$this->persistence->SaveState("error", "Data Activity yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.activity");
			}
		}

		// untuk kirim variable ke view
        $this->Set("company", new Company($this->userCompanyId));
		$this->Set("activity", $activity);
	}

	private function DoUpdate(Activity $activity) {
		if ($activity->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

        if ($activity->ActCode == "") {
			$this->Set("error", "Kode activity masih kosong");
			return false;
		}

        if ($activity->ActName == "") {
			$this->Set("error", "Nama activity masih kosong");
			return false;
		}

		if ($activity->Update($activity->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data activity sebelum melakukan hapus data !");
			redirect_url("master.activity");
		}

		$activity = new Activity();
		$activity = $activity->FindById($id);
        if ($activity == null) {
			$this->persistence->SaveState("error", "Data activity yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.activity");
		}

		if ($activity->Delete($activity->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Activity: '%s' Dengan Kode: %s telah berhasil dihapus.", $activity->ActName, $activity->ActCode));
            redirect_url("master.activity");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data activity: '%s'. Message: %s", $activity->ActName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.activity");
	}
}
