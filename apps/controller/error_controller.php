<?php
/**
 * This class used to provide user with appropriate error message instead of single line of message
 * There is some predefined error : missing_file, missing_controller, missing_method, missing_view, not_auth, not_allowed
 * These error are common and automatically called by the Dispatcher so DON'T Remove the method
 * index and generic error are allowed to be removed !
 *
 * You can add more error handler here or using other error controller.
 */
class ErrorController extends AppController {
	/**
	 * @var HttpHelper
	 */
	private $httpHelper;
	/**
	 * ErrorController::Initialize()
	 */
	protected function Initialize() {
		require_once(CORE . "helper/http_helper.php");
		$this->httpHelper = new HttpHelper();
	}
	
	/**
	 * ErrorController::index()
	 * 
	 * @param string $msg
	 */
	public function index($msg = "") {
		// Executing the error.generic() instead of error.index() because same content....
		// Do not use redirection ?? Why ?? I also don't know why...
		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("error", "generic", array($msg));
	}
	
	/**
	 * ErrorController::generic()
	 * 
	 * @param string $msg
	 */
	public function generic($msg) {
		$this->httpHelper->SetReponseCode(400);
		$this->Set("error", $msg);
	}

	// Start Section : DON'T Remove the method (But you can change the implementation)
	/**
	 * ErrorController::missing_file()
	 * 
	 * @param string $controllerName
	 * @param string $namespace
	 */
	public function missing_file($controllerName, $namespace = "") {
		$this->httpHelper->SetReponseCode(404);
		$this->Set("controller", $controllerName);
		$this->Set("folder", empty($namespace) ? "" : "/" . str_replace(".", "/", $namespace));
	}
	
	/**
	 * ErrorController::missing_controller()
	 * 
	 * @param string $controllerName
	 * @param string $namespace
	 */
	public function missing_controller($controllerName, $namespace = "") {
		$this->httpHelper->SetReponseCode(404);
		$this->Set("controller", $controllerName);
		$this->Set("folder", empty($namespace) ? "" : "/" . str_replace(".", "/", $namespace));
	}
	
	/**
	 * ErrorController::missing_method()
	 * 
	 * @param string $controllerName
	 * @param string $methodName
	 * @param string $namespace
	 */
	public function missing_method($controllerName, $methodName, $namespace = "") {
		$this->httpHelper->SetReponseCode(404);
		$this->Set("controller", $controllerName);
		if (empty($namespace))
			$this->Set("fqn", $controllerName);
		else
			$this->Set("fqn", $namespace . "." . $controllerName);
		$this->Set("method", $methodName);
		$this->Set("folder", empty($namespace) ? "" : "/" . str_replace(".", "/", $namespace));
	}
	
	/**
	 * ErrorController::missing_view()
	 * 
	 * @param string $controllerName
	 * @param string $viewName
	 * @param string $namespace
	 */
	public function missing_view($controllerName, $viewName, $namespace = "") {
		$this->httpHelper->SetReponseCode(404);
		// View name and method name are the same one.
		$this->Set("controller", $controllerName);
		if (empty($namespace))
			$this->Set("fqn", $controllerName);
		else
			$this->Set("fqn", $namespace . "." . $controllerName);
		$this->Set("view", $viewName);
		$this->Set("ext", Dispatcher::VIEW_EXTENSION);
		$this->Set("folder", empty($namespace) ? "" : "/" . str_replace(".", "/", $namespace));
	}
	
	/**
	 * ErrorController::not_auth()
	 * 
	 * @param string $controllerName
	 * @param string $methodName
	 * @param string $namespace
	 */
	public function not_auth($controllerName, $methodName, $namespace = "") {
//		$this->httpHelper->SetReponseCode(403);
//		if (empty($namespace))
//			$this->Set("fqn", $controllerName);
//		else
//			$this->Set("fqn", $namespace . "." . $controllerName);
//		$this->Set("method", $methodName);
		$appHelper = new AppHelper();
		redirect_url($appHelper->site_url("home/login"));
	}

	/**
	 * ErrorController::not_auth()
	 *
	 * @param string $controllerName
	 * @param string $methodName
	 * @param string $namespace
	 */
	public function not_allowed($controllerName, $methodName, $namespace = "") {
//		$this->httpHelper->SetReponseCode(403);
//		if (empty($namespace))
//			$this->Set("fqn", $controllerName);
//		else
//			$this->Set("fqn", $namespace . "." . $controllerName);
//		$this->Set("method", $methodName);
		$appHelper = new AppHelper();
		redirect_url($appHelper->site_url("main"));
	}

	/**
	 * ErrorController::db_error()
	 * Automatically dispatched by ConnectorBase::RaiseErrorIfRequired()
	 * This will help us (programmer to detect query error) IF DebugMode is set to true in ConnectorSettings
	 *
	 * @param $errCode
	 * @param $errMsg
	 * @param $query
	 * @return void
	 */
	public function db_error($errCode, $errMsg, $query) {
		$this->httpHelper->SetReponseCode(500);
		$this->Set("errCode", $errCode);
		$this->Set("errMsg", $errMsg);
		$this->Set("lastQuery", $query);
	}
	// End of Section
}
?>
