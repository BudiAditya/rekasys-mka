<?php
abstract class AclBase extends ObjectExtended {
	const AUTHENTICATE_INITIALIZATION = 0;
	const AUTHENTICATE_SUCCESS = 1;
	const AUTHENTICATE_INVALID_USERNAME = -1;
	const AUTHENTICATE_INVALID_PASSWORD = -2;

	// Used to stored current user accessing the Web Application
	protected $CurrentUser = array("Type" => "rw");

	// Used to easily determine whether current session was Authenticated or not
	protected $IsUserAuthenticated = array("Type" => "r");
	// Stored detailed authentication result from Authenticate() call
	protected $AuthenticationResult = array("Type" => "r");
	// List of controller(s) which bypassed by ACL
	protected $bypassedControllers;
	// List of controller(s) which bypassed by ACL after user is authenticated
	protected $bypassedAfterAuthenticated;

	// START - Compulsory accessor method(s)
	/**
	 * @abstract
	 * @return User
	 */
	abstract function GetCurrentUser();
	abstract function SetCurrentUser(User $user);
	/**
	 * @abstract
	 * @return bool
	 */
	abstract function GetIsUserAuthenticated();
	/**
	 * @abstract
	 * @return int (see AclBase::AUTHENTICATE_xx)
	 */
	abstract function GetAuthenticationResult();
	// END - Compulsory accessor method(s)

	/**
	 * Constructor of abstract AclBase class.
	 * This constructor marked as final because we must call DeserializeUser() function.
	 * Because of that there is a replacement function for the constructor called InitializeAcl()
	 *
	 * @param array $bypassedControllers
	 * @param array $bypassedAfterAuthenticated
	 */
	final function __construct(array $bypassedControllers, array $bypassedAfterAuthenticated) {
		// Should be set before we call InitializeAcl()
		$this->bypassedControllers = $bypassedControllers;
		$this->bypassedAfterAuthenticated = $bypassedAfterAuthenticated;

		$this->InitializeAcl();
		$this->DeserializeUser();
	}

	/**
	 * AclBase::Authenticate()
	 * Used to check the given username and password with list of users in the persistence storage.
	 * NOTE:
	 * 	- Password encryption is determined by YOUR Acl implementation
	 * 	- Detailed authentication result can be checked at AuthenticateResult property (invalid username / invalid password / user locked / etc)
	 *
	 * @param string $username	=> username to be checked against current Acl
	 * @param string $password	=> password that should be matched with associated username
	 * @return bool				=> true when authentication is valid
	 */
	abstract function Authenticate($username, $password);

	/**
	 * AclBase::CheckUserAccess()
	 * Check whether user is allowed to access the given resources or not
	 * This function is overridable in case you need custom ACL checking...
	 *
	 * @overridable
	 * @param string $controller
	 * @param string $method
	 * @param string $namespace
	 * @return bool true when user is allowed to access the given controller etc
	 */
	public function CheckUserAccess($controller, $method, $namespace = null) {
		// Preparation
		$idx = strrpos($controller, ".");
		if ($idx !== false) {
			$namespace = $namespace . substr($controller, 0, $idx);
			$controller = substr($controller, $idx + 1);
		}

		$temp = "";
		$key = null;
		if (!empty($namespace)) {
			$temp = $namespace . ".";
		}
		$temp .= $controller;

		// #1-A: All bypassed controller should be used as initial value
		$effectiveAcl = $this->bypassedControllers;
		// #1-B: If used already authenticated then we also add another bypassed controller(s) and his ACL
		if ($this->GetIsUserAuthenticated()) {
			$effectiveAcl = array_merge($effectiveAcl, $this->bypassedAfterAuthenticated, $this->GetCurrentUser()->GetEffectiveAcl());
		}

		// #2: Checking for specific ACL (namespace.controller/method)
		$key = $temp . "/" . $method;
		if (array_key_exists($key, $effectiveAcl)) {
			return $effectiveAcl[$key];
		}

		// #3-A: Checking for full controller access with '*' sign (namespace.controller/*)
		$key = $temp . "/*";
		if (array_key_exists($key, $effectiveAcl)) {
			return $effectiveAcl[$key];
		}
		// #3-B: Checking for full controller access WITHOUT '*' sign for compatibility (namespace.controller)
		$key = $temp;
		if (array_key_exists($key, $effectiveAcl)) {
			return $effectiveAcl[$key];
		}

		// #4: Checking for namespace access
		//	   Sorry but I don't offer namespace checking without '*' sign because can cause unwanted problem if there is same name for controller and namespace
		if (empty($namespace)) {
			$temp = array();	// hack to prevent namespace checking
		} else {
			$temp = explode(".", $namespace);
		}
		while (count($temp) > 0) {
			$key = implode(".", $temp);
			$key .= ".*";
			if (array_key_exists($key, $effectiveAcl)) {
				return $effectiveAcl[$key];
			}

			// Not found then remove last namespace
			unset($temp[count($temp) - 1]);
		}

		// #5: Checking global access
		$key = "*";
		if (array_key_exists($key, $effectiveAcl)) {
			return $effectiveAcl[$key];
		}

		// #6: No matching ACL checking...
		return false;
	}

	/**
	 * AclBase::DeserializeUser()
	 * Recreate user from persistence storage. Always called by the constructor.
	 *
	 * @return void
	 */
	abstract function DeserializeUser();

	/**
	 * AclBase::SerializeUser()
	 * Saving current user to persistence storage. Call this function after authentication or you want to extends the session time
	 * NOTE: Extending session time depends on YOUR Acl implementation
	 *
	 * @return void
	 */
	abstract function SerializeUser();

	/**
	 * AclBase::Signout()
	 * Remove / LogOut current logged user from system. depend on your implementation Calling SerializeUser() after Signout is a MUST !!!
	 *
	 * @return void
	 */
	abstract function Signout();

	/**
	 * AclBase::InitializeAcl()
	 * constructor replacement
	 *
	 * @return void
	 */
	abstract protected function InitializeAcl();
}

// EOF: ./system/core/acl/acl_base.php
