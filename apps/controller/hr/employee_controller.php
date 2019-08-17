<?php
class EmployeeController extends AppController {
	private $userCompanyId;
    private $userUid;
	private $userLevel;
	private $userCabangId;

	protected function Initialize() {
		require_once(MODEL . "hr/employee.php");
		require_once(MODEL . "master/user_admin.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
		$this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
		$this->userLevel = $this->persistence->LoadState("user_lvl");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		//$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 60);
		$settings["columns"][] = array("name" => "a.badge_id", "display" => "ID Badge", "width" => 100);
		$settings["columns"][] = array("name" => "a.nama", "display" => "Employee Name", "width" => 200);
        $settings["columns"][] = array("name" => "a.jabatan", "display" => "Level", "width" => 150);
        $settings["columns"][] = array("name" => "a.bagian", "display" => "Dept", "width" => 150);
        $settings["columns"][] = array("name" => "a.no_hp", "display" => "Phone", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.jk = 'L','Laki-laki',if(a.jk='P','Perempuan','-'))", "display" => "Gender", "width" => 100);

		$settings["filters"][] = array("name" => "a.nama", "display" => "Nama");
        $settings["filters"][] = array("name" => "a.jabatan", "display" => "Jabatan");
        $settings["filters"][] = array("name" => "a.bagian", "display" => "Bagian");
        $settings["filters"][] = array("name" => "a.badge_id", "display" => "ID");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Employee Master";

			if ($acl->CheckUserAccess("employee", "add", "master")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "hr.employee/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("employee", "edit", "master")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "hr.employee/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih data employee terlebih dahulu !\nPERHATIAN: Mohon memilih tepat satu employee",
											   "Info" => "Apakah anda yakin mau merubah data employee yang dipilih ?");
			}
			if ($acl->CheckUserAccess("employee", "view", "master")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "hr.employee/view/%s", "Class" => "bt_view", "ReqId" => 1,
						"Error" => "Mohon memilih data employee terlebih dahulu !\nPERHATIAN: Mohon memilih tepat satu employee",
                        "Confirm" => "");
			}
			if ($acl->CheckUserAccess("employee", "delete", "master")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "hr.employee/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih data employee terlebih dahulu !\nPERHATIAN: Mohon memilih tepat satu employee",
											   "Info" => "Apakah anda yakin mau menghapus data employee yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "hr_employee_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
        require_once(MODEL . "master/department.php");
		$loader = null;
		//$log = new UserAdmin();
		$employee = new Employee();
		$fpath = null;
		$ftmp = null;
		$fname = null;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$employee->EntityId = $this->userCompanyId;
            $employee->DeptId = $this->GetPostValue("DeptId");
            $employee->MulaiKerja = strtotime($this->GetPostValue("MulaiKerja"));
			$employee->BadgeId = $this->GetPostValue("BadgeId");
            $employee->Nik = $this->GetPostValue("Nik");
            $employee->Npwp = $this->GetPostValue("Npwp");
            $employee->Nama = strtoupper($this->GetPostValue("Nama"));
            $employee->TglLahir = strtotime($this->GetPostValue("TglLahir"));
            $employee->T4Lahir = $this->GetPostValue("T4Lahir");
            $employee->Jkelamin = $this->GetPostValue("Jkelamin");
            $employee->StsPajak = $this->GetPostValue("StsPajak");
            $employee->Alamat = $this->GetPostValue("Alamat");
            $employee->Jabatan = $this->GetPostValue("Jabatan");
            $employee->Bagian = $this->GetPostValue("Bagian");
            $employee->Pendidikan = $this->GetPostValue("Pendidikan");
            $employee->Gol = $this->GetPostValue("Gol");
            $employee->Email = $this->GetPostValue("Email");
            $employee->NoHp = $this->GetPostValue("NoHp");
            $employee->Poh = $this->GetPostValue("Poh");
            $employee->Agama = $this->GetPostValue("Agama");
            $employee->NmIbuKandung = $this->GetPostValue("NmIbuKandung");
            $employee->Bank = $this->GetPostValue("Bank");
            $employee->NoRek = $this->GetPostValue("NoRek");
            $employee->NoBpjsKes = $this->GetPostValue("NoBpjsKes");
            $employee->NoBpjsTk = $this->GetPostValue("NoBpjsTk");
            $employee->NoInhealth = $this->GetPostValue("NoInhealth");
            $employee->StsKaryawan = $this->GetPostValue("StsKaryawan");
            $employee->IsAktif = $this->GetPostValue("IsAktif");
            $employee->CreatebyId = $this->userUid;
			$employee->Fphoto = null;
            $employee->Fsignature = null;
            if (!empty($_FILES['Fphoto']['tmp_name'])){
                $fpath = 'public/upload/images/';
                $ftmp = $_FILES['Fphoto']['tmp_name'];
                $fname = $_FILES['Fphoto']['name'];
                $ext = explode(".", $fname);
                $ext = end($ext);
                $fname = 'pic-'.$employee->BadgeId.'.'.$ext;
                $fpath.= $fname;
                $employee->Fphoto = $fpath;
                if(!move_uploaded_file($ftmp,$fpath)){
                    $this->Set("error", sprintf("Gagal Upload file photo..", $this->connector->GetErrorMessage()));
                }
            }
            if (!empty($_FILES['Fsignature']['tmp_name'])){
                $fpath = 'public/upload/images/';
                $ftmp = $_FILES['Fsignature']['tmp_name'];
                $fname = $_FILES['Fsignature']['name'];
                $ext = explode(".", $fname);
                $ext = end($ext);
                $fname = 'sig-'.$employee->BadgeId.'.'.$ext;
                $fpath.= $fname;
                $employee->Fsignature = $fpath;
                if(!move_uploaded_file($ftmp,$fpath)){
                    $this->Set("error", sprintf("Gagal Upload file signature..", $this->connector->GetErrorMessage()));
                }
            }

			if ($this->DoInsert($employee)) {
				$this->persistence->SaveState("info", sprintf("Data Nama Karyawan: '%s' Dengan ID: %s telah berhasil disimpan.", $employee->Nama, $employee->BadgeId));
				redirect_url("hr.employee");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("ID: '%s' telah ada pada database !", $employee->BadgeId));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// load data company for combo box
        $loader = new Department();
        $depts = $loader->LoadByEntityId($this->userCompanyId);

		// untuk kirim variable ke view
		$this->Set("employee", $employee);
        $this->Set("depts", $depts);
	}

	private function DoInsert(Employee $employee) {

		if ($employee->BadgeId == "") {
			$this->Set("error", "Badge ID masih kosong");
			return false;
		}
		if ($employee->DeptId == "" || $employee->DeptId == 0 || $employee->DeptId == null) {
			$this->Set("error", "Departemen masih kosong");
			return false;
		}

		if ($employee->Nama == "") {
			$this->Set("error", "Nama Karyawan masih kosong");
			return false;
		}

        if ($employee->MulaiKerja == "") {
            $this->Set("error", "Tanggal Mulai Kerja masih kosong");
            return false;
        }

        if ($employee->TglLahir == "") {
            $this->Set("error", "Tanggal Lahir masih kosong");
            return false;
        }

		if ($employee->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
        require_once(MODEL . "master/department.php");
		$loader = null;
		//$log = new UserAdmin();
		$employee = new Employee();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$employee->Id = $id;
            $employee->EntityId = $this->userCompanyId;
            $employee->DeptId = $this->GetPostValue("DeptId");
            $employee->MulaiKerja = strtotime($this->GetPostValue("MulaiKerja"));
            $employee->BadgeId = $this->GetPostValue("BadgeId");
            $employee->Nik = $this->GetPostValue("Nik");
            $employee->Npwp = $this->GetPostValue("Npwp");
            $employee->Nama = strtoupper($this->GetPostValue("Nama"));
            $employee->TglLahir = strtotime($this->GetPostValue("TglLahir"));
            $employee->T4Lahir = $this->GetPostValue("T4Lahir");
            $employee->Jkelamin = $this->GetPostValue("Jkelamin");
            $employee->StsPajak = $this->GetPostValue("StsPajak");
            $employee->Alamat = $this->GetPostValue("Alamat");
            $employee->Jabatan = $this->GetPostValue("Jabatan");
            $employee->Bagian = $this->GetPostValue("Bagian");
            $employee->Pendidikan = $this->GetPostValue("Pendidikan");
            $employee->Gol = $this->GetPostValue("Gol");
            $employee->Email = $this->GetPostValue("Email");
            $employee->NoHp = $this->GetPostValue("NoHp");
            $employee->Poh = $this->GetPostValue("Poh");
            $employee->Agama = $this->GetPostValue("Agama");
            $employee->NmIbuKandung = $this->GetPostValue("NmIbuKandung");
            $employee->Bank = $this->GetPostValue("Bank");
            $employee->NoRek = $this->GetPostValue("NoRek");
            $employee->NoBpjsKes = $this->GetPostValue("NoBpjsKes");
            $employee->NoBpjsTk = $this->GetPostValue("NoBpjsTk");
            $employee->NoInhealth = $this->GetPostValue("NoInhealth");
            $employee->StsKaryawan = $this->GetPostValue("StsKaryawan");
            $employee->IsAktif = $this->GetPostValue("IsAktif");
            $employee->UpdatebyId = $this->userUid;
            //$employee->Fphoto = null;
            //$employee->Fsignature = null;
            if (!empty($_FILES['Fphoto']['tmp_name'])){
                $fpath = 'public/upload/images/';
                $ftmp = $_FILES['Fphoto']['tmp_name'];
                $fname = $_FILES['Fphoto']['name'];
                $ext = explode(".", $fname);
                $ext = end($ext);
                $fname = 'pic-'.$employee->BadgeId.'.'.$ext;
                $fpath.= $fname;
                $employee->Fphoto = $fpath;
                if(!move_uploaded_file($ftmp,$fpath)){
                    $this->Set("error", sprintf("Gagal Upload file photo..", $this->connector->GetErrorMessage()));
                }
            }
            if (!empty($_FILES['Fsignature']['tmp_name'])){
                $fpath = 'public/upload/images/';
                $ftmp = $_FILES['Fsignature']['tmp_name'];
                $fname = $_FILES['Fsignature']['name'];
                $ext = explode(".", $fname);
                $ext = end($ext);
                $fname = 'sig-'.$employee->BadgeId.'.'.$ext;
                $fpath.= $fname;
                $employee->Fsignature = $fpath;
                if(!move_uploaded_file($ftmp,$fpath)){
                    $this->Set("error", sprintf("Gagal Upload file signature..", $this->connector->GetErrorMessage()));
                }
            }
			if ($this->DoUpdate($employee)) {
				$this->persistence->SaveState("info", sprintf("Data Nama: '%s' Dengan ID: %s telah berhasil diupdate.", $employee->Nama, $employee->BadgeId));
				redirect_url("hr.employee");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("ID: '%s' telah ada pada database !", $employee->BadgeId));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data employee sebelum melakukan edit data !");
				redirect_url("hr.employee");
			}
			$employee = $employee->FindById($id);
			if ($employee == null) {
				$this->persistence->SaveState("error", "Data Nama yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("hr.employee");
			}
		}

        // load data company for combo box
        $loader = new Department();
        $depts = $loader->LoadByEntityId($this->userCompanyId);

        // untuk kirim variable ke view
        $this->Set("employee", $employee);
        $this->Set("depts", $depts);

	}

	public function view($id = null) {
		require_once(MODEL . "master/department.php");

		$loader = null;
		//$log = new UserAdmin();
		$employee = new Employee();

		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data employee untuk direview !");
			redirect_url("hr.employee");
		}
		$employee = $employee->FindById($id);
		if ($employee == null) {
			$this->persistence->SaveState("error", "Data Nama yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("hr.employee");
		}

        // load data company for combo box
        $loader = new Department();
        $depts = $loader->LoadByEntityId($this->userCompanyId);

        // untuk kirim variable ke view
        $this->Set("employee", $employee);
        $this->Set("depts", $depts);

	}

	private function DoUpdate(Employee $employee) {
        if ($employee->BadgeId == "") {
            $this->Set("error", "Badge ID masih kosong");
            return false;
        }
        if ($employee->DeptId == "" || $employee->DeptId == 0 || $employee->DeptId == null) {
            $this->Set("error", "Departemen masih kosong");
            return false;
        }

        if ($employee->Nama == "") {
            $this->Set("error", "Nama Karyawan masih kosong");
            return false;
        }

        if ($employee->MulaiKerja == "") {
            $this->Set("error", "Tanggal Mulai Kerja masih kosong");
            return false;
        }

        if ($employee->TglLahir == "") {
            $this->Set("error", "Tanggal Lahir masih kosong");
            return false;
        }

		if ($employee->Update($employee->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data employee sebelum melakukan hapus data !");
			redirect_url("hr.employee");
		}
		//$log = new UserAdmin();
		$employee = new Employee();
		$employee = $employee->FindById($id);
		if ($employee == null) {
			$this->persistence->SaveState("error", "Data employee yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("hr.employee");
		}

		if ($employee->Delete($employee->Id) == 1) {
			//$log = $log->UserActivityWriter($this->userCabangId,'hr.employee','Delete Employee -> NIK: '.$employee->Nik.' - '.$employee->Nama,'-','Success');
			$this->persistence->SaveState("info", sprintf("Data Nama: '%s' Dengan ID: %s telah berhasil dihapus.", $employee->Nama, $employee->BadgeId));
			redirect_url("hr.employee");
		} else {
			//$log = $log->UserActivityWriter($this->userCabangId,'hr.employee','Delete Employee -> NIK: '.$employee->Nik.' - '.$employee->Nama,'-','Success');
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data employee: '%s'. Message: %s", $employee->Nama, $this->connector->GetErrorMessage()));
		}
		redirect_url("hr.employee");
	}

	public function autoNik($cabId) {
		$employee = new Employee();
		$nik = $employee->GetAutoNik($cabId);
		print($nik);
	}
}
