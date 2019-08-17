<?php
/**
 * Define global folder data. It's will be used for loading another resource
 */
define("BASE", dirname(__FILE__));
define("SYSTEM", BASE . "/system/");
define("SYSTEM_CONFIG", SYSTEM . "config/");
define("CORE", SYSTEM . "core/");
define("SYSTEM_HELPER", CORE . "helper/");
// START APPLICATION VARIABLE
define("APPS", BASE . "/apps/");
define("USER_CONFIG", APPS . "config/");
define("MODEL", APPS . "model/");
define("VIEW", APPS . "view/");
define("CONTROLLER", APPS . "controller/");
define("LIBRARY", APPS . "library/");			// Support for third party PHP library (just put in this folder and load it using require() method)

// This is the main bootstrap for PHP Web Application
try {
	// Processing the Application Configuration First.
	// This process also responsible to load required classes.
	require(SYSTEM_CONFIG . "configure.php");
	
	// Set Trap for un-expected PHP Error
	// Comment line bellow to disable PHP Error handler
	ErrorHandler::GetInstance()->RegisterErrorHandler();
	
	$router = Router::GetInstance();
	$router->Initialize();		

	$dispatcher = Dispatcher::CreateInstance();
	PersistenceManager::CreateDefaultPersistence(PERSISTENCE_TYPE);
	AclManager::CreateAcl(ACL_TYPE);
	// Set global Buffering before starting execution
	ob_start();
	// In dispatcher we also create buffer in rendering process but PHP can handle multiple level buffer
	$dispatcher->Dispatch($router->ControllerName
							, $router->MethodName
							, $router->Parameters
							, $router->NamedParameters
							, $router->Namespace);
} catch (Exception $ex) {
	ErrorHandler::GetInstance()->HandleException($ex);
}

// Act as finally block after all execution finished
// This only called when redirect is not used !
Dispatcher::FlushOutput();

/*
// This one already fixed in SessionPersistence :)
ini_set('unserialize_callback_func', 'mycallback'); // set your callback_function
function mycallback($classname) {
    // just include a file containing your classdefinition
    // you get $classname to figure out which classdefinition is required
	print($classname);
	exit();
}
*/
