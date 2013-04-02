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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);

if (isset($_POST['form_sent8']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form8']);
	require FORUM_ROOT.'include/email.php';
	while (list($key, $input) = @each($form))
	{
		if (array_key_exists('o_'.$key, $configuration) && $configuration['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent11']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form11']);
	require FORUM_ROOT.'include/email.php';
	if ($form[upload_path] == '') message('You must enter an upload path.', true);
	if (realpath($form[upload_path]) === false) message('Upload path you entered isn\'t a valid directory.', true);
	$form['upload_path'] = realpath($form['upload_path']) .'/';
	if (!is_dir($form[upload_path])) message('Upload path you entered isn\'t a valid directory.', true);
	if (!is_writable($form[upload_path])) message('Upload path isn\'t writable.', true);
	$form['max_width'] = intval($form['max_width']);
	if ($form['max_width'] <= 0) message('Invalid maximum image width.', true);
	$form['max_height'] = intval($form['max_height']);
	if ($form['max_height'] <= 0) message('Invalid maximum image height.', true);
	$form['max_size'] = intval($form['max_size']);
	if ($form['max_size'] <= 0) message('Invalid maximum image size.', true);
	$form['thumb_width'] = intval($form['thumb_width']);
	if ($form['thumb_width'] <= 0) message('Invalid thumbnail width.', true);
	$form['thumb_height'] = intval($form['thumb_height']);
	if ($form['thumb_height'] <= 0) message('Invalid thumbnail height.', true);
	$form['table_cols'] = intval($form['table_cols']);
	if ($form['table_cols'] <= 0) message('Invalid column number.', true);
	$form['max_post_images'] = intval($form['max_post_images']);
	if ($form['max_post_images'] <= 0) message('Invalid maximum images per post.', true);
	$form['allowed_ext'] = strtolower($form['allowed_ext']);
	while (list($key, $input) = @each($form))
	{
		if (array_key_exists('o_'.$key, $configuration) && $configuration['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent13']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form13']);
	$allow = array_map('trim', $_POST['allow']);
	$limit = array_map('trim', $_POST['limit']);
	while (list($key, $input) = @each($form))
	{
		if ((isset($configuration['o_'.$key])) || ($configuration['o_'.$key] == NULL)) {
			if ($configuration['o_'.$key] != $input)
			{
				if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
				else $value = 'NULL';
				$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
			}
		}
	}
	while (list($id, $set) = @each($allow))
	{
		$db->query('UPDATE '.$db->prefix.'groups SET g_pm='.$set.' WHERE g_id=\''.$id.'\'') or error('Unable to change permissions.', __FILE__, __LINE__, $db->error());
	}
	while (list($id, $set) = @each($limit))
	{
	
		$db->query('UPDATE '.$db->prefix.'groups SET g_pm_limit='.intval($set).' WHERE g_id=\''.$id.'\'') or error('Unable to change permissions.', __FILE__, __LINE__, $db->error());
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent14']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form14']);
	while (list($key, $input) = @each($form))
	{
		if ((isset($configuration['g_'.$key])) || ($configuration['g_'.$key] == NULL)) {
			if ($configuration['g_'.$key] != $input)
			{
				if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
				else $value = 'NULL';
				$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'g_'.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
			}
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent15']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form15']);
	while (list($key, $input) = @each($form))
	{
		if ((isset($configuration['cb_'.$key])) || ($configuration['cb_'.$key] == NULL)) {
			if ($configuration['cb_'.$key] != $input)
			{
				if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
				else $value = 'NULL';
				$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'cb_'.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
			}
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}

if (isset($_POST['form_sent16']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent17']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form17']);
	while (list($key, $input) = @each($form))
	{
		if (array_key_exists('o_'.$key, $configuration) && $configuration['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent18']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form18']);
	while (list($key, $input) = @each($form))
	{
		if (array_key_exists('cal_'.$key, $configuration) && $configuration['cal_'.$key] != $input)
		{
			if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'cal_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent19']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_modules\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form19']);
	while (list($key, $input) = @each($form))
	{
		if (array_key_exists('o_'.$key, $configuration) && $configuration['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_modules.php', 'Options updated. Redirecting &hellip;');
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Modules options'];
$form_name = 'update_options';
require FORUM_ROOT.'header.php';
generate_admin_menu('modules');
?>
<div class="blockform">
	<div class="tab-page" id="modulesPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "modulesPane" ), 1 )</script>
	<div class="tab-page" id="help-mod-page"><h2 class="tab">Help</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-mod-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
								<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/options.png></td>
								<td>
									<span><?php echo $lang_admin['help_modules'] ?></span>
								</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="reputation-mod-page"><h2 class="tab">Reputation</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "reputation-mod-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_modules.php">
				<div class="inform">
				<input type="hidden" name="form_sent8" value="1" />
					<fieldset>
						<legend>Reputation system</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Use reputation system?</th>
									<td>
										<input type="radio" class="radio_yes" name="form8[reputation_enabled]" value="1"<?php if ($configuration['o_reputation_enabled'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form8[reputation_enabled]" value="0"<?php if ($configuration['o_reputation_enabled'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users to give reputation points to other users.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Timeout</th>
									<td>
										<input type="text" class="textbox" name="form8[reputation_timeout]" size="5" maxlength="5" value="<?php echo $configuration['o_reputation_timeout'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Revoting time in seconds.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="save" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="iup-mod-page"><h2 class="tab">Image upload</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "iup-mod-page" ) );</script>
			<div class="box">
				<form method="post" action="admin_modules.php">
					<div class="inform">
					<input type="hidden" name="form_sent11" value="1" />
						<fieldset>
							<legend>General Settings</legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>
										<th scope="row">Upload Directory</th>
										<td>
											<input type="text" class="textbox" name="form11[upload_path]" size="40" maxlength="255" value="<?php echo $configuration['o_upload_path']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is the full path to the directory where all images are uploaded. Be sure that the web server is able to write to it, but preferably not serve content from it (for security reasons).');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">Allowed Extensions</th>
										<td>
											<input type="text" class="textbox" name="form11[allowed_ext]" size="40" maxlength="255" value="<?php echo $configuration['o_allowed_ext']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is a list of all extensions that will be accepted.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
								</table>
							</div>
						</fieldset>
					</div>
					<div class="inform">
						<fieldset>
							<legend>Image Settings</legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>
										<th scope="row">Max width</th>
										<td>
											<input type="text" class="textbox" name="form11[max_width]" size="5" maxlength="5" value="<?php echo $configuration['o_max_width']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum image width. Images wider then this value will be discarded, but it does not affect those that are already uploaded.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">Max height</th>
										<td>
											<input type="text" class="textbox" name="form11[max_height]" size="5" maxlength="5" value="<?php echo $configuration['o_max_height']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum image height. Images taller then this value will be discarded, but it does not affect those that are already uploaded.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">Max size</th>
										<td>
											<input type="text" class="textbox" name="form11[max_size]" size="5" maxlength="7" value="<?php echo $configuration['o_max_size']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed size of images in bytes.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
								</table>
							</div>
						</fieldset>
					</div>
					<div class="inform">
						<fieldset>
							<legend>Thumbnail Settings</legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>
										<th scope="row">Thumbnail width</th>
										<td>
											<input type="text" class="textbox" name="form11[thumb_width]" size="5" maxlength="6" value="<?php echo $configuration['o_thumb_width']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum width of thumbnail images. Changing this value will not affect already uploaded images.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">Thumbnail height</th>
										<td>
											<input type="text" class="textbox" name="form11[thumb_height]" size="5" maxlength="6" value="<?php echo $configuration['o_thumb_height']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum height of thumbnail images. Changing this value will not affect already uploaded images.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
								</table>
							</div>
						</fieldset>
					</div>
					<div class="inform">
						<fieldset>
							<legend>Miscellaneous</legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>

										<th scope="row">Table columns</th>
										<td>
											<input type="text" class="textbox" name="form11[table_cols]" size="5" maxlength="6" value="<?php echo $configuration['o_table_cols']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The number of columns images are displayed in for every post.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">Maximum images per post</th>
										<td>
											<input type="text" class="textbox" name="form11[max_post_images]" size="5" maxlength="6" value="<?php echo $configuration['o_max_post_images']; ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum number of images for any post.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
								</table>
							</div>

						</fieldset>
					</div>
					<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Save'] ?>" /></p>
				</form>
			</div>
	</div>
	<div class="tab-page" id="dig-mod-page"><h2 class="tab">Digests</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "dig-mod-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_modules.php">
				<div class="inform">
					<input type="hidden" name="form_sent19" value="1" />
					<fieldset>
						<legend>E-Mail digests</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">E-Mail digests</th>
									<td>
										<input type="radio" class="radio_yes" name="form19[digests_enable]" value="1"<?php if ($configuration['o_digests_enable'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form19[digests_enable]" value="0"<?php if ($configuration['o_digests_enable'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Here you can enable or disable the email digests system (read the documentation for more info).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Send digests on this day</th>
									<td>
               <select name="form19[weekly_digest_day]">
                    <option value="1"<?php if ($configuration['o_weekly_digest_day'] == '1') echo ' selected="selected"' ?>>Monday</option>
                    <option value="2"<?php if ($configuration['o_weekly_digest_day'] == '2') echo ' selected="selected"' ?>>Tuesday</option>
                    <option value="3"<?php if ($configuration['o_weekly_digest_day'] == '3') echo ' selected="selected"' ?>>Wednesday</option>
                    <option value="4"<?php if ($configuration['o_weekly_digest_day'] == '4') echo ' selected="selected"' ?>>Thursday</option>
                    <option value="5"<?php if ($configuration['o_weekly_digest_day'] == '5') echo ' selected="selected"' ?>>Friday</option>
                    <option value="6"<?php if ($configuration['o_weekly_digest_day'] == '6') echo ' selected="selected"' ?>>Saturday</option>
                    <option value="0"<?php if ($configuration['o_weekly_digest_day'] == '0') echo ' selected="selected"' ?>>Sunday</option>

              </select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Select a week day to send E-Mail digests.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Forum E-Mail divider</th>
									<td>
										<textarea name="form19[forum_email_divider]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_forum_email_divider']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('');" onmouseout="return nd();" alt="" />
									</td>
								</tr>

								<tr>
									<th scope="row">Topic E-Mail divider</th>
									<td>
										<textarea name="form19[topic_email_divider]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_topic_email_divider']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Message E-Mail divider</th>
									<td>
										<textarea name="form19[message_email_divider]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_message_email_divider']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="maps-mod-page"><h2 class="tab">User Maps</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "maps-mod-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_modules.php">
				<div class="inform">
					<input type="hidden" name="form_sent17" value="1" />
					<fieldset>
						<legend>Users Map</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Google Maps API key</th>
									<td>
										<input type="text" class="textbox" name="form17[um_key]" size="50" value="<?php echo convert_htmlspecialchars($configuration['o_um_key']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('See <a href=\'http://www.google.com/apis/maps/signup.html\'>Google Maps API - Sign Up</a> to get a free key.',STICKY,MOUSEOFF);" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Enable user map</th>
									<td>
										<input type="radio" class="radio_yes" name="form17[um_enable]" value="1"<?php if ($configuration['o_um_enable'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form17[um_enable]" value="0"<?php if ($configuration['o_um_enable'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable the user map (in main menu and `Profile` page).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>

								<tr>
									<th scope="row">Default zoom level</th>
									<td>
										<input type="text" class="textbox" name="form17[um_default_zoom]" size="2" value="<?php echo convert_htmlspecialchars($configuration['o_um_default_zoom']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Zoom applyed to the map by default. Choose a number between 0 (maximal zoom) and 17 (minimal zoom).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Default latitude</th>
									<td>
										<input type="text" class="textbox" name="form17[um_default_lat]" size="25" value="<?php echo convert_htmlspecialchars($configuration['o_um_default_lat']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Default map\'s center latitude.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Default longitude</th>
									<td>
										<input type="text" class="textbox" name="form17[um_default_lng]" size="25" value="<?php echo convert_htmlspecialchars($configuration['o_um_default_lng']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Default map\'s center longitude.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="gal-mod-page"><h2 class="tab">Gallery</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "gal-mod-page" ) );</script>
		<div class="box">
			<?php if ($configuration['g_rep_upload'] == "") echo '<div class="inbox"><table border="1" style="color:#FFFFFF;background-color:#ff0000;"><tr><td align="center" style="background-color:#ff0000;"><b>The module "Photo Gallery" is not installed.</b></tb></tr></table></div>'; ?>
			<form method="post" action="admin_modules.php">
				<div class="inform">
					<input type="hidden" name="form_sent14" value="1" />
					<fieldset>
						<legend>Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable</th>
								<td>
									<input type="text" class="textbox" name="form14[gallery_enable]" size="30" maxlength="255" value="<?php echo $configuration['g_gallery_enable'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Turn on the gallery or off. 1 for on, 0 for off.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
								<th scope="row">Upload directory</th>
								<td>
									<input type="text" class="textbox" name="form14[rep_upload]" size="30" maxlength="255" value="<?php echo $configuration['g_rep_upload'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The upload directory for pictures (relative to the forum root directory). PHP must have write permissions to this directory. (i.e. img/gallery)');" onmouseout="return nd();" alt="" />
								</td>
							<tr>
								<th scope="row">Pictures per page default</th>
								<td>
									<input type="text" class="textbox" name="form14[disp_img_default]" size="5" maxlength="255" value="<?php echo $configuration['g_disp_img_default'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The default number of pictures to display per page in a gallery.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>FTP Upload Server</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">FTP Upload Server</th>
								<td>
									<input type="radio" name="form14[ftp_upload]" value="1"<?php if ($configuration['g_ftp_upload'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form14[ftp_upload]" value="0"<?php if ($configuration['g_ftp_upload'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If enabled, Photo Gallery will upload all pictures to the indicated external server.<br /><strong>Must be actived before post any picture in gallery.</strong>');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">HTTP Base URL of FTP</th>
								<td>
									<input type="text" class="textbox" name="form14[ftp_site]" size="30" maxlength="255" value="<?php echo $configuration['g_ftp_site'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The complete URL of the website who host picture without trailing slash (i.e. http://www.mydomain.com).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">FTP Upload directory</th>
								<td>
									<input type="text" class="textbox" name="form14[ftp_rep]" size="30" maxlength="255" value="<?php echo $configuration['g_ftp_rep'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The FTP upload directory for pictures (relative to the FTP Base URL root directory) without trailing slash (i.e. img/gallery). PHP must have write permissions to this directory.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">FTP Server</th>
								<td>
									<input type="text" class="textbox" name="form14[ftp_host]" size="30" maxlength="255" value="<?php echo $configuration['g_ftp_host'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Name of FTP server? Use a DNS name without a protocol (i.e. ftp.upload.com)');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">FTP User name</th>
								<td>
									<input type="text" class="textbox" name="form14[ftp_login]" size="30" maxlength="255" value="<?php echo $configuration['g_ftp_login'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Username for ftp access. If the username is part of a Windows Active Directory do not forget to qualify the name with the NT domain.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">FTP User pass</th>
								<td>
									<input type="text" class="textbox" name="form14[ftp_pass]" size="30" maxlength="255" value="<?php echo $configuration['g_ftp_pass'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The user password for ftp access.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Pictures</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Max width</th>
								<td>
									<input type="text" class="textbox" name="form14[max_width]" size="5" maxlength="255" value="<?php echo $configuration['g_max_width'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed width of pictures in pixels (auto-resize if bigger).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Max height</th>
								<td>
									<input type="text" class="textbox" name="form14[max_height]" size="5" maxlength="255" value="<?php echo $configuration['g_max_height'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed height of pictures in pixels (auto-resize if bigger).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Max size</th>
								<td>
									<input type="text" class="textbox" name="form14[max_size]" size="5" maxlength="255" value="<?php echo $configuration['g_max_size'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed size of pictures in bytes (1048576 is recommended).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Thumbs</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Max width</th>
								<td>
									<input type="text" class="textbox" name="form14[max_width_thumbs]" size="5" maxlength="255" value="<?php echo $configuration['g_max_width_thumbs'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed width of thumbs in pixels.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Max height</th>
								<td>
									<input type="text" class="textbox" name="form14[max_height_thumbs]" size="5" maxlength="255" value="<?php echo $configuration['g_max_height_thumbs'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed height of thumbs in pixels.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Margin</th>
								<td>
									<input type="text" class="textbox" name="form14[thumbs_margin]" size="5" maxlength="255" value="<?php echo $configuration['g_thumbs_margin'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Margin between image and border.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Background color</th>
								<td>
									<input type="text" class="textbox" name="form14[thumbs_bgcolor]" size="5" maxlength="255" value="<?php echo $configuration['g_thumbs_bgcolor'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Background color of thumbs (i.e. F5F5F5).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Border color</th>
								<td>
									<input type="text" class="textbox" name="form14[thumbs_bordercolor]" size="5" maxlength="255" value="<?php echo $configuration['g_thumbs_bordercolor'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Border color of thumbs (i.e. 666666).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="save" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="cbox-mod-page"><h2 class="tab">Chatbox</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "cbox-mod-page" ) );</script>
		<div class="box">
			<?php if ($configuration['cb_height'] == "") echo '<div class="inbox"><table border="1" style="color:#FFFFFF;background-color:#ff0000;"><tr><td align="center" style="background-color:#ff0000;"><b>The module "Chatbox" is not installed.</b></tb></tr></table></div>'; ?>
			<form method="post" action="admin_modules.php">
				<div class="inform">
					<input type="hidden" name="form_sent15" value="1" />
					<fieldset>
						<legend>Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable/Disable</th>
								<td>
									<input type="text" class="textbox" name="form15[enable]" size="30" maxlength="255" value="<?php echo $configuration['cb_enable'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Turn on the chatbox on or off or off. 1 for on, 0 for off.');" onmouseout="return nd();" alt="" />
								</td>
							<tr>
							<tr>
								<th scope="row">ChatBox Height</th>
								<td>
									<input type="text" class="textbox" name="form15[height]" size="5" maxlength="255" value="<?php echo $configuration['cb_height'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The Height in pixel of your ChatBox.');" onmouseout="return nd();" alt="" />
								</td>
							<tr>
								<th scope="row">Max Length of Messages</th>
								<td>
									<input type="text" class="textbox" name="form15[msg_maxlength]" size="5" maxlength="255" value="<?php echo $configuration['cb_msg_maxlength'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The max length of messages.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Max Messages in ChatBox</th>
								<td>
									<input type="text" class="textbox" name="form15[max_msg]" size="5" maxlength="255" value="<?php echo $configuration['cb_max_msg'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of maximum messages.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Messages display (Parsing)</th>
								<td>
									<textarea name="form15[disposition]" rows="5" cols="30"><?php echo $configuration['cb_disposition'] ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('How to display the messages in your ChatBox. You can use HTML.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="save" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>

	</div>
	<div class="tab-page" id="cal-mod-page"><h2 class="tab">Calendar</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "cal-mod-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_modules.php">
				<div class="inform">
					<input type="hidden" name="form_sent18" value="1" />
					<fieldset>
						<legend>Calendar options</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Start view</th>
								<td>
               <select name="form18[start_view]">
                    <option value="posts"<?php if ($configuration['cal_start_view'] == "posts") echo ' selected="selected"' ?>>posts</option>
                    <option value="events"<?php if ($configuration['cal_start_view'] == "events") echo ' selected="selected"' ?>>events</option>
              </select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When calendar is opened, which view starts? (posts or events).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Show mini calendars</th>
								<td>
									<input type="radio" name="form18[show_cal]" value="yes"<?php if ($configuration['cal_show_cal'] == 'yes') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form18[show_cal]" value="no"<?php if ($configuration['cal_show_cal'] == 'no') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show two small callendars of the previous and next month.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>


							<tr>
								<th scope="row">User add events</th>
								<td>
									<input type="radio" name="form18[user_add]" value="yes"<?php if ($configuration['cal_user_add'] == 'yes') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form18[user_add]" value="no"<?php if ($configuration['cal_user_add'] == 'no') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable the users to add events.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Moderators add events</th>
								<td>
									<input type="radio" name="form18[mod_add]" value="yes"<?php if ($configuration['cal_mod_add'] == 'yes') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form18[mod_add]" value="no"<?php if ($configuration['cal_mod_add'] == 'no') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable the moderators to add events.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Moderators edit events</th>
								<td>
									<input type="radio" name="form18[mod_edit]" value="yes"<?php if ($configuration['cal_mod_edit'] == 'yes') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form18[mod_edit]" value="no"<?php if ($configuration['cal_mod_edit'] == 'no') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable the moderators to edit events.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Start day of the week</th>
								<td>
               <select name="form18[start_day]">
                    <option value="M"<?php if ($configuration['cal_start_day'] == 'M') echo ' selected="selected"' ?>>Monday</option>
                    <option value="S"<?php if ($configuration['cal_start_day'] == 'S') echo ' selected="selected"' ?>>Sunday</option>
              </select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The day starting the week.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="save" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="pm-mod-page"><h2 class="tab">Private Mess.</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "pm-mod-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_modules.php">
				<div class="inform">
					<input type="hidden" name="form_sent13" value="1" />
					<fieldset>
						<legend>Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable private messaging</th>
								<td>
									<input type="radio" name="form13[pms_enabled]" value="1"<?php if ($configuration['o_pms_enabled'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form13[pms_enabled]" value="0"<?php if ($configuration['o_pms_enabled'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If no all private messaging functions will be disabled.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Messages per page</th>
								<td>
									<input type="text" class="textbox" name="form13[pms_mess_per_page]" size="7" maxlength="255" value="<?php echo $configuration['o_pms_mess_per_page'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is the numebr of messages that will be displayed per page in private messaging views.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Permissions</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<?php
							$result = $db->query('SELECT g_id, g_title, g_pm, g_pm_limit FROM '.$db->prefix.'groups WHERE g_id>'.USER_ADMIN.' AND g_id != 3 ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
							while ($cur_group = $db->fetch_assoc($result))
							{
							?>
							<tr> 
								<th scope="row"><?php echo $cur_group['g_title'] ?></th>
								<td>
									<input type="radio" name="allow[<?php echo $cur_group['g_id'] ?>]" value="1"<?php if ($cur_group['g_pm'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="allow[<?php echo $cur_group['g_id'] ?>]" value="0"<?php if ($cur_group['g_pm'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow this group to use private messaging.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">&nbsp;</th>
								<td>
									Messages limit: <input type="text" class="textbox" name="limit[<?php echo $cur_group['g_id'] ?>]" size="10" maxlength="10" value="<?php echo $cur_group['g_pm_limit'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is the number of messages each user is allowed in their inbox.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<?
							}
							?>
							
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save" class="b1" value="<?php echo $lang_admin['Save'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>