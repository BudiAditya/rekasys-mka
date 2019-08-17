<?php
namespace Notification;

class OrController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function approval_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Nama Debtor");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Invoice");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.doc_date, '%d %M %Y')", "display" => "Tgl. Dokumen");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.trx_date, '%d %M %Y')", "display" => "Tgl. Transaksi");
		$settings["columns"][] = array("name" => "c.short_desc", "display" => "Metode Bayar");
		$settings["columns"][] = array("name" => "a.bank_name", "display" => "Nama Bank");
		$settings["columns"][] = array("name" => "a.cbg_no", "display" => "No. Referensi");

		$settings["from"] =
"ar_receipt_master AS a
	JOIN ar_debtor_master AS b ON a.debtor_id = b.id
	JOIN sys_status_code AS c ON a.payment_mode = c.code AND c.key = 'payment_mode'";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 1";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.doc_status = 1 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "b.debtor_name";

		$settings["title"] = "Daftar Official Receipt yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "ar.receipt/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar Official Receipt", "Url" => "ar.receipt");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: or_controller.php