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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/post.php';
if(!$configuration['o_pms_enabled'] || $forum_user['is_guest'] || $forum_user['g_pm'] == 0) message($lang_common['No permission']);
if (isset($_POST['form_sent']) || isset($_POST['preview']))
{
	if($forum_user['g_id'] > USER_GUEST)
	{
		$result = $db->query('SELECT posted FROM '.$db->prefix.'messages ORDER BY id DESC LIMIT 1') or error('Unable to fetch message time for flood protection', __FILE__, __LINE__, $db->error());
		if(list($last) = $db->fetch_row($result))
		{
			if((time() - $last) < $forum_user['g_post_flood']) message($lang_pms['Flood start'].' '.$forum_user['g_post_flood'].' '.$lang_pms['Flood end']);
		}
	}
	if (isset($_POST['hide_smilies'])) $smilies = 0;
	else $smilies = 1;
	$subject = forum_trim($_POST['req_subject']);
	if ($subject == '') message($lang_post['No subject']);
	else if (forum_strlen($subject) > 70) message($lang_post['Too long subject']);
	else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($subject) == $subject && $forum_user['g_id'] > USER_GUEST) $subject = ucwords(strtolower($subject));
	$message = forum_linebreaks(forum_trim($_POST['req_message']));
	if ($message == '') message($lang_post['No message']);
	else if (strlen($message) > 65535) message($lang_post['Too long message']);
	else if ($configuration['p_message_all_caps'] == '0' && strtoupper($message) == $message && $forum_user['g_id'] > USER_GUEST) $message = ucwords(strtolower($message));
	if ($configuration['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
	{
		require FORUM_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}
	if (isset($errors)) message($errors[0]);
	if (isset($_POST['preview']))
	{
		require FORUM_ROOT.'header.php';
		require_once FORUM_ROOT.'include/parser.php';
		$hide_smilies = isset($_POST['hide_smilies']) ? 1 : 0;
		$preview_message = parse_message($message, $hide_smilies);
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
<p> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
<?php
		require FORUM_ROOT.'footer.php';    
	}
	elseif (isset($_POST['form_sent']))
	{
	$result = $db->query('SELECT id, username, displayname group_id, email, email_alert FROM '.$db->prefix.'users WHERE id!=1 AND username=\''.addslashes($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());
	if(list($id,$user,$status, $email, $alert) = $db->fetch_row($result))
	{
		if($forum_user['g_pm_limit'] != 0 && $forum_user['g_id'] > USER_GUEST && $status > USER_GUEST)
		{
			$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$id) or error('Unable to get message count for the receiver', __FILE__, __LINE__, $db->error());
			list($count) = $db->fetch_row($result);
			if($count >= $forum_user['g_pm_limit']) message($lang_pms['Inbox full']);
			if(isset($_POST['savemessage']) && intval($_POST['savemessage']) == 1)
			{
				$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$forum_user['id']) or error('Unable to get message count the sender', __FILE__, __LINE__, $db->error());
				list($count) = $db->fetch_row($result);
				if($count >= $forum_user['g_pm_limit']) message($lang_pms['Sent full']);
			}
		}
		$db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted) VALUES(
			\''.$id.'\',
			\''.addslashes($subject).'\',
			\''.addslashes($message).'\',
			\''.addslashes($forum_user['username']).'\',
			\''.$forum_user['id'].'\',
			\''.get_remote_address().'\',
			\''.$smilies.'\',
			\'0\',
			\'0\',
			\''.time().'\'
		)') or error('Unable to send message', __FILE__, __LINE__, $db->error());
		if(isset($_POST['savemessage']))
		{
			$db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted) VALUES(
				\''.$forum_user['id'].'\',
				\''.addslashes($subject).'\',
				\''.addslashes($message).'\',
				\''.addslashes($user).'\',
				\''.$id.'\',
				\''.get_remote_address().'\',
				\''.$smilies.'\',
				\'1\',
				\'1\',
				\''.time().'\'
			)') or error('Unable to send message', __FILE__, __LINE__, $db->error());

		}
		if ($alert)
            {
            	require FORUM_ROOT.'include/email.php';
            	$mail_subject = 'Alert - New Private Message';
            	$mail_message = 'The user \''.$forum_user['username'].'\' from the forum '.$configuration['o_base_url'].' has sent you a private message. '."\n\n".'-- '."\n".'Automatic e-mail'."\n".'(Please do not reply)';
            	forum_mail($email, $mail_subject, $mail_message);
            }
	}
	else
	{
		message($lang_pms['No user']);
	}
	$topic_redirect = intval($_POST['topic_redirect']);
	$from_profile = isset($_POST['from_profile']) ? intval($_POST['from_profile']) : '';
	if($from_profile != 0) redirect(FORUM_ROOT.'profile.php?id='.$from_profile, $lang_pms['Sent redirect']);
	else if($topic_redirect != 0) redirect(FORUM_ROOT.'view_topic.php?id='.$topic_redirect, $lang_pms['Sent redirect']);
	else redirect(FORUM_ROOT.'message_list.php', $lang_pms['Sent redirect']);
	}
}
else
{
	if (isset($_GET['id'])) $id = intval($_GET['id']);
	else $id = 0;
	if($id > 0)
	{
		$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		list($username) = $db->fetch_row($result);
	}
	if(isset($_GET['reply']) || isset($_GET['quote']))
	{
		$r = isset($_GET['reply']) ? intval($_GET['reply']) : 0;
		$q = isset($_GET['quote']) ? intval($_GET['quote']) : 0;
		empty($r) ? $id = $q : $id = $r;
		$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id.' AND owner='.$forum_user['id']) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		$message = $db->fetch_assoc($result);
		if(isset($_GET['quote'])) $quote = '[quote='.$message['sender'].']'.$message['message'].'[/quote]';
		$subject = "RE: " . $message['subject'];
	}
	$action = $lang_pms['Send a message'];
	$form = '<form method="post" id="post" action="message_send.php?action=send" onsubmit="return process_form(this)">';
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$action;
	$form_name = 'post';
	$cur_index = 1;
	if (!isset($username)) $username = '';
	if (!isset($quote)) $quote = '';
	if (!isset($subject)) $subject = '';
	require FORUM_ROOT.'header.php';
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
				<input type="hidden" name="topic_redirect" value="<?php echo isset($_GET['tid']) ? $_GET['tid'] : '' ?>" />
				<input type="hidden" name="topic_redirect" value="<?php echo isset($_POST['from_profile']) ? $_POST['from_profile'] : '' ?>" />
				<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['username']) : 'Guest'; ?>" />
				<label class="conl"><strong><?php echo $lang_pms['Send to'] ?></strong><br /><?php echo '<input type="text" class="textbox" name="req_username" size="25" maxlength="25" value="'.convert_htmlspecialchars($username).'" tabindex="'.($cur_index++).'" />'; ?><br /></label>
				<div class="clearer"></div>
				<label><strong><?php echo $lang_common['Subject'] ?></strong><br /><input class="textbox" type='text' name='req_subject' value='<?php echo $subject ?>' size="60" maxlength="70" tabindex='<?php echo $cur_index++ ?>' /><br /></label>
				<label><strong><?php echo $lang_common['Message'] ?></strong><br />
				<textarea name="req_message" rows="20" cols="95" tabindex="<?php echo $cur_index++ ?>"><?php echo $quote ?></textarea><br /></label>
				<ul class="bblinks">
					<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a>: <?php echo ($configuration['p_message_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
					<li><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a>: <?php echo ($configuration['p_message_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
					<li><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a>: <?php echo ($configuration['o_smilies'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
				</ul>
			</div>
		</fieldset>
<?php
	$checkboxes = array();
	if ($configuration['o_smilies'] == '1') $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['hide_smilies']) ? ' checked="checked"' : '').' />'.$lang_post['Hide smilies'];
	$checkboxes[] = '<label><input type="checkbox" name="savemessage" value="1" checked="checked" tabindex="'.($cur_index++).'" />'.$lang_pms['Save message'];
	if (!empty($checkboxes))
	{
?>
			</div>
			<div class="inform">
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
			<p><input type="button" class="b1" name="submit" OnClick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="submit" value="<?php echo $lang_pms['Send'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /><input type="submit" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
?>