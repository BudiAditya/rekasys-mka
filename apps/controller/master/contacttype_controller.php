<?php

class ContactTypeController extends AppController {
	private $userCompanyId;
    private $userCabangId;

	protected function Initialize() {
		require_once(MODEL . "master/contacttype.php");
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.type_code", "display" => "Kode", "width" => 100);
		$settings["columns"][] = array("name" => "a.type_descs", "display" => "Keterangan", "width" => 300);

		$settings["filters"][] = array("name" => "a.type_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.type_descs", "display" => "Keterangan");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Jenis Kontak";

			if ($acl->CheckUserAccess("master.contacttype", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.contacttype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.contacttype", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.contacttype/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih contacttype terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu contacttype.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.contacttype", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.contacttype/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih contacttype terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu contacttype.",
					"Confirm" => "Apakah anda mau menghapus data contacttype yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_contacttype AS a";
            $settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ContactType $contacttype) {

		return true;
	}

	public function add() {
        $log = new UserAdmin();
        $contacttype = new ContactType();
        if (count($this->postData) > 0) {
            $contacttype->TypeCode = $this->GetPostValue("TypeCode");
            $contacttype->TypeDescs = $this->GetPostValue("TypeDescs");
            if ($this->ValidateData($contacttype)) {
                $contacttype->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $contacttype->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.contacttype','Add New Contact Type -> Kode: '.$contacttype->TypeCode.' - '.$contacttype->TypeDescs,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Jenis Kotak: %s (%s) sudah berhasil disimpan", $contacttype->TypeDescs, $contacttype->TypeCode));
                    redirect_url("master.contacttype");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.contacttype','Add New Contact Type -> Kode: '.$contacttype->TypeCode.' - '.$contacttype->TypeDescs,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("contacttype", $contacttype);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.contacttype");
        }
        $log = new UserAdmin();
        $contacttype = new ContactType();
        if (count($this->postData) > 0) {
            $contacttype->Id = $id;
            $contacttype->TypeCode = $this->GetPostValue("TypeCode");
            $contacttype->TypeDescs = $this->GetPostValue("TypeDescs");
            if ($this->ValidateData($contacttype)) {
                $contacttype->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $contacttype->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.contacttype','Update Contact Type -> Kode: '.$contacttype->TypeCode.' - '.$contacttype->TypeDescs,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data jenis kontak: %s (%s) sudah berhasil disimpan", $contacttype->TypeDescs, $contacttype->TypeCode));
                    redirect_url("master.contacttype");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.contacttype','Update Contact Type -> Kode: '.$contacttype->TypeCode.' - '.$contacttype->TypeDescs,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data jenis kontak. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $contacttype = $contacttype->LoadById($id);
            if ($contacttype == null || $contacttype->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.contacttype");
            }
        }
        $this->Set("contacttype", $contacttype);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.contacttype");
        }
        $log = new UserAdmin();
        $contacttype = new ContactType();
        $contacttype = $contacttype->LoadById($id);
        if ($contacttype == null || $contacttype->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.contacttype");
        }
        $rs = $contacttype->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.contacttype','Delete Contact Type -> Kode: '.$contacttype->TypeCode.' - '.$contacttype->TypeDescs,'-','Success');
            $this->persistence->SaveState("info", sprintf("Jenis Kontak: %s (%s) sudah dihapus", $contacttype->TypeDescs, $contacttype->TypeCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.contacttype','Delete Contact Type -> Kode: '.$contacttype->TypeCode.' - '.$contacttype->TypeDescs,'-','Success');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $contacttype->TypeDescs, $contacttype->TypeCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.contacttype");
	}
}

// End of file: contacttype_controller.php
