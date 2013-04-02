<?php
//----------------------------------------------
// PowerBB
//----------------------------------------------
// All code is copyright to Power Software
// unless mentioned otherwise. This code
// may NOT be reproduced, or distributed
// by any means, unless you have explicit
// written permission from Power Software.
// Some code is derived from early versions
// of PunBB.
//-----------------------------------------------
// Copyright as of 2006
// All rights reserved
//-----------------------------------------------

$show_max_topics = 60;			// The maximum number of topics that will be displayed
$max_subject_length = 30;		// The length at which topic subjects will be truncated (for HTML output)
define('FORUM_ROOT', './');
@include FORUM_ROOT.'config.php';
if (!defined('IN_FORUM')) exit('The file \'config.php\' doesn\'t exist or is corrupt. Please run install.php to install PowerBB Forum first.');
error_reporting(E_ALL ^ E_NOTICE);
set_magic_quotes_runtime(0);
require FORUM_ROOT.'include/functions.php';
require FORUM_ROOT.'include/dblayer/common_db.php';
@include FORUM_ROOT.'cache/cache_config.php';
if (!defined('CONFIG_LOADED'))
{
    require FORUM_ROOT.'include/cache.php';
    generate_config_cache();
    require FORUM_ROOT.'cache/cache_config.php';
}
$result = $db->query('SELECT g_read_board FROM '.$db->prefix.'groups WHERE g_id=3') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
if ($db->result($result) == '0') exit('No permission');
@include FORUM_ROOT.'lang/'.$configuration['o_default_lang'].'/common.php';
if (!isset($lang_common)) exit('There is no valid language pack \''.$configuration['o_default_lang'].'\' installed. Please reinstall a language of that name.');
if (!isset($_GET['action'])) exit('No parameters supplied. See the documentation for instructions.');

function escape_cdata($str)
{
	return str_replace(']]>', ']]&gt;', $str);
}

if ($_GET['action'] == 'active' || $_GET['action'] == 'new')
{
	$order_by = ($_GET['action'] == 'active') ? 't.last_post' : 't.posted';
	$forum_sql = '';
	if (isset($_GET['fid']) && $_GET['fid'] != '')
	{
		$fids = explode(',', trim($_GET['fid']));
		$fids = array_map('intval', $fids);
		if (!empty($fids)) $forum_sql = ' AND f.id IN('.implode(',', $fids).')';
	}
	if (isset($_GET['nfid']) && $_GET['nfid'] != '')
	{
		$nfids = explode(',', trim($_GET['nfid']));
		$nfids = array_map('intval', $nfids);
		if (!empty($nfids)) $forum_sql = ' AND f.id NOT IN('.implode(',', $nfids).')';
	}
	if (isset($_GET['type']) && strtoupper($_GET['type']) == 'RSS')
	{
		$rss_description = ($_GET['action'] == 'active') ? $lang_common['RSS Desc Active'] : $lang_common['RSS Desc New'];
		$url_action = ($_GET['action'] == 'active') ? '&amp;action=new' : '';
		header('Content-Type: text/xml');
		header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		echo '<?xml version="1.0" encoding="'.$lang_common['lang_encoding'].'"?>'."\r\n";
		echo '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">'."\r\n";
		echo '<rss version="0.91">'."\r\n";
		echo '<channel>'."\r\n";
		echo "\t".'<title>'.convert_htmlspecialchars($configuration['o_board_name']).'</title>'."\r\n";
		echo "\t".'<link>'.$configuration['o_base_url'].'/</link>'."\r\n";
		echo "\t".'<description>'.convert_htmlspecialchars($rss_description.' '.$configuration['o_board_name']).'</description>'."\r\n";
		echo "\t".'<language>en-us</language>'."\r\n";
		$result = $db->query('SELECT t.id, t.poster, t.subject, t.posted, t.last_post, t.question, f.id AS fid, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.$order_by.' DESC LIMIT 15') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
		while ($cur_topic = $db->fetch_assoc($result))
		{
			if ($configuration['o_censoring'] == '1') $cur_topic['subject'] = censor_words($cur_topic['subject']);
			echo "\t".'<item>'."\r\n";
			echo "\t\t".'<title>'.convert_htmlspecialchars($cur_topic['subject']).'</title>'."\r\n";
			if (!$cur_topic['question'] == '') echo "\t\t".'<link>'.$configuration['o_base_url'].'/view_poll.php?id='.$cur_topic['id'].$url_action.'</link>'."\r\n";
			else echo "\t\t".'<link>'.$configuration['o_base_url'].'/view_topic.php?id='.$cur_topic['id'].$url_action.'</link>'."\r\n";
			echo "\t\t".'<description><![CDATA['.escape_cdata($lang_common['Forum'].': <a href="'.$configuration['o_base_url'].'/view_forum.php?id='.$cur_topic['fid'].'">'.$cur_topic['forum_name'].'</a><br />'."\r\n".$lang_common['Author'].': '.$cur_topic['poster'].'<br />'."\r\n".$lang_common['Posted'].': '.date('r', $cur_topic['posted']).'<br />'."\r\n".$lang_common['Last post'].': '.date('r', $cur_topic['last_post'])).']]></description>'."\r\n";
			echo "\t".'</item>'."\r\n";
		}
		echo '</channel>'."\r\n";
		echo '</rss>';
	}
	else
	{
		$show = isset($_GET['show']) ? intval($_GET['show']) : 15;
		if ($show < 1 || $show > 50) $show = 15;
		$result = $db->query('SELECT t.id, t.subject, t.question FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.$order_by.' DESC LIMIT '.$show) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
		while ($cur_topic = $db->fetch_assoc($result))
		{
			if ($configuration['o_censoring'] == '1') $cur_topic['subject'] = censor_words($cur_topic['subject']);
			if (forum_strlen($cur_topic['subject']) > $max_subject_length) $subject_truncated = convert_htmlspecialchars(trim(substr($cur_topic['subject'], 0, ($max_subject_length-5)))).' &hellip;';
			else $subject_truncated = convert_htmlspecialchars($cur_topic['subject']);
			if (!$cur_topic['question'] == '') echo '<li><a href="'.$configuration['o_base_url'].'/view_poll.php?id='.$cur_topic['id'].'&amp;action=new" title="'.convert_htmlspecialchars($cur_topic['subject']).'">'.$subject_truncated.'</a></li>'."\n";
			else echo '<li><a href="'.$configuration['o_base_url'].'/view_topic.php?id='.$cur_topic['id'].'&amp;action=new" title="'.convert_htmlspecialchars($cur_topic['subject']).'">'.$subject_truncated.'</a></li>'."\n";
		}
	}
	return;
}
else if ($_GET['action'] == 'online' || $_GET['action'] == 'online_full')
{
	require FORUM_ROOT.'lang/'.$configuration['o_default_lang'].'/index.php';
	$num_guests = $num_users = 0;
	$users = array();
	$result = $db->query('SELECT user_id, ident, color FROM '.$db->prefix.'online WHERE idle=0 group by user_id ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
	while ($forum_user_online = $db->fetch_assoc($result))
	{
		if ($forum_user_online['user_id'] > 1)
		{
			$users[] = '<a href="'.$configuration['o_base_url'].'/profile.php?id='.$forum_user_online['user_id'].'"><span style="color: '.$forum_user_online['color'].'">'.convert_htmlspecialchars($forum_user_online['ident']).'</span></a>';
			++$num_users;
		}
		else ++$num_guests;
	}
	echo $lang_index['Guests online'].': '.$num_guests.'<br />';
	if ($_GET['action'] == 'online_full') echo $lang_index['Users online'].': '.implode(', ', $users).'<br />';
	else echo $lang_index['Users online'].': '.$num_users.'<br />';
	return;
}
else if ($_GET['action'] == 'stats')
{
	require FORUM_ROOT.'lang/'.$configuration['o_default_lang'].'/index.php';
	$result = $db->query('SELECT COUNT(id)-1 FROM '.$db->prefix.'users') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
	$stats['total_users'] = $db->result($result);
	$result = $db->query('SELECT id, username FROM '.$db->prefix.'users ORDER BY registered DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
	$stats['last_user'] = $db->fetch_assoc($result);
	$result = $db->query('SELECT SUM(num_topics), SUM(num_posts) FROM '.$db->prefix.'forums') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
	list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);
	echo $lang_index['No of users'].': '.$stats['total_users'].'<br />';
	echo $lang_index['Newest user'].': <a href="'.$configuration['o_base_url'].'/profile.php?id='.$stats['last_user']['id'].'">'.convert_htmlspecialchars($stats['last_user']['username']).'</a><br />';
	echo $lang_index['No of topics'].': '.$stats['total_topics'].'<br />';
	echo $lang_index['No of posts'].': '.$stats['total_posts'];
	return;
}
else if ($_GET['action'] == 'random_user')
{
	require FORUM_ROOT.'lang/'.$configuration['o_default_lang'].'/index.php';
	$result = $db->query('SELECT id, username, displayname use_avatar FROM '.$db->prefix.'users WHERE id > 1 ORDER BY RAND() DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
	$stats['featured_user'] = $db->fetch_assoc($result);
	echo '<div align="center"><a href="'.$configuration['o_base_url'].'/profile.php?id='.$stats['featured_user']['id'].'">';
	if ($stats['featured_user']['use_avatar'])
	{
		if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$stats['featured_user']['id'].'.gif')) echo '<img src="'.$configuration['o_base_url']. '/'.$configuration['o_avatars_dir'].'/'.$stats['featured_user']['id'].'.gif" '.$img_size[3].' alt="Avatar" />';
		else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$stats['featured_user']['id'].'.jpg')) echo '<img src="'.$configuration['o_base_url']. '/'.$configuration['o_avatars_dir'].'/'.$stats['featured_user']['id'].'.jpg" '.$img_size[3].' alt="Avatar" />';
		else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$stats['featured_user']['id'].'.png')) echo '<img src="'.$configuration['o_base_url']. '/'.$configuration['o_avatars_dir'].'/'.$stats['featured_user']['id'].'.png" '.$img_size[3].' alt="Avatar" />';
	}
	else
	{
		echo '<img src="'.$configuration['o_base_url']. '/'.$configuration['o_avatars_dir'].'/noavatar.gif" '.$img_size[3].' alt="no avatar" />';
	}
	echo '<br /><b>'. $stats['featured_user']['username'].'</a></b></div><br />';
	return;
}
else exit('Bad request');