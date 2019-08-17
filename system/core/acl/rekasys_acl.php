<?php

class RekaSysAcl extends AclBase {
    private static $SESSION_NAME;
    private $mapping = array(
        1 => "add",
        2 => "edit",
        3 => "delete",
        4 => "view",
        5 => "print",
        6 => "approve",
        7 => "verify",
        8 => "post",
        9 => "*",
    );

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

    protected function InitializeAcl() {
        $sid = session_id();
		if (empty($sid)) {
			session_start();
		}

		self::$SESSION_NAME = "credential-" . APP_NAME;

		$this->connector = ConnectorManager::GetDefaultConnector();
    }

    function Authenticate($username, $password, $project_id = 0) {
        $user = strtolower($username);
		if (empty($user)) {
			// No Username Supplied ?
            $this->authenticationResult = AclBase::AUTHENTICATE_INVALID_USERNAME;
			return false;
		}

        $query = 'SELECT user_uid, user_pwd, user_name, user_lvl FROM sys_users AS a WHERE a.user_id = ?user';
		$this->connector->CommandText = $query;

        $this->connector->AddParameter("?user", $user);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			// Username Not Found !
            $this->authenticationResult = AclBase::AUTHENTICATE_INVALID_USERNAME;
			return false;
		}

		$row = $rs->FetchAssoc();

		$userId = $row["user_uid"];
		$realPassword = $row["user_pwd"];
		$realName = $row["user_name"];
        $userLvl = $row["user_lvl"];

		$rs->CloseReader();

		if (strtolower($password) != strtolower($realPassword)) {
			// Booo Wrong user_pwd
            $this->authenticationResult = AclBase::AUTHENTICATE_INVALID_PASSWORD;
			return false;
		}

		// Setting logged user
		$this->currentUser = new User($userId, $user, $realName, $row);
        if ($userLvl >= 5) {
            $this->currentUser->Allow("*");
            $this->authenticationResult = AclBase::AUTHENTICATE_SUCCESS;
            return true;
        }

		// OK Everything is OK then retrieve the user access data...
		$query = 'SELECT b.resource_path, a.rights FROM sys_user_rights AS a JOIN sys_resource AS b ON a.resource_id = b.id WHERE a.user_uid = ?userId';
        if ($project_id > 0){
            $query.= ' And project_id = '.$project_id;
        }
		$this->connector->CommandText = $query;

        $this->connector->AddParameter("?userId", $userId);
		$rs = $this->connector->ExecuteQuery();

        while ($row = $rs->FetchAssoc()) {
            $rights = $row["rights"];
			// Jika punya akses terhadap 'namespace.controller' maka secara otomatis akses ke index dan lists granted !
            $this->currentUser->Allow($row["resource_path"] . "/index");
            $this->currentUser->Allow($row["resource_path"] . "/lists");

			// Baca detail ACL nya
            for ($i = 0; $i < strlen($rights); $i++) {
                $code = $rights[$i];
                $this->currentUser->Allow($row["resource_path"] . "/" . $this->mapping[$code]);
				if ($code == 1) {
					// Jika memiliki ACL add maka user tersebut juga boleh generate data
					$this->currentUser->Allow($row["resource_path"] . "/generate");
				}
            }
		}

        $this->authenticationResult = AclBase::AUTHENTICATE_SUCCESS;
        return true;
    }

	/**
	 * Harus kita override karena akan ada sedikit validasi tambahan sebelum checking acl
	 *  - Berhubung nama method-nya ada yang common (add_master, add_detail, add_xxx) itu akan digabung menjadi add
	 *  - Untuk report semuanya akan digabung pada ACL 'view'
	 *  - Khusus untuk generate memerlukan ACL 'add' yang di handle pada saat pembuatan ACL oleh Authenticate(). Rencana awal akan dimaskin disini tetapi tidak jadi agar tidak ada beban terus menerus tiap request
	 *
	 * @param string $controller
	 * @param string $method
	 * @param null $namespace
	 * @return bool
	 */
	public function CheckUserAccess($controller, $method, $namespace = null) {
		$commonWords = array("index", "lists", "add", "view", "edit", "delete", "approve", "verify", "post", "generate", "print");

		$method = strtolower($method);
		$found = false;
		foreach ($commonWords as $word) {
			if (strpos($method, $word) !== false) {
				// Ketemu common word
				$method = $word;
				$found = true;
				break;
			}
		}

		if (!$found) {
			// Hmm... mencoba akses method yang tidak terdaftar pada commonWords kita asumsikan ini report (report akan share di ACL view)
			// Jika mengakses file yang tidak ada maka tidak akan masuk disini
			$method = "view";
		}

		// Sisanya serahkan pada parent... kita hanya merubah nama method
		return parent::CheckUserAccess($controller, $method, $namespace);
	}

    function DeserializeUser() {
        $this->currentUser = isset($_SESSION[self::$SESSION_NAME]) ? $_SESSION[self::$SESSION_NAME] : null;
    }

    function GetAuthenticationResult() {
        return $this->authenticationResult;
    }

    function GetCurrentUser() {
        return $this->currentUser;
    }

    function GetIsUserAuthenticated() {
        return $this->currentUser != null;
    }

    function SerializeUser() {
        $_SESSION[self::$SESSION_NAME] = $this->currentUser;	// Serializing User object to perform caching.
    }

    function SetCurrentUser(User $user) {
        $this->currentUser = $user;
    }

    function Signout() {
        // Destroying current session login
		unset($_SESSION[self::$SESSION_NAME]);
        PersistenceManager::GetInstance()->DestroyPersistence();
		$this->currentUser = null;
		// Now call to this method is compulsory since the automatic user serialization in Dispatcher disabled
		$this->SerializeUser();
    }
}
