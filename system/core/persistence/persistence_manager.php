<?php
class PersistenceManager {
	private static $instance = null;

	/**
	 * @static
	 * @return IPersistence
	 */
	public static function GetInstance() {
		return self::$instance;
	}
	
	public static function CreateDefaultPersistence($type) {
		if (self::$instance != null) {
			trigger_error("Cannot load another instance of Persistence. Instance already created.", E_USER_ERROR);
		}
		$className = $type . "Persistence";
		require_once(sprintf(CORE . "persistence/%s_persistence.php", strtolower($type)));
		self::$instance = new $className();
		if (!self::$instance instanceof IPersistence)
			self::$instance = null;
		return self::$instance;
	}
	
	public static function DisposeInstance() {
		self::$instance = null;
		return empty(self::$instance);
	}
}

// EOF: ./system/core/persistence/persistence_manager.php
