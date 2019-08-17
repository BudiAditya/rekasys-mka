<?php

/**
 * Dipake untuk membuat laporan stock pergudang atau overview
 * By Default ini hanya bisa melihat stock per Company dan tidak bisa semua Company. Jadi Corporate user harus impersonate
 *
 * NOTE:
 *  - Berhubung query yang dipake disini aneh-aneh maka saya prefer tidak pakai model tetapi langsung query.
 */
class StockController extends AppController {
    private $userCompanyId;
    private $userProjectId;
    private $userProjectIds;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "inventory/stock.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
        $this->userProjectId = $this->persistence->LoadState("project_id");
	}

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.item_id", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.project_name", "display" => "Project", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Item Code", "width" => 80);
        $settings["columns"][] = array("name" => "COALESCE(a.part_no,'-')", "display" => "Part Number", "width" => 100);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Item Name & Specification", "width" => 200);
        //$settings["columns"][] = array("name" => "b.category_desc", "display" => "Category", "width" => 80);
        $settings["columns"][] = array("name" => "COALESCE(a.brand_name, '-')", "display" => "Unit Brand", "width" => 80);
        $settings["columns"][] = array("name" => "COALESCE(a.type_desc, '-')", "display" => "Unit Type", "width" => 80);
        $settings["columns"][] = array("name" => "COALESCE(a.sn_no,'-')", "display" => "Serial Number", "width" => 100);
        $settings["columns"][] = array("name" => "format(a.qty_stock,0)", "display" => "Qty Stock", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "a.uom_cd", "display" => "UOM", "width" => 60);
        $settings["columns"][] = array("name" => "COALESCE(a.gclass,'-')", "display" => "QClass", "width" => 100);
        $settings["columns"][] = array("name" => "COALESCE(a.notes,'-')", "display" => "Description", "width" => 200);

        $settings["filters"][] = array("name" => "a.item_code", "display" => "Item Code");
        $settings["filters"][] = array("name" => "a.item_name", "display" => "Item Name");
        $settings["filters"][] = array("name" => "b.category_desc", "display" => "Category");
        $settings["filters"][] = array("name" => "a.type_desc", "display" => "Unit Type");
        $settings["filters"][] = array("name" => "a.brand_name", "display" => "Unit Brand");
        $settings["filters"][] = array("name" => "a.comp_name", "display" => "Component");
        $settings["filters"][] = array("name" => "a.part_no", "display" => "Part Number");
        $settings["filters"][] = array("name" => "a.sn_no", "display" => "Serial Number");
        $settings["filters"][] = array("name" => "a.gclass", "display" => "QClass");

        if (!$router->IsAjaxRequest) {
            // UI Settings
            $acl = AclManager::GetInstance();
            $settings["title"] = "Items Stock Posisition";
            if ($acl->CheckUserAccess("stock", "view", "inventory")) {
                $settings["actions"][] = array("Text" => "Track Item", "Url" => "inventory.stock/track?item=%s&project=".$this->userProjectId, "Class" => "bt_verify", "ReqId",
                    "Error" => "Anda harus memilih item terlebih dahulu sebelum melakukan proses tracking !\nPERHATIAN: Pilih tepat 1 item.",
                    "Confirm" => "");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Stock Report", "Url" => "inventory.stock/overview", "Class" => "bt_report", "ReqId" => 0);
            }

            $settings["def_filter"] = 1;
            $settings["def_order"] = 4;
            $settings["singleSelect"] = true;
        } else {
            $settings["from"] = "vw_ic_stock_list AS a JOIN ic_item_category AS b On a.category_id = b.id";
            if ($this->userLevel < 5){
                $settings["where"] = "Locate(a.project_id,".$this->userProjectIds.")";
            }else {
                $settings["where"] = "a.entity_id = " . $this->userCompanyId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }


	public function overview() {
		require_once(MODEL . "master/project.php");
        $project = new Project();

		if (count($this->getData) > 0) {
			$projectId = $this->GetGetValue("project");
			$date = strtotime($this->GetGetValue("date"));
			$output = $this->GetGetValue("output", "web");
			if (is_int($date)) {
				// OK Conversion complete
				$date += 86399;		// Tambah 23:59:59
			} else {
				$date = time();		// Fail safe
			}
			if ($projectId == "") {
				$projectId = null;
			} else {
				$project = $project->LoadById($projectId);
			}

            if ($projectId == null || $project->EntityId == $this->userCompanyId) {
				// Proses laporan jika dia memilih mode semua gudang atau Gudangnya sama dengan user login
				$baseQuery = "SELECT aa.*, bb.item_code, bb.item_name, bb.min_qty, bb.max_qty, bb.is_discontinue, bb.part_no, bb.sn_no, bb.brand_name, bb.type_desc FROM ( %s ) AS aa JOIN vw_ic_item_master AS bb ON aa.item_id = bb.id ORDER BY bb.item_name";
				if ($projectId == null) {
					// Semua gudang based on user entity
					$this->connector->CommandText = sprintf($baseQuery, Stock::QUERY_STOCK_BY_SBU);
					$this->connector->AddParameter("?sbu", $this->userCompanyId);
				} else {
					// Specific project
					$this->connector->CommandText = sprintf($baseQuery, Stock::QUERY_STOCK_BY_WAREHOUSE);
					$this->connector->AddParameter("?projectId", $projectId);
				}
				$this->connector->AddParameter("?date", date(SQL_DATETIME, $date));

				$report = $this->connector->ExecuteQuery();
				if ($report == null) {
					// Ada error ini
					$this->Set("error", "Error while generating report: " . $this->connector->GetErrorMessage());
				}
			} else {
				// Hwee pilih gudang siapa ente ????
				$this->Set("error", "Gudang yang diminta tidak dapat ditemukan !");
				$report = null;
			}
		} else {
			$projectId = $this->userProjectId;
			$date = time();
			$output = "web";
            $project = $project->LoadById($projectId);
            // Proses laporan jika dia memilih mode semua gudang atau Gudangnya sama dengan user login
            $baseQuery = "SELECT aa.*, bb.item_code, bb.item_name, bb.min_qty, bb.max_qty, bb.is_discontinue, bb.part_no, bb.sn_no, bb.brand_name, bb.type_desc FROM ( %s ) AS aa JOIN vw_ic_item_master AS bb ON aa.item_id = bb.id ORDER BY bb.item_name";
            if ($projectId == null) {
                // Semua gudang based on user entity
                $this->connector->CommandText = sprintf($baseQuery, Stock::QUERY_STOCK_BY_SBU);
                $this->connector->AddParameter("?sbu", $this->userCompanyId);
            } else {
                // Specific project
                $this->connector->CommandText = sprintf($baseQuery, Stock::QUERY_STOCK_BY_WAREHOUSE);
                $this->connector->AddParameter("?projectId", $projectId);
            }
            $this->connector->AddParameter("?date", date(SQL_DATETIME, $date));

            $report = $this->connector->ExecuteQuery();
            if ($report == null) {
                // Ada error ini
                $this->Set("error", "Error while generating report: " . $this->connector->GetErrorMessage());
            }
		}

		if ($project == null) {
            $project = new Project();
		}
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }
        $this->Set("projects", $projects);
		$this->Set("projectId", $projectId);
		$this->Set("date", $date);
		$this->Set("output", $output);
		$this->Set("report", $report);
        $this->Set("userLevel", $this->userLevel);
	}

	/**
	 * Ini function yang berfungsi untuk tracking barang yang diminta.
	 * Walau hanya kirim id barang funtion ini akan tetap jalan dan menggunakan default value.
	 *
	 * NOTE: tracking ini tidak detail sampai perbedaan harga hanya berdasarkan item dan satuannya
	 */
	public function track() {
		$itemId = $this->GetGetValue("item");
		$startDate = strtotime($this->GetGetValue("start"));
		$endDate = strtotime($this->GetGetValue("end"));
		$projectId = $this->GetGetValue("project");
		$output = $this->GetGetValue("output", "web");
		$saldoAwal = null;
		$records = null;
		if ($projectId == "") {
			$projectId = null;
		}

		// Checking
		if (!is_int($endDate)) {
			// Default ke saat ini
			$endDate = time();
		}
		if (!is_int($startDate)) {
			// Default ke awal bulan ini
			$startDate = mktime(0, 0, 0, (int)date("n", $endDate), 1, (int)date("Y", $endDate));
		}

		// Kita baru bisa proses kalau data item dan satuan ada
		if ($itemId == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih barang terlebih dahulu sebelum melakukan tracking.");
			redirect_url("inventory.item");
		}

		require_once(MODEL . "master/project.php");
		require_once(MODEL . "inventory/item.php");

		$item = new Item();
		$item = $item->LoadById($itemId);

		// #01: Cari saldo awal barang yang dimininta
		if ($projectId == null) {
			// track item tidak berdasarkan gudang
			$project = null;

			$this->connector->CommandText = "SELECT SUM(CASE WHEN a.stock_type < 100 THEN a.qty ELSE a.qty * -1 END) AS saldo_awal FROM ic_stock AS a WHERE a.is_deleted = 0 AND a.item_id = ?itemId AND a.date < ?startDate AND a.project_id IN (SELECT id FROM cm_project WHERE entity_id = ?sbu)";
			$this->connector->AddParameter("?sbu", $this->userCompanyId);
		} else {
			//
			$project = new Project();
			$project = $project->LoadById($projectId);

			$this->connector->CommandText = "SELECT SUM(CASE WHEN a.stock_type < 100 THEN a.qty ELSE a.qty * -1 END) AS saldo_awal FROM ic_stock AS a WHERE a.is_deleted = 0 AND a.item_id = ?itemId AND a.date < ?startDate AND a.project_id = ?projectId";
			$this->connector->AddParameter("?projectId", $projectId);
		}

		$this->connector->AddParameter("?itemId", $item->Id);
		$this->connector->AddParameter("?startDate", date(SQL_DATETIME, $startDate));

		$saldoAwal = $this->connector->ExecuteScalar();
		if ($saldoAwal == null) {
			$saldoAwal = 0;		// Fail safe
		}

		// #02: Ambil semua track record barangnya sekarang....
		$stock = new Stock();
		$histories = $stock->LoadHistoriesBetween($itemId, $startDate, $endDate, $projectId);
		// OK.. jujur aja gw lom pake query join buat ambil dokumen referensinya... (masih bingung sama complex klo jadi juga)
		foreach ($histories as $stock) {
			$stock->LoadReferencedDocument();
		}

		// OK semua pemain sudah terkumpul... let's the play begin...

        if ($project == null) {
            $project = new Project();
        }
        if ($this->userLevel < 5) {
            $projects = $project->LoadAllowedProject($this->userProjectIds);
        }else{
            $projects = $project->LoadByEntityId($this->userCompanyId);
        }

		$this->Set("item", $item);
		$this->Set("projectId", $projectId);
		$this->Set("projects", $projects);
		$this->Set("startDate", $startDate);
		$this->Set("endDate", $endDate);
		$this->Set("saldoAwal", $saldoAwal);
		$this->Set("histories", $histories);
        $this->Set("userLevel", $this->userLevel);
		$this->Set("output", $output);
	}
}


// End of File: stock_controller.php
