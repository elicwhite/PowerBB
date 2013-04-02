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

define('FORUM_ROOT', '');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/digests.php';
require_once FORUM_ROOT.'include/email.php';
$ban_list = array();
foreach ($forum_bans as $cur_ban)
{
	$ban_list[] = strtolower($cur_ban['username']);
}
$today = getdate();
$mday = $today['wday'];
$current_hour = $today['hours'];
$weekly_digest_text = ($mday == $configuration['o_weekly_digest_day'] ) ? " or (digest_type = 'WEEK')" : "";
$sql = "SELECT s.user_id, u.username, u.email as user_email, u.last_visit as user_lastvisit, from_unixtime(u.last_visit, '%d %b %Y %h:%m %p') as 'Last Visited', s.digest_type, s.show_text, s.show_mine, s.new_only, s.send_on_no_messages, s.text_length FROM ".$db->prefix."digest_subscriptions s, ".$db->prefix."users u WHERE s.user_id = u.id AND ((s.digest_type = 'DAY')" . $weekly_digest_text . ")"; 
$result = $db->query($sql) or error(mysql_error(), __FILE__, __LINE__, $db->error());
$digests_sent = 0;
while ($row = $db->fetch_assoc($result)) 
{
	if (in_array(strtolower($row['username']), $ban_list))
	{
		echo $row['username']." at ".$row['user_email']." is currently banned and was skipped.\n";
	}
	else
	{
		$sql5 = "SELECT u.username, u.id, u.realname, u.language, u.timezone, g.g_id, g.g_user_title FROM " . $db->prefix."users AS u LEFT JOIN " . $db->prefix."groups AS g ON g.g_id=u.group_id WHERE u.id=" . $row['user_id'] . "";
		$result5 = $db->query($sql5) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result5)) message($lang_common['Bad request']);
		$user = $db->fetch_assoc($result5);
		if ($row['new_only'] == 'TRUE')
		{
			$sql3 = 'SELECT max(posted) AS last_post_date FROM '.$db->prefix.'posts WHERE poster_id = ' . $row['user_id'];
			$result3 = $db->query($sql3) or error('Unable to select last post date for user.', __FILE__, __LINE__, $db->error());
			$row3 = $db->fetch_assoc($result3);
			$last_post_date = ($row3['last_post_date'] <> '') ? $row3['last_post_date'] : 0;
			$sql3 = 'SELECT max(logged) AS last_session_date FROM ' . $db->prefix.'online WHERE user_id = ' . $row['user_id'];
			$result3 = $db->query($sql3) or error('Unable to get last session date for user.', __FILE__, __LINE__, $db->error());
			$row3 = $db->fetch_assoc($result3);
			$last_session_date = ($row3['last_session_date'] <> '') ? $row3['last_session_date'] : 0;
			$last_visited_date = $row['user_lastvisit'];
			if ($last_visited_date == '')
			{
				$last_visited_date = 0;
			}
			$last_visited_date = max($last_post_date, $last_session_date, $last_visited_date);
		}	
		$sql3 = "SELECT s.forum_id FROM ".$db->prefix."digest_subscribed_forums AS s, " . $db->prefix."forums AS f, " . $db->prefix."categories AS c WHERE	s.user_id = ".$user['id']." AND s.forum_id = f.id AND f.cat_id = c.id ORDER BY c.disp_position, f.disp_position";
		$result3 = $db->query($sql3) or error(mysql_error(), __FILE__, __LINE__, $db->error());
		$queried_forums = array();
		while ($row3 = $db->fetch_assoc($result3)) 
		{
			array_push($queried_forums,$row3['forum_id']);
		}
		$forum_list = array();
		$forum_list = implode(',',$queried_forums);
		$show_text = ($row['show_text'] == 'YES') ? true: false; 
		$show_mine = ($row['show_mine'] == 'YES') ? true: false; 
		$period = ($row['digest_type'] == 'DAY') ? "1 DAY" : "7 DAY";
		if ($row['new_only'] == 'TRUE') 
		{
			$code = 'GREATEST(UNIX_TIMESTAMP(DATE_SUB(CURRENT_DATE, INTERVAL ' . $period . ')), ' . $last_visited_date . ')'; 
		}        
		else 
		{
			$code = 'UNIX_TIMESTAMP(DATE_SUB(CURRENT_DATE, INTERVAL ' . $period . '))'; 
		}
		if ($show_mine == false)
		{
			$code .= ' and p.poster_id <> ' . $row['user_id'];
		}
		$diff = ($user['timezone'] - $configuration['o_server_timezone']) * 3600;
		$sql2 = "SELECT c.cat_name AS cat_title, f.forum_name, t.subject AS topic_title, u.username AS 'Posted by', p.posted AS post_time, from_unixtime((p.posted + " . $diff . "), '%a %b %d %Y, %h:%i %p') AS 'Display Time', if(length(p.message)<=" . $row['text_length'] . ",p.message,concat(substring(p.message,1," . $row['text_length'] . ") ,'...')) AS 'Post Text', p.id AS post_id, t.id AS topic_id, f.id AS forum_id FROM " . $db->prefix."posts AS p, " . $db->prefix."topics AS t, " . $db->prefix."forums AS f, " . $db->prefix."users AS u, " . $db->prefix."categories AS c WHERE p.topic_id = t.id AND t.forum_id = f.id AND p.poster_id = u.id AND f.cat_id = c.id AND p.posted > " . $code . " AND f.id IN (" . $forum_list . ") ORDER BY c.disp_position, f.disp_position, t.subject, p.posted"; 
		$result2 = $db->query($sql2) or error('Unable to retrieve message summary for user', __FILE__, __LINE__, $db->error());
		$last_forum = '';
		$last_topic = '';
		$msg        = '';
		$msg_count  = 0;
		while ($row2 = $db->fetch_assoc($result2))
		{
			if ($row2['forum_name'] <> $last_forum) 
			{ 
				$msg .= "\r\n\r\n" . $configuration['o_forum_email_divider'] . "\r\n". $lang_digests['forum'] . $row2['forum_name'] . "\r\n" . $configuration['o_base_url'].'/view_forum.php?id=' . $row2['forum_id'] . "\r\n";
			}
			if ($row2['topic_title'] <> $last_topic) 
			{
				$msg .= "\r\n" . $configuration['o_topic_email_divider'] . "\r\n". $lang_digests['topic'] . $row2['topic_title'] . "\r\n" . $configuration['o_base_url'].'/view_topic.php?id=' . $row2['topic_id'] . "\r\n";
			}
			$msg .= "\r\n" . $configuration['o_message_email_divider'] . "\r\n". $lang_digests['posted_by'] . ' ' . $row2['Posted by']  . ' - ' . $row2['Display Time'] . "\r\n" . $configuration['o_base_url'].'/view_topic.php?id=' . $row2['topic_id'] . "#p" . $row2['post_id'] . "\r\n";
			if ($show_text) 
			{
				if (strlen($row2['Post Text']) < ($row['text_length'] + 3)) 
				{
					$msg .= "\r\n" . preg_replace('/\[\S+\]/', '', $row2['Post Text']) . "\r\n";
				}
				else
				{
					$msg .= "\r\n" . $lang_digests['message_excerpt'] . preg_replace('/\[\S+\]/', '', $row2['Post Text']) . "\r\n";
				}
				$msg .= "\r\n"; 
			}
			if ($row2['forum_name'] <> $last_forum)
			{ 
				$last_forum = $row2['forum_name'];
			}
			if ($row2['topic_title'] <> $last_topic)
			{ 
				$last_topic = $row2['topic_title'];
			}
			$msg_count++;
		}
		if ($msg_count == 0)
		{
			$msg .= "\r\n\r\n" . $lang_digests['no_new_messages'] . "\r\n\r\n";
		}
		if ($msg_count > 0 || $row['send_on_no_messages'] == 'YES')
		{
			if (file_exists(FORUM_ROOT.'lang/'.$user['language'].'/mail_templates/digests_email.tpl'))
			{
				$timestamp = getdate();
				$diff = ($user['timezone'] - $configuration['o_server_timezone']) * 3600;
				$digest_timestamp = date("D M j Y H:i A",$timestamp[0]+$diff);
				$mail_to = $row['username'] . ' <' . $row['user_email'] . '>';
				$mail_tpl = trim(file_get_contents(FORUM_ROOT.'lang/'.$user['language'].'/mail_templates/digests_email.tpl'));
				$first_crlf = strpos($mail_tpl, "\n");
				$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
				$mail_message = trim(substr($mail_tpl, $first_crlf));
				$mail_subject = str_replace('<board_mailer>', $configuration['o_board_name'], $mail_subject);
				$mail_message = str_replace('<board_mailer>', $configuration['o_board_name'], $mail_message);
				$mail_message = str_replace('<digest_introduction>', $lang_digests['digest_introduction'], $mail_message);
				$mail_message = str_replace('<unsubscribe_url>', $lang_digests['unsubscribe_url'], $mail_message);
				$mail_message = str_replace('<digest_disclaimer>', $lang_digests['digest_disclaimer'], $mail_message);
				$mail_message = str_replace('<current_datetime>', $digest_timestamp, $mail_message);
				$mail_message = str_replace('<digest_contents>', $msg, $mail_message);
				forum_mail($mail_to, $mail_subject, $mail_message);
				$extra_s = ($msg_count == 1) ? "" : "s";
				echo 'A digest containing ' . $msg_count . ' post'. $extra_s .' was sent to ' . $row['username'] . ' at ' . $row['user_email']. "\r\n";
				$digests_sent++;
				$mail_subject = $mail_message = null;
			}
		}
	}
}
$hours = ($today['hours'] < 10) ? "0".$today['hours'] : $today['hours'];
$minutes = ($today['minutes'] < 10) ? "0".$today['minutes'] : $today['minutes'];
$seconds = ($today['seconds'] < 10) ? "0".$today['seconds'] : $today['seconds'];
echo 'Digests Sent: ' . $digests_sent . "\r\n";
echo 'Server Date: ' . $today['mon'] . '/' . $today['mday'] . '/' . $today['year'] . "\r\n";
echo 'Server Time: ' . $hours . ':' .  $minutes . ':' .  $seconds . "\r\n";
echo 'Server Time Zone: ' . date('Z')/3600 . ' or ' . date('T') . "\r\n";
echo "----\r\n";
?>