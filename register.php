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
if (!$forum_user['is_guest'])
{
	header('Location: index.php');
	exit;
}
require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
if(isset($_REQUEST['code'])) $invitation_code = substr($_REQUEST['code'],0,32);
elseif(isset($_REQUEST['invite'])) $invitation_code = substr($_REQUEST['invite'],0,32);
else  $invitation_code = '';
if ($configuration['o_regs_allow'] == '0') message($lang_register['No new regs']);
if (isset($_GET['cancel'])) redirect(FORUM_ROOT.'index.php', $lang_register['Reg cancel redirect']);
else if ($configuration['o_rules'] == '1' && !isset($_GET['agree']) && !isset($_POST['form_sent']))
{
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_register['Register'];
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_register['Forum rules'] ?></span></h2>
	<div class="box">
		<form method="get" action="register.php">
		<input type="hidden" name="code" value="<? echo $invitation_code ?>" />
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Rules legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $configuration['o_rules_message'] ?></p>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="agree" value="<?php echo $lang_register['Agree'] ?>" /><input class="b1" type="submit" name="cancel" value="<?php echo $lang_register['Cancel'] ?>" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if (isset($_POST['form_sent']))
{
//	confirm_referrer('register.php');
	$username = forum_trim($_POST['req_username']);
	$email1 = strtolower(trim($_POST['req_email1']));
	if ($configuration['o_regs_verify'] == '1')
	{
		$email2 = strtolower(trim($_POST['req_email2']));
		$password1 = random_pass(8);
		$password2 = $password1;
	}
	else
	{
		$password1 = trim($_POST['req_password1']);
		$password2 = trim($_POST['req_password2']);
	}
	$username = preg_replace('#\s+#s', ' ', $username);
	if (strlen($username) < 2) message($lang_prof_reg['Username too short']);
	else if (forum_strlen($username) > 25) message($lang_common['Bad request']);
	else if (strlen($password1) < 4) message($lang_prof_reg['Pass too short']);
	else if ($password1 != $password2) message($lang_prof_reg['Pass not match']);
	else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) message($lang_prof_reg['Username guest']);
	else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) message($lang_prof_reg['Username IP']);
	else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false) message($lang_prof_reg['Username reserved chars']);
	else if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) message($lang_prof_reg['Username BBCode']);
	if ($configuration['o_censoring'] == '1')
	{
		if (censor_words($username) != $username) message($lang_register['Username censor']);
	}
	if ($configuration['o_regs_verify_image'] == '1')
	{
		session_start();
		if (trim($_POST['req_image']) == '') message($lang_register['Text mismatch']);
		if (strtolower(trim($_POST['req_image'])) != strtolower($_SESSION['text'])) message($lang_register['Text mismatch']);
		
	}
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE UPPER(username)=UPPER(\''.$db->escape($username).'\') OR UPPER(username)=UPPER(\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\')') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		$busy = $db->result($result);
		message($lang_register['Username dupe 1'].' '.convert_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2']);
	}
	require FORUM_ROOT.'include/email.php';
	if (!is_valid_email($email1)) message($lang_common['Invalid e-mail']);
	else if ($configuration['o_regs_verify'] == '1' && $email1 != $email2) message($lang_register['E-mail not match']);
	if (is_banned_email($email1))
	{
		if ($configuration['p_allow_banned_email'] == '0') message($lang_prof_reg['Banned e-mail']);
		$banned_email = true;
	}
	else
		$banned_email = false;
	$dupe_list = array();
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE email=\''.$email1.'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		if ($configuration['p_allow_dupe_email'] == '0') message($lang_prof_reg['Dupe e-mail']);
		while ($cur_dupe = $db->fetch_assoc($result)) $dupe_list[] = $cur_dupe['username'];
	}
	if($_POST['code'] != '')
	{
	  $code = forum_trim($_POST['code']);
	  include_once(FORUM_ROOT ."include/modules/mod_invitation_db.php");
	  $inviter = checkInvitation($email1,$code);
	  if(!is_numeric($inviter))  error('Unable to verify invitation'.$inviter, __FILE__, __LINE__, $inviter);
	}
	else $inviter = 0;
	$timezone = intval($_POST['timezone']);
	$language = isset($_POST['language']) ? $_POST['language'] : $configuration['o_default_lang'];
	$save_pass = (!isset($_POST['save_pass']) || $_POST['save_pass'] != '1') ? '0' : '1';
	$email_setting = intval($_POST['email_setting']);
	if ($email_setting < 0 || $email_setting > 2) $email_setting = 1;
	$now = time();
	$intial_group_id = ($configuration['o_regs_verify'] == '0') ? $configuration['o_default_user_group'] : UNVERIFIED;
	$password_hash = forum_hash($password1);
	$db->query('INSERT INTO '.$db->prefix.'users (username, group_id, password, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit,invitedby) VALUES(\''.$db->escape($username).'\', '.$intial_group_id.', \''.$password_hash.'\', \''.$email1.'\', '.$email_setting.', '.$save_pass.', '.$timezone.' , \''.$db->escape($language).'\', \''.$configuration['o_default_style'].'\', '.$now.', \''.get_remote_address().'\', '.$now.','.$inviter.')') or error('Unable to create user', __FILE__, __LINE__, $db->error());
	$result = $db->query('SELECT g_invitations FROM '.$db->prefix.'groups WHERE g_id='.$intial_group_id) or error('Unable to fetch invitation amount from groups', __FILE__, __LINE__, $db->error());
	include_once(FORUM_ROOT. 'include/modules/mod_invitation.php');
	list($invitations) = $db->fetch_row($result);
	massInvite($new_uid,intval($invitations));
	$new_uid = $db->insert_id();
	if ($banned_email && $configuration['o_mailing_list'] != '')
	{
		$mail_subject = 'Alert - Banned e-mail detected';
		$mail_message = 'User \''.$username.'\' registered with banned e-mail address: '.$email1."\n\n".'User profile: '.$configuration['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';
		forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
	}
	if (!empty($dupe_list) && $configuration['o_mailing_list'] != '')
	{
		$mail_subject = 'Alert - Duplicate e-mail detected';
		$mail_message = 'User \''.$username.'\' registered with an e-mail address that also belongs to: '.implode(', ', $dupe_list)."\n\n".'User profile: '.$configuration['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';
		forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
	}
	if ($configuration['o_regs_report'] == '1')
	{
		$mail_subject = 'Alert - New registration';
		$mail_message = 'User \''.$username.'\' registered in the forums at '.$configuration['o_base_url']."\n\n".'User profile: '.$configuration['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';
		forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
	}
	if(isset($_COOKIE["forumreferrer"]))
	{
		$referral_id = $_COOKIE["forumreferrer"];
		$result = $db->query('SELECT referral_count FROM '.$db->prefix.'users WHERE id='.$referral_id) or error('Invalid Member ID', __FILE__, __LINE__, $db->error());
		list($referral_val) = $db->fetch_row($result);
		$rval = $referral_val[0] + 1;
		$db->query('UPDATE '.$db->prefix.'users SET referral_count='. $rval . ' WHERE id='.$referral_id) or error('Invalid Member ID', __FILE__, __LINE__, $db->error());
	}
	if ($configuration['o_regs_verify'] == '1')
	{
		$mail_tpl = trim(file_get_contents(FORUM_ROOT.'lang/'.$forum_user['language'].'/mail_templates/welcome.tpl'));
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = trim(substr($mail_tpl, $first_crlf));
		$mail_subject = str_replace('<board_title>', $configuration['o_board_name'], $mail_subject);
		$mail_message = str_replace('<base_url>', $configuration['o_base_url'].'/', $mail_message);
		$mail_message = str_replace('<username>', $username, $mail_message);
		$mail_message = str_replace('<password>', $password1, $mail_message);
		$mail_message = str_replace('<login_url>', $configuration['o_base_url'].'/login.php', $mail_message);
		$mail_message = str_replace('<board_mailer>', $configuration['o_board_name'].' '.$lang_common['Mailer'], $mail_message);
		forum_mail($email1, $mail_subject, $mail_message);
		message($lang_register['Reg e-mail'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.', true);
	}
	forum_setcookie($new_uid, $password_hash, ($save_pass != '0') ? $now + 31536000 : 0);
	redirect(FORUM_ROOT.'index.php', $lang_register['Reg complete']);
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_register['Register'];
$required_fields = array('req_username' => $lang_common['Username'], 'req_password1' => $lang_common['Password'], 'req_password2' => $lang_prof_reg['Confirm pass'], 'req_email1' => $lang_common['E-mail'], 'req_email2' => $lang_common['E-mail'].' 2');
$focus_element = array('register', 'req_username');
require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_register['Register'] ?></span></h2>
	<div class="box">
		<form id="register" method="post" action="register.php?action=register" onsubmit="this.register.disabled=true;if(process_form(this)){return true;}else{this.register.disabled=false;return false;}">
			<div class="inform">
				<div class="forminfo">
					<h3><?php echo $lang_common['Important information'] ?></h3>
					<p><?php echo $lang_register['Desc 1'] ?></p>
					<p><?php echo $lang_register['Desc 2'] ?></p>
				</div>
				<fieldset>
					<legend><?php echo $lang_register['Username legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="code" value="<? echo $invitation_code ?>" />
						<input type="hidden" name="form_sent" value="1" />
						<label><strong><?php echo $lang_common['Username'] ?></strong><br /><input type="text" name="req_username" size="30" maxlength="25" /><br /></label>
					</div>
				</fieldset>
			</div>
<?php if ($configuration['o_regs_verify'] == '0'): ?>			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Pass legend 1'] ?></legend>
					<div class="infldset">
						<label class="conl"><strong><?php echo $lang_common['Password'] ?></strong><br /><input type="password" name="req_password1" size="16" maxlength="16" /><br /></label>
						<label class="conl"><strong><?php echo $lang_prof_reg['Confirm pass'] ?></strong><br /><input type="password" name="req_password2" size="16" maxlength="16" /><br /></label>
						<p class="clearb"><?php echo $lang_register['Pass info'] ?></p>
					</div>
				</fieldset>
			</div>
<?php endif; ?>			<div class="inform">
<?php if ($configuration['o_regs_verify_image'] == '1'): ?>			
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Image verification'] ?></legend>
					<div class="infldset">
						<img src="<? echo FORUM_ROOT.'include/modules/mod_image_validation.php';?>" /><br />
						<label class="conl"><strong><?php echo $lang_register['Image text'] ?></strong><br /><input type="text" name="req_image" size="16" maxlength="16" /><br /></label>
						<p class="clearb"><?php echo $lang_register['Image info'] ?></p>
					</div>
				</fieldset>
			</div>
<?php endif; ?>
				<fieldset>
					<legend><?php echo ($configuration['o_regs_verify'] == '1') ? $lang_prof_reg['E-mail legend 2'] : $lang_prof_reg['E-mail legend'] ?></legend>
					<div class="infldset">
<?php if ($configuration['o_regs_verify'] == '1'): ?>			<p><?php echo $lang_register['E-mail info'] ?></p>
<?php endif; ?>					<label><strong><?php echo $lang_common['E-mail'] ?></strong><br />
						<input type="text" name="req_email1" size="30" maxlength="50" /><br /></label>
<?php if ($configuration['o_regs_verify'] == '1'): ?>						<label><strong><?php echo $lang_register['Confirm e-mail'] ?></strong><br />
						<input type="text" name="req_email2" size="30" maxlength="50" /><br /></label>
<?php endif; ?>					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_prof_reg['Localisation legend'] ?></legend>
					<div class="infldset">
	    <label><?php echo $lang_prof_reg['Timezone info'] ?>
            <br /><br /><?php echo $lang_prof_reg['Timezone'] ?>: <select id="time_zone" name="timezone">
                    <option value="-12"<?php if ($configuration['o_server_timezone'] == -12) echo ' selected="selected"' ?>>(GMT-12:00) International Date West</option>
                    <option value="-11"<?php if ($configuration['o_server_timezone'] == -11) echo ' selected="selected"' ?>>(GMT-11:00) Midway Island, Samoa</option>
                    <option value="-10"<?php if ($configuration['o_server_timezone'] == -10) echo ' selected="selected"' ?>>(GMT-10:00) Hawaii</option>
                    <option value="-9"<?php if ($configuration['o_server_timezone'] == -9) echo ' selected="selected"' ?>>(GMT-09:00) Alaska</option>
                    <option value="-8"<?php if ($configuration['o_server_timezone'] == -8) echo ' selected="selected"' ?>>(GMT-08:00) Pacific</option>
                    <option value="-7"<?php if ($configuration['o_server_timezone'] == -7) echo ' selected="selected"' ?>>(GMT-07:00) Mountain</option>
                    <option value="-6"<?php if ($configuration['o_server_timezone'] == -6) echo ' selected="selected"' ?>>(GMT-06:00) Central</option>
                    <option value="-5"<?php if ($configuration['o_server_timezone'] == -5) echo ' selected="selected"' ?>>(GMT-05:00) Eastern</option>
                    <option value="-4"<?php if ($configuration['o_server_timezone'] == -4) echo ' selected="selected"' ?>>(GMT-04:00) Atlantic</option>
                    <option value="-3.5"<?php if ($configuration['o_server_timezone'] == -3.5) echo ' selected="selected"' ?>>(GMT-03:30) Newfoundland</option>
                    <option value="-3"<?php if ($configuration['o_server_timezone'] == -3) echo ' selected="selected"' ?>>(GMT-03:00) Brazil, Buenos Aires</option>
                    <option value="-2"<?php if ($configuration['o_server_timezone'] == -2) echo ' selected="selected"' ?>>(GMT-02:00) Mid-Atlantic</option>
                    <option value="-1"<?php if ($configuration['o_server_timezone'] == -1) echo ' selected="selected"' ?>>(GMT-01:00) Azores</option>
                    <option value="0"<?php if ($configuration['o_server_timezone'] == 0) echo ' selected="selected"' ?>>(GMT-00:00) Greenwich, West. Europe</option>
                    <option value="1"<?php if ($configuration['o_server_timezone'] == 1) echo ' selected="selected"' ?>>(GMT+01:00) Central European</option>
                    <option value="2"<?php if ($configuration['o_server_timezone'] == 2) echo ' selected="selected"' ?>>(GMT+02:00) Eastern European</option>
                    <option value="3"<?php if ($configuration['o_server_timezone'] == 3) echo ' selected="selected"' ?>>(GMT+03:00) Moscow, Baghdad</option>
                    <option value="3.5"<?php if ($configuration['o_server_timezone'] == 3.5) echo ' selected="selected"' ?>>(GMT+03:30) Iran</option>
                    <option value="4"<?php if ($configuration['o_server_timezone'] == 4) echo ' selected="selected"' ?>>(GMT+04:00) Abu Dhabi, Dubai</option>
                    <option value="4.5"<?php if ($configuration['o_server_timezone'] == 4.5) echo ' selected="selected"' ?>>(GMT+04:30) Kabul</option>
                    <option value="5"<?php if ($configuration['o_server_timezone'] == 5) echo ' selected="selected"' ?>>(GMT+05:00) Islamabad, Karachi</option>
                    <option value="5.5"<?php if ($configuration['o_server_timezone'] == 5.5) echo ' selected="selected"' ?>>(GMT+05:30) India</option>
                    <option value="5.75"<?php if ($configuration['o_server_timezone'] == 5.75) echo ' selected="selected"' ?>>(GMT+05:45) Kathmandu</option>
                    <option value="6"<?php if ($configuration['o_server_timezone'] == 6) echo ' selected="selected"' ?>>(GMT+06:00) Astana, Dhaka</option>
                    <option value="6.5"<?php if ($configuration['o_server_timezone'] == 6.5) echo ' selected="selected"' ?>>(GMT+06:30) Rangoon</option>
                    <option value="7"<?php if ($configuration['o_server_timezone'] == 7) echo ' selected="selected"' ?>>(GMT+07:00) Bangkok, Jakarta</option>
                    <option value="8"<?php if ($configuration['o_server_timezone'] == 8) echo ' selected="selected"' ?>>(GMT+08:00) Western Australia</option>
                    <option value="9"<?php if ($configuration['o_server_timezone'] == 9) echo ' selected="selected"' ?>>(GMT+09:00) Japan, Korea</option>
                    <option value="9.5"<?php if ($configuration['o_server_timezone'] == 9.5) echo ' selected="selected"' ?>>(GMT+09:30) Central Austrailia</option>
                    <option value="10"<?php if ($configuration['o_server_timezone'] == 10) echo ' selected="selected"' ?>>(GMT+10:00) Eastern Austrailia</option>
                    <option value="11"<?php if ($configuration['o_server_timezone'] == 11) echo ' selected="selected"' ?>>(GMT+11:00) Magadan, Solomon Is.</option>
                    <option value="12"<?php if ($configuration['o_server_timezone'] == 12) echo ' selected="selected"' ?>>(GMT+12:00) New Zealand, Fiji</option>
                    <option value="12.75"<?php if ($configuration['o_server_timezone'] == 12.75) echo ' selected="selected"' ?>>(GMT+12:45) Chatam Island, NZ</option>
                    <option value="13"<?php if ($configuration['o_server_timezone'] == 13) echo ' selected="selected"' ?>>(GMT+13:00) Tonga, Phoenix Islands</option>
                    <option value="14"<?php if ($configuration['o_server_timezone'] == 14) echo ' selected="selected"' ?>>(GMT+14:00) Christmas Islands</option>
              </select>
						<br /></label>
<?php
		$languages = array();
		$d = dir(FORUM_ROOT.'lang');
		while (($entry = $d->read()) !== false)
		{
			if ($entry != '.' && $entry != '..' && is_dir(FORUM_ROOT.'lang/'.$entry) && file_exists(FORUM_ROOT.'lang/'.$entry.'/common.php')) $languages[] = $entry;
		}
		$d->close();
		if (count($languages) > 1)
		{
?>
							<label><?php echo $lang_prof_reg['Language'] ?>: <?php echo $lang_prof_reg['Language info'] ?>
							<br /><select name="language">
<?php
			while (list(, $temp) = @each($languages))
			{
				if ($configuration['o_default_lang'] == $temp) echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
				else echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
			}
?>
							</select>
							<br /></label>
<?php
		}
?>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_prof_reg['Privacy options legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $lang_prof_reg['E-mail setting info'] ?></p>
						<div class="rbox">
							<label><input type="radio" name="email_setting" value="0" /><?php echo $lang_prof_reg['E-mail setting 1'] ?><br /></label>
							<label><input type="radio" name="email_setting" value="1" checked="checked" /><?php echo $lang_prof_reg['E-mail setting 2'] ?><br /></label>
							<label><input type="radio" name="email_setting" value="2" /><?php echo $lang_prof_reg['E-mail setting 3'] ?><br /></label>
						</div>
						<p><?php echo $lang_prof_reg['Save user/pass info'] ?></p>
						<div class="rbox">
							<label><input type="checkbox" name="save_pass" value="1" checked="checked" /><?php echo $lang_prof_reg['Save user/pass'] ?><br /></label>
						</div>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="register" value="<?php echo $lang_register['Register'] ?>" /></p>
		</form>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>