<?php
class Group extends ObjectExtended {
	protected $AllowList = array("Type" => "r", "Value" => array());
	protected $DenyList = array("Type" => "r", "Value" => array());
	protected $Description = array("Type" => "r");
	protected $Name = array("Type" => "r");

	protected $customFields = array();
	
	public function __construct($name, $desc) {
		$this->Name["Value"] = $name;
		$this->Description["Value"] = $desc;
	}

	public function GetAllCustomFields() {
		return $this->customFields;
	}

	public function GetCustomField($name) {
		return array_key_exists($name, $this->customFields) ? $this->customFields[$name] : null;
	}
	
	public function Allow($path) {
		// Check whether user already allowed to access or not
		if (in_array($path, $this->AllowList["Value"]))
			return $path;
		$this->AllowList["Value"][] = $path;
		return $path;
	}
	
	public function RevokeAllow($path) {
		if (in_array($path, $this->AllowList["Value"])) {
			$pos = array_keys($this->AllowList["Value"], $path);
			unset($this->AllowList["Value"][$pos[0]]);
		}
	}
	
	public function Deny($path) {
		// Check whether user already denied to access or not
		if (in_array($path, $this->DenyList["Value"]))
			return $path;
		$this->DenyList["Value"][] = $path;
		return $path;
	}
	
	public function RevokeDeny($path) {
		if (in_array($path, $this->DenyList["Value"])) {
			$pos = array_keys($this->DenyList["Value"], $path);
			unset($this->DenyList["Value"][$pos[0]]);
		}
	}
}

// EOF: ./system/core/acl/group.php
