<?php
namespace Notification;

class TenantController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function approval_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Debtor");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.booking_date, '%d %M %Y')", "display" => "Tgl. Booking");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.contract_date, '%d %M %Y')", "display" => "Tgl. Kontrak");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.open_date, '%d %M %Y')", "display" => "Tgl. Buka");

		$settings["from"] = "ar_debtor_transaction AS a JOIN ar_debtor_master AS b ON a.debtor_id = b.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.trx_status = 0";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.trx_status = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "b.debtor_name";

		$settings["title"] = "Daftar Transaksi Tenant yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "tm.tenant/view_master/%s");
        $settings["actions"][] = array("Text" => "Approve", "Url" => "tm.tenant/approve/%s");
		$settings["returnUrl"] = array("Text" => "Daftar Tenant", "Url" => "tm.tenant");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}

	public function last_30_days() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.debtor_name", "display" => "Debtor");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.start_date, '%d %M %Y')", "display" => "Tgl. Mulai");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.end_date, '%d %M %Y')", "display" => "Tgl. Selesai");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.open_date, '%d %M %Y')", "display" => "Tgl. Buka");
		$settings["columns"][] = array("name" => "DATEDIFF(a.end_date, CURRENT_DATE)", "display" => "Selisih Hari", "align" => "right");

		$settings["from"] = "ar_debtor_transaction AS a JOIN ar_debtor_master AS b ON a.debtor_id = b.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.terminate_status = 0 AND DATEDIFF(a.end_date, CURRENT_DATE) <= 30";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.terminate_status = 0 AND DATEDIFF(a.end_date, CURRENT_DATE) <= 30 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "DATEDIFF(a.end_date, CURRENT_DATE) DESC";

		$settings["title"] = "Daftar Tenant Akan Selesai Kontrak Dalam 30 Hari Mendatang";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "tm.tenant/view_master/%s");
		$settings["returnUrl"] = array("Text" => "Daftar Tenant", "Url" => "tm.tenant");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: tenant_controller.php