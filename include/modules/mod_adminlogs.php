<?php
define("AddBan",1);
define("EditBan",2);
define("RemBan",3);
define("AddCat",4);
define("RemCat",5);
define("EditCat",6);
define("AddCensor",7);
define("RemCensor",8);
define("EditCensor",9);
define("AddForum",10);
define("RemForum",11);
define("EditForum",12);
define("AddGroup",13);
define("RemGroup",14);
define("EditGroup",15);
define("EditOptions",16);
define("EditPermissions",17);
define("AddRank",18);
define("RemRank",19);
define("EditRank",20);
define("EditPassword",21);
define("EditUserGroup",23);
define("EditForumMods",24);
define("RemUser",25);
define("EditUser",26);

/**
 * Enter description here...
 *
 * @param unknown_type $type
 * @param unknown_type $data

 */
function AddLog($type, $data)
{
	global $db, $forum_user, $configuration;
	$time = time();
	$page = basename($_SERVER['PHP_SELF']);
	$indata = serialize($data);
	$query = 'INSERT INTO '.$db->prefix.'logs (username, userid, page, type, ip, time, data) VALUES(\''.$db->escape($forum_user['username']).'\', '.intval($forum_user['id']).', \''.$db->escape($page).'\', \''.intval($type).'\', \''.$_SERVER['REMOTE_ADDR'].'\', \''.$time.'\', \''.$db->escape($indata).'\')';
	$db->query($query) or error('Unable to add log', __FILE__, __LINE__, $db->error());
}

/**
 * Enter description here...
 *
 * @param unknown_type $data

 */
function array_fix(&$data)
{
	global $db;
	$data = $db->escape($data);
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function UsernameFromID ($id)
{
	global $db;
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.intval($id)) or error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
	$t_user = $db->fetch_assoc($result);
	return $t_user['username'];
}
/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function BanFromID ($id)
{
	global $db;
	$result = $db->query('SELECT username, ip, email, message FROM '.$db->prefix.'bans WHERE id='.intval($id)) or error('Unable to fetch ban information', __FILE__, __LINE__, $db->error());
	$t_ban = $db->fetch_row($result);
	return $t_ban;
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function CatFromID ($id)
{
	global $db;
	$result = $db->query('SELECT cat_name FROM '.$db->prefix.'categories WHERE id='.intval($id)) or error('Unable to fetch cat information', __FILE__, __LINE__, $db->error());
	$t_cat = $db->fetch_assoc($result);
	return $t_cat['cat_name'];
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function CensorFromID ($id)
{
	global $db;
	$result = $db->query('SELECT search_for FROM '.$db->prefix.'censoring WHERE id='.intval($id)) or error('Unable to fetch censor information', __FILE__, __LINE__, $db->error());
	$t_censor = $db->fetch_assoc($result);
	return $t_censor['search_for'];
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function ForumFromID ($id)
{
	global $db;
	$result = $db->query('SELECT forum_name FROM '.$db->prefix.'forums WHERE id='.intval($id)) or error('Unable to fetch forum information', __FILE__, __LINE__, $db->error());
	$t_forum = $db->fetch_assoc($result);
	return $t_forum['forum_name'];
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function GroupFromID ($id)
{
	global $db;
	$result = $db->query('SELECT g_title FROM '.$db->prefix.'groups WHERE g_id='.intval($id)) or error('Unable to fetch group information', __FILE__, __LINE__, $db->error());
	$t_group = $db->fetch_assoc($result);
	return $t_group['g_title'];
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function RankFromID ($id)
{
	global $db;
	$result = $db->query('SELECT rank FROM '.$db->prefix.'ranks WHERE id='.intval($id)) or error('Unable to fetch rank information', __FILE__, __LINE__, $db->error());
	$t_rank = $db->fetch_assoc($result);
	return $t_rank['rank'];
}

/**
 * Enter description here...
 *
 * @param unknown_type $id
 * @return unknown

 */
function UserFromID ($id)
{
	global $db;
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.intval($id)) or error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
	$t_user = $db->fetch_assoc($result);
	return $t_user['username'];
}

/**
 * Enter description here...
 *
 * @param unknown_type $sql

 */
function LogQuery($sql)
{
	$pages = array('admin_bans.php','admin_categories.php','admin_censoring.php','admin_forums.php','admin_groups.php','admin_options.php','admin_permissions.php','admin_ranks.php','profile.php');
	if (in_array(basename($_SERVER['PHP_SELF']), $pages))
	{
		$sqldata = explode(' ', $sql);
		switch (strtolower($sqldata[0]))
		{
			case "update":
				ProcessUpdates($sql,$sqldata);
				break;
			case "delete":
				ProcessDeletes($sql,$sqldata);
				break;
			case "insert":
				ProcessInserts($sql,$sqldata);
				break;
		}
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $sql
 * @param unknown_type $sqldata

 */
function ProcessUpdates($sql,$sqldata)
{
	global $forum_user;
	$page = basename($_SERVER['PHP_SELF']);
	$loginfo = array();
	switch (strtolower($page))
	{
		case "admin_bans.php":
			if (preg_match("/SET username=(.+), ip=(.+), email=(.+), message=(.+), expire=(.+) WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditBan,$loginfo);
			}
			break;
		case "admin_categories.php":
			if (preg_match("/categories SET cat_name=\'(.+)\', disp_position=(.+) WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditCat,$loginfo);
			}
			break;
		case "admin_censoring.php":
			if (preg_match("/censoring SET search_for=\'(.+)\', replace_with=\'(.+)\' WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditCensor,$loginfo);
			}
			break;
		case "admin_forums.php":
			if (preg_match("/forums SET forum_name=\'(.+)\', forum_desc=(.+), redirect_url=(.+), sort_by=(.+), cat_id=(.+) WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditForum,$loginfo);
			}
			break;
		case "admin_groups.php":
			if (preg_match("/groups SET g_title=\'(.+)\', g_user_title=(.+), g_read_board=(.+), g_post_replies=(.+), g_post_topics=(.+), g_edit_posts=(.+), g_delete_posts=(.+), g_delete_topics=(.+), g_set_title=(.+), g_search=(.+), g_search_users=(.+), g_edit_subjects_interval=(.+), g_post_flood=(.+), g_search_flood=(.+) WHERE g_id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditGroup,$loginfo);
			}
			break;
		case "admin_options.php":
			if (preg_match("/config SET conf_value=(.+) WHERE conf_name=(.+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditOptions,$loginfo);
			}
			break;
		case "admin_permissions.php":
			if (preg_match("/config SET conf_value=(.+) WHERE conf_name=(.+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditPermissions,$loginfo);
			}
			break;
		case "admin_ranks.php":
			if (preg_match("/ranks SET rank=\'(.+)\', min_posts=([0-9]+) WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(EditRank,$loginfo);
			}
			break;
		case "profile.php":
			if (preg_match("/users SET password=\'.+\' WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				if ($loginfo[0] != $forum_user['id'])
					AddLog(EditPassword,$loginfo);
			}
			elseif (preg_match("/users SET group_id=([0-9]+) WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				$userdata = UserFromID($loginfo[1]);
				$loginfo[] = $userdata;
				AddLog(EditUserGroup,$loginfo);
			}
			elseif (preg_match("/users SET timezone=(.+),admin_note=(.+),username=(.+),num_posts=(.+),email=(.+) WHERE id=([0-9]+)/i",$sql, $loginfo)) {
				array_shift($loginfo);
				$id = intval($_GET['id']);
				$userdata = UserFromID($id);
				$loginfo[] = $userdata;
				if ($id != $forum_user['id'])
					AddLog(EditUser,$loginfo);
			}
			elseif (isset($_POST['update_forums'])) {
				if (preg_match("/forums SET moderators=.+ WHERE id=([0-9]+)/i",$sql, $loginfo)) {
					array_shift($loginfo);
					$id = intval($_GET['id']);
					$loginfo[] = $id;
					AddLog(EditForumMods,$loginfo);
				}
			}

			break;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $sql
 * @param unknown_type $sqldata

 */
function ProcessInserts($sql,$sqldata)
{
	$page = basename($_SERVER['PHP_SELF']);
	$loginfo = array();
	switch (strtolower($page))
	{
		case "admin_bans.php":
			if (preg_match("/bans \\(username, ip, email, message, expire\\) VALUES\\((.+), (.+), (.+), (.+), (.+)\\)/i", $sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(AddBan,$loginfo);
			}
			break;
		case "admin_categories.php":
			if (preg_match("/categories \\(cat_name\\) VALUES\\(\'(.+)\'\\)/i", $sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(AddCat,$loginfo);
			}
			break;
		case "admin_censoring.php":
			if (preg_match("/censoring \\(search_for, replace_with\\) VALUES \\(\'(.+)\', \'(.+)\'\\)/i", $sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(AddCensor,$loginfo);
			}
			break;
		case "admin_forums.php":
			if (preg_match("/forums \\(cat_id\\) VALUES\\(([0-9]+)\\)/i", $sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(AddForum,$loginfo);
			}
			break;
		case "admin_groups.php":
			if (preg_match("/groups \\(g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood\\) VALUES\\(\'(.+)\', (.+), (.+), (.+), (.+), (.+), (.+), (.+), (.+), (.+), (.+), (.+), (.+), (.+)\\)/i", $sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(AddGroup,$loginfo);
			}
			break;
		case "admin_ranks.php":
			if (preg_match("/ranks \\(rank, min_posts\\) VALUES\\(\'(.+)\', ([0-9]+)\\)/i", $sql, $loginfo)) {
				array_shift($loginfo);
				AddLog(AddRank,$loginfo);
			}
			break;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $sql
 * @param unknown_type $sqldata

 */
function ProcessDeletes($sql,$sqldata)
{
	$page = basename($_SERVER['PHP_SELF']);
	$loginfo = array();
	$logid = array();
	switch (strtolower($page))
	{
		case "admin_bans.php":
			if (preg_match("/WHERE id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo = BanFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemBan,$loginfo);
			}
			break;
		case "admin_categories.php":
			if (preg_match("/categories WHERE id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo[] = CatFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemCat,$loginfo);
			}
			break;
		case "admin_censoring.php":
			if (preg_match("/censoring WHERE id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo[] = CensorFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemCensor,$loginfo);
			}
			break;
		case "admin_forums.php":
			if (preg_match("/forums WHERE id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo[] = ForumFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemForum,$loginfo);
			}
			break;
		case "admin_groups.php":
			if (preg_match("/groups WHERE g_id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo[] = GroupFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemGroup,$loginfo);
			}
			break;
		case "admin_ranks.php":
			if (preg_match("/ranks WHERE id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo[] = RankFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemRank,$loginfo);
			}
			break;
		case "profile.php":
			if (preg_match("/users WHERE id=([0-9]+)/i",$sql, $logid)) {
				array_shift($logid);
				$loginfo[] = UserFromID($logid[0]);
				$loginfo[] = $logid[0];
				AddLog(RemUser,$loginfo);
			}
			break;
	}
}
?>