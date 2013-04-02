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
require FORUM_ROOT.'include/parser.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/topic.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/misc.php';
if(!$configuration['o_pms_enabled'] || $forum_user['g_pm'] == 0) message($lang_common['No permission']);
if ($forum_user['is_guest']) message($lang_common['Login required']);
if(isset($_GET['box'])) $box = (int)($_GET['box']);
else $box = 0;
$box != 1 ? $box = 0 : $box = 1;
$box != 1 ? $status = 0 : null;
$box == 0 ? $name = $lang_pms['Inbox'] : $name = $lang_pms['Outbox'];
$page_name = $name;
if( isset($_POST['delete_messages']) || isset($_POST['delete_messages_comply']) )
{
	if( isset($_POST['delete_messages_comply']) )
	{
		confirm_referrer('message_list.php');
		$db->query('DELETE FROM '.$db->prefix.'messages WHERE id IN('.$_POST['messages'].') AND owner=\''.$forum_user['id'].'\'') or error('Unable to delete messages.', __FILE__, __LINE__, $db->error());
		redirect(FORUM_ROOT.'message_list.php?box='.$_POST['box'], $lang_pms['Deleted redirect']);
	}
	else
	{
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_pms['Multidelete'];
		$idlist = $_POST['delete_messages'];
		require FORUM_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_pms['Multidelete'] ?></span></h2>
	<div class="box">
		<form method="post" action="message_list.php">
			<input type="hidden" name="messages" value="<?php echo implode(',', array_values($idlist)) ?>">
			<input type="hidden" name="box" value="<?php echo $_POST['box']; ?>">
			<div class="inform">
				<fieldset>
					<div class="infldset">
						<p class="warntext"><strong><?php echo $lang_pms['Delete messages comply'] ?></strong></p>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input class="b1" type="submit" name="delete_messages_comply" value="<?php echo $lang_pms['Delete'] ?>" /></p>
		</form>
	</div>
</div>
<?php
		require FORUM_ROOT.'footer.php';
	}
}
else if (isset($_GET['action']) && $_GET['action'] == 'markall')
{
	$db->query('UPDATE '.$db->prefix.'messages SET showed=1 WHERE owner='.$forum_user['id']) or error('Unable to update message status', __FILE__, __LINE__, $db->error());
	$p = (!isset($_GET['p']) || $_GET['p'] <= 1) ? 1 : $_GET['p'];
	redirect(FORUM_ROOT.'message_list.php?box='.$box.'&p='.$p, $lang_pms['Read redirect']);
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_pms['Private Messages'].' - '.$name;
$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE status='.$box.' AND owner='.$forum_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
list($num_messages) = $db->fetch_row($result);
$num_pages = ceil($num_messages / $configuration['o_pms_mess_per_page']);
$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $configuration['o_pms_mess_per_page'] * ($p - 1);
$limit = $start_from.','.$configuration['o_pms_mess_per_page'];
require FORUM_ROOT.'header.php';
?>
<div class="block2col">
	<div class="blockmenu" style="padding: 0px 10px 0px 0px; margin-top: 10px;">
		<h2><span><?php echo $lang_pms['Private Messages'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li <?php if ($box == 0) echo 'class="isactive"' ?>><a href="message_list.php?box=0"><?php echo $lang_pms['Inbox'] ?></a></li>
					<li <?php if ($box == 1) echo 'class="isactive"' ?>><a href="message_list.php?box=1"><?php echo $lang_pms['Outbox'] ?></a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="linkst">
		<div class="inbox">
			<p class="pagelink conl"><?php echo $lang_common['Pages'].': '.paginate($num_pages, $p, 'message_list.php?box='.$box) ?></p>
			<p class="postlink conr" id="postbuttons"><a href="message_send.php"><?php echo $lang_pms['New message'] ?></a></p>
			<ul><li><?php echo $lang_pms['Private Messages'] ?>&nbsp;</li><li>&raquo;&nbsp;<?php echo $page_name ?></li></ul>
		</div>
	</div>

<?php
if(isset($_GET['id']))
{
	$id = intval($_GET['id']);
	$result = $db->query('SELECT status,owner FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to get message status', __FILE__, __LINE__, $db->error());
	list($status, $owner) = $db->fetch_row($result);
	$status == 0 ? $where = 'u.id=m.sender_id' : $where = 'u.id=m.owner';
	$result = $db->query('
	SELECT m.id AS mid,
	m.subject,
	m.sender_ip,
	u.displayname as displayposter,
	m.message,
	m.smileys,
	m.posted,
	m.showed,
	u.id,
	u.group_id as g_id,
	g.g_user_title,
	u.username,
	u.displayname,
	u.registered,
	u.email,
	u.title,
	u.url,
	u.icq,
	u.msn,
	u.aim,
	u.yahoo,
	u.location,
	u.use_avatar,
	u.email_setting,
	u.num_posts,
	u.admin_note,
	u.signature,
	o.user_id AS is_online
	FROM '.$db->prefix.'messages AS m,'.$db->prefix.'users AS u
	LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.idle=0)
	LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id = g.g_id WHERE '.$where.' AND m.id='.$id.' AND displayname=m.sender'
	) or error(mysql_error(), __FILE__, __LINE__, $db->error());
	$cur_post = $db->fetch_assoc($result);
	if ($owner != $forum_user['id']) message($lang_common['No permission']);
	if ($cur_post['showed'] == 0) $db->query('UPDATE '.$db->prefix.'messages SET showed=1 WHERE id='.$id) or error('Unable to update message info', __FILE__, __LINE__, $db->error());
	if ($cur_post['id'] > 0)
	{
		$username = '<a href="profile.php?id='.$cur_post['id'].'">'.convert_htmlspecialchars($cur_post['username']).'</a>';
		$cur_post['username'] = $cur_post['username'];
		$user_title = get_title($cur_post);
		if ($configuration['o_censoring'] == '1') $user_title = censor_words($user_title);
		$is_online = ($cur_post['is_online'] == $cur_post['id']) ? '<strong>'.$lang_topic['Online'].'</strong>' : $lang_topic['Offline'];
		if ($configuration['o_avatars'] == '1' && $cur_post['use_avatar'] == '1' && $forum_user['show_avatars'] != '0')
		{
			if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$cur_post['id'].'.gif')) $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/'.$cur_post['id'].'.gif" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$cur_post['id'].'.jpg')) $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/'.$cur_post['id'].'.jpg" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$cur_post['id'].'.png')) $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/'.$cur_post['id'].'.png" '.$img_size[3].' alt="" />';
		}
		else $user_avatar = '';
		if ($configuration['o_show_user_info'] == '1')
		{
			if ($cur_post['location'] != '')
			{
				if ($configuration['o_censoring'] == '1') $cur_post['location'] = censor_words($cur_post['location']);
				$user_info[] = '<dd>'.$lang_topic['From'].': '.convert_htmlspecialchars($cur_post['location']);
			}
			$user_info[] = '<dd>'.$lang_common['Registered'].': '.date($configuration['o_date_format'], $cur_post['registered']);
			if ($configuration['o_show_post_count'] == '1' || $forum_user['g_id'] < USER_GUEST) $user_info[] = '<dd>'.$lang_common['Posts'].': '.$cur_post['num_posts'];
			if (($cur_post['email_setting'] == '0' && !$forum_user['is_guest']) || $forum_user['g_id'] < USER_GUEST) $user_contacts[] = '<a href="mailto:'.$cur_post['email'].'">'.$lang_common['E-mail'].'</a>';
			else if ($cur_post['email_setting'] == '1' && !$forum_user['is_guest']) $user_contacts[] = '<a href="misc.php?email='.$cur_post['id'].'">'.$lang_common['E-mail'].'</a>';
			require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
			if($configuration['o_pms_enabled'] && !$forum_user['is_guest'] && $forum_user['g_pm'] == 1)
			{
				$pid = isset($cur_post['poster_id']) ? $cur_post['poster_id'] : $cur_post['id'];
				$user_contacts[] = '<a href="message_send.php?id='.$pid.'&tid='.$id.'">'.$lang_pms['PM'].'</a>';
			}
			if ($cur_post['url'] != '') $user_contacts[] = '<a href="'.convert_htmlspecialchars($cur_post['url']).'">'.$lang_topic['Website'].'</a>';
		}
		if ($forum_user['g_id'] < USER_GUEST)
		{
			$user_info[] = '<dd>IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['sender_ip'].'</a>';
			if ($cur_post['admin_note'] != '') $user_info[] = '<dd>'.$lang_topic['Note'].': <strong>'.convert_htmlspecialchars($cur_post['admin_note']).'</strong>';
		}
		if(!$status) $post_actions[] = '<li><a href="message_send.php?id='.$cur_post['id'].'&amp;reply='.$cur_post['mid'].'">'.$lang_pms['Reply'].'</a>';
		$post_actions[] = '<li><a href="message_delete.php?id='.$cur_post['mid'].'&amp;box='.(int)$_GET['box'].'&amp;p='.(int)$_GET['p'].'">'.$lang_pms['Delete'].'</a>';
		if(!$status) $post_actions[] = '<li><a href="message_send.php?id='.$cur_post['id'].'&amp;quote='.$cur_post['mid'].'">'.$lang_pms['Quote'].'</a>';
	}
	else
	{
		$result = $db->query('SELECT m.id,m.sender,m.message,m.posted,u.username,u.displayname FROM '.$db->prefix.'messages as m INNER JOIN '.$db->prefix.'users as u ON u.username=m.sender WHERE m.id='.$id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
		$cur_post = $db->fetch_assoc($result);
		if($cur_post['displayname'] != "") $cur_post['sender'] = $cur_post['displayname'];
		$username = convert_htmlspecialchars($cur_post['sender']);
		$user_title = "";
		$post_actions[] = '<li><a href="message_delete.php?id='.$cur_post['id'].'&amp;box='.(int)$_GET['box'].'&amp;p='.(int)$_GET['p'].'">'.$lang_pms['Delete'].'</a>';
		$is_online = $lang_topic['Offline'];
	}
	$cur_post['smileys'] = isset($cur_post['smileys']) ? $cur_post['smileys'] : $forum_user['show_smilies'];
	$cur_post['message'] = parse_message($cur_post['message'], (int)(!$cur_post['smileys']));
	if (isset($cur_post['signature']) && $forum_user['show_sig'] != '0')
	{
		$signature = parse_signature($cur_post['signature']);
	}
?>

	<div id="p<?php echo $cur_post['id'] ?>" class="blockpost row_odd firstpost" style="padding-left: 153px;">
		<h2><span><?php echo format_time($cur_post['posted']) ?></span></h2>
		<div class="box">
			<div class="inbox">
				<div class="postleft">
					<dl>
						<dt><strong><?php echo $username ?></strong></dt>
						<dd class="usertitle"><strong><?php echo $user_title ?></strong></dd>
						<dd class="postavatar"><?php if (isset($user_avatar)) echo $user_avatar ?></dd>
	<?php if (isset($user_info)) if (count($user_info)) echo "\t\t\t\t\t".implode('</dd>'."\n\t\t\t\t\t", $user_info).'</dd>'."\n"; ?>
	<?php if (isset($user_contacts)) if (count($user_contacts)) echo "\t\t\t\t\t".'<dd class="usercontacts">'.implode('&nbsp;&nbsp;', $user_contacts).'</dd>'."\n"; ?>
					</dl>
				</div>
				<div class="postright">
					<div class="postmsg">
						<?php echo $cur_post['message']."\n" ?>
					</div>
	<?php if (isset($signature)) echo "\t\t\t\t".'<div class="postsignature"><hr />'.$signature.'</div>'."\n"; ?>
				</div>
				<div class="clearer"></div>
				<div class="postfootleft"><?php if ($cur_post['id'] > 1) echo '<p>'.$is_online.'</p>'; ?></div>
				<div class="postfootright"><?php echo (count($post_actions)) ? '<ul>'.implode($lang_topic['Link separator'].'</li>', $post_actions).'</li></ul></div>'."\n" : '<div>&nbsp;</div></div>'."\n" ?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
<?php	
}
?>
<form method="post" action="message_list.php">
<div class="blocktable" style="margin-left: 152px;">
	<h2><span><?php echo $name ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
<?php
		if($forum_user['g_pm_limit'] != 0 && $forum_user['g_id'] > USER_GUEST)
		{
			$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$forum_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
			list($tot_messages) = $db->fetch_row($result);
			$proc = ceil($tot_messages / $forum_user['g_pm_limit'] * 100);
			$status = ' - '.$lang_pms['Status'].' '.$proc.'%';
		}
		else 
			$status = '';
?>
					<th class="tcl"><?php echo $lang_pms['Subject'] ?><?php echo $status ?></th>
					<th class="tcl" style="width:100px;"><?php if($box == 0) echo $lang_pms['Sender']; else echo $lang_pms['Receiver']; ?></th>
					<?php if(isset($_GET['action']) && $_GET['action'] == 'multidelete') { ?>
					<th class="tcr"><?php echo $lang_pms['Date'] ?></th>
					<th class="tcr">Delete</th>
					<?php } else { ?>
					<th class="tcr"><?php echo $lang_pms['Date'] ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
<?php
$result = $db->query('SELECT m.*, u.username, u.displayname FROM '.$db->prefix.'messages as m INNER JOIN '.$db->prefix.'users as u on u.username=m.sender WHERE m.owner='.$forum_user['id'].' AND status='.$box.' ORDER BY posted DESC LIMIT '.$limit) or error('Unable to fetch messages list for forum', __FILE__, __LINE__, $db->error());
$new_messages = false;
$messages_exist = false;
if ($db->num_rows($result))
{
	$messages_exist = true;
	while ($cur_mess = $db->fetch_assoc($result))
	{
		if($cur_mess['displayname'] != "") $cur_mess['sender'] = $cur_mess['displayname'];
		$icon_text = $lang_common['Normal icon'];
		$icon_type = 'icon';
		if ($cur_mess['showed'] == '0')
		{
			$icon_text .= ' '.$lang_common['New icon'];
			$icon_type = 'icon inew';
		}
		($new_messages == false && $cur_mess['showed'] == '0') ? $new_messages = true : null;
		$subject = '<a href="message_list.php?id='.$cur_mess['id'].'&amp;p='.$p.'&amp;box='.(int)$box.'">'.convert_htmlspecialchars($cur_mess['subject']).'</a>';
		if (isset($_GET['id']))
			if($cur_mess['id'] == $_GET['id']) $subject = "<strong>$subject</strong>";
?>
	<tr>
		<td class="tcl">
			<div class="intd">
				<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo trim($icon_text) ?></div></div>
				<div class="tclcon">
					<?php echo $subject."\n" ?>
				</div>
			</div>
		</td>
		<td class="tc2" style="white-space: nowrap; OVERFLOW: hidden"><a href="profile.php?id=<?php echo $cur_mess['sender_id'] ?>"><?php echo $cur_mess['sender'] ?></a></td>
<?php if(isset($_GET['action']) && $_GET['action'] == 'multidelete') { ?>
		<td style="white-space: nowrap"><?php echo format_time($cur_mess['posted']) ?></td>
		<td style="text-align: center"><input type="checkbox" name="delete_messages[]" value="<? echo $cur_mess['id']; ?>"></td>
<?php } else { ?>
		<td class="tcr" style="white-space: nowrap"><?php echo format_time($cur_mess['posted']) ?></td>
<?php } ?>
	</tr>
<?php
	}
}
else
{
	$cols = isset($_GET['action']) ? '4' : '3';
	echo "\t".'<tr><td class="tcl" colspan="'.$cols.'">'.$lang_pms['No messages'].'</td></tr>'."\n";
}
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<div class="postlinksb">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $lang_common['Pages'].': '.paginate($num_pages, $p, 'message_list.php?box='.$box) ?></p>
<?php
if(isset($_GET['action']) && $_GET['action'] == 'multidelete')
{
?>
		<p class="postlink conr"><input type="hidden" name="box" value="<?php echo $box; ?>"><input class="b1" type="submit" value="Delete"></p>
<?php
}
else
{
?>
		<p class="postlink conr"><a href="message_send.php"><?php echo $lang_pms['New message']; ?></a></p>
<?php
}
?>
		<ul><li><a href="index.php"><?php echo convert_htmlspecialchars($configuration['o_board_name']) ?></a>&nbsp;</li><li>&raquo;&nbsp;<?php echo $lang_pms['Private Messages'] ?>&nbsp;</li><li>&raquo;&nbsp;<?php echo $page_name ?></li></ul>
		<div class="clearer"></div>
	</div>
</div>
</form>
	<div class="clearer"></div>
</div>
<?php
if(isset($_GET['id']))
{
	$forum_id = $id;
}
$footer_style = 'message_list';
require FORUM_ROOT.'footer.php';?>