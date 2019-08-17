<?php
class VoucherTypeController extends AppController {
	protected function Initialize() {
		require_once(MODEL . "accounting/voucher_type.php");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.voucher_cd", "display" => "Kode", "width" => 80);
		$settings["columns"][] = array("name" => "a.voucher_desc", "display" => "Nama Voucher", "width" => 250);

		$settings["filters"][] = array("name" => "a.voucher_cd", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.voucher_desc", "display" => "Nama Voucher");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();

			$settings["title"] = "Accounting Voucher Type";
/*
			if($acl->CheckUserAccess("vouchertype", "add", "common")){
				$settings["actions"][] = array("Text" => "Add", "Url" => "common.vouchertype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if($acl->CheckUserAccess("vouchertype", "edit", "common")){
				$settings["actions"][] = array("Text" => "Edit", "Url" => "common.vouchertype/edit/%s", "Class" => "bt_edit", "ReqId" => 1);
			}
			if($acl->CheckUserAccess("vouchertype", "delete", "common")){
				$settings["actions"][] = array("Text" => "Delete", "Url" => "common.vouchertype/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
			}
*/
			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "ac_voucher_type AS a";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings);
	}

	public function add() {
		$voucher = new VoucherType();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$voucher->VoucherCd = $this->GetPostValue("VoucherCd");
			$voucher->VoucherDesc = $this->GetPostValue("VoucherDesc");

			if ($this->DoInsert($voucher)) {
				$this->persistence->SaveState("info", sprintf("Data Lokasi: '%s' Dengan Kode: %s telah berhasil disimpan.", $voucher->VoucherDesc, $voucher->VoucherCd));
				redirect_url("common.vouchertype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $voucher->VoucherCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		}

		$this->Set("voucher", $voucher);
	}

	private function DoInsert(VoucherType $voucher) {
		if ($voucher->VoucherCd == "") {
			$this->Set("error", "Kode modul masih kosong");
			return false;
		}

		if ($voucher->VoucherDesc == "") {
			$this->Set("error", "Nama modul masih kosong");
			return false;
		}

		if ($voucher->Insert() == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function edit($id = null) {

		$voucher = new VoucherType();

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$voucher->Id = $this->GetPostValue("Id");
			$voucher->VoucherCd = $this->GetPostValue("VoucherCd");
			$voucher->VoucherDesc = $this->GetPostValue("VoucherDesc");

			if ($this->DoUpdate($voucher)) {
				$this->persistence->SaveState("info", sprintf("Data Jenis Accounting Voucher: '%s' Dengan Kode: %s telah berhasil diupdate.", $voucher->VoucherDesc, $voucher->VoucherCd));
				redirect_url("common.vouchertype");
			} else {
				if ($this->connector->GetHasError()) {
					if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
						$this->Set("error", sprintf("Kode: '%s' telah ada pada database !", $voucher->VoucherCd));
					} else {
						$this->Set("error", sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage()));
					}
				}
			}
		} else {
			if ($id == null) {
				$this->persistence->SaveState("error", "Anda harus memilih data lokasi sebelum melakukan edit data !");
				redirect_url("common.vouchertype");
			}
			$voucher = $voucher->FindById($id);
			if ($voucher == null) {
				$this->persistence->SaveState("error", "Jenis Voucher Accounting yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
				redirect_url("common.vouchertype");
			}
		}

		$this->Set("voucher", $voucher);
	}

	private function DoUpdate(VoucherType $voucher) {
		if ($voucher->VoucherCd == "") {
			$this->Set("error", "Kode modul masih kosong");
			return false;
		}

		if ($voucher->VoucherDesc == "") {
			$this->Set("error", "Nama modul masih kosong");
			return false;
		}

		if ($voucher->Update($voucher->Id) == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data lokasi sebelum melakukan hapus data !");
			redirect_url("common.vouchertype");
		}

		$voucher = new VoucherType();
		$voucher = $voucher->FindById($id);
		if ($voucher == null) {
			$this->persistence->SaveState("error", "Data lokasi yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("common.vouchertype");
		}

		if ($voucher->Delete($voucher->Id) == 1) {
			$this->persistence->SaveState("info", sprintf("Data Lokasi: '%s' Dengan Kode: %s telah berhasil dihapus.", $voucher->VoucherDesc, $voucher->VoucherCd));
			redirect_url("common.vouchertype");
		} else {
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data lokasi: '%s'. Message: %s", $voucher->VoucherDesc, $this->connector->GetErrorMessage()));
		}
		redirect_url("common.vouchertype");
	}
}
