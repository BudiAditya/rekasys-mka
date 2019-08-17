<?php
class AclManager {
	private static $instance = null;
	private static $bypassedControllers = array();
	private static $bypassedAfterAuthenticated = array();

	/**
	 * Retrieve currently loaded concrete class of AclBase
	 *
	 * @static
	 * @return AclBase
	 */
	public static function GetInstance() {
		return self::$instance;
	}
	
	public static function CreateAcl($type) {
		if (self::$instance != null) {
			trigger_error("Cannot load another instance of Acl. Instance already created.", E_USER_ERROR);
		}
		$className = $type . "Acl";
		require_once(sprintf(CORE . "acl/%s_acl.php", strtolower($type)));
		if (is_subclass_of($className, "AclBase")) {
			self::$instance = new $className(self::$bypassedControllers, self::$bypassedAfterAuthenticated);
			return self::$instance;
		} else {
			return null;
		}
	}
	
	public static function DisposeAcl() {
		self::$instance = null;
		return empty(self::$instance);
	}

	/**
	 * Get all bypassed controller for current execution.
	 * NOTE: Be careful if you call this function before instance created in index.php. You may not get a complete list.
	 *
	 * @static
	 * @return array
	 */
	public static function GetBypassedControllers() {
		return array_keys(self::$bypassedControllers);
	}

	/**
	 * Add controller that will not checked against ACL.
	 * This controller will be allowed access by CheckUserAccess even these controller not specified in user ACL
	 *
	 * @static
	 * @param $fqn
	 * @throws Exception
	 */
	public static function AddBypassedController($fqn) {
		if (self::$instance != null) {
			throw new Exception("Adding bypassed controller should be done before Acl class created ! Please call this function at user.config.php.");
		}

		self::$bypassedControllers[strtolower($fqn)] = true;
	}

	/**
	 * Remove controller from bypassed list
	 *
	 * @static
	 * @param $fqn
	 * @return bool
	 * @throws Exception
	 */
	public static function RemoveBypassedController($fqn) {
		if (self::$instance != null) {
			throw new Exception("Adding bypassed controller should be done before Acl class created ! Please call this function at user.config.php.");
		}

		$fqn = strtolower($fqn);
		unset(self::$bypassedControllers[$fqn]);
		return isset(self::$bypassedControllers[$fqn]);
	}

	/**
	 * Get all bypassed after authenticated for current execution.
	 * NOTE: Be careful if you call this function before instance created in index.php. You may not get a complete list.
	 *
	 * @static
	 * @return array
	 */
	public static function GetBypassedAfterAuthenticated() {
		return array_keys(self::$bypassedAfterAuthenticated);
	}

	/**
	 * Add controller that will not checked against ACL. NOTE: executed after user is authenticated
	 * This controller will be allowed access by CheckUserAccess even these controller not specified in user ACL
	 *
	 * @static
	 * @param $fqn
	 * @throws Exception
	 */
	public static function AddBypassedAfterAuthenticated($fqn) {
		if (self::$instance != null) {
			throw new Exception("Adding bypassed after authenticated should be done before Acl class created ! Please call this function at user.config.php.");
		}

		self::$bypassedAfterAuthenticated[strtolower($fqn)] = true;
	}

	/**
	 * Remove controller from bypassed after authenticated list
	 *
	 * @static
	 * @param $fqn
	 * @return bool
	 * @throws Exception
	 */
	public static function RemoveBypassedAfterAuthenticated($fqn) {
		if (self::$instance != null) {
			throw new Exception("Adding bypassed after authenticated should be done before Acl class created ! Please call this function at user.config.php.");
		}

		$fqn = strtolower($fqn);
		unset(self::$bypassedAfterAuthenticated[$fqn]);
		return isset(self::$bypassedAfterAuthenticated[$fqn]);
	}
}

// EOF: ./system/core/acl/acl_manager.php
