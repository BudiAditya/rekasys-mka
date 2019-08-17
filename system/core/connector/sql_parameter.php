<?php

/**
 * This class used as intermediate helper in Parameter creation
 * Because PHP doesn't support strong datatype then in EscapeValue() we implement datatype auto detection.
 *
 * Problems arrive when we want to store string which zero padded such as '00001'.
 * Using auto detection query generation will output 00001 (because it's numerical form) instead of '00001
 * NOTE: the first one will be treated as 1 (int) in some database .
 *
 * Hence this class used to overcome this limitation. Using this class you can force datatype for a parameter
 * NOTE: Please refer to database driver API for supported datatype.
 */
class SqlParameter {
	public $Name;
	public $Value;
	public $DataType;

	public function __construct($name, $value = null, $dataType = "auto") {
		$this->Name = $name;
		$this->Value = $value;
		$this->DataType = $dataType;
	}
}
