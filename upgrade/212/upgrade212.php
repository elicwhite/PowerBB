<?php
define('FORUM_ROOT', '../../');
require FORUM_ROOT.'include/common.php';

$db->query('UPDATE '.$db_prefix."config SET `conf_value` = '2.1.3' WHERE `conf_name` = 'o_cur_version'") or error('Unable to update table '.$db_prefix.'config. Please check your configuration and try again.');

$db->query(
'CREATE TABLE '.$db->prefix."chatbox_msg (
id int(10) NOT NULL AUTO_INCREMENT,
poster VARCHAR(200) default NULL,
poster_id INT(10) NOT NULL DEFAULT '1',
poster_ip VARCHAR(15) default NULL,
poster_email VARCHAR(50) default NULL,
message TEXT,
posted INT(10) NOT NULL default '0',
PRIMARY KEY (id)
) TYPE=MyISAM;") or error(mysql_error(),  __FILE__, __LINE__, $db->error());
$db->query('ALTER TABLE '.$db->prefix."groups ADD g_read_chatbox TINYINT(1) default '1' NOT NULL, ADD g_title_chatbox TEXT default NULL,ADD g_post_flood_chatbox SMALLINT(6) default '5' NOT NULL") or error('Unable to altrt table '.$db->prefix.'groups',  __FILE__, __LINE__, $db->error());
$db->query('ALTER TABLE '.$db->prefix."users ADD num_posts_chatbox INT(10) NOT NULL default '0', ADD displayname VARCHAR(200) NOT NULL DEFAULT ''") or error('Unable to alter table '.$db->prefix.'users',  __FILE__, __LINE__, $db->error());
$db->query('UPDATE '.$db->prefix.'groups SET g_title_chatbox=\'<strong>[Admin]</strong>&nbsp;-&nbsp;\', g_read_chatbox=1, g_post_flood_chatbox=0 WHERE g_id=1') or error('Unable to update group', __FILE__, __LINE__, $db->error());
$db->query('UPDATE '.$db->prefix.'groups SET g_title_chatbox=\'<strong>[Modo]</strong>&nbsp;-&nbsp;\', g_read_chatbox=1, g_post_flood_chatbox=0 WHERE g_id=2') or error('Unable to update group', __FILE__, __LINE__, $db->error());
$db->query('UPDATE '.$db->prefix.'groups SET g_read_chatbox=1, g_post_flood_chatbox=10 WHERE g_id=3') or error('Unable to update group', __FILE__, __LINE__, $db->error());
$db->query('UPDATE '.$db->prefix.'groups SET g_read_chatbox=1, g_post_flood_chatbox=5 WHERE g_id=4') or error('Unable to update group', __FILE__, __LINE__, $db->error());
$chatbox_config = array(
		'cb_height'		=> "'500'",
		'cb_msg_maxlength'=> "'300'",
		'cb_max_msg'	=> "'50'",
		'cb_disposition'	=> "'<strong><forum_username></strong> - <power_date> - [ <power_nbpost><power_nbpost_txt> ] <power_admin><br /><power_message><br /><br />'",
		'cb_pbb_version'	=> "'1.0'",
		'cb_enable'	=> "'1'",
		'cal_start_view'	=> "'posts'",
		'cal_show_cal'	=> "'yes'",
		'cal_user_add' => "'no'",
		'cal_mod_add'  => "'no'",
		'cal_mod_edit' => "'no'",
		'cal_start_day'	=> "'S'"
	);
	foreach($chatbox_config AS $key => $value)
	{
		$db->query("INSERT INTO ".$db->prefix."config (conf_name, conf_value) VALUES ('$key', '".$db->escape($value)."')") or error('Unable to add column "'.$key.'" to config table', __FILE__, __LINE__, $db->error());
	}
require_once FORUM_ROOT.'include/cache.php';
generate_config_cache();

$latest_version = str_replace('.', '', $configuration['o_cur_version']);
if (file_exists('../'.$latest_version))
{
?>
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
	<html>
	<head>
		<title>PowerBB Upgrade Wizard Complete</title>
		<link rel="stylesheet" type="text/css" href="../../include/css/install.css" />
	</head>
	<body>
	<div class="Banner">
		<img src="../../img/logo.png" border="0" alt="PowerBB Forum" />
	</div>
      <div class="Body">
		<div class="Contents">
			<h1>PowerBB Upgrade Wizard</h1><p>You have successfully updated. Click the link below to continue.</p>
			<div class="Button"><a href="../index.php">Click here to upgrade and continue.</a></div>
		</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
	</div>
	</body>
	</html>
<?
}
else
{
?>
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
	<html>
	<head>
		<title>PowerBB Upgrade Wizard Complete</title>
		<link rel="stylesheet" type="text/css" href="../../include/css/install.css" />
	</head>
	<body>
	<div class="Banner">
		<img src="../../img/logo.png" border="0" alt="PowerBB Forum" />
	</div>
      <div class="Body">
		<div class="Contents">
			<h1>PowerBB Upgrade Wizard</h1><p>You are now up to date. Click below to return to your forum.</p>
			<div class="Button"><a href="../../index.php">Click here to return to your forum.</a></div>
		</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
	</div>
	</body>
	</html>
<?
}
?>