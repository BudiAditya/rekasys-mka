<?php
class MySqliConnector extends ConnectorBase {
	private $haveTransaction = false;

	// START - Accessor method for respective property
	public function GetErrorCode() {
		if (!$this->isReady) {
			return null;
		}
		return $this->connection->errno;
	}

	public function GetErrorMessage() {
		if (!$this->isReady) {
			return null;
		}
		return $this->connection->error;
	}

	public function GetHasError() {
		if (!$this->isReady) {
			return false;
		}
		return $this->connection->errno != 0;
	}

	public function GetHaveTransaction() {
		return $this->haveTransaction;
	}

	public function GetDuplicateErrorCode() {
		return 1062;
	}
	// END - Accessor method for respective property

	public function __construct(ConnectorSettings $data) {
		parent::__construct($data);
	}

	public function OpenConnection() {
		if ($this->connection === null) {
			if ($this->settings->SuppressPhpError) {
				// User want any error to be suppressed...
				$this->connection = @new mysqli($this->settings->Host, $this->settings->Username, $this->settings->Password, $this->settings->DatabaseName, $this->settings->Port);
			} else {
				$this->connection = new mysqli($this->settings->Host, $this->settings->Username, $this->settings->Password, $this->settings->DatabaseName, $this->settings->Port);
			}

            $this->connection->set_charset("utf8");
		}

		// OK if reach there means we already trying open connection
		if ($this->connection->connect_errno !== 0) {
			// Hmm.. error while opening connection ?
			//throw new SqlException("MySqli", $this->connection->connect_error, $this->connection->connect_errno);
			$this->RaiseConnectionErrorIfRequired($this->connection->connect_errno, $this->connection->connect_error);
			return false;
		}

		$this->isReady = true;
		return true;
	}

	public function EscapeValue($value, $dataType = "auto") {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}
		$dataType = strtolower($dataType);

		switch ($dataType) {
			case "string":
			case "varchar":
			case "char":
				return sprintf("'%s'", $this->connection->real_escape_string($value));
			case "int":
			case "float":
			case "double":
			case "numeric":
				return $this->connection->real_escape_string($value);
			case "bool":
			case "boolean":
				return $value ? "1" : "0";
			case "null":
				return "NULL";
			case "auto":
				if (is_null($value)) {
					return "NULL";	// Tell MySQL to insert NULL values instead of 'NULL' (string)
				} else if (is_numeric($value)) {
					return $this->connection->real_escape_string($value);
				} else if(is_string($value)) {
					return sprintf("'%s'", $this->connection->real_escape_string($value));
				} else if (is_bool($value)) {
					// When this connector written MySQL doesn't have native support for boolean
					// They use tinyint(1) for the boolean value
					return $value ? "1" : "0";
				} else {
					throw new SqlException("MySqli", "Auto detection in MySqli::EscapeValue() doesn't support datatype: '" . gettype($value) . "' (class name: " . get_class($value) . ") !", -1);
				}
				break;
			default:
				throw new SqlException("MySqli", "Manual detection in MySqli::EscapeValue() doesn't support datatype: '" . $dataType . "' !", -1);
		}
	}

	public function BeginTransaction() {
		if (!$this->isReady) {
			if (!$this->OpenConnection()) {
				throw new SqlException("MySqli", "Failed to open connection", -1);
			}
		}
		$this->haveTransaction = true;
		$this->connection->autocommit(false);
	}

	public function ChangeDatabase($dbName) {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}
		$this->connection->select_db($dbName);
	}

	public function CommitTransaction() {
		$this->connection->commit();
		$this->haveTransaction = false;
		$this->connection->autocommit(true);
	}

	public function ExecuteNonQuery($query = null) {
		if (!$this->isReady) {
			if (!$this->OpenConnection()) {
				return -1;
			}
		}

		if (!empty($query)) {
			// Hmmm don't use setter method because it will clear the parameter(s)
			$this->CommandText = $query;
		}

		$this->MySqliFix();

		$rs = $this->connection->query($this->CompileParameterizedQuery());
		$this->RaiseQueryErrorIfRequired();

		return $rs ? $this->connection->affected_rows : -1;
	}

	public function ExecuteQuery($query = null) {
		if (!$this->isReady) {
			if (!$this->OpenConnection()) {
				$this->reader = null;
				return $this->reader;
			}
		}

		if (!empty($query)) {
			// Hmmm don't use setter method because it will clear the parameter(s)
			$this->CommandText = $query;
		}

		$this->MySqliFix();
		$flag = $this->connection->multi_query($this->CompileParameterizedQuery());
		$this->RaiseQueryErrorIfRequired();

		if ($flag) {
			$this->reader = new MysqliReader($this->connection->store_result());
		} else {
			$this->reader = null;
		}
		return $this->reader;
	}

	public function ExecuteScalar($query = null) {
		if (!$this->isReady) {
			if (!$this->OpenConnection()) {
				return null;
			}
		}

		if (!empty($query)) {
			// Hmmm don't use setter method because it will clear the parameter(s)
			$this->CommandText = $query;
		}

		$this->MySqliFix();

		// For performance purpose I use native reader instead of MysqliReader
		$rs = $this->connection->query($this->CompileParameterizedQuery());
		$this->RaiseQueryErrorIfRequired();

		if ($rs) {
			$row = $rs->fetch_array();
			$rs->close();
			$temp = $row[0];
		} else {
			$temp = null;
		}
		return $temp;
	}

	public function HasMoreResults() {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}
		return $this->connection->more_results();
	}

	public function NextResult() {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}

		$flag = $this->HasMoreResults();
		if ($flag) {
			$this->connection->next_result();
			$this->reader = new MysqliReader($this->connection->store_result());
		}
		return $flag ? $this->reader : null;
	}

	public function RollbackTransaction() {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}

		$this->connection->rollback();
		$this->haveTransaction = false;
		$this->connection->autocommit(true);
	}

	public function CloseConnection() {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}

		$this->connection->close();
		$this->connection = null;
	}

	private function MySqliFix() {
		if (!$this->isReady) {
			throw new SqlException("MySqli", "Connection not ready yet", -1);
		}

		// Stupid Fix for MySQLi Extension caused by stored procedure call
		while ($this->HasMoreResults()) {
			$this->NextResult();
		}
	}
}

// EOF: ./system/core/connector/mysqli/driver.php
