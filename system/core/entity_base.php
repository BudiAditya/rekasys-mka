<?php
/**
 * Act as base class for the model. This class adding automatic data binding from database.
 * This class also have connector to the database.
 *
 * @see ConnectorManager
 * @see ConnectorBase
 */
abstract class EntityBase extends ObjectExtended implements Serializable {
	// Connector instance for performing database task related.
	protected $connector;

	/**
	 * EntityBase::__construct()
	 * This constructor must be called if the derived class override the constructor
	 * PHP doesn't automatically called parent constructor so in derived class you must call:
	 * parent::__construct()
	 *
	 * @return EntityBase
	 */
	public function __construct() {
		$this->connector = ConnectorManager::GetDefaultConnector();
	}

	/**
	 * EntityBase::serialize()
	 * This magic method will be automatically called when serialization performed.
	 * We must override default PHP serialization because in these class we're using resource type variable (ConnectorBase)
	 * DRAWBACK: Private variable from derived class are NOT serializable ! (NOTE: Protected variable are accessible)
	 * Above drawback can be overcome with copying and pasting the serialize() and unserialize() to the derived class (Sorry for the inconvenience)
	 *
	 * @return string	=> Serialized Form of current Class Extending EntityBase
	 */
	public function serialize() {
		$this->connector = null; // Connection Can't be serialized because this is a resource type
		$dump = array();
		foreach ($this as $key => $value) {
			$dump[$key] = $value;
		}
		return serialize($dump);
	}

	/**
	 * EntityBase::unserialize()
	 * This magic method will be called when unserialization performed.
	 * We must re-assign the resource type variable (ConnectorBase one)
	 *
	 * @param  $data	=> Serialized string form
	 * @return void
	 */
	public function unserialize($data) {
		$dump = unserialize($data);
		foreach ($dump as $key => $value) {
			$this->{$key} = $value;
		}
		$this->connector = ConnectorManager::GetDefaultConnector();
	}

	/**
	 * EntityBase::BindData()
	 * DEPRECATED! Usage of auto binding is discourage because lack of data validation...
	 * 
	 * Auto Binding data to properties feature (Ignoring the read only property when assigning value)
	 * Used this feature if your class is mapped to some table of the database(s)
	 * Analogy : Class Property == a Column in Table
	 * So we can map the column name with the property name using Key 'Map' of the current Property
	 * The 'Map' key value is the respective column name in the table
	 *
	 * NOTE : This Bind doesn't affected by Property Type ! Even the type is 'r' we still can set the value
	 *		  This will automatically call custom Setter method if defined.
	 *
	 * @deprecated
	 * @param array $data	=> array consisting 'Column Name' as key and the Value as the Property value
	 * @return EntityBase
	 */
	public function BindData($data) {
		if (!is_array($data)) {
			return null;
		}
		foreach ($this as $propName => $propMetaData) {
			if (!is_array($propMetaData)) {
				// Probably this is not a Property considering Property pattern
				continue;
			}
			if (!isset($propMetaData["Type"])) {
				// Of course this not a Property.... (Property SHOULD HAVE this array key)
				continue;
			}
			if (!isset($propMetaData["Map"])) {
				// This Property doesn't support auto binding
				continue;
			}
			if (!isset($data[$propMetaData["Map"]])) {
				// We can't found the data in the given array
				continue;
			}
			
			$handler = "Set" . $propName;
			// Property Value Setter
			if (method_exists($this, $handler)) {
				// Using custom property setter
				$this->$handler($data[$propMetaData["Map"]]);
			} else {
				// Using auto property
				$propMetaData["Value"] = $data[$propMetaData["Map"]];
				// Required to reflect value changes (PHP use pass by value instead of pass by reference)
				$this->$propName = $propMetaData;
			}
		}
		return $this;
	}

	/**
	 * EntityBase::BindDataFromXml()
	 * Same as BindData Feature but instead of array you will give XML in string format
	 *
	 * @param mixed $stringXml = well formed xml in string format with any document root (element name used as column name).
	 * @return EntityBase
	 */
	public function BindDataFromXml($stringXml) {
		if (empty($stringXml)) {
			return null;
		}
		$temp = simplexml_load_string($stringXml);
		if (!$temp) {
			return null;
		}
		$data = array();
		foreach ($temp as $key => $value) {
			// Force the value as string instead of SimpleXmlElement Object.....
			$data[$key] = "" . $value;
		}
		return $this->BindData($data);
	}
}

// EOF: ./system/core/entity_base.php
