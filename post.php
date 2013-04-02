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

//----------------------------------------------
// Definitions and includes for required files
//----------------------------------------------
define('FORUM_ROOT', './');
require FORUM_ROOT . 'include/common.php';
require FORUM_ROOT . 'include/modules/mod_image_upload.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/post.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';

//--------------------------------------------
// If a guest is not allowed to view
//--------------------------------------------
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;

//--------------------------------------------
// If topic/forum doesn't exist
//--------------------------------------------
if ($tid < 1 && $fid < 1 || $tid > 0 && $fid > 0) message($lang_common['Bad request']);

//--------------------------------------------
// Forum and topic queries for topic lists
//--------------------------------------------
if ($tid) $result = $db->query('SELECT f.id, f.forum_name, f.moderators, f.redirect_url, f.valide, fp.post_replies, fp.post_topics, fp.image_upload, t.subject, t.closed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$tid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
else $result = $db->query('SELECT f.id, f.forum_name, f.moderators, f.redirect_url, f.valide, fp.post_replies, fp.post_topics, fp.image_upload FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$fid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
//--------------------------------------------
// If specific group...
//--------------------------------------------
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_posting = $db->fetch_assoc($result);
if ($cur_posting['redirect_url'] != '') message($lang_common['Bad request']);
$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
if ((($tid && (($cur_posting['post_replies'] == '' && $forum_user['g_post_replies'] == '0') || $cur_posting['post_replies'] == '0')) || ($fid && (($cur_posting['post_topics'] == '' && $forum_user['g_post_topics'] == '0') || $cur_posting['post_topics'] == '0')) || (isset($cur_posting['closed']) && $cur_posting['closed'] == '1')) && !$is_admmod) message($lang_common['No permission']);
$errors = array();
if (isset($_POST['form_sent']))
{
	if (($forum_user['is_guest'] && $_POST['form_user'] != 'Guest') || (!$forum_user['is_guest'] && $_POST['form_user'] != $forum_user['username'])) message($lang_common['Bad request']);
	if (!$forum_user['is_guest'] && !isset($_POST['preview']) && $forum_user['last_post'] != '' && (time() - $forum_user['last_post']) < $forum_user['g_post_flood']) $errors[] = $lang_post['Flood start'].' '.$forum_user['g_post_flood'].' '.$lang_post['flood end'];
	if ($fid)
	{
		$subject = forum_trim($_POST['req_subject']);
		if ($subject == '') $errors[] = $lang_post['No subject'];
		else if (forum_strlen($subject) > 70) $errors[] = $lang_post['Too long subject'];
		else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($subject) == $subject && $forum_user['g_id'] > USER_MOD) $subject = ucwords(strtolower($subject));
	}
//---------------
// If not guest
//---------------
	if (!$forum_user['is_guest'])
	{
		$username = $forum_user['username'];
$displayname = $forum_user['displayname'];
		$email = $forum_user['email'];
	}
	else
	{
//------------------------------------
// When posting as guest
//------------------------------------
		$username = trim($_POST['req_username']);
		$email = strtolower(trim(($configuration['p_force_guest_email'] == '1') ? $_POST['req_email'] : $_POST['email']));
		if (strlen($username) < 2) $errors[] = $lang_prof_reg['Username too short'];
		else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) $errors[] = $lang_prof_reg['Username guest'];
		else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) $errors[] = $lang_prof_reg['Username IP'];
		if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
			$errors[] = $lang_prof_reg['Username reserved chars'];
		if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username))
			$errors[] = $lang_prof_reg['Username BBCode'];
		$temp = censor_words($username);
		if ($temp != $username)
			$errors[] = $lang_register['Username censor'];
		$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\' OR username=\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$busy = $db->result($result);
			$errors[] = $lang_register['Username dupe 1'].' '.convert_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2'];
		}
		if ($configuration['p_force_guest_email'] == '1' || $email != '')
		{
			require FORUM_ROOT.'include/email.php';
			if (!is_valid_email($email))
				$errors[] = $lang_common['Invalid e-mail'];
		}
	}
//------------------------------------
// Length checks and ability checks
//-------------------------------------
	$message = forum_linebreaks(forum_trim($_POST['req_message']));
	if ($message == '') $errors[] = $lang_post['No message'];
	else if (strlen($message) > 65535) $errors[] = $lang_post['Too long message'];
	
//------------------------------------
// BBCode and settings
//-------------------------------------
	else if ($configuration['p_message_all_caps'] == '0' && strtoupper($message) == $message && $forum_user['g_id'] > USER_MOD) $message = ucwords(strtolower($message));
	if ($configuration['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
	{
		require FORUM_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}
	require FORUM_ROOT.'include/search_idx.php';
	$hide_smilies = isset($_POST['hide_smilies']) ? 1 : 0;
	$subscribe = isset($_POST['subscribe']) ? 1 : 0;
	$now = time();
	if (empty($errors) && !isset($_POST['preview']))
	{
		if ($tid)
		{
		
//------------------------------------
// More of those darn queries
//------------------------------------
			if (!$forum_user['is_guest'])
			{
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_id, poster_ip, message, hide_smilies, posted, topic_id) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', \''.$db->escape($message).'\', \''.$hide_smilies.'\', '.$now.', '.$tid.')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();
				if ($configuration['o_subscriptions'] == '1' && $subscribe)
				{
					$result = $db->query('SELECT 1 FROM '.$db->prefix.'subscriptions WHERE user_id='.$forum_user['id'].' AND topic_id='.$tid) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
					if (!$db->num_rows($result)) $db->query('INSERT INTO '.$db->prefix.'subscriptions (user_id, topic_id) VALUES('.$forum_user['id'].' ,'.$tid.')') or error('Unable to add subscription', __FILE__, __LINE__, $db->error());
				}
			}
			else
			{
				$email_sql = ($configuration['p_force_guest_email'] == '1' || $email != '') ? '\''.$email.'\'' : 'NULL';
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_ip, poster_email, message, hide_smilies, posted, topic_id) VALUES(\''.$db->escape($username).'\', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($message).'\', \''.$hide_smilies.'\', '.$now.', '.$tid.')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();
			}
			$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'posts WHERE topic_id='.$tid) or error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
			$num_replies = $db->result($result, 0) - 1;
			$db->query('UPDATE '.$db->prefix.'topics SET num_replies='.$num_replies.', last_post='.$now.', last_post_id='.$new_pid.', last_poster=\''.$db->escape($username).'\' WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
			update_search_index('post', $new_pid, $message);
			update_forum($cur_posting['id']);
			if ($configuration['o_subscriptions'] == '1')
			{
				$result = $db->query('SELECT posted FROM '.$db->prefix.'posts WHERE topic_id='.$tid.' ORDER BY id DESC LIMIT 1, 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
				$previous_post_time = $db->result($result);
				$result = $db->query('SELECT u.id, u.email, u.notify_with_post, u.language FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'subscriptions AS s ON u.id=s.user_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id='.$cur_posting['id'].' AND fp.group_id=u.group_id) LEFT JOIN '.$db->prefix.'online AS o ON u.id=o.user_id LEFT JOIN '.$db->prefix.'bans AS b ON u.username=b.username WHERE b.username IS NULL AND COALESCE(o.logged, u.last_visit)>'.$previous_post_time.' AND (fp.read_forum IS NULL OR fp.read_forum=1) AND s.topic_id='.$tid.' AND u.id!='.intval($forum_user['id'])) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
				if ($db->num_rows($result))
				{
					require_once FORUM_ROOT.'include/email.php';
					$notification_emails = array();
					while ($cur_subscriber = $db->fetch_assoc($result))
					{
						if (!isset($notification_emails[$cur_subscriber['language']]))
						{
//------------------------------------
// Editing tpl files
//------------------------------------
							if (file_exists(FORUM_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/new_reply.tpl'))
							{
								$mail_tpl = trim(file_get_contents(FORUM_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/new_reply.tpl'));
								$mail_tpl_full = trim(file_get_contents(FORUM_ROOT.'lang/'.$cur_subscriber['language'].'/mail_templates/new_reply_full.tpl'));
								$first_crlf = strpos($mail_tpl, "\n");
								$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
								$mail_message = trim(substr($mail_tpl, $first_crlf));
								$first_crlf = strpos($mail_tpl_full, "\n");
								$mail_subject_full = trim(substr($mail_tpl_full, 8, $first_crlf-8));
								$mail_message_full = trim(substr($mail_tpl_full, $first_crlf));
								$mail_subject = str_replace('<topic_subject>', '\''.$cur_posting['subject'].'\'', $mail_subject);
								$mail_message = str_replace('<topic_subject>', '\''.$cur_posting['subject'].'\'', $mail_message);
								$mail_message = str_replace('<replier>', $username, $mail_message);
								$mail_message = str_replace('<post_url>', $configuration['o_base_url'].'/view_topic.php?pid='.$new_pid.'#p'.$new_pid, $mail_message);
								$mail_message = str_replace('<unsubscribe_url>', $configuration['o_base_url'].'/misc.php?unsubscribe='.$tid, $mail_message);
								$mail_message = str_replace('<board_mailer>', $configuration['o_board_name'].' '.$lang_common['Mailer'], $mail_message);
								$mail_subject_full = str_replace('<topic_subject>', '\''.$cur_posting['subject'].'\'', $mail_subject_full);
								$mail_message_full = str_replace('<topic_subject>', '\''.$cur_posting['subject'].'\'', $mail_message_full);
								$mail_message_full = str_replace('<replier>', $username, $mail_message_full);
								$mail_message_full = str_replace('<message>', $message, $mail_message_full);
								$mail_message_full = str_replace('<post_url>', $configuration['o_base_url'].'/view_topic.php?pid='.$new_pid.'#p'.$new_pid, $mail_message_full);
								$mail_message_full = str_replace('<unsubscribe_url>', $configuration['o_base_url'].'/misc.php?unsubscribe='.$tid, $mail_message_full);
								$mail_message_full = str_replace('<board_mailer>', $configuration['o_board_name'].' '.$lang_common['Mailer'], $mail_message_full);
								$notification_emails[$cur_subscriber['language']][0] = $mail_subject;
								$notification_emails[$cur_subscriber['language']][1] = $mail_message;
								$notification_emails[$cur_subscriber['language']][2] = $mail_subject_full;
								$notification_emails[$cur_subscriber['language']][3] = $mail_message_full;
								$mail_subject = $mail_message = $mail_subject_full = $mail_message_full = null;
							}
						}
						if (isset($notification_emails[$cur_subscriber['language']]))
						{
							if ($cur_subscriber['notify_with_post'] == '0') forum_mail($cur_subscriber['email'], $notification_emails[$cur_subscriber['language']][0], $notification_emails[$cur_subscriber['language']][1]);
							else forum_mail($cur_subscriber['email'], $notification_emails[$cur_subscriber['language']][2], $notification_emails[$cur_subscriber['language']][3]);
						}
					}
				}
			}
		}
		else if ($fid)
		{
//------------------------------------
// If posting validation and queries
//------------------------------------
			if ($cur_posting['valide'] == '1') $closed = '2';
			else $closed = '0';
			$icon_topic = $_POST['icon_topic'];
			$db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, last_poster, closed, forum_id, icon_topic) VALUES(\''.$db->escape($username).'\', \''.$db->escape($subject).'\', '.$now.', '.$now.', \''.$db->escape($username).'\', '.$closed.', '.$fid.', '.$icon_topic.')') or error('Unable to create topic', __FILE__, __LINE__, $db->error());
			$new_tid = $db->insert_id();
			if (!$forum_user['is_guest'])
			{
				if ($configuration['o_subscriptions'] == '1' && (isset($_POST['subscribe']) && $_POST['subscribe'] == '1')) $db->query('INSERT INTO '.$db->prefix.'subscriptions (user_id, topic_id) VALUES('.$forum_user['id'].' ,'.$new_tid.')') or error('Unable to add subscription', __FILE__, __LINE__, $db->error());
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_id, poster_ip, message, hide_smilies, posted, topic_id) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', \''.$db->escape($message).'\', \''.$hide_smilies.'\', '.$now.', '.$new_tid.')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
			}
			else
			{
				$email_sql = ($configuration['p_force_guest_email'] == '1' || $email != '') ? '\''.$email.'\'' : 'NULL';
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_ip, poster_email, message, hide_smilies, posted, topic_id) VALUES(\''.$db->escape($username).'\', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($message).'\', \''.$hide_smilies.'\', '.$now.', '.$new_tid.')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
			}
			$new_pid = $db->insert_id();
			$db->query('UPDATE '.$db->prefix.'topics SET last_post_id='.$new_pid.' WHERE id='.$new_tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
			update_search_index('post', $new_pid, $message, $subject);
			update_forum($fid);
		}
		if (!$forum_user['is_guest'])
		{
			$low_prio = ($db_type == 'mysql') ? 'LOW_PRIORITY ' : '';
			$db->query('UPDATE '.$low_prio.$db->prefix.'users SET num_posts=num_posts+1, last_post='.$now.' WHERE id='.$forum_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());
		}
		$upload_result = process_uploaded_images($new_pid);
		redirect(FORUM_ROOT.'view_topic.php?pid='.$new_pid.'#p'.$new_pid, $upload_result.$lang_post['Post redirect']);
	}
}
if ($tid)
{
	$action = $lang_post['Post a reply'];
	$form = '<form name="spelling_mod" id="post" method="post" action="post.php?action=post&amp;tid='.$tid.'" onsubmit="this.submit.disabled=true;if(submitForm(this)){return true;}else{this.submit.disabled=false;return false;}"  enctype="multipart/form-data">';
	if (isset($_GET['qid']))
	{
		$qid = intval($_GET['qid']);
		if ($qid < 1) message($lang_common['Bad request']);
		$result = $db->query('SELECT poster, message FROM '.$db->prefix.'posts WHERE id='.$qid.' AND topic_id='.$tid) or error('Unable to fetch quote info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		list($q_poster, $q_message) = $db->fetch_row($result);
		$q_message = str_replace('[img]', '[url]', $q_message);
		$q_message = str_replace('[/img]', '[/url]', $q_message);
		$q_message = convert_htmlspecialchars($q_message);
		if ($configuration['p_message_bbcode'] == '1')
		{
			if (strpos($q_poster, '[') !== false || strpos($q_poster, ']') !== false)
			{
				if (strpos($q_poster, '\'') !== false) $q_poster = '"'.$q_poster.'"';
				else $q_poster = '\''.$q_poster.'\'';
			}
			else
			{
//------------------------------------
// Quote formatting
//------------------------------------
				$ends = substr($q_poster, 0, 1).substr($q_poster, -1, 1);
				if ($ends == '\'\'') $q_poster = '"'.$q_poster.'"';
				else if ($ends == '""') $q_poster = '\''.$q_poster.'\'';
			}
			$quote = '[quote='.$q_poster.']'.$q_message.'[/quote]'."\n";
		}
		else $quote = '> '.$q_poster.' '.$lang_common['wrote'].':'."\n\n".'> '.$q_message."\n";
	}
	$forum_name = '<a href="view_forum.php?id='.$cur_posting['id'].'">'.convert_htmlspecialchars($cur_posting['forum_name']).'</a>';
}
else if ($fid)
{
	$action = $lang_post['Post new topic'];
	$form = '<form name="spelling_mod" id="post" method="post" action="post.php?action=post&amp;fid='.$fid.'" onsubmit="return submitForm(this)" enctype="multipart/form-data">';
	$forum_name = convert_htmlspecialchars($cur_posting['forum_name']);
}
else message($lang_common['Bad request']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$action;
$required_fields = array('req_email' => $lang_common['E-mail'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('post');
if (!$forum_user['is_guest']) $focus_element[] = ($fid) ? 'req_subject' : 'req_message';
else
{
	$required_fields['req_username'] = $lang_post['Guest name'];
	$focus_element[] = 'req_username';
}
//------------------------------------
// Beggining of viewable page
//------------------------------------
require FORUM_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo $forum_name ?><?php if (isset($cur_posting['subject'])) echo '</li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_posting['subject']) ?></li></ul>
	</div>
</div>
<?php
if (!empty($errors))
{
//------------------------------------
// If errors...
//------------------------------------
?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul>
<?php
	while (list(, $cur_error) = each($errors)) echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>
<?php
}
//------------------------------------
// If...edit
//------------------------------------
else if (isset($_POST['preview']))
{
	require_once FORUM_ROOT.'include/parser.php';
	$preview_message = parse_message($message, $hide_smilies);
//------------------------------------
// Preview html code
//------------------------------------
?>
<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_post['Post preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postright">
				<div class="postmsg">
					<?php echo $preview_message."\n" ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}
$cur_index = 1;

//------------------------------------
// Post HTML CODE !Important Part!
//------------------------------------
?>
<div class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form."\n" ?>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Write message legend'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['username']) : 'Guest'; ?>" />
<?php
if ($forum_user['is_guest'])
{
	$email_label = ($configuration['p_force_guest_email'] == '1') ? '<strong>'.$lang_common['E-mail'].'</strong>' : $lang_common['E-mail'];
	$email_form_name = ($configuration['p_force_guest_email'] == '1') ? 'req_email' : 'email';
?>						<label class="conl">
							<strong>
								<?php echo $lang_post['Guest name'] ?>
							</strong>
							<br />
							<input type="text" name="req_username" value="<?php if (isset($_POST['req_username'])) echo convert_htmlspecialchars($username); ?>" size="25" maxlength="25" tabindex="<?php echo $cur_index++ ?>" />
							<br />
						</label>
						<label class="conl">
							<?php echo $email_label ?>
							<br />
							<input type="text" name="<?php echo $email_form_name ?>" value="<?php if (isset($_POST[$email_form_name])) echo convert_htmlspecialchars($email); ?>" size="50" maxlength="50" tabindex="<?php echo $cur_index++ ?>" />
							<br />
						</label>
						<div class="clearer"></div>
<?php
}
//------------------------------------
// Smiley stuff
//------------------------------------
if ($fid):
	$icons_topic = array();
	$d = dir(FORUM_ROOT.'img/general/icons');
	while (($entry = $d->read()) !== false)
	{
		if (substr($entry, strlen($entry)-4) == '.gif')
            {    
			$icons_topic[] = substr($entry, 0, strlen($entry)-4);
            }    
	}
	$d->close();
	if (count($icons_topic) > 1)
	{
		echo 'Please select an icon for the topic.<br />';
		while (list(, $temp) = @each($icons_topic))
		{
			echo '<input type="radio" name="icon_topic" value="'.$temp.'" />&nbsp;<img src="./img/general/icons/'.$temp.'.gif" alt="'.$temp.'" />&nbsp;';
		}
		echo '<input type="radio" name="icon_topic" value="0" checked="checked" />&nbsp;None';
	}
//------------------------------------
// More post html stuff
//------------------------------------
?>
						<label>
							<strong>
								<?php echo $lang_common['Subject'] ?>
							</strong>
							<br />
							<input type="text" class="textbox" name="req_subject" value="<?php if (isset($_POST['req_subject'])) echo convert_htmlspecialchars($subject); ?>" size="70" maxlength="70" tabindex="<?php echo $cur_index++ ?>" />
							<br />
						</label>
<?php endif; require FORUM_ROOT.'include/modules/mod_bbcode.php'; ?>						<label><strong><?php echo $lang_common['Message'] ?></strong><br />
						<textarea name="req_message" class="post" rows="20" style="width:500px" tabindex="<?php echo $cur_index++ ?>">
<?php 
	if (isset($_POST['req_message']))
	{
		echo convert_htmlspecialchars($message);
	}
	elseif(isset($quote))
	{
		echo $quote;
	}
	else
	{
	echo '';
	}
?></textarea><br /></label>
					</div>
				</fieldset>
<?php
$checkboxes = array();
//------------------------------------
// what to show...if guest.
//------------------------------------
if (!$forum_user['is_guest'])
{
	if ($configuration['o_smilies'] == '1') $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['hide_smilies']) ? ' checked="checked"' : '').' />'.$lang_post['Hide smilies'];
	if ($configuration['o_subscriptions'] == '1') $checkboxes[] = '<label><input type="checkbox" name="subscribe" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['subscribe']) ? ' checked="checked"' : '').' />'.$lang_post['Subscribe'];
}
else if ($configuration['o_smilies'] == '1') $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['hide_smilies']) ? ' checked="checked"' : '').' />'.$lang_post['Hide smilies'];
if (!empty($checkboxes))
{
?>
			</div>
	<div id="div_imageshack" style="display: none;">
            <div class="inform">
                <fieldset>
                    <legend>Upload Images using ImageShack(tm)</legend>
                    <div class="infldset">
                        <div class="rbox">
                        <iframe src="http://imageshack.us/iframe.php?txtcolor=111111&type=blank&size=30" scrolling="no" allowtransparency="true" frameborder="0" width="280" height="80">Update your browser for ImageShack.us!</iframe><br /><br />
                        Note: To include image on post, use the form above and after it is uploaded, choose option "<b>Hotlink for forums (1)</b>, copy and paste inside message box.
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
			<div class="inform">
<?php
				show_image_upload($cur_posting);
?>
				<fieldset>
					<legend><?php echo $lang_common['Options'] ?></legend>
					<div class="infldset">
						<div class="rbox">
							<?php echo implode('<br /></label>'."\n\t\t\t\t", $checkboxes).'<br /></label>'."\n" ?>
						</div>
					</div>
				</fieldset>
<?php
}
?>
			</div>
			<p>
<?php
//------------------------------------
// Final Buttons
//------------------------------------
?>
	<input type="button" class="b1" name="submit" OnClick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /><input class="b1" type="submit" name="preview" onclick="ClearUploadSlots();" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /></p>
		</form>
	</div>
</div>
<?php
if ($tid && $configuration['o_topic_review'] != '0')
{
	require_once FORUM_ROOT.'include/parser.php';
	$result = $db->query('SELECT p.poster, p.message, p.hide_smilies, p.posted, u.username, u.displayname FROM '.$db->prefix.'posts as p INNER JOIN '.$db->prefix.'users as u on u.username=p.poster WHERE p.topic_id='.$tid.' ORDER BY p.id DESC LIMIT '.$configuration['o_topic_review']) or error('Unable to fetch topic review', __FILE__, __LINE__, $db->error());
?>
<div id="postreview" class="blockpost">
	<h2><span><?php echo $lang_post['Topic review'] ?></span></h2>
<?php
	$bg_switch = true;
	$post_count = 0;
	while ($cur_post = $db->fetch_assoc($result))
	{
		if ($cur_post['displayname'] != "") $cur_post['poster'] = $cur_post['displayname'];
		$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
		$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
		$post_count++;
		$cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']);
?>
	<div class="box<?php echo $vtbg ?>">
		<div class="inbox">
			<div class="postleft">
				<dl>
					<dt><strong><?php echo convert_htmlspecialchars($cur_post['poster']) ?></strong></dt>
					<dd><?php echo format_time($cur_post['posted']) ?></dd>
				</dl>
			</div>
			<div class="postright">
				<div class="postmsg">
					<?php echo $cur_post['message'] ?>
				</div>
			</div>
			<div class="clearer"></div>
		</div>
	</div>
<?php
	}
?>
</div>
<?php
}
require FORUM_ROOT.'footer.php';
?>