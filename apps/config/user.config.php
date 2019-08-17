<?php
/**
 * Suppose your application don't placed at document root of the web server then specify the relative folder here
 * Example :
 * 1. http://www.example.com							->		Folder : '' (empty string)
 * 2. http://www.example.com/folder1					->		Folder : 'folder1'
 * 2. http://www.example.com/folder1/folder2			->		Folder : 'folder1/folder2'
 *
 * For your convenience there is a script to automatically detected the folder. (No user intervention required)
 * But if you prefer to manually define you can uncomment line bellow
 */
//define("FOLDER", "");

/**
 * Define your site URL here (Your web apps) NOTE : please don't give '/' at the end
 * Warning even your application accessed using www.example.com/folder1 DON'T write http://www.example.com/folder1 instead write http://www.example.com
 */
define("DOMAIN", "http://www.example.com");

/**
 * You can use this one as Prefix Title in the Window Bar
 */
define("APP_NAME", "Rekasys - PT. Manado Karya Anugrah");

/**
 * Tell the system to use which Access Control List class.
 * Change THIS LINE into appropriate Acl class (Your Acl Class).
 * Ex: SimpleAcl class then you specify 'Simple' without quote
 * WARNING: Changes maybe require to remove all user cookies and sessions data (depend on the code implemented).
 */
define("ACL_TYPE", "RekaSys");

/**
 * Tell the system to use which persistence model.
 * Change NEXT LINE into appropriate Persistence Class !!
 * WARNING : class must implements IPersistence and put the class in persistence folder
 */
define("PERSISTENCE_TYPE", "Session");

/**
 * Tell system which default controller / default method which will be executed when there is no specific request
 */
define("DEFAULT_CONTROLLER", "home");
define("DEFAULT_METHOD", "index");

 /**
  * You can add additional controller which will be bypassed from ACL checking
  * You may add the Register/Login/Logout Controller to this one so all user can access this one by default
  * WARNING: Use with cautions ! all public methods in the controller will be accessible
  * Automatically added from system.config.php : HomeController and ErrorController
  *
  * Syntax Add    : Dispatcher::GetInstance()->AddAclBypass("[Fully Qualified Name]");
  * Syntax Remove : Dispatcher::GetInstance()->RemoveAclBypass("[Fully Qualified Name]");
  */
// Don't Forget to change this one to your actual LoginController or comment line bellow
// Dispatcher::GetInstance()->AddAclBypass("Login");
AclManager::AddBypassedAfterAuthenticated("main");
AclManager::AddBypassedAfterAuthenticated("utilities");
AclManager::AddBypassedAfterAuthenticated("notification.*");

/**
 * You can configure auto load for model(s) or libraries bellow
 * This user.config.php will be executed every time request is made by user (web browser)
 * Just use require_once(); method calling
 */
require_once(CORE . "helper/procedural_helper.php");
// autoload general functions library
require_once(LIBRARY . "gen_functions.php");

// GLOBAL VARIABLE(S)
define("HUMAN_DATE", "d M Y");
define("HUMAN_DATE_NUMERIC", "d-m-Y");
define("JS_DATE", "d-m-Y");
define("SQL_DATETIME", "Y-m-d H:i:s");
define("SQL_DATEONLY", "Y-m-d");
