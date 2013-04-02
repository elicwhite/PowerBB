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
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/chatbox.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/post.php';
if (isset($_GET['get_host']))
{
	if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
	if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $_GET['get_host'])) $ip = $_GET['get_host'];
	else
	{
		$get_host = intval($_GET['get_host']);
		if ($get_host < 1) message($lang_common['Bad request']);
		$result = $db->query('SELECT poster_ip FROM '.$db->prefix.'chatbox_msg WHERE id='.$get_host) or error('Unable to fetch post IP address', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		$ip = $db->result($result);
	}
	message('The IP address is: '.$ip.'<br />The host name is: '.@@gethostbyaddr($ip).'<br /><br /><a href="admin_users.php?show_users='.$ip.'">Show more users for this IP</a>');
}
$page_title = convert_htmlspecialchars($lang_chatbox['Page_title']);
define('ALLOW_INDEX', 1);
require FORUM_ROOT.'header.php';
if ($forum_user['g_read_chatbox'] != '1') message($lang_chatbox['No Read Permission']);
if (isset($_POST['form_sent']))
{
	if (($forum_user['is_guest'] && $_POST['form_user'] != 'Guest') || (!$forum_user['is_guest'] && $_POST['form_user'] != $forum_user['username'])) message($lang_common['Bad request']);
	$errors = array();
	if (!$forum_user['is_guest'] && $forum_user['last_post_chatbox'] != '' && (time() - $forum_user['last_post_chatbox']) < $forum_user['g_post_flood_chatbox']) $errors[] = $lang_post['Flood start'].' '.$forum_user['g_post_flood_chatbox'].' '.$lang_post['flood end'];
	if ($forum_user['is_guest'])
	{
		$result = $db->query('SELECT id, poster_ip, posted FROM '.$db->prefix.'chatbox_msg WHERE poster_ip=\''.get_remote_address().'\' ORDER BY posted DESC LIMIT 1') or error('Unable to fetch messages for flood protection', __FILE__, __LINE__, $db->error());
		$cur_post = $db->fetch_assoc($result);
		if ((time() - $cur_post['posted']) < $forum_user['g_post_flood_chatbox'])
  		$errors[] = $lang_post['Flood start'].' '.$forum_user['g_post_flood_chatbox'].' '.$lang_post['flood end'];
	}
	if (!$forum_user['is_guest'])
	{
		$username = $forum_user['username'];
		$displayname = $forum_user['displayname'];
		$email = $forum_user['email'];
	}
	else
	{
		$username = trim($_POST['req_username']);
		$email = strtolower(trim(($configuration['p_force_guest_email'] == '1') ? $_POST['req_email'] : $_POST['email']));
		require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
		require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';
		if (strlen($username) < 2) $errors[] = $lang_prof_reg['Username too short'];
		else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) $errors[] = $lang_prof_reg['Username guest'];
		else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) $errors[] = $lang_prof_reg['Username IP'];
		if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false) $errors[] = $lang_prof_reg['Username reserved chars'];
		if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) $errors[] = $lang_prof_reg['Username BBCode'];
		$temp = censor_words($username);
		if ($temp != $username) $errors[] = $lang_register['Username censor'];
		$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\' OR username=\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$busy = $db->result($result);
			$errors[] = $lang_register['Username dupe 1'].' '.convert_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2'];
		}
		if ($configuration['p_force_guest_email'] == '1' || $email != '')
		{
			require FORUM_ROOT.'include/email.php';
			if (!is_valid_email($email)) $errors[] = $lang_common['Invalid e-mail'];
		}
	}
	$message = forum_linebreaks(forum_trim($_POST['req_message']));
	if ($message == '') $errors[] = $lang_chatbox['Error No message'];
	else if (strlen($message) > $configuration['cb_msg_maxlength']) $errors[] = $lang_chatbox['Error Too long message'];
	else if ($configuration['p_message_all_caps'] == '0' && strtoupper($message) == $message && $forum_user['g_id'] > USER_MOD) $message = ucwords(strtolower($message));
	if ($configuration['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
	{
		require FORUM_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}
	if (empty($errors))
	{
		$now = time();
		if (!$forum_user['is_guest'])
		{
			$db->query('INSERT INTO '.$db->prefix.'chatbox_msg (poster, poster_id, poster_ip, message, posted) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', \''.$db->escape($message).'\', '.$now.')') or error('Unable to post message', __FILE__, __LINE__, $db->error());
  			$low_prio = ($db_type == 'mysql') ? 'LOW_PRIORITY ' : '';
  			$db->query('UPDATE '.$low_prio.$db->prefix.'users SET num_posts_chatbox=num_posts_chatbox+1, last_post_chatbox='.$now.' WHERE id='.$forum_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

		}
		else
		{
			$email_sql = ($configuration['p_force_guest_email'] == '1' || $email != '') ? '\''.$email.'\'' : 'NULL';
			$db->query('INSERT INTO '.$db->prefix.'chatbox_msg (poster, poster_id, poster_ip, poster_email, message, posted) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($message).'\', '.$now.')') or error('Unable to post message', __FILE__, __LINE__, $db->error());
		}
		$count = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'chatbox_msg') or error('Unable to fetch chatbox post count', __FILE__, __LINE__, $db->error());
		$num_post = $db->result($count);
		$limit = ($num_post-$configuration['cb_max_msg'] <= 0) ? 0 : $num_post-$configuration['cb_max_msg'];
		$result = $db->query('SELECT id,posted FROM '.$db->prefix.'chatbox_msg ORDER BY posted ASC LIMIT '.$limit) or error('Unable to select post to delete', __FILE__, __LINE__, $db->error());
		while ($del_msg = $db->fetch_assoc($result))
		{
			$db->query('DELETE FROM '.$db->prefix.'chatbox_msg WHERE id = '.$del_msg['id'].' LIMIT 1') or error('Unable to delete post', __FILE__, __LINE__, $db->error());
		}
      	$_POST['req_message'] = NULL;
	}
}
if (!empty($errors))
{
?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul>
<?php
	while (list(, $cur_error) = each($errors)) echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>

<?php
}
?>
<div class="block">
  <h2>
    <span><?php echo $lang_chatbox['Chatbox'] ?></span>
  </h2>
	<div class="box">
		<div class="inbox" style="overflow:auto;height:<?php echo $configuration['cb_height'] ?>px;">
<?php
if ($configuration['cb_enable'] != 1)
{
echo "Chatbox is currently disabled";
}
else
{
require FORUM_ROOT.'include/parser.php';
$cur_msg_txt = '';
$count_id = array();
$result = $db->query('SELECT u.id, u.group_id, u.num_posts_chatbox, u.displayname, m.id AS m_id, m.poster_id, m.poster, m.poster_ip, m.poster_email, m.message, m.posted, g.g_id, g.g_title_chatbox FROM '.$db->prefix.'chatbox_msg AS m INNER JOIN '.$db->prefix.'users AS u ON u.id=m.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id ORDER BY m.posted DESC LIMIT '.$configuration['cb_max_msg']) or error('Unable to fetch messages', __FILE__, __LINE__, $db->error());

if ($cur_msg['displayname'] != "")
	{
		$cur_msg['poster'] = $cur_msg['displayname'];
	}
while ($cur_msg = $db->fetch_assoc($result))
{
	$cur_msg_txt .= $cur_msg['g_title_chatbox'].$configuration['cb_disposition'];
	if ($cur_msg['g_id'] != USER_GUEST) $cur_msg_txt = str_replace('<forum_username>', '<a href="profile.php?id='.$cur_msg['id'].'">'.convert_htmlspecialchars($cur_msg['poster']).'</a>', $cur_msg_txt);
	else $cur_msg_txt = str_replace('<forum_username>', convert_htmlspecialchars($cur_msg['poster']), $cur_msg_txt);
	$cur_msg_txt = str_replace('<power_date>', format_time($cur_msg['posted']), $cur_msg_txt);
	if ($cur_msg['g_id'] != USER_GUEST) $cur_msg_txt = str_replace('<power_nbpost>', $cur_msg['num_posts_chatbox'], $cur_msg_txt);
	else
	{
		if (!isset($count_id[$cur_msg['poster']]))
		{
			$like_command = ($db_type == 'pgsql') ? 'ILIKE' : 'LIKE';
			$count = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'chatbox_msg WHERE poster '.$like_command.' \''.$db->escape(str_replace('*', '%', $cur_msg['poster'])).'\'') or error('Unable to fetch user chatbox post count', __FILE__, __LINE__, $db->error());
			$num_post = $db->result($count);
			$count_id[$cur_msg['poster']] = $num_post;
		}
		else $num_post = $count_id[$cur_msg['poster']];
		$cur_msg_txt = str_replace('<power_nbpost>', $num_post, $cur_msg_txt);
	}
	$cur_msg_txt = str_replace('<power_nbpost_txt>', $lang_chatbox['Posts'], $cur_msg_txt);
	if ($forum_user['g_id'] < USER_GUEST)
	{
		$cur_msg_admin = ' [ <a href="chatbox.php?get_host='.$cur_msg['m_id'].'">'.$cur_msg['poster_ip'].'</a>';
		if ($cur_msg['poster_email']) $cur_msg_admin .= ' | <a href="mailto:'.$cur_msg['poster_email'].'">'.$lang_common['E-mail'].'</a> ]';
		else $cur_msg_admin .= ' ] ';
	}
	else $cur_msg_admin = '';
	$cur_msg_txt = str_replace('<power_admin>', $cur_msg_admin, $cur_msg_txt);
	$cur_msg_txt = str_replace('<power_message>', parse_message($cur_msg['message'], 0), $cur_msg_txt);
}
if (!$cur_msg_txt) echo $lang_chatbox['No Message'];
else echo $cur_msg_txt;
?>
    </div>
  </div>
  <h2>
    <span>
<?php
	$cur_index = 1;
?>
	<form id="formulaire" method="post" action="chatbox.php" onsubmit="return(chatbox_send(this))">
	<input type="hidden" name="form_sent" value="1" />
	<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['username']) : 'Guest'; ?>" />
<?php
	if ($forum_user['is_guest'])
	{
		$email_label = ($configuration['p_force_guest_email'] == '1') ? '<strong>'.$lang_common['E-mail'].':</strong>' : $lang_common['E-mail'];
		$email_form_name = ($configuration['p_force_guest_email'] == '1') ? 'req_email' : 'email';
?>
          	<strong><?php echo $lang_post['Guest name'] ?>:</strong> <input type="text" name="req_username" value="<?php if (isset($_POST['req_username'])) echo convert_htmlspecialchars($username); ?>" size="20" maxlength="25" tabindex="<?php echo $cur_index++ ?>" /> 
          	<?php echo $email_label ?> <input type="text" name="<?php echo $email_form_name ?>" value="<?php if (isset($_POST[$email_form_name])) echo convert_htmlspecialchars($email); ?>" size="20" maxlength="50" tabindex="<?php echo $cur_index++ ?>" /> 
<?php
	}
?>
	<strong><?php echo $lang_chatbox['Message']; ?>:</strong> <input type="text" name="req_message"  value="<?php if (isset($_POST['req_message'])) echo convert_htmlspecialchars($message); ?>" size="35" maxlength="<?php echo $configuration['cb_msg_maxlength']; ?>"  tabindex="<?php echo $cur_index++ ?>" /> 
	<input class="b1" type="submit" name="submit" value="<?php echo $lang_chatbox['Btn Send']; ?>" accesskey="s" tabindex="<?php echo $cur_index++; ?>" /> <input type="button" class="b1" value="<?php echo $lang_chatbox['Btn Refresh']; ?>" onclick="javascript:window.location='chatbox.php';"  tabindex="<?php echo $cur_index++; ?>" />
      </form>
      <script type="text/javascript">
	document.forms[0].req_message.focus();
	function chatbox_send(formulaire)
	{
		formulaire.elements["submit"].value = "<?php echo $lang_chatbox['Sending'] ?>";
            formulaire.elements["submit"].disabled="yes";
            document.formulaire.submit();
            return true;
	}
	</script>
<?php
}
echo "</h2></div>";

	require FORUM_ROOT.'footer.php';
?>
