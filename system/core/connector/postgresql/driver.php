<?php

class PostgreSqlConnector extends ConnectorBase {
	/**
	 * @return int
	 */
	public function GetDuplicateErrorCode() {
		return 23505;
	}

	/**
	 * @return int
	 */
	public function GetErrorCode() {
		if ($this->reader == null) {
			return null;
		}
		//return pg_result_error($this->reader->GetNativeReader());
		return pg_result_error($this->reader->GetNativeReader());
	}

	/**
	 * @return string
	 */
	public function GetErrorMessage() {
		if (!$this->connection) {
			return null;
		}
		return pg_last_error($this->connection);
}

	/**
	 * @return bool
	 */
	public function GetHasError() {
		if (!$this->connection) {
			return false;
		}
		return pg_last_error($this->connection) != "";
	}

	/**
	 * @return bool
	 */
	public function GetHaveTransaction() {
		if (!$this->connection) {
			return false;
		}
		return pg_transaction_status($this->connection) == PGSQL_TRANSACTION_ACTIVE;
	}

	/**
	 * @Overriding We must override base implementation because in PostgreSQL we have some difficulties in retrieving error code
	 *
	 * @return bool
	 */
	public function IsDuplicateError() {
		$errMsg = $this->GetErrorMessage();
		return stripos($errMsg, "duplicate key") !== false;
	}

	/**
	 * Try to open database connection. Creating Driver instance doesn't automatically open database connection.
	 * This function should be called before and checked for their return value before executing query.
	 *
	 * @return bool
	 */
	public function OpenConnection() {
		if ($this->connection === null) {
			$conString = "host='%s' port=%d dbname='%s' user='%s' password='%s'";
			$conString = sprintf($conString, $this->settings->Host, $this->settings->Port, $this->settings->DatabaseName, $this->settings->Username, $this->settings->Password);
			if ($this->settings->SuppressPhpError) {
				// User want any error to be suppressed...
				$this->connection = @pg_connect($conString);
			} else {
				$this->connection = pg_connect($conString);
			}
		}

		// OK if reach there means we already trying open connection
		if ($this->connection === false) {
			// Connection open failed !
			$this->RaiseConnectionErrorIfRequired(null, "Failed to establish PostgreSql connection");
			return false;
		}

		if (pg_connection_status($this->connection) !== PGSQL_CONNECTION_OK) {
			// Hmm.. error while opening connection ?
			$this->RaiseConnectionErrorIfRequired(null, pg_last_error($this->connection));
			return false;
		}

		return true;
	}

	/**
	 * Escaping problematic character from given value.
	 * For this web application we have convention that boolean value will use 1 and 0 instead of native postgresqsl data type
	 *
	 * @param mixed $value        => value that will be escaped
	 * @param string $dataType    => Data type of the given value. Pass 'auto' to tell engine to automatically determine data type
	 * @throws SqlException
	 * @return mixed
	 */
	public function EscapeValue($value, $dataType = "auto") {
		$dataType = strtolower($dataType);
		if (is_null($value)) {
			return "NULL";
		}

		switch ($dataType) {
			case "string";
			case "varchar";
			case "char";
				return sprintf("'%s'", pg_escape_string($this->connection, $value));
			case "int";
			case "float";
			case "double";
			case "numeric";
				return pg_escape_string($this->connection, $value);
			case "bool";
			case "boolean";
				return $value ? 1 : 0;
			case "native_boolean":
				return $value ? "true" : "false";
			case "null";
				return "NULL";
			case "auto";
				if (is_numeric($value)) {
					return pg_escape_string($this->connection, $value);
				} else if(is_string($value)) {
					return sprintf("'%s'", pg_escape_string($this->connection, $value));
				} else if (is_bool($value)) {
					return $value ? 1 : 0;
				} else {
					throw new SqlException("PostgreSql", "Auto detection in PostgreSql::EscapeValue() doesn't support datatype: '" . gettype($value) . "' (class name: " . get_class($value) . ") !", -1);
				}
				break;
			default:
				throw new SqlException("PostgreSql", "Manual detection in PostgreSql::EscapeValue() doesn't support datatype: '" . $dataType . "' !", -1);
		}
	}

	/**
	 * Should change HaveTransaction Property
	 * @return void
	 */
	public function BeginTransaction() {
		if (!$this->OpenConnection()) {
			return;
		}
		pg_query("BEGIN");
	}

	public function ChangeDatabase($dbName) {
		if (!$this->OpenConnection()) {
			return;
		}
		pg_query("USE " . $dbName);
	}

	/**
	 * Should change HaveTransaction Property
	 * @return void
	 */
	public function CommitTransaction() {
		if (!$this->OpenConnection()) {
			return;
		}
		pg_query("COMMIT");
	}

	/**
	 * @param string $query
	 * @return int
	 */
	public function ExecuteNonQuery($query = null) {
		if (!$this->OpenConnection()) {
			return -1;
		}

		if (!empty($query)) {
			$this->CommandText = $query;
		}

		$rs = @pg_query($this->connection, $this->CompileParameterizedQuery());
		// HACK agar error code bisa di detect
		if ($rs) {
			$this->reader = new PostgreSqlReader($rs);
		} else {
			$this->reader = null;
		}
		$this->RaiseQueryErrorIfRequired();

		return $rs ? pg_affected_rows($rs) : -1;
	}

	/**
	 * @param string $query
	 * @return ReaderBase
	 */
	public function ExecuteQuery($query = null) {
		if (!$this->OpenConnection()) {
			$this->reader = null;
			return $this->reader;
		}

		if (!empty($query)) {
			// Hmmm don't use setter method because it will clear the parameter(s)
			$this->CommandText = $query;
		}

		$rs = @pg_query($this->connection, $this->CompileParameterizedQuery());
		$this->RaiseQueryErrorIfRequired();

		if ($rs) {
			$this->reader = new PostgreSqlReader($rs);
		} else {
			$this->reader = null;
		}
		return $this->reader;
	}

	/**
	 * @param string $query
	 * @return mixed
	 */
	public function ExecuteScalar($query = null) {
		if (!$this->OpenConnection()) {
			return null;
		}

		if (!empty($query)) {
			// Hmmm don't use setter method because it will clear the parameter(s)
			$this->CommandText = $query;
		}

		// For performance purpose I use native reader instead of PostgreReader
		$rs = @pg_query($this->connection, $this->CompileParameterizedQuery());
		// HACK agar error code bisa di detect
		$this->reader = new PostgreSqlReader($rs);
		$this->RaiseQueryErrorIfRequired();

		if ($rs) {
			$row = pg_fetch_row($rs);
			$temp = $row[0];
		} else {
			$temp = null;
		}
		return $temp;
	}

	/**
	 * @return bool
	 */
	public function HasMoreResults() {
		// Looks alike PHP driver is not support this one
		return false;
	}

	/**
	 * @throws Exception
	 * @return ReaderBase
	 */
	public function NextResult() {
		throw new Exception("PostgreSqlReader doesn't support multiple result set query !");
	}

	/**
	 * Should change HaveTransaction Property
	 * @return void
	 */
	public function RollbackTransaction() {
		pg_query("ROLLBACK");
	}

	public function CloseConnection() {
		pg_close($this->connection);
	}

	/**
	 * Overriding base implementation required to check unique key constraint using error message instead of error code
	 *
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
//		$errMsg = $this->GetErrorMessage();
//		$isDuplicate = stripos($errMsg, "duplicate key") !== false;
		if (!$this->settings->DuplicateRaiseError && $this->IsDuplicateError()) {
			// OK this is duplicate error ! Don't raise error !
			return;
		}

		// OK We have error to be debugged
		$this->RaiseError($this->GetErrorCode(), $this->GetErrorMessage(), $this->CompileParameterizedQuery());
	}
}
