<?php
/**
 * Factory class intended to creating, retrieving any instance of ConnectorBase class.
 * Every connection is pooled by this class.
 *
 * @see ConnectorBase
 * @see ReaderBase
 */
class ConnectorManager {
	private static $defaultAliasName = null;
	private static $connectionPool = array();

	/**
	 * Used to retrieve default database connection
	 *
	 * @static 
	 * @return null|ConnectorBase
	 */
	public static function GetDefaultConnector() {
		return ConnectorManager::GetPool(ConnectorManager::$defaultAliasName);
	}

	/**
	 * Loading database layer based on ConnectorSettings.
	 * Automatically called by CreatePool() to maintain driver instance or can be called directly
	 *
	 * @static
	 * @throws Exception
	 * @param ConnectorSettings $settings
	 * @return ConnectorBase
	 */
	private static function LoadDriver(ConnectorSettings $settings) {
		// This path will point to driver folder package
		$path = sprintf("%sconnector/%s/", CORE, strtolower($settings->DriverType));
		if (!file_exists($path)) {
			throw new Exception(sprintf("Invalid SQL driver type specified (Unknown: %s) ! Make sure you have correct spelling or installed driver package.", $settings->DriverType));
		}

		// Loading driver package(s)
		$packages = array("driver.php", "reader.php");
		foreach ($packages as $package) {
			$file = $path . $package;
			if (!file_exists($file)) {
				throw new Exception(sprintf("%s driver packages is incomplete ! Please contact your driver vendor for package updates. Missing: ", $settings->DriverType, $package));
			}

			require_once($file);
		}

		// Load complete now we create Connector object.
		$className = $settings->DriverType . "Connector";
		if (!is_subclass_of($className, "ConnectorBase")) {
			throw new Exception(sprintf("%s is not sub-class of ConnectorBase ! A proper driver should derived from ConnectorBase class ! Please contact your driver vendor !"));
		}

		return new $className($settings);
	}

	/**
	 * Used to create default database connection. This function automatically called in index.php
	 * For database type and setting(s) are stored in user.config.php and database.config.php
	 * Default connector now pooled in connection pool (since ver 4.2)
	 * DON'T CALL THIS METHOD MANUALLY
	 *
	 * @static
	 * @param ConnectorSettings $settings
	 * @param string $aliasName
	 * @return null|ConnectorBase
	 */
	public static function CreateDefaultConnector(ConnectorSettings $settings, $aliasName = "default") {
		if (ConnectorManager::$defaultAliasName != null) {
			trigger_error("Cannot load another instance of Connector. Default instance already created. Alias name: " . ConnectorManager::$defaultAliasName, E_USER_ERROR);
			return ConnectorManager::GetPool(ConnectorManager::$defaultAliasName);
		}

		ConnectorManager::$defaultAliasName = $aliasName;
		return ConnectorManager::CreatePool($aliasName, $settings);
	}

	/**
	 * Destroying default connector from pool
	 *
	 * @static
	 * @return void
	 */
	public static function DisposeConnector() {
		ConnectorManager::DestroyPool(ConnectorManager::$defaultAliasName);
		ConnectorManager::$defaultAliasName = null;
	}

	/**
	 * Used to load another type of ConnectorBase. In case we need more than one database connection we must use this function to load another one
	 * NOTE:
	 * - As method name implied after creating this connection is automatically pooled so we can re-use them.
	 * - This connector doesn't automatically assigned to Controller NOR Model ! You must call this method or using ConnectorManager::GetPool()
	 *
	 * @static
	 * @param string $name
	 * @param ConnectorSettings $settings
	 * @return null|ConnectorBase
	 */
	public static function CreatePool($name, ConnectorSettings $settings) {
		if (array_key_exists($name, ConnectorManager::$connectionPool)) {
			return ConnectorManager::$connectionPool[$name];
		}

		ConnectorManager::$connectionPool[$name] = ConnectorManager::LoadDriver($settings);
		return ConnectorManager::$connectionPool[$name];
	}

	/**
	 * Retrieve existing pooled connection.
	 *
	 * @static
	 * @param string $name
	 * @return null|ConnectorBase
	 */
	public static function GetPool($name) {
		return array_key_exists($name, ConnectorManager::$connectionPool) ? ConnectorManager::$connectionPool[$name] : null;
	}

	/**
	 * Rollback, Closing, Removing a connection from the pool
	 *
	 * @static
	 * @param string $name
	 * @return void
	 */
	public static function DestroyPool($name) {
		$name = strtolower($name);

		$temp = ConnectorManager::GetPool($name);
		if ($temp != null) {
			// Rollback any transaction and closing database connection
			$temp->RollbackTransaction();
			$temp->CloseConnection();
			unset($temp);
		}

		unset(ConnectorManager::$connectionPool[$name]);
	}
}

// EOF: ./system/core/connector/connector_manager.php
