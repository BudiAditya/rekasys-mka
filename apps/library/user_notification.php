<?php

class NotificationGroup {
	public $Name;
	/** @var Notification[] */
	public $UserNotifications;

	public function __construct($name) {
		$this->Name = $name;
	}
}

class Notification {
	public  $Text;
	public  $Url;
	/** @var NotificationGroup */
	private $group;

	private function  __construct(NotificationGroup $group) {
		$this->group = $group;
	}

	/**
	 * @return NotificationGroup
	 */
	public function GetGroup() {
		return $this->group;
	}

	/**
	 * @return NotificationGroup[]
	 */
	public static function GetCurrentUserNotifications() {
		$notifications = array();

		$acl = AclManager::GetInstance();
		$sbu = PersistenceManager::GetInstance()->LoadState("entity_id");
		$isCorporate = ($sbu == 7 || $sbu == null);
		$connector = ConnectorManager::GetDefaultConnector();

		// Group : NPKP
		$group = new NotificationGroup("NPKP");
		// NPKP masih Draft
		if ($acl->CheckUserAccess("accounting.cashrequest", "post")) {
			if ($isCorporate) {
				$query = "SELECT COUNT(a.id) FROM ac_cash_request_master AS a WHERE a.is_deleted = 0 AND a.status < 2";
			} else {
				$query = "SELECT COUNT(a.id) FROM ac_cash_request_master AS a WHERE a.is_deleted = 0 AND a.status < 2 AND a.entity_id = $sbu";
			}

			$connector->CommandText = $query;
			$rs = $connector->ExecuteScalar();
			if ($rs > 0) {
				$notification = new Notification($group);
				$notification->Text = sprintf("Ada %d data NPKP yang belum di proses.", $rs);
				$notification->Url = "notification.npkp/approval_pending";

				$group->UserNotifications[] = $notification;
			}
		}
		// OK jika ada notifnya baru kita add...
		if (count($group->UserNotifications) > 0) {
			$notifications[] = $group;
		}

		return $notifications;
	}
}

// EoF: user_notification.php