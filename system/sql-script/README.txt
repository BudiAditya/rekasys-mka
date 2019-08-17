This folder contains SQL script that can be used by SqlAcl class
If you were using another database engine you have 2 options to use this class:

Option #1:
1. Restore this script to your favourite DBMS (This script intended to MySQL for other database there is minor changes in query)
2. Setting the connector settings to your database engine
3. Change ACL_TYPE in user.config.php to 'Sql' without quote
4. Done

Option #2: (If you already have user(s) table or other table used for authentication mechanism)
1. Create another class that extending AclBase class (you can copy paste the sql_acl.php and made some changes) and put it in 'acl' folder
	Note: name must be ended with '_acl.php'
2. Depend on your code in previous acl you may be change the connector settings appropriately
3. Change ACL_TYPE in user.config.php to [ClassName] you created before
	Note: Don't append the Acl even the class name have Acl suffix
4. Done

Please delete this folder in production server or after you restore the data to MySQL
This folder contains script for sample SqlAcl purpose and does nothing to your actual application
