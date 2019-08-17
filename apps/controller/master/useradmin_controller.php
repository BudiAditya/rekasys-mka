<?php
class UserAdminController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/user_admin.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.user_uid", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.user_id", "display" => "User ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.user_name", "display" => "User Name", "width" => 150);
		$settings["columns"][] = array("name" => "a.user_email", "display" => "Email", "width" => 150);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 50);
		$settings["columns"][] = array("name" => "CASE a.is_aktif WHEN 1 THEN 'Aktif' ELSE 'Non-Aktif' END", "display" => "Status", "width" => 50);
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status Login", "width" => 70);
		$settings["columns"][] = array("name" => "a.login_time", "display" => "Waktu Login", "width" => 100);
		$settings["columns"][] = array("name" => "a.login_from", "display" => "Login Dari", "width" => 80);

		$settings["filters"][] = array("name" => "a.user_id", "display" => "User ID");
		$settings["filters"][] = array("name" => "a.user_name", "display" => "User Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "User Management";
			if ($acl->CheckUserAccess("master.useradmin", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.useradmin/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.useradmin", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.useradmin/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if ($acl->CheckUserAccess("master.useradmin", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.useradmin/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}
			if ($acl->CheckUserAccess("master.useracl", "add")) {
				$settings["actions"][] = array("Text" => "Access Control List", "Url" => "master.useracl/add/%s/0", "Class" => "bt_lock", "ReqId" => 1);
			}
		} else {
			$settings["from"] = "sys_users AS a JOIN cm_company AS b ON a.entity_id = b.entity_id JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'login_audit'";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;

		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "hr/employee.php");

		$loader = null;
		$userAdmin = new UserAdmin();
        $alproids = null;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$userAdmin->UserId = $this->GetPostValue("UserId");
			$userAdmin->UserPwd1 = $this->GetPostValue("UserPwd1");
			$userAdmin->UserPwd2 = $this->GetPostValue("UserPwd2");
			$userAdmin->EntityId = $this->userCompanyId;
			$userAdmin->EmployeeId = $this->GetPostValue("EmployeeId");
			$userAdmin->ProjectId = 0;
			$userAdmin->UserLvl = $this->GetPostValue("UserLvl");
            $alproids = $this->GetPostValue("AProjectId",array());
            if (isset($this->postData["AllowMultipleLogin"])) {
                $userAdmin->AllowMultipleLogin = 1;
            } else {
                $userAdmin->AllowMultipleLogin = 0;
            }
            if (isset($this->postData["IsAktif"])) {
                $userAdmin->IsAktif = 1;
            } else {
                $userAdmin->IsAktif = 0;
            }
            if (isset($this->postData["IsForcePeriod"])) {
                $userAdmin->IsForceAccountingPeriod = 1;
            } else {
                $userAdmin->IsForceAccountingPeriod = 0;
            }

			if ($this->DoInsert($userAdmin,$alproids)) {
				$this->persistence->SaveState("info", sprintf("Data User: '%s' Dengan ID: %s telah berhasil disimpan.", $userAdmin->UserName, $userAdmin->UserId));
				redirect_url("master.useradmin");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("ID: '%s' telah ada pada database !", $userAdmin->UserId));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// load data company for combo box
		$loader = new Company();
		$companies = $loader->LoadById($this->userCompanyId);
		$loader = new Project();
		$projects = $loader->LoadByEntityId($this->userCompanyId);
		$this->Set("companies", $companies);
		$this->Set("projects", $projects);
		$this->Set("userAdmin", $userAdmin);
        $loader = new Employee();
        $employees = $loader->LoadByEntityId($this->userCompanyId);
        $this->Set("employees", $employees);
	}

	private function DoInsert(UserAdmin $userAdmin,$alproids) {

        if ($userAdmin->UserId == "") {
            $this->Set("error", "ID User masih kosong");
            return false;
        }

	    if ($userAdmin->EmployeeId > 0) {
            require_once(MODEL . "hr/employee.php");
            $staf = new Employee($userAdmin->EmployeeId);
            if ($staf == null){
                $this->Set("error", "Nama Karyawan tidak terdaftar!");
                return false;
            }else{
                $userAdmin->UserName = $staf->Nama;
                $userAdmin->UserEmail = $staf->Email;
            }
        }else{
            $this->Set("error", "Nama Karyawan tidak terdaftar!");
	        return false;
        }

		if ($userAdmin->UserName == "") {
			$this->Set("error", "Nama User masih kosong");
			return false;
		}

		if (count($alproids) == 0) {
			$this->Set("error", "Project belum dipilih!");
			return false;
		}else{
		    foreach ($alproids as $proid){
		        $userAdmin->AProjectId.= $proid[0];
            }
        }

		if (strlen($userAdmin->UserPwd1) == 0 || strlen($userAdmin->UserPwd2) == 0) {
			$this->Set("error", "Password belum diisi");
			return false;
		}
		if ($userAdmin->UserPwd1 <> $userAdmin->UserPwd2) {
			$this->Set("error", "Password & Password Konfirmasi tidak sama");
			return false;
		}

		if ($userAdmin->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/project.php");
        require_once(MODEL . "hr/employee.php");

		$loader = null;
		$userAdmin = new UserAdmin();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$userAdmin->UserUid = $id;
			$userAdmin->UserId = $this->GetPostValue("UserId");
			$userAdmin->UserPwd1 = $this->GetPostValue("UserPwd1");
			$userAdmin->UserPwd2 = $this->GetPostValue("UserPwd2");
            $userAdmin->EmployeeId = $this->GetPostValue("EmployeeId");
			$userAdmin->EntityId = $this->userCompanyId;
			$userAdmin->ProjectId = null;
			$userAdmin->UserLvl = $this->GetPostValue("UserLvl");
            $alproids = $this->GetPostValue("AProjectId",array());
			if (isset($this->postData["AllowMultipleLogin"])) {
				$userAdmin->AllowMultipleLogin = 1;
			} else {
				$userAdmin->AllowMultipleLogin = 0;
			}
			if (isset($this->postData["IsAktif"])) {
				$userAdmin->IsAktif = 1;
			} else {
				$userAdmin->IsAktif = 0;
			}
			if (isset($this->postData["IsForcePeriod"])) {
				$userAdmin->IsForceAccountingPeriod = 1;
			} else {
				$userAdmin->IsForceAccountingPeriod = 0;
			}

			if ($this->DoUpdate($userAdmin,$alproids)) {
				$this->persistence->SaveState("info", sprintf("Data User: '%s' Dengan ID: %s telah berhasil diupdate.", $userAdmin->UserName, $userAdmin->UserId));
				redirect_url("master.useradmin");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("ID: '%s' telah ada pada database !", $userAdmin->UserId));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data User sebelum melakukan edit data !");
				redirect_url("master.useradmin");
			}
			$userAdmin = $userAdmin->FindById($id);
			if ($userAdmin == null) {
				$this->persistence->SaveState("error", "Data User yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.useradmin");
			}
		}

		// load data company for combo box
		$loader = new Company();
		$companies = $loader->LoadById($this->userCompanyId);
		$project = new Project();
		$this->Set("companies", $companies);
		$this->Set("projects", $project->LoadByEntityId($this->userCompanyId));
		$this->Set("userAdmin", $userAdmin);
        $loader = new Employee();
        $employees = $loader->LoadByEntityId($this->userCompanyId);
        $this->Set("employees", $employees);
	}

	private function DoUpdate(UserAdmin $userAdmin,$alproids) {
		if ($userAdmin->UserId == "") {
			$this->Set("error", "ID User masih kosong");
			return false;
		}

        if ($userAdmin->EmployeeId > 0) {
            require_once(MODEL . "hr/employee.php");
            $staf = new Employee($userAdmin->EmployeeId);
            if ($staf == null){
                $this->Set("error", "Nama Karyawan tidak terdaftar!");
                return false;
            }else{
                $userAdmin->UserName = $staf->Nama;
                $userAdmin->UserEmail = $staf->Email;
            }
        }else{
            $this->Set("error", "Nama Karyawan tidak terdaftar!");
            return false;
        }

		if ($userAdmin->UserName == "") {
			$this->Set("error", "Nama User masih kosong");
			return false;
		}

        if (count($alproids) == 0) {
            $this->Set("error", "Project belum dipilih!");
            return false;
        }else{
            foreach ($alproids as $proid){
                $userAdmin->AProjectId.= $proid[0];
            }
        }

		if ($userAdmin->UserPwd1 <> $userAdmin->UserPwd2) {
			$this->Set("error", "Password & Password Konfirmasi tidak sama");
			return false;
		}

		if ($userAdmin->Update($userAdmin->UserUid) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data User sebelum melakukan hapus data !");
			redirect_url("master.useradmin");
		}

		$userAdmin = new UserAdmin();
		$userAdmin = $userAdmin->FindById($id);
		if ($userAdmin == null) {
			$this->persistence->SaveState("error", "Data User yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.useradmin");
		}

		if ($userAdmin->Delete($userAdmin->UserUid) == 1) {
			$this->persistence->SaveState("info", sprintf("Data User: '%s' Dengan ID: %s telah berhasil dihapus.", $userAdmin->UserName, $userAdmin->UserId));
			redirect_url("master.useradmin");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data User: '%s'. Message: %s", $userAdmin->UserName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.useradmin");
	}
}
