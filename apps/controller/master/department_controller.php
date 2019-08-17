<?php
class DepartmentController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/department.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 100);
		$settings["columns"][] = array("name" => "a.dept_code", "display" => "Dept Code", "width" => 100);
		$settings["columns"][] = array("name" => "a.dept_name", "display" => "Dept Name", "width" => 250);

		$settings["filters"][] = array("name" => "a.dept_code", "display" => "Dept Code");
		$settings["filters"][] = array("name" => "a.dept_name", "display" => "Dept Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Department Information";

			if ($acl->CheckUserAccess("department", "add", "master")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.department/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("department", "edit", "master")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.department/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih data departemen terlebih dahulu !\nPERHATIAN: Mohon memilih tepat satu departemen",
											   "Confirm" => "");
			}
			if ($acl->CheckUserAccess("department", "delete", "master")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.department/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih data departemen terlebih dahulu !\nPERHATIAN: Mohon memilih tepat satu departemen",
											   "Confirm" => "Apakah anda yakin mau menghapus data departemen yang dipilih ?");
			}
			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_dept AS a JOIN cm_company AS b ON a.entity_id = b.entity_id";
			$settings["where"] = "a.is_deleted = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		$loader = null;
		$dept = new Department();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$dept->EntityId = $this->userCompanyId;
			$dept->DeptCode = $this->GetPostValue("DeptCode");
			$dept->DeptName = $this->GetPostValue("DeptName");

			if ($this->DoInsert($dept)) {
				$this->persistence->SaveState("info", sprintf("Data Departemen: '%s' Dengan Kode: %s telah berhasil disimpan.", $dept->DeptName, $dept->DeptCode));
				redirect_url("master.department");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $dept->EntityCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		// untuk kirim variable ke view
		$this->Set("dept", $dept);
        $this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(Department $dept) {

		if ($dept->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
		if ($dept->DeptCode == "") {
			$this->Set("error", "Kode departemen masih kosong");
			return false;
		}

		if ($dept->DeptName == "") {
			$this->Set("error", "Nama departemen masih kosong");
			return false;
		}

		if ($dept->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {
		require_once(MODEL . "master/company.php");
		$loader = null;
		$dept = new Department();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$dept->Id = $id;
			$dept->EntityId = $this->userCompanyId;
			$dept->DeptCode = $this->GetPostValue("DeptCode");
			$dept->DeptName = $this->GetPostValue("DeptName");

			if ($this->DoUpdate($dept)) {
				$this->persistence->SaveState("info", sprintf("Data Departemen: '%s' Dengan Kode: %s telah berhasil diupdate.", $dept->DeptName, $dept->DeptCode));
				redirect_url("master.department");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $dept->EntityCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
				redirect_url("master.department");
			}
			$dept = $dept->FindById($id);
			if ($dept == null) {
				$this->persistence->SaveState("error", "Data Departemen yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.department");
			}
		}
        // untuk kirim variable ke view
		$this->Set("dept", $dept);
        $this->Set("company", new Company($this->userCompanyId));

	}

	private function DoUpdate(Department $dept) {
		if ($dept->EntityId == "") {
			$this->Set("error", "Kode departemen masih kosong");
			return false;
		}

		if ($dept->DeptName == "") {
			$this->Set("error", "Nama departemen masih kosong");
			return false;
		}

		if ($dept->Update($dept->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null)
    {
        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih data departemen sebelum melakukan hapus data !");
            redirect_url("master.department");
        }

        $dept = new Department();
        $dept = $dept->FindById($id);
        if ($dept == null) {
            $this->persistence->SaveState("error", "Data departemen yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("master.department");
        }

        if ($dept->Delete($dept->Id) == 1) {
            $this->persistence->SaveState("info", sprintf("Data Departemen: '%s' Dengan Kode: %s telah berhasil dihapus.", $dept->DeptName, $dept->DeptCode));
            redirect_url("master.department");
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus data departemen: '%s'. Message: %s", $dept->DeptName, $this->connector->GetErrorMessage()));
        }
        redirect_url("master.department");
    }
}
