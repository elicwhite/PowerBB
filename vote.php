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

define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/polls.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$pollid = isset($_POST['poll_id']) ? intval($_POST['poll_id']) : 0;
if ($pollid < 1) message($lang_common['Bad request']);
$result = $db->query('SELECT f.id, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.subject, t.closed, poll.ptype, poll.options, poll.voters, poll.votes FROM '.$db->prefix.'polls AS poll RIGHT JOIN '.$db->prefix.'topics AS t ON poll.pollid=t.id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$pollid) or error('Unable to fetch topic and poll info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_poll = $db->fetch_assoc($result);
$mods_array = ($cur_poll['moderators'] != '') ? unserialize($cur_poll['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
if ((((($cur_poll['post_replies'] == '' && $forum_user['g_post_replies'] == '0') || $cur_poll['post_replies'] == '0')) || (isset($cur_poll['closed']) && $cur_poll['closed'] == '1')) && !$is_admmod) message($lang_common['No permission']);
if ($forum_user['is_guest']) message($lang_common['No permission']);
if (isset($_POST['form_sent']))
{
	if ($forum_user['is_guest'] || $_POST['form_user'] != $forum_user['username']) message($lang_common['Bad request']);
	$options = unserialize($cur_poll['options']);
	if (!empty($cur_poll['voters'])) $voters = unserialize($cur_poll['voters']);
	else $voters = array();
	if (!empty($cur_poll['votes'])) $votes = unserialize($cur_poll['votes']);
	else $votes = array();
	$ptype = $cur_poll['ptype'];
	if (in_array($forum_user['id'], $voters)) message($lang_polls['Already voted']);
	if (empty($_POST['null']))
	{
		if ($ptype == 1)
		{
			$myvote = intval(trim($_POST['vote']));
			if ((empty($myvote)) || (!array_key_exists($myvote, $options))) message($lang_common['Bad request']);
			if (isset($votes[$myvote])) $votes[$myvote]++;
			else $votes[$myvote] = 1;
		}
		else if ($ptype == 2) 
		{
			while (list($key, $value) = each($_POST['options'])) 
			{
	            	if (!empty($value) && array_key_exists($key, $options)) 
				{
					if (isset($votes[$key])) $votes[$key]++;
					else $votes[$key] = 1;
	            	} 
	        	}
		}
		else if ($ptype == 3) 
		{
			while (list($key, $value) = each($_POST['options'])) 
			{
	           		if (!empty($value) && array_key_exists($key, $options)) 
				{
					if ($value == "yes")
					{
						if (isset($votes[$key]['yes'])) $votes[$key]['yes']++;
						else $votes[$key]['yes'] = 1;
					}
					else
					{
						if (isset($votes[$key]['no'])) $votes[$key]['no']++;
						else $votes[$key]['no'] = 1;
	            		}
				} 	
	        	}
		}
	      else message($lang_common['Bad request']);
	}
	$voters[] = $forum_user['id'];
	$voters_serialized = serialize($voters);
	$votes_serialized = serialize($votes);
	$db->query('UPDATE '.$db->prefix.'polls SET votes=\''.$votes_serialized.'\', voters=\''.$voters_serialized.'\' WHERE pollid='.$pollid) or error('Unable to update poll', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'view_poll.php?id='.$pollid, $lang_polls['Vote success']);
}
else message($lang_common['Bad request']);