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

if (isset($_POST['form_sent1']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form1']);
	require FORUM_ROOT.'include/email.php';
	if ($form['board_title'] == '') message('You must enter a board title.');
	if (substr($form['base_url'], -1) == '/') $form['base_url'] = substr($form['base_url'], 0, -1);
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent2']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form2']);
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent3']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form3']);
	require FORUM_ROOT.'include/email.php';
	if ($form['timeout_online'] >= $form['timeout_visit']) message('The value of "Timeout online" must be smaller than the value of "Timeout visit".');
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent4']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form4']);
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent5']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form5']);
	require FORUM_ROOT.'include/email.php';
	if ($form['additional_navlinks'] != '') $form['additional_navlinks'] = trim(forum_linebreaks($form['additional_navlinks']));
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent6']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form6']);
	require FORUM_ROOT.'include/email.php';
	if ($form['mailing_list'] != '') $form['mailing_list'] = strtolower(preg_replace('/[\s]/', '', $form['mailing_list']));
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent7']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form7']);
	require FORUM_ROOT.'include/email.php';
	if (substr($form['avatars_dir'], -1) == '/') $form['avatars_dir'] = substr($form['avatars_dir'], 0, -1);
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent9']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form9']);
	require FORUM_ROOT.'include/email.php';
	$form['admin_email'] = strtolower($form['admin_email']);
	if (!is_valid_email($form['admin_email'])) message('The admin e-mail address you entered is invalid.');
	$form['webmaster_email'] = strtolower($form['webmaster_email']);
	if (!is_valid_email($form['webmaster_email'])) message('The webmaster e-mail address you entered is invalid.');

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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent10']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form10']);
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
if (isset($_POST['form_sent12']))
{
	if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('#/admin_options\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form12']);
	require FORUM_ROOT.'include/email.php';
	if ($form['announcement_message'] != '') $form['announcement_message'] = forum_linebreaks($form['announcement_message']);
	else
	{
		$form['announcement_message'] = 'Enter your announcement here.';
		if ($form['announcement'] == '1') $form['announcement'] = '0';
	}
	if ($form['advertisement_message'] != '') $form['advertisement_message'] = forum_linebreaks($form['advertisement_message']);
	else
	{
		$form['advertisement_message'] = 'Enter your advertisement here.';
		if ($form['advertisement'] == '1') $form['advertisement'] = '0';
	}
		if ($form['information_message'] != '') $form['information_message'] = forum_linebreaks($form['information_message']);
	else
	{
		$form['information_message'] = 'Enter your information here.';
		if ($form['information'] == '1') $form['information'] = '0';
	}
	if ($form['guest_information_message'] != '') $form['guest_information_message'] = forum_linebreaks($form['guest_information_message']);
	else
	{
		$form['guest_information_message'] = 'Enter your information here.';
		if ($form['guest_information'] == '1') $form['guest_information'] = '0';
	}
	if ($form['rules_message'] != '') $form['rules_message'] = forum_linebreaks($form['rules_message']);
	else
	{
		$form['rules_message'] = 'Enter your rules here.';
		if ($form['rules'] == '1') $form['rules'] = '0';
	}
	if ($form['maintenance_message'] != '') $form['maintenance_message'] = forum_linebreaks($form['maintenance_message']);
	else
	{
		$form['maintenance_message'] = 'The forums are temporarily down for maintenance. Please try again in a few minutes.\n\n/Administrator';
		if ($form['maintenance'] == '1') $form['maintenance'] = '0';
	}
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
	redirect(FORUM_ROOT.'admin/admin_options.php', 'Options updated. Redirecting &hellip;');
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Options'];
$form_name = 'update_options';
require FORUM_ROOT.'header.php';
generate_admin_menu('options');
?>
<div class="blockform">
	<div class="tab-page" id="configPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "configPane" ), 1 )</script>
	<div class="tab-page" id="help-opt-page"><h2 class="tab">Help</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-opt-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
								<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/options.png></td>
								<td>
									<span><?php echo $lang_admin['help_options'] ?></span>
								</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="general-page"><h2 class="tab">General</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "general-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent1" value="1" />
					<fieldset>
						<legend>Essentials</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Board Title</th>
									<td>
										<input type="text" class="textbox" name="form1[board_name]" size="35" maxlength="255" value="<?php echo convert_htmlspecialchars($configuration['o_board_name']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('What shows up in the title of every page on your browser');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Logo URL</th>
									<td>
										<input type="text" class="textbox" name="form1[board_title]" size="35" maxlength="255" value="<?php echo convert_htmlspecialchars($configuration['o_board_title']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The URL of the logo or board banner.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Board description</th>
									<td>
										<input type="text" class="textbox" name="form1[board_desc]" size="35" maxlength="255" value="<?php echo convert_htmlspecialchars($configuration['o_board_desc']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('A short description of this bulletin board (shown at the top of every page). This field may contain HTML.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Base URL</th>
									<td>
										<input type="text" class="textbox" name="form1[base_url]" size="35" maxlength="100" value="<?php echo $configuration['o_base_url'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The complete URL of the forum without trailing slash (i.e. http://www.mydomain.com/forums). This <strong>must</strong> be correct in order for all admin and moderator features to work. If you get `Bad referer` errors, it\'s probably incorrect.');" onmouseout="return nd();" alt="" />

									</td>
								</tr>
								<tr>
	<th scope="row">Server Time Zone</th>
           <td>
               <select name="form1[server_timezone]">
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
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The timezone of the server where PowerBB Forum is installed.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Default language</th>
									<td>
										<select name="form1[default_lang]">
<?php
		$languages = array();
		$d = dir(FORUM_ROOT.'lang');
		while (($entry = $d->read()) !== false)
		{
			if ($entry != '.' && $entry != '..' && is_dir(FORUM_ROOT.'lang/'.$entry) && file_exists(FORUM_ROOT.'lang/'.$entry.'/common.php'))
				$languages[] = $entry;
		}
		$d->close();
		@natsort($languages);
		while (list(, $temp) = @each($languages))
		{
			if ($configuration['o_default_lang'] == $temp) echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
			else echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
		}
?>
										</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is the default language style used if the visitor is a guest or a user that hasn\'t changed from the default in his/her profile. If you remove a language pack, this must be updated.');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="seo-page"><h2 class="tab">SEO</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "seo-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent2" value="1" />
					<fieldset>
						<legend>Search Engine Optimisations</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">URL Rewriting</th>
									<td>
										<input type="radio" class="radio_yes" name="form2[rewrite_urls]" value="1"<?php if ($configuration['o_rewrite_urls'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form2[rewrite_urls]" value="0"<?php if ($configuration['o_rewrite_urls'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Use URL rewriting engine to improve search engine hits (must have installed mod_rewrite into Apache and rename the file htaccess.txt to .htaccess.', CAPTION, 'ATTENTION');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Global meta description</th>
									<td>
										<input type="text" class="textbox" name="form2[board_meta]" size="35" maxlength="255" value="<?php echo convert_htmlspecialchars($configuration['o_board_meta']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If you want to improve your search engine access, plese input the meta description of your site.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>

								<tr>
									<th scope="row">Global meta keywords</th>
									<td>
										<input type="text" class="textbox" name="form2[board_meta_keywords]" size="35" maxlength="255" value="<?php echo convert_htmlspecialchars($configuration['o_board_meta_keywords']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Input the keywords that best describe your site (separated by comma).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Show author meta tag</th>
									<td>
										<input type="radio" class="radio_yes" name="form2[board_meta_author]" value="1"<?php if ($configuration['o_board_meta_author'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form2[board_meta_author]" value="0"<?php if ($configuration['o_board_meta_author'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Display the meta author tag in every page?');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="time-page"><h2 class="tab">Time</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "time-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent3" value="1" />
					<fieldset>
						<legend>Time and timeouts</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Time format</th>
									<td>
										<input type="text" class="textbox" name="form3[time_format]" size="15" maxlength="25" value="<?php echo convert_htmlspecialchars($configuration['o_time_format']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('[Current format: <?php echo date($configuration['o_time_format']) ?>]&nbsp;See the PHP manual for formatting options.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Date format</th>
									<td>
										<input type="text" class="textbox" name="form3[date_format]" size="15" maxlength="25" value="<?php echo convert_htmlspecialchars($configuration['o_date_format']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('[Current format: <?php echo date($configuration['o_date_format']) ?>]&nbsp;See the PHP manual for formatting options.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Visit timeout</th>
									<td>
										<input type="text" class="textbox" name="form3[timeout_visit]" size="5" maxlength="5" value="<?php echo $configuration['o_timeout_visit'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of seconds a user must be idle before his/hers last visit data is updated (primarily affects new message indicators).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Online timeout</th>
									<td>
										<input type="text" class="textbox" name="form3[timeout_online]" size="5" maxlength="5" value="<?php echo $configuration['o_timeout_online'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of seconds a user must be idle before being removed from the online users list.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Redirect time</th>
									<td>
										<input type="text" class="textbox" name="form3[redirect_delay]" size="5" maxlength="3" value="<?php echo $configuration['o_redirect_delay'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of seconds to wait when redirecting. If set to 0, no redirect page will be displayed (not recommended).');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="display-page"><h2 class="tab">Display</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "display-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent4" value="1" />
					<fieldset>
						<legend>Display</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Version number</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[show_version]" value="1"<?php if ($configuration['o_show_version'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[show_version]" value="0"<?php if ($configuration['o_show_version'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show version number in footer.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Show online list</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[onlist_enable]" value="1"<?php if ($configuration['o_onlist_enable'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[onlist_enable]" value="0"<?php if ($configuration['o_onlist_enable'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show a page with current online users, their actions and IPs in the main menu.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Board Statistics</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[boardstats_enable]" value="1"<?php if ($configuration['o_boardstats_enable'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[boardstats_enable]" value="0"<?php if ($configuration['o_boardstats_enable'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show board statistics in footer.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">User info in posts</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[show_user_info]" value="1"<?php if ($configuration['o_show_user_info'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[show_user_info]" value="0"<?php if ($configuration['o_show_user_info'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show information about the poster under the username in topic view. The information affected is location, register date, post count and the contact links (e-mail and URL).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">User post count</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[show_post_count]" value="1"<?php if ($configuration['o_show_post_count'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[show_post_count]" value="0"<?php if ($configuration['o_show_post_count'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show the number of posts a user has made (affects topic view, profile and userlist).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Smilies</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[smilies]" value="1"<?php if ($configuration['o_smilies'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[smilies]" value="0"<?php if ($configuration['o_smilies'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Convert smilies to small icons.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Smilies in signatures</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[smilies_sig]" value="1"<?php if ($configuration['o_smilies_sig'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[smilies_sig]" value="0"<?php if ($configuration['o_smilies_sig'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Convert smilies to small icons in user signatures.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Make clickable links</th>
									<td>
										<input type="radio" class="radio_yes" name="form4[make_links]" value="1"<?php if ($configuration['o_make_links'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form4[make_links]" value="0"<?php if ($configuration['o_make_links'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, PowerBB will automatically detect any URL\'s in posts and make them clickable hyperlinks.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Most active users</th>
									<td>
										<input type="text" class="textbox" name="form4[most_active]" size="3" maxlength="2" value="<?php echo $configuration['o_most_active'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Show top of most active users (specify the number to show, 0 to disable) in footer.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Topic review</th>
									<td>
										<input type="text" class="textbox" name="form4[topic_review]" size="3" maxlength="3" value="<?php echo $configuration['o_topic_review'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Maximum number of posts to display when posting (newest first). 0 to disable.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Topics per page default</th>
									<td>
										<input type="text" class="textbox" name="form4[disp_topics_default]" size="3" maxlength="3" value="<?php echo $configuration['o_disp_topics_default'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The default number of topics to display per page in a forum. Users can personalize this setting.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Posts per page default</th>
									<td>
										<input type="text" class="textbox" name="form4[disp_posts_default]" size="3" maxlength="3" value="<?php echo $configuration['o_disp_posts_default'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The default number of posts to display per page in a topic. Users can personalize this setting.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Indent size</th>
									<td>
										<input type="text" class="textbox" name="form4[indent_num_spaces]" size="3" maxlength="3" value="<?php echo $configuration['o_indent_num_spaces'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If set to 8, a regular tab will be used when displaying text within the [code][/code] tag. Otherwise this many spaces will be used to indent the text.');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="features-page"><h2 class="tab">Features</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "features-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent5" value="1" />
					<fieldset>
						<legend>Features</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Quick post</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[quickpost]" value="1"<?php if ($configuration['o_quickpost'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[quickpost]" value="0"<?php if ($configuration['o_quickpost'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, PowerBB will add a quick post form at the bottom of topics. This way users can post directly from the topic view.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Invitations</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[invitations_enable]" value="1"<?php if ($configuration['o_invitations_enable'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[invitations_enable]" value="0"<?php if ($configuration['o_invitations_enable'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Here you can enable or disable the invitation system.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">RSS Feeds</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[rss_type]" value="1"<?php if ($configuration['o_rss_type'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>RSS 1.0</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[rss_type]" value="2"<?php if ($configuration['o_rss_type'] == '2') echo ' checked="checked"' ?> />&nbsp;<strong>RSS 2.0</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form[rss_type]" value="0"<?php if ($configuration['o_rss_type'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>no RSS</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Here you can choose which type of RSS feeds you want to provide to your forum readers.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Active topics</th>
									<td>
										<input type="text" class="textbox" name="form5[active_topics_nr]" size="3" maxlength="2" value="<?php echo $configuration['o_active_topics_nr'] ?>" />
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum number of active topics to show on index page (0 to disable).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Collapsable categories</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[coll_cat]" value="1"<?php if ($configuration['o_coll_cat'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[coll_cat]" value="0"<?php if ($configuration['o_coll_cat'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Whether do you want collapsable categories in index page.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Full clickable row</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[click_row]" value="1"<?php if ($configuration['o_click_row'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[click_row]" value="0"<?php if ($configuration['o_click_row'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Whether do you want the entire topic and forum row to act like a link or just the actual text.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Users online</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[users_online]" value="1"<?php if ($configuration['o_users_online'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[users_online]" value="0"<?php if ($configuration['o_users_online'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Display info on the index page about guests and registered users currently browsing the forums.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><a name="censoring">Censor words</a></th>
									<td>
										<input type="radio" class="radio_yes" name="form5[censoring]" value="1"<?php if ($configuration['o_censoring'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[censoring]" value="0"<?php if ($configuration['o_censoring'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable this to censor specific words in the forum. See `Censoring` for more info.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><a name="ranks">User ranks</a></th>
									<td>
										<input type="radio" class="radio_yes" name="form5[ranks]" value="1"<?php if ($configuration['o_ranks'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[ranks]" value="0"<?php if ($configuration['o_ranks'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable this to use user ranks. See `Ranks` for more info.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">User has posted earlier</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[show_dot]" value="1"<?php if ($configuration['o_show_dot'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[show_dot]" value="0"<?php if ($configuration['o_show_dot'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This feature displays a dot in front of topics in view_forum.php in case the currently logged in user has posted in that topic earlier. Disable if you are experiencing high server load.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Quick jump</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[quickjump]" value="1"<?php if ($configuration['o_quickjump'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[quickjump]" value="0"<?php if ($configuration['o_quickjump'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable the quick jump (jump to forum) drop list.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">GZip output</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[gzip]" value="1"<?php if ($configuration['o_gzip'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[gzip]" value="0"<?php if ($configuration['o_gzip'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If enabled, PowerBB will gzip the output sent to browsers. This will reduce bandwidth usage, but use a little more CPU. This feature requires that PHP is configured with zlib (--with-zlib). Note: If you already have one of the Apache modules mod_gzip or mod_deflate set up to compress PHP scripts, you should disable this feature.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Search all forums</th>
									<td>
										<input type="radio" class="radio_yes" name="form5[search_all_forums]" value="1"<?php if ($configuration['o_search_all_forums'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form5[search_all_forums]" value="0"<?php if ($configuration['o_search_all_forums'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When disabled, searches will only be allowed in one forum at a time. Disable if server load is high due to excessive searching.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Additional menu items</th>
									<td>
										<textarea name="form5[additional_navlinks]" rows="3" cols="40"><?php echo convert_htmlspecialchars($configuration['o_additional_navlinks']) ?></textarea>
										&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('By entering HTML hyperlinks into this textbox, any number of items can be added to the navigation menu at the top of all pages. Read the documentation about the format. Separate entries with a linebreak.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Polls</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Number of poll fields</th>
									<td>
										<input type="text" class="textbox" name="form5[poll_max_fields]" size="4" value="<?php echo $configuration['o_poll_max_fields'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The number of options you want avaliable for new polls.');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="reports-page"><h2 class="tab">Reports</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "reports-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent6" value="1" />
					<fieldset>
						<legend>Reports</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Report method</th>
									<td>
										<input type="radio" name="form6[report_method]" value="0"<?php if ($configuration['o_report_method'] == '0') echo ' checked="checked"' ?> />&nbsp;Internal&nbsp;&nbsp;&nbsp;<input type="radio" name="form6[report_method]" value="1"<?php if ($configuration['o_report_method'] == '1') echo ' checked="checked"' ?> />&nbsp;E-mail&nbsp;&nbsp;&nbsp;<input type="radio" name="form[report_method]" value="2"<?php if ($configuration['o_report_method'] == '2') echo ' checked="checked"' ?> />&nbsp;Both
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Select the method for handling topic/post reports. You can choose whether topic/post reports should be handled by the internal report system,  e-mailed to the addresses on the mailing list (see below) or both.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Report new registrations</th>
									<td>
										<input type="radio" class="radio_yes" name="form6[regs_report]" value="1"<?php if ($configuration['o_regs_report'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form6[regs_report]" value="0"<?php if ($configuration['o_regs_report'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If enabled, PowerBB will notify users on the mailing list (see below) when a new user registers in the forums.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Mailing list</th>
									<td>
										<textarea name="form6[mailing_list]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_mailing_list']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('A comma separated list of subscribers. The people on this list are the recipients of reports.');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="avatars-page"><h2 class="tab">Avatars</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "avatars-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent7" value="1" />
					<fieldset>
						<legend>Avatars</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Use avatars</th>
									<td>
										<input type="radio" class="radio_yes" name="form7[avatars]" value="1"<?php if ($configuration['o_avatars'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form7[avatars]" value="0"<?php if ($configuration['o_avatars'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, users will be able to upload an avatar which will be displayed under their title/rank.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Upload directory</th>
									<td>
										<input type="text" class="textbox" name="form7[avatars_dir]" size="30" maxlength="50" value="<?php echo convert_htmlspecialchars($configuration['o_avatars_dir']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The upload directory for avatars (relative to the PowerBB Forum root directory). PHP must have write permissions to this directory.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Max width</th>
									<td>
										<input type="text" class="textbox" name="form7[avatars_width]" size="5" maxlength="5" value="<?php echo $configuration['o_avatars_width'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed width of avatars in pixels (60 is recommended).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Max height</th>
									<td>
										<input type="text" class="textbox" name="form7[avatars_height]" size="5" maxlength="5" value="<?php echo $configuration['o_avatars_height'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed height of avatars in pixels (60 is recommended).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Max size</th>
									<td>
										<input type="text" class="textbox" name="form7[avatars_size]" size="5" maxlength="6" value="<?php echo $configuration['o_avatars_size'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The maximum allowed size of avatars in bytes (10240 is recommended).');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="email-page"><h2 class="tab">E-Mail</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "email-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent9" value="1" />
					<fieldset>
						<legend>E-mail</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Admin e-mail</th>
									<td>
										<input type="text" class="textbox" name="form9[admin_email]" size="30" maxlength="50" value="<?php echo $configuration['o_admin_email'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The e-mail address of the forum administrator.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Webmaster e-mail</th>
									<td>
										<input type="text" class="textbox" name="form9[webmaster_email]" size="30" maxlength="50" value="<?php echo $configuration['o_webmaster_email'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is the address that all e-mails sent by the forum will be addressed from.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Subscriptions</th>
									<td>
										<input type="radio" class="radio_yes" name="form9[subscriptions]" value="1"<?php if ($configuration['o_subscriptions'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form9[subscriptions]" value="0"<?php if ($configuration['o_subscriptions'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable users to subscribe to topics (recieve e-mail when someone replies).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">SMTP server address</th>
									<td>
										<input type="text" class="textbox" name="form9[smtp_host]" size="30" maxlength="100" value="<?php echo convert_htmlspecialchars($configuration['o_smtp_host']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The address of an external SMTP server to send e-mails with. You can specify a custom port number if the SMTP server doesn\'t run on the default port 25 (example: mail.myhost.com:3580). Leave blank to use the local mail program.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">SMTP username</th>
									<td>
										<input type="text" class="textbox" name="form9[smtp_user]" size="30" maxlength="50" value="<?php echo convert_htmlspecialchars($configuration['o_smtp_user']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Username for SMTP server. Only enter a username if it is required by the SMTP server (most servers <strong>do not</strong> require authentication).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">SMTP password</th>
									<td>
										<input type="text" class="textbox" name="form9[smtp_pass]" size="30" maxlength="50" value="<?php echo convert_htmlspecialchars($configuration['o_smtp_pass']) ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Password for SMTP server. Only enter a password if it is required by the SMTP server (most servers <strong>do not</strong> require authentication).');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="registration-page"><h2 class="tab">Registration</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "registration-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent10" value="1" />
					<fieldset>
						<legend>Registration</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Allow new registrations</th>
									<td>
										<input type="radio" class="radio_yes" name="form10[regs_allow]" value="1"<?php if ($configuration['o_regs_allow'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form10[regs_allow]" value="0"<?php if ($configuration['o_regs_allow'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Controls whether this forum accepts new registrations. Disable only under special circumstances.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">E-Mail Verify registrations</th>
									<td>
										<input type="radio" class="radio_yes" name="form10[regs_verify]" value="1"<?php if ($configuration['o_regs_verify'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form10[regs_verify]" value="0"<?php if ($configuration['o_regs_verify'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, users are e-mailed a random password when they register. They can then log in and change the password in their profile if they see fit. This feature also requires users to verify new e-mail addresses if they choose to change from the one they registered with. This is an effective way of avoiding registration abuse and making sure that all users have `correct` e-mail addresses in their profiles.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Image Verify registrations</th>
									<td>
										<input type="radio" class="radio_yes" name="form10[regs_verify_image]" value="1"<?php if ($configuration['o_regs_verify_image'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form10[regs_verify_image]" value="0"<?php if ($configuration['o_regs_verify_image'] == '0') echo 'checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, users are forced to match text with an image in order to register. This is an effective way of avoiding registration abuse and not forcing all users to verify via email which can be time consuming.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Use forum rules</th>
									<td>
										<input type="radio" class="radio_yes" name="form10[rules]" value="1"<?php if ($configuration['o_rules'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form10[rules]" value="0"<?php if ($configuration['o_rules'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, users must agree to a set of rules when registering (enter text below). The rules will always be available through a link in the navigation table at the top of every page.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Rules</th>
									<td>
										<textarea name="form10[rules_message]" rows="10" cols="40"><?php echo convert_htmlspecialchars($configuration['o_rules_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Here you can enter any rules or other information that the user must review and accept when registering. If you enabled rules above you have to enter something here, otherwise it will be disabled. This text will not be parsed like regular posts and thus may contain HTML.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Invitation Message</th>
									<td>
										<textarea name="form10[invitation_message]" rows="5" cols="40"><?php echo convert_htmlspecialchars($configuration['o_invitation_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Here you can enter a message that users will get along with their invitation.<br />It will be added to the text that the inviter writes.');" onmouseout="return nd();" alt="" />
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
	<div class="tab-page" id="others-page"><h2 class="tab">Others</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "others-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_options.php">
				<div class="inform">
				<input type="hidden" name="form_sent12" value="1" />
					<fieldset>
						<legend>Advertisement</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Display Advertisement</th>
									<td>
										<input type="radio" class="radio_yes" name="form12[advertisement]" value="1"<?php if ($configuration['o_advertisement'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form12[advertisement]" value="0"<?php if ($configuration['o_advertisement'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable this to display the below message in the forums.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Advertisement message</th>
									<td>
										<textarea name="form12[advertisement_message]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_advertisement_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This text will not be parsed like regular posts and thus may contain HTML.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Information</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Display Information</th>
									<td>
										<input type="radio" class="radio_yes" name="form12[information]" value="1"<?php if ($configuration['o_information'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form12[information]" value="0"<?php if ($configuration['o_information'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable this to display the below message in the forums.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Information message</th>
									<td>
										<textarea name="form12[information_message]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_information_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This text will not be parsed like regular posts and thus may contain HTML.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Guest Information</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Display Guest Information</th>
									<td>
										<input type="radio" class="radio_yes" name="form12[guest_information]" value="1"<?php if ($configuration['o_guest_information'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form12[guest_information]" value="0"<?php if ($configuration['o_guest_information'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable this to display the below message in the forums.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Guest Information message</th>
									<td>
										<textarea name="form12[guest_information_message]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_guest_information_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This text will not be parsed like regular posts and thus may contain HTML.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Announcement</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Display announcement</th>
									<td>
										<input type="radio" class="radio_yes" name="form12[announcement]" value="1"<?php if ($configuration['o_announcement'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form12[announcement]" value="0"<?php if ($configuration['o_announcement'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enable this to display the below message in the forums.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Announcement message</th>
									<td>
										<textarea name="form12[announcement_message]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_announcement_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This text will not be parsed like regular posts and thus may contain HTML.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Maintenance</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><a name="maintenance">Maintenance mode</a></th>
									<td>
										<input type="radio" class="radio_yes" name="form12[maintenance]" value="1"<?php if ($configuration['o_maintenance'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" class="radio_no" name="form12[maintenance]" value="0"<?php if ($configuration['o_maintenance'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When enabled, the board will only be available to administrators. This should be used if the board needs to taken down temporarily for maintenance.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Maintenance message</th>
									<td>
										<textarea name="form12[maintenance_message]" rows="5" cols="30"><?php echo convert_htmlspecialchars($configuration['o_maintenance_message']) ?></textarea>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The message that will be displayed to users when the board is in maintenance mode. If left blank a default message will be used. This text will not be parsed like regular posts and thus may contain HTML.');" onmouseout="return nd();" alt="" />
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
	<div class="clearer"></div>
</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>