<?php
namespace Notification;

class VoucherController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function approval_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Voucher");
		$settings["columns"][] = array("name" => "a.note", "display" => "Ket. Voucher");
		$settings["columns"][] = array("name" => "b.description", "display" => "Jenis Voucher");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.voucher_date, '%d %M %Y')", "display" => "Tgl. Voucher");
		$settings["columns"][] = array("name" => "a.voucher_source", "display" => "Asal Voucher");
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status");

		$settings["from"] = "ac_voucher_master AS a JOIN cm_doctype AS b ON a.doc_type_id = b.id JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'voucher_status'";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.status < 4";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.status < 4 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "a.doc_no";

		$settings["title"] = "Daftar Voucher yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "accounting.voucher/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar Voucher", "Url" => "accounting.voucher");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}

	public function hris_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Voucher");
		$settings["columns"][] = array("name" => "a.note", "display" => "Ket. Voucher");
		$settings["columns"][] = array("name" => "b.description", "display" => "Jenis Voucher");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.voucher_date, '%d %M %Y')", "display" => "Tgl. Voucher");
		$settings["columns"][] = array("name" => "a.voucher_source", "display" => "Asal Voucher");
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Status");

		$settings["from"] = "ac_voucher_master AS a JOIN cm_doctype AS b ON a.doc_type_id = b.id JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'voucher_status'";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.voucher_source = 'HRIS' AND a.status < 4";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.voucher_source = 'HRIS' AND a.status < 4 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "a.doc_no";

		$settings["title"] = "Daftar Voucher Posting Otomatis HRIS yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "accounting.voucher/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar Voucher", "Url" => "accounting.voucher");

		\Dispatcher::CreateInstance()->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: voucher_controller.php