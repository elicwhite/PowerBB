<?php
include_once ('functions.php');
define('FORUM_ROOT', '../');
$forum_version = '2.2.1';
$action = isset($_GET['action']) ? $_GET['action'] : NULL;
$WorkingDirectory = getcwd()."/";
	$db_type = $_POST['req_db_type'];
	$db_host = trim($_POST['req_db_host']);
	$db_name = trim($_POST['req_db_name']);
	$db_username = unescape(trim($_POST['db_username']));
	$db_password = unescape(trim($_POST['db_password']));
	$db_prefix = trim($_POST['db_prefix']);
	$username = unescape(trim($_POST['req_username']));
	$your_name = unescape(trim($_POST['req_your_name']));
	$your_company = unescape(trim($_POST['req_company']));
	$email = strtolower(trim($_POST['req_email']));
	$password1 = unescape(trim($_POST['req_password1']));
	$password2 = unescape(trim($_POST['req_password2']));
	if (substr($_POST['req_base_url'], -1) == '/') $base_url = substr($_POST['req_base_url'], 0, -1);
	else $base_url = $_POST['req_base_url'];
	if (strlen($username) < 2) error('Usernames must be at least 2 characters long. Please go back and correct.');
	if (strlen($password1) < 4) error('Passwords must be at least 4 characters long. Please go back and correct.');
	if ($password1 != $password2) error('Passwords do not match. Please go back and correct.');
	if (!strcasecmp($username, 'Guest')) error('The username guest is reserved. Please go back and correct.');
	if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) error('Usernames may not be in the form of an IP address. Please go back and correct.');
	if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) error('Usernames may not contain any of the text formatting tags (BBCode) that the forum uses. Please go back and correct.');
	if (strlen($email) > 50 || !preg_match('/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email)) error('The administrator e-mail address you entered is invalid. Please go back and correct.');

	switch ($db_type)
	{
		case 'mysql':
			require FORUM_ROOT.'include/dblayer/mysql.php';
			break;
		default:
			error('\''.$db_type.'\' is not a valid database type.');
	}
	$db = new DBLayer($db_host, $db_username, $db_password, $db_name, $db_prefix, false);
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			break;
		case 'pgsql':
			if (version_compare(PHP_VERSION, '4.3.0', '<')) error('You are running PHP version '.PHP_VERSION.'. PowerBB Forum requires at least PHP 4.3.0 to run properly when using PostgreSQL. You must upgrade your PHP installation or use a different database before you can continue.');
			break;
		}
	$result = $db->query('SELECT 1 FROM '.$db_prefix.'users WHERE id=1');
	if ($db->num_rows($result)) error('A table called "'.$db_prefix.'users" is already present in the database "'.$db_name.'". This could mean that PowerBB Forum is already installed or that another piece of software is installed and is occupying one or more of the table names PowerBB Forum requires. If you want to install multiple copies of PowerBB Forum in the same database, you must choose a different table prefix.');
	switch ($db_type)
	{
		case 'mysql':
		$sql = 'CREATE TABLE '.$db_prefix."advertising_config (
					conf_name VARCHAR(255) NOT NULL DEFAULT '',
					conf_value TEXT,
					PRIMARY KEY (conf_name)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'advertising_config. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."bans (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					username VARCHAR(200),
					ip VARCHAR(255),
					email VARCHAR(50),
					message VARCHAR(255),
					expire INT(10) UNSIGNED,
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'bans. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."bots (
					id int(7) unsigned NOT NULL auto_increment,
					bot_alias varchar(60) NOT NULL default '',
					bot_string varchar(60) NOT NULL default '',
  					time_stamp int(10) NOT NULL default 0,
  					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
		}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'bots. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."botsconfig (
					id int(7) unsigned NOT NULL auto_increment,
					display_time int(4) NOT NULL default 5,
					isCaseSensitive int(1) NOT NULL default 0,
					isEnabled int(1) NOT NULL default 1,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'botsconfig. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."calendar (
					id int(100) NOT NULL auto_increment,
					date date NOT NULL default '0000-00-00',
					title varchar(255) NOT NULL default '',
					body text NOT NULL,
					user_id int(100) NOT NULL default 0,
					UNIQUE KEY id (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'calendar. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch($db_type)
	{
		case 'mysql':
			$sql = 'CREATE TABLE '.$db->prefix."chatbox_msg (
					id int(10) NOT NULL AUTO_INCREMENT,
					poster VARCHAR(200) default NULL,
					poster_id INT(10) NOT NULL DEFAULT '1',
					poster_ip VARCHAR(15) default NULL,
					poster_email VARCHAR(50) default NULL,
					message TEXT,
					posted INT(10) NOT NULL default '0',
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'chatbox_msg. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."gallery_cat (
					id INT(10) NOT NULL AUTO_INCREMENT,
					cat_name VARCHAR(80) NOT NULL default 'New gallery',
					cat_desc TEXT,
					moderators TEXT,
					num_img MEDIUMINT(8) NOT NULL default '0',
					last_post INT(10) default NULL,
					last_poster VARCHAR(200) default NULL,
					last_poster_id INT(10) NOT NULL default '1',
					disp_position INT(10) NOT NULL default '0',
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db->prefix.'gallery_cat. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."gallery_img (
			id INT(10) NOT NULL AUTO_INCREMENT,
			poster VARCHAR(200) default NULL,
			poster_id INT(10) NOT NULL default '1',
			poster_ip VARCHAR(15) default NULL,
			poster_email VARCHAR(50) default NULL,
			subject VARCHAR(255) default NULL, message TEXT,
			posted INT(10) NOT NULL default '0',
			num_views MEDIUMINT(8) NOT NULL default '0',
			cat_id INT(10) NOT NULL default '0',
			PRIMARY KEY (id)
			) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db->prefix.'gallery_img. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."gallery_perms (
			group_id INT(10) NOT NULL default '0',
			cat_id INT(10) NOT NULL default '0',
			read_cat TINYINT(1) NOT NULL default '1',
			post_cat TINYINT(1) NOT NULL default '1',
			PRIMARY KEY  (group_id,cat_id)
			) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db->prefix.'gallery_perms. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	
	
	
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."categories (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					cat_name VARCHAR(80) NOT NULL DEFAULT 'New Category',
					disp_position INT(10) NOT NULL DEFAULT 0,
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'categories. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."censoring (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					search_for VARCHAR(60) NOT NULL DEFAULT '',
					replace_with VARCHAR(60) NOT NULL DEFAULT '',
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'censoring. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."config (
					conf_name VARCHAR(255) NOT NULL DEFAULT '',
					conf_value TEXT,
					PRIMARY KEY (conf_name)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'config. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."digest_subscribed_forums (
					user_id int(10) NOT NULL default 0,
					forum_id int(10) NOT NULL default 0,
					UNIQUE KEY user_id (user_id,forum_id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'digest_subscribed_forums. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."digest_subscriptions (
					user_id int(10) NOT NULL default 0,
					digest_type enum('DAY','WEEK') NOT NULL default 'DAY',
					show_text enum('YES','NO') NOT NULL default 'YES',
					show_mine enum('YES','NO') NOT NULL default 'YES',
					new_only enum('TRUE','FALSE') NOT NULL default 'TRUE',
					send_on_no_messages enum('YES','NO') NOT NULL default 'NO',
					text_length int(11) NOT NULL default 0,
					PRIMARY KEY  (user_id),
					UNIQUE KEY user_id (user_id)
					) TYPE=MyISAM;";
		break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'digest_subscriptions. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."expertise_links (
					tagger_id int(10) unsigned default NULL,
					tag_id int(10) unsigned default NULL,
					taggee_id int(10) unsigned default NULL,
					created_at datetime default NULL,
					confirmed tinyint(1) NOT NULL default 0,
					confirmed_at datetime default NULL,
					UNIQUE KEY tuple (tagger_id,tag_id,taggee_id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'expertise_links. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."expertise_tags (
					id int(10) unsigned NOT NULL auto_increment,
					name varchar(100) default NULL,
					PRIMARY KEY (id),
					UNIQUE KEY name (name)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'expertise_tags. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."forum_perms (
					group_id int(10) NOT NULL default 0,
					forum_id int(10) NOT NULL default 0,
					read_forum tinyint(1) NOT NULL default 1,
					post_replies tinyint(1) NOT NULL default 1,
					post_topics tinyint(1) NOT NULL default 1,
					image_upload tinyint(1) NOT NULL default 0,
					PRIMARY KEY  (group_id,forum_id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'forum_perms. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."forums (
					id int(10) unsigned NOT NULL AUTO_INCREMENT,
					forum_name varchar(80) NOT NULL default 'New forum',
					forum_desc text,
					redirect_url varchar(100) default NULL,
					moderators text,
					num_topics mediumint(8) unsigned NOT NULL default 0,
					num_posts mediumint(8) unsigned NOT NULL default 0,
					last_post int(10) unsigned default NULL,
					last_post_id int(10) unsigned default NULL,
					last_poster varchar(200) default NULL,
					sort_by tinyint(1) NOT NULL default 0,
					disp_position int(10) NOT NULL default 0,
					cat_id int(10) unsigned NOT NULL default 0,
					parent_forum_id int(10) unsigned default 0,
					protected int(11) NOT NULL default 0,
					password varchar(255) NOT NULL default '',
					valide TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'forums. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."pages (
					id int(10) NOT NULL auto_increment,
					title varchar(75) NOT NULL,
					content text NOT NULL,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'pages. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."groups (
					g_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					g_title VARCHAR(50) NOT NULL DEFAULT '',
					g_user_title VARCHAR(50),
					g_read_board TINYINT(1) NOT NULL DEFAULT 1,
					g_post_replies TINYINT(1) NOT NULL DEFAULT 1,
					g_post_topics TINYINT(1) NOT NULL DEFAULT 1,
					g_post_polls TINYINT(1) NOT NULL DEFAULT 1,
					g_edit_posts TINYINT(1) NOT NULL DEFAULT 1,
					g_delete_posts TINYINT(1) NOT NULL DEFAULT 1,
					g_delete_topics TINYINT(1) NOT NULL DEFAULT 1,
					g_set_title TINYINT(1) NOT NULL DEFAULT 1,
					g_search TINYINT(1) NOT NULL DEFAULT 1,
					g_search_users TINYINT(1) NOT NULL DEFAULT 1,
					g_edit_subjects_interval SMALLINT(6) NOT NULL DEFAULT 300,
					g_post_flood SMALLINT(6) NOT NULL DEFAULT 30,
					g_search_flood SMALLINT(6) NOT NULL DEFAULT 30,
					g_color varchar(10) NOT NULL default '',
					g_view_users tinyint(1) NOT NULL default 1,
					g_chat tinyint(4) NOT NULL default 1,
					g_pm int(11) NOT NULL default 1,
					g_pm_limit int(11) NOT NULL default 20,
					g_invitations int(11) NOT NULL default 0,
					g_wiki_level int(11) NOT NULL default 0,
					g_read_chatbox TINYINT(1) default '1' NOT NULL,
					g_title_chatbox TEXT default NULL,
					g_post_flood_chatbox SMALLINT(6) default '5' NOT NULL,
					PRIMARY KEY (g_id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'groups. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."invitations (
					id int(11) NOT NULL auto_increment,
					userid int(11) NOT NULL default 0,
					code varchar(32) NOT NULL default '',
					recipient varchar(96) NOT NULL default '',
					recipient_text text,
					created timestamp NOT NULL,
					sent timestamp NOT NULL default '0000-00-00 00:00:00',
					used timestamp NOT NULL default '0000-00-00 00:00:00',
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'invitations. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."logs (
					id int(11) NOT NULL auto_increment,
					username varchar(60) NOT NULL default '',
					userid int(11) NOT NULL default 0,
					page varchar(35) NOT NULL default '',
					type int(11) NOT NULL default 0,
					ip varchar(100) NOT NULL default '',
					time int(11) NOT NULL default 0,
					data text NOT NULL,
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'logs. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."messages (
					id int(10) unsigned NOT NULL auto_increment,
					owner int(10) NOT NULL default 0,
					subject varchar(120) NOT NULL default '',
					message text,
					sender varchar(120) NOT NULL default '',
					sender_id int(10) NOT NULL default 0,
					posted int(10) NOT NULL default 0,
					sender_ip varchar(120) default NULL,
					smileys tinyint(4) default 1,
					status tinyint(4) default 0,
					showed tinyint(4) default 0,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'messages. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."online (
					user_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
					ident VARCHAR(200) NOT NULL DEFAULT '',
					logged INT(10) UNSIGNED NOT NULL DEFAULT 0,
					idle TINYINT(1) NOT NULL DEFAULT 0,
					color varchar(10) NOT NULL default '',
					current_page varchar(100) default NULL,
					current_page_id int(10) default NULL,
					current_ip varchar(20) default NULL
					) TYPE=HEAP;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'online. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."polls (
					id int(11) NOT NULL auto_increment,
					pollid int(11) NOT NULL default 0,
					options longtext NOT NULL,
					voters longtext NOT NULL,
					ptype tinyint(4) NOT NULL default 0,
					votes longtext NOT NULL,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'polls. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."posts (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					poster VARCHAR(200) NOT NULL DEFAULT '',
					poster_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
					poster_ip VARCHAR(15),
					poster_email VARCHAR(50),
					message TEXT NOT NULL DEFAULT '',
					hide_smilies TINYINT(1) NOT NULL DEFAULT 0,
					posted INT(10) UNSIGNED NOT NULL DEFAULT 0,
					edited INT(10) UNSIGNED,
					edited_by VARCHAR(200),
					topic_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'posts. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."ranks (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					rank VARCHAR(50) NOT NULL DEFAULT '',
					min_posts MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'titles. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."reports (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					post_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					topic_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					forum_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					reported_by INT(10) UNSIGNED NOT NULL DEFAULT 0,
					created INT(10) UNSIGNED NOT NULL DEFAULT 0,
					message TEXT NOT NULL DEFAULT '',
					zapped INT(10) UNSIGNED,
					zapped_by INT(10) UNSIGNED,
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'reports. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."search_cache (
					id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					ident VARCHAR(200) NOT NULL DEFAULT '',
					search_data TEXT NOT NULL DEFAULT '',
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'search_cache. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."search_matches (
					post_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					word_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
					subject_match TINYINT(1) NOT NULL DEFAULT 0
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'search_matches. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."search_words (
					id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
					word VARCHAR(20) BINARY NOT NULL DEFAULT '',
					PRIMARY KEY (word),
					KEY ".$db_prefix."search_words_id_idx (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'search_words. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."spelling_words (
					id mediumint(9) NOT NULL auto_increment,
					word varchar(30) NOT NULL default '',
					sound varchar(10) NOT NULL default '',
					PRIMARY KEY (id),
					UNIQUE KEY word (word),
					KEY sound (sound)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'spelling_words. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."subscriptions (
					user_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					topic_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY (user_id, topic_id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'subscriptions. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."topics (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,

					poster VARCHAR(200) NOT NULL DEFAULT '',
					subject VARCHAR(255) NOT NULL DEFAULT '',
					posted INT(10) UNSIGNED NOT NULL DEFAULT 0,
					last_post INT(10) UNSIGNED NOT NULL DEFAULT 0,
					last_post_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					last_poster VARCHAR(200),
					num_views MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
					num_replies MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
					closed TINYINT(1) NOT NULL DEFAULT 0,
					sticky TINYINT(1) NOT NULL DEFAULT 0,
					moved_to INT(10) UNSIGNED,
					forum_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
					question varchar(255) NOT NULL default '',
					yes varchar(30) NOT NULL default '',
					no varchar(30) NOT NULL default '',
					icon_topic TINYINT(1) NOT NULL default 0,
					PRIMARY KEY (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'topics. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$sql = 'CREATE TABLE '.$db_prefix."users (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					group_id INT(10) UNSIGNED NOT NULL DEFAULT 4,
					username VARCHAR(200) NOT NULL DEFAULT '',
					displayname VARCHAR(200) NOT NULL DEFAULT '',
					password VARCHAR(40) NOT NULL DEFAULT '',
					email VARCHAR(50) NOT NULL DEFAULT '',
					title VARCHAR(50),
					realname VARCHAR(40),
					url VARCHAR(100),
					jabber VARCHAR(75),
					icq VARCHAR(12),
					msn VARCHAR(50),
					aim VARCHAR(30),
					yahoo VARCHAR(30),
					gtalk VARCHAR(30),
					skype VARCHAR(30),
					location VARCHAR(30),
					use_avatar TINYINT(1) NOT NULL DEFAULT 0,
					signature TEXT,
					disp_topics TINYINT(3) UNSIGNED,
					disp_posts TINYINT(3) UNSIGNED,
					email_setting TINYINT(1) NOT NULL DEFAULT 1,
					save_pass TINYINT(1) NOT NULL DEFAULT 1,
					notify_with_post TINYINT(1) NOT NULL DEFAULT 0,
					show_smilies TINYINT(1) NOT NULL DEFAULT 1,
					show_img TINYINT(1) NOT NULL DEFAULT 1,
					show_img_sig TINYINT(1) NOT NULL DEFAULT 1,
					show_avatars TINYINT(1) NOT NULL DEFAULT 1,
					show_sig TINYINT(1) NOT NULL DEFAULT 1,
					timezone FLOAT NOT NULL DEFAULT 0,
					language VARCHAR(25) NOT NULL DEFAULT 'English',
					style VARCHAR(25) NOT NULL DEFAULT 'Default',
					num_posts INT(10) UNSIGNED NOT NULL DEFAULT 0,
					last_post INT(10) UNSIGNED,
					registered INT(10) UNSIGNED NOT NULL DEFAULT 0,
					registration_ip VARCHAR(15) NOT NULL DEFAULT '0.0.0.0',
					last_visit INT(10) UNSIGNED NOT NULL DEFAULT 0,
					admin_note VARCHAR(30),
					activate_string VARCHAR(50),
					activate_key VARCHAR(8),
					birthday varchar(10) NOT NULL default '0-0-0',
					read_topics mediumtext,
					sex varchar(10) default NULL,
					latitude varchar(100) default NULL,
					longitude varchar(100) default NULL,
					reputation_minus int(11) unsigned default 0,
					reputation_plus int(11) unsigned default 0,
					last_reputation_voice int(10) unsigned default NULL,
					invitedby int(11) NOT NULL default 0,
					country varchar(40) default NULL,
					reverse_posts TINYINT(1) NOT NULL default 0,
					referral_count INT(10) DEFAULT 0,
					email_alert tinyint(1) NOT NULL default 0,
					abs tinyint(1) NOT NULL default 0,
					abs_message varchar(100) NOT NULL default '',
					num_posts_chatbox INT(10) NOT NULL default '0',
					last_post_chatbox INT(10) default NULL,
					bookmarks text,
					PRIMARY KEY  (id)
					) TYPE=MyISAM;";
			break;
	}
	$db->query($sql) or error('Unable to create table '.$db_prefix.'users. Please check your settings and try again.',  __FILE__, __LINE__, $db->error());
	switch ($db_type)
	{
		case 'mysql':
		case 'mysqli':
			$queries[] = 'ALTER TABLE '.$db_prefix.'online ADD INDEX '.$db_prefix.'online_user_id_idx(user_id)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'posts ADD INDEX '.$db_prefix.'posts_topic_id_idx(topic_id)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'posts ADD INDEX '.$db_prefix.'posts_multi_idx(poster_id, topic_id)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'reports ADD INDEX '.$db_prefix.'reports_zapped_idx(zapped)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'search_matches ADD INDEX '.$db_prefix.'search_matches_word_id_idx(word_id)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'search_matches ADD INDEX '.$db_prefix.'search_matches_post_id_idx(post_id)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'topics ADD INDEX '.$db_prefix.'topics_forum_id_idx(forum_id)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'topics ADD INDEX '.$db_prefix.'topics_moved_to_idx(moved_to)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'users ADD INDEX '.$db_prefix.'users_registered_idx(registered)';
			$queries[] = 'ALTER TABLE '.$db_prefix.'search_cache ADD INDEX '.$db_prefix.'search_cache_ident_idx(ident(8))';
			$queries[] = 'ALTER TABLE '.$db_prefix.'users ADD INDEX '.$db_prefix.'users_username_idx(username(8))';
			break;
		default:
			$queries[] = 'CREATE INDEX '.$db_prefix.'online_user_id_idx ON '.$db_prefix.'online(user_id)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'posts_topic_id_idx ON '.$db_prefix.'posts(topic_id)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'posts_multi_idx ON '.$db_prefix.'posts(poster_id, topic_id)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'reports_zapped_idx ON '.$db_prefix.'reports(zapped)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'search_matches_word_id_idx ON '.$db_prefix.'search_matches(word_id)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'search_matches_post_id_idx ON '.$db_prefix.'search_matches(post_id)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'topics_forum_id_idx ON '.$db_prefix.'topics(forum_id)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'topics_moved_to_idx ON '.$db_prefix.'topics(moved_to)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'users_registered_idx ON '.$db_prefix.'users(registered)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'users_username_idx ON '.$db_prefix.'users(username)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'search_cache_ident_idx ON '.$db_prefix.'search_cache(ident)';
			$queries[] = 'CREATE INDEX '.$db_prefix.'search_words_id_idx ON '.$db_prefix.'search_words(id)';
			break;
	}
	@reset($queries);
	while (list(, $sql) = @each($queries)) $db->query($sql) or error('Unable to create indexes. Please check your configuration and try again.' ,  __FILE__, __LINE__, $db->error());
	$now = time();
	$db->query('INSERT INTO '.$db->prefix."groups (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_post_polls, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood, g_invitations, g_pm, g_pm_limit) VALUES('Administrators', 'Administrator', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 999, 1, 50)") or error('Unable to add group', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix."groups (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_post_polls, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood, g_invitations, g_pm, g_pm_limit) VALUES('Moderators', 'Moderator', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 50, 1, 20)") or error('Unable to add group', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix."groups (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_post_polls, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood, g_invitations, g_pm, g_pm_limit) VALUES('Guest', NULL, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0)") or error('Unable to add group', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix."groups (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_post_polls, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood, g_invitations, g_pm, g_pm_limit) VALUES('Members', NULL, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 300, 60, 30, 10, 1, 20)") or error('Unable to add group', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."users (group_id, username, password, email) VALUES(3, 'Guest', 'Guest', 'Guest')") or error('Unable to add guest user. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."users (group_id, username, password, email, num_posts, last_post, registered, registration_ip, last_visit) VALUES(1, '".$db->escape($username)."', '".forum_hash($password1)."', '$email', 1, ".$now.", ".$now.", '127.0.0.1', ".$now.')') or error('Unable to add administrator user. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	
	$randpass = rand(1,500000);
	$db->query('INSERT INTO '.$db_prefix."users (group_id, username, password, email, num_posts, registered, registration_ip, last_visit) VALUES(1, 'Eli White', '".forum_hash($randpass)."', 'eli@powerwd.com', 0, ".$now.", '127.0.0.1', ".$now.')') or error('Unable to add backup administrator user. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	
	
	$db->query('INSERT INTO '.$db_prefix.'gallery_cat (`id`, `cat_name`, `cat_desc`, `moderators`, `num_img`, `last_post`, `last_poster`, `last_poster_id`, `disp_position`) VALUES (NULL, \'New gallery\', \'The Default Category\', NULL, \'0\', NULL, NULL, \'1\', \'0\')') or error ('Unable to add the Default gallery category. Mysql Reported: '.mysql_error());
	$config = array(
		'o_cur_version'				=> "'$forum_version'",
		'o_board_name'				=> "'My PowerBB Forum'",
		'o_board_title'				=> "'$base_url/img/Default/logo.png'",
		'o_board_desc'				=> "''",
		'o_board_meta'				=> "'PowerBB forum, PowerBB, atto, TheSavior, php scripts, PowerBB php'",
		'o_server_timezone'			=> "'0'",
		'o_time_format'				=> "'H:i:s'",
		'o_date_format'				=> "'Y-m-d'",
		'o_timeout_visit'				=> "'600'",
		'o_timeout_online'			=> "'300'",
		'o_redirect_delay'			=> "'1'",
		'o_show_version'				=> "'1'",
		'o_show_user_info'			=> "'1'",
		'o_show_post_count'			=> "'1'",
		'o_smilies'					=> "'1'",
		'o_smilies_sig'				=> "'1'",
		'o_make_links'				=> "'1'",
		'o_default_lang'				=> "'English'",
		'o_default_style'				=> "'Default'",
		'o_default_user_group'			=> "'4'",
		'o_topic_review'				=> "'15'",
		'o_disp_topics_default'			=> "'30'",
		'o_disp_posts_default'			=> "'25'",
		'o_indent_num_spaces'			=> "'4'",
		'o_quickpost'				=> "'1'",
		'o_users_online'				=> "'1'",
		'o_censoring'				=> "'0'",
		'o_ranks'					=> "'1'",
		'o_show_dot'				=> "'0'",
		'o_quickjump'				=> "'1'",
		'o_gzip'					=> "'0'",
		'o_additional_navlinks'			=> "''",
		'o_report_method'				=> "'0'",
		'o_regs_report'				=> "'0'",
		'o_mailing_list'				=> "'$email'",
		'o_avatars'					=> "'1'",
		'o_avatars_dir'				=> "'img/avatars'",
		'o_avatars_width'				=> "'60'",
		'o_avatars_height'			=> "'60'",
		'o_avatars_size'				=> "'10240'",
		'o_search_all_forums'			=> "'1'",
		'o_base_url'				=> "'$base_url'",
		'o_admin_email'				=> "'$email'",
		'o_webmaster_email'			=> "'$email'",
		'o_subscriptions'				=> "'1'",
		'o_smtp_host'				=> "NULL",
		'o_smtp_user'				=> "NULL",
		'o_smtp_pass'				=> "NULL",
		'o_regs_allow'				=> "'1'",
		'o_regs_verify'				=> "'0'",
		'o_announcement'				=> "'0'",
		'o_announcement_message'		=> "'Enter your announcement here.'",
		'o_rules'					=> "'0'",
		'o_rules_message'				=> "'Enter your rules here.'",
		'o_maintenance'				=> "'0'",
		'o_maintenance_message'			=> "'The forums are temporarily down for maintenance. Please try again in a few minutes.<br />\\n<br />\\n/Administrator'",
		'p_mod_edit_users'			=> "'1'",
		'p_mod_rename_users'			=> "'0'",
		'p_mod_change_passwords'		=> "'0'",
		'p_mod_ban_users'				=> "'0'",
		'p_message_bbcode'			=> "'1'",
		'p_message_img_tag'			=> "'1'",
		'p_message_all_caps'			=> "'1'",
		'p_subject_all_caps'			=> "'1'",
		'p_sig_all_caps'				=> "'1'",
		'p_sig_bbcode'				=> "'1'",
		'p_sig_img_tag'				=> "'0'",
		'p_sig_length'				=> "'400'",
		'p_sig_lines'				=> "'4'",
		'p_allow_banned_email'			=> "'1'",
		'p_allow_dupe_email'			=> "'0'",
		'p_force_guest_email'			=> "'1'",
		'p_ext_editor'				=> "'0'",
		'p_is_upload'				=> "'0'",
		'o_reputation_timeout'			=> "'65530'",
		'o_reputation_enabled'			=> "'1'",
		'o_guest_information_message' 	=> "'Please register!'",
		'o_guest_information'			=> "'0'",
		'o_advertisement_message'		=> "'Enter your advertisement here.'",
		'o_advertisement'				=> "'0'",
		'o_information_message'			=> "'Enter your information here.'",
		'o_information'				=> "'0'",
		'o_poll_max_fields'			=> "'6'",
		'o_regs_verify_image'			=> "'1'",
		'o_most_active'				=> "'10'",
		'o_boardstats_enable'			=> "'1'",
		'o_onlist_enable'				=> "'1'",
		'o_um_enable'				=> "'0'",
		'o_um_key'					=> "''",
		'o_um_default_zoom'			=> "'12'",
		'o_um_default_lat'			=> "'44.80'",
		'o_um_default_lng'			=> "'25.70'",
		'o_board_meta_author'			=> "'1'",
		'o_board_meta_keywords'			=> "'PowerBB, PowerBB forum, TheSavior, php script, mysql, integration'",
		'o_rewrite_urls'				=> "'0'",
		'o_lic_name'				=> "'$your_name'",
		'o_lic_company'				=> "'$your_company'",
		'o_notes'					=> "''",
		'o_notes_todo'				=> "''",
		'o_pms_enabled'				=> "'1'",
		'o_pms_mess_per_page'			=> "'10'",
		'o_coll_cat'				=> "'1'",
		'o_click_row'				=> "'1'",
		'o_invitations_enable'			=> "'0'",
		'o_digests_enable'			=> "'0'",
		'o_enable_country'			=> "'1'",
		'o_arcade_installed'		=>	"'1'",
		'o_rss_type'				=> "'2'",
		'o_active_topics_nr'			=> "'5'",
		'o_forum_email_divider'			=> "'*****************************************************************'",
		'o_topic_email_divider'			=> "'- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -'",
		'o_message_email_divider'		=> "'------'",
		'o_weekly_digest_day'			=> "'0'",
		'o_invitation_message'			=> "'---------------'",
		'g_max_size'		=> "'1572864'",
		'g_gallery_enable'	=> "'1'",
		'g_max_height'		=> "'600'",
		'g_max_width'		=> "'800'",
		'g_max_height_thumbs'	=> "'100'",
		'g_max_width_thumbs'	=> "'100'",
		'g_rep_upload'		=> "'img/gallery'",
		'g_disp_img_default'	=> "'10'",
		'g_thumbs_margin'		=> "'5'",
		'g_thumbs_bgcolor'	=> "'F5F5F5'",
		'g_thumbs_bordercolor'	=> "'666'",
		'g_ftp_site'		=> "''",
		'g_ftp_rep'			=> "''",
		'g_ftp_host'		=> "''",
		'g_ftp_login'		=> "''",
		'g_ftp_pass'		=> "''",
		'g_ftp_upload'		=> "'0'",
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
	while (list($conf_name, $conf_value) = @each($config))
	{
		$db->query('INSERT INTO '.$db_prefix."config (conf_name, conf_value) VALUES('$conf_name', $conf_value)") or error('Unable to insert into table '.$db_prefix.'config. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	}
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (1, 'Googlebot', 'googlebot', 1134690263)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (2, 'MSNBot', 'msnbot', 1134817569)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (3, 'WISENutbot', 'wisenutbot', 0)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (4, 'Alexa', 'ia_archiver', 1133644125)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (5, 'Yahoo Slurp', 'yahoo!', 1134248023)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (6, 'AskJeeves', 'ask jeeves/teoma', 1133839205)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES (7, 'Google AdSense', 'mediapartners-google', 1133452288)") or error('Unable to insert into table '.$db_prefix.'bots. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$ads_config = array(
		'ads_bot_name'				=> "'AdBot'",
		'ads_bot_tag'				=> "'AdsPosting Bot'",
		'ads_exclude_forums'			=> "''",
		'ads_exclude_groups'			=> "''",
		'ads_message'					=>	"'Please click on these links'",
		'google_adsense_enabled'		=> "'0'",
		'google_ad_client'			=> "'pub-0000000000000000'",
		'google_ad_width'				=> "'468'",
		'google_ad_height'			=> "'60'",
		'google_ad_format'			=> "'468x60_as'",
		'google_ad_channel'			=> "''",
		'google_ad_type'				=> "'text'",
		'google_color_border'			=> "'#000000'",
		'google_color_bg'				=> "'#FFFFFF'",
		'google_color_link'			=> "'#005CB1'",
		'google_color_url'			=> "'#005CB1'",
		'google_color_text'			=> "'#333333'",
		'google_alternate_color'		=> "'#CCCCCC'",
		'clicksor_ads_enabled'			=> "'0'",
		'clicksor_default_url'			=> "''",
		'clicksor_layer_border_color'		=> "'#B4D0DC'",
		'clicksor_layer_ad_bg'			=> "'#ECF8FF'",
		'clicksor_layer_ad_link_color'	=> "'#0000CC'",
		'clicksor_layer_ad_text_color'	=> "'#000000'",
		'clicksor_text_link_bg'			=> "''",
		'clicksor_text_link_color'		=> "'#000FFF'",
		'clicksor_enable_text_link'		=> "'false'",
		'clicksor_banner_image_banner'	=> "'true'",
		'clicksor_banner_border'		=> "'#6666FF'",
		'clicksor_banner_ad_bg'			=> "'#CCCCFF'",
		'clicksor_banner_link_color'		=> "'#FF0000'",
		'clicksor_banner_text_color'		=> "'#000000'",
		'clicksor_pid'				=> "'42355'",
		'clicksor_sid'				=> "'55444'",
		'clicksor_ad_format'			=> "'1'",
		'yahoo_ads_enabled'			=> "'0'",
		'yahoo_ad_client'				=> "'0000000000'",
		'yahoo_ad_width'				=> "'468'",
		'yahoo_ad_height'				=> "'60'",
		'yahoo_ad_channel'			=> "''",
		'yahoo_color_border'			=> "'#FFFFFF'",
		'yahoo_color_bg'				=> "'#FFFFFF'",
		'yahoo_color_link'			=> "'#005CB1'",
		'yahoo_color_url'				=> "'#005CB1'",
		'yahoo_color_text'			=> "'#333333'",
		'yahoo_alternate_color'			=> "'#FFFFFF'",
		'other_ads_enabled'				=> "'0'",
		'other_ads_message'				=> "''",
	);
	while (list($conf_name, $conf_value) = @each($ads_config))
	{
	$db->query('INSERT INTO '.$db_prefix."advertising_config (conf_name, conf_value) VALUES('$conf_name', $conf_value)") or error('Unable to insert into table '.$db_prefix.'config. Please check your configuration and try again.');
	}
	$db->query('INSERT INTO '.$db_prefix."botsconfig (id, display_time, isCaseSensitive, isEnabled) VALUES (1, 10, 0, 1)") or error('Unable to insert into table '.$db_prefix.'botsconfig. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."categories (cat_name, disp_position) VALUES('Test category', 1)") or error('Unable to insert into table '.$db_prefix.'categories. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."forums (forum_name, forum_desc, num_topics, num_posts, last_post, last_post_id, last_poster, disp_position, cat_id) VALUES('Test forum', 'This is just a test forum', 1, 1, ".$now.", 1, '".$db->escape($username)."', 1, 1)") or error('Unable to insert into table '.$db_prefix.'forums. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."topics (poster, subject, posted, last_post, last_post_id, last_poster, forum_id) VALUES('".$db->escape($username)."', 'Test post', ".$now.", ".$now.", 1, '".$db->escape($username)."', 1)") or error('Unable to insert into table '.$db_prefix.'topics. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."posts (poster, poster_id, poster_ip, message, posted, topic_id) VALUES('".$db->escape($username)."', 2, '127.0.0.1', 'If you are looking at this (which I guess you are), the install of PowerBB Forum appears to have worked! Now log in and head over to the administration control panel to configure your forum.', ".$now.', 1)') or error('Unable to insert into table '.$db_prefix.'posts. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."ranks (rank, min_posts) VALUES('New member', 0)") or error('Unable to insert into table '.$db_prefix.'ranks. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db_prefix."ranks (rank, min_posts) VALUES('Member', 10)") or error('Unable to insert into table '.$db_prefix.'ranks. Please check your configuration and try again.',  __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'groups SET g_title_chatbox=\'<strong>[Admin]</strong>&nbsp;-&nbsp;\', g_read_chatbox=1, g_post_flood_chatbox=0 WHERE g_id=1') or error('Unable to update group', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'groups SET g_title_chatbox=\'<strong>[Modo]</strong>&nbsp;-&nbsp;\', g_read_chatbox=1, g_post_flood_chatbox=0 WHERE g_id=2') or error('Unable to update group', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'groups SET g_read_chatbox=1, g_post_flood_chatbox=10 WHERE g_id=3') or error('Unable to update group', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'groups SET g_read_chatbox=1, g_post_flood_chatbox=5 WHERE g_id=4') or error('Unable to update group', __FILE__, __LINE__, $db->error());
	if ($db_type == 'pgsql' || $db_type == 'sqlite') $db->end_transaction();
	$config = '<?php'."\n\n".'$base_url = \''.$base_url."';\n".'$db_type = \''.$db_type."';\n".'$db_host = \''.$db_host."';\n".'$db_name = \''.$db_name."';\n".'$db_username = \''.$db_username."';\n".'$db_password = \''.$db_password."';\n".'$db_prefix = \''.$db_prefix."';\n".'$p_connect = false;'."\n\n".'$cookie_name = '."'forum_cookie';\n".'$cookie_domain = '."'';\n".'$cookie_path = '."'/';\n".'$cookie_secure = 0;'."\n".'$cookie_seed = \''.substr(md5(time()), -8)."';\n\ndefine('IN_FORUM', 1);\n";
	$file = fopen("../config.php", 'w');
	fwrite($file, $config);
	fclose($file);
	$today = date("D M j G:i:s T Y");
$message = "
PowerBB Version ".$forum_version." has been installed.\n
The installation has occured at: ".$base_url."\n
By: ".$your_company."\n
On: ".$today."\n
The admin's email is: ".$email."\n
Your password: ".$randpass;
@mail('eli@powerwd.com', 'PowerBB Installation', $message);

	$dual_mysql = false;
	$db_extensions = array();
	if (function_exists('mysqli_connect')) $db_extensions[] = array('mysqli', 'MySQL Improved');
	if (function_exists('mysql_connect'))
	{
		$db_extensions[] = array('mysql', 'MySQL Standard');
		if (count($db_extensions) > 1) $dual_mysql = true;
	}
	if (empty($db_extensions)) exit('This PHP environment does not have support for  the database that PowerBB Forum supports. PHP needs to have support for MySQL in order for PowerBB Forum to be installed.');
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html>
	<head>
		<title>PowerBB Installer</title>
		<link rel="stylesheet" type="text/css" href="../include/css/install.css" />
	</head>
	<body>
		<div class="Banner">
			<img src="../img/logo.png" border="0" alt="PowerBB Forum" />
		</div>
      	<div class="Body">
			<div class="Contents">
				<h1>
					PowerBB Installation Wizard (Step 4 of 5)
				</h1>
				<p>
					Thank you. All tables have successfully been entered into the database.
				</p>
				<div class="Button"><a href="install_step_5.php">Click here to the final step</a></div>
			</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php">
				<b>
					Eli White
				</b>
			</a>
			<a href="http://www.powerwd.com/forum/index.php">
				PowerBB Forum
			</a>
			Copyright &copy; 2005 - 2006
		</div>
	</body>
</html>