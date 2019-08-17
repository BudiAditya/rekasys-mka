<?php
namespace Notification;

class BillingController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function tenant_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "c.debtor_name", "display" => "Nama Debtor");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.billing_date, '%d-%m-%Y')", "display" => "Tgl. Penagihan");
		$settings["columns"][] = array("name" => "a.description", "display" => "Keterangan");

		$settings["from"] =
"ar_billing_schedule AS a
	JOIN ar_debtor_transaction AS b ON a.debtor_trans_id = b.id
	JOIN ar_debtor_master AS c ON b.debtor_id = c.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.is_sales_trx = 0 AND a.billing_status < 3 AND DATEDIFF(a.billing_date, CURRENT_DATE) <= 30";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.is_sales_trx = 0 AND a.billing_status < 3 AND DATEDIFF(a.billing_date, CURRENT_DATE) <= 30 AND b.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "a.billing_date, c.debtor_name";

		$settings["title"] = "Daftar Billing Schedule yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "ar.billing/detail/%s");
		$settings["returnUrl"] = array("Text" => "Maintain Billing Schedule", "Url" => "ar.billing/maintain");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}

	public function sp_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "c.customer_name", "display" => "Nama Customer");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.billing_date, '%d-%m-%Y')", "display" => "Tgl. Penagihan");
		$settings["columns"][] = array("name" => "a.description", "display" => "Keterangan");
		$settings["columns"][] = array("name" => "b.lot_no", "display" => "Lot");

		$settings["from"] =
"ar_billing_schedule AS a
	JOIN sm_sp_master AS b ON a.debtor_trans_id = b.id
	JOIN sm_customermaster AS c ON b.customer_id = c.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.is_sales_trx = 1 AND a.billing_status < 3 AND DATEDIFF(a.billing_date, CURRENT_DATE) <= 30";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.is_sales_trx = 1 AND a.billing_status < 3 AND DATEDIFF(a.billing_date, CURRENT_DATE) <= 30 AND b.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "a.billing_date, c.customer_name";

		$settings["title"] = "Daftar Billing Schedule yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Pembayaran", "Url" => "ar.payment/add/%s");
		$settings["actions"][] = array("Text" => "Edit", "Url" => "ar.billing/edit/%s");
		$settings["returnUrl"] = array("Text" => "Maintain Billing Schedule Sales", "Url" => "ar.billing/maintain_sales");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: billing_controller.php