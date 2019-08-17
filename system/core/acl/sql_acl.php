<?php
final class SqlAcl extends AclBase {
	private static $SESSION_NAME;

	/**
	 * Private variable for CurrentUser Property
	 * @var User
	 */
	private $currentUser = null;
	/**
	 * @var ConnectorBase
	 */
	private $connector;
	/**
	 * @var int
	 */
	private $authenticationResult;

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
		$user = strtolower($user);
		if (empty($user)) {
			$this->authenticationResult = AclBase::AUTHENTICATE_INVALID_USERNAME;
			// No Username Supplied ?
			return false;
		}

		$columns = array("a.id", "a.password", "a.real_name");
		if (ADDITIONAL_USER_FIELDS != null) {
			if(is_string(ADDITIONAL_USER_FIELDS)) {
				$fields = explode(",", ADDITIONAL_USER_FIELDS);
				for ($i = 0; $i < count($fields); $i++) {
					$columns[] = "a." . trim($fields[$i]);		// Remove trailing space and make sure this column retrieved from table USER_TABLE
				}
			} else if (is_array(ADDITIONAL_USER_FIELDS)) {
				foreach (ADDITIONAL_USER_FIELDS as $field) {
					$columns[] = "a." . trim($field);			// Remove trailing space and make sure this column retrieved from table USER_TABLE
				}
			} else {
				// Un-supported type given
				trigger_error("ADDITIONAL_USER_FIELDS only support string or array !", E_USER_ERROR);
			}
		}

		// Retrieve data from table users
		$query =
'SELECT %s
FROM %s AS a
WHERE a.username = ?user';
		$this->connector->CommandText = sprintf($query, implode(", ", $columns), USER_TABLE);

		$this->connector->AddParameter("?user", $user);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			$this->authenticationResult = AclBase::AUTHENTICATE_INVALID_USERNAME;
			// Username Not Found !
			return false;
		}

		$row = $rs->FetchAssoc();

		$userId = $row["id"];
		$realPassword = $row["password"];
		$realName = $row["real_name"];
		// OK time for custom fields
		unset($row["id"]);
		unset($row["password"]);
		unset($row["real_name"]);

		$rs->CloseReader();

		if ($password !== $realPassword) {
			$this->authenticationResult = AclBase::AUTHENTICATE_INVALID_PASSWORD;
			// Booo Wrong password
			return false;
		}

		// Setting logged user
		$this->currentUser = new User($userId, $user, $realName, $row);

		// OK Everything is OK then retrieve the user access data...
		$query =
'SELECT a.path, a.is_allowed
FROM %s AS a
WHERE a.user_id = ?userId';
		$this->connector->CommandText = sprintf($query, USER_ACL_TABLE);

		$this->connector->AddParameter("?userId", $userId);
		$rs = $this->connector->ExecuteQuery();

		while ($row = $rs->FetchAssoc()) {
			$tokens = explode(",", $row["path"]);
			foreach ($tokens as $token) {
				if ($row["is_allowed"] == 1) {
					$this->currentUser->Allow(trim($token));
				} else {
					$this->currentUser->Deny(trim($token));
				}
			}
		}

		$rs->CloseReader();

		if (USE_GROUP_ACCESS_LEVEL) {
			$columns = array("a.group_id", "b.name", "b.description", "c.path", "c.is_allowed");

			if (ADDITIONAL_GROUP_FIELDS != null) {
				if(is_string(ADDITIONAL_GROUP_FIELDS)) {
					$fields = explode(",", ADDITIONAL_GROUP_FIELDS);
					for ($i = 0; $i < count($fields); $i++) {
						$columns[] = "a." . trim($fields[$i]);		// Remove trailing space and make sure this column retrieved from table GROUP_TABLE
					}
				} else if (is_array(ADDITIONAL_GROUP_FIELDS)) {
					foreach (ADDITIONAL_GROUP_FIELDS as $field) {
						$columns[] = "a." . trim($field);			// Remove trailing space and make sure this column retrieved from table GROUP_TABLE
					}
				} else {
					// Un-supported type given
					trigger_error("ADDITIONAL_GROUP_FIELDS only support string or array !", E_USER_ERROR);
				}
			}

			// OK group ACL are enabled... now we retrieving group data and their respective ACL in one swoop
			// general query : SELECT * FROM user_group JOIN group JOIN group_acl WHERE user_id = ...
			$query =
'SELECT %s
FROM %s AS a
	JOIN %s AS b ON a.group_id = b.id
	JOIN %s AS c ON b.id = c.group_id
WHERE a.user_id = ?userId
ORDER BY b.id, b.name';
			$this->connector->CommandText = sprintf($query, implode(", ", $columns), USER_GROUP_TABLE, GROUP_TABLE, GROUP_ACL_TABLE);

			$this->connector->AddParameter("?userId", $userId);
			$rs = $this->connector->ExecuteQuery();
			$prevGroupId = -1;
			$group = null;
			while ($row = $rs->FetchAssoc()) {
				if ($prevGroupId != $row["group_id"]) {
					// New Group Identified
					$prevGroupId = $row["group_id"];
					$group = new Group($row["name"], $row["description"]);
					$this->currentUser->AddGroup($group);
				}

				$tokens = explode(",", $row["path"]);
				foreach ($tokens as $token) {
					if ($row["is_allowed"] == 1) {
						$group->Allow(trim($token));
					} else {
						$group->Deny(trim($token));
					}
				}
			}
		}

		$this->authenticationResult = AclBase::AUTHENTICATE_SUCCESS;
		return true;
	}
	
	public function DeserializeUser() {
		$this->currentUser = isset($_SESSION[self::$SESSION_NAME]) ? $_SESSION[self::$SESSION_NAME] : null;
	}
	
	public function SerializeUser() {
		$_SESSION[self::$SESSION_NAME] = $this->currentUser;	// Serializing User object to perform caching.
	}
	
	public function Signout() {
		// Destroying current session login
		unset($_SESSION[self::$SESSION_NAME]);
		$this->currentUser = null;
		// Now call to this method is compulsory since the automatic user serialization in Dispatcher disabled
		$this->SerializeUser();
	}
	
	protected function InitializeAcl() {
		// We put some config on external file so we can seperate dynamic data from class file
		if (!file_exists(USER_CONFIG . "sql_acl.config.php")) {
			throw new Exception("Required config file for SqlAcl class not found ! Make sure you have 'sql_acl.config.php'");
		}
		require_once(USER_CONFIG . "sql_acl.config.php");
		
		// We already have Exception Handler defined so we removed these error handler and use the global one
		$sid = session_id();
		if (empty($sid)) {
			session_start();
		}
	
		self::$SESSION_NAME = "credential-" . APP_NAME;
		
		$this->connector = ConnectorManager::GetDefaultConnector();
	}
}

// EOF: ./system/core/acl/sql_acl.php
