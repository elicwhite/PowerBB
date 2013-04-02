<?php
if (!defined('IN_FORUM')) exit;
if (defined('SHOW_QUERIES'))
{
	function get_microtime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
}
switch ($db_type)
{
	case 'mysql':
		require FORUM_ROOT.'include/dblayer/mysql.php';
		break;
	case 'mysqli':
		require FORUM_ROOT.'include/dblayer/mysqli.php';
		break;
	case 'pgsql':
		require FORUM_ROOT.'include/dblayer/pgsql.php';
		break;
	case 'sqlite':
		require FORUM_ROOT.'include/dblayer/sqlite.php';
		break;
	default:
		error('\''.$db_type.'\' is not a valid database type. Please check settings in config.php.', __FILE__, __LINE__);
		break;
}
$db = new DBLayer($db_host, $db_username, $db_password, $db_name, $db_prefix, $p_connect);