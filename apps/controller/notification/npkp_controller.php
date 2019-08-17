<?php
namespace Notification;

class NpkpController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("entity_id");
	}

	public function approval_pending() {
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID");
		$settings["columns"][] = array("name" => "b.name", "display" => "Jenis NPKP");
		$settings["columns"][] = array("name" => "a.doc_no", "display" => "No. Invoice");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.cash_request_date, '%d %M %Y')", "display" => "Tgl. NPKP");
		$settings["columns"][] = array("name" => "a.objective", "display" => "Tujuan NPKP");
		$settings["columns"][] = array("name" => "DATE_FORMAT(a.eta_date, '%d %M %Y')", "display" => "Tgl. Diharapkan");

		$settings["from"] =
"ac_cash_request_master AS a
	JOIN ac_cash_request_category AS b ON a.category_id = b.id";

		if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
			$settings["where"] = "a.is_deleted = 0 AND a.status < 2";
		} else {
			$settings["where"] = "a.is_deleted = 0 AND a.status < 2 AND a.entity_id = " . $this->userCompanyId;
		}

		$settings["order_by"] = "a.doc_no";

		$settings["title"] = "Daftar Official Receipt yang Belum Diproses";
		$settings["actions"][] = array("Text" => "Detail", "Url" => "accounting.cashrequest/view/%s");
		$settings["returnUrl"] = array("Text" => "Daftar NPKP", "Url" => "accounting.cashrequest");

		$dispatcher = \Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "lists", array(), $settings, null, true);
	}
}

// EoF: npkp_controller.php