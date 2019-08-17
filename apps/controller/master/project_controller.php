<?php
class ProjectController extends AppController {
	private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/project.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.entity_cd", "display" => "Company", "width" => 80);
		$settings["columns"][] = array("name" => "a.project_cd", "display" => "Code", "width" => 80);
		$settings["columns"][] = array("name" => "a.project_name", "display" => "Project Name", "width" => 250);
		$settings["columns"][] = array("name" => "a.project_location", "display" => "Location", "width" => 100);
        $settings["columns"][] = array("name" => "a.pic", "display" => "P I C", "width" => 100);

		$settings["filters"][] = array("name" => "a.project_cd", "display" => "Code");
		$settings["filters"][] = array("name" => "a.project_name", "display" => "Project Name");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Projects Information";

			if ($acl->CheckUserAccess("master.project", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.project/add", "Class" => "bt_add", "ReqId" => 0);
			}
            if ($acl->CheckUserAccess("master.project", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "master.project/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih proyek yang akan di edit terlebih dahulu !\nPERHATIAN: Harap memilih tepat 1 proyek.",
                    "Confirm" => "");
            }
			if ($acl->CheckUserAccess("master.project", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "master.project/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih proyek terlebih dahulu !\nPERHATIAN: Harap memilih tepat 1 proyek.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.project", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.project/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih proyek yang akan di hapus terlebih dahulu !\nPERHATIAN: Harap memilih tepat 1 proyek.",
					"Confirm" => "Apakah anda yakin mau meng-hapus data proyek yang dipilih ?");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "cm_project AS a JOIN cm_company AS b ON a.entity_id = b.entity_id";
			$settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		$project = new Project();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$project->EntityId = $this->userCompanyId;
			$project->ProjectCd = $this->GetPostValue("ProjectCd");
			$project->ProjectName = $this->GetPostValue("ProjectName");
			$project->ProjectLocation = $this->GetPostValue("ProjectLocation");
			$project->Pic = $this->GetPostValue("Pic");
			if ($this->DoInsert($project)) {
				$this->persistence->SaveState("info", sprintf("Data Proyek: '%s' Dengan Kode: %s telah berhasil disimpan.", $project->ProjectName, $project->ProjectCd));
				redirect_url("master.project");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $project->EntityCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}
		// untuk kirim variable ke view
		$this->Set("project", $project);
		$this->Set("company", new Company($this->userCompanyId));
	}

	private function DoInsert(Project $project) {
		if ($project->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}
		if ($project->ProjectCd == "") {
			$this->Set("error", "Kode proyek masih kosong");
			return false;
		}

		if ($project->ProjectName == "") {
			$this->Set("error", "Nama proyek masih kosong");
			return false;
		}

		if ($project->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function view($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
			redirect_url("master.project");
		}
		$project = new Project($id);
		if ($project == null) {
			$this->persistence->SaveState("error", "Data Proyek yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.project");
		}

		require_once(MODEL . "master/company.php");

		$this->Set("project", $project);
		$this->Set("company", new Company($project->EntityId));

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
		require_once(MODEL . "master/company.php");
		$project = new Project();
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$project->Id = $id;
			$project->EntityId = $this->userCompanyId;
			$project->ProjectCd = $this->GetPostValue("ProjectCd");
			$project->ProjectName = $this->GetPostValue("ProjectName");
			$project->ProjectLocation = $this->GetPostValue("ProjectLocation");
			$project->Pic = $this->GetPostValue("Pic");
			if ($this->DoUpdate($project)) {
				$this->persistence->SaveState("info", sprintf("Data Proyek: '%s' Dengan Kode: %s telah berhasil diupdate.", $project->ProjectName, $project->ProjectCd));
				redirect_url("master.project");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $project->EntityCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data perusahaan sebelum melakukan edit data !");
				redirect_url("master.project");
			}
			$project = $project->FindById($id);
			if ($project == null) {
				$this->persistence->SaveState("error", "Data Proyek yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("master.project");
			}
		}
		// untuk kirim variable ke view
		$this->Set("project", $project);
		$this->Set("company", new Company($project->EntityId));
	}

	private function DoUpdate(Project $project) {
		if ($project->EntityId == "") {
			$this->Set("error", "Kode perusahaan masih kosong");
			return false;
		}

		if ($project->ProjectName == "") {
			$this->Set("error", "Nama perusahaan masih kosong");
			return false;
		}
		if ($project->Update($project->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data proyek sebelum melakukan hapus data !");
			redirect_url("master.project");
		}

		$project = new Project();
		$project = $project->FindById($id);
		if ($project == null) {
			$this->persistence->SaveState("error", "Data proyek yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("master.project");
		}
		if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
			if ($project->EntityId != $this->userCompanyId) {
				$this->persistence->SaveState("error", "Maaf dikarenakan project dibuat oleh Company " . $project->EntityCd . " maka hanya Company bersangkutan yang dapat meng-hapusnya");
				redirect_url("master.project/view/" . $project->Id);
			}
		}

		if ($project->Delete($project->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Proyek: '%s' Dengan Kode: %s telah berhasil dihapus.", $project->ProjectName, $project->ProjectCd));
			redirect_url("master.project");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data proyek: '%s'. Message: %s", $project->ProjectName, $this->connector->GetErrorMessage()));
		}
		redirect_url("master.project");
	}
}
