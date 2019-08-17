<?php
/**
 * This class used to store your database connection preferences
 * This class also responsible for some database debugging output.
 * If you're ready please change RaiseConnectionError and RaiseQueryError value
 * NOTE: You can change UseSqlException value if you want to log the error but not show the error to user
 */
class ConnectorSettings extends ObjectExtended {
	public $DriverType;
	public $Host;
	public $Port;
	public $Username;
	public $Password;
	public $DatabaseName;
	// Error related settings
	public $SuppressPhpError = true;
	public $RaiseConnectionError = true;
	public $RaiseQueryError = true;
	public $UseSqlException = false;
	public $DuplicateRaiseError = false;
}

// EOF: ./system/core/connector/connector_settings.php
