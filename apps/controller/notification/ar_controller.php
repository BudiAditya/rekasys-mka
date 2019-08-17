<?php
namespace Notification;

class ArController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function approval_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Nama Customer");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Invoice");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.doc_date, '%d %M %Y')", "display" => "Tgl. Dokumen");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.trx_date, '%d %M %Y')", "display" => "Tgl. Transaksi");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.due_date, '%d %M %Y')", "display" => "Tgl. Jth. Tempo");

		$settings["from"] = "ar_ivmaster AS a JOIN ar_debtor_master AS b ON a.debtor_id = b.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 0";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "b.debtor_name";

		$settings["title"] = "Daftar Invoice AR yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "ar.invoice/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar AR Invoice", "Url" => "ar.invoice");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}

	public function tax_invoice_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Nama Customer");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Invoice");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.doc_date, '%d %M %Y')", "display" => "Tgl. Dokumen");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.trx_date, '%d %M %Y')", "display" => "Tgl. Transaksi");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.due_date, '%d %M %Y')", "display" => "Tgl. Jth. Tempo");

		$settings["from"] = "ar_ivmaster AS a JOIN ar_debtor_master AS b ON a.debtor_id = b.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 1 AND a.is_faktur = 0";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 1 AND a.is_faktur = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "b.debtor_name";

		$settings["title"] = "Daftar Invoice AR yang Tanpa Faktur Pajak";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "ar.invoice/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar Tax Invoice", "Url" => "tax.invoice");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}

	public function last_30_days() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Nama Customer");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Invoice");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.doc_date, '%d %M %Y')", "display" => "Tgl. Dokumen");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.trx_date, '%d %M %Y')", "display" => "Tgl. Transaksi");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.due_date, '%d %M %Y')", "display" => "Tgl. Jth. Tempo");
		$settings["columns"][] = array("name" => "DATEDIFF(a.due_date, CURRENT_DATE)", "display" => "Selisih Hari");

		$settings["from"] = "ar_ivmaster AS a JOIN ar_debtor_master AS b ON a.debtor_id = b.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 1 AND DATEDIFF(a.due_date, CURRENT_DATE) <= 30";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 1 AND DATEDIFF(a.due_date, CURRENT_DATE) <= 30 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "DATEDIFF(a.due_date, CURRENT_DATE) DESC";

		$settings["title"] = "Daftar Invoice AR yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "ar.invoice/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar AR Invoice", "Url" => "ar.invoice");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: ar_controller.php