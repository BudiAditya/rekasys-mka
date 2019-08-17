<?php
// This file also act as Class File Loader...
// Used to load required file / classes used in the application
// Note core file are automatically loaded but Model classes are not automatically loaded to prevent resource consumption

// Importing core libs
require_once(CORE . "object_extended.php");		// Used as base of every class (to extend class capabilities with Property)
require_once(CORE . "entity_base.php");			// Used as base class for Model
require_once(CORE . "error_handler.php");		// Used for custom error handling
require_once(CORE . "route_data.php");			// Used to store used request data
require_once(CORE . "router.php");				// Used to translate user request into appropriate controller and method
require_once(CORE . "hook/i_router_hook.php");	// This interface will be used contract if application running in MAINTENANCE MODE
require_once(CORE . "dispatcher.php");			// Used to dispatch task (Create controller and executing action/method)
require_once(CORE . "app_controller.php");		// Base class of concrete controller
require_once(CORE . "helper/app_helper.php");	// Basic Helper should be loaded :)

// Importing Database settings
// You will get some error message in PHP log as long as you didn't provide correct DBase settings in apps/database.config.php
require_once(CORE . "connector/connector_manager.php");
require_once(CORE . "connector/connector_base.php");
require_once(CORE . "connector/connector_settings.php");
require_once(CORE . "connector/reader_base.php");
require_once(CORE . "connector/sql_exception.php");
require_once(CORE . "connector/sql_parameter.php");


// Importing ACL libs (Used to determine user access)
require_once(CORE . "acl/acl_base.php");
require_once(CORE . "acl/acl_manager.php");
require_once(CORE . "acl/group.php");
require_once(CORE . "acl/user.php");

// Persistence module here....
require_once(CORE . "persistence/i_persistence.php");
require_once(CORE . "persistence/persistence_manager.php");

// Load System Configuration
// Some of the config from configure.php are moved to system.config.php
require(SYSTEM_CONFIG . "system.config.php");

// Load Database setting from user directory (so we can upgrade the framework without any problem)
// Database setting also moved from config directory to user application directory to prevent problem when user upgrading framework
if (file_exists(USER_CONFIG . "database.config.php")) {
	require_once(USER_CONFIG . "database.config.php");
}

// After we finished with system initialization then we proceed with user settings...
// New concept for user to have their own config(s)
require(USER_CONFIG . "user.config.php");
$file = USER_CONFIG . "maintenance.config.php";
if (file_exists($file)) {
	require($file);
}

// POST CONFIGURE SYSTEM

// Code to automatically determine FOLDER
// Folder are calculated based on index.php script location (Adding support if bootstrap filename changed)
// Ex : /xxx/yyy/zzz/index.php -> we must get rid of first '/' and '/index.php'
if (!defined("FOLDER")) {
	$scriptName = $_SERVER["SCRIPT_NAME"];
	$tokens = explode("/", $scriptName);

	// Remove the filename ('index.php' or other name)
	unset($tokens[count($tokens) - 1]);

	// Remove first empty array
	if (empty($tokens[0])) {
		unset($tokens[0]);
	}

	$scriptName = implode("/", $tokens);
	define("FOLDER", $scriptName);
}

// Make sure some global variable declared
if (!defined("IS_MAINTENANCE_MODE")) {
	define("IS_MAINTENANCE_MODE", false);
}

// EOF: ./system/config/configure.php
