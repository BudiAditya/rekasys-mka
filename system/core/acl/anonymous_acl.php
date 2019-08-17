<?php
class AnonymousAcl extends AclBase {
	/**
	 * @var User
	 */
	private $anonymous;

	// START - Accessor method(s)
	public function GetCurrentUser() {
		return $this->anonymous;
	}
	
	public function SetCurrentUser(User $user) { }
	
	public function Authenticate($username, $password) {
		// Allow all access for any username and password
		return true;
	}
	
	public function GetIsUserAuthenticated() {
		return true;
	}

	public function GetAuthenticationResult() {
		return AclBase::AUTHENTICATE_SUCCESS;
	}
	// END - Accessor method(s)
	
	/**
	 * Here we are checking whether user allowed to access resources or not !
	 * The given path always in format controller/method
	 * Return true if authorized
	 *
	 * @param $controller
	 * @param $method
	 * @param $namespace
	 * @return bool
	 */
	public function CheckUserAccess($controller, $method, $namespace) {
		// This site allowed any access
		return true;
	}
	
	public function DeserializeUser() { }
	
	public function SerializeUser() { }
	
	public function Signout() { }

	protected function InitializeAcl() {
		$this->anonymous = new User(null, "Anonymous", "Anonymous");
	}
}

// EOF: ./system/core/acl/anonymous_acl.php
