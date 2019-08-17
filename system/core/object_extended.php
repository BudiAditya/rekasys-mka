<?php
/**
 * This class usage is to add .NET property and auto property concept into PHP. In PHP there is 'Property' concept wrapped using public variable of the class.
 * This public variable always acting as Read/Write property and can't have custom Get/Set method if needed.
 * Suppose we need read only property then how to achieve that ? Easy... just extends your class from ObjectExtended class then follow this rules:
 *
 * 1. Your property must be declared as protected ! (Even property concept is public but you MUST declare as protected then magic method will works)
 *		 NOTE: it's should protected not private !!! if you declare as private magic method will fail !
 *		 Example: protected $MyProperty;
 * 2. Tell system that $MyProperty should act as public property in this way:
 *		 protected $MyProperty = array("Type" => "rw"[, "Value" => null][, "Map" => "database_column_name"]);
 *
 *		 Property created by assigning array into protected variable (array keys will be explained later)
 * 3. DONE ! You already created AUTO PROPERTY named 'MyProperty' with notes:
 *		 - To read value from object instance: $temp = $obj->MyProperty;
 *		 - To set value into object instance: $obj->MyProperty = "Some Other Value";
 *		 - Property actual value stored in $MyProperty["Value"] because it's use AUTO PROPERTY.
 *		 - Value manipulation within class should use $this->MyProperty["Value"] and don't manipulate $this->MyProperty directly or property will broke
 *
 * Array Key Explanation:
 *	 'Type'	: define property type as Read/Write (rw), ReadOnly (r), WriteOnly (w)
 *	 'Value'	: used to store actual property value if you're using AUTO PROPERTY
 *	 'Map'	: define property mapping to database column name. Ex: if this property mapped into 'my_column' then give 'my_column' as 'Map' (read EntityBase class for more detailed explanation)
 *
 * CUSTOM PROPERTY:
 * In case you need some data validation before data retrieval or data assignment you need to use CUSTOM PROPERTY !
 * Custom property can be achieved by add method accessor: GetXXX() and SetXXX($value). From example above now we convert into custom property:
 *
 * 1. Change declaration into: protected $MyProperty = array("Type" => "rw"[, "Map" => "database_column_name"]);
 *		 NOTE: remove "Value" key from array definition.
 * 2. Add private/protected variable to act as value holder (backing variable)
 *		 Example: protected $_myPropertyValue = null;
 * 3. Add Get function for the property and declare as public (because property is defined as Read/Write).
 *		NAMING CONVENTION FOR GET ACCESSOR: public function Get[PropertyName]()
 *		 Example: public function GetMyProperty() { return $this->_myPropertyValue; }
 * 4. Add Set function for the property and declare as public (because property is defined as Read/Write).
 *		 NAMING CONVENTION FOR SET ACCESSOR: public function Set[PropertyName]($value)
 *		 Example: public function SetMyProperty($value) { $this->_myPropertyValue = $value; }
 *
 * NOTES FOR CUSTOM PROPERTY:
 *	 - Backing variable is not a MUST but VERY RECOMMENDED !
 *	 - You can define some data validation in Get or Set Accessor before retrieving or assigning value
 *	 - It's COMPULSORY if your property type is Read/Write to have Get and Set Accessor instead only one of them
 *	   In above scenario if your property type is 'rw' then you only have Get method then for assigning value will be done by AUTO PROPERTY into $this->MyProperty["Value"] instead of $this->_myPropertyValue
 *		Having only one method accessor for 'rw' property can cause UNEXPECTED BUG IN YOUR CODE
 *		If you insist to have only one accessor then make your mind THAT CUSTOM ACCESSOR HAVE PRIORITY OTHER THAN AUTO PROPERTY
 *	 - Get and Set method is preferable to have public visibility rather than protected/private (yes you can have these visibility also for method accessor)
 *	   Reason: backward compatibility for PHP/Java user which implements Get Set method for property (this style is purely available in .net platform)
 *	 - IF YOUR IDE SUPPORT FOR CLASS METADATA EXTRACTION (for Intellisense usage) AND COMPLAINING PROTECTED ACCESS in $obj->MyProperty THEN IGNORE IT !!!!! This code 100% work because of magic method __get and __set
 */
class ObjectExtended {
	/**
	 * This function act as default checking of a Property and Should be called first by all of the PHP magic method
	 * This used for enforce class consistency. PHP allow dynamically property creation which not possible in other OOP Lang
	 *
	 * @throws Exception when property name is not found ! usually have wrong capital writing
	 * @param $name		=> property name
	 * @return void
	 */
	private function CheckPropertyExistance($name) {
		// #1 Check whetever this class have variable with this name
		$flag = isset($this->{$name});
		// #2 Probing whetever this variable is an array or not
		if ($flag) {
			$flag = $flag && is_array($this->{$name});
		}
		// #3 When is an array then check the compulsory Key for Property
		if ($flag) {
			$flag = $flag && isset($this->{$name}["Type"]);
		}
		if (!$flag) {
			throw new Exception(sprintf("There is no such property %s.%s in this class object", get_class($this), $name));
		}
	}

	/**
	 * PHP magic method __get() called when user trying to access protected/private member of a class
	 * This will be used to handling data retrieval of a property
	 *
	 * @throws Exception
	 * @param $name			=> Property name
	 * @return mixed
	 */
	public function __get($name) {
		$this->CheckPropertyExistance($name);
		if (strpos($this->{$name}["Type"], "r") === false) {
			throw new Exception("Property '$name' has no getter (Probaly Write Only)");
		}

		// Check whether user defined the custom accessor method or not
		$handler = "Get" . $name;
		if (method_exists($this, $handler)) {
			return $this->$handler();
		} else {
			if (array_key_exists("Value", $this->{$name})) {
				return $this->{$name}["Value"];
			} else {
				$message = sprintf("Trying to access Incomplete Auto-Property of %s.%s ! Auto-Property without 'Value' key defined !", get_class($this), $name);
				throw new Exception($message);
			}
		}
	}

	/**
	 * PHP magic method __set() called whenever user trying to assign some value into protected/private member of a class
	 * Handling for value assignment
	 *
	 * @throws Exception
	 * @param $name
	 * @param $value
	 * @return void
	 */
	public function __set($name, $value) {
		$this->CheckPropertyExistance($name);
		if (strpos($this->{$name}["Type"], "w") === false) {
			throw new Exception("Property '$name' has no setter (Probaly Read Only)");
		}

		// Check whetever user defined the custom accessor method or not
		$handler = "Set" . $name;
		if (method_exists($this, $handler)) {
			$this->$handler($value);
		} else {
			$this->{$name}["Value"] = $value;
		}
	}

	/**
	 * PHP magic method __isset()
	 *
	 * @param $name
	 * @return bool
	 */
	public function __isset($name) {
		$temp = $this->__get($name);
		return isset($temp);
	}

	/**
	 * PHP magic method __unset() is called whenever you call unset()
	 * Because we can't remove property from a class (it's doesn't make sense !) then we just remove their value
	 *
	 * @param $name		=> Property name
	 * @return void
	 */
	public function __unset($name) {
		$this->__set($name, null);
	}

	public function __toString() {
		return get_class($this);
	}

	/**
	 * This method will convert your class property into associative array. This useful if you want to serialize data into JSON format.
	 *
	 * @return array
	 */
	public function ToJsonFriendly() {
		$buff = array();
		// For safety reason some variable must be nulled whenever ToJsonFriendly called
		$forceNull = array("connector");

		foreach ($this as $propName => $propValue) {
			if (in_array($propName, $forceNull)) {
				$propValue = null;
			}

			if (is_array($propValue)) {
				// Maybe this array following our property pattern
				if (array_key_exists("Type", $propValue)) {
					// Treat as Property...
					if ($propValue["Type"] == "w") {
						// This property is write only
						continue;
					}

					$buff[$propName] = $this->__get($propName);
				} else {
					// Regular array
					$buff[$propName] = $propValue;
				}
			} else {
				if (is_resource($propValue)) {
					// Impossible to serialize resource type
					continue;
				}
				$buff[$propName] = $propValue;
			}
		}

		return $buff;
	}

	public function DispatchMethod($methodName, $params = array()) {
		switch (count($params)) {
			case 0:
				return $this->{$methodName}();
			case 1:
				return $this->{$methodName}($params[0]);
			case 2:
				return $this->{$methodName}($params[0], $params[1]);
			case 3:
				return $this->{$methodName}($params[0], $params[1], $params[2]);
			case 4:
				return $this->{$methodName}($params[0], $params[1], $params[2], $params[3]);
			case 5:
				return $this->{$methodName}($params[0], $params[1], $params[2], $params[3], $params[4]);
			default:
				return call_user_func_array(array(&$this, $methodName), $params);
		}
	}
}

// EOF: ./system/core/object_extended.php
