<?php
/**
 * Abstract class which must be extended by any Controller class that want executed by Dispatcher.
 *
 * Note for any class which extends AppController:
 * 1. ClassName must be suffixed by 'Controller'. Ex: TestController.
 * 2. Filename pattern : 'classname_controller.php'. Ex: text_controller.php
 * 3. File must be stored at 'apps/controller' folder
 * 4. Namespace concept supported by Router class and Dispatcher. Just put in to correct folder location.
 *    Ex: com.sample.TestController must be found at 'app/controller/com/sample/test_controller.php'
 *
 * @see Dispatcher
 * @see Router
 */
abstract class AppController extends ObjectExtended {
	public $MustHaveView = false;
	protected $DataForView = array("Type" => "r");
	protected $Name = array("Type" => "r");
	protected $NamedParams = array("Type" => "rw", "Value" => array());
	// These variable was used to fast access to their property
	/** @var int */
	protected $dispatcherSequence = -1;
	/** @var \ConnectorBase */
	protected $connector = null;
	/** @var array */
	protected $dataForView = array();
	/** @var array */
	protected $getData = array();
	/** @var array */
	protected $namedParams = array();
	/** @var \IPersistence */
	protected $persistence = null;
	/** @var array */
	protected $postData = array();

	/**
	 * AppController::GetDataForView()
	 *
	 * @return array consisting variable name and variable value
	 */
	public final function GetDataForView() {
		return $this->dataForView;
	}

	/**
	 * AppController::GetNamedParams()
	 *
	 * @return array of named parameters extracted from query string
	 */
	public final function GetNamedParams() {
		return $this->namedParams;
	}

	/**
	 * AppController::SetNamedParams()
	 *
	 * @param mixed $val
	 * @return void
	 */
	public final function SetNamedParams(array $val) {
		$this->namedParams = $val;
	}

	/**
	 * AppController::__construct()
	 *
	 * @param $name
	 * @param ConnectorBase $connector
	 * @param IPersistence $persistence
	 * @param array $namedParameters
	 * @param int $dispatcherSequence
	 * @return AppController
	 */
	public final function __construct($name, ConnectorBase $connector, IPersistence $persistence, array $namedParameters, $dispatcherSequence) {
		$this->Name["Value"] = $name;
		$this->connector = $connector;
		$this->persistence = $persistence;
		// Retrieving data from query string and post
		$this->getData = $_GET;
		$this->postData = array_merge($_POST, $_FILES);
		$this->namedParams = $namedParameters;
		$this->dispatcherSequence = $dispatcherSequence;
		// OK because the constructor declared as final then we provide another method act's as default constructor...
		$this->Initialize();
	}

	/**
	 * AppController::Initialize()
	 * Act as constructor because we mark __construct as final so it's can't be override by inheriting class
	 *
	 * @return void
	 */
	protected abstract function Initialize();

	/**
	 * AppController::Remove()
	 * Remove a variable from the array used in 'view'
	 *
	 * @param string $name
	 * @return void
	 */
	public final function Remove($name) {
		if (isset($this->dataForView[$name]))
			unset($this->dataForView[$name]);
	}

	/**
	 * AppController::Set()
	 * Set a variable with specific name for used in 'view'
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public final function Set($name, $value) {
		$this->dataForView[$name] = $value;
	}

	/**
	 * AppController::SetFromArray()
	 * Bulk set from array to VIEW. This method will merge $vars into DataForView instead of replace it.
	 *
	 * @param array $vars
	 * @param bool $overrideSameName
	 * @return void
	 */
	public final function SetFromArray(array $vars, $overrideSameName = true) {
		foreach ($vars as $name => $value) {
			if (!$overrideSameName && array_key_exists($name, $this->dataForView)) {
				// OK Don't override same variable name for view (skip it)
				continue;
			}

			$this->dataForView[$name] = $value;
		}
	}

	/**
	 * AppController::FixClassDefinition()
	 * Used to Fix Incomplete Class caused by serialization and un-serialization. MAKE SURE CLASS DEFINITION ALREADY LOADED !
	 * This case is happened when session is started but class definition is not loaded yet
	 *
	 * @param mixed $incompleteObject
	 * @return mixed
	 */
	public function FixClassDefinition($incompleteObject) {
		return unserialize(serialize($incompleteObject));
	}

	/**
	 * AppController::GetGetValue()
	 * Try to get values from user query string (GET data). Return $default if keyName not found
	 *
	 * @param string $keyName
	 * @param mixed $default
	 * @return mixed
	 */
	public function GetGetValue($keyName, $default = null) {
		return isset($this->getData[$keyName]) ? $this->getData[$keyName] : $default;
	}

	/**
	 * AppController::GetPostValue()
	 * Try to get values from user POST data (from <form method="POST" /> including uploaded file(s)). Return $default if keyName not found
	 *
	 * @param string $keyName
	 * @param mixed $default
	 * @return mixed
	 */
	public function GetPostValue($keyName, $default = null) {
		return isset($this->postData[$keyName]) ? $this->postData[$keyName] : $default;
	}

	/**
	 * AppController::GetNamedValue()
	 * Try to get values from Named parameters. Return $default if keyName not found
	 *
	 * @param string $keyName
	 * @param mixed $default
	 * @return mixed
	 */
	public function GetNamedValue($keyName, $default = null) {
		return isset($this->namedParams[$keyName]) ? $this->namedParams[$keyName] : $default;
	}
}

// EOF: ./system/core/app_controller.php
