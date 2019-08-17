<?php
class SessionPersistence extends ObjectExtended implements IPersistence {
	private $sid;

	function __construct() {
		try {
			session_start();
			$this->sid = session_id();
		} catch (Exception $e) {
			//print($e->getMessage());
			// We'll use error handler provided by framework
			throw new Exception("Failed in Constructing SessionPersistence object instance", -1, $e);
		}
	}

	/**
	 * Return persistence Unique Identifier. Should be different between user request
	 *
	 * @return string
	 */
	public function GetPersistenceId() {
		return $this->sid;
	}

	public function DestroyPersistence() {
		unset($_SESSION);
		session_destroy();
	}

	public function DestroyState($name) {
		unset($_SESSION[$name]);
	}

	public function LoadState($name) {
		if ($this->StateExists($name)) {
			return unserialize($_SESSION[$name]);
		} else {
			return null;
		}
	}

	public function SaveState($name, $value) {
		$_SESSION[$name] = serialize($value);
	}

	public function StateExists($name) {
		return isset($_SESSION[$name]);
	}
}

// EOF: ./system/core/persistence/session_persistence.php
