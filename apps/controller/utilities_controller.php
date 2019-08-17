<?php
/**
 * Controller yang berfungsi banyak dan akan dipanggil oleh controller lainnya menggunakan Dispatcher::Dispatch()
 * Ada 2 Fungsi Utama yaitu search()/flexigrid() dan lists()/batch()
 * Yang pertama untuk pencarian dan yang ke-2 untuk listing data tanpa pencarian
 * NOTE: batch() akan mirip dengan lists() tetapi disertai kemampuan untuk processing secara batch
 *
 * == SEARCH / LIST SETTINGS ==
 * $settings["columns"][] = array("name" => "col_name", "display" => "display_name"[, "sortable" => true][, "width" => int][, "align" => left|center|right][, "overrideSort" => "col_nam"])
 *  - Untuk mode LIST hanya "name", "display", dan "align" yang terpakai
 *  - Untuk kolom-kolom yang ditampilkan (Kolom pertama otomatis di hidden dan HARUS BERUPA ID yang digunakan sebagai link detail):
 *  - Data-data kolom juga akan digunakan sebagai query select
 *  - Juga akan digunakan pada proses ORDER BY jika sortable == true
 *  - Jika pada ORDER BY terjadi perbedaan algoritma maka gunakan overrideSort ! key ini akan digunakan pada proses ORDER BY
 *    key diatas sangat berguna untuk sorting tanggal ! karena ditampilkan dalam format text tetapi untuk sorting harus berdasarkan tanggal.
 * $settings["dBasePool"] = "pool_name"
 *  - Jika mau menggunakan database yang non-default yang dispecify di 'database.config.php'. Jika diberikan maka akan menggunakan pool yang diberikan.
 *
 * == SEARCH SETTINGS ==
 * $settings["filters"][] = array("name" => "col_name", "display" => "display_name"[, "numeric" => false])
 *  - Untuk kolom-kolom yang digunakan untuk filter data (digunakan pada WHERE)
 *  - Pada halaman pencarian user dapat mencari data berdasarkan filter-filter yang diberikan diatas
 *  - Khusus PostgreSQL tidak dapat melakukan LIKE pada kolom integer secara langsung harus di proses CAST dahulu
 *
 * == LIST SETTINGS ==
 * $settings["order_by"] = untuk digunakan pada query ORDER BY
 * $settings["newDataUrl] = relative link untuk membuat data baru
 * $settings["returnUrl"] = array("Url" => "relative link untuk kembali", "Text" => "text to be displayed")
 *
 * == SEARCH / LIST QUERY SETTINGS ==
 * $settings["from"] = query FROM yang akan digunakan sebagai basis data pencarian. con: 'table1 AS a JOIN table2 AS b ON a.id = b.ref_id' (tidak ada keyword FROM)
 * $settings["where"] = tambahan query where selain filter where yang diatas. Jika value bukan null maka clause WHERE disini akan di AND dengan filter dari user.
 * 						Query where ini akan proses dengan '(' dan ')' baru di AND dengan filter sehingga pada where ini bisa dimasukkan keyword OR tanpa merubah hasil
 *
 * == SEARCH / LIST USER INTERFACE ==
 * $settings["title"] = Judul halaman pencarian
 * -- 'action' key obsoleted and removed --
 * $settings["action"] = Relative URL ketika user memilih/klik data dari hasil pencarian. HARUS memiliki TEPAT 1 '%s' yang mana akan disisipkan kolom pertama dari $settings["columns"]
 * -- 'actions' key now used instead of action --
 * $settings["actions"][] = array yang berisi data-data yang akan digunakan untuk pembuatan link hasil search
 * 							KEY in MegaPMS Flexigrid:
 * 							 - 'Text'	: Toolbar Button Text
 * 							 - 'Url'	: associated link if user click the button
 * 							 - 'Class'	: class name for flexigrid toolbar button
 * 							 - 'ReqId'	: 0 = No ID required, 1 = Exactly 1 ID, 2 = Multiple ID is allowed
 * 										  (Jika 1 maka akan dikirim dengan parameter, Jika > 1 akan dikirim melalui GET dengan nama id)
 * 							 - 'Error'	: Error message kalau user belum memilih data (jika ReqId = true)
 * 							 - 'Confirm': Confirm message kalau user akan melakukan eksekusi data (jika ReqId = true)
 * $settings["recordPerPage"] = [OPTIONAL] Jumlah record yang ditampilkan dalam 1 halaman (default: 20)
 * $settings["singleSelect"] = [OPTIONAL] [FlexigridOnly] menentukan apakah boleh memilih data > 1 / tidak (default = true)
 *
 * == OPTIONAL SEARCH SETTINGS ==
 * $settings["def_query"] = Default untuk data query (berisi string bebas)
 * $settings["def_query2"] = Default untuk data query Filter ke-2
 * $settings["def_filter"] = Default untuk data yang difilter (berisi int dengan max adalah jumlah kolom pada $settings["columns"])
 * $settings["def_order"] = Default pengurutan data yang digunakan (berisi int dengan max adalah jumlah kolom pada $settings["columns"])
 * $settings["def_direction"] = Default arah pengurutan yang digunakan (berisi 'asc' atau 'desc')
 */

class UtilitiesController extends AppController {
	private $searchSettings;
	private $listSettings;

	protected function Initialize() { }

	/**
	 * Berfungsi untuk validasi setting pencarian data.
	 * Controller ini berfungsi umum / global berdasarkan setting yang diberikan. Oleh karena itu settingan-nya tidak boleh salah / perlu validasi
	 *
	 * @param bool $flexigridMode (Jika menggunakan flexigrid maka ini harus di set menjadi true karena flexigrid memerlukan settingan tambahan)
	 * @throws Exception when settings is invalid.
	 */
	private function CheckSearchSettings($flexigridMode = false) {
		// Kita cek untuk beberapa key array yang harus ada !
		if (!isset($this->searchSettings["columns"]) || !is_array($this->searchSettings["columns"])) {
			throw new Exception("Invalid Search Settings key: 'columns'");
		}

		if (!isset($this->searchSettings["filters"]) || !is_array($this->searchSettings["filters"])) {
			throw new Exception("Invalid Search Settings key: 'filters'");
		}

		// Setting yang dibawah sudah optional
		if (!isset($this->searchSettings["where"])) {
			$this->searchSettings["where"] = null;
		}
		if (!isset($this->searchSettings["title"])) {
			$this->searchSettings["title"] = "Pencarian Data";
		}
		if (!isset($this->searchSettings["subTitle"])) {
			$this->searchSettings["subTitle"] = null;
		}
		if (!isset($this->searchSettings["actions"])) {
			$this->searchSettings["actions"] = array();
		}
		if (!isset($this->searchSettings["recordPerPage"])) {
			$this->searchSettings["recordPerPage"] = 15;
		}
		if (isset($this->searchSettings["dBasePool"])) {
			$this->connector = ConnectorManager::GetPool($this->searchSettings["dBasePool"]);
			if ($this->connector == null) {
				throw new Exception("Database pool not Found ! Expected Pool: " . $this->searchSettings["dBasePool"]);
			}
		}

		// Proses data-data optional
		foreach ($this->searchSettings["columns"] as &$column) {
			// Sortable column
			if (!array_key_exists("sortable", $column)) {
				$column["sortable"] = true;
			}

			// Column alignment
			if (!array_key_exists("align", $column)) {
				$column["align"] = "left";
			}

			if (!$flexigridMode) {
				// array key dibawah khusus dipakai di flexigrid saja
				continue;
			}

			// Column width
			if (!array_key_exists("width", $column)) {
				$column["width"] = 120;
			}
		}
		foreach ($this->searchSettings["filters"] as &$filter) {
			if (!isset($filter["numeric"])) {
				$filter["numeric"] = false;
			}
		}

		if (!$flexigridMode) {
			return;
		}

		// OK additional checking for flexigrid mode
		foreach ($this->searchSettings["actions"] as &$action) {
			if (!isset($action["ReqId"])) {
				$action["ReqId"] = 1;
			}
		}
	}

	private function CheckListSettings() {
		// Kita cek untuk beberapa key array yang harus ada !
		if (!isset($this->listSettings["columns"]) || !is_array($this->listSettings["columns"])) {
			throw new Exception("Invalid List Settings key: 'columns'");
		}
		if (!isset($this->listSettings["from"])) {
			throw new Exception("Invalid List Settings key: 'from'");
		}
		if (!isset($this->listSettings["order_by"])) {
			throw new Exception("Invalid List Settings key: 'order_by'");
		}

		// OPTIONAL settings
		if (!isset($this->listSettings["where"])) {
			$this->listSettings["where"] = null;
		}
		if (!isset($this->listSettings["title"])) {
			$this->listSettings["title"] = "Listing Data";
		}
		if (!isset($this->listSettings["subTitle"])) {
			$this->listSettings["subTitle"] = null;
		}
		if (!isset($this->listSettings["recordPerPage"])) {
			$this->listSettings["recordPerPage"] = 20;
		}
		if (!isset($this->listSettings["newDataUrl"])) {
			$this->listSettings["newDataUrl"] = null;
		}
		if (!isset($this->listSettings["returnUrl"])) {
			$this->listSettings["returnUrl"] = null;
		}

		// Proses data-data optional
		foreach ($this->listSettings["columns"] as &$column) {
			// Column alignment
			if (!array_key_exists("align", $column)) {
				$column["align"] = "left";
			}
		}
	}

	/**
	 * Yang ini memiliki fungsi yang sama dengan search() BAHKAN SAMA PERSIS hanya berbeda pada tampilan
	 * Untuk mode ini kita menggunakan bantuan FLexigrid untuk proses penampilan datanya sedangkan search murni di coding di level VIEW
	 * Method ini dibuat karena beberapa program sudah terbiasa dengan flexigrid sehingga sengaja digunakan.
	 * Hasil yang ditampilkan 100% SAMA dengan yang search jadi hanya berbeda pada cita rasa tampilan dan fungsionalitas tombol back SUDAH IMPLEMENTASI
	 *
	 * Berhuhubung pake Flexigrid maka kita harus mempersiapkan 2 URL (1 untuk VIEW dan 1 lagi untuk generate data)
	 * TAPI KITA DISINI HANYA MENGGUNAKAN 1 URL jadi kita detect dari Request Origin-nya jika AJAX maka request data.
	 */
	public function flexigrid() {
		$this->searchSettings = $this->namedParams;
		$this->CheckSearchSettings(true);

		require_once(SYSTEM_HELPER . "flexigrid_helper.php");
		if (Router::GetInstance()->IsAjaxRequest) {
			// AJAX Request Detected ! (Flexigrid meminta data....)
			$this->LoadDataFg();
		} else {
			// Bikin halaman utama...
			$this->CreateFlexigrid();
		}
	}

	/**
	 * Berfungsi untuk membuat tampilan flexigrid pada web-browser.
	 * Dipanggil oleh flexigrid() jika request origin-nya BUKAN AJAX
	 *
	 * Kenapa tidak dibuat 2 url yang berbeda ? Biar ga pusing liatnya (misal: flexigrid() dan load_flexigrid())
	 *  1. Biar ACL nya tidak banyak (2 function ini 1 paket jika 1 deny maka 1 lagi harus deny jika tidak maka tidak make sense)
	 *  2. Ini global controller. Jika ada fungsi load_flexigrid() disini maka di controller lain juga harus ada.
	 * 		NOTE: Jika ditaruh disini maka di controller lain kita cukup merubah Dispatch() dari "search" ke "flexigrid" jika tidak disini maka di masing-masing controller harus detect request origin-nya lalu dispatch method yang sesuai.
	 */
	private function CreateFlexigrid() {
		$fg = new FlexigridHelper();
		$appHelper = new AppHelper();
		$router = Router::GetInstance();
		$eventHandlers = array();

		$fg->AutoLoad = false;
		$fg->CustomConfigs["rpOptions"] = array(10, 15, 20, 30);
		$fg->RecordPerPage = $this->searchSettings["recordPerPage"];
		$fg->Url = $appHelper->url($router->Fqn, $router->MethodName);
		$fg->Height = isset($this->searchSettings["height"]) ? $this->searchSettings["height"] : "400";
		$fg->IsSingleSelect = isset($this->searchSettings["singleSelect"]) ? $this->searchSettings["singleSelect"] : true;

		$fg->CustomConfigs["query"] = isset($this->searchSettings["def_query"]) ? $this->searchSettings["def_query"] : "";
		$fg->CustomConfigs["condition"] = isset($this->searchSettings["def_condition"]) ? $this->searchSettings["def_condition"] : "";
		$fg->CustomConfigs["query2"] = isset($this->searchSettings["def_query2"]) ? $this->searchSettings["def_query2"] : "";
		$fg->SortBy = isset($this->searchSettings["def_order"]) ? $this->searchSettings["def_order"] : 1;
		$fg->SortOrder = isset($this->searchSettings["def_direction"]) ? $this->searchSettings["def_direction"] : "asc";
		$fg->CustomConfigs["onSuccess"] = "func:searchTable_Success";

		foreach ($this->searchSettings["actions"] as $idx => $action) {
			$fgButtonDefinition = array();
			$fgButtonDefinition[0] = isset($action["Text"]) ? $action["Text"] : "Button " . ($idx + 1);
			$fgButtonDefinition[1] = isset($action["Class"]) ? $action["Class"] : "";
			$fgButtonDefinition[2] = "fgButton_Click";

			$fg->Buttons[] = $fgButtonDefinition;

			// Persiapkan object-object untuk process javascriptnya
			$jsonDefinition = array();
			$jsonDefinition["ReqId"] = $action["ReqId"];
			$jsonDefinition["Url"] = $appHelper->site_url($action["Url"]);
			$jsonDefinition["Error"] = isset($action["Error"]) ? $action["Error"] : null;
			$jsonDefinition["Confirm"] = isset($action["Confirm"]) ? $action["Confirm"] : null;
			$jsonDefinition["Target"] = isset($action["Target"]) ? $action["Target"] : "_self";

			$eventHandlers[] = $jsonDefinition;
		}

		// Columns conversion...
		foreach ($this->searchSettings["columns"] as $idx => $column) {
			if ($idx == 0) {
				$fg->Columns[$idx] = array("No.", 30, false, "right");
			} else {
				$fg->Columns[$idx]  = array($column["display"], $column["width"], $column["sortable"], $column["align"]);
			}
		}

		// Filter conversion...
		$default = isset($this->searchSettings["def_filter"]) ? $this->searchSettings["def_filter"] : 0;
		$default2 = isset($this->searchSettings["def_filter2"]) ? $this->searchSettings["def_filter2"] : 0;
		foreach ($this->searchSettings["filters"] as $idx => $filter) {
			if ($idx == $default) {
				$fg->SearchFilters[$idx] = array($filter["display"], true);
			} else if ($idx == $default2) {
				$fg->SearchFilters[$idx] = array($filter["display"], false, true);
			} else {
				$fg->SearchFilters[$idx] = array($filter["display"], false, false);
			}
		}

		$this->Set("scriptFg", $fg->CompileJavaScript());
		$this->Set("settings", $this->searchSettings);
		$this->Set("searchUrl", $fg->Url);
		$this->Set("eventHandlers", $eventHandlers);

		// Harus menampilkan info / error
		if($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	/**
	 * Berfungsi untuk loading data yang akan dimuat oleh flexigrid.
	 * Dipanggil oleh flexigrid() jika request origin-nya adalah AJAX
	 */
	private function LoadDataFg() {
		// Render VIEW nya harus dimatiin karena ini kita akan output lsg pake print json_encode...
		Dispatcher::GetInstanceAt($this->dispatcherSequence - 1)->SuppressNextSequence(true);

		// OK mulai scripting...
		$fg = new FlexigridHelper();
		$request = $fg->ValidateData();
		if ($request["sortBy"] == "null") {
			$request["sortBy"] = 1;
		}

		$select = null;
		$from = null;
		$where = null;
		$orderBy = null;
		$limit = null;
		$offset = null;

		foreach ($this->searchSettings["columns"] as $row) {
			$select .= ", " . $row["name"];
		}
		$select = substr($select, 2);
		$from = $this->searchSettings["from"];
		// Format $where = ([filter from setting]) AND ([user filter [AND|OR user filter 2]])
		$where = empty($this->searchSettings["where"]) ? '(' : "(" . $this->searchSettings["where"] . ") AND (";
		if ($request["query"] === null) {
			// Jangan pakai filter....
			$where .= "TRUE";
		} else {
			$where .= $this->searchSettings["filters"][$request["filterBy"]]["name"] . " LIKE ?query";
		}


		// OK NEW FEATURE from Flexigrid HACK Double Search Term
		if ($request["condition"] == "a") {
			$where .= " AND ";
		} else if ($request["condition"] == "o") {
			$where .= " OR ";
		}
		if ($request["condition"] != null) {
			$where .= $this->searchSettings["filters"][$request["filterBy2"]]["name"] . " LIKE ?query2";
		}
		$where .= ")";

		$sortIdx = $request["sortBy"];
		if (isset($this->searchSettings["columns"][$sortIdx]["overrideSort"])) {
			$columnName = $this->searchSettings["columns"][$sortIdx]["overrideSort"];
		} else {
			$columnName = $this->searchSettings["columns"][$sortIdx]["name"];
		}
		$orderBy = $columnName . ($request["sortOrder"] == "desc" ? " DESC" : " ASC");
		$limit = $request["recordPerPage"];
		$offset = ($request["page"] - 1) * $request["recordPerPage"];

		// Hwee persiapan query beres....
		// #01: Query Hitung !
		$query =
"SELECT COUNT(%s)
FROM %s
WHERE %s";
		$this->connector->CommandText = sprintf($query, $this->searchSettings["columns"][0]["name"], $from, $where);
		$this->connector->AddParameter("?query", "%" . $request["query"] . "%");
		$this->connector->AddParameter("?query2", "%" . $request["query2"] . "%");
		// Tambahan kalo sudah ada hasil pencarian
		$request["totalRecords"] = (int)$this->connector->ExecuteScalar();
		//var_dump($this->connector->GenerateLastQuery());

		// #02: Query Pencarian !
		$query =
"SELECT %s
FROM %s
WHERE %s
ORDER BY %s
LIMIT %d
OFFSET %d";
		$this->connector->CommandText = sprintf($query, $select, $from, $where, $orderBy, $limit, $offset);
		$this->connector->AddParameter("?query", "%" . $request["query"] . "%");
        //var_dump($this->connector->GenerateLastQuery());
		$rs = $this->connector->ExecuteQuery();

		// Sekarang bikin hasil pencarian untuk flexigridnya
		$rows = array();
		if ($rs) {
			// Walah-walah... ini nge hack flexigridnya ajaib juga wakakakak karena kolom pertama dibuat jadi ID untuk row flexigrid
			// Ya maklumlah... gw bikin kan dari helper CI (**** off to maintain ci compatibility)
			$maxColumn = count($this->searchSettings["columns"]);
			$no = ($request["page"] - 1) * $request["recordPerPage"];

			while ($row = $rs->FetchRow()) {
				$no++;

				$temp = array();
				$temp[] = $row[0];
				$temp[] = $no . ".";
				for ($i = 1; $i < $maxColumn; $i++) {
					$temp[] = $row[$i];
				}

				$rows[] = $temp;
			}
		}
		print($fg->CompileJsonResult($request["page"], $request["totalRecords"], $rows));
	}

	///
	///	NOTICE: Codes below are declared as private because it was ported from HRIS and may be will not implemented
	///

	/**
	 * PORTED FROM HRIS-Renewal.... Not implemented yet (will use flexigrid mode for PMS)
	 *
	 * Berfungsi utuk membuat halaman pencarian sekaligus proses pencarian data.
	 *  - Jika tidak ada data yang dikirim (melalui GET method) maka akan menampilkan halaman pencarian
	 *  - Jika ada parameter yang dikirim makan akan melakukan proses pencarian berdasarkan request yang dikirim oleh user
	 */
	private function search() {
		$this->searchSettings = $this->namedParams;
		$this->CheckSearchSettings();

		$request = array();
		if (count($this->getData) > 0) {
			// ada data yang dikirim oleh user... kita proses pencarian
			$request["page"] = $this->GetGetValue("p", 1);
			$request["query"] = $this->GetGetValue("q");
			$request["filter"] = (int)$this->GetGetValue("f", 0);
			$request["order"] = (int)$this->GetGetValue("o", 1);
			$request["direction"] = $this->GetGetValue("d", "a");

			// Check vailiditas...
			if ($request["filter"] > count($this->searchSettings["filters"])) {
				$request["filter"] = 0;
			}
			if ($request["order"] > count($this->searchSettings["columns"])) {
				$request["order"] = 0;
			}

			$select = "";
			$from = null;
			$where = null;
			$orderBy = null;
			$limit = null;

			foreach ($this->searchSettings["columns"] as $row) {
				$select .= ", " . $row["name"];
			}
			$select = substr($select, 2);
			$from = $this->searchSettings["from"];
			$where = empty($this->searchSettings["where"]) ? '' : "(" . $this->searchSettings["where"] . ") AND ";
			$where .= $this->searchSettings["filters"][$request["filter"]]["name"] . " LIKE ?query";
			$orderBy = $this->searchSettings["columns"][$request["order"]]["name"] . ($request["direction"] == "d" ? " DESC" : " ASC");
			$limit = sprintf("%d, %d", ($request["page"] - 1) * $this->searchSettings["recordPerPage"], $this->searchSettings["recordPerPage"]);

			// Hwee persiapan query beres....
			// #01: Query Hitung Jumlah data total (terfilter tapi tidak ada LIMIT)!
			$query =
				"SELECT COUNT(%s)
FROM %s
WHERE %s";
			$this->connector->CommandText = sprintf($query, $this->searchSettings["columns"][0]["name"], $from, $where);
			$this->connector->AddParameter("?query", "%" . $request["query"] . "%");
			// Tambahan kalo sudah ada hasil pencarian
			$this->searchSettings["totalRecords"] = (int)$this->connector->ExecuteScalar();
			$this->searchSettings["totalPages"] = ceil($this->searchSettings["totalRecords"] / $this->searchSettings["recordPerPage"]);
			//var_dump($this->connector->GenerateLastQuery());

			// #02: Query Pencarian Data (ini query baru mengambil data yang sesungguhnya) !
			$query =
				"SELECT %s
FROM %s
WHERE %s
ORDER BY %s
LIMIT %s";
			$this->connector->CommandText = sprintf($query, $select, $from, $where, $orderBy, $limit);
			$this->connector->AddParameter("?query", "%" . $request["query"] . "%");
			$rs = $this->connector->ExecuteQuery();
			//var_dump($this->connector->GenerateLastQuery());

			$result = array();
			if ($rs != null) {
				while ($row = $rs->FetchRow()) {
					$result[] = $row;
				}
			}
		} else {
			$result = null;

			$request["page"] = 1;
			// Hmm mau override beberapa value untuk default pencarian...
			$request["query"] = isset($this->searchSettings["def_query"]) ? $this->searchSettings["def_query"] : "";
			$request["filter"] = isset($this->searchSettings["def_filter"]) ? $this->searchSettings["def_filter"] : 0;
			$request["order"] = isset($this->searchSettings["def_order"]) ? $this->searchSettings["def_order"] : 0;
			$request["direction"] = isset($this->searchSettings["def_direction"]) ? $this->searchSettings["def_direction"] : "asc";
		}
		$this->Set("searchLink", Router::GetInstance()->Fqn . "/" . Router::GetInstance()->MethodName);
		$this->Set("settings", $this->searchSettings);
		$this->Set("request", $request);
		$this->Set("result", $result);

		// Harus menampilkan info / error
		if($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	/**
	 * PORTED FROM HRIS-Renewal.... Not implemented yet
	 *
	 * Berfungsi untuk menampilkan data saja tanpa disertai kemampuan untuk pencarian
	 * Digunakan untuk meng-listing sesuatu yang sudah pasti
	 */
	public function lists() {
		$this->listSettings = $this->namedParams;
		$this->CheckListSettings();

		$request = array();
		$request["page"] = $this->GetGetValue("p", 1);

		// Mari kita bikin querynya...
		$select = "";
		$from = null;
		$where = null;
		$orderBy = null;
		$limit = null;

		foreach ($this->listSettings["columns"] as $row) {
			$select .= ", " . $row["name"];
		}
		$select = substr($select, 2);
		$from = $this->listSettings["from"];
		$where = empty($this->listSettings["where"]) ? "1=1" : $this->listSettings["where"];
		$orderBy = $this->listSettings["order_by"];
		$limit = sprintf("%d, %d", ($request["page"] - 1) * $this->listSettings["recordPerPage"], $this->listSettings["recordPerPage"]);

		// Khusus untuk Listing Kita perbolehkan tampilkan semua data dengan mode recordPerPage = 0
		if ($this->listSettings["recordPerPage"] === 0) {
			// #01: Query Hitung Jumlah data total (terfilter tapi tidak ada LIMIT)!
			$query =
"SELECT COUNT(%s)
FROM %s
WHERE %s";
			$this->connector->CommandText = sprintf($query, $this->listSettings["columns"][0]["name"], $from, $where);
			$this->listSettings["totalRecords"] = (int)$this->connector->ExecuteScalar();
			$this->listSettings["totalPages"] = 1;

			// #02: Query Pencarian Data (ini query baru mengambil data yang sesungguhnya) !
			$query =
"SELECT %s
FROM %s
WHERE %s
ORDER BY %s";
			$this->connector->CommandText = sprintf($query, $select, $from, $where, $orderBy);
		} else {
			// Hwee persiapan query beres....
			// #01: Query Hitung Jumlah data total (terfilter tapi tidak ada LIMIT)!
			$query =
"SELECT COUNT(%s)
FROM %s
WHERE %s";
			$this->connector->CommandText = sprintf($query, $this->listSettings["columns"][0]["name"], $from, $where);
			$this->listSettings["totalRecords"] = (int)$this->connector->ExecuteScalar();
			$this->listSettings["totalPages"] = ceil($this->listSettings["totalRecords"] / $this->listSettings["recordPerPage"]);

			// #02: Query Pencarian Data (ini query baru mengambil data yang sesungguhnya) !
			$query =
"SELECT %s
FROM %s
WHERE %s
ORDER BY %s
LIMIT %s";
			$this->connector->CommandText = sprintf($query, $select, $from, $where, $orderBy, $limit);
		}

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchRow()) {
				$result[] = $row;
			}
		}

		// OK Completed...
		$appHelper = new AppHelper();
		$router = Router::GetInstance();
		$this->Set("listLink", $appHelper->url($router->Fqn, $router->MethodName, $router->Parameters));
		$this->Set("settings", $this->listSettings);
		$this->Set("request", $request);
		$this->Set("result", $result);

		// Harus menampilkan info / error
		if($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}
		if($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	/**
	 * PORTED FROM HRIS-Renewal.... Not implemented yet
	 */
	private function batch() {
		// Seperti biasa gw mau curang.... berhubung fungsi sama hanya berbeda tampilan
		$this->lists();
	}
}
