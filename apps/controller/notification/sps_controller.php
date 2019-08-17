<?php
namespace Notification;

class SpsController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function approval_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.customer_name", "display" => "Nama Customer");
		$settings["columns"][] = array("name" => "a.nm_toko", "display" => "Nama Toko");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.sp_date, '%d %M %Y')", "display" => "Tgl. SP");
		$settings["columns"][] = array("name" => "c.sales_name", "display" => "Nama Sales");

		$settings["from"] =
"sm_sp_master AS a
	JOIN sm_customermaster AS b ON a.customer_id = b.id
	JOIN sm_salesperson AS c ON a.salesperson_id = c.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.sp_status = 0";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.sp_status = 0 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "b.customer_name";

		$settings["title"] = "Daftar Transaksi SP Penjualan yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "sm.sps/view_master/%s");
		$settings["returnUrl"] = array("Text" => "Daftar SP Penjualan", "Url" => "sm.sps");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: sps_controller.php