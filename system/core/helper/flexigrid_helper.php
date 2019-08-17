<?php
/**
 * Helper for create Flexigrid JavaScript string.
 *
 * For $Columns array Property should be assigned with array with index scheme:
 *	- index #0 		=> Column Display Name / Column Title
 *	- index #1 		=> [OPTIONAL] Column Width
 *	- index #2 		=> [OPTIONAL] Whether this column sortable or not
 * 	- index #3 		=> [OPTIONAL] Column text alignment [left|center|right]
 * 	- index #4 		=> [OPTIONAL] Whether this column searchable or not (this will used as conjunction with $SearchFilters)
 * 		acceptable value for index #4: 0 = not searchable, 1 = searchable, 2 = searchable + default selected, other = not searchable
 * 	- index #5 		=> [OPTIONAL] Column visibility
 *
 * NOTE: $Columns array key will be used as column id [and search id if this column searchable (sent using 'qtype' parameter)]
 * NOTE: $Columns array key advisable to be string but numeric index also can be used
 * NOTE: if the column is searchable then this column will be added into search filter after we processing $SearchFilters
 * Ex: $fgHelperObj->Columns["name"] = array("Customer Name", 120, true, "left", 2)
 * 		Result: Column with id = "name", column heading = "Customer Name", column width = 120, sortable (by clicking column heading), searchable (by click magnifier icon in navigation)
 *
 *
 * For $Buttons array Property should be assigned with array with index scheme:
 *	- index #0		=> Button Display Name. (if name == 'separator' then a separator will be created instead of button)
 * 	- index #1		=> Button class name (Please refer to flexigrid.css for how to add css class for your flexigrid button)
 * 	- index #2		=> onpress handler (Flexigrid will call this function and pass 2 parameters. Param #1: Display Name, Param #2: Your grid element (<table> or <div>))
 *
 * Ex: $fgHelperObj->Buttons[] = array("Edit", "btnEdit", "performEdit");
 * 		Result: Button in flexigrid toolbar with button name = "Edit", button class name = "btnEdit", and will call JavaScript performEdit
 *
 *
 * For $SearchFilters array Property should be assigned with array with index scheme:
 *	- index #0		=> Search Display Name. (if name == 'separator' then a separator will be created instead of button)
 * 	- index #1		=> [OPTIONAL] Default selected
 *  - index #2		=> [OPTIONAL] Default selected for second search term
 *
 * NOTE: $SearchFilters array key will be used as search id (sent using 'qtype' parameter) *
 * Ex: $fgHelperObj->SearchFilters["name"] = array("Customer Name", true);
 * 		Result: Search filter by 'name' added into navigation panel
 *
 * @throws Exception
 *
 */
class FlexigridHelper {
	// Common Flexigrid Property (Usually modified based on requirement and search result)
	public $AutoLoad = true;				// Should Flexigrid automatically load data after grid creation ?
	public $SendMethod = "GET";				// how flexigrid send data into server ? GET or POST
	public $Url;							// Remote URL which provide data
	public $DataType = "json";				// Return type of server script
	public $Columns = array();				// Flexigrid columns definition(s)
	public $Buttons = array();				// Flexigrid toolbar button(s)
	public $SearchFilters = array();		// Filter(s) for quick search in navigation
	public $SortBy;							// Default column sort
	public $SortOrder = "asc";				// Default sort order
	public $UsePager = true;				// Show or hide navigation toolbar
	public $Title = null;					// Flexigrid title
	public $UseRecordPerPage = true;		// Whether user is allowed to change record per page or not
	public $RecordPerPage = 10;				// Default Record Per Page
	public $ShowToggleButton = true;		// Show or Hide Flexigrid toggle button
	public $Width = "auto";					// Flexigrid width. Use 'auto' or specify int
	public $Height = 300;					// Flexigrid height.
	public $IsSingleSelect = true;			// Determine whether user allowed to select multiple row(s) or not
	public $Resizable = false;				// Determine whether flexigird table resizable or not

	private $additionalSearchColumns = array();

	/**
	 * OK Flexigrid is support more customization (A LOT OF CUSTOMIZATION... after I see the flexigrid.js)
	 * For other config / customization we'll use array because it's rarely used / customized
	 * In this way we'll support for new config if flexigrid library is updated
	 * These custom config key(s) are retrieved from flexigrid.js
	 *
	 * IMPORTANT: for JS function reference YOU SHOULD PREFIX IT USING 'func:' before you set the reference
	 * 	Ex: "onSubmit" => "func:javaScriptHandlerName"
	 */
	public $CustomConfigs = array(
		"striped" => true 					//apply odd even stripes
		, "novstripe" => false
		, "minwidth" => 30	 				//min width of columns
		, "minheight" => 80 				//min height of columns
		, "errormsg" => "Connection Error"
		, "nowrap" => true
		, "page" => 1 						//current page
		, "total" => 1 						//total pages
		, "rpOptions" => array(10, 20, 30) 	//allowed per-page values
		, "pagestat" => "Displaying {from} to {to} of {total} items"
		, "pagetext" => "Page"
		, "outof" => "of"
		, "findtext" => "Filter 1"
		, "procmsg" => "Processing, please wait ..."
		, "query" => ""
		, "qtype" => ""
		, "nomsg" => "No items"
		, "minColToggle" => 1 				//minimum allowed column to be hidden
		, "hideOnSubmit" => true
		, "blockOpacity" => 0.5
		// Bellow are config which should call javascript and MUST BE prefixed with 'func:' otherwise you will get javascript error
		, "preProcess" => false
		, "onDragCol" => false
		, "onToggleCol" => false
		, "onChangeSort" => false
		, "onSuccess" => false
		, "onError" => false
		, "onSubmit" => false 				//using a custom populate function
	);

	private function CompileColumns() {
		// Reset additional search column(s)
		$this->additionalSearchColumns = array();
		$columns = array();

		foreach ($this->Columns as $key => $colDefinitions) {
			$display = $colDefinitions[0];
			if (array_key_exists(4, $colDefinitions)) {
				$searchAble = $colDefinitions[4];
			} else {
				$searchAble = 0;
			}

			$columns[] = array(
				"name" => $key
				, "display" => $display
				, "width" =>array_key_exists(1, $colDefinitions) ? $colDefinitions[1] : 120
				, "sortable" => array_key_exists(2, $colDefinitions) ? $colDefinitions[2] : true
				, "align" => array_key_exists(3, $colDefinitions) ? $colDefinitions[3] : "left"
				, "hide" => array_key_exists(5, $colDefinitions) ? $colDefinitions[5] : false
			);

			// OK CURSED YOU CI FLEXIGRID HELPER !!!!! You allowed this column to be searchable in same column definition
			// MAKE MY LIFE MORE DIFFICULT !!!!
			switch ($searchAble) {
				case 1:
					$this->additionalSearchColumns[] = array(
						"name" => $key
						, "display" => $display
					);
					break;
				case 2:
					$this->additionalSearchColumns[] = array(
						"name" => $key
						, "display" => $display
						, "isdefault" => true
					);
					$this->SortBy = $key;
					break;
			}
		}

		return $columns;
	}

	private function CompileButtons() {
		/**
		 * Compiling button a little bit tricky because json_encode can't encode JS function reference
		 * It will treat JS function reference as string and added double quote sign.
		 *
		 * OK my special key for JS function reference will started with 'func:' so json_encode will output something like
		 * 		"func:xxxxxxx"
		 * Later before we perform last compilation we'll replace it using ReGex into
		 * 		xxxxxxx
		 * NOTE: result output will be without double quote sign and this replace performed by CompileJavaScript()
		 */
		$buttons = array();

		foreach ($this->Buttons as $btnDefinitions) {
			if ($btnDefinitions[0] == "separator") {
				$buttons[] = array("separator" => true);
			} else {
				$buttons[] = array(
					"name" => $btnDefinitions[0]
					, "bclass" => $btnDefinitions[1]
					, "onpress" => "func:" . $btnDefinitions[2]
				);
			}
		}

		return $buttons;
	}

	private function CompileSearchFilters() {
		$filters = array();

		foreach ($this->SearchFilters as $key => $filterDefinitions) {
			$filters[] = array(
				"name" => $key
				, "display" => $filterDefinitions[0]
				, "isdefault" => array_key_exists(1, $filterDefinitions) ? $filterDefinitions[1] : false
				, "isdefault2" => array_key_exists(2, $filterDefinitions) ? $filterDefinitions[2] : false
			);
		}

		$filters = array_merge($filters, $this->additionalSearchColumns);
		return $filters;
	}

	/**
	 * Build / compiling proper javascript to create Flexigrid based on provided setting(s)
	 * WARNING: $CustomConfigs will OVERRIDE default settings if there is same key found !
	 *
	 * IMPORTANT: for JS function reference YOU SHOULD PREFIX IT USING 'func:' before you set the reference
	 * 	Ex: "onSubmit" => "func:javaScriptHandlerName"
	 *
	 * @param string $gridId			: must be ID of <table> or <div>
	 * @param bool $surroundWithTag		: tell the compiler to add <script>. Only applied when $gridId is given
	 * @return string					: JavaScript to create Flexigrid
	 */
	public function CompileJavaScript($gridId = null, $surroundWithTag = false) {
		/**
		 * OK prepare basic Flexigrid configs..
		 * NOTE: json_encode unable to reference a java script function so we have do special technique to apply JS function in JSON
		 * 		 Source: http://solutoire.com/2008/06/12/sending-javascript-functions-over-json/
		 * 		 Implementation logic: give some special key and replace the special key later with actual code
		 *
		 * 		 My Implementation: using some special key then using Regular Expression to replace the special keys in one swoop LOLZ
		 */
		$configs = array(
			"autoload" => $this->AutoLoad
			, "url" => $this->Url
			, "method" => $this->SendMethod
			, "dataType" => $this->DataType
			, "colModel" => $this->CompileColumns()
			, "buttons" => $this->CompileButtons()
			, "searchitems" => $this->CompileSearchFilters()
			, "sortname" => $this->SortBy
			, "sortorder" => $this->SortOrder
			, "usepager" => $this->UsePager
			, "title" => $this->Title
			, "useRp" => $this->UseRecordPerPage
			, "rp" => $this->RecordPerPage
			, "showTableToggleBtn" => $this->ShowToggleButton
			, "width" => $this->Width
			, "height" => $this->Height
			, "singleSelect" => $this->IsSingleSelect
			, "resizable" => $this->Resizable
		);

		// Merge with custom configs and checking for 'func:' reference
		$configs = array_merge($configs, $this->CustomConfigs);
		$regexRequired = false;
		foreach ($configs as  $config) {
			if (!is_string($config)) {
				continue;
			}

			if (strpos($config, "func:") !== false) {
				// OK some JS reference found ! And stopping search
				$regexRequired = true;
				break;
			}
		}

		$script = json_encode($configs);
		// OK before completing we must performing ReGex if there is button(s) added
		if ($regexRequired || count($this->Buttons) > 0) {
			$pattern = '/"func:(\w+)"/i';
			$replacement = '${1}';
			$script = preg_replace($pattern, $replacement, $script);
		}

		if (!empty($gridId)) {
			$script = sprintf('$("#%s").flexigrid(%s);', $gridId, $script);
			if ($surroundWithTag) {
				$script = sprintf('<script type="text/javascript">$(document).ready(function() { %s })</script>%s', $script, "\n");
			}
		}

		return $script;
	}

	/**
	 * This function identical with Flixigrid helper for CodeIgniter....
	 *
	 * @param $gridId
	 * @param $url
	 * @param $columns
	 * @param $sortBy
	 * @param string $sortOrder
	 * @param array $gridParams
	 * @param array $button
	 * @return string
	 */
	public function BuildGridJs($gridId, $url, $columns, $sortBy, $sortOrder = "asc", array $gridParams = array(), array $button = array()) {
		$this->Url = $url;
		$this->Columns = $columns;
		$this->SortBy = $sortBy;
		$this->SortOrder = $sortOrder;

		// OK for compatibility reason we will merge $CustomConfigs with $gridParams instead of replace it
		foreach ($gridParams as $key => $value) {
			$this->CustomConfigs[$key] = $value;
		}

		$this->Buttons = $button;

		return $this->CompileJavaScript($gridId, true);
	}

	public function ValidateData() {
		$result = array();

		// Until I found a better way to access POST data then accessing via super globals are acceptable for time being
		if (count($_POST) > 0) {
			$result["page"] = $_POST["page"];
			$result["sortBy"] = $_POST["sortname"];
			$result["sortOrder"] = $_POST["sortorder"];
			$result["filterBy"] = $_POST["qtype"];
			$result["query"] = $_POST["query"];
			$result["recordPerPage"] = $_POST["rp"];

			// HACK Double Search Term
			$result["condition"] = $_POST["condition"];
			$result["query2"] = $_POST["query2"];
			$result["filterBy2"] = $_POST["qtype2"];
		} else {
			$result["page"] = $_GET["page"];
			$result["sortBy"] = $_GET["sortname"];
			$result["sortOrder"] = $_GET["sortorder"];
			$result["filterBy"] = $_GET["qtype"];
			$result["query"] = $_GET["query"];
			$result["recordPerPage"] = $_GET["rp"];

			// HACK Double Search Term
			$result["condition"] = $_GET["condition"];
			$result["query2"] = $_GET["query2"];
			$result["filterBy2"] = $_GET["qtype2"];
		}

		return $result;
	}


	/**
	 * Compiling flexigrid JSON result
	 *
	 * @param int $page		=> Current Page
	 * @param int $total	=> Total Records Found
	 * @param array $rows	=> array of data. first index will be used as ID and the rest will be row data.
	 * @return string
	 */
	public function CompileJsonResult($page = 1, $total = 0, array $rows = array()) {
		$result = array(
			"page" => (int)$page
			, "total" => (int)$total
			, "rows" => array()
		);

		foreach ($rows as $row) {
			$id = $row[0];
			unset($row[0]);
			$result["rows"][] = array(
				"id" => $id
				, "cell" => array_values($row)
			);
		}

		return json_encode($result);
	}
}

// EOF: ./system/core/helper/flexigrid_helper.php
