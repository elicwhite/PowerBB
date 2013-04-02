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

//---Default Admin Stuff ---//
define('ADMIN_CONSOLE', 1);
define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Bot Detect';
require FORUM_ROOT.'header.php';

if (isset($_POST['delete_bot']))
{
	if (!isset($_POST['toremove']))
	{
		message("You did not select a bot to be deleted");
	}
	else
	{	
		global $db;
		$toremove = stripslashes($_POST['toremove']);
		$db->query('DELETE FROM '.$db->prefix."bots WHERE id='$toremove'") or error('Unable to delete bot definition', __FILE__, __LINE__, $db->error());
		if($result) redirect(FORUM_ROOT.'admin/admin_bots.php', "Saving Changes");
	}
}
$result = $db->query('SELECT * FROM '.$db->prefix.'botsconfig') or error('Unable to gather Bot Configuration', __FILE__, __LINE__, $db->error());
if (!$result)
{
	die("query failed: " . msql_error());
}
$botConfig = $db->fetch_row($result);
$botLifeSpan = $botConfig[1];
$botUAStringSensitive = $botConfig[2];
$botEnabled = $botConfig[3];
if(isset($_POST['update_config']))
{
	$newConfig = array((int)$_POST['bot_lifespan'], (int)isset($_POST['bot_caseSensitive']), (int)isset($_POST['bot_enabled']));
	if($newConfig[0] != $botLifeSpan | $newConfig[1] != $botUAStringSensitive | $newConfig[3] != $botEnabled)
	{
		$result = $db->query('UPDATE '.$db->prefix.'botsconfig SET display_time='.$newConfig[0].', isCaseSensitive='.$newConfig[1].', isEnabled='.$newConfig[2].' WHERE id=1') or error('Unable to gather Bot Configuration', __FILE__, __LINE__, $db->error());
		if($result) redirect(FORUM_ROOT.'admin/admin_bots.php', "Updating Bot Configuration");
	}

}
if (isset($_POST['submit_bot']))
{
	if (trim($_POST['bot_alias']) == '') message("Bot Alias was not defined");
	if (trim($_POST['bot_string']) == '') message("Bot UserAgent String was not defined");
	$bot_alias = $_POST['bot_alias'];
	$bot_string = $_POST['bot_string'];
	$result = $db->query('INSERT INTO '.$db->prefix."bots (id, bot_alias, bot_string, time_stamp) VALUES('', '$bot_alias', '$bot_string', '')") or error('Unable to add Bot Definition', __FILE__, __LINE__, $db->error());
	if($result) redirect(FORUM_ROOT.'admin/admin_bots.php', "Updating Bot Definitions");
	generate_admin_menu("bots");
?>
	<div class="block">
		<h2><span>BotDetect Mod</span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo "Bot Definition Successfully Added" ?></p>
				<p><a href="javascript: history.go(-1)">Go back</a></p>
			</div>
		</div>
	</div>
<?php
}
else
{
	generate_admin_menu("bots");
?>
<div class="blockform">
	<div class="tab-page" id="botPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "botPane" ), 1 )</script>
	<div class="tab-page" id="help-bot-page"><h2 class="tab"><?php echo $lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-bot-page" ) );</script>
		<div class="box">
		<form>
			<div class="inform">
				<div class="infldset">
					<table class="aligntop" cellspacing="0">
					<tr>
							<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/bans.png></td>
							<td>
								<span>This plugin allows you to detect when a search engine bot is crawling your forums and place them in "Users Online"; you can easily add/remove bot definitions from the database and set bot detection options without hassle.</span>
							</td>
					</tr>
					</table>
				</div>
			</div>
		</form>
		</div>
	</div>
	<div class="tab-page" id="conf-bot-page"><h2 class="tab">Config</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "conf-bot-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Configure BotDetect Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Bot Online Time</th>
								<td>
									<input type="text" class="textbox" value="<?php echo $botLifeSpan ?>" name="bot_lifespan" size="3" tabindex="1" /> (in minutes)
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('When detected, bot will appear online for this length of time.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">User Agent String Settings</th>
								<td>
									<input type="checkbox" name="bot_caseSensitive" <?php if($botUAStringSensitive == 1) echo ' checked' ?> tabindex="1" /> Is User Agent String case sensitive?
								</td>
							</tr>
							<tr>
								<th scope="row">Bot Detection</th>
								<td>
									<input type="checkbox" name="bot_enabled" <?php if($botEnabled == 1) echo ' checked' ?> tabindex="1" />
									Is Bot Detection Enabled?
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" name="update_config" class="b1" value="Update Configuration" tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="new-bot-page"><h2 class="tab">Add</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "new-bot-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Submit a new bot definition by providing it's alias and it's user agent string. </legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Bot Alias</th>
								<td>
									<input type="text" class="textbox" name="bot_alias" size="25" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Bot will appear online with this name.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Bot UserAgent String</th>
								<td>
									<input type="text" class="textbox" name="bot_string" size="25" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('String that identifies the Bot (HTTP_USER_AGENT).');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="submit_bot" value="Submit Bot Definition" tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="edit-bot-page"><h2 class="tab">Edit</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-bot-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<fieldset>
						<legend>Edit the current bot definitions. </legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Bot Alias</th>
								<th scope="row">Bot UserAgent String</th>
								<th scope="row"></th>
							</tr>
<?php
$result = $db->query('SELECT * FROM '.$db->prefix.'bots') or error('Unable to count messages', __FILE__, __LINE__, $db->error());
if (!$result)
{
	die("query failed: " . msql_error());
}
$num_definitions = 0;
while ($row = $db->fetch_row($result))
{
	$num_definitions++;
	$cur_ident = $row[0];
   	echo "<tr>";
	echo "<td>".$row[1]."</td>";
	echo "<td>".$row[2]."</td>";
	echo "<form id=\"example\" method=\"post\" action=\"$_SERVER[REQUEST_URI]&delbot=yes\">
	<td><input type=\"hidden\" name=\"toremove\" value=\"$cur_ident\">
		<input type=\"submit\" name=\"delete_bot\" class=\"b1\" value=\""; echo "Delete Bot Definition"; echo "\" tabindex=\"2\">
	</td>
	</form>";
}
?>
							<tr>
								<th scope="row">Total Bot Definitions: <?php echo $num_definitions ?></th>
								<th scope="row"></th>
								<th scope="row"></th>
								<th scope="row"></th>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
<?php
}
?>
<div class="clearer"></div>
</div>
<?php
	require FORUM_ROOT.'admin/admin_footer.php';
?>
