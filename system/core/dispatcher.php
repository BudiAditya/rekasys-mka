<?php
/**
 * This class used to execute any controller and method based on user request. User request are determined from Router class and execution is handled by Dispatcher instance.
 * An instance of Dispatcher can only dispatching one request. Another subsequent request (programmatically) must be using another instance by calling GetInstance()
 *
 * @see Router
 * @see AppController
 * @see AclManager
 * @see ConnectorManager
 */
class Dispatcher {
	const MISSING_FILE = -1;
	const MISSING_CLASS_DEFINITION = -2;
	const MISSING_METHOD = -3;
	const MISSING_VIEW = -4;
	const AUTH_NOT_AUTHENTICATED = -5;
	const AUTH_NOT_ALLOWED = -6;
	const CONTROLLER_FOUND = 1;
	const INITIALIZE_COMPLETE = 2;
	const INITIALIZE_COMPLETE_NO_VIEW = 3;
	const SEQUENCE_SUPPRESSED = 4;

	/**
	 * Stored instance(s) of Dispatcher which used for controller execution. Any new dispatch request must be done in new instance of dispatcher
	 *
	 * @see Dispatcher::CreateInstance()
	 * @var Dispatcher[]
	 */
	private static $instances;
	/** @var int */
	private static $instanceCounter = 0;
	/**
	 * Store Concrete AppController object which used to execute request
	 *
	 * @var AppController
	 */
	private $controller;
	/**
	 * Flag that controlling Application Execution. If you need to stop execution just set this flag to true
	 *
	 * @var bool
	 */
	private $suppressNextSequence = false;
	/**
	 * Flag that determine whether we will execute method using compatibility name or not.
	 * Some method name are reserved by PHP (Ex: print()) so we must prefix the method name but the caller did not required to specify prefix
	 *
	 * @var bool
	 */
	private $compatibilityMode = false;
	/**
	 * @var bool
	 */
	private $isDispatching = false;
	/**
	 * Used to determine dispatcher sequence or instance number
	 *
	 * @var int
	 */
	private $currentInstanceNumber = 0;

	/**
	 * @param int $instanceNumber
	 * @return Dispatcher
	 */
	private function __construct($instanceNumber) {
		$this->currentInstanceNumber = $instanceNumber;
	}

	/**
	 * Used to load controller class definition before we create controller instance
	 *
	 * @param string $controllerName
	 * @param string $folderPrefix
	 * @param string $className [OUT] parameter. This will specify Controller Class Name
	 * @return int status of controller
	 */
	private function LoadController($controllerName, $folderPrefix, &$className) {
		$className = null;
		$file = sprintf(CONTROLLER . "%s%s_controller.php", $folderPrefix, $controllerName);
		if (!file_exists($file)) {
			// What the heck controller file not FOUND !!!
			// New Enumeration for error handling.
			return Dispatcher::MISSING_FILE;
		}
		require_once($file);

		// Attempt #1 - Only Controller Class Name without FQN
		$className = sprintf("%sController", $controllerName);
		if (class_exists($className)) {
			// Class Found without FQN
			//$this->controller = new $className($controllerName, ConnectorManager::GetDefaultConnector(), PersistenceManager::GetInstance());
			return Dispatcher::CONTROLLER_FOUND;
		}

		// Try to using FQN  maybe user also applied namespace in the class
		// Attempt #2 - Fully Qualified Class Name
		$className = str_replace("/", "\\", $folderPrefix) . $className;

		// If we found the class then return the class name
		if (class_exists($className)) {
			//$this->controller = new $className($controllerName, ConnectorManager::GetDefaultConnector(), PersistenceManager::GetInstance());
			return Dispatcher::CONTROLLER_FOUND;
		} else {
			return Dispatcher::MISSING_CLASS_DEFINITION;
		}
	}

	/**
	 * Checking whether $methodName callable in current controller.
	 * If not found then $methodName will be prefixed with PREFIX_CONFLICT_METHOD_NAME and trying second attempt to check method existence
	 *
	 * @param string $methodName
	 * @return bool true when $methodName found in the $controller
	 */
	private function CheckMethod($methodName) {
		if (method_exists($this->controller, $methodName)) {
			return true;
		} else {
			$methodName = PREFIX_CONFLICT_METHOD_NAME . $methodName;
			$this->compatibilityMode = method_exists($this->controller, $methodName);
			return $this->compatibilityMode;
		}
	}

	/**
	 * Checking whether VIEW file is exists or not
	 *
	 * @param mixed $controllerName
	 * @param mixed $methodName
	 * @param string $folderPrefix
	 * @return bool true on success
	 */
	private function CheckView($controllerName, $methodName, $folderPrefix) {
		$file = sprintf(VIEW . "%s%s/%s.php", $folderPrefix, $controllerName, $methodName);
		return file_exists($file);
	}

	/**
	 * Create new instance of Dispatcher for executing AppController
	 *
	 * @see AppController
	 * @param bool $supressPreviousInstance
	 * @return Dispatcher instance
	 */
	public static function CreateInstance($supressPreviousInstance = true) {
		if ($supressPreviousInstance) {
			if (Dispatcher::$instanceCounter > 0) {
				Dispatcher::$instances[Dispatcher::$instanceCounter - 1]->SuppressNextSequence();
			}
		}

		Dispatcher::$instanceCounter++;
		$instance = new Dispatcher(Dispatcher::$instanceCounter);
		Dispatcher::$instances[] = $instance;

		return $instance;
	}

	/**
	 * Get all instance if Dispatcher
	 *
	 * @return Dispatcher[]
	 */
	public static function GetInstances() {
		return Dispatcher::$instances;
	}

	/**
	 * Retrieve specific instance of Dispatcher which currenty loaded or executing any user request.
	 * You can get current dispatcher of an AppController by their sequence defined by AppController (protected field)
	 *   Example : Dispacther::GetInstanceAt($this->dispatcherSequence);
	 *   Note    : You must execute above code in AppController context
	 *
	 * @see AppController::dispatcherSequence
	 * @param int $idx
	 * @return Dispatcher|null
	 * @throws Exception
	 */
	public static function GetInstanceAt($idx) {
		if ($idx < 0) {
			throw new Exception('ArgumentOutOfIndex $idx could not less than 0');
		} else if ($idx < Dispatcher::$instanceCounter) {
			return Dispatcher::$instances[$idx];
		} else {
			return null;
		}
	}

	/**
	 * Since we using Router class we can safely say that the $controllerName will not contains namespace data !
	 * User request data extraction handled by Router class
	 *
	 * @param string $controllerName
	 * @param string $methodName
	 * @param array $params
	 * @param array $namedParams
	 * @param string $namespace
	 * @param bool $bypassAcl
	 * @throws Exception
	 */
	public function Dispatch($controllerName, $methodName, array $params = array(), array $namedParams = array(), $namespace = null, $bypassAcl = false) {
		if ($this->isDispatching) {
			throw new Exception("Current dispatcher already dispatching. Please user another instance of dispatcher.");
		}

		$this->isDispatching = true;
		$haveView = true;

		// OK User code tell that next sequence must be suppressed !
		if ($this->suppressNextSequence) {
			return;
		}

		switch ($this->InitializeResource($controllerName, $methodName, $namedParams, $namespace, $bypassAcl)) {
			case Dispatcher::MISSING_FILE:
				$anotherInstance = Dispatcher::CreateInstance();
				$anotherInstance->Dispatch("error", "missing_file", array($controllerName, $namespace));
				return;
			case Dispatcher::MISSING_CLASS_DEFINITION:
				$anotherInstance = Dispatcher::CreateInstance();
				$anotherInstance->Dispatch("error", "missing_controller", array($controllerName, $namespace));
				return;
			case Dispatcher::AUTH_NOT_AUTHENTICATED:
				$anotherInstance = Dispatcher::CreateInstance();
				$anotherInstance->Dispatch("error", "not_auth", array($controllerName, $methodName, $namespace));
				return;
			case Dispatcher::AUTH_NOT_ALLOWED:
				$anotherInstance = Dispatcher::CreateInstance();
				$anotherInstance->Dispatch("error", "not_allowed", array($controllerName, $methodName, $namespace));
				return;
			case Dispatcher::MISSING_METHOD:
				$anotherInstance = Dispatcher::CreateInstance();
				$anotherInstance->Dispatch("error", "missing_method", array($controllerName, $methodName, $namespace));
				return;
			case Dispatcher::MISSING_VIEW:
				$anotherInstance = Dispatcher::CreateInstance();
				$anotherInstance->Dispatch("error", "missing_view", array($controllerName, $methodName, $namespace));
				return;
			case Dispatcher::INITIALIZE_COMPLETE_NO_VIEW:
				$haveView = false;
			case Dispatcher::INITIALIZE_COMPLETE:
				// Allowing to suppress method dispatch
				if ($this->suppressNextSequence) {
					return;
				}
				if ($this->compatibilityMode) {
					// This will happened when user defined PREFIX_CONFLICT_METHOD_NAME
					// Please check the CheckMethod()
					$this->controller->DispatchMethod(PREFIX_CONFLICT_METHOD_NAME . $methodName, $params);
				} else {
					$this->controller->DispatchMethod($methodName, $params);
				}

				// Allowing to suppress view rendering
				if (!$haveView || $this->suppressNextSequence) {
					return;
				}
				$this->RenderView($controllerName, $methodName, $namespace, true);
				break;
			case Dispatcher::SEQUENCE_SUPPRESSED:
			default:
				break;
		}

		// OK dispatching is finished... reset counter etc;
		Dispatcher::$instanceCounter--;
		unset(Dispatcher::$instances[Dispatcher::$instanceCounter]);
	}

	/**
	 * WARNING: Redirect cleaning all ob handler and terminate current PHP script immediately
	 *
	 * @param string $url
	 * @param bool $endResponse
	 * @param bool $useLocation
	 * @param int $httpResponseCode
	 * @return void
	 */
	public static function Redirect($url, $endResponse = true, $useLocation = true, $httpResponseCode = 302) {
		// Hmm redirecting ? so cleaning up resources if any
		$obHandlers = ob_list_handlers();
		for ($i = 0; $i < count($obHandlers); $i++) {
			ob_end_clean();
		}
		// Redirect process
		if ($useLocation) {
			header("Location: " . $url, true, $httpResponseCode);
		} else {
			header("Refresh: 0;url=" . $url, true, $httpResponseCode);
			print('Redirecting... click <a href="' . $url . '">here</a> if you not being redirected.');
		}

		if (!$endResponse) {
			// Allow system to finish current execution script
			return;
		}

		// Terminate current PHP execution
		exit();
	}

	/**
	 * Used to redirect request to our site page NOT other site page. For other site page user Redirect() instead
	 *
	 * @param string $siteUrl => Relative url of our site
	 * @param bool $endResponse
	 * @param bool $useLocation
	 * @param int $httpResponseCode
	 * @return void
	 */
	public static function RedirectUrl($siteUrl, $endResponse = true, $useLocation = true, $httpResponseCode = 302) {
		$helper = new AppHelper();
		Dispatcher::Redirect($helper->site_url($siteUrl), $endResponse, $useLocation, $httpResponseCode);
	}

	/**
	 * Checking resource, initialize resource of controller
	 *
	 * @param string $controllerName
	 * @param string $methodName
	 * @param string $namedParams
	 * @param string $namespace
	 * @param bool $bypassAcl
	 * @return int enumeration
	 */
	private function InitializeResource($controllerName, $methodName, $namedParams, $namespace, $bypassAcl) {
		if ($this->suppressNextSequence) {
			return Dispatcher::SEQUENCE_SUPPRESSED;
		}

		$folderPrefix = "";
		if (!empty($namespace)) {
			// Change the namespace format to folder format
			// Ex: Report.Sales -> Report/Sales/
			$folderPrefix = str_replace(".", "/", $namespace);
			if (strripos("/", $folderPrefix) !== strlen($folderPrefix)) {
				$folderPrefix .= "/";
			}
		}

		// Load appropriate controller
		$className = null;
		$result = $this->LoadController($controllerName, $folderPrefix, $className);
		if ($result !== Dispatcher::CONTROLLER_FOUND) {
			// Something bad happened ! we expecting CONTROLLER_FOUND value
			return $result;
		}

		// OK now we must check ACL before creating controller instance...
		// If we check ACL after controller instantiated, It'll cause unexpected error if Initialize() at AppController trying to access user data when user already logged out
		$acl = AclManager::GetInstance();
		$accessAllowed = $bypassAcl ? true : $acl->CheckUserAccess($controllerName, $methodName, $namespace);
		if (!$accessAllowed) {
			// Current user still not allowed to access this resource even after we set bypassed controller ?
			// OK kick him because we sure that user is NOT ALLOWED to access !
			if ($acl->GetIsUserAuthenticated()) {
				// OK this user access denied implicitly / explicitly by Effective Acl
				return Dispatcher::AUTH_NOT_ALLOWED;
			} else {
				// Hmm user still not authenticated yet so he trying to access resource outside bypassed controller(s)
				return Dispatcher::AUTH_NOT_AUTHENTICATED;
			}
		}

		$this->controller = new $className($controllerName, ConnectorManager::GetDefaultConnector(), PersistenceManager::GetInstance(), $namedParams, $this->currentInstanceNumber);

		// OK there is a probability that current controller is dispatching another controller in AppController::Initialize()
		// We must stop if there is suppress sequence flag found after controller instance created
		if ($this->suppressNextSequence) {
			return Dispatcher::SEQUENCE_SUPPRESSED;
		}

		// Checking for controller method
		if (!$this->CheckMethod($methodName)) {
			return Dispatcher::MISSING_METHOD;
		}

		// Checking for view template used in rendering if the controller state that VIEW is compulsory
		$haveView = $this->CheckView($controllerName, $methodName, $folderPrefix);
		if ($this->controller->MustHaveView === true && !$haveView) {
			return Dispatcher::MISSING_VIEW;
		}

		// Passed all check !
		return $haveView ? Dispatcher::INITIALIZE_COMPLETE : Dispatcher::INITIALIZE_COMPLETE_NO_VIEW;
	}

	/**
	 * Rendering any VIEW which associated controller
	 *
	 * @param string $controllerName
	 * @param string $methodName
	 * @param string $namespace
	 * @param bool $sendToBrowser
	 * @throws Exception
	 * @return null|string
	 */
	public function RenderView($controllerName, $methodName, $namespace, $sendToBrowser = true) {
		// Copy the content of DataForView to prevent notice 'Indirect modification of overloaded property'
		// This fix for PHP > 5.2.0
		if ($this->controller == null) {
			throw new Exception("RenderView unable to find any loaded controller. Did you directly call RenderView after Dispatcher::CreateInstance ?");
		} else {
			$localScope = $this->controller->GetDataForView();
		}
		$folderPrefix = "";
		if (!empty($namespace)) {
			// Change the namespace format to folder format
			// Ex: Report.Sales -> Report/Sales/
			$folderPrefix = str_replace(".", "/", $namespace);
			$folderPrefix .= "/";
		}

		// View Rendering Process
		extract($localScope);
		// This $helper will be available in view file. Framework force this variable existence in every VIEW because of usability offered by this helper
		$helper = new AppHelper();
		$file = sprintf(VIEW . "%s%s/%s.php", $folderPrefix, $controllerName, $methodName);
		ob_start();
		require_once($file);
		$content = ob_get_clean();

		// Latest version allow us to retrieve View content
		if ($sendToBrowser) {
			print($content);
			return null;
		} else {
			return $content;
		}
	}

	/**
	 * Tell current instance of dispatcher to stop any further of action.
	 *
	 * @param bool $flag
	 */
	public function SuppressNextSequence($flag = true) {
		$this->suppressNextSequence = $flag;
	}

	/**
	 * Tell system to immediately flush all buffer to the browser.
	 */
	public static  function FlushOutput() {
		$obHandlers = ob_list_handlers();
		for ($i = 0; $i < count($obHandlers); $i++) {
			ob_end_flush();
		}
	}
}

// EoF: ./system/core/dispatcher.php
