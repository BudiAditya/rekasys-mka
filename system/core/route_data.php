<?php
/**
 * Class RouteData
 *
 * This class will store all data related to user request from beginning (URL raw data) until system process the request into ControllerName etc
 * CONVENTION:
 * 		If you change a RouteData then you MUST create a new RouteData instance and pass the old RouteData into constructor.
 * 		With this pattern we able to track changes. Of course Router class will NOT inspect any data from $previousRouteData, BUT it's for your sake to be able to detect previous RouteData.
 */
class RouteData {
	private $previousRouteData = null;

	public $IpAddress;
	public $IsAjaxRequest;
	public $RawData;
	public $Namespace;
	public $ControllerName;
	public $MethodName;
	public $Parameters = array();
	public $NamedParameters = array();

	public $PreventNextSequence = false;

	public function __construct(RouteData $previous = null) {
		$this->previousRouteData = $previous;
		if ($previous != null) {
			// IP address and Request Origin should be same with previous one by default
			$this->IpAddress = $previous->IpAddress;
			$this->IsAjaxRequest = $previous->IsAjaxRequest;
			// Raw data is provided from previous RouteData for your convenience
			$this->RawData = $previous->RawData;
		}
	}

	/**
	 * @return string
	 */
	public function GetFqn() {
		return empty($this->Namespace) ? $this->ControllerName : $this->Namespace . "." . $this->ControllerName;
	}

	/**
	 * Return previous RouteData in case system performing re-routing.
	 * This usually happen if maintenance mode activated
	 *
	 * @return RouteData
	 */
	public function GetPreviousRequest() {
		return $this->previousRouteData;
	}
}

// EoF: route_data.php