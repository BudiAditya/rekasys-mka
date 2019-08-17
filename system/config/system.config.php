<?php
// These section containing configuration required by the framework. Usually you don't need to editing this one
// All user configurable option placed on user.config.php
// This configuration will affect the whole system so please be careful when editing this one

/**
 * MVC Framework Version string
 */
define("FRAMEWORK_VERSION", "5.1");

/**
 * Used to prefixes the method name that conflicting with PHP keyword used in dispatcher.php
 * Ex: list() in controller will throw error because list is reserved keyword
 * WARNING : THIS WILL APPLY TO METHOD NAME ONLY ! DON'T PREFIX THE view file with this one
 */
define("PREFIX_CONFLICT_METHOD_NAME", "_");

/**
 * Tell the engine whether to use mod_rewrite module or 'force clean url'
 * Use the 'force clean url' if your server doesn't have the ability to re-writing url at runtime
 * Un-comment line bellow to 'force clean url' and specify the base script name if not index.php
 * Some thought...
 *		mod_rewrite			: http://www.example.com/controller/method/param1/param2
 *		force clean url		: http://www.example.com/index.php/controller/method/param1/param2
 * Note: If you were change the bootstrap filename then this is also changed !
 */
//define("BASE_NAME", "index.php");

/**
 * Since we have Router class now is possible to reference a namespace using folder style in query string
 * This setting used in AppHelper class to determine whether namespace are converted into slash or still remain in dot
 */
define("PREFER_SLASH", false);

/**
 * Tell the AclManager to ignore some controller from being checked against ACL
 * This useful for ErrorController and HomeController which are default to this framework and every user should have access to this controller
 */
AclManager::AddBypassedController("home");
AclManager::AddBypassedController("error");

/**
 * This section are for 'Router' class settings.
 * Please carefully when change this section it's will affect the whole application
 */

// For URL_REWRITE_TO must be the same key with the .htaccess file from root directory of your application
// Please careful when change this one. It can conflicting with your GET data. Please choose key that not used in you apps
define("URL_REWRITE_TO", "reWrittenUrl");

// Moved into user.config.php
//define("DEFAULT_CONTROLLER", "home");
//define("DEFAULT_METHOD", "index");

// EOF: ./system/config/system.config.php
