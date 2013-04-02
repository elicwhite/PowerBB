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

if (isset($_POST['prune']))
{
	if ((trim($_POST['days']) == '') || trim($_POST['posts']) == '') message('You need to set all settings!');
	if ($_POST['admods_delete'])
	{
		$admod_delete = 'group_id > 0';
	}
	else
	{
		$admod_delete = 'group_id > 3';
	}
	if ($_POST['verified'] == 1) $verified = '';
	elseif ($_POST['verified'] == 0) $verified = 'AND (group_id < 32000)';
	else $verified = 'AND (group_id = 32000)';
	$prune = ($_POST['prune_by'] == 1) ? 'registered' : 'last_visit';
	$user_time = time() - ($_POST['days'] * 86400);
	$result = $db->query('DELETE FROM '.$db->prefix.'users WHERE (num_posts < '.intval($_POST['posts']).') AND ('.$prune.' < '.intval($user_time).') AND (id > 2) AND ('.$admod_delete.')'.$verified, true) or error('Unable to delete users', __FILE__, __LINE__, $db->error());
	$users_pruned = $db->affected_rows();
	message('Pruning complete. Users pruned '.$users_pruned.'.');
}
elseif (isset($_POST['add_user']))
{
	require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
	require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';
	$username = forum_trim($_POST['username']);
	$email1 = strtolower(trim($_POST['email']));
	$email2 = strtolower(trim($_POST['email']));
	if ($_POST['random_pass'] == '1')
	{
		$password1 = random_pass(8);
		$password2 = $password1;
	}
	else
	{
		$password1 = trim($_POST['password']);
		$password2 = trim($_POST['password']);
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
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\' OR username=\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		$busy = $db->result($result);
		message($lang_register['Username dupe 1'].' '.convert_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2']);
	}
	require FORUM_ROOT.'include/email.php';
	if (!is_valid_email($email1)) message($lang_common['Invalid e-mail']);
	$dupe_list = array();
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE email=\''.$email1.'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while ($cur_dupe = $db->fetch_assoc($result)) $dupe_list[] = $cur_dupe['username'];
	}
	$timezone = '0';
	$language = isset($_POST['language']) ? $_POST['language'] : $configuration['o_default_lang'];
	$save_pass = (!isset($_POST['save_pass']) || $_POST['save_pass'] != '1') ? '0' : '1';
	$email_setting = intval(1);
	$now = time();
	$intial_group_id = ($_POST['random_pass'] == '0') ? $configuration['o_default_user_group'] : UNVERIFIED;
	$password_hash = forum_hash($password1);
	$db->query('INSERT INTO '.$db->prefix.'users (username, group_id, password, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit) VALUES(\''.$db->escape($username).'\', '.$intial_group_id.', \''.$password_hash.'\', \''.$email1.'\', '.$email_setting.', '.$save_pass.', '.$timezone.' , \''.$language.'\', \''.$configuration['o_default_style'].'\', '.$now.', \''.get_remote_address().'\', '.$now.')') or error('Unable to create user', __FILE__, __LINE__, $db->error());
	$new_uid = $db->insert_id();
	if ($configuration['o_regs_report'] == '1')
	{
		$mail_subject = 'Alert - New registration';
		$mail_message = 'User \''.$username.'\' registered in the forums at '.$configuration['o_base_url']."\n\n".'User profile: '.$configuration['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';
		forum_mail($configuration['o_mailing_list'], $mail_subject, $mail_message);
	}
	if ($_POST['random_pass'] == '1')
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
	}
	message('User Created');
}

if (isset($_GET['ip_stats']))
{
	$ip_stats = intval($_GET['ip_stats']);
	if ($ip_stats < 1) message($lang_common['Bad request']);
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Users';
	require FORUM_ROOT.'header.php';
?>
<div id="users1" class="blocktable">
	<h2><span><?php echo $lang_admin['Users']; ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">IP address</th>
					<th class="tc2" scope="col">Last used</th>
					<th class="tc3" scope="col">Times found</th>
					<th class="tcr" scope="col">Action</th>
				</tr>
			</thead>
			<tbody>
<?php
	$result = $db->query('SELECT poster_ip, MAX(posted) AS last_used, COUNT(id) AS used_times FROM '.$db->prefix.'posts WHERE poster_id='.$ip_stats.' GROUP BY poster_ip ORDER BY last_used DESC') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while ($cur_ip = $db->fetch_assoc($result))
		{
?>
				<tr>
					<td class="tcl"><a href="<?php echo FORUM_ROOT?>moderate.php?get_host=<?php echo $cur_ip['poster_ip'] ?>"><?php echo $cur_ip['poster_ip'] ?></a></td>
					<td class="tc2"><?php echo format_time($cur_ip['last_used']) ?></td>
					<td class="tc3"><?php echo $cur_ip['used_times'] ?></td>
					<td class="tcr"><a href="admin_users.php?show_users=<?php echo $cur_ip['poster_ip'] ?>"><?php echo $lang_admin['find_more_users']; ?></a></td>
				</tr>
<?php
		}
	}
	else echo "\t\t\t\t".'<tr><td class="tcl" colspan="4">'. $lang_admin['no_posts_by_user'].'</td></tr>'."\n";
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<div class="linksb">
	<div class="inbox">
		<div><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"></div>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}

if (isset($_GET['show_users']))
{
	$ip = $_GET['show_users'];
	if (!preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip)) message('The supplied IP address is not correctly formatted.');
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Users'];
	require FORUM_ROOT.'header.php';
?>
<div id="users2" class="blocktable">
	<h2><span>Users</span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">Username</th>
					<th class="tc2" scope="col">E-mail</th>
					<th class="tc3" scope="col">Title/Status</th>
					<th class="tc4" scope="col">Posts</th>
					<th class="tc5" scope="col">Admin note</th>
					<th class="tcr" scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
<?php
	$result = $db->query('SELECT DISTINCT poster_id, poster FROM '.$db->prefix.'posts WHERE poster_ip=\''.$db->escape($ip).'\' ORDER BY poster DESC') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$num_posts = $db->num_rows($result);
	if ($num_posts)
	{
		for ($i = 0; $i < $num_posts; ++$i)
		{
			list($poster_id, $poster) = $db->fetch_row($result);
			$result2 = $db->query('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id>1 AND u.id='.$poster_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if (($user_data = $db->fetch_assoc($result2)))
			{
				$user_title = get_title($user_data);
				$actions = '<a href="admin_users.php?ip_stats='.$user_data['id'].'">View IP stats</a> - <a href="'.FORUM_ROOT.'search.php?action=show_user&amp;user_id='.$user_data['id'].'">Show posts</a>';
?>
				<tr>
					<td class="tcl"><?php echo '<a href="'.FORUM_ROOT.'profile.php?id='.$user_data['id'].'">'.convert_htmlspecialchars($user_data['username']).'</a>' ?></td>
					<td class="tc2"><a href="mailto:<?php echo $user_data['email'] ?>"><?php echo $user_data['email'] ?></a></td>
					<td class="tc3"><?php echo $user_title ?></td>
					<td class="tc4"><?php echo $user_data['num_posts'] ?></td>
					<td class="tc5"><?php echo ($user_data['admin_note'] != '') ? $user_data['admin_note'] : '&nbsp;' ?></td>
					<td class="tcr"><?php echo $actions ?></td>
				</tr>
<?php
			}
			else
			{
?>
				<tr>
					<td class="tcl"><?php echo convert_htmlspecialchars($poster) ?></td>
					<td class="tc2">&nbsp;</td>
					<td class="tc3">Guest</td>
					<td class="tc4">&nbsp;</td>
					<td class="tc5">&nbsp;</td>
					<td class="tcr">&nbsp;</td>
				</tr>
<?php
			}
		}
	}
	else echo "\t\t\t\t".'<tr><td class="tcl" colspan="6">'. $lang_admin['ip_not_in_db'].'</td></tr>'."\n";
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<div class="linksb">
	<div class="inbox">
		<div><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"></div>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else if (isset($_POST['find_user']))
{
	$form = $_POST['form'];
	$form['username'] = $_POST['username'];
	$form = array_map('trim', $form);
	$conditions = array();
	$posts_greater = trim($_POST['posts_greater']);
	$posts_less = trim($_POST['posts_less']);
	$last_post_after = trim($_POST['last_post_after']);
	$last_post_before = trim($_POST['last_post_before']);
	$registered_after = trim($_POST['registered_after']);
	$registered_before = trim($_POST['registered_before']);
	$order_by = $_POST['order_by'];
	$direction = $_POST['direction'];
	$user_group = $_POST['user_group'];
	if (preg_match('/[^0-9]/', $posts_greater.$posts_less)) message('You entered a non-numeric value into a numeric only column.');
	if ($last_post_after != '') $last_post_after = strtotime($last_post_after);
	if ($last_post_before != '') $last_post_before = strtotime($last_post_before);
	if ($registered_after != '') $registered_after = strtotime($registered_after);
	if ($registered_before != '') $registered_before = strtotime($registered_before);
	if ($last_post_after == -1 || $last_post_before == -1 || $registered_after == -1 || $registered_before == -1) message('You entered an invalid date/time.');
	if ($last_post_after != '') $conditions[] = 'u.last_post>'.$last_post_after;
	if ($last_post_before != '') $conditions[] = 'u.last_post<'.$last_post_before;
	if ($registered_after != '') $conditions[] = 'u.registered>'.$registered_after;
	if ($registered_before != '') $conditions[] = 'u.registered<'.$registered_before;
	$like_command = ($db_type == 'pgsql') ? 'ILIKE' : 'LIKE';
	while (list($key, $input) = @each($form))
	{
		if ($input != '') $conditions[] = 'u.'.$db->escape($key).' '.$like_command.' \''.$db->escape(str_replace('*', '%', $input)).'\'';
	}
	if ($posts_greater != '') $conditions[] = 'u.num_posts>'.$posts_greater;
	if ($posts_less != '') $conditions[] = 'u.num_posts<'.$posts_less;
	if ($user_group != 'all') $conditions[] = 'u.group_id='.$db->escape($user_group);
	if (empty($conditions)) message('You didn\'t enter any search terms.');
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Users';
	require FORUM_ROOT.'header.php';
?>
<div id="users2" class="blocktable">
	<h2><span>Users</span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">Username</th>
					<th class="tc2" scope="col">E-mail</th>
					<th class="tc3" scope="col">Title/Status</th>
					<th class="tc4" scope="col">Posts</th>
					<th class="tc5" scope="col">Admin note</th>
					<th class="tcr" scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
<?php
	$result = $db->query('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id>1 AND '.implode(' AND ', $conditions).' ORDER BY '.$db->escape($order_by).' '.$db->escape($direction)) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while ($user_data = $db->fetch_assoc($result))
		{
			$user_title = get_title($user_data);
			if (($user_data['g_id'] == '' || $user_data['g_id'] == UNVERIFIED) && $user_title != $lang_common['Banned']) $user_title = '<span class="warntext">Not verified</span>';
			$actions = '<a href="admin_users.php?ip_stats='.$user_data['id'].'">View IP stats</a> - <a href="'.FORUM_ROOT.'search.php?action=show_user&amp;user_id='.$user_data['id'].'">Show posts</a>';
?>
				<tr>
					<td class="tcl"><?php echo '<a href="'.FORUM_ROOT.'profile.php?id='.$user_data['id'].'">'.convert_htmlspecialchars($user_data['username']).'</a>' ?></td>
					<td class="tc2"><a href="mailto:<?php echo $user_data['email'] ?>"><?php echo $user_data['email'] ?></a></td>
					<td class="tc3"><?php echo $user_title ?></td>
					<td class="tc4"><?php echo $user_data['num_posts'] ?></td>
					<td class="tc5"><?php echo ($user_data['admin_note'] != '') ? $user_data['admin_note'] : '&nbsp;' ?></td>
					<td class="tcr"><?php echo $actions ?></td>
				</tr>
<?php
		}
	}
	else echo "\t\t\t\t".'<tr><td class="tcl" colspan="6">'.$lang_admin['no_match'].'</td></tr>'."\n";
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<div class="linksb">
	<div class="inbox">
		<div><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"></div>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else
{
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Users'];
	$focus_element = array('find_user', 'username');
	require FORUM_ROOT.'header.php';
	generate_admin_menu('users');
?>
	<div class="blockform">
	<div class="tab-page" id="usersPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "usersPane" ), 1 )</script>
	<div class="tab-page" id="help-users-page"><h2 class="tab"><?php echo $lang_admin['Help'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-users-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
								<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/users.png></td>
								<td>
									<span><?php echo $lang_admin['help_users']; ?></span>
								</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="add-users-page"><h2 class="tab"><?php echo $lang_admin['Add'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-users-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row"><?php echo $lang_admin['Username'] ?></th>
								<td>
									<input type="text" class="textbox" name="username" size="25" tabindex="3" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_admin['E-Mail'] ?></th>
								<td>
									<input type="text" class="textbox" name="email" size="25" tabindex="3" />
								</td>
							</tr>
							<tr>
								<th scope="row">Generate random password?</th>
								<td>
									<input type="radio" name="random_pass" value="1" />&nbsp;<strong><?php echo $lang_admin['Yes'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="random_pass" value="0" checked="checked" />&nbsp;<strong><?php echo $lang_admin['No'] ?></strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If Yes a random password will be generated and emailed to the above address.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_admin['Password'] ?></th>
								<td>
									<input type="text" class="textbox" name="password" size="25" tabindex="3" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Fill this field if you don\'t want a random password.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="add_user" value="<?php echo $lang_admin['Add'] ?>" tabindex="4" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="user-search-page"><h2 class="tab"><?php echo $lang_admin['User search'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "user-search-page" ) );</script>
		<div class="box">
			<form id="find_user" method="post" action="admin_users.php?action=find_user">
				<div class="inform">
					<fieldset>
						<legend>Enter search criteria</legend>
						<div class="infldset">
							<p><?php echo $lang_admin['help_user_search'] ?></p>
							<table  class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin['Username'] ?></th>
									<td><input type="text" class="textbox" name="username" size="30" maxlength="25" tabindex="2" /></td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin['E-Mail'] ?></th>
									<td><input type="text" class="textbox" name="form[email]" size="30" maxlength="50" tabindex="3" /></td>
								</tr>
								<tr>
									<th scope="row">Title</th>
									<td><input type="text" class="textbox" name="form[title]" size="30" maxlength="50" tabindex="4" /></td>
								</tr>
								<tr>
									<th scope="row">Real name</th>
									<td><input type="text" class="textbox" name="form[realname]" size="30" maxlength="40" tabindex="5" /></td>
								</tr>
								<tr>
									<th scope="row">Website</th>
									<td><input type="text" class="textbox" name="form[url]" size="30" maxlength="100" tabindex="6" /></td>
								</tr>
								<tr>
									<th scope="row">Jabber</th>
									<td><input type="text" class="textbox" name="form[jabber]" size="30" maxlength="12" tabindex="7" /></td>
								</tr>
								<tr>
									<th scope="row">ICQ</th>
									<td><input type="text" class="textbox" name="form[icq]" size="30" maxlength="12" tabindex="7" /></td>
								</tr>
								<tr>
									<th scope="row">MSN Messenger</th>
									<td><input type="text" class="textbox" name="form[msn]" size="30" maxlength="50" tabindex="8" /></td>
								</tr>
								<tr>
									<th scope="row">AOL IM</th>
									<td><input type="text" class="textbox" name="form[aim]" size="30" maxlength="20" tabindex="9" /></td>
								</tr>
								<tr>
									<th scope="row">Yahoo! Messenger</th>
									<td><input type="text" class="textbox" name="form[yahoo]" size="30" maxlength="20" tabindex="10" /></td>
								</tr>
								<tr>
									<th scope="row">Location</th>
									<td><input type="text" class="textbox" name="form[location]" size="30" maxlength="30" tabindex="11" /></td>
								</tr>
								<tr>
									<th scope="row">Signature</th>
									<td><input type="text" class="textbox" name="form[signature]" size="30" maxlength="512" tabindex="12" /></td>
								</tr>
								<tr>
									<th scope="row">Admin note</th>
									<td><input type="text" class="textbox" name="form[admin_note]" size="30" maxlength="30" tabindex="13" /></td>
								</tr>
								<tr>
									<th scope="row">Number of posts greater than</th>
									<td><input type="text" class="textbox" name="posts_greater" size="5" maxlength="8" tabindex="14" /></td>
								</tr>
								<tr>
									<th scope="row">Number of posts less than</th>
									<td><input type="text" class="textbox" name="posts_less" size="5" maxlength="8" tabindex="15" /></td>
								</tr>
								<tr>
									<th scope="row">Last post is after</th>
									<td><input type="text" class="textbox" name="last_post_after" size="30" maxlength="19" tabindex="16" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('(yyyy-mm-dd hh:mm:ss)');" onmouseout="return nd();" alt="" /></td>
								</tr>
								<tr>
									<th scope="row">Last post is before</th>
									<td><input type="text" class="textbox" name="last_post_before" size="30" maxlength="19" tabindex="17" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('(yyyy-mm-dd hh:mm:ss)');" onmouseout="return nd();" alt="" /></td>
								</tr>
								<tr>
									<th scope="row">Registered after</th>
									<td><input type="text" class="textbox" name="registered_after" size="30" maxlength="19" tabindex="18" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('(yyyy-mm-dd hh:mm:ss)');" onmouseout="return nd();" alt="" /></td>
								</tr>
								<tr>
									<th scope="row">Registered before</th>
									<td><input type="text" class="textbox" name="registered_before" size="30" maxlength="19" tabindex="19" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('(yyyy-mm-dd hh:mm:ss)');" onmouseout="return nd();" alt="" /></td>
								</tr>
								<tr>
									<th scope="row">Order by</th>
									<td>
										<select name="order_by" tabindex="20">
											<option value="username" selected="selected">username</option>
											<option value="email">e-mail</option>
											<option value="num_posts">posts</option>
											<option value="last_post">last post</option>
											<option value="registered">registered</option>
										</select>&nbsp;&nbsp;&nbsp;<select name="direction" tabindex="21">
											<option value="ASC" selected="selected">ascending</option>
											<option value="DESC">descending</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">User group</th>
									<td>
										<select name="user_group" tabindex="22">
												<option value="all" selected="selected">All groups</option>
<?php
	$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.USER_GUEST.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
	while ($cur_group = $db->fetch_assoc($result)) echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
?>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="find_user" value="<?php echo $lang_admin['Search'] ?>" tabindex="23" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="ip-search-page"><h2 class="tab"><?php echo $lang_admin['IP search'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "ip-search-page" ) );</script>
		<div class="box">
			<form method="get" action="admin_users.php">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin['help_ip_search'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">IP address</th>
									<td><input type="text" class="textbox" name="show_users" size="30" maxlength="15" tabindex="24" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The IP address to search for in the post database.');" onmouseout="return nd();" alt="" />
</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input class="b1" type="submit" value="<?php echo $lang_admin['Find'] ?>" tabindex="25" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="prune-users-page"><h2 class="tab"><?php echo $lang_admin['Prune'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "prune-users-page" ) );</script>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Prune by</th>
								<td>
									<input type="radio" name="prune_by" value="1" checked="checked" />&nbsp;<strong>Registed date</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="prune_by" value="0" />&nbsp;<strong>Last Login</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This decides if the minimum number of days is calculated since the last login or the registered date.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Minimum days since registration/last login</th>
								<td>
									<input type="text" class="textbox" name="days" value="28" size="25" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The minimum number of days before users are pruned by the setting specified above.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Maximum number of posts</th>
								<td>
									<input type="text" class="textbox" name="posts" value="1"  size="25" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Users with more posts than this won\'t be pruned. e.g. a value of 1 will remove users with no posts.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">Delete admins and mods?</th>
								<td>
									<input type="radio" name="admods_delete" value="1" />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="admods_delete" value="0" checked="checked" />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('If Yes, any affected Moderators and Admins will also be pruned.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
							<tr>
								<th scope="row">User status</th>
								<td>
									<input type="radio" name="verified" value="1" />&nbsp;<strong>Delete any</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="verified" value="0" checked="checked" />&nbsp;<strong>Delete only verified</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="verified" value="2" />&nbsp;<strong>Delete only unverified</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Decideds if (un)verified users should be deleted.');" onmouseout="return nd();" alt="" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="prune" value="<?php echo $lang_admin['Prune'] ?>" tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php
	require FORUM_ROOT.'admin/admin_footer.php';
}
?>