<?php
final class SimpleAcl extends AclBase {
	private static $SESSION_NAME;
	
	// Private variable for the Property
	/**
	 * @var User
	 */
	private $currentUser = null;
	private $authenticationResult;
	
	private $groups;
	private $users;
	private $mapping = array();

	// START - Accessor method(s)
	public function GetCurrentUser() {
		return $this->currentUser;
	}
	
	public function SetCurrentUser(User $user) {
		$this->currentUser = $user;
	}
	
	public function GetIsUserAuthenticated() {
		// Now in Dispatcher we have feature to bypass ACL for Home and Error Controller
		// That two controller will be sufficient for anonymous user. So we changed from concrete user to null checking
		return $this->currentUser != null;
	}

	public function GetAuthenticationResult() {
		return $this->authenticationResult;
	}
	// END - Accessor method(s)
	
	public function Authenticate($user, $password) {
		$this->authenticationResult = AclBase::AUTHENTICATE_INITIALIZATION;
		$this->LoadDatabaseAcl();
		
		$user = strtolower($user);
		if (empty($user) || !array_key_exists($user, $this->mapping)) {
			$this->authenticationResult = AclBase::AUTHENTICATE_INVALID_USERNAME;
			// User Empty or Not Found
			return false;
		}

		if ($this->mapping[$user] !== $password) {
			$this->authenticationResult = AclBase::AUTHENTICATE_INVALID_PASSWORD;
			// Wrong Password
			return false;
		}

		$this->authenticationResult = AclBase::AUTHENTICATE_SUCCESS;
		$this->currentUser = $this->users[$user];
		return true;
	}
	
	public function DeserializeUser() {
		$this->currentUser = isset($_SESSION[self::$SESSION_NAME]) ? $_SESSION[self::$SESSION_NAME] : null;
	}
	
	public function SerializeUser() {
		$_SESSION[self::$SESSION_NAME] = $this->currentUser;
	}
	
	public function Signout() {
		unset($_SESSION[self::$SESSION_NAME]);
		$this->currentUser = null;
		// Now call to this method is compulsory since the automatic user serialization in Dispatcher disabled
		$this->SerializeUser();
	}
	
	protected function InitializeAcl() {
		// We already have Exception Handler defined so we removed these error handler and use the global one
		$sid = session_id();
		if (empty($sid)) {
			session_start();
		}
		
		self::$SESSION_NAME = "credential-" . APP_NAME;
	}
	
	private function LoadDatabaseAcl() {		
		$temp = parse_ini_file(USER_CONFIG . "simple_group.ini", true);
		foreach ($temp as $key => $value) {
			$key = strtolower($key);
			$group = new Group($key, $value["Description"]);
			$allows = explode(",", $value["Allow"]);
			foreach ($allows as $path) {
				if (strlen(trim($path)) == 0) continue;
				$group->Allow(trim($path));
			}
			$denies = explode(",", $value["Deny"]);
			foreach ($denies as $path) {
				if (strlen(trim($path)) == 0) continue;
				$group->Deny(trim($path));
			}
			$this->groups[$key] = $group;
		}
		
		$temp = parse_ini_file(USER_CONFIG . "simple_user.ini", true);
		foreach ($temp as $key => $value) {
			$key = strtolower($key);
			$user = new User($value["Id"], $key, $value["Realname"]);
			$allows = explode(",", $value["Allow"]);
			foreach ($allows as $path) {
				if (strlen(trim($path)) == 0) continue;
				$user->Allow(trim($path));
			}
			$denies = explode(",", $value["Deny"]);
			foreach ($denies as $path) {
				if (strlen(trim($path)) == 0) continue;
				$user->Deny(trim($path));
			}
			$groups = explode(",", $value["Group"]);
			foreach ($groups as $group) {
				$group = strtolower($group);
				if (strlen(trim($group)) == 0) continue;
				if (!array_key_exists($group, $this->groups)) continue;
				$user->AddGroup($this->groups[$group]);
			}
			$this->users[$key] = $user;
			$this->mapping[$key] = $value["Password"];
		}
	}
}

// EOF: ./system/core/acl/simple_acl.php
