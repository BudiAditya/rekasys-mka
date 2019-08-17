<?php
/**
 * Used to determine/routing and populate user/client request (usually browser) and their parameters.
 * NOTE: For your convenience then Namespace always returned in dotted format instead of slashed
 * MAIN USAGE: Extracting requested URL into controller, method and their parameters if any
 *
 * Router Sequence:
 * 	 1. Created
 * 	 2. [HOOK] RouterCreated
 * 	 3. Checking if Router already initialized or not
 * 	 4. [HOOK] PreInitialize
 * 	 5. Client IP address detection
 * 	 6. Request Origin detection (normal / AJAX)
 * 	 7. [HOOK] PostIpAddressDetected
 * 	 8. User request detection (get URL from web browser)
 * 	 9 [HOOK] PreExtractRequest
 * 	10. User request extraction (get namespace, controller and method)
 * 	11. [HOOK] PostExtractRequest
 * 	12. Finished and return its data to Dispatcher (done by index.php)
 */
class Router extends ObjectExtended {
	/** @var Router */
	private static $instance = null;
	/** @var bool */
	private $isInitialized = false;
	/** @var IRouterHook */
	private $hook = null;
	/** @var RouteData This private variable will replace a lot of Router property... But there property still supported for backward compatibility */
	private $routeData = null;

	/**
	 * Retrieve current RouteData of user user request
	 *
	 * @return RouteData
	 */
	public function GetRouteData() {
		return $this->routeData;
	}

	/**
	 * Private constructor because this class intended to be a singleton class
	 */
	private final function __construct() {
		if (!IS_MAINTENANCE_MODE) {
			$className = defined("ROUTER_HOOK_CLASS") ? ROUTER_HOOK_CLASS : null;
		} else {
			// OK MAINTENANCE MODE detected
			$className = defined("ROUTER_HOOK_CLASS_MAINTENANCE") ? ROUTER_HOOK_CLASS_MAINTENANCE : null;
		}

		if (!empty($className)) {
			$this->hook = new $className();
		}
		if (!($this->hook instanceof IRouterHook)) {
			// WTF... this is not a IRouterHook instance...
			$this->hook = null;
		}
		if ($this->hook != null) {
			$this->hook->RouterCreated($this);
		}
	}

	/**
	 * @return Router
	 */
	public static function GetInstance() {
		if (self::$instance == null) {
			self::$instance = new Router();
		}

		return self::$instance;
	}

	/**
	 * Initialize Router object from user request. Source data automatically determined
	 * (URL Rewrite -> Force Clean URL -> Nothing)
	 *
	 * @return RouteData
	 */
	public function Initialize() {
		if ($this->isInitialized) {
			return $this->routeData; // Initialization only allowed once per request from client (browser)
		}
		// Enable hook PreInitialize
		if ($this->hook != null) {
			$this->hook->PreInitialize($this);
		}

		// Prepare object to store user request
		$this->routeData = new RouteData();

		// Detect client IP address
		$this->DetectClientIpAddress();

		// Detect request origin (standard request or AJAX request)
		// This method is not 100% trusted because client can modify sent header easily and not all AjaxRequest will set this header
		// This technique is implemented just for your convenience
		$ajaxHeader = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) : null;
		$this->routeData->IsAjaxRequest = $ajaxHeader == "xmlhttprequest";

		// Enable hook PostIpAddressDetected
		if ($this->hook != null) {
			$newRoute = $this->hook->PostIpAddressDetected($this, $this->routeData);
			if ($newRoute instanceof RouteData) {
				$this->routeData = $newRoute;
				if ($this->routeData->PreventNextSequence) {
					$this->isInitialized = true;
					return $this->routeData;
				}
			}
		}

		// Detect user request by URL
		$request = null;
		if (isset($_GET[URL_REWRITE_TO])) {
			$this->routeData->RawData = $_GET[URL_REWRITE_TO];
			unset($_GET[URL_REWRITE_TO]); // Remove the URL rewrite query string...
		} else {
			if (isset($_SERVER["PATH_INFO"])) {
				$request = $_SERVER["PATH_INFO"];
				$this->routeData->RawData = substr($request, 1);
			} else {
				$this->routeData->RawData = "";
			}
		}
		// Sanitize the request string before we proceed
		$this->SanitizeRequest();

		// Enable hook PreExtractRequest
		if ($this->hook != null) {
			$newRoute = $this->hook->PreExtractRequest($this, $this->routeData);
			if ($newRoute instanceof RouteData) {
				$this->routeData = $newRoute;
				if ($this->routeData->PreventNextSequence) {
					$this->isInitialized = true;
					return $this->routeData;
				}
			}
		}

		$this->ExtractRequest();
		if ($this->hook != null) {
			$this->hook->PostExtractRequest($this, $this->routeData);
		}
		$this->isInitialized = true;

		return $this->routeData;
	}

	/**
	 * Used to detect client IP Address.
	 * Source: http://www.kavoir.com/2010/03/php-how-to-detect-get-the-real-client-ip-address-of-website-visitors.html
	 *
	 * @return void
	 */
	private function DetectClientIpAddress() {
		$searchIn = array("HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_X_CLUSTER_CLIENT_IP", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED", "REMOTE_ADDR");

		foreach ($searchIn as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				$ipAddresses = explode(",", $_SERVER[$key]);

				foreach ($ipAddresses as $ip) {
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						$this->routeData->IpAddress = $ip;
						return; // Return to caller...
					}
				}
			}
		}
	}

	/**
	 * Before URL (user request) can be extracte there is some validation required
	 */
	private function SanitizeRequest() {
		$pos = strrpos($this->routeData->RawData, "/");

		// Remove trailing '/' from request (Causing error when auto detect namespace)
		if ($pos === strlen($this->routeData->RawData) - 1) {
			$this->routeData->RawData = substr($this->routeData->RawData, 0, strlen($this->routeData->RawData) - 1);
		}
	}

	/**
	 * Actual request extraction
	 */
	private function ExtractRequest() {
		/**
		 * $request format possibility :
		 * [folder[/controllerName[/methodName[/parameterN[/keyN:valueN]]]]]
		 *
		 * Note:
		 * We using 'namespace' term for folder location in the web server.
		 * This folder must have same structure for each 'MVC' in your APPS folder
		 * There is a possibility the 'namespace' written directly in controllerName (Ex: other.group.home)
		 */

		$tokens = explode("/", $this->routeData->RawData);
		$temp = null;

		/**
		 * OK start namespace auto detection
		 * Intelligent detection used for same controller name and folder name. Ex:
		 *
		 * Request: 'other/group/home/hello_world/param1/param2'
		 * Case #1: home_controller.php exists at folder 'other/group' then HomeController will be used / passed to dispatcher
		 * Case #2: group_controller.php exists at folder 'other' then GroupController will be used
		 *            WARNING! EVEN THERE IS A FOLDER named 'group' in the 'other' folder (Case #1) we'll used the GroupController Instead
		 * Case #3: like Case #2 (worse) there is other_controller.php exists in the root of controller folder. Result OtherController will be used !
		 *            WARNING! File have more priority than folder
		 *
		 * Q: CAN I Specify HomeController of 'other.group' namespace if such case (case #2 or case #3) occurred ?
		 * A: Yes you can !
		 *
		 * Q: HOW ? are you stupid ? You say file have more priority than folder !
		 * A: Change the request into specific one like 'other.group.home/hello_world/param1/param2'. Settled !
		 *
		 * Q: It's sound stupid but can I access that resource using 'other/group.home/hello_world/param1/param2'
		 * A: -_-a... As you wish ! It can ! but you take the responsible to generate the link like that ok :)
		 */

		$idx = 0;
		$currentFolder = "";
		for (; $idx < count($tokens); $idx++) {
			if (strpos($tokens[$idx], ".") !== false) {
				// This tokens have a dot (namespace separator) so this one must be the controller name
				break;
			}

			// Checking controller file (File have more priority than folder)
			$fileName = sprintf(CONTROLLER . "%s%s_controller.php", $currentFolder, $tokens[$idx]);
			if (file_exists($fileName)) {
				// At least corresponding controller file found ! Leave to Dispatcher to loading and check class existence
				break;
			}

			$folderName = CONTROLLER . $currentFolder . $tokens[$idx] . "/";
			if (!is_dir($folderName)) {
				// Ooppss not a controller file nor folder... (probably a plain file without extension)
				// Leave it to dispatcher to KILL the request by ErrorController::missing_file();
				break;
			}

			// OK Folder found go for next iteration checking until controller found !
			$currentFolder .= $tokens[$idx] . "/";
		}

		if ($idx == count($tokens)) {
			// Hmmm this possibility only happened when $request is mapped to folder without controller name nor method nor parameter
			// Ex: 'other/group' --> we have that folder and inside there is a home_controller.php (default controller) then it's working
			$tokens[] = DEFAULT_CONTROLLER;
			$tokens[] = DEFAULT_METHOD;
		}

		// Convert current folder to namespace and let the dispatcher do the rest
		if (strlen($currentFolder) > 0) {
			$currentFolder = str_replace("/", ".", $currentFolder);
			// Remove the last '.'
			$currentFolder = substr($currentFolder, 0, strlen($currentFolder) - 1);
			// We set the namespace latter because some issue like 'other/group.home/hello_world/param1/param2'
		}

		// Extract controller name
		$this->routeData->ControllerName = !empty($tokens[$idx]) ? $tokens[$idx] : DEFAULT_CONTROLLER;
		// Check for namespace in controller name (Multi dot controller name is handled)
		$temp = strrpos($this->routeData->ControllerName, ".");
		if ($temp > 0) {
			// Namespace for controller found in URL (Merging with previous namespace)
			if (strlen($currentFolder) > 0) {
				$currentFolder .= "." . substr($this->routeData->ControllerName, 0, $temp);
			} else {
				$currentFolder = substr($this->routeData->ControllerName, 0, $temp);
			}

			// Remove the last '.'
			$this->routeData->ControllerName = substr($this->routeData->ControllerName, $temp + 1);
		}

		// Setting namespace
		$this->routeData->Namespace = $currentFolder;

		// Extract method name
		$this->routeData->MethodName = !empty($tokens[$idx + 1]) ? $tokens[$idx + 1] : DEFAULT_METHOD;

		// Parameter Extraction (always at the third argument after controller name)
		for ($i = $idx + 2; $i < count($tokens); $i++) {
			if (strpos($tokens[$i], ":") !== false) {
				list($key, $value) = explode(":", $tokens[$i], 2);
				$this->routeData->NamedParameters[$key] = $value;
			} else {
				$this->routeData->Parameters[] = $tokens[$i];
			}
		}
	}

	/// REGION - Backward compatibility HACK

	/// All of these property now declared as obsoleted
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $IpAddress = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $IsAjaxRequest = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $Fqn = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $Namespace = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $ControllerName = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $MethodName = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $Parameters = array("Type" => "r");
	/** @obsolete Please use Router::GetRouteData() instead */
	protected $NamedParameters = array("Type" => "r");

	/// All of methods bellow are used by Router Property
	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetIpAddress() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->IpAddress;
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetIsAjaxRequest() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->IsAjaxRequest;
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetFqn() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->GetFqn();
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetNamespace() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->Namespace;
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetControllerName() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->ControllerName;
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetMethodName() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->MethodName;
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetParameters() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->Parameters;
	}

	/**
	 * @obsolete Please use Router::GetRouteData() instead
	 * @return string
	 */
	protected function GetNamedParameters() {
		if (!$this->isInitialized) {
			return null;
		}

		return $this->routeData->NamedParameters;
	}
	/// END REGION - Backward compatibility
}

// EoF: ./system/core/router.php
