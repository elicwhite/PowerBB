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

if (isset($_GET['action'])) define('QUIET_VISIT', 1);
define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/misc.php';
$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($action == 'rules')
{
	require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_register['Forum rules'];
	require FORUM_ROOT.'header.php';
?>
<div class="block">
	<h2><span><?php echo $lang_register['Forum rules'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $configuration['o_rules_message'] ?></p>
		</div>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if ($action == 'markread')
{
	if ($forum_user['is_guest']) message($lang_common['No permission']);
	$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$forum_user['logged'].', read_topics=NULL WHERE id='.$forum_user['id']) or error('Unable to update user last visit data', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'index.php', $lang_misc['Mark read redirect']);
}
else if ($action == 'markforumread')
{
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	if ($forum_user['is_guest']) message($lang_common['No permission']);
	if ($id < 1) message($lang_common['Bad request']);
	$forum_user['read_topics']['f'][$id] = time();
	$db->query('UPDATE '.$db->prefix.'users SET read_topics=\''.$db->escape(serialize($forum_user['read_topics'])).'\' WHERE id='.$forum_user['id']) or error('Unable to update read-topic data', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'view_forum.php?id='.$id, $lang_misc['Mark forum read redirect']);
}
else if (isset($_GET['email']))
{
	if ($forum_user['is_guest']) message($lang_common['No permission']);
	$recipient_id = intval($_GET['email']);
	if ($recipient_id < 2) message($lang_common['Bad request']);
	$result = $db->query('SELECT username, email, email_setting FROM '.$db->prefix.'users WHERE id='.$recipient_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result)) message($lang_common['Bad request']);
	list($recipient, $recipient_email, $email_setting) = $db->fetch_row($result);
	if ($email_setting == 2 && $forum_user['g_id'] > USER_MOD) message($lang_misc['Form e-mail disabled']);
	if (isset($_POST['form_sent']))
	{
		$subject = forum_trim($_POST['req_subject']);
		$message = forum_trim($_POST['req_message']);
		if ($subject == '') message($lang_misc['No e-mail subject']);
		else if ($message == '') message($lang_misc['No e-mail message']);
		else if (strlen($message) > 65535) message($lang_misc['Too long e-mail message']);
		$mail_tpl = trim(file_get_contents(FORUM_ROOT.'lang/'.$forum_user['language'].'/mail_templates/form_email.tpl'));
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = trim(substr($mail_tpl, $first_crlf));
		$mail_subject = str_replace('<mail_subject>', $subject, $mail_subject);
		$mail_message = str_replace('<sender>', $forum_user['username'], $mail_message);
		$mail_message = str_replace('<board_title>', $configuration['o_board_name'], $mail_message);
		$mail_message = str_replace('<mail_message>', $message, $mail_message);
		$mail_message = str_replace('<board_mailer>', $configuration['o_board_name'].' '.$lang_common['Mailer'], $mail_message);
		require_once FORUM_ROOT.'include/email.php';
		forum_mail($recipient_email, $mail_subject, $mail_message, '"'.str_replace('"', '', $forum_user['username']).'" <'.$forum_user['email'].'>');
		redirect($_POST['redirect_url'], $lang_misc['E-mail sent redirect']);
	}
	$redirect_url = (isset($_SERVER['HTTP_REFERER']) && preg_match('#^'.preg_quote($configuration['o_base_url']).'/(.*?)\.php#i', $_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'index.php';
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_misc['Send e-mail to'].' '.convert_htmlspecialchars($recipient);
	$required_fields = array('req_subject' => $lang_misc['E-mail subject'], 'req_message' => $lang_misc['E-mail message']);
	$focus_element = array('email', 'req_subject');
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_misc['Send e-mail to'] ?> <?php echo convert_htmlspecialchars($recipient) ?></span></h2>
	<div class="box">
		<form id="email" method="post" action="misc.php?email=<?php echo $recipient_id ?>" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_misc['Write e-mail'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="redirect_url" value="<?php echo $redirect_url ?>" />
						<label><strong><?php echo $lang_misc['E-mail subject'] ?></strong><br />
						<input class="textbox" type="text" name="req_subject" size="70" maxlength="70" tabindex="1" /><br /></label>
						<label><strong><?php echo $lang_misc['E-mail message'] ?></strong><br />
						<textarea name="req_message" rows="10" cols="75" tabindex="2"></textarea><br /></label>
						<p><?php echo $lang_misc['E-mail disclosure note'] ?></p>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input class="b1" type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="3" accesskey="s" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if (isset($_GET['report']))
{
	if ($forum_user['is_guest']) message($lang_common['No permission']);
	$post_id = intval($_GET['report']);
	if ($post_id < 1) message($lang_common['Bad request']);
	if (isset($_POST['form_sent']))
	{
		$reason = forum_linebreaks(forum_trim($_POST['req_reason']));
		if ($reason == '') message($lang_misc['No reason']);
		$result = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$post_id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		$topic_id = $db->result($result);
		$result = $db->query('SELECT subject, forum_id FROM '.$db->prefix.'topics WHERE id='.$topic_id) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		list($subject, $forum_id) = $db->fetch_row($result);
		if ($configuration['o_report_method'] == 0 || $configuration['o_report_method'] == 2) $db->query('INSERT INTO '.$db->prefix.'reports (post_id, topic_id, forum_id, reported_by, created, message) VALUES('.$post_id.', '.$topic_id.', '.$forum_id.', '.$forum_user['id'].', '.time().', \''.$db->escape($reason).'\')' ) or error('Unable to create report', __FILE__, __LINE__, $db->error());
		if ($configuration['o_report_method'] == 1 || $configuration['o_report_method'] == 2)
		{
			if ($configuration['o_mailing_list'] != '')
			{
				$mail_subject = 'Report('.$forum_id.') - \''.$subject.'\'';
				$mail_message = 'User \''.$forum_user['username'].'\' has reported the following message:'."\n".$configuration['o_base_url'].'/view_topic.php?pid='.$post_id.'#p'.$post_id."\n\n".'Reason:'."\n".$reason;
				require FORUM_ROOT.'include/email.php';
				forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
			}
		}
		redirect(FORUM_ROOT.'view_topic.php?pid='.$post_id.'#p'.$post_id, $lang_misc['Report redirect']);
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_misc['Report post'];
	$required_fields = array('req_reason' => $lang_misc['Reason']);
	$focus_element = array('report', 'req_reason');
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_misc['Report post'] ?></span></h2>
	<div class="box">
		<form id="report" method="post" action="misc.php?report=<?php echo $post_id ?>" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_misc['Reason desc'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<label><strong><?php echo $lang_misc['Reason'] ?></strong><br /><textarea name="req_reason" rows="5" cols="60"></textarea><br /></label>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="submit" value="<?php echo $lang_common['Submit'] ?>" accesskey="s" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if (isset($_GET['subscribe']))
{
	if ($forum_user['is_guest'] || $configuration['o_subscriptions'] != '1') message($lang_common['No permission']);
	$topic_id = intval($_GET['subscribe']);
	if ($topic_id < 1) message($lang_common['Bad request']);
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'subscriptions WHERE user_id='.$forum_user['id'].' AND topic_id='.$topic_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) message($lang_misc['Already subscribed']);
	$db->query('INSERT INTO '.$db->prefix.'subscriptions (user_id, topic_id) VALUES('.$forum_user['id'].' ,'.$topic_id.')') or error('Unable to add subscription', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'view_topic.php?id='.$topic_id, $lang_misc['Subscribe redirect']);
}
else if (isset($_GET['unsubscribe']))
{
	if ($forum_user['is_guest'] || $configuration['o_subscriptions'] != '1') message($lang_common['No permission']);
	$topic_id = intval($_GET['unsubscribe']);
	if ($topic_id < 1) message($lang_common['Bad request']);
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'subscriptions WHERE user_id='.$forum_user['id'].' AND topic_id='.$topic_id) or error('Unable to fetch subscription info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result)) message($lang_misc['Not subscribed']);
	$db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE user_id='.$forum_user['id'].' AND topic_id='.$topic_id) or error('Unable to remove subscription', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'view_topic.php?id='.$topic_id, $lang_misc['Unsubscribe redirect']);
}
else message($lang_common['Bad request']);