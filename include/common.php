<?php
if (!defined('FORUM_ROOT')) exit('The constant FORUM_ROOT must be defined and point to a valid PowerBB Forum installation root directory.');
require FORUM_ROOT.'include/functions.php';
require FORUM_ROOT.'include/rewrite.php';
if (@ini_get('register_globals')) unregister_globals();
@include FORUM_ROOT.'config.php';
if (!defined('IN_FORUM')) exit('The file \'config.php\' doesn\'t exist or is corrupt. Please run <a href="install/index.php">install.php</a> to install PowerBB Forum first.');
list($usec, $sec) = explode(' ', microtime());
$forum_start = ((float)$usec + (float)$sec);
error_reporting(E_ALL ^ E_NOTICE);
set_magic_quotes_runtime(0);
if (get_magic_quotes_gpc())
{
	function stripslashes_array($array)
	{
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}
	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);
}
mt_srand((double)microtime()*1000000);
if (empty($cookie_name)) $cookie_name = 'forum_cookie';
define('UNVERIFIED', 32000);
define('USER_ADMIN', 1);
define('USER_MOD', 2);
define('USER_MEMBER', 3);
define('USER_GUEST', 4);
require FORUM_ROOT.'include/dblayer/common_db.php';
$db->start_transaction();
@include FORUM_ROOT.'cache/cache_config.php';
if (!defined('CONFIG_LOADED'))
{
	require FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	require FORUM_ROOT.'cache/cache_config.php';
}
@include FORUM_ROOT.'cache/cache_ads_config.php';
if (!defined('ADS_CONFIG_LOADED'))
{
	require_once FORUM_ROOT.'include/cache.php';
	generate_advertising_config_cache();
	require FORUM_ROOT.'cache/cache_ads_config.php';
}
if (!defined('DISABLE_BUFFERING'))
{
	$_SERVER['HTTP_ACCEPT_ENCODING'] = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
	if ($configuration['o_gzip'] && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false)) ob_start('ob_gzhandler');
	else ob_start();
}
$forum_user = array();
check_cookie($forum_user);
@include FORUM_ROOT.'lang/'.$forum_user['language'].'/common.php';
if (!isset($lang_common)) exit('There is no valid language pack \''.convert_htmlspecialchars($forum_user['language']).'\' installed. Please reinstall a language of that name.');
if ($configuration['o_maintenance'] && $forum_user['g_id'] > USER_ADMIN && !defined('TURN_OFF_MAINT')) maintenance_message();
@include FORUM_ROOT.'cache/cache_bans.php';
if (!defined('BANS_LOADED'))
{
	require_once FORUM_ROOT.'include/cache.php';
	generate_bans_cache();
	require FORUM_ROOT.'cache/cache_bans.php';
}
check_bans();
update_users_online();