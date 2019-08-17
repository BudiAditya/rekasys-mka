<?php
/**
 * Encapsulation for Database engine or driver.
 * Any basic database operation is defined here and any drivers must be extend this class.
 */
abstract class ConnectorBase extends ObjectExtended {
	public $CommandText;
	protected $DuplicateErrorCode = array("Type" => "r");
	protected $ErrorCode = array("Type" => "r");
	protected $ErrorMessage = array("Type" => "r");
	protected $Reader = array("Type" => "r");
	protected $HasError = array("Type" => "r");
	protected $HaveTransaction = array("Type" => "r");

	protected $connection = null;
	/**
	 * @var ConnectorSettings
	 */
	protected $settings;
	/**
	 * @var ReaderBase
	 */
	protected $reader;
	protected $params = array();
	protected $isSortRequired = true;
	protected $isReady = false;

	public function __construct(ConnectorSettings $data) {
		$this->settings = $data;
	}

	// Forcing user to implement custom property getter
	/**
	 * @abstract
	 * @return int
	 */
	public abstract function GetDuplicateErrorCode();
	/**
	 * @abstract
	 * @return int
	 */
	public abstract function GetErrorCode();
	/**
	 * @abstract
	 * @return string
	 */
	public abstract function GetErrorMessage();
	/**
	 * @abstract
	 * @return bool
	 */
	public abstract function GetHasError();
	/**
	 * @abstract
	 * @return bool
	 */
	public abstract function GetHaveTransaction();
	// End of section

    /**
     * @return ConnectorSettings
     */
    public function GetCurrentSettings() {
        return $this->settings;
    }

	/**
	 * @return ReaderBase
	 */
	public function GetReader() {
		return $this->reader;
	}

	public function IsDuplicateError() {
		return $this->GetErrorCode() == $this->GetDuplicateErrorCode();
	}

	/**
	 * Try to open database connection. Creating Driver instance doesn't automatically open database connection.
	 * This function should be called before and checked for their return value before executing query.
	 *
	 * @abstract
	 * @return bool
	 */
	public abstract function OpenConnection();

	/**
	 * Generate last executed query (taken from CommandText property)
	 *
	 * @param bool $performParameterSubstitution => true if you want to perform parameter binding substitution
	 * @return string
	 */
	public function GenerateLastQuery($performParameterSubstitution = true) {
		if ($performParameterSubstitution) {
			return $this->CompileParameterizedQuery();
		} else {
			return $this->CommandText;
		}
	}

	/**
	 * Escaping problematic character from given value.
	 *
	 * @abstract
	 * @param mixed $value		=> value that will be escaped
	 * @param string $dataType	=> Data type of the given value. Pass 'auto' to tell engine to automatically determine data type
	 * @return mixed
	 */
	public abstract function EscapeValue($value, $dataType = "auto");

	public function AddParameter($name, $value, $dataType = "auto") {
		$this->params[$name] = new SqlParameter($name, $value, $dataType);
		$this->isSortRequired = true;
	}

	public function GetParameter($name) {
		if (!isset($this->params[$name])) {
			throw new Exception("Parameter Not Found !");
		}
		return $this->params[$name];
	}

	public function RemoveParameter($name) {
		unset($this->params[$name]);
		return !isset($this->params[$name]);
	}

	/**
	 * Should change HaveTransaction Property
	 * @abstract
	 * @return void
	 */
	public abstract function BeginTransaction();

	public abstract function ChangeDatabase($dbName);

	public function ClearParameter() {
		$this->params = array();
	}

	public function CloseReader() {
		if ($this->reader == null) {
			return;
		}
		$this->reader->CloseReader();
	}

	/**
	 * Should change HaveTransaction Property
	 * @abstract
	 * @return void
	 */
	public abstract function CommitTransaction();

	/**
	 * @abstract
	 * @param string $query
	 * @return int
	 */
	public abstract function ExecuteNonQuery($query = null);

	/**
	 * @abstract
	 * @param string $query
	 * @return ReaderBase
	 */
	public abstract function ExecuteQuery($query = null);

	/**
	 * @abstract
	 * @param string $query
	 * @return mixed
	 */
	public abstract function ExecuteScalar($query = null);

	/**
	 * @abstract
	 * @return bool
	 */
	public abstract function HasMoreResults();

	/**
	 * @abstract
	 * @return ReaderBase
	 */
	public abstract function NextResult();

	/**
	 * Should change HaveTransaction Property
	 * @abstract
	 * @return void
	 */
	public abstract function RollbackTransaction();

	public abstract function CloseConnection();

	/**
	 * Compiling query string with their associated parameter.
	 *
	 * @return string
	 */
	protected function CompileParameterizedQuery() {
		$queryString = $this->CommandText;
		if ($this->isSortRequired) {
			// Sort tha array key in reverse order to prevent similar key wrongly decoded
			// Ex : we must process ?itemId first before we process ?item
			$this->isSortRequired = krsort($this->params, SORT_STRING);
		}

		foreach ($this->params as $key => $sqlParameter) {
			// Checking if the given value is an array then we must process it before add the parameter
			if (is_array($sqlParameter->Value)) {
				// Copy array first
				$tokens = $sqlParameter->Value;
				foreach ($tokens as $idx => $token) {
					$tokens[$idx] = $this->EscapeValue($token, $sqlParameter->DataType);
				}

				$queryString = str_replace($key, "(" . implode(", ", $tokens) . ")", $queryString);
			} else {
				$queryString = str_replace($key, $this->EscapeValue($sqlParameter->Value, $sqlParameter->DataType), $queryString);
			}
		}
		return $queryString;
	}

	/**
	 * This method will check whether there is an error while opening database connection
	 * If error occurred then it'll dispatch database error page and terminating next sequence.
	 * SHOULD BE CALLED BEFORE RETURNING TO CALLER BY: OpenConnection()
	 *
	 * @param int $errCode
	 * @param string $errMessage
	 * @return void
	 */
	protected function RaiseConnectionErrorIfRequired($errCode, $errMessage) {
		if ($this->settings->RaiseConnectionError === false) {
			// Please don't debug it even there is an error :) DebugMode is false
			return;
		}

		// OK We have error to be debugged
		$this->RaiseError($errCode, $errMessage, null);
	}

	/**
	 * This method will check whether there is an error while executing query.
	 * If error occurred then it'll dispatch database error page and terminating next sequence.
	 * SHOULD BE CALLED BEFORE RETURNING TO CALLER BY: ExecuteNonQuery(), ExecuteQuery() and ExecuteScalar()
	 *
	 * @return void
	 */
	protected function RaiseQueryErrorIfRequired() {
		if ($this->settings->RaiseQueryError === false) {
			// Please don't debug it even there is an error :) DebugMode is false
			return;
		}
		if (!$this->GetHasError()) {
			// OK we don't have error even we set DEBUG
			return;
		}
		if (!$this->settings->DuplicateRaiseError && $this->GetErrorCode() == $this->GetDuplicateErrorCode()) {
			// OK this is duplicate error ! Don't raise error !
			return;
		}

		// OK We have error to be debugged
		$this->RaiseError($this->GetErrorCode(), $this->GetErrorMessage(), $this->CompileParameterizedQuery());
	}

	protected function RaiseError($errCode, $errMessage, $query) {
		// Ooppsss we MUST roll back transaction if any
		if ($this->GetHaveTransaction()) {
			$this->RollbackTransaction();
		}

		if ($this->settings->UseSqlException) {
			// User want exception to be thrown...
			throw new SqlException($this->settings->DriverType, $errMessage, $errCode);
		} else {
			// Dispatching error page !
			$dispatcher = Dispatcher::CreateInstance();
			$dispatcher->Dispatch("error", "db_error", array($errCode, $errMessage, $query), array(), null, true);
			$dispatcher->FlushOutput();
		}
		// System assumed we are in debug mode then it will terminate current sequence to make sure error message processed / shown
		// Error message will not shown if redirect occured or any next procedure override it
		exit();
	}
}

// EOF: ./system/core/connector/connector_base.php
