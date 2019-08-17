<?php
/**
 * Some of the configurable settings for SqlAcl class.
 * Note #1: To use this class please create the required table. SQL script can be found at folder system/sql-script
 * The scripts consist of:
 *  - users.sql				=> List of user(s) for current web application
 *  - user_access.sql		=> Access Control List (ACL) for every user registered in users table
 *  - groups.sql*			=> List og group(s) available for current web application
 *  - group_access.sql*		=> ACL for group
 *  - user_group.sql*		=> Relation between user and group (one to many)
 *
 * Note #2: Group ACL can be disabled (not used) per user requirement. And if disabled the last three sql files is not required !
 */
define("USE_GROUP_ACCESS_LEVEL", false); // Tell the engine whether use additional (group) level access or not

/**
 * Now we support for custom table name. Table structure at least must be follow default one
 * Column addition is allowed but existing column CAN'T CHANGED !
 * If default column is changed you must Create new Acl class which extends AclBase class
 *
 * WARNING: Be careful with your table name(s)
 */
define("USER_TABLE", "sys_users"); 					// Table name which stored username and their password.
define("USER_ACL_TABLE", "sys_user_acl"); 		// Table name which stored user Access Control List
define("ADDITIONAL_USER_FIELDS", null);			// Additional fields for user data (this field(s) will be stored at User object)

if (USE_GROUP_ACCESS_LEVEL) {
	define("GROUP_TABLE", "sys_groups"); 			// Table name which stored group name and their definitions
	define("GROUP_ACL_TABLE", "sys_group_acl");	// Table name which stored group Access Control List
	define("USER_GROUP_TABLE", "sys_user_group");	// Table name which bind user with their group(s)
	define("ADDITIONAL_GROUP_FIELDS", null);	// Additional fields for Group data (this field(s) will be stored at Group object)
}
?>
