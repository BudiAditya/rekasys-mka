<?php
class UserAdmin extends EntityBase {
	public $UserUid;
	public $IsAktif;
	public $UserId;
	public $EntityId;
	public $EntityCd;
	public $ProjectId = 1;
	public $ProjectCd;
	public $UserName;
	public $UserEmail;
	public $Status = 7;		// By Default Logged Out
	public $LoginTime;
	public $LoginFrom;
	public $UserLvl;
	public $ShortDesc;
	public $AllowMultipleLogin;
	public $UserPwd1;
	public $UserPwd2;
	public $SessionId;
	public $IsForceAccountingPeriod = false;
	public $AProjectId = null;
	public $EmployeeId = 0;
	public $PwdchangeCnt = 0;

	// Helper Variable
	public function FillProperties(array $row) {
		$this->UserUid = $row["user_uid"];
		$this->IsAktif = $row["is_aktif"];
		$this->UserId = $row["user_id"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->ProjectId = $row["project_id"];
		$this->ProjectCd = null;
		$this->UserName = $row["user_name"];
		$this->UserEmail = $row["user_email"];
		$this->Status = $row["status"];
		$this->LoginTime = $row["login_time"];
		$this->LoginFrom = $row["login_from"];
		$this->UserLvl = $row["user_lvl"];
		$this->ShortDesc = $row["short_desc"];
		$this->AllowMultipleLogin = $row["allow_multiple_login"];
		$this->UserPwd1 = $row["user_pwd"];
		$this->UserPwd2 = $row["user_pwd"];
        $this->EmployeeId = $row["employee_id"];
        $this->AProjectId = $row["a_project_id"];
        $this->PwdchangeCnt = $row["pwdchange_cnt"];

		$this->IsForceAccountingPeriod = $row["force_accounting_period"] == 1;
	}

	public function LoadAll($orderBy = "a.user_id", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
				"SELECT a.*, b.entity_cd, c.short_desc
FROM sys_users AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'login_audit'
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
				"SELECT a.*, b.entity_cd, c.short_desc
FROM sys_users AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'login_audit'
WHERE a.is_aktif = 1
ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UserAdmin();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function FindById($id) {
		$this->connector->CommandText =
			"SELECT a.*, b.entity_cd, c.short_desc
FROM sys_users AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN sys_status_code AS c ON a.status = c.code AND c.key = 'login_audit'
WHERE a.user_uid = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO sys_users(employee_id,a_project_id,is_aktif, user_id, entity_id, project_id, user_pwd, user_lvl, user_name, user_email, allow_multiple_login, force_accounting_period)
VALUES(?employee_id,?a_project_id,?is_aktif, ?user_id, ?entity_id, ?project_id, ?user_pwd, ?user_lvl, ?user_name, ?user_email, ?allow_multiple_login, ?force_accounting_period)";
		$this->connector->AddParameter("?is_aktif", $this->IsAktif);
		$this->connector->AddParameter("?user_id", strtolower($this->UserId)); // Semua user ID disimpan dalam lowercase (PostgreSQL is case-sensitive search)
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?user_pwd", md5($this->UserPwd1));
		$this->connector->AddParameter("?user_lvl", $this->UserLvl);
		$this->connector->AddParameter("?user_name", $this->UserName);
		$this->connector->AddParameter("?user_email", $this->UserEmail);
		$this->connector->AddParameter("?allow_multiple_login", $this->AllowMultipleLogin);
		$this->connector->AddParameter("?force_accounting_period", $this->IsForceAccountingPeriod);
        $this->connector->AddParameter("?a_project_id", $this->AProjectId);
        $this->connector->AddParameter("?employee_id", $this->EmployeeId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		if (strlen($this->UserPwd1) > 0) {
			$this->connector->CommandText =
'UPDATE sys_users SET
	is_aktif = ?is_aktif
	, user_id = ?user_id
	, entity_id = ?entity_id
	, project_id = ?project_id
	, user_pwd = ?user_pwd
	, user_lvl = ?user_lvl
	, user_name = ?user_name
	, user_email = ?user_email
	, allow_multiple_login = ?allow_multiple_login
	, force_accounting_period = ?force_accounting_period
	, employee_id = ?employee_id
	, a_project_id = ?a_project_id 
WHERE user_uid = ?id';
			$this->connector->AddParameter("?user_pwd", md5($this->UserPwd1));
		} else {
			$this->connector->CommandText =
'UPDATE sys_users SET
	is_aktif = ?is_aktif
	, user_id = ?user_id
	, entity_id = ?entity_id
	, project_id = ?project_id
	, user_lvl = ?user_lvl
	, user_name = ?user_name
	, user_email = ?user_email
	, allow_multiple_login = ?allow_multiple_login
	, force_accounting_period = ?force_accounting_period
	, employee_id = ?employee_id
	, a_project_id = ?a_project_id 
WHERE user_uid = ?id';
		}
		$this->connector->AddParameter("?is_aktif", $this->IsAktif);
		$this->connector->AddParameter("?user_id", strtolower($this->UserId)); // Semua user ID disimpan dalam lowercase (PostgreSQL is case-sensitive search)
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?project_id", $this->ProjectId);
		$this->connector->AddParameter("?user_lvl", $this->UserLvl);
		$this->connector->AddParameter("?user_name", $this->UserName);
		$this->connector->AddParameter("?user_email", $this->UserEmail);
		$this->connector->AddParameter("?allow_multiple_login", $this->AllowMultipleLogin);
		$this->connector->AddParameter("?force_accounting_period", $this->IsForceAccountingPeriod);
        $this->connector->AddParameter("?a_project_id", $this->AProjectId);
        $this->connector->AddParameter("?employee_id", $this->EmployeeId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM sys_users WHERE user_uid = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function LoginRecord($uid) {
		$this->connector->CommandText =
'UPDATE sys_users SET
	status = ?status
	, login_time = ?login_time
	, login_from = ?login_from
	, session_id = ?session_id
WHERE user_uid = ?uid';
		$this->connector->AddParameter("?status", $this->Status);
		$this->connector->AddParameter("?login_time", $this->LoginTime);
		$this->connector->AddParameter("?login_from", $this->LoginFrom);
		$this->connector->AddParameter("?session_id", $this->SessionId);
		$this->connector->AddParameter("?uid", $uid);
		return $this->connector->ExecuteNonQuery();
	}

    public function LoginActivityWriter($lProjectId,$lUserId,$lStatus){;
        $sqx = "Insert Into sys_login_logs (project_id,user_id,log_time,from_ipad,browser_app,ref_info,login_status)";
        $sqx.= " Values (?project_id,?user_id,now(),?ipad,?browser,?ref,?lstatus)";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?project_id", $lProjectId);
        $this->connector->AddParameter("?user_id", $lUserId);
        $this->connector->AddParameter("?ipad", getenv('REMOTE_ADDR'));
        $this->connector->AddParameter("?browser", getenv('HTTP_USER_AGENT'));
        $this->connector->AddParameter("?ref", getenv('HTTP_REFERER'));
        $this->connector->AddParameter("?lstatus", $lStatus);
        return $this->connector->ExecuteNonQuery();
    }

    public function UserActivityWriter($project_id,$resource,$process,$doc_no,$status){
        $sqx = "Insert Into sys_user_activity (project_id,user_uid,log_time,resource,process,doc_no,status)";
        $sqx.= " Values (?project_id,?user_uid,now(),?res,?process,?doc_no,?status)";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?project_id", $project_id);
        $this->connector->AddParameter("?user_uid", AclManager::GetInstance()->GetCurrentUser()->Id);
        $this->connector->AddParameter("?res", $resource);
        $this->connector->AddParameter("?process", $process);
        $this->connector->AddParameter("?doc_no", $doc_no);
        $this->connector->AddParameter("?status", $status);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetSysUserActivity($userUid,$stDate,$enDate){
        $sql = "Select a.* From vw_sys_user_activity a Where a.user_uid = ?userUid and a.log_time BETWEEN ?stDate and ?enDate Order By a.log_time;";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?userUid", $userUid);
        $this->connector->AddParameter("?stDate", date('Y-m-d',$stDate).' 00:00:00');
        $this->connector->AddParameter("?enDate", date('Y-m-d',$enDate).' 23:59:59');
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetUserLevel($getLevel = 5,$operator = '<'){
        $sql = "SELECT a.* FROM `sys_status_code` a WHERE a.`key` = 'user_level' AND a.`code` $operator $getLevel Order By a.code;";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}
