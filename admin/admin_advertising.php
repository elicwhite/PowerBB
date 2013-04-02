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
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Advertising';
require FORUM_ROOT.'header.php';

if (isset($_POST['save1']))
{
	$form = array_map('trim', $_POST['form1']);
	if (!isset($form['bot_name']) || $form['bot_name'] == '') message('You must enter a name for the bot!');
	while (list($key, $input) = @each($form))
	{
		$key = $db->escape($key);
		if ($ads_config['ads_'.$key] != $input)
		{
			if ($input != '') $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'advertising_config SET conf_value='.$value.' WHERE conf_name=\'ads_'.$key.'\'') or error('Unable to update ads config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_advertising_config_cache();
	$redirect_url = str_replace("?foo=bar", "", $_SERVER['REQUEST_URI']);
	redirect($redirect_url, 'Advertising options updated. Redirecting &hellip;');
}
if (isset($_POST['save2']))
{
	$form = array_map('trim', $_POST['form2']);
//	$form['adsense_enabled']  = intval($form['adsense_enabled']);
	if ($form['adsense_enabled'] == '1' && $ads_config['yahoo_ads_enabled'] == '1') message('You can activate only one type of ads at a time.');
	if ($form['adsense_enabled'] == '1' && $form['ad_client'] == '') message('You must enter a client ID if you\'re enabling the ads!');
	$form['ad_format'] = $_POST['format'].'_as';
	$measurements = explode("x", $_POST['format']);
	$form['ad_width'] = intval($measurements[0]);
	$form['ad_height'] = intval($measurements[1]);
	while (list($key, $input) = @each($form))
	{
		$key = $db->escape($key);
		if ($ads_config['google_'.$key] != $input)
		{
			if ($input != '') $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'advertising_config SET conf_value='.$value.' WHERE conf_name=\'google_'.$key.'\'') or error('Unable to update ads config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_advertising_config_cache();
	$redirect_url = str_replace("?foo=bar", "", $_SERVER['REQUEST_URI']);
	redirect($redirect_url, 'Advertising options updated. Redirecting &hellip;');
}
if (isset($_POST['save3']))
{
	$form = array_map('trim', $_POST['form3']);
//	$form['ads_enabled']  = intval($form['ads_enabled']);
	if ($form['ads_enabled'] == '1' && $ads_config['google_adsense_enabled'] == '1') message('You can activate only one type of ads at a time.');
	if ($form['ads_enabled'] == '1' && $form['ad_client'] == '') message('You must enter a client ID if you\'re enabling the ads!');
	$form['ad_format'] = $_POST['format'].'_as';
	$measurements = explode("x", $_POST['format']);
	$form['ad_width'] = intval($measurements[0]);
	$form['ad_height'] = intval($measurements[1]);
	while (list($key, $input) = @each($form))
	{
		$key = $db->escape($key);
		if ($ads_config['yahoo_'.$key] != $input)
		{
			if ($input != '') $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'advertising_config SET conf_value='.$value.' WHERE conf_name=\'yahoo_'.$key.'\'') or error('Unable to update ads config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_advertising_config_cache();
	$redirect_url = str_replace("?foo=bar", "", $_SERVER['REQUEST_URI']);
	redirect($redirect_url, 'Advertising options updated. Redirecting &hellip;');
}
if (isset($_POST['save4']))
{
	$form = array_map('trim', $_POST['form4']);
	while (list($key, $input) = @each($form))
	{
		$key = $db->escape($key);
		if ($ads_config['clicksor_'.$key] != $input)
		{
			if ($input != '') $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'advertising_config SET conf_value='.$value.' WHERE conf_name=\'clicksor_'.$key.'\'') or error('Unable to update ads config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_advertising_config_cache();
	$redirect_url = str_replace("?foo=bar", "", $_SERVER['REQUEST_URI']);
	redirect($redirect_url, 'Advertising options updated. Redirecting &hellip;');
}
if (isset($_POST['save5']))
{
	$form = array_map('trim', $_POST['form5']);
	while (list($key, $input) = @each($form))
	{
		$key = $db->escape($key);
		if ($ads_config['other_'.$key] != $input)
		{
			if ($input != '') $value = '\''.$db->escape($input).'\'';
			else $value = 'NULL';
			$db->query('UPDATE '.$db->prefix.'advertising_config SET conf_value='.$value.' WHERE conf_name=\'other_'.$key.'\'') or error('Unable to update ads config', __FILE__, __LINE__, $db->error());
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_advertising_config_cache();
	$redirect_url = str_replace("?foo=bar", "", $_SERVER['REQUEST_URI']);
	redirect($redirect_url, 'Advertising options updated. Redirecting &hellip;');
}
generate_admin_menu("adverts");
?>
<div class="blockform">
	<div class="tab-page" id="advertPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "advertPane" ), 1 )</script>
	<div class="tab-page" id="gen-advert-page"><h2 class="tab">Config</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "gen-advert-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>?foo=bar">
				<div class="inform">
					<fieldset>
						<legend>Board Options</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Bot Name</th>
								<td>
									<input type="text" class="textbox" name="form1[bot_name]" size="25" tabindex="1" value = "<?php echo $ads_config['ads_bot_name'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('This will be the name on the posts with the Advertising in it.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Bot Tag</th>
								<td>
									<input type="text" class="textbox" name="form1[bot_tag]" size="25" tabindex="1" value = "<?php echo $ads_config['ads_bot_tag'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Fill this out if you want the bot posts to have a tag.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Exclude Forums</th>
								<td>
									<input type="text" class="textbox" name="form1[exclude_forums]" size="25" tabindex="1" value = "<?php echo $ads_config['ads_exclude_forums'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Fill this out if you want to exclude certain forums. Enter forum ids, put `,`s around them (for example: ,5,6,7,).');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Exclude Groups</th>
								<td>
									<input type="text" class="textbox" name="form1[exclude_groups]" size="25" tabindex="1" value = "<?php echo $ads_config['ads_exclude_groups'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Fill this out if you want to exclude certain groups. Enter group ids, put `,`s around them (for example: ,5,6,7,).');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Advertising Message</th>
								<td>
									<input type="text" class="textbox" name="form1[message]" size="25" tabindex="1" value = "<?php echo $ads_config['ads_message'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Put a message here that you want to appear on the adbots posts.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save1" class="b1" value="Save changes" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="adsense-advert-page"><h2 class="tab">AdSense</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "adsense-advert-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>?foo=bar">
				<div class="inform">
					<fieldset>
						<legend>Ads Options</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable Ads</th>
								<td>
									<input type="radio" name="form2[adsense_enabled]" value="1"<?php if ($ads_config['google_adsense_enabled'] == '1') echo ' checked="checked"'?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="radio" name="form2[adsense_enabled]" value="0"<?php if ($ads_config['google_adsense_enabled'] == '0') echo ' checked="checked"'?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Click on No if you want to temporarily turn off the ads after the first post.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Adsense Client ID</th>
								<td>
									<input type="text" class="textbox" name="form2[ad_client]" size="25" tabindex="1" value = "<?php echo $ads_config['google_ad_client'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The unique identifier given to you by Google Adsense.');" onMouseOut="return nd();" alt="" />
									<span></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Ad Channel</th>
								<td>
									<input type="text" class="textbox" name="form2[ad_channel]" size="20" tabindex="1" value = "<?php echo $ads_config['google_ad_channel'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('If you need this, feel free to use it: for the most part I don\'t think people do.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Ad Type</th>
								<td>
									<select name="form2[ad_type]">
										<option value="text" <?php if ($ads_config['google_ad_type'] == 'text') echo 'selected="selected"' ?> > Text only </option>
										<option value="image" <?php if ($ads_config['google_ad_type'] == 'image') echo 'selected="selected"' ?> > Image only </option>
										<option value="text_image" <?php if ($ads_config['google_ad_type'] == 'text_image') echo 'selected="selected"' ?> > Text and Image </option>
									</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Specify your ad type here.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Ad Sizes</th>
								<td>
									<select id="text_img_format" name="format">
									<optgroup label="Text and image ads:">
										<option value="728x90" <?php if ($ads_config['google_ad_format'] == '728x90_as') echo 'selected="selected"' ?>> 728 x 90 Leaderboard</option>
										<option value="468x60" <?php if ($ads_config['google_ad_format'] == '468x60_as') echo 'selected="selected"' ?>> 468 x 60 Banner</option>
										<option value="300x250" <?php if ($ads_config['google_ad_format'] == '300x250_as') echo 'selected="selected"' ?>> 300 x 250 Medium rectangle</option>
										<option value="160x600" <?php if ($ads_config['google_ad_format'] == '160x600_as') echo 'selected="selected"' ?>> 160 x 600 Wide Skyscraper</option>
										<option value="120x600" <?php if ($ads_config['google_ad_format'] == '120x600_as') echo 'selected="selected"' ?>> 120 x 600 Skyscraper</option>
									</optgroup>
									<optgroup label="Text ads only:">
										<option value="336x280" <?php if ($ads_config['google_ad_format'] == '336x280_as') echo 'selected="selected"' ?>> 336 x 280 Large Rectangle</option>
										<option value="250x250" <?php if ($ads_config['google_ad_format'] == '250x250_as') echo 'selected="selected"' ?>> 250 x 250 Square</option>
										<option value="234x60" <?php if ($ads_config['google_ad_format'] == '234x60_as') echo 'selected="selected"' ?>> 234 x 60 Half Banner</option>
										<option value="180x150" <?php if ($ads_config['google_ad_format'] == '180x150_as') echo 'selected="selected"' ?>> 180 x 150 Small Rectangle</option>
										<option value="125x125" <?php if ($ads_config['google_ad_format'] == '125x125_as') echo 'selected="selected"' ?>> 125 x 125 Button</option>
										<option value="120x240" <?php if ($ads_config['google_ad_format'] == '120x240_as') echo 'selected="selected"' ?>> 120 x 240 Vertical Banner</option>
									</optgroup>
									</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Select from among the different ad sizes.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
					</div>
				</fieldset>
			</div>
			<div class="inform">
					<fieldset>
						<legend>Color Choices</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Border Color</th>
								<td>
									<input type="text" class="textbox" name="form2[color_border]" size="10" tabindex="1" value = "<?php echo $ads_config['google_color_border'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color for the border of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Background Color</th>
								<td>
									<input type="text" class="textbox" name="form2[color_bg]" size="10" tabindex="1" value = "<?php echo $ads_config['google_color_bg'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The background color of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Link Color</th>
								<td>
									<input type="text" class="textbox" name="form2[color_link]" size="10" tabindex="1" value = "<?php echo $ads_config['google_color_link'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color of the links in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
														<tr>
								<th scope="row">URL Color</th>
								<td>
									<input type="text" class="textbox" name="form2[color_url]" size="10" tabindex="1" value = "<?php echo $ads_config['google_color_url'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color of the URLs in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
														<tr>
								<th scope="row">Text Color</th>
								<td>
									<input type="text" class="textbox" name="form2[color_text]" size="10" tabindex="1" value = "<?php echo $ads_config['google_color_text'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color of the text in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
														<tr>
								<th scope="row">Alternate Color</th>
								<td>
									<input type="text" class="textbox" name="form2[alternate_color]" size="10" tabindex="1" value = "<?php echo $ads_config['google_alternate_color'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The alternate color of the text in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save2" class="b1" value="Save changes" /></p>
			</form>
		</div>
	</div>

	<div class="tab-page" id="yahoo-advert-page"><h2 class="tab">Y! Ads</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "yahoo-advert-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>?foo=bar">
				<div class="inform">
					<fieldset>
						<legend>Ads Options</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable Ads</th>
								<td>
									<input type="radio" name="form3[ads_enabled]" value="1"<?php if ($ads_config['yahoo_ads_enabled']  == '1') echo ' checked="checked"'?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="radio" name="form3[ads_enabled]" value="0"<?php if ($ads_config['yahoo_ads_enabled']  == '0') echo ' checked="checked"'?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Click on No if you want to temporarily turn off the ads after the first post.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Y! Ads Partner ID</th>
								<td>
									<input type="text" class="textbox" name="form3[ad_client]" size="15" tabindex="1" value = "<?php echo $ads_config['yahoo_ad_client'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The unique identifier given to you by Yahoo Ads.');" onMouseOut="return nd();" alt="" />
									<span></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Ad Section</th>
								<td>
									<input type="text" class="textbox" name="form3[ad_channel]" size="15" tabindex="1" value = "<?php echo $ads_config['yahoo_ad_channel'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('If you need this, feel free to use it: for the most part I don\'t think people do.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Ad Sizes</th>
								<td>
									<select id="text_img_format" name="format">
									<optgroup label="Text and image ads:">
										<option value="728x90" <?php if ($ads_config['yahoo_ad_format'] == '728x90_as') echo 'selected="selected"' ?>> 728 x 90 Leaderboard</option>
										<option value="468x60" <?php if ($ads_config['yahoo_ad_format'] == '468x60_as') echo 'selected="selected"' ?>> 468 x 60 Banner</option>
										<option value="300x250" <?php if ($ads_config['yahoo_ad_format'] == '300x250_as') echo 'selected="selected"' ?>> 300 x 250 Medium rectangle</option>
										<option value="160x600" <?php if ($ads_config['yahoo_ad_format'] == '160x600_as') echo 'selected="selected"' ?>> 160 x 600 Wide Skyscraper</option>
										<option value="120x600" <?php if ($ads_config['yahoo_ad_format'] == '120x600_as') echo 'selected="selected"' ?>> 120 x 600 Skyscraper</option>
									</optgroup>
									<optgroup label="Text ads only:">
										<option value="336x280" <?php if ($ads_config['yahoo_ad_format'] == '336x280_as') echo 'selected="selected"' ?>> 336 x 280 Large Rectangle</option>
										<option value="250x250" <?php if ($ads_config['yahoo_ad_format'] == '250x250_as') echo 'selected="selected"' ?>> 250 x 250 Square</option>
										<option value="234x60" <?php if ($ads_config['yahoo_ad_format'] == '234x60_as') echo 'selected="selected"' ?>> 234 x 60 Half Banner</option>
										<option value="180x150" <?php if ($ads_config['yahoo_ad_format'] == '180x150_as') echo 'selected="selected"' ?>> 180 x 150 Small Rectangle</option>
										<option value="125x125" <?php if ($ads_config['yahoo_ad_format'] == '125x125_as') echo 'selected="selected"' ?>> 125 x 125 Button</option>
										<option value="120x240" <?php if ($ads_config['yahoo_ad_format'] == '120x240_as') echo 'selected="selected"' ?>> 120 x 240 Vertical Banner</option>
									</optgroup>
									</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Select from among the different ad sizes.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
					</div>
				</fieldset>
			</div>
			<div class="inform">
					<fieldset>

						<legend>Color Choices</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Border Color</th>
								<td>
									<input type="text" class="textbox" name="form3[color_border]" size="10" tabindex="1" value = "<?php echo $ads_config['yahoo_color_border'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color for the border of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Background Color</th>
								<td>
									<input type="text" class="textbox" name="form3[color_bg]" size="10" tabindex="1" value = "<?php echo $ads_config['yahoo_color_bg'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The background color of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Link Color</th>
								<td>
									<input type="text" class="textbox" name="form3[color_link]" size="10" tabindex="1" value = "<?php echo $ads_config['yahoo_color_link'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color of the links in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
														<tr>
								<th scope="row">URL Color</th>
								<td>
									<input type="text" class="textbox" name="form3[color_url]" size="10" tabindex="1" value = "<?php echo $ads_config['yahoo_color_url'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color of the URLs in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
														<tr>
								<th scope="row">Text Color</th>
								<td>
									<input type="text" class="textbox" name="form3[color_text]" size="10" tabindex="1" value = "<?php echo $ads_config['yahoo_color_text'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The color of the text in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
														<tr>
								<th scope="row">Alternate Color</th>
								<td>
									<input type="text" class="textbox" name="form3[alternate_color]" size="10" tabindex="1" value = "<?php echo $ads_config['yahoo_alternate_color'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The alternate color of the text in the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save3" class="b1" value="Save changes" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="clicksor-advert-page"><h2 class="tab">Clicksor Ads</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "clicksor-advert-page" ) );</script>
		<div class="box">
			<div class="inform">
				<div class="infldset"><div align="center">
					<table height='1' cellpadding='10' style='border:0px;width:468px;'>
					<tr>
						<td style='border:0px;' height='1'><strong>Ad Preview</strong> (size:468x60)<br /><br />
							<table width='468' height='60' cellspacing='0' cellpadding='0' border='0' style='cursor:hand;border:0px;'>
							<tr>
								<td align='center' valign='middle' style='border:0px;padding:0px 0px 0px 0px;background-color:<?php echo $ads_config['clicksor_banner_border'] ?>' id='clicksor_banner_border_sample' class='clicksor_banner_border_sample'>
									<table cellpadding='0' cellspacing='0' height='60' style='border:0px;'>
									<tr>
										<td style='border:0px;padding:0px 0px 0px 0px' align='left'>
											<table cellpadding='4' cellspacing='1'>
											<tr>
												<td width='50%' height='43' style='border:0px;background-color:<?php echo $ads_config['clicksor_banner_ad_bg'] ?>;' id='clicksor_banner_ad_bg_sample' class='clicksor_banner_ad_bg_sample'><span style='color:<?php echo $ads_config['clicksor_banner_link_color'] ?>;text-decoration:underline' id='clicksor_banner_link_color_sample' class='clicksor_banner_link_color_sample'>Title</span><br /><span style='color:<?php echo $ads_config['clicksor_banner_text_color'] ?>' id='clicksor_banner_text_color_sample' class='clicksor_banner_text_color_sample'>Description Line 1<br />Description Line 2</span></span></td>
												<td width='50%' height='43' style='border:0px;background-color:<?php echo $ads_config['clicksor_banner_ad_bg'] ?>;' id='clicksor_banner_ad_bg_sample' class='clicksor_banner_ad_bg_sample'><span style='color:<?php echo $ads_config['clicksor_banner_link_color'] ?>;text-decoration:underline' id='clicksor_banner_link_color_sample' class='clicksor_banner_link_color_sample'>Title</span><br /><span style='color:<?php echo $ads_config['clicksor_banner_text_color'] ?>' id='clicksor_banner_text_color_sample' class='clicksor_banner_text_color_sample'>Description Line 1<br />Description Line 2</span></span></td>
											</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style='border:0px;padding:0px 10px 5px 0px' colspan='1' height='14' align=right><font style='line-height:;font-size:10px; font-family:verdana,arial,sans-serif;color:#ffffff;padding-left:3px'>Ads by Clicksor</font></td>
									</tr>
									</table>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</table></div>
				</div>
			</div>
		</div><br />
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>?foo=bar">
				<div class="inform">
					<fieldset>
						<legend>Ads Options</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable Ads</th>
								<td>
									<input type="radio" name="form4[ads_enabled]" value="1"<?php if ($ads_config['clicksor_ads_enabled']  == '1') echo ' checked="checked"'?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="radio" name="form4[ads_enabled]" value="0"<?php if ($ads_config['clicksor_ads_enabled']  == '0') echo ' checked="checked"'?> />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Click on No if you want to temporarily turn off the ads after the first post.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Clicksor Ads Partner ID</th>
								<td>
									<input type="text" class="textbox" name="form4[pid]" size="15" tabindex="1" value = "<?php echo $ads_config['clicksor_pid'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The unique partner identifier given to you by Clicksor.');" onMouseOut="return nd();" alt="" />
									<span></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Clicksor Ads Site ID</th>
								<td>
									<input type="text" class="textbox" name="form4[sid]" size="15" tabindex="1" value = "<?php echo $ads_config['clicksor_sid'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The unique site identifier given to you by Clicksor.');" onMouseOut="return nd();" alt="" />
									<span></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Default URL</th>
								<td>
									<input type="text" class="textbox" name="form4[default_url]" size="15" tabindex="1" value = "<?php echo $ads_config['clicksor_default_url'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Default URL to show in case the ad is not available.');" onMouseOut="return nd();" alt="" />
									<span></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Ad Sizes</th>
								<td>
									<select id="text_img_format" name="form4[ad_format]">
									<optgroup label="Text and image ads:">
										<option value="1" <?php if ($ads_config['clicksor_ad_format'] == '1') echo 'selected="selected"' ?>>728 x 90 Leaderboard</option>
										<option value="2" <?php if ($ads_config['clicksor_ad_format'] == '2') echo 'selected="selected"' ?>>468 x 60 Banner</option>
										<option value="3" <?php if ($ads_config['clicksor_ad_format'] == '3') echo 'selected="selected"' ?>>125 x 125 Button</option>
										<option value="4" <?php if ($ads_config['clicksor_ad_format'] == '4') echo 'selected="selected"' ?>>120 x 600 Skyscraper</option>
										<option value="5" <?php if ($ads_config['clicksor_ad_format'] == '5') echo 'selected="selected"' ?>>160 x 600 Wide Skyscraper</option>
										<option value="6" <?php if ($ads_config['clicksor_ad_format'] == '6') echo 'selected="selected"' ?>>120 x 240 Vertical Banner</option>
										<option value="7" <?php if ($ads_config['clicksor_ad_format'] == '7') echo 'selected="selected"' ?>>300 x 250 Medium rectangle</option>
										<option value="8" <?php if ($ads_config['clicksor_ad_format'] == '8') echo 'selected="selected"' ?>>250 x 250 Square</option>
										<option value="9" <?php if ($ads_config['clicksor_ad_format'] == '9') echo 'selected="selected"' ?>>336 x 280 Large Rectangle</option>
										<option value="10" <?php if ($ads_config['clicksor_ad_format'] == '10') echo 'selected="selected"' ?>>180 x 150 Small Rectangle</option>
									</optgroup>
									</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Select from among the different ad sizes.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
					</div>
				</fieldset>
			</div>
			<div class="inform">
					<fieldset>
						<legend>Color Choices</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Border color</th>
								<td>
									<a href="javascript:pickColor('clicksor_banner_border');" id="clicksor_banner_border" style="border: 1px solid rgb(0, 0, 0); background-color: <?php echo $ads_config['clicksor_banner_border'] ?>; font-family: Verdana; font-size: 14px; text-decoration: none;">&nbsp;&nbsp;&nbsp;</a>
									<input id="clicksor_banner_border_value" type="text" class="textbox" name="form4[banner_border]" size="10" tabindex="1" value = "<?php echo $ads_config['clicksor_banner_border'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The border color of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Ad background</th>
								<td>
									<a href="javascript:pickColor('clicksor_banner_ad_bg');" id="clicksor_banner_ad_bg" style="border: 1px solid rgb(0, 0, 0); background-color: <?php echo $ads_config['clicksor_banner_ad_bg'] ?>; font-family: Verdana; font-size: 14px; text-decoration: none;">&nbsp;&nbsp;&nbsp;</a>
									<input id="clicksor_banner_ad_bg_value" type="text" class="textbox" name="form4[banner_ad_bg]" size="10" tabindex="1" value = "<?php echo $ads_config['clicksor_banner_ad_bg'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The background color of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Link Color</th>
								<td>
									<a href="javascript:pickColor('clicksor_banner_link_color');" id="clicksor_banner_link_color" style="border: 1px solid rgb(0, 0, 0); background-color:<?php echo $ads_config['clicksor_banner_link_color'] ?>; font-family: Verdana; font-size: 14px; text-decoration: none;">&nbsp;&nbsp;&nbsp;</a>
									<input id="clicksor_banner_link_color_value" type="text" class="textbox" name="form4[banner_link_color]" size="10" tabindex="1" value = "<?php echo $ads_config['clicksor_banner_link_color'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The link color of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Text Color</th>
								<td>
									<a href="javascript:pickColor('clicksor_banner_text_color');" id="clicksor_banner_text_color" style="border: 1px solid rgb(0, 0, 0); background-color:<?php echo $ads_config['clicksor_banner_text_color'] ?>; font-family: Verdana; font-size: 14px; text-decoration: none;">&nbsp;&nbsp;&nbsp;</a>
									<input id="clicksor_banner_text_color_value" type="text" class="textbox" name="form4[banner_text_color]" size="10" tabindex="1" value = "<?php echo $ads_config['clicksor_banner_text_color'] ?>" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The text color of the ad.');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save4" class="b1" value="Save changes" /></p>
			</form>
		</div>
		</div>
		<div class="tab-page" id="other-advert-page"><h2 class="tab">Custom Ad</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "other-advert-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>?foo=bar">
				<div class="inform">
					<fieldset>
						<legend>Ads Options</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Enable Ads</th>
									<td>
										<input type="radio" name="form5[ads_enabled]" value="1"<?php if ($ads_config['other_ads_enabled'] == '1') echo ' checked="checked"'?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="form5[ads_enabled]" value="0"<?php if ($ads_config['other_ads_enabled'] == '0') echo ' checked="checked"'?> />&nbsp;<strong>No</strong>
	&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Click on No if you want to temporarily turn off the ads after the first post.');" onMouseOut="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Other ads message</th>
									<td>
										<textarea name="form5[ads_message]" rows="15" cols="60"><? echo $ads_config['other_ads_message']?></textarea>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="save5" class="b1" value="Save changes" /></p>
			</form>
		</div>
	</div>
		
		
	</div>
</div>
<div class="clearer"></div>
</div>
<?php
	require FORUM_ROOT.'admin/admin_footer.php';
?>

