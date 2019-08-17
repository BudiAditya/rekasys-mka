<?php
/**
 * Framework custom class for SqlException. Please don't confused with native PHP SqlException class
 */
class SqlException extends Exception {
	public $ConnectorType;

	public function __construct($connectorType, $message, $code, $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->ConnectorType = $connectorType;
	}
}

// EOF: ./system/core/connector/sql_exception.php
