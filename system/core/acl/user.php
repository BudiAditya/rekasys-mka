<?php
class User extends ObjectExtended {
	private $effectiveAcl = null;

	protected $AllowList = array("Type" => "r", "Value" => array());
	protected $DenyList = array("Type" => "r", "Value" => array());
	protected $EffectiveAcl = array("Type" => "r");
	protected $Id = array("Type" => "r", "Value" => null);
	protected $Username = array("Type" => "r", "Value" => null);
	protected $RealName = array("Type" => "r", "Value" => null);
	protected $Groups = array("Type" => "r", "Value" => array());

	protected $customFields = array();

	/**
	 * Calculate effective Access Control List based on groups and his user
	 *
	 * @return null|array
	 */
	public function GetEffectiveAcl() {
		if ($this->effectiveAcl != null) {
			return $this->effectiveAcl;
		}
		// Should find a way to caching this EffectiveAcl instead of re-calculate every request !
		$this->effectiveAcl = array();
		
		/**
		 * Rules of Effective Acl
		 * 1. Deny rule precede over the allow rule (same rule applied then Deny will be used)
		 * 2. User rule precede over the group rule (deny rule from group overidden by user rule)
		 */
	 	// Process group ACL
		foreach ($this->Groups["Value"] as $group) {
			// Process allow first
			foreach ($group->AllowList as $path) {
				$path = strtolower($path);
				// Be careful processing this one we must consider ACL from other group
				if (array_key_exists($path, $this->effectiveAcl)) {
					// Only allowed true if previous value also true
					$this->effectiveAcl[$path] = $this->effectiveAcl[$path] && true;
				} else {
					$this->effectiveAcl[$path] = true;
				}
			}
			
			// Process deny list
			foreach ($group->DenyList as $path) {
				$path = strtolower($path);
				// Deny access
				$this->effectiveAcl[$path] = false;
			}
		}
		// Process user ACL (Override the value from group ACL)
		foreach ($this->AllowList["Value"] as $path) {
			$this->effectiveAcl[$path] = true;
		}
		
		foreach ($this->DenyList["Value"] as $path) {
			$this->effectiveAcl[$path] = false;
		}
		
		return $this->effectiveAcl;
	}
	
	public function __construct($id, $username, $realname, array $customFields = array()) {
		$this->Id["Value"] = $id;
		$this->Username["Value"] = $username;
		$this->RealName["Value"] = $realname;
		$this->customFields = $customFields;
	}

	public function GetAllCustomFields() {
		return $this->customFields;
	}

	public function GetCustomField($name) {
		return array_key_exists($name, $this->customFields) ? $this->customFields[$name] : null;
	}

	public function ReGenerateEffectiveAcl() {

	}
	
	public function AddGroup(Group $group) {
		$key = strtolower($group->Name);
		if (!array_key_exists($key, $this->Groups["Value"]))
			$this->Groups["Value"][$key] = $group;
		return $group;
	}
	
	public function RemoveGroup(Group $group) {
		$key = strtolower($group->Name);
		if (array_key_exists($key, $this->Groups["Value"]))
			unset($this->Groups["Value"][$key]);
	}
	
	public function IsInRole($groupName) {
		$groupName = strtolower($groupName);
		return array_key_exists($groupName, $this->Groups["Value"]);
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

// EOF: ./system/core/acl/user.php
