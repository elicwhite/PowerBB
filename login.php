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

ob_start();
if (isset($_GET['action'])) define('QUIET_VISIT', 1);
define('FORUM_ROOT', './');
define('TURN_OFF_MAINT', '1');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/login.php';
$action = isset($_GET['action']) ? $_GET['action'] : null;
if (isset($_POST['form_sent']) && $action == 'in')
{
	$form_username = trim($_POST['req_username']);
	$form_password = trim($_POST['req_password']);
	$result = $db->query('SELECT user_id, ident, color FROM '.$db->prefix.'online WHERE idle=0 ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
	while ($forum_user_online = $db->fetch_assoc($result))
	{
//		if ($forum_user_online['ident'] == $form_username) error("You are already logged in. Clear all cookies if you want to relogin.");
	}
	$username_sql = ($db_type == 'mysql' || $db_type == 'mysqli') ? 'username=\''.$db->escape($form_username).'\'' : 'LOWER(username)=LOWER(\''.$db->escape($form_username).'\')';
	$result = $db->query('SELECT id, group_id, password, save_pass FROM '.$db->prefix.'users WHERE '.$username_sql) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	list($user_id, $group_id, $db_password_hash, $save_pass) = $db->fetch_row($result);
	$authorized = false;
	if (!empty($db_password_hash))
	{
		$sha1_in_db = (strlen($db_password_hash) == 40) ? true : false;
		$sha1_available = (function_exists('sha1') || function_exists('mhash')) ? true : false;
		$form_password_hash = forum_hash($form_password);
		if ($sha1_in_db && $sha1_available && $db_password_hash == $form_password_hash) $authorized = true;
		else if (!$sha1_in_db && $db_password_hash == md5($form_password))
		{
			$authorized = true;
			if ($sha1_available) $db->query('UPDATE '.$db->prefix.'users SET password=\''.$form_password_hash.'\' WHERE id='.$user_id) or error('Unable to update user password', __FILE__, __LINE__, $db->error());
		}
	}
	if (!$authorized) message($lang_login['Wrong user/pass'].' <a href="login.php?action=forget">'.$lang_login['Forgotten pass'].'</a>');
  	if ($group_id == UNVERIFIED)
	{
    		$db->query('UPDATE '.$db->prefix.'users SET group_id='.$configuration['o_default_user_group'].' WHERE id='.$user_id) or error('Unable to update user status', __FILE__, __LINE__, $db->error());
    		$result = $db->query('SELECT g_invitations FROM '.$db->prefix.'groups WHERE g_id='.$configuration['o_default_user_group']) or error('Unable to fetch invitation amount from groups', __FILE__, __LINE__, $db->error());
    		include_once(FORUM_ROOT. 'include/invitation/invitation.php');
    		list($invitations) = $db->fetch_row($result);
    		massInvite($user_id,intval($invitations));
    	}
	$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
	$expire = ($save_pass == '1') ? time() + 31536000 : 0;
	forum_setcookie($user_id, $form_password_hash, $expire);
	redirect($_POST['redirect_url'], $lang_login['Login redirect']);
}
else if ($action == 'out')
{
	if ($forum_user['is_guest'] || !isset($_GET['id']) || $_GET['id'] != $forum_user['id'])
	{
		header('Location: index.php');
		exit;
	}
	$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$forum_user['id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
	if (isset($forum_user['logged'])) $db->query('UPDATE '.$db->prefix.'users SET last_visit='.$forum_user['logged'].', read_topics=NULL WHERE id='.$forum_user['id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
	forum_setcookie(1, random_pass(8), time() + 31536000);
	redirect(FORUM_ROOT.'index.php', $lang_login['Logout redirect']);
}
else if ($action == 'forget' || $action == 'forget_2')
{
	if (!$forum_user['is_guest']) header('Location: index.php');
	if (isset($_POST['form_sent']))
	{
		require FORUM_ROOT.'include/email.php';
		$email = strtolower(trim($_POST['req_email']));
		if (!is_valid_email($email)) message($lang_common['Invalid e-mail']);
		$result = $db->query('SELECT id, username FROM '.$db->prefix.'users WHERE email=\''.$db->escape($email).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$mail_tpl = trim(file_get_contents(FORUM_ROOT.'lang/'.$forum_user['language'].'/mail_templates/activate_password.tpl'));
			$first_crlf = strpos($mail_tpl, "\n");
			$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
			$mail_message = trim(substr($mail_tpl, $first_crlf));
			$mail_message = str_replace('<base_url>', $configuration['o_base_url'].'/', $mail_message);
			$mail_message = str_replace('<board_mailer>', $configuration['o_board_name'].' '.$lang_common['Mailer'], $mail_message);
			while ($cur_hit = $db->fetch_assoc($result))
			{
				$new_password = random_pass(8);
				$new_password_key = random_pass(8);
				$db->query('UPDATE '.$db->prefix.'users SET activate_string=\''.forum_hash($new_password).'\', activate_key=\''.$new_password_key.'\' WHERE id='.$cur_hit['id']) or error('Unable to update activation data', __FILE__, __LINE__, $db->error());
				$cur_mail_message = str_replace('<username>', $cur_hit['username'], $mail_message);
				$cur_mail_message = str_replace('<activation_url>', $configuration['o_base_url'].'/profile.php?id='.$cur_hit['id'].'&action=change_pass&key='.$new_password_key, $cur_mail_message);
				$cur_mail_message = str_replace('<new_password>', $new_password, $cur_mail_message);
				forum_mail($email, $mail_subject, $cur_mail_message);
			}
			message($lang_login['Forget mail'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
		}
		else message($lang_login['No e-mail match'].' '.htmlspecialchars($email).'.');
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_login['Request pass'];
	$required_fields = array('req_email' => $lang_common['E-mail']);
	$focus_element = array('request_pass', 'req_email');
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_login['Request pass'] ?></span></h2>
	<div class="box">
		<form id="request_pass" method="post" action="login.php?action=forget_2" onsubmit="this.request_pass.disabled=true;if(process_form(this)){return true;}else{this.request_pass.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_login['Request pass legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<input id="req_email" type="text" name="req_email" size="50" maxlength="50" />
						<p><?php echo $lang_login['Request pass info'] ?></p>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>" /><input type="submit" name="request_pass" class="b1" value="<?php echo $lang_common['Submit'] ?>" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
if (!$forum_user['is_guest']) header('Location: index.php');
$redirect_url = (isset($_SERVER['HTTP_REFERER']) && preg_match('#^'.preg_quote($configuration['o_base_url']).'/(.*?)\.php#i', $_SERVER['HTTP_REFERER'])) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Login'];
$required_fields = array('req_username' => $lang_common['Username'], 'req_password' => $lang_common['Password']);
$focus_element = array('login', 'req_username');
require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_common['Login'] ?></span></h2>
	<div class="box">
		<form id="login" method="post" action="login.php?action=in" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_login['Login legend'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="redirect_url" value="<?php echo $redirect_url ?>" />
							<label class="conl"><strong><?php echo $lang_common['Username'] ?></strong><br /><input type="text" name="req_username" size="25" maxlength="25" tabindex="1" /><br /></label>
							<label class="conl"><strong><?php echo $lang_common['Password'] ?></strong><br /><input type="password" name="req_password" size="16" maxlength="16" tabindex="2" /><br /></label>
							<p class="clearb"><?php echo $lang_login['Login info'] ?></p>
							<p><a href="register.php" tabindex="4"><?php echo $lang_login['Not registered'] ?></a>&nbsp;&nbsp;
							<a href="login.php?action=forget" tabindex="5"><?php echo $lang_login['Forgotten pass'] ?></a></p>
						</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="login" value="<?php echo $lang_common['Login'] ?>" tabindex="3" /></p>
		</form>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>