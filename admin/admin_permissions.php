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

define('ADMIN_CONSOLE', 1);
define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';
if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);
if (isset($_POST['form_sent']))
{
	confirm_referrer('admin_permissions.php');
	$form = array_map('intval', $_POST['form']);
	while (list($key, $input) = @each($form))
	{
		if (array_key_exists('p_'.$key, $configuration) && $configuration['p_'.$key] != $input) $db->query('UPDATE '.$db->prefix.'config SET conf_value='.$input.' WHERE conf_name=\'p_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_permissions.php', 'Permissions updated. Redirecting &hellip;');
}
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Permissions'];
require FORUM_ROOT.'header.php';
generate_admin_menu('permissions');
?>
	<div class="blockform">
	<div class="tab-page" id="permisPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "permisPane" ), 1 )</script>
	<div class="tab-page" id="help-permis-page"><h2 class="tab"><?php echo $lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-permis-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">
									<img src="<?php echo FORUM_ROOT?>img/admin/permissions.png" alt="permissions">
								</td>
								<td>
									<span>
										<?php echo$lang_admin['help_permissions'];?>
									</span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="posting-perms-page"><h2 class="tab"><?php echo$lang_admin['Posting'];?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "posting-perms-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_permissions.php">
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend><?php echo$lang_admin['Posting'];?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo$lang_admin['Bbcode'];?></th>
									<td>
										<input type="radio" name="form[message_bbcode]" value="1"<?php if ($configuration['p_message_bbcode'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[message_bbcode]" value="0"<?php if ($configuration['p_message_bbcode'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow BBCode in posts (recommended).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Image_tag'];?></th>
									<td>
										<input type="radio" name="form[message_img_tag]" value="1"<?php if ($configuration['p_message_img_tag'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[message_img_tag]" value="0"<?php if ($configuration['p_message_img_tag'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow the BBCode [img][/img] tag in posts.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Caps_message'];?></th>
									<td>
										<input type="radio" name="form[message_all_caps]" value="1"<?php if ($configuration['p_message_all_caps'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[message_all_caps]" value="0"<?php if ($configuration['p_message_all_caps'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow a message to contain only capital letters.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Caps_subject'];?></th>
									<td>
										<input type="radio" name="form[subject_all_caps]" value="1"<?php if ($configuration['p_subject_all_caps'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[subject_all_caps]" value="0"<?php if ($configuration['p_subject_all_caps'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow a subject to contain only capital letters.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Require_guest_mail'];?></th>
									<td>
										<input type="radio" name="form[force_guest_email]" value="1"<?php if ($configuration['p_force_guest_email'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[force_guest_email]" value="0"<?php if ($configuration['p_force_guest_email'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Require guests to supply an e-mail address when posting.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Extended_editor'];?></th>
									<td>
										<input type="radio" name="form[ext_editor]" value="1"<?php if ($configuration['p_ext_editor'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[ext_editor]" value="0"<?php if ($configuration['p_ext_editor'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable the built-in extended WYSIWYG editor.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Allow ImageShack image upload</th>
									<td>
										<input type="radio" name="form[is_upload]" value="1"<?php if ($configuration['p_is_upload'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[is_upload]" value="0"<?php if ($configuration['p_is_upload'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow the users to use ImageShack for uploads.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Update'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="sign-perms-page"><h2 class="tab"><?php echo$lang_common['Signatures']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "sign-perms-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_permissions.php">
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend><?php echo$lang_common['Signatures']?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo$lang_admin['Bbcode_in_sig'];?></th>
									<td>
										<input type="radio" name="form[sig_bbcode]" value="1"<?php if ($configuration['p_sig_bbcode'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[sig_bbcode]" value="0"<?php if ($configuration['p_sig_bbcode'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow BBCodes in user signatures.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Image_in_sig'];?></th>
									<td>
										<input type="radio" name="form[sig_img_tag]" value="1"<?php if ($configuration['p_sig_img_tag'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[sig_img_tag]" value="0"<?php if ($configuration['p_sig_img_tag'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow the BBCode [img][/img] tag in user signatures (not recommended).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin['Caps_signature'];?></th>
									<td>
										<input type="radio" name="form[sig_all_caps]" value="1"<?php if ($configuration['p_sig_all_caps'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[sig_all_caps]" value="0"<?php if ($configuration['p_sig_all_caps'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow a signature to contain only capital letters.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Max_sig_length'];?></th>
									<td>
										<input type="text" class="textbox" name="form[sig_length]" size="7" maxlength="5" value="<?php echo $configuration['p_sig_length'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum number of characters a user signature may contain.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Max_sig_lines'];?></th>
									<td>
										<input type="text" class="textbox" name="form[sig_lines]" size="7" maxlength="3" value="<?php echo $configuration['p_sig_lines'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum number of lines a user signature may contain.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Update'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="mod-perms-page"><h2 class="tab"><?php echo$lang_common['Moderated by']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "mod-perms-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_permissions.php">
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend><?php echo$lang_common['Moderated by']?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo$lang_admin['Edit_user_profile'];?></th>
									<td>
										<input type="radio" name="form[mod_edit_users]" value="1"<?php if ($configuration['p_mod_edit_users'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_edit_users]" value="0"<?php if ($configuration['p_mod_edit_users'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow moderators to edit user profiles.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Rename_user'];?></th>
									<td>
										<input type="radio" name="form[mod_rename_users]" value="1"<?php if ($configuration['p_mod_rename_users'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_rename_users]" value="0"<?php if ($configuration['p_mod_rename_users'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow moderators to rename users. Other moderators and administrators are excluded.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Change_user_password'];?></th>
									<td>
										<input type="radio" name="form[mod_change_passwords]" value="1"<?php if ($configuration['p_mod_change_passwords'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_change_passwords]" value="0"<?php if ($configuration['p_mod_change_passwords'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow moderators to change user passwords. Other moderators and administrators are excluded.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Ban_users'];?></th>
									<td>
										<input type="radio" name="form[mod_ban_users]" value="1"<?php if ($configuration['p_mod_ban_users'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_ban_users]" value="0"<?php if ($configuration['p_mod_ban_users'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow moderators to ban users (and edit/remove current bans).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Update'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="reg-perms-page"><h2 class="tab"><?php echo$lang_admin['Registration'];?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "reg-perms-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_permissions.php">
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend><?php echo$lang_admin['Registration'];?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo$lang_admin['Allow_ban_email'];?></th>
									<td>
										<input type="radio" name="form[allow_banned_email]" value="1"<?php if ($configuration['p_allow_banned_email'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[allow_banned_email]" value="0"<?php if ($configuration['p_allow_banned_email'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users to register with or change to a banned e-mail address/domain. If left at it\'s default setting (yes) this action will be allowed, but an alert e-mail will be sent to the mailing list (an effective way of detecting multiple registrations).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Duplicate_emails'];?></th>
									<td>
										<input type="radio" name="form[allow_dupe_email]" value="1"<?php if ($configuration['p_allow_dupe_email'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['Yes']?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[allow_dupe_email]" value="0"<?php if ($configuration['p_allow_dupe_email'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo$lang_common['No']?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Controls whether users should be allowed to register with an e-mail address that another user already has. If allowed, an alert e-mail will be sent to the mailing list if a duplicate is detected.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Update'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>