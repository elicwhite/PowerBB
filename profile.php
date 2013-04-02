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
require FORUM_ROOT.'include/modules/mod_image_upload.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/profile.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/invitation.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/calendar.php';
$action = isset($_GET['action']) ? $_GET['action'] : null;
$section = isset($_GET['section']) ? $_GET['section'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 2) message($lang_common['Bad request']);
if (($forum_user['g_read_board'] == '0' || ($forum_user['g_view_users'] == 0 && $forum_user['id'] != $id)) && ($action != 'change_pass' || !isset($_GET['key']))) message($lang_common['No view']);
if ($action == 'change_pass')
{
	if (isset($_GET['key']))
	{
		if (!$forum_user['is_guest'])
		{
			header('Location: index.php');
			exit;
		}
		$key = $_GET['key'];
		$result = $db->query('SELECT activate_string, activate_key FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch new password', __FILE__, __LINE__, $db->error());
		list($new_password_hash, $new_password_key) = $db->fetch_row($result);
		if ($key == '' || $key != $new_password_key) message($lang_profile['Pass key bad'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
		else
		{
			$db->query('UPDATE '.$db->prefix.'users SET password=\''.$new_password_hash.'\', activate_string=NULL, activate_key=NULL WHERE id='.$id) or error('Unable to update password', __FILE__, __LINE__, $db->error());
			message($lang_profile['Pass updated'], true);
		}
	}
	if ($forum_user['id'] != $id)
	{
		if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
		else if ($forum_user['g_id'] == USER_MOD)
		{
			$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if (!$db->num_rows($result)) message($lang_common['Bad request']);
			if ($configuration['p_mod_edit_users'] == '0' || $configuration['p_mod_change_passwords'] == '0' || $db->result($result) < USER_GUEST) message($lang_common['No permission']);
		}
	}
	if (isset($_POST['form_sent']))
	{
		$old_password = isset($_POST['req_old_password']) ? trim($_POST['req_old_password']) : '';
		$new_password1 = trim($_POST['req_new_password1']);
		$new_password2 = trim($_POST['req_new_password2']);
		if ($new_password1 != $new_password2) message($lang_prof_reg['Pass not match']);
		if (strlen($new_password1) < 4) message($lang_prof_reg['Pass too short']);
		$result = $db->query('SELECT password, save_pass FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch password', __FILE__, __LINE__, $db->error());
		list($db_password_hash, $save_pass) = $db->fetch_row($result);
		$authorized = false;
		if (!empty($db_password_hash))
		{
			$sha1_in_db = (strlen($db_password_hash) == 40) ? true : false;
			$sha1_available = (function_exists('sha1') || function_exists('mhash')) ? true : false;
			$old_password_hash = forum_hash($old_password);
			if (($sha1_in_db && $sha1_available && $db_password_hash == $old_password_hash) || (!$sha1_in_db && $db_password_hash == md5($old_password)) || $forum_user['g_id'] < USER_GUEST) $authorized = true;
		}
		if (!$authorized) message($lang_profile['Wrong pass']);
		$new_password_hash = forum_hash($new_password1);
		$db->query('UPDATE '.$db->prefix.'users SET password=\''.$new_password_hash.'\' WHERE id='.$id) or error('Unable to update password', __FILE__, __LINE__, $db->error());
		if ($forum_user['id'] == $id)
		{
			$expire = ($save_pass == '1') ? time() + 31536000 : 0;
			forum_setcookie($forum_user['id'], $new_password_hash, $expire);
		}
		redirect(FORUM_ROOT.'profile.php?section=essentials&amp;id='.$id, $lang_profile['Pass updated redirect']);
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
	$required_fields = array('req_old_password' => $lang_profile['Old pass'], 'req_new_password1' => $lang_profile['New pass'], 'req_new_password2' => $lang_profile['Confirm new pass']);
	$focus_element = array('change_pass', (($forum_user['g_id'] > USER_MOD) ? 'req_old_password' : 'req_new_password1'));
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Change pass'] ?></span></h2>
	<div class="box">
		<form id="change_pass" method="post" action="profile.php?action=change_pass&amp;id=<?php echo $id ?>" onsubmit="return process_form(this)">
			<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
				<fieldset>
					<legend><?php echo $lang_profile['Change pass legend'] ?></legend>
					<div class="infldset">
<?php if ($forum_user['g_id'] > USER_MOD): ?>						<label><strong><?php echo $lang_profile['Old pass'] ?></strong><br />
						<input type="password" name="req_old_password" size="16" maxlength="16" /><br /></label>
<?php endif; ?>						<label class="conl"><strong><?php echo $lang_profile['New pass'] ?></strong><br />
						<input type="password" name="req_new_password1" size="16" maxlength="16" /><br /></label>
						<label class="conl"><strong><?php echo $lang_profile['Confirm new pass'] ?></strong><br />
						<input type="password" name="req_new_password2" size="16" maxlength="16" /><br /></label>
						<div class="clearb"></div>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" OnClick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if ($action == 'change_email')
{
	if ($forum_user['id'] != $id)
	{
		if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
		else if ($forum_user['g_id'] == USER_MOD)
		{
			$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if (!$db->num_rows($result)) message($lang_common['Bad request']);
			if ($configuration['p_mod_edit_users'] == '0' || $db->result($result) < USER_GUEST) message($lang_common['No permission']);
		}
	}
	if (isset($_GET['key']))
	{
		$key = $_GET['key'];
		$result = $db->query('SELECT activate_string, activate_key FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch activation data', __FILE__, __LINE__, $db->error());
		list($new_email, $new_email_key) = $db->fetch_row($result);
		if ($key != $new_email_key) message($lang_profile['E-mail key bad'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
		else
		{
			$db->query('UPDATE '.$db->prefix.'users SET email=activate_string, activate_string=NULL, activate_key=NULL WHERE id='.$id) or error('Unable to update e-mail address', __FILE__, __LINE__, $db->error());
			message($lang_profile['E-mail updated'], true);
		}
	}
	else if (isset($_POST['form_sent']))
	{
		if (forum_hash($_POST['req_password']) !== $forum_user['password']) message($lang_profile['Wrong pass']);
		require FORUM_ROOT.'include/email.php';
		$new_email = strtolower(trim($_POST['req_new_email']));
		if (!is_valid_email($new_email)) message($lang_common['Invalid e-mail']);
		if (is_banned_email($new_email))
		{
			if ($configuration['p_allow_banned_email'] == '0') message($lang_prof_reg['Banned e-mail']);
			else if ($configuration['o_mailing_list'] != '')
			{
				$mail_subject = 'Alert - Banned e-mail detected';
				$mail_message = 'User \''.$forum_user['username'].'\' changed to banned e-mail address: '.$new_email."\n\n".'User profile: '.$configuration['o_base_url'].'/profile.php?id='.$id."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';
				forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
			}
		}
		$result = $db->query('SELECT id, username FROM '.$db->prefix.'users WHERE email=\''.$db->escape($new_email).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			if ($configuration['p_allow_dupe_email'] == '0') message($lang_prof_reg['Dupe e-mail']);
			else if ($configuration['o_mailing_list'] != '')
			{
				while ($cur_dupe = $db->fetch_assoc($result)) $dupe_list[] = $cur_dupe['username'];
				$mail_subject = 'Alert - Duplicate e-mail detected';
				$mail_message = 'User \''.$forum_user['username'].'\' changed to an e-mail address that also belongs to: '.implode(', ', $dupe_list)."\n\n".'User profile: '.$configuration['o_base_url'].'/profile.php?id='.$id."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';
				forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
			}
		}
		$new_email_key = random_pass(8);
		$db->query('UPDATE '.$db->prefix.'users SET activate_string=\''.$db->escape($new_email).'\', activate_key=\''.$new_email_key.'\' WHERE id='.$id) or error('Unable to update activation data', __FILE__, __LINE__, $db->error());
		$mail_tpl = trim(file_get_contents(FORUM_ROOT.'lang/'.$forum_user['language'].'/mail_templates/activate_email.tpl'));
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = trim(substr($mail_tpl, $first_crlf));
		$mail_message = str_replace('<username>', $forum_user['username'], $mail_message);
		$mail_message = str_replace('<base_url>', $configuration['o_base_url'], $mail_message);
		$mail_message = str_replace('<activation_url>', $configuration['o_base_url'].'/profile.php?action=change_email&id='.$id.'&key='.$new_email_key, $mail_message);
		$mail_message = str_replace('<board_mailer>', $configuration['o_board_name'].' '.$lang_common['Mailer'], $mail_message);
		forum_mail($new_email, $mail_subject, $mail_message);
		message($lang_profile['Activate e-mail sent'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.', true);
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
	$required_fields = array('req_new_email' => $lang_profile['New e-mail'], 'req_password' => $lang_common['Password']);
	$focus_element = array('change_email', 'req_new_email');
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Change e-mail'] ?></span></h2>
	<div class="box">
		<form id="change_email" method="post" action="profile.php?action=change_email&amp;id=<?php echo $id ?>" id="change_email" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_profile['E-mail legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<label><strong><?php echo $lang_profile['New e-mail'] ?></strong><br /><input type="text" class="textbox" name="req_new_email" size="50" maxlength="50" /><br /></label>
						<label><strong><?php echo $lang_common['Password'] ?></strong><br /><input type="password" name="req_password" size="16" maxlength="16" /><br /></label>
						<p><?php echo $lang_profile['E-mail instructions'] ?></p>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="new_email" value="<?php echo $lang_common['Submit'] ?>" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if ($action == 'avatar_gallery')
{
	if (isset($_POST['form_sent']))
	{
		if(empty($_POST['PIA'])) message($lang_profile['Avatar_Error']);
		$ext = explode('.',$_POST['PIA']);
		$ext = $ext[1];
		$file = "img/pre_avatars/".$_POST['category']."/".$_POST['PIA'];
		$new_file = $configuration['o_avatars_dir'].'/'.$id.'.'.$ext;
		@unlink($configuration['o_avatars_dir'].'/'.$id.'.gif');
		@unlink($configuration['o_avatars_dir'].'/'.$id.'.jpg');
		@unlink($configuration['o_avatars_dir'].'/'.$id.'.png');
		$temp = copy($file, $new_file);
		chmod($new_file, 0644);
		if (!$temp) message($lang_profile['Move failed'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
		$db->query('UPDATE '.$db->prefix.'users SET use_avatar=1 WHERE id='.$id) or error('Unable to update avatar state', __FILE__, __LINE__, $db->error());
		redirect(FORUM_ROOT.'profile.php?section=personality&id='.$id, $lang_profile['Avatar upload redirect']);
	}
	else
	{
		$category = (isset($_POST['category']))? $_POST['category']: "Community Pack";
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'].' / '.$lang_profile['Avatar_gallery'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('personality');

?>
<div class="blockform">
	<div class="box">
		<h2><span><?php echo $category?></span></h2>
		<form method="post" action="profile.php?action=avatar_gallery&amp;id=<?php echo $id ?>" id="copy_av" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend>Select Avatar</legend>
					<div class="infldset">

						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="category" value="<?php echo $category?>" />
						<table cellspacing="0">
						<tbody>
							<tr>
<?
						$rows = "5";
						$row = "0";
						$dir = dir('img/pre_avatars/'.$category.'/');
						while ($entry = $dir->read())
						{
							if (substr($entry, strlen($entry)-4) == '.jpg'||substr($entry, strlen($entry)-4) == '.gif'||substr($entry, strlen($entry)-4) == '.png'){
								$name = substr($entry, 0, strlen($entry)-4);
								if($row == $rows)
								{
									echo"\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t<tr>\n";
									echo"\t\t\t\t\t\t\t\t<td valign='bottom'>\n\t\t\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t\t\t<img src='img/pre_avatars/".$category."/".$entry."' alt='' /><br />\n\t\t\t\t\t\t\t\t\t\t<input type='radio' name='PIA' value='".$entry."' />".$name."\n\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t</td>\n";
									$row="1";
								}
								else
								{
									echo"\t\t\t\t\t\t\t\t<td valign='bottom'>\n\t\t\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t\t\t<img src='img/pre_avatars/".$category."/".$entry."' alt='' /><br />\n\t\t\t\t\t\t\t\t\t\t<input type='radio' name='PIA' value='".$entry."' />".$name."\n\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t</td>\n";
									$row++;
								}
							}
						}
						$dir->close();
						if ($row != $rows){
							while($row < $rows)
							{
								echo"\t\t\t\t\t\t\t\t<td> &nbsp; </td>\n";	
								$row++;
							}	
						}
?>
							</tr>
						</tbody>
						</table>
						<input type="submit" class="b1" name="upload" value="<?php echo $lang_profile['Upload'] ?>" />
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div><br />
<div class="blockform">
	<div class="box">
		<form id="change_avatar" method="post" action="profile.php?action=avatar_gallery&amp;id=<?php echo $id ?>">
			<div class="inform">
				<fieldset>
					<legend>Change Gallery</legend>	
					<div class="infldset">
						<select name='category'>
<?
						$dir = dir('img/pre_avatars/');
						while ($entry =$dir->read())
						{
							$select = ($category == $entry)?' selected="selected"': NULL;
							if($entry != '.' && $entry != '..') echo "\t\t\t\t\t\t\t<option value='".$entry."'".$select.">".$entry."</option>\n";
						}
						$dir->close();
?>						</select><input type="submit" value="&nbsp;Go!&nbsp;" name="submit" />
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div></div>
<?
		require FORUM_ROOT.'footer.php';
	}
}
else if ($action == 'upload_avatar' || $action == 'upload_avatar2')
{
	if ($configuration['o_avatars'] == '0') message($lang_profile['Avatars disabled']);
	if ($forum_user['id'] != $id && $forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
	if (isset($_POST['form_sent']))
	{
		if (!isset($_FILES['req_file'])) message($lang_profile['No file']);
		$uploaded_file = $_FILES['req_file'];
		if (isset($uploaded_file['error']))
		{
			switch ($uploaded_file['error'])
			{
				case 1:
				case 2:
					message($lang_profile['Too large ini']);
					break;

				case 3:
					message($lang_profile['Partial upload']);
					break;

				case 4:
					message($lang_profile['No file']);
					break;

				case 6:
					message($lang_profile['No tmp directory']);
					break;

				default:
					if ($uploaded_file['size'] == 0)
						message($lang_profile['No file']);
					break;
			}
		}
		if (is_uploaded_file($uploaded_file['tmp_name']))
		{
			$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
			if (!in_array($uploaded_file['type'], $allowed_types)) message($lang_profile['Bad type']);
			if ($uploaded_file['size'] > $configuration['o_avatars_size']) message($lang_profile['Too large'].' '.$configuration['o_avatars_size'].' '.$lang_profile['bytes'].'.');
			$extensions = null;
			if ($uploaded_file['type'] == 'image/gif') $extensions = array('.gif', '.jpg', '.png');
			else if ($uploaded_file['type'] == 'image/jpeg' || $uploaded_file['type'] == 'image/pjpeg') $extensions = array('.jpg', '.gif', '.png');
			else $extensions = array('.png', '.gif', '.jpg');
			if (!@move_uploaded_file($uploaded_file['tmp_name'], $configuration['o_avatars_dir'].'/'.$id.'.tmp')) message($lang_profile['Move failed'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
			list($width, $height, $type,) = getimagesize($configuration['o_avatars_dir'].'/'.$id.'.tmp');
			if (empty($width) || empty($height) || $width > $configuration['o_avatars_width'] || $height > $configuration['o_avatars_height'])
			{
				@unlink($configuration['o_avatars_dir'].'/'.$id.'.tmp');
				message($lang_profile['Too wide or high'].' '.$configuration['o_avatars_width'].'x'.$configuration['o_avatars_height'].' '.$lang_profile['pixels'].'.');
			}
			else if ($type == 1 && $uploaded_file['type'] != 'image/gif')
			{
				@unlink($configuration['o_avatars_dir'].'/'.$id.'.tmp');
				message($lang_profile['Bad type']);
			}			
			@unlink($configuration['o_avatars_dir'].'/'.$id.$extensions[0]);
			@unlink($configuration['o_avatars_dir'].'/'.$id.$extensions[1]);
			@unlink($configuration['o_avatars_dir'].'/'.$id.$extensions[2]);
			@rename($configuration['o_avatars_dir'].'/'.$id.'.tmp', $configuration['o_avatars_dir'].'/'.$id.$extensions[0]);
			@chmod($configuration['o_avatars_dir'].'/'.$id.$extensions[0], 0644);
		}
		else message($lang_profile['Unknown failure']);
		$db->query('UPDATE '.$db->prefix.'users SET use_avatar=1 WHERE id='.$id) or error('Unable to update avatar state', __FILE__, __LINE__, $db->error());
		redirect(FORUM_ROOT.'profile.php?section=personality&amp;id='.$id, $lang_profile['Avatar upload redirect']);
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
	$required_fields = array('req_file' => $lang_profile['File']);
	$focus_element = array('upload_avatar', 'req_file');
	require FORUM_ROOT.'header.php';
	generate_profile_menu('personality');

?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Upload avatar'] ?></span></h2>
	<div class="box">
		<form id="upload_avatar" method="post" enctype="multipart/form-data" action="profile.php?action=upload_avatar2&amp;id=<?php echo $id ?>" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_profile['Upload avatar legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $configuration['o_avatars_size'] ?>" />
						<label><strong><?php echo $lang_profile['File'] ?></strong><br /><input name="req_file" type="file" size="40" /><br /></label>
						<p><?php echo $lang_profile['Avatar desc'].' '.$configuration['o_avatars_width'].' x '.$configuration['o_avatars_height'].' '.$lang_profile['pixels'].' '.$lang_common['and'].' '.$configuration['o_avatars_size'].' '.$lang_profile['bytes'].' ('.ceil($configuration['o_avatars_size'] / 1024) ?> KB).</p>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" OnClick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="upload" value="<?php echo $lang_profile['Upload'] ?>" /></p>
		</form>
	</div>
</div></div>
<div class="clearer"></div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if ($action == 'delete_avatar')
{
	if ($forum_user['id'] != $id && $forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
	confirm_referrer('profile.php');
	@unlink($configuration['o_avatars_dir'].'/'.$id.'.jpg');
	@unlink($configuration['o_avatars_dir'].'/'.$id.'.png');
	@unlink($configuration['o_avatars_dir'].'/'.$id.'.gif');
	$db->query('UPDATE '.$db->prefix.'users SET use_avatar=0 WHERE id='.$id) or error('Unable to update avatar state', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'profile.php?section=personality&amp;id='.$id, $lang_profile['Avatar deleted redirect']);
}
else if (isset($_POST['update_group_membership']))
{
	if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);
	confirm_referrer('profile.php');
	$new_group_id = intval($_POST['group_id']);
	$db->query('UPDATE '.$db->prefix.'users SET group_id='.$new_group_id.' WHERE id='.$id) or error('Unable to change user group', __FILE__, __LINE__, $db->error());
	include_once(FORUM_ROOT. 'include/modules/mod_invitation.php');
	$inv = $db->query('SELECT g_invitations FROM '.$db->prefix.'groups where G_ID=' . $_POST['group_id']) or error('Unable to fetch group invitations', __FILE__, __LINE__, $db->error());
	list($invitations) = $db->fetch_row($inv);
	massInvite($id,intval($invitations));
	if ($new_group_id > USER_MOD)
	{
		$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
		while ($cur_forum = $db->fetch_assoc($result))
		{
			$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
			if (in_array($id, $cur_moderators))
			{
				$username = array_search($id, $cur_moderators);
				unset($cur_moderators[$username]);
				$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';
				$db->query('UPDATE '.$db->prefix.'forums SET moderators='.$cur_moderators.' WHERE id='.$cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
			}
		}
	}
	redirect(FORUM_ROOT.'profile.php?section=admin&amp;id='.$id, $lang_profile['Group membership redirect']);
}
else if (isset($_POST['update_forums']))
{
	if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);
	confirm_referrer('profile.php');
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$username = $db->result($result);
	$moderator_in = (isset($_POST['moderator_in'])) ? array_keys($_POST['moderator_in']) : array();
	$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
	while ($cur_forum = $db->fetch_assoc($result))
	{
		$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
		if (in_array($cur_forum['id'], $moderator_in) && !in_array($id, $cur_moderators))
		{
			$cur_moderators[$username] = $id;
			ksort($cur_moderators);
			$db->query('UPDATE '.$db->prefix.'forums SET moderators=\''.$db->escape(serialize($cur_moderators)).'\' WHERE id='.$cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
		}
		else if (!in_array($cur_forum['id'], $moderator_in) && in_array($id, $cur_moderators))
		{
			unset($cur_moderators[$username]);
			$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';
			$db->query('UPDATE '.$db->prefix.'forums SET moderators='.$cur_moderators.' WHERE id='.$cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
		}
	}
	redirect(FORUM_ROOT.'profile.php?section=admin&amp;id='.$id, $lang_profile['Update forums redirect']);
}
else if (isset($_POST['ban']))
{
	if ($forum_user['g_id'] > USER_MOD || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_ban_users'] == '0')) message($lang_common['No permission']);
	redirect(FORUM_ROOT.'admin_bans.php?add_ban='.$id, $lang_profile['Ban redirect']);
}
else if (isset($_POST['delete_user']) || isset($_POST['delete_user_comply']))
{
	if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);
	confirm_referrer('profile.php');
	$result = $db->query('SELECT group_id, username FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	list($group_id, $username) = $db->fetch_row($result);
	if ($group_id == USER_ADMIN) message('Administrators cannot be deleted. In order to delete this user, you must first move him/her to a different user group.');
	if (isset($_POST['delete_user_comply']))
	{
		if ($group_id < USER_GUEST)
		{
			$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
			while ($cur_forum = $db->fetch_assoc($result))
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
				if (in_array($id, $cur_moderators))
				{
					unset($cur_moderators[$username]);
					$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';
					$db->query('UPDATE '.$db->prefix.'forums SET moderators='.$cur_moderators.' WHERE id='.$cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
				}
			}
		}
		$db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE user_id='.$id) or error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());
		$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$id) or error('Unable to remove user from online list', __FILE__, __LINE__, $db->error());
		if (isset($_POST['delete_posts']))
		{
			require FORUM_ROOT.'include/search_idx.php';
			@set_time_limit(0);
			$result = $db->query('SELECT p.id, p.topic_id, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE p.poster_id='.$id) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
			if ($db->num_rows($result))
			{
				while ($cur_post = $db->fetch_assoc($result))
				{
					$result2 = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$cur_post['topic_id'].' ORDER BY posted LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
					if ($db->result($result2) == $cur_post['id']) delete_topic($cur_post['topic_id']);
					else delete_post($cur_post['id'], $cur_post['topic_id']);
					update_forum($cur_post['forum_id']);
				}
			}
		}
		else $db->query('UPDATE '.$db->prefix.'posts SET poster_id=1 WHERE poster_id='.$id) or error('Unable to update posts', __FILE__, __LINE__, $db->error());
		$db->query('DELETE FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to delete user', __FILE__, __LINE__, $db->error());
		$db->query('DELETE FROM '.$db->prefix.'messages WHERE owner='.$id) or error('Unable to delete users messages', __FILE__, __LINE__, $db->error());
		redirect(FORUM_ROOT.'index.php', $lang_profile['User delete redirect']);
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
	require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Confirm delete user'] ?></span></h2>
	<div class="box">
		<form id="confirm_del_user" method="post" action="profile.php?id=<?php echo $id ?>">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_profile['Confirm delete legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $lang_profile['Confirmation info'].' '.convert_htmlspecialchars($username).'.' ?></p>
						<div class="rbox">
							<label><input type="checkbox" name="delete_posts" value="1" checked="checked" /><?php echo $lang_profile['Delete posts'] ?><br /></label>
						</div>
						<p class="warntext"><strong><?php echo $lang_profile['Delete warning'] ?></strong></p>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="delete_user_comply" value="<?php echo $lang_profile['Delete'] ?>" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if (isset($_POST['form_sent']))
{
	$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result)) message($lang_common['Bad request']);
	$group_id = $db->result($result);
	if ($forum_user['id'] != $id && ($forum_user['g_id'] > USER_MOD || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_edit_users'] == '0') || ($forum_user['g_id'] == USER_MOD && $group_id < USER_GUEST))) message($lang_common['No permission']);
	if ($forum_user['g_id'] < USER_GUEST) confirm_referrer('profile.php');
	function extract_elements($allowed_elements)
	{
		$form = array();
		while (list($key, $value) = @each($_POST['form']))
		{
		    if (in_array($key, $allowed_elements)) $form[$key] = $value;
		}
		return $form;
	}
	$username_updated = false;
	$displayname_updated = false;	
	switch ($section)
	{
    		case 'invitation':
        	{
        		include_once(FORUM_ROOT.'include/modules/mod_invitation.php');
        		break;
        	}
		case 'essentials':
		{
			$form = extract_elements(array('timezone', 'language'));
			if ($forum_user['g_id'] < USER_GUEST)
			{
				$form['admin_note'] = trim($_POST['admin_note']);
				$form['displayname'] = trim($_POST['displayname']);
				$old_displayname = trim($_POST['old_displayname']);
				if ($form['displayname'] != $old_displayname) $displayname_updated = true;
				if ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_rename_users'] == '1'))
				{
					$form['username'] = trim($_POST['req_username']);
					$old_username = trim($_POST['old_username']);
					if (strlen($form['username']) < 2) message($lang_prof_reg['Username too short']);
					else if (forum_strlen($form['username']) > 25) message($lang_common['Bad request']);
					else if (!strcasecmp($form['username'], 'Guest') || !strcasecmp($form['username'], $lang_common['Guest'])) message($lang_prof_reg['Username guest']);
					else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $form['username'])) message($lang_prof_reg['Username IP']);
					else if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $form['username'])) message($lang_prof_reg['Username BBCode']);
					$result = $db->query('SELECT 1 FROM '.$db->prefix.'users WHERE username=\''.$db->escape($form['username']).'\' AND id!='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
					if ($db->num_rows($result)) message($lang_profile['Duplicate username']);
					if ($form['username'] != $old_username) $username_updated = true;
				}
				if ($forum_user['g_id'] == USER_ADMIN) $form['num_posts'] = intval($_POST['num_posts']);
			}
			if ($configuration['o_regs_verify'] == '0' || $forum_user['g_id'] < USER_GUEST)
			{
				require FORUM_ROOT.'include/email.php';
				$form['email'] = strtolower(trim($_POST['req_email']));
				if (!is_valid_email($form['email'])) message($lang_common['Invalid e-mail']);
			}
			if (isset($form['language']))
			{
				$form['language'] = preg_replace('#[\.\\\/]#', '', $form['language']);
				if (!file_exists(FORUM_ROOT.'lang/'.$form['language'].'/common.php')) message($lang_common['Bad request']);
			}
			
				
			break;
		}
		case 'personal':
		{
			$form = extract_elements(array('realname', 'url', 'location', 'sex', 'latitude', 'longitude', 'country', 'abs', 'abs_message'));
			if ($forum_user['g_id'] == USER_ADMIN) $form['title'] = trim($_POST['title']);
			else if ($forum_user['g_set_title'] == '1')
			{
				$form['title'] = trim($_POST['title']);

				if ($form['title'] != '')
				{
					$forbidden = array('Member', 'Moderator', 'Administrator', 'Banned', 'Guest', $lang_common['Member'], $lang_common['Moderator'], $lang_common['Administrator'], $lang_common['Banned'], $lang_common['Guest']);
					if (in_array($form['title'], $forbidden)) message($lang_profile['Forbidden title']);
				}
			}
			if ($form['url'] != '' && !stristr($form['url'], 'http://')) $form['url'] = 'http://'.$form['url'];
			if (!is_numeric($form['latitude']) || !is_numeric($form['longitude']))
			{
				$form['latitude'] = '';
				$form['longitude'] = '';
			}
			break;
		}
		case 'messaging':
		{
			$form = extract_elements(array('jabber', 'icq', 'msn', 'aim', 'yahoo', 'gtalk', 'skype'));
			if ($form['icq'] != '' && preg_match('/[^0-9]/', $form['icq'])) message($lang_prof_reg['Invalid ICQ']);
			break;
		}
		case 'personality':
		{
			$form = extract_elements(array('use_avatar'));
			$form['signature'] = forum_linebreaks(trim($_POST['signature']));
			if (forum_strlen($form['signature']) > $configuration['p_sig_length']) message($lang_prof_reg['Sig too long'].' '.$configuration['p_sig_length'].' '.$lang_prof_reg['characters'].'.');
			else if (substr_count($form['signature'], "\n") > ($configuration['p_sig_lines']-1)) message($lang_prof_reg['Sig too many lines'].' '.$configuration['p_sig_lines'].' '.$lang_prof_reg['lines'].'.');
			else if ($form['signature'] && $configuration['p_sig_all_caps'] == '0' && strtoupper($form['signature']) == $form['signature'] && $forum_user['g_id'] > USER_MOD) $form['signature'] = ucwords(strtolower($form['signature']));
			if ($configuration['p_sig_bbcode'] == '1' && strpos($form['signature'], '[') !== false && strpos($form['signature'], ']') !== false)
			{
				require FORUM_ROOT.'include/parser.php';
				$form['signature'] = preparse_bbcode($form['signature'], $foo, true);
			}
			if (!isset($form['use_avatar']) || $form['use_avatar'] != '1') $form['use_avatar'] = '0';
			break;
		}
		case 'display':
		{
			$form = extract_elements(array('disp_topics', 'disp_posts', 'show_smilies', 'show_img', 'show_img_sig', 'reverse_posts', 'show_avatars', 'show_sig', 'style'));
			if ($form['disp_topics'] != '' && intval($form['disp_topics']) < 3) $form['disp_topics'] = 3;
			if ($form['disp_topics'] != '' && intval($form['disp_topics']) > 75) $form['disp_topics'] = 75;
			if ($form['disp_posts'] != '' && intval($form['disp_posts']) < 3) $form['disp_posts'] = 3;
			if ($form['disp_posts'] != '' && intval($form['disp_posts']) > 75) $form['disp_posts'] = 75;
			if (!isset($form['show_smilies']) || $form['show_smilies'] != '1') $form['show_smilies'] = '0';
			if (!isset($form['show_img']) || $form['show_img'] != '1') $form['show_img'] = '0';
			if (!isset($form['show_img_sig']) || $form['show_img_sig'] != '1') $form['show_img_sig'] = '0';
			if (!isset($form['reverse_posts']) || $form['reverse_posts'] != '1') $form['reverse_posts'] = '0';
			if (!isset($form['show_avatars']) || $form['show_avatars'] != '1') $form['show_avatars'] = '0';
			if (!isset($form['show_sig']) || $form['show_sig'] != '1') $form['show_sig'] = '0';
			break;
		}
		case 'privacy':
		{
			$form = extract_elements(array('email_setting', 'save_pass', 'notify_with_post', 'email_alert'));
			$form['email_setting'] = intval($form['email_setting']);
			if ($form['email_setting'] < 0 && $form['email_setting'] > 2) $form['email_setting'] = 1;
			if (!isset($form['save_pass']) || $form['save_pass'] != '1') $form['save_pass'] = '0';
			if (!isset($form['notify_with_post']) || $form['notify_with_post'] != '1') $form['notify_with_post'] = '0';
			if (!isset($form['email_alert']) || $form['email_alert'] != '1') $form['email_alert'] = '0';
			if ($forum_user['id'] == $id && $form['save_pass'] != $forum_user['save_pass'])
			{
				$result = $db->query('SELECT password FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user password hash', __FILE__, __LINE__, $db->error());
				forum_setcookie($id, $db->result($result), ($form['save_pass'] == '1') ? time() + 31536000 : 0);
			}
			break;
		}
		default: message($lang_common['Bad request']);
	}
	$temp = array();
	while (list($key, $input) = @each($form))
	{
		$value = ($input !== '') ? '\''.$db->escape($input).'\'' : 'NULL';
		$temp[] = $key.'='.$value;
	}
	if(isset($_POST['bday_year'])&& isset($_POST['bday_month']) && isset($_POST['bday_day'])) $birthday = $_POST['bday_year']."-".$_POST['bday_month']."-".$_POST['bday_day'];
	$bday_query = (isset($birthday)) ? 'birthday="'.$birthday.'", ': NULL;
	if (empty($temp)) message($lang_common['Bad request']);
	$db->query('UPDATE '.$db->prefix.'users SET '.$bday_query.implode(',', $temp).' WHERE id='.$id) or error('Unable to update profile', __FILE__, __LINE__, $db->error());
	if ($displayname_updated)
	{
		$displaynamecheck = 'SELECT COUNT (*) FROM '.$db->prefix.'users where displayname=\''.$db->escape($form['displayname']);
		$db->query($displaynamecheck);
		$displaynamecheck = mysql_result($displaynamecheck , 0);
		if ($displaynamecheck == 0)
		{
			$db->query('UPDATE '.$db->prefix.'users SET displayname=\''.$db->escape($form['displayname']).'\' WHERE username=\''.$db->escape($form['username']).'\'') or error('Unable to update posts', __FILE__, __LINE__, $db->error());
		}
		else
		{
			error('That displayname is already being used');
		}
	}
	
	if ($username_updated)
	{
		$db->query('UPDATE '.$db->prefix.'posts SET poster=\''.$db->escape($form['username']).'\' WHERE poster_id='.$id) or error('Unable to update posts', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'topics SET poster=\''.$db->escape($form['username']).'\' WHERE poster=\''.$db->escape($old_username).'\'') or error('Unable to update topics', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'topics SET last_poster=\''.$db->escape($form['username']).'\' WHERE last_poster=\''.$db->escape($old_username).'\'') or error('Unable to update topics', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'forums SET last_poster=\''.$db->escape($form['username']).'\' WHERE last_poster=\''.$db->escape($old_username).'\'') or error('Unable to update forums', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'online SET ident=\''.$db->escape($form['username']).'\' WHERE ident=\''.$db->escape($old_username).'\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());
		$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		$group_id = $db->result($result);
		if ($group_id < USER_GUEST)
		{
			$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
			while ($cur_forum = $db->fetch_assoc($result))
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
				if (in_array($id, $cur_moderators))
				{
					unset($cur_moderators[$old_username]);
					$cur_moderators[$form['username']] = $id;
					ksort($cur_moderators);
					$db->query('UPDATE '.$db->prefix.'forums SET moderators=\''.$db->escape(serialize($cur_moderators)).'\' WHERE id='.$cur_forum['id']) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
				}
			}
		}
	}
	redirect(FORUM_ROOT.'profile.php?section='.$section.'&amp;id='.$id, $lang_profile['Profile redirect']);
}
$result = $db->query('SELECT u.email_alert, u.username, u.bookmarks, u.displayname, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.gtalk, u.skype, u.sex, u.location, u.birthday, u.use_avatar, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.save_pass, u.notify_with_post, u.show_smilies, u.show_img, u.show_img_sig, u.reverse_posts, u.show_avatars, u.show_sig, u.timezone, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.country, u.latitude, u.longitude, u.invitedby, g.g_id, g.g_user_title, g.g_color, u.abs, u.abs_message FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

if (!$db->num_rows($result)) message($lang_common['Bad request']);
$user = $db->fetch_assoc($result);
$last_post = format_time($user['last_post']);
if ($user['signature'] != '')
{
	require FORUM_ROOT.'include/parser.php';
	$parsed_signature = parse_signature($user['signature']);
}
if ($forum_user['id'] != $id && ($forum_user['g_id'] > USER_MOD || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_edit_users'] == '0') || ($forum_user['g_id'] == USER_MOD && $user['g_id'] < USER_GUEST)) ||	($action == 'view'))
{
	if ($user['email_setting'] == '0' && !$forum_user['is_guest']) $email_field = '<a href="mailto:'.$user['email'].'">' . $lang_profile['Send e-mail'] . '</a>';
	else if ($user['email_setting'] == '1' && !$forum_user['is_guest']) $email_field = '<a href="misc.php?email='.$id.'">' . $lang_profile['Send e-mail'] . '</a>';
	else $email_field = $lang_profile['Private'];
	$user_title_field = get_title($user);
	if ($user['url'] != '')
	{
		$user['url'] = convert_htmlspecialchars($user['url']);
		if ($configuration['o_censoring'] == '1') $user['url'] = censor_words($user['url']);
		$url = '<a href="'.$user['url'].'">' . $lang_profile['Visit website'] . '</a>';
	}
	else $url = $lang_profile['Unknown'];
	if ($configuration['o_avatars'] == '1')
	{
		if ($user['use_avatar'] == '1')
		{
			if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$id.'.gif')) $avatar_field = '<img src="'.$configuration['o_avatars_dir'].'/'.$id.'.gif" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$id.'.jpg')) $avatar_field = '<img src="'.$configuration['o_avatars_dir'].'/'.$id.'.jpg" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$id.'.png')) $avatar_field = '<img src="'.$configuration['o_avatars_dir'].'/'.$id.'.png" '.$img_size[3].' alt="" />';
			else $avatar_field = $lang_profile['No avatar'];
		}
		else $avatar_field = $lang_profile['No avatar'];
	}
	list($year, $month, $day) = explode('-',$user['birthday']);
	if($month!=0 && $day!=0)
	{
		$Nyear = (strlen($year)!=4)? NULL: $year;
		$birthday = date("F jS ",mktime(0,0,0,$month,$day,0)).$Nyear;
	}
	else $birthday = $lang_profile['Unknown'];
	$posts_field = '';
	if ($configuration['o_show_post_count'] == '1' || $forum_user['g_id'] < USER_GUEST) $posts_field = $user['num_posts'];
	if ($forum_user['g_search'] == '1') $posts_field .= (($posts_field != '') ? ' - ' : '').'<a href="search.php?action=show_user&amp;user_id='.$id.'">'.$lang_profile['Show posts'].'</a>';
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
	define('ALLOW_INDEX', 1);
	require FORUM_ROOT.'header.php';
	generate_profile_menu('view');
?>
<div id="viewprofile" class="block">
	<h2><span><?php echo $lang_common['Profile'] ?></span></h2>
	<div class="box">
		<div class="fakeform">
			<?php if ($forum_user['g_id'] < USER_GUEST || $forum_user['g_id'] == USER_MEMBER){ ?>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_profile['Section expertise']; ?></legend>
					<div class="infldset">
						<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="expertise.php?id=<?php echo $id ?>"><?php echo $lang_profile['Expertise Link']; ?></a></p>
					</div>
				</fieldset>
			</div>
			<?php } ?>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_profile['Section personal'] ?></legend>
					<div class="infldset">
						<dl>
							<dt><?php echo $lang_common['Username'] ?>: </dt>
							<dd><span style="color:<?php echo $user['g_color'] ?>"><b><?php echo convert_htmlspecialchars($user['username']) ?></b></span></dd>
							<dt>Display Name: </dt>
							<dd><span style="color:<?php echo $user['g_color'] ?>"><b><?php echo convert_htmlspecialchars($user['displayname']) ?></b></span></dd>
							<dt><?php echo $lang_common['Title'] ?>: </dt>
							<dd><?php echo ($configuration['o_censoring'] == '1') ? censor_words($user_title_field) : $user_title_field; ?></dd>
							<dt><?php echo $lang_profile['Realname'] ?>: </dt>
							<dd><?php echo ($user['realname'] !='') ? convert_htmlspecialchars(($configuration['o_censoring'] == '1') ? censor_words($user['realname']) : $user['realname']) : $lang_profile['Unknown']; ?></dd>
					  <dt><?php echo $lang_profile['Sex'] ?>: </dt>
					  <dd><?php echo ($user['sex'] !='') ? convert_htmlspecialchars(($configuration['o_censoring'] == '1') ? censor_words($user['sex']) : $user['sex']) : $lang_profile['Unknown']; ?></dd>
							<dt><?php echo $lang_profile['Location'] ?>: </dt>
							<dd><?php echo ($user['location'] !='') ? convert_htmlspecialchars(($configuration['o_censoring'] == '1') ? censor_words($user['location']) : $user['location']) : $lang_profile['Unknown']; ?></dd>
  <dt><?php echo $lang_profile['Country'] ?>: </dt>
  <dd><?php echo ($user['country'] !='') ? convert_htmlspecialchars($user['country']) . '<br />' : $lang_profile['Unknown']; ?></dd> 
<?php if ($configuration['o_um_enable'] == '1') { ?>
	<br /><br />
	<?php if ($user['latitude'] != '' && $user['longitude'] != '') { ?>
							<div id="map" style="width: 450px; height: 350px"></div>
							<script src="http://maps.google.com/maps?file=api&v=1&key=<?php echo convert_htmlspecialchars($configuration['o_um_key']) ?>" type="text/javascript"></script>
							<script type="text/javascript">
							//<![CDATA[
		<?php if (eregi('msie', $_SERVER['HTTP_USER_AGENT'])) echo '	window.onload = function() {'."\n"; ?>
							var map = new GMap(document.getElementById("map"));
							map.addControl(new GSmallMapControl());
							map.addControl(new GMapTypeControl());
							map.centerAndZoom(new GPoint(<?php echo $user['longitude'] ?>, <?php echo $user['latitude'] ?>), <?php echo convert_htmlspecialchars($configuration['o_um_default_zoom']) ?>);
							map.addOverlay(new GMarker(new GPoint('<?php echo $user['longitude'] ?>', '<?php echo $user['latitude'] ?>')));
		<?php if (eregi('msie', $_SERVER['HTTP_USER_AGENT'])) echo '	}'; ?>
							//]]>
							</script><br />
	<?php } ?>
<?php } ?>
							<dt><?php echo $lang_profile['Website'] ?>: </dt>
							<dd><?php echo $url ?>&nbsp;</dd>
							<dt><?php echo $lang_profile['Birthday'] ?>: </dt>
							<dd><?php echo $birthday?>&nbsp;</dd>
							<dt><?php echo $lang_common['E-mail'] ?>: </dt>
							<dd><?php echo $email_field ?></dd>
<?php
	if($configuration['o_pms_enabled'] && !$forum_user['is_guest'] && $forum_user['g_pm'] == 1)
	{
?>
							<dt><?php echo $lang_pms['PM'] ?>: </dt>
							<dd><a href="message_send.php?id=<?php echo $id ?>"><?php echo $lang_pms['Quick message'] ?></a></dd>
<?php
	}
?>

						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
			<?php if ($user['abs']): ?>
			<a name="absent"></a>
			<div class="inform">
				<fieldset>
				<legend><?php echo '<strong>'.$user['username'].'</strong> is absent' ?></legend>
					<div class="infldset">
						<dl>
							<dt><?php echo 'Message' ?>: </dt>
							<dd><?php if ($user['abs_message'] == '') $user['abs_message'] = 'No message.'; ?><?php echo $user['abs_message']; ?></dd>
						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
			<?php endif; ?>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_profile['Section messaging'] ?></legend>
					<div class="infldset">
						<dl>
<?php if ($user['jabber'] != '') { ?>
							<dt><?php echo $lang_profile['Jabber'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/jabber.png" alt="' . $user['jabber'] .'"></dd>';
}
if ($user['icq'] != '') { ?>
							<dt><?php echo $lang_profile['ICQ'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/icq.png" alt="' . $user['icq'] .'"></dd>';
}
if ($user['msn'] != '') { ?>
							<dt><?php echo $lang_profile['MSN'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/msn.png" alt="' . $user['msn'] .'"></dd>';
}
if ($user['aim'] != '') { ?>
 							<dt><?php echo $lang_profile['AOL IM'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/aim.png" alt="' . $user['aim'] .'"></dd>';
}
if ($user['yahoo'] != '') { ?>
							<dt><?php echo $lang_profile['Yahoo'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/yahoo.png" alt="' . $user['yahoo'] .'">';
}
if ($user['gtalk'] != '') { ?>
							<dt><?php echo $lang_profile['Google Talk'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/gtalk.png" alt="' . $user['gtalk'] .'"></dd>';
}
if ($user['skype'] != '') { ?>
							<dt><?php echo $lang_profile['Skype'] ?>: </dt>
							<?php echo '<dd><img border="0" src="img/' . $forum_user['style'] . '/skype.png" alt="' . $user['skype'] .'"></dd>';
} ?>
						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_profile['Section personality'] ?></legend>
					<div class="infldset">
						<dl>
<?php if ($configuration['o_avatars'] == '1'): ?>							<dt><?php echo $lang_profile['Avatar'] ?>: </dt>
							<dd><?php echo $avatar_field ?></dd>
<?php endif; ?>							<dt><?php echo $lang_profile['Signature'] ?>: </dt>
							<dd><div><?php echo isset($parsed_signature) ? $parsed_signature : $lang_profile['No sig']; ?></div></dd>
						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_profile['User activity'] ?></legend>
					<div class="infldset">
						<dl>
<?php if ($posts_field != ''): ?>							<dt><?php echo $lang_common['Posts'] ?>: </dt>
							<dd><?php echo $posts_field ?></dd>
<?php endif; ?>							<dt><?php echo $lang_common['Last post'] ?>: </dt>
							<dd><?php echo $last_post ?></dd>
<?php
$result = $db->query('SELECT referral_count FROM '.$db->prefix.'users WHERE id='.$id) or error('Invalid Member ID', __FILE__, __LINE__, $db->error());
$referral_count = $db->fetch_row($result);
$referral_url = $configuration['o_base_url'] . '/index.php?referrer=' . $id;
?>
							<dt>Referral Count: </dt>
							<dd><?php echo $referral_count[0] ?><br /></dd>
							<dt>Referral URL: </dt>
							<dd><a href="<?php echo $referral_url ?>"><?php echo $referral_url ?></a><br /></dd>
							<dt><?php echo $lang_common['Registered'] ?>: </dt>
							<dd><?php echo format_time($user['registered'], true) ?></dd>
						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else
{
	if (!$section || $section == 'essentials')
	{
		if ($forum_user['g_id'] < USER_GUEST)
		{
			if ($forum_user['g_id'] == USER_ADMIN || $configuration['p_mod_rename_users'] == '1') $username_field = '<input type="hidden" name="old_username" value="'.convert_htmlspecialchars($user['username']).'" /><label><strong>'.$lang_common['Username'].'</strong><br /><input type="text" class="textbox" name="req_username" value="'.convert_htmlspecialchars($user['username']).'" size="25" maxlength="25" /><br /></label>'."\n";			
			$email_field = '<label><strong>'.$lang_common['E-mail'].'</strong><br /><input type="text" class="textbox" name="req_email" value="'.$user['email'].'" size="25" maxlength="50" /><br /></label><p><a href="misc.php?email='.$id.'">'.$lang_common['Send e-mail'].'</a></p>'."\n";
			$email_field .= '<p><a href="message_send.php?id='.$id.'">'.$lang_pms['Quick message'].'</a></p>'."\n";
		}
		else
		{
			$username_field = '<p>'.$lang_common['Username'].': '.convert_htmlspecialchars($user['username']).'</p>'."\n";
			if ($configuration['o_regs_verify'] == '1') $email_field = '<p>'.$lang_common['E-mail'].': '.$user['email'].'&nbsp;-&nbsp;<a href="profile.php?action=change_email&amp;id='.$id.'">'.$lang_profile['Change e-mail'].'</a></p>'."\n";
			else $email_field = '<label><strong>'.$lang_common['E-mail'].'</strong><br /><input type="text" class="textbox" name="req_email" value="'.$user['email'].'" size="40" maxlength="50" /><br /></label>'."\n";
		}
		if ($forum_user['g_id'] == USER_ADMIN) $posts_field = '<label>'.$lang_common['Posts'].'<br /><input type="text" class="textbox" name="num_posts" value="'.$user['num_posts'].'" size="8" maxlength="8" /><br /></label><p><a href="search.php?action=show_user&amp;user_id='.$id.'">'.$lang_profile['Show posts'].'</a></p>'."\n";
		else if ($configuration['o_show_post_count'] == '1' || $forum_user['g_id'] < USER_GUEST) $posts_field = '<p>'.$lang_common['Posts'].': '.$user['num_posts'].' - <a href="search.php?action=show_user&amp;user_id='.$id.'">'.$lang_profile['Show posts'].'</a></p>'."\n";
		else $posts_field = '<p><a href="search.php?action=show_user&amp;user_id='.$id.'">'.$lang_profile['Show posts'].'</a></p>'."\n";
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		$required_fields = array('req_username' => $lang_common['Username'], 'req_email' => $lang_common['E-mail']);
		require FORUM_ROOT.'header.php';
		generate_profile_menu('essentials');
		$displayname_field = '<input type="hidden" name="old_displayname" value="'.convert_htmlspecialchars($user['displayname']).'" /><label><strong>Display Name</strong><br /><input type="text" class="textbox" name="displayname" value="'.convert_htmlspecialchars($user['displayname']).'" size="25" maxlength="25" /><br /></label>'."\n";
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section essentials'] ?></span></h2>
		<div class="box">
			<form id="profile1" method="post" action="profile.php?section=essentials&amp;id=<?php echo $id ?>" onsubmit="return process_form(this)">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Username and pass legend'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<?php echo $username_field ?><br />
							<?php echo $displayname_field ?>
<?php if ($forum_user['id'] == $id || $forum_user['g_id'] == USER_ADMIN || ($user['g_id'] > USER_MOD && $configuration['p_mod_change_passwords'] == '1')): ?><p><a href="profile.php?action=change_pass&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Change pass'] ?></a></p>
<?php endif; ?>					</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_prof_reg['E-mail legend'] ?></legend>
						<div class="infldset">
							<?php echo $email_field ?>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_prof_reg['Localisation legend'] ?></legend>
						<div class="infldset">
         <label><?php echo $lang_prof_reg['Timezone'] ?>: <select name="form[timezone]">
                 <option value="-12"<?php if ($user['timezone'] == -12) echo ' selected="selected"' ?>>(GMT-12:00) International Date West</option>
                 <option value="-11"<?php if ($user['timezone'] == -11) echo ' selected="selected"' ?>>(GMT-11:00) Midway Island, Samoa</option>
                 <option value="-10"<?php if ($user['timezone'] == -10) echo ' selected="selected"' ?>>(GMT-10:00) Hawaii</option>
                 <option value="-9"<?php if ($user['timezone'] == -9) echo ' selected="selected"' ?>>(GMT-09:00) Alaska</option>
                 <option value="-8"<?php if ($user['timezone'] == -8) echo ' selected="selected"' ?>>(GMT-08:00) Pacific</option>
                 <option value="-7"<?php if ($user['timezone'] == -7) echo ' selected="selected"' ?>>(GMT-07:00) Mountain</option>
                 <option value="-6"<?php if ($user['timezone'] == -6) echo ' selected="selected"' ?>>(GMT-06:00) Central</option>
                 <option value="-5"<?php if ($user['timezone'] == -5) echo ' selected="selected"' ?>>(GMT-05:00) Eastern</option>
                 <option value="-4"<?php if ($user['timezone'] == -4) echo ' selected="selected"' ?>>(GMT-04:00) Atlantic</option>
                 <option value="-3.5"<?php if ($user['timezone'] == -3.5) echo ' selected="selected"' ?>>(GMT-03:30) Newfoundland</option>
                 <option value="-3"<?php if ($user['timezone'] == -3) echo ' selected="selected"' ?>>(GMT-03:00) Brazil, Buenos Aires</option>
                 <option value="-2"<?php if ($user['timezone'] == -2) echo ' selected="selected"' ?>>(GMT-02:00) Mid-Atlantic</option>
                 <option value="-1"<?php if ($user['timezone'] == -1) echo ' selected="selected"' ?>>(GMT-01:00) Azores</option>
                 <option value="0"<?php if ($user['timezone'] == 0) echo ' selected="selected"' ?>>(GMT-00:00) Greenwich, West. Europe</option>
                 <option value="1"<?php if ($user['timezone'] == 1) echo ' selected="selected"' ?>>(GMT+01:00) Central European</option>
                 <option value="2"<?php if ($user['timezone'] == 2) echo ' selected="selected"' ?>>(GMT+02:00) Eastern European</option>
                 <option value="3"<?php if ($user['timezone'] == 3) echo ' selected="selected"' ?>>(GMT+03:00) Moscow, Baghdad</option>
                 <option value="3.5"<?php if ($user['timezone'] == 3.5) echo ' selected="selected"' ?>>(GMT+03:30) Iran</option>
                 <option value="4"<?php if ($user['timezone'] == 4) echo ' selected="selected"' ?>>(GMT+04:00) Abu Dhabi, Dubai</option>
                 <option value="4.5"<?php if ($user['timezone'] == 4.5) echo ' selected="selected"' ?>>(GMT+04:30) Kabul</option>
                 <option value="5"<?php if ($user['timezone'] == 5) echo ' selected="selected"' ?>>(GMT+05:00) Islamabad, Karachi</option>
                 <option value="5.5"<?php if ($user['timezone'] == 5.5) echo ' selected="selected"' ?>>(GMT+05:30) India</option>
                 <option value="5.75"<?php if ($user['timezone'] == 5.75) echo ' selected="selected"' ?>>(GMT+05:45) Kathmandu</option>
                 <option value="6"<?php if ($user['timezone'] == 6) echo ' selected="selected"' ?>>(GMT+06:00) Astana, Dhaka</option>
                 <option value="6.5"<?php if ($user['timezone'] == 6.5) echo ' selected="selected"' ?>>(GMT+06:30) Rangoon</option>
                 <option value="7"<?php if ($user['timezone'] == 7) echo ' selected="selected"' ?>>(GMT+07:00) Bangkok, Jakarta</option>
                 <option value="8"<?php if ($user['timezone'] == 8) echo ' selected="selected"' ?>>(GMT+08:00) Western Australia</option>
                 <option value="9"<?php if ($user['timezone'] == 9) echo ' selected="selected"' ?>>(GMT+09:00) Japan, Korea</option>
                 <option value="9.5"<?php if ($user['timezone'] == 9.5) echo ' selected="selected"' ?>>(GMT+09:30) Central Austrailia</option>
                 <option value="10"<?php if ($user['timezone'] == 10) echo ' selected="selected"' ?>>(GMT+10:00) Eastern Austrailia</option>
                 <option value="11"<?php if ($user['timezone'] == 11) echo ' selected="selected"' ?>>(GMT+11:00) Magadan, Solomon Is.</option>
                 <option value="12"<?php if ($user['timezone'] == 12) echo ' selected="selected"' ?>>(GMT+12:00) New Zealand, Fiji</option>
                 <option value="12.75"<?php if ($user['timezone'] == 12.75) echo ' selected="selected"' ?>>(GMT+12:45) Chatam Island, NZ</option>
                 <option value="13"<?php if ($user['timezone'] == 13) echo ' selected="selected"' ?>>(GMT+13:00) Tonga, Phoenix Islands</option>
                 <option value="14"<?php if ($user['timezone'] == 14) echo ' selected="selected"' ?>>(GMT+14:00) Christmas Islands</option>
           </select>
&nbsp;&nbsp;<img src="img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_prof_reg['Timezone info'] ?>');" onmouseout="return nd();" alt="" />
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
			natsort($languages);
?>
							<label><?php echo $lang_prof_reg['Language'] ?>: <?php echo $lang_prof_reg['Language info'] ?>
							<br /><select name="form[language]">
<?php
			while (list(, $temp) = @each($languages))
			{
				if ($user['language'] == $temp) echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
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
						<legend><?php echo $lang_profile['User activity'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_common['Registered'] ?>: <?php echo format_time($user['registered'], true); if ($forum_user['g_id'] < USER_GUEST) echo ' (<a href="moderate.php?get_host='.convert_htmlspecialchars($user['registration_ip']).'">'.convert_htmlspecialchars($user['registration_ip']).'</a>)'; ?></p>
<?php if($user['invitedby'] > 0)
{
	include_once(FORUM_ROOT.'include/modules/mod_invitation.php');
?>
<p><?php echo $lang_invitation['Invited By'] ?>: <a href="profile.php?id=<?php echo $user['invitedby'] ?>"><?php echo showInviter($user['invitedby']) ?></p>
<?php
}
?>
							<p><?php echo $lang_common['Last post'] ?>: <?php echo $last_post ?></p>
								<?php echo $posts_field ?>
<?php
$result = $db->query('SELECT referral_count FROM '.$db->prefix.'users WHERE id='.$id) or error('Invalid Member ID', __FILE__, __LINE__, $db->error());
$referral_count = $db->fetch_row($result);
$referral_url = $configuration['o_base_url'] . '/index.php?referrer=' . $id;
?>
							<p>Referral Count: <?php echo $referral_count[0] ?></p>
							<p>Referral URL: <a href="<?php echo $referral_url ?>"><?php echo $referral_url ?></a></p>

<?php if ($forum_user['g_id'] < USER_GUEST): ?>							<label><?php echo $lang_profile['Admin note'] ?><br />
							<input id="admin_note" type="text" class="textbox" name="admin_note" value="<?php echo convert_htmlspecialchars($user['admin_note']) ?>" size="30" maxlength="30" /><br /></label>
	<?php endif; ?>	
						</div>
				</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /><?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
	else if ($section == 'bookmarks')
	{
		if ($_POST['formsent'] == '1')
		{
			$db->query('UPDATE '.$db->prefix.'users SET bookmarks = \''.$_POST['bookmarks'].'\' WHERE username=\''.$db->escape($user['username']).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());	
		redirect(FORUM_ROOT.'profile.php?section=bookmarks&amp;id='.$id, "Bookmarks Updated. Redirecting &hellip;");
		}
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('bookmarks');
		?>
		<div class="blockform">
			<h2>
				<span>
					<?php echo convert_htmlspecialchars($user['username']).' - Bookmarks'?>
				</span>
			</h2>
			<div class="box">
				<form id="profile8" method="post" action="profile.php?section=bookmarks&amp;id=<?php echo $id ?>" />
					<input type="hidden" name="formsent" id="formsent" value="1" />
					<div class="inform">
						<fieldset>
							<legend>Instructions</legend>
							<div class="infldset">
								To add or edit bookmarks, follow the following format:<br /><br />
								
								<input size="200" maxlength="300" type="text" readonly="readonly" value="3=<a href='http://www.powerwd.com'>Power Software</a>" style="background-color: transparent; border: none;" /><br /><br />
								The number in front defines what link it is after. If you want it to be the first link, put 0. If you want it to be the second link, you can put 1. Place a different bookmark on each line. The "href" attribute is where you put the URL for the site you want to link to. In between the start and end tag is where you put what you want to appear on the link. Example: Power Software.
							</div>
						</fieldset>
						<br />
						<fieldset>
							<legend>Bookmarks</legend>
							<div class="infldset">
								<textarea cols="80" rows="10" name="bookmarks" id="bookmarks"><?php echo $user['bookmarks']?></textarea>
							</div>
						</fieldset>
					</div>
					<input type="submit" class="b1" name="updatebooks" value="<?php echo $lang_common['Submit'] ?>" />
				</form>
			</div>
		</div>
<?php
	}
	else if ($section == 'personal')
	{
		if ($forum_user['g_set_title'] == '1') $title_field = '<label>'.$lang_common['Title'].'&nbsp;&nbsp;(<em>'.$lang_profile['Leave blank'].'</em>)<br /><input type="text" class="textbox" name="title" value="'.convert_htmlspecialchars($user['title']).'" size="30" maxlength="50" /><br /></label>'."\n";
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('personal');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section personal'] ?></span></h2>
		<div class="box">
			<form id="profile2" method="post" action="profile.php?section=personal&amp;id=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Personal details legend'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<label><?php echo $lang_profile['Realname'] ?><br /><input type="text" class="textbox" name="form[realname]" value="<?php echo convert_htmlspecialchars($user['realname']) ?>" size="30" maxlength="40" /><br /></label>
<label><?php echo $lang_profile['Sex'] ?></label> <select name="form[sex]"> <option value="male" <?php if ($user['sex'] == 'male') echo ' selected="selected"' ?>><?php echo $lang_profile['Male'] ?></option> <option value="female"<?php if ($user['sex'] == 'female') echo ' selected="selected"' ?>><?php echo $lang_profile['Female'] ?></option> </select>
<?php if (isset($title_field)): ?>					<?php echo $title_field ?>
<?php endif; ?>							<label><?php echo $lang_profile['Location'] ?><br /><input type="text" class="textbox" name="form[location]" value="<?php echo convert_htmlspecialchars($user['location']) ?>" size="30" maxlength="30" /><br /></label>
   <label>
     <?php echo $lang_profile['Country'] ?><br />
     <select name="form[country]">
       <?php require FORUM_ROOT.'include/modules/mod_countries.php';
       echo '<option value=" "';
       if ($user['country'] == " ") {
         echo ' selected="selected"'; }
       echo '> none </option>';
       foreach ($countries as $value) {
          echo '<option value="' . $value. '"';
          if ($user['country'] == $value) {
            echo ' selected="selected"'; }
         echo '>' . $value . '</option>'; } ?>
     </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   </label> 

<?php if ($configuration['o_um_enable'] == '1') { ?>
							<?php echo $lang_profile['Click'] ?><div id="map" style="width: 500px; height: 400px"></div>
							<script src="http://maps.google.com/maps?file=api&v=1&key=<?php echo convert_htmlspecialchars($configuration['o_um_key']) ?>" type="text/javascript"></script>
							<script type="text/javascript">
							//<![CDATA[
	<?php if (eregi('msie', $_SERVER['HTTP_USER_AGENT'])) echo '	window.onload = function() {'."\n"; ?>
							var map = new GMap(document.getElementById("map"));
							map.addControl(new GSmallMapControl());
							map.addControl(new GMapTypeControl());
							map.centerAndZoom(new GPoint(<?php echo ($user['longitude'] !='') ? $user['longitude'] : $configuration['o_um_default_lng']; ?>, <?php echo ($user['latitude'] !='') ? $user['latitude'] : $configuration['o_um_default_lat']; ?>), <?php echo convert_htmlspecialchars($configuration['o_um_default_zoom']) ?>);
							<?php if ($user['latitude'] !='' && $user['longitude'] !='') echo 'map.addOverlay(new GMarker(new GPoint(' . $user['longitude'] . ', ' . $user['latitude'] . ')));'; ?>
							GEvent.addListener(map, 'click', function(overlay, point) {
								if (overlay) {
									map.removeOverlay(overlay);
									document.getElementById("lat").value='';
									document.getElementById("lng").value='';
								} else if (point) {
									map.clearOverlays();
    								map.addOverlay(new GMarker(point));
									document.getElementById("lat").value=point.y;
									document.getElementById("lng").value=point.x;
								}
							});
	<?php if (eregi('msie', $_SERVER['HTTP_USER_AGENT'])) echo '	}'; ?>
							//]]>
							</script>
							<label><?php echo $lang_profile['Latitude'] ?><br /><input type="text" class="textbox" id="lat" name="form[latitude]" value="<?php echo convert_htmlspecialchars($user['latitude']) ?>" size="50" /><br /></label>
							<label><?php echo $lang_profile['Longitude'] ?><br /><input type="text" class="textbox" id="lng" name="form[longitude]" value="<?php echo convert_htmlspecialchars($user['longitude']) ?>" size="50" /><br /></label>
<?php } ?>
							<label><?php echo $lang_profile['Website'] ?><br /><input type="text" class="textbox" name="form[url]" value="<?php echo convert_htmlspecialchars($user['url']) ?>" size="30" maxlength="80" /><br /></label>
							<label><?php echo $lang_profile['Birthday'] ?></label>
							<div>
<?list($bday_year,$bday_month,$bday_day) = explode('-', $user['birthday']);?>
								<select name="bday_month">
									<option value="0" <?if(empty($bday_month)&& $bday_month="0")echo 'selected'?>>Month</option>
<?
	$month_name = array('',$lang_calendar['January'],$lang_calendar['February'],$lang_calendar['March'],$lang_calendar['April'],$lang_calendar['May'],$lang_calendar['June'],$lang_calendar['July'],$lang_calendar['August'],$lang_calendar['September'],$lang_calendar['October'],$lang_calendar['November'],$lang_calendar['December']);
	for($x=1;$x<13;$x++)
	{
		$s = ($bday_month == $x)? " selected": "";
		echo"\t\t\t\t\t\t\t\t\t<option value='".$x."'".$s.">".$month_name[$x]."</option>\n";
	}
?>
								</select>
								<select name="bday_day">
									<option value="0" <?if(empty($bday_day)&& $bday_day="0")echo 'selected'?>>Day</option>
<?
	for($x=01;$x<=31;$x++)
	{
		$s = ($bday_day == $x)? " selected": "";
		echo"\t\t\t\t\t\t\t\t\t<option value='".$x."'".$s.">".$x."</option>\n";
	}
?>
								</select>
								<select name="bday_year">
									<option value="0" <?if(empty($bday_year)&& $bday_year="0")echo 'selected'?>>Year</option>
<?
	for($x=date("Y")-10; $x>date("Y")-50; $x--)
	{
		$s = ($bday_year == $x)? " selected": "";
		echo"\t\t\t\t\t\t\t\t\t<option value='".$x."'".$s.">".$x."</option>\n";
	}

?>
								</select>
							</div>
						</div>
					</fieldset>
				</div>

				<div class="inform">
					<fieldset>
						<legend><?php echo 'Show your status' ?></legend>
						<div class="infldset">
							<input type="hidden" name="form[abs]" value="0" />
							<select name="form[abs]" >
							<?php if ($user['abs'] == '0'): ?>			
							<option value="0" checked>Present</option>
							<option value="1" checked>Absent</option>
							<?php endif; ?>					
							<?php if ($user['abs'] == '1'): ?>
							<option value="1" checked>Absent</option>
							<option value="0">Present</option>
							<?php endif; ?>
							</select><label><?php echo 'Absence message:' ?><br />
							<input type="text" class="textbox" name="form[abs_message]" value="<?php echo convert_htmlspecialchars($user['abs_message']) ?>" size="50" maxlength="70" />
&nbsp;&nbsp;<img src="img/admin/tooltip.png" onmouseover="return overlib('(Ex: Absent until 01/12/05 or, maybe 24/12/05)');" onmouseout="return nd();" alt="" /></label>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /><?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
	else if ($section == 'invitation')
	{
		include_once(FORUM_ROOT.'include/modules/mod_invitation_display.php');
	}
	else if ($section == 'messaging')
	{
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('messaging');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section messaging'] ?></span></h2>
		<div class="box">
			<form id="profile3" method="post" action="profile.php?section=messaging&amp;id=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Contact details legend'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<label><?php echo $lang_profile['Jabber'] ?><br /><input id="jabber" type="text" class="textbox" name="form[jabber]" value="<?php echo convert_htmlspecialchars($user['jabber']) ?>" size="20" maxlength="75" /><br /></label>
							<label><?php echo $lang_profile['ICQ'] ?><br /><input id="icq" type="text" class="textbox" name="form[icq]" value="<?php echo $user['icq'] ?>" size="20" maxlength="12" /><br /></label>
							<label><?php echo $lang_profile['MSN'] ?><br /><input id="msn" type="text" class="textbox" name="form[msn]" value="<?php echo convert_htmlspecialchars($user['msn']) ?>" size="20" maxlength="50" /><br /></label>
							<label><?php echo $lang_profile['AOL IM'] ?><br /><input id="aim" type="text" class="textbox" name="form[aim]" value="<?php echo convert_htmlspecialchars($user['aim']) ?>" size="20" maxlength="30" /><br /></label>
							<label><?php echo $lang_profile['Yahoo'] ?><br /><input id="yahoo" type="text" class="textbox" name="form[yahoo]" value="<?php echo convert_htmlspecialchars($user['yahoo']) ?>" size="20" maxlength="30" /><br /></label>
							<label><?php echo $lang_profile['Google Talk'] ?><br /><input id="gtalk" type="text" class="textbox" name="form[gtalk]" value="<?php echo convert_htmlspecialchars($user['gtalk']) ?>" size="20" maxlength="50" /><br /></label>
							<label><?php echo $lang_profile['Skype'] ?><br /><input id="skype" type="text" class="textbox" name="form[skype]" value="<?php echo convert_htmlspecialchars($user['skype']) ?>" size="20" maxlength="30" /><br /></label>

						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /><?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
	else if ($section == 'personality')
	{
		$avatar_field = '<a href="profile.php?action=upload_avatar&amp;id='.$id.'">'.$lang_profile['Upload avatar'].'</a>';
		if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$id.'.gif')) $avatar_format = 'gif';
		else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$id.'.jpg')) $avatar_format = 'jpg';
		else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$id.'.png')) $avatar_format = 'png';
		else $avatar_field = '<a href="profile.php?action=upload_avatar&amp;id='.$id.'">'.$lang_profile['Upload avatar'].'</a>';
		if ($img_size) $avatar_field .= '&nbsp;&nbsp;&nbsp;<a href="profile.php?action=avatar_gallery&amp;id='.$id.'">'.$lang_profile['Change avatar'].'</a>&nbsp;&nbsp;&nbsp;<a href="profile.php?action=delete_avatar&amp;id='.$id.'">'.$lang_profile['Delete avatar'].'</a>';
		else $avatar_field .= '&nbsp;&nbsp;&nbsp;<a href="profile.php?action=avatar_gallery&amp;id='.$id.'">'.$lang_profile['Select avatar'].'</a>';	
		if ($user['signature'] != '') $signature_preview = '<p>'.$lang_profile['Sig preview'].'</p>'."\n\t\t\t\t\t".'<div class="postsignature">'."\n\t\t\t\t\t\t".'<hr />'."\n\t\t\t\t\t\t".$parsed_signature."\n\t\t\t\t\t".'</div>'."\n";
		else $signature_preview = '<p>'.$lang_profile['No sig'].'</p>'."\n";
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('personality');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section personality'] ?></span></h2>
		<div class="box">
			<form id="profile4" method="post" action="profile.php?section=personality&amp;id=<?php echo $id ?>">
				<div><input type="hidden" name="form_sent" value="1" /></div>
<?php if ($configuration['o_avatars'] == '1'): ?>				<div class="inform">
					<fieldset id="profileavatar">
						<legend><?php echo $lang_profile['Avatar legend'] ?></legend>
						<div class="infldset">
<?php if (isset($avatar_format)): ?>					<img src="<?php echo $configuration['o_avatars_dir'].'/'.$id.'.'.$avatar_format ?>" <?php echo $img_size[3] ?> alt="" />
<?php endif; ?>					<p><?php echo $lang_profile['Avatar info'] ?></p>
							<div class="rbox">
								<label><input type="checkbox" name="form[use_avatar]" value="1"<?php if ($user['use_avatar'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Use avatar'] ?><br /></label>
							</div>
							<p class="clearb"><?php echo $avatar_field ?></p>
						</div>
					</fieldset>
				</div>
<?php endif; ?>				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Signature legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_profile['Signature info'] ?></p>
							<div class="txtarea">
								<label><?php echo $lang_profile['Sig max length'] ?>: <?php echo $configuration['p_sig_length'] ?> / <?php echo $lang_profile['Sig max lines'] ?>: <?php echo $configuration['p_sig_lines'] ?><br />
								<textarea name="signature" rows="4" cols="30"><?php echo convert_htmlspecialchars($user['signature']) ?></textarea><br /></label>
							</div>
							<ul class="bblinks">
								<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a>: <?php echo ($configuration['p_sig_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
								<li><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a>: <?php echo ($configuration['p_sig_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
								<li><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a>: <?php echo ($configuration['o_smilies_sig'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							</ul>
							<?php echo $signature_preview ?>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /><?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
	else if ($section == 'display')
	{
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('display');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section display'] ?></span></h2>
		<div class="box">
			<form id="profile5" method="post" action="profile.php?section=display&amp;id=<?php echo $id ?>">
				<div><input type="hidden" name="form_sent" value="1" /></div>
<?php
		$styles = array();
		$d = dir(FORUM_ROOT.'style');
		while (($entry = $d->read()) !== false)
		{
			if (substr($entry, strlen($entry)-4) == '.css') $styles[] = substr($entry, 0, strlen($entry)-4);
		}
		$d->close();
		if (count($styles) == 1) echo "\t\t\t".'<div><input type="hidden" name="form[style]" value="'.$styles[0].'" /></div>'."\n";
		else if (count($styles) > 1)
		{
			natsort($styles);
?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Style legend'] ?></legend>
						<div class="infldset">
							<label><?php echo $lang_profile['Style info'] ?><br />
							<select name="form[style]">
<?php
			while (list(, $temp) = @each($styles))
			{
				if ($user['style'] == $temp) echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.str_replace('_', ' ', $temp).'</option>'."\n";
				else echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>'."\n";
			}
?>
							</select>
							<br /></label>
						</div>
					</fieldset>
				</div>
<?php
		}
?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Post display legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_profile['Post display info'] ?></p>
							<div class="rbox">
								<label><input type="checkbox" name="form[show_smilies]" value="1"<?php if ($user['show_smilies'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Show smilies'] ?><br /></label>
								<label><input type="checkbox" name="form[show_sig]" value="1"<?php if ($user['show_sig'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Show sigs'] ?><br /></label>
<?php if ($configuration['o_avatars'] == '1'): ?>							<label><input type="checkbox" name="form[show_avatars]" value="1"<?php if ($user['show_avatars'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Show avatars'] ?><br /></label>
<?php endif; ?>								<label><input type="checkbox" name="form[show_img]" value="1"<?php if ($user['show_img'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Show images'] ?><br /></label>
								<label><input type="checkbox" name="form[show_img_sig]" value="1"<?php if ($user['show_img_sig'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Show images sigs'] ?><br /></label>
							</div>
							<p><?php echo $lang_profile['Reverse posts info'] ?></p>
							<div class="rbox">
								<label><input type="checkbox" name="form[reverse_posts]" value="1"<?php if ($user['reverse_posts'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Reverse posts'] ?><br /></label>
							</div>

						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Pagination legend'] ?></legend>
						<div class="infldset">
							<label class="conl"><?php echo $lang_profile['Topics per page'] ?><br /><input type="text" class="textbox" name="form[disp_topics]" value="<?php echo $user['disp_topics'] ?>" size="6" maxlength="3" /><br /></label>
							<label class="conl"><?php echo $lang_profile['Posts per page'] ?><br /><input type="text" class="textbox" name="form[disp_posts]" value="<?php echo $user['disp_posts'] ?>" size="6" maxlength="3" />
&nbsp;&nbsp;<img src="img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_profile['Paginate info'] ?> <?php echo $lang_profile['Leave blank'] ?>');" onmouseout="return nd();" alt="" /><br /></label>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" />  <?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
	else if ($section == 'privacy')
	{
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('privacy');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section privacy'] ?></span></h2>
		<div class="box">
			<form id="profile6" method="post" action="profile.php?section=privacy&amp;id=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_prof_reg['Privacy options legend'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<p><?php echo $lang_prof_reg['E-mail setting info'] ?></p>
							<div class="rbox">
								<label><input type="radio" name="form[email_setting]" value="0"<?php if ($user['email_setting'] == '0') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['E-mail setting 1'] ?><br /></label>
								<label><input type="radio" name="form[email_setting]" value="1"<?php if ($user['email_setting'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['E-mail setting 2'] ?><br /></label>
								<label><input type="radio" name="form[email_setting]" value="2"<?php if ($user['email_setting'] == '2') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['E-mail setting 3'] ?><br /></label>
							</div>
							<p><?php echo $lang_prof_reg['Save user/pass info'] ?></p>
							<div class="rbox">
								<label><input type="checkbox" name="form[save_pass]" value="1"<?php if ($user['save_pass'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['Save user/pass'] ?><br /></label>
							</div>
							<p><?php echo $lang_profile['Notify full info'] ?></p>
							<div class="rbox">
								<label><input type="checkbox" name="form[notify_with_post]" value="1"<?php if ($user['notify_with_post'] == '1') echo ' checked="checked"' ?> /><?php echo $lang_profile['Notify full'] ?><br /></label>
							</div>
							<p><?php echo 'If you want to receive an e-mail when you have a new Private Message.' ?></p>
                            				<div class="rbox">
                                				<label><input type="checkbox" name="form[email_alert]" value="1"<?php if ($user['email_alert'] == '1') echo ' checked="checked"' ?> /><?php echo 'Yes, notify me' ?><br /></label>
                            				</div>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /><?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php
	}
	else if ($section == 'admin')
	{
		if ($forum_user['g_id'] > USER_MOD || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_ban_users'] == '0')) message($lang_common['Bad request']);
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('admin');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_profile['Section admin'] ?></span></h2>
		<div class="box">
			<form id="profile7" method="post" action="profile.php?section=admin&amp;id=<?php echo $id ?>&amp;action=foo">
				<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
					<fieldset>
<?php
		if ($forum_user['g_id'] == USER_MOD)
		{
?>
						<legend><?php echo $lang_profile['Delete ban legend'] ?></legend>
						<div class="infldset">
							<p><input type="submit" class="b1" name="ban" value="<?php echo $lang_profile['Ban user'] ?>" /></p>
						</div>
					</fieldset>
				</div>
<?php
		}
		else
		{
			if ($forum_user['id'] != $id)
			{
?>
						<legend><?php echo $lang_profile['Group membership legend'] ?></legend>
						<div class="infldset">
							<select id="group_id" name="group_id">
<?php
				$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.USER_GUEST.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
				while ($cur_group = $db->fetch_assoc($result))
				{
					if ($cur_group['g_id'] == $user['g_id'] || ($cur_group['g_id'] == $configuration['o_default_user_group'] && $user['g_id'] == '')) echo "\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
					else echo "\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
				}
?>
							</select>
							<input type="submit" class="b1" name="update_group_membership" value="<?php echo $lang_profile['Save'] ?>" />
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
<?php
			}
?>
						<legend><?php echo $lang_profile['Delete ban legend'] ?></legend>
						<div class="infldset">
							<input type="submit" class="b1" name="delete_user" value="<?php echo $lang_profile['Delete user'] ?>" />&nbsp;&nbsp;<input type="submit" class="b1" name="ban" value="<?php echo $lang_profile['Ban user'] ?>" />
						</div>
					</fieldset>
				</div>
<?php
			if ($user['g_id'] == USER_MOD || $user['g_id'] == USER_ADMIN)
			{
?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Set mods legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_profile['Moderator in info'] ?></p>
<?php
				$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.moderators FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
				$cur_category = 0;
				while ($cur_forum = $db->fetch_assoc($result))
				{
					if ($cur_forum['cid'] != $cur_category)
					{
						if ($cur_category) echo "\n\t\t\t\t\t\t\t\t".'</div>';
						if ($cur_category != 0) echo "\n\t\t\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t\t\t".'<div class="conl">'."\n\t\t\t\t\t\t\t\t".'<p><strong>'.$cur_forum['cat_name'].'</strong></p>'."\n\t\t\t\t\t\t\t\t".'<div class="rbox">';
						$cur_category = $cur_forum['cid'];
					}
					$moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
					echo "\n\t\t\t\t\t\t\t\t\t".'<label><input type="checkbox" name="moderator_in['.$cur_forum['fid'].']" value="1"'.((in_array($id, $moderators)) ? ' checked="checked"' : '').' />'.convert_htmlspecialchars($cur_forum['forum_name']).'<br /></label>'."\n";
				}
?>
								</div>
							</div>
							<br class="clearb" /><input type="submit" class="b1" name="update_forums" value="<?php echo $lang_profile['Update forums'] ?>" />
						</div>
					</fieldset>
				</div>
<?php
			}
		}
?>
			</form>
		</div>
	</div>
<?php
	}
?>
	<div class="clearer"></div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
?>
