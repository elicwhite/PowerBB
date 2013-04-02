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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/reputation.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/topic.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/polls.php'; 
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($id < 1 && $pid < 1) message($lang_common['Bad request']);
if ($pid) 
{
	$result = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$pid) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result)) message($lang_common['Bad request']);
	$id = $db->result($result); 
	$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$id.' ORDER BY posted'.($forum_user['reverse_posts']? ' DESC':'')) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$num_posts = $db->num_rows($result);
	for ($i = 0; $i < $num_posts; ++$i) 
	{
		$cur_id = $db->result($result, $i);
		if ($cur_id == $pid) break;
	} 
	++$i;
	$_GET['p'] = ceil($i / $forum_user['disp_posts']);
} 
else if ($action == 'new' && !$forum_user['is_guest']) 
{
	if(!empty($forum_user['read_topic']['t'][$id]))
	{
		$last_read = $forum_user['read_topic']['t'][$id];
	}
	else
	{
		$last_read = $forum_user['last_visit'];
	}
	$result = $db->query('SELECT MIN(id) FROM '.$db->prefix.'posts WHERE topic_id='.$id.' AND posted>'.$last_read) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$first_new_post_id = $db->result($result);
	if ($first_new_post_id) header('Location: view_poll.php?pid='.$first_new_post_id.'#p'.$first_new_post_id);
	else header('Location: view_poll.php?id='.$id.'&action=last');
	exit;
} 
else if ($action == 'last') 
{
	$result = $db->query('SELECT MAX(id) FROM '.$db->prefix.'posts WHERE topic_id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$last_post_id = $db->result($result);
	if ($last_post_id) 
	{
		header('Location: view_poll.php?pid='.$last_post_id.'#p'.$last_post_id);
		exit;
	} 
}
if (!$forum_user['is_guest']) $result = $db->query('SELECT pf.forum_name AS parent_forum, f.parent_forum_id, t.subject, t.closed, t.num_replies, t.sticky, t.last_post, t.question, t.yes, t.no, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$forum_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum_id=pf.id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.question!=\'\' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else $result = $db->query('SELECT pf.forum_name AS parent_forum, f.parent_forum_id, t.subject, t.closed, t.num_replies, t.sticky, t.question, t.yes, t.no, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum_id=pf.id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.question!=\'\' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_topic = $db->fetch_assoc($result);
$protected = $db->query("SELECT protected FROM ". $db->prefix ."forums WHERE `id` = '{$cur_topic['forum_id']}'");
$is_protect = $db->fetch_row($protected);
if(intval($is_protect[0]) == 1 && ($forum_user['group_id'] = 1 || $forum_user['group_id'] = 2))
{
	if(isset($_POST['check_pass']))
	{
		$fid = $cur_topic['forum_id'];
		$okpass = mysql_real_escape_string($_POST['pass']);
		$check = $db->query("SELECT password FROM ". $db->prefix ."forums WHERE `password` = '{$okpass}' AND `id` = '$fid'") or error(mysql_error());
		if($db->num_rows($check) < 1)
		{
			message($lang_common['No Forum Auth']);
		}
	}
	else
	{
		message($lang_common['Password Protected'] .'
		 	<form method="post" action="view_topic.php?id='. $id .'">
			 Password: <input type="password" name="pass"><br /><br />
		 	<input type="submit" name="check_pass">
		 	</form>');
	}
}
if (!$forum_user['is_guest']) mark_topic_read($id, $cur_topic['forum_id'], $cur_topic['last_post']);
$mods_array = ($cur_topic['moderators'] != '') ? unserialize($cur_topic['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
if ($cur_topic['closed'] == '0')
{
	if (($cur_topic['post_replies'] == '' && $forum_user['g_post_replies'] == '1') || $cur_topic['post_replies'] == '1' || $is_admmod) $post_link = '<a href="poll.php?tid=' . $id . '">' . $lang_topic['Post reply'] . '</a>';
	else $post_link = '<a href="login.php">'.$lang_topic['Post reply'].'</a>'; 
} 
else 
{
	$post_link = $lang_topic['Topic closed'];
	if ($is_admmod) $post_link .= ' / <a href="poll.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
} 
$num_pages = ceil(($cur_topic['num_replies'] + 1) / $forum_user['disp_posts']);
$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $forum_user['disp_posts'] * ($p - 1);
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'view_poll.php?id='.$id);
if ($configuration['o_censoring'] == '1') $cur_topic['subject'] = censor_words($cur_topic['subject']);
$quickpost = false;
if ($configuration['o_quickpost'] == '1' && !$forum_user['is_guest'] && ($cur_topic['post_replies'] == '1' || ($cur_topic['post_replies'] == '' && $forum_user['g_post_replies'] == '1')) && ($cur_topic['closed'] == '0' || $is_admmod))
{
	$required_fields = array('req_message' => $lang_common['Message']);
	$quickpost = true;
} 
if (!$forum_user['is_guest'] && $configuration['o_subscriptions'] == '1')
{
	if ($cur_topic['is_subscribed']) $subscraction = '<p class="subscribelink clearb"><a href="#">'.$lang_topic['Go to Top'].'</a>&nbsp;&nbsp;'.$lang_topic['Is subscribed'].' - <a href="misc.php?unsubscribe='.$id.'">'.$lang_topic['Unsubscribe'].'</a></p>'."\n";
	else $subscraction = '<p class="subscribelink clearb"><a href="#">'.$lang_topic['Go to Top'].'</a>&nbsp;&nbsp;<a href="misc.php?subscribe='.$id.'">'.$lang_topic['Subscribe'].'</a></p>'."\n";
}
else $subscraction = '<div class="clearer"></div>'."\n";
if ($cur_topic['question']) $page_title = convert_htmlspecialchars($configuration['o_board_name'] . ' / ' . $cur_topic['question'] . ' - ' . $cur_topic['subject']);
define('ALLOW_INDEX', 1);
require FORUM_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<p class="postlink conr"><?php echo $post_link ?></p>
<?php
if ($configuration['o_rewrite_urls'] == '1')
{
	if($cur_topic['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="'.makeurl("f", $cur_topic['parent_forum_id'], $cur_topic['parent_forum']).'">'.convert_htmlspecialchars($cur_topic['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="'.makeurl("f", $cur_topic['forum_id'], $cur_topic['forum_name']).'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a></li><li>&nbsp;&raquo;&nbsp;<a href="'.makeurl("f", $cur_topic['forum_id'], $cur_topic['forum_name']).'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a></li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
}
else
{
	if($cur_topic['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="view_forum.php?id='.$cur_topic['parent_forum_id'].'">'.convert_htmlspecialchars($cur_topic['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="view_forum.php?id='.$cur_topic['forum_id'].'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a></li><li>&nbsp;&raquo;&nbsp;<a href="view_forum.php?id='.$cur_topic['forum_id'].'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a></li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
}
?>
		<div class="clearer"></div>
	</div>
</div>
<?php
require FORUM_ROOT.'include/parser.php';
$bg_switch = true;
$post_count = 0;
$result = $db->query('SELECT ptype,options,voters,votes FROM ' . $db->prefix . 'polls WHERE pollid=' . $id . '') or error('Unable to fetch poll info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_poll = $db->fetch_assoc($result);
$options = unserialize($cur_poll['options']);
if (!empty($cur_poll['voters'])) $voters = unserialize($cur_poll['voters']);
else $voters = array();
$ptype = $cur_poll['ptype']; 
$firstcheck = false;
?>
<div class="blockform">
	<h2><span><?php echo $lang_polls['Poll'] ?></span></h2>
	<div class="box">
<?php
if ((!$forum_user['is_guest']) && (!in_array($forum_user['id'], $voters)) && ($cur_topic['closed'] == '0'))
{
	$showsubmit = true;
?>
		<form id="post" method="post" action="vote.php">
			<div class="inform">
				<div class="rbox" style="text-align:center;">
						<input type="hidden" name="poll_id" value="<?php echo $id; ?>" />
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['username']) : 'Guest'; ?>" /><strong><?php echo convert_htmlspecialchars($cur_topic['question']) ?></strong><br /><br />
		<table style="WIDTH: auto; TABLE-LAYOUT: auto; TEXT-ALIGN: center; BORDER: 0; CELLSPACING: 0; CELLPADDING: 0;">
<?php
	if ($ptype == 1)
	{
		while (list($key, $value) = each($options))
		{
?>
				<tr><td style="WIDTH: 10; BORDER: 0;"><input name="vote" <?php if (!$firstcheck) { echo "checked"; $firstcheck = true; }; ?> type="radio" value="<?php echo $key ?>"></td><td style="BORDER: 0; WIDTH: auto;"><span><?php echo convert_htmlspecialchars($value);
                ?></span></td></tr>
<?php
		} 
	}
	elseif ($ptype == 2)
	{
		while (list($key, $value) = each($options))
		{
?>
				<tr><td style="WIDTH: 10; BORDER: 0;"><input name="options[<?php echo $key ?>]" type="checkbox" value="1"></td><td style="BORDER: 0; WIDTH: auto;"><span><?php echo convert_htmlspecialchars($value);?>
				</span></td></tr>
<?php
		} 
	}
	elseif ($ptype == 3)
	{
		while (list($key, $value) = each($options))
		{
?>
				<tr><td style="WIDTH: auto; BORDER: 0;"><?php echo convert_htmlspecialchars($value); ?></td><td style="BORDER: 0; WIDTH: auto;"><input name="options[<?php echo $key ?>]" checked type="radio" value="yes"> <?php echo $cur_topic['yes']; ?></td><td style="BORDER: 0; WIDTH: auto;"><input name="options[<?php echo $key ?>]" type="radio" value="no"> <?php echo $cur_topic['no']; ?></td></tr>
<?php
		} 
	}
	else message($lang_common['Bad request']);
}
else
{
	$showsubmit = false;
?>
				<div class="inform">
				<div class="rbox" style="text-align:center;">
				<strong><?php echo convert_htmlspecialchars($cur_topic['question']) ?></strong><br /><br />
				<table style="WIDTH: auto; TABLE-LAYOUT: auto; TEXT-ALIGN: left; BORDER: 0; CELLSPACING: 0; CELLPADDING: 0;">
<?php
	if (!empty($cur_poll['votes'])) $votes = unserialize($cur_poll['votes']);
	else $votes = array();
	if ($ptype == 1 || $ptype == 2) 
	{
		$total = 0;
		$percent = 0;
		$percent_int = 0;
		while (list($key, $val) = each($options)) 
		{
			if (isset($votes[$key])) $total += $votes[$key];
		}
		reset($options);
	}
	while (list($key, $value) = each($options))
	{    
		if ($ptype == 1 || $ptype == 2)
            { 
			if (isset($votes[$key]))
			{
				$percent =  $votes[$key] * 100 / $total;
				$percent_int = floor($percent);
			}
			if (isset($votes[$key])) $vote_bar = '<span><img src="./img/'.$forum_user['style'].'/bar.gif" width="'.$percent_int.'" height="8px" alt="" /></span>';
			else $vote_bar = '<span><img src="./img/'.$forum_user['style'].'/bar.gif" height="8px" alt="" /></span>';
?>
				<tr>
				<td style="WIDTH: auto; BORDER: 0;"><?php echo convert_htmlspecialchars($value); ?>
				</td>
				<td style="BORDER: 0; WIDTH: auto;">
				<?php echo $vote_bar ?>
				</td>
				<td style="BORDER: 0; WIDTH: auto;"> <?php if (isset($votes[$key])) echo $percent_int . "% - " . $votes[$key]; else echo ' 0% - 0'; ?>
				</td>
				</tr>
<?php
			}
			else if ($ptype == 3) 
            	{ 
				$total = 0;
				$yes_percent = 0;
				$no_percent = 0;
				$vote_yes = 0;
				$vote_no = 0;
				if (isset($votes[$key]['yes']))
				{
					$vote_yes = $votes[$key]['yes'];
				}
				if (isset($votes[$key]['no']))
				{
					$vote_no += $votes[$key]['no'];
				}
				$total = $vote_yes + $vote_no;
				if (isset($votes[$key]))
				{
					$yes_percent = floor(($vote_yes * 100) / $total);
					$no_percent = floor(($vote_no * 100) / $total);
				}
				if (isset($votes[$key]))
				{                
					$vote_bar_yes = '<span><img src="./img/'.$forum_user['style'].'/bar.gif" width="'.$yes_percent.'" height="8px" alt="" /></span>';
					$vote_bar_no = '<span><img src="./img/'.$forum_user['style'].'/bar.gif" width="'.$no_percent.'" height="8px" alt="" /></span>';
				}
				if ($yes_percent == 0) $vote_bar_yes = '<span><img src="./img/'.$forum_user['style'].'/bar.gif" height="8px" alt="" /></span>';
				if ($no_percent == 0) $vote_bar_no = '<span><img src="./img/'.$forum_user['style'].'/bar.gif" height="8px" alt="" /></span>';
?>
                <tr>
                <td style="WIDTH: auto; BORDER: 0;">
                <?php echo convert_htmlspecialchars($value); ?>
                </td>
                <td style="BORDER: 0; WIDTH: auto;"><b><?php echo $cur_topic['yes']; ?></b>
                </td>
                <td style="BORDER: 0; WIDTH: auto;"><?php echo $vote_bar_yes; ?>
                </td>
                <td style="BORDER: 0; WIDTH: auto;">
                <?php 
                if (isset($votes[$key]['yes'])) echo $yes_percent . "% - " . $votes[$key]['yes']; 
                else echo "0% - " . 0; 
                ?> 
                </td>
                <td style="BORDER: 0; WIDTH: auto;"><b><?php echo $cur_topic['no']; ?></b>
                </td>
                <td style="BORDER: 0; WIDTH: auto;"><?php echo $vote_bar_no; ?>
                <td style="BORDER: 0; WIDTH: auto;">
<?php 
				if (isset($votes[$key]['no'])) echo $no_percent . "% - " . $votes[$key]['no']; 
				else echo "0% - " . 0; 
?> 
                </td>
                </tr>
            
<?php
			}
			else message($lang_common['Bad request']);
		} 	
?>
			<tr>
				<td colspan="7" style="WIDTH: auto; BORDER: 0;">
				<div style="text-align:center;">Total: <?php echo $total; ?> <?php echo $lang_polls['vote(s)'] ?></div>
				</td>
<?php 
	} 
?>
				</tr>
</table>
			</div>
			</div>
			<?php if ($showsubmit == true) { ?>
				<p align="center"><input type="submit" class="b1" name="submit" tabindex="2" value="<?php echo $lang_common['Submit'] ?>" accesskey="s" /> <input type="submit" class="b1" name="null" tabindex="2" value="<?php echo $lang_polls['Null vote'] ?>" accesskey="n" /></p>
			<?php } ?>
	</div>
</div>
<?php
$result = $db->query("SELECT COUNT(*) FROM ".$db->prefix."posts WHERE topic_id=".$id) or error('Unable to count posts in thread', __FILE__, __LINE__, $db->error());
$num_posts = $db->result($result);
$result = $db->query('SELECT DISTINCT u.email, u.title, u.url, u.yahoo, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, u.country, u.reputation_minus, u.reputation_plus, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, g.g_color, o.user_id AS is_online FROM ' . $db->prefix . 'posts AS p INNER JOIN ' . $db->prefix . 'users AS u ON u.id=p.poster_id INNER JOIN ' . $db->prefix . 'groups AS g ON g.g_id=u.group_id LEFT JOIN ' . $db->prefix . 'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.topic_id=' . $id . ' AND p.message<>\'\' ORDER BY p.id '.($forum_user['reverse_posts']? 'DESC ' : '').'LIMIT ' . $start_from . ',' . $forum_user['disp_posts'], true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
while ($cur_post = $db->fetch_assoc($result))
{
	$post_count++;
	$user_avatar = '';
	$user_info = array();
	$user_contacts = array();
	$post_actions = array();
	$is_online = '';
	$signature = ''; 
	if ($cur_post['poster_id'] > 1) 
	{
		$rank_pips = "";
		if($cur_post['num_posts'] > 5000) { $num_pips = 10; }
		elseif($cur_post['num_posts'] > 3000) { $num_pips = 9; }
		elseif($cur_post['num_posts'] > 2000) { $num_pips = 8; }
		elseif($cur_post['num_posts'] > 1000) { $num_pips = 7; }
		elseif($cur_post['num_posts'] > 500) { $num_pips = 6; }
		elseif($cur_post['num_posts'] > 300) { $num_pips = 5; }
		elseif($cur_post['num_posts'] > 100) { $num_pips = 4; }
		elseif($cur_post['num_posts'] > 50) { $num_pips = 3; }
		elseif($cur_post['num_posts'] > 10) { $num_pips = 2; }
		else { $num_pips = 1; }
		for($pip=0; $pip<$num_pips; $pip++)
		{
			if(is_file(FORUM_ROOT.'img/'.$forum_user['style'].'/pip.gif'))
			{
				$rank_pips .= '<img style="border:0;" src="img/'.$forum_user['style'].'/pip.gif" alt="" />';
			}
			else
			{
				$rank_pips .= '<img style="border:0;" src="img/Default/pip.gif" alt="" />';
			}		
		}
		$username = '<a href="profile.php?id=' . $cur_post['poster_id'] . '"><span style="color:'.$cur_post['g_color'].'">' . convert_htmlspecialchars($cur_post['username']) . '</span></a>';
		$user_title = get_title($cur_post);
		$user_country = $cur_post['country'];
		if ($configuration['o_censoring'] == '1') $user_title = censor_words($user_title); 
		$is_online = ($cur_post['is_online'] == $cur_post['poster_id']) ? '<strong>'.$lang_topic['Online'].'</strong>' : $lang_topic['Offline'];
		if ($configuration['o_avatars'] == '1' && $cur_post['use_avatar'] == '1' && $forum_user['show_avatars'] != '0')
		{
			if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif')) $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg')) $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($configuration['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png')) $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png" '.$img_size[3].' alt="" />';
		}
		else $user_avatar = '<img src="'.$configuration['o_avatars_dir'].'/noavatar.gif" alt="" />';
		if ($configuration['o_avatars'] == '1' && $forum_user['show_avatars'] != '1') $user_avatar = '';
		if ($configuration['o_show_user_info'] == '1')
		{
			if ($cur_post['location'] != '')
			{
				if ($configuration['o_censoring'] == '1') $cur_post['location'] = censor_words($cur_post['location']);
				$user_info[] = '<dd>'.$lang_topic['From'].': '.convert_htmlspecialchars($cur_post['location']);
			} 
			$user_info[] = '<dd>'.$lang_common['Registered'].': '.date($configuration['o_date_format'], $cur_post['registered']);
			if ($configuration['o_show_post_count'] == '1' || $forum_user['g_id'] < USER_GUEST) $user_info[] = '<dd>'.$lang_common['Posts'].': '.$cur_post['num_posts'];
//			if ($cur_post['yahoo'] != '' && !$forum_user['is_guest']) $user_contacts[] = '<a href="ymsgr:sendim?'.$cur_post['yahoo'].'"><img src="http://opi.yahoo.com/online?u='.$cur_post['yahoo'].'&m=g&t=3" border=0 alt="YM" title="Send '.$cur_post['username'].' instant message!"></a>'; 
			if (($cur_post['email_setting'] == '0' && !$forum_user['is_guest']) || $forum_user['g_id'] < USER_GUEST) $user_contacts[] = '<a href="mailto:' . $cur_post['email'] . '">' . $lang_common['E-mail'] . '</a>';
			else if ($cur_post['email_setting'] == '1' && !$forum_user['is_guest']) $user_contacts[] = '<a href="misc.php?email='.$cur_post['poster_id'] . '">' . $lang_common['E-mail'] . '</a>';
			if($configuration['o_pms_enabled'] && !$forum_user['is_guest'] && $forum_user['g_pm'] == 1)
			{
				$pid = isset($cur_post['poster_id']) ? $cur_post['poster_id'] : $cur_post['id'];
				$user_contacts[] = '<a href="message_send.php?id='.$pid.'&tid='.$id.'">'.$lang_pms['PM'].'</a>';
			}
			if ($cur_post['url'] != '') $user_contacts[] = '<a href="'.convert_htmlspecialchars($cur_post['url']).'">'.$lang_topic['Website'].'</a>';
			$user_contacts[] = '<a href="expertise.php?id='.$cur_post['poster_id'].'">'.$lang_topic['Expertise'].'</a>';
		}
		if ($forum_user['g_id'] < USER_GUEST)
		{
			$user_info[] = '<dd>IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['poster_ip'].'</a>';
			if ($cur_post['admin_note'] != '') $user_info[] = '<dd>'.$lang_topic['Note'].': <strong>'.convert_htmlspecialchars($cur_post['admin_note']) . '</strong>';
		} 
	} 
	else
	{
		$username = convert_htmlspecialchars($cur_post['username']);
		$user_title = get_title($cur_post);
		if ($forum_user['g_id'] < USER_GUEST) $user_info[] = '<dd>IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['poster_ip'].'</a>';
		if ($configuration['o_show_user_info'] == '1' && $cur_post['poster_email'] != '' && !$forum_user['is_guest']) $user_contacts[] = '<a href="mailto:' . $cur_post['poster_email'] . '">' . $lang_common['E-mail'] . '</a>';
	} 
	if (!$is_admmod)
	{
		if (!$forum_user['is_guest']) $post_actions[] = '<li class="postreport"><a href="misc.php?report='.$cur_post['id'].'">'.$lang_topic['Report'].'</a>';
		if ($cur_topic['closed'] == '0')
		{
			if ($cur_post['poster_id'] == $forum_user['id'])
			{
				if ((($start_from + $post_count) == 1 && $forum_user['g_delete_topics'] == '1') || (($start_from + $post_count) > 1 && $forum_user['g_delete_posts'] == '1')) $post_actions[] = '<li class="postdelete"><a href="delete.php?id='.$cur_post['id'].'">'.$lang_topic['Delete'].'</a>';
				if ($forum_user['g_edit_posts'] == '1') $post_actions[] = '<li class="postedit"><a href="edit.php?id='.$cur_post['id'].'">'.$lang_topic['Edit'].'</a>';
			}
			if (($cur_topic['post_replies'] == '' && $forum_user['g_post_replies'] == '1') || $cur_topic['post_replies'] == '1') $post_actions[] = '<li class="postquote"><a href="poll.php?tid='.$id.'&amp;qid='.$cur_post['id'].'">'.$lang_topic['Quote'].'</a>';
		}
	}
	else $post_actions[] = '<li class="postreport"><a href="misc.php?report='.$cur_post['id'].'">'.$lang_topic['Report'].'</a>'.$lang_topic['Link separator'].'</li><li class="postdelete"><a href="delete.php?id='.$cur_post['id'].'">'.$lang_topic['Delete'].'</a>'.$lang_topic['Link separator'].'</li><li class="postedit"><a href="edit.php?id='.$cur_post['id'].'">'.$lang_topic['Edit'].'</a>'.$lang_topic['Link separator'].'</li><li class="postquote"><a href="poll.php?tid='.$id.'&amp;qid='.$cur_post['id'].'">'.$lang_topic['Quote'].'</a>';
	$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
	$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
	if ($cur_topic['closed'] == '2' && !$is_admmod) $cur_post['message'] = '[quote]Topic awaiting validation[/quote]';
	$cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']); 
	if ($cur_post['signature'] != '' && $forum_user['show_sig'] != '0')
	{
		if (isset($signature_cache[$cur_post['poster_id']])) $signature = $signature_cache[$cur_post['poster_id']];
		else
		{
			$signature = parse_signature($cur_post['signature']);
			$signature_cache[$cur_post['poster_id']] = $signature;
		} 
	} 
?>
<div id="p<?php echo $cur_post['id'] ?>" class="blockpost<?php echo $vtbg ?><?php if (($post_count + $start_from) == 1) echo ' firstpost';?>">
<?php
if ($configuration['o_rewrite_urls'] == '1')
{
?>
	<h2><span><span class="conr">#<?php echo $forum_user['reverse_posts']? ($num_posts+1-($start_from + $post_count)):($start_from + $post_count) ?>&nbsp;</span><a href="<?php echo makeurl("p", $cur_post['id'], format_time($cur_post['posted'])).'#p'.$cur_post['id'] ?>"><?php echo format_time($cur_post['posted']) ?></a></span></h2>
<?php 
}
else
{
?>
	<h2><span><span class="conr">#<?php echo $forum_user['reverse_posts']? ($num_posts+1-($start_from + $post_count)):($start_from + $post_count) ?>&nbsp;</span><a href="view_topic.php?pid=<?php echo $cur_post['id'].'#p'.$cur_post['id'] ?>"><?php echo format_time($cur_post['posted']) ?></a></span></h2>
<?php 
}
?>
	<div class="box">
		<div class="inbox">
			<div class="postleft">
				<dl>
					<dt><strong><?php echo $username ?></strong></dt>
					<dd class="usertitle"><strong><?php echo $user_title ?></strong></dd>
					<?php echo "<dd class=\"usertitle\">".$rank_pips."</dd>\n"; ?>
					<dd class="postavatar"><?php echo $user_avatar ?></dd>
<?php
		if (count($user_info)) echo "\t\t\t\t\t".implode('</dd>'."\n\t\t\t\t\t", $user_info).'</dd>'."\n";
?>
		<dd>
<?php  
            if($configuration['o_reputation_enabled'] == '1')
		{
            	echo $lang_reputation['Reputation']; 
?> : 
<?php 
	            if($forum_user['is_guest'] != true && $forum_user['username'] != $cur_post['username'])
			{ 
?>
      		      <a href="./reputation.php?id=<?php echo $cur_post['poster_id']; ?>&plus"><img src="./img/general/plus.png" alt="give 1 reputation point" border="0"></a>
		            <a href="./reputation.php?id=<?php echo $cur_post['poster_id']; ?>&minus"><img src="./img/general/minus.png" alt="take 1 reputation point" border="0"></a> 
<?php
			}
?>
			&nbsp;<strong><small>[+ <?php echo $cur_post['reputation_plus']; ?>/ -<?php echo $cur_post['reputation_minus']; ?> ]</small></strong>
<?php
		}
?>
		</dd>
<?php
		if (count($user_contacts)) echo "\t\t\t\t\t".'<dd class="usercontacts">'.implode('&nbsp;&nbsp;', $user_contacts).'</dd>'."\n";
?>
				</dl>
			</div>
			<div class="postright">
				<h3><?php if (($post_count + $start_from) > 1) echo ' Re: ';?><?php echo convert_htmlspecialchars($cur_topic['subject']) ?></h3>
				<div class="postmsg">
					<?php echo $cur_post['message']."\n" ?>
					<?php show_post_images($cur_post['id']) ?>
<?php if ($cur_post['edited'] != '') echo "\t\t\t\t\t".'<p class="postedit"><em>'.$lang_topic['Last edit'].' '.convert_htmlspecialchars($cur_post['edited_by']) . ' (' . format_time($cur_post['edited']) . ')</em></p>' . "\n";?>
				</div>
<?php if ($signature != '') echo "\t\t\t\t".'<div class="postsignature"><hr />'.$signature.'</div>'."\n"; ?>
			</div>
			<div class="clearer"></div>
			<div class="postfootleft"><?php if ($cur_post['poster_id'] > 1) echo '<p>'.$is_online.'</p>'; ?></div>
			<div class="postfootright"><?php echo (count($post_actions)) ? '<ul>'.implode($lang_topic['Link separator'].'</li>', $post_actions).'</li></ul></div>' . "\n" : '<div>&nbsp;</div></div>' . "\n" ?>
		</div>
	</div>
</div>
<?php
include('include/modules/mod_advertising.php');
}
?>
<div class="postlinksb">
	<div class="inbox">
		<p class="postlink conr"><?php echo $post_link ?></p>
		<p class="pagelink conl"><?php echo $paging_links ?></p>
<?php
if ($configuration['o_rewrite_urls'] == '1')
{
	if($cur_topic['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="'.makeurl("f", $cur_topic['parent_forum_id'], $cur_topic['parent_forum_name']).'">'.convert_htmlspecialchars($cur_topic['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="'.makeurl("f", $cur_topic['forum_id'], $cur_topic['forum_name']).'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a></li><li>&nbsp;&raquo;&nbsp;<a href="'.makeurl("f", $cur_topic['forum_id'], $cur_topic['forum_name']).'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a></li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
}
else
{
	if($cur_topic['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="view_forum.php?id='.$cur_topic['parent_forum_id'].'">'.convert_htmlspecialchars($cur_topic['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="view_forum.php?id='.$cur_topic['forum_id'].'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a></li><li>&nbsp;&raquo;&nbsp;<a href="view_forum.php?id='.$cur_topic['forum_id'].'">'.convert_htmlspecialchars($cur_topic['forum_name']).'</a></li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_topic['subject']).'</li></ul>';
}
?>
		<?php echo $subscraction ?>
	</div>
</div>
<?php 
if ($quickpost)
{
	$cur_qpost = 0;
	$qpost_count = 0;
	$qpost_ids = (isset($_COOKIE['qpostprefs']))? $_COOKIE['qpostprefs'].',': FALSE;
	if (strstr($qpost_ids, $qpost_count.','))
	{
		$div_ido = "none";
		$div_idx = "show";
	}
	else
	{
		$div_ido = "show";
		$div_idx = "none";
	}
	$exp_up   = (is_file(FORUM_ROOT.'img/general/exp_up.png'))?  'general/exp_up.png': 'exp_up.png';
	$exp_down = (is_file(FORUM_ROOT.'img/general/exp_down.png'))? 'general/exp_down.png': 'exp_down.png';
?>
<div id="ido<?php echo $qpost_count ?>" class="blockform" style="display:<?php echo $div_ido?>">
	<h2>
		<span style="float:right"><a href="javascript:togglecategory(<?php echo $qpost_count?>, 0);"><img src="img/<?php echo $exp_down ?>" alt="Expand" /></a></span>
		<span><?php echo $lang_topic['Quick post'] ?>
	</h2>
</div>
<div id="idx<?php echo $qpost_count ?>" class="blockform" style="display:<?php echo $div_idx?>">
	<h2>
		<span style="float:right"><a href="javascript:togglecategory(<?php echo $qpost_count?>, 1);"><img src="img/<?php echo $exp_up?>" alt="Collapse" /></a></span>
		<span><?php echo $lang_topic['Quick post'] ?>
	</h2>
	<div class="box">
		<form id="post" method="post" action="poll.php?tid=<?php echo $id ?>" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Write message legend'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['username']) : 'Guest'; ?>" />
						<label><textarea name="req_message" rows="7" cols="50" tabindex="1"></textarea></label>
						<ul class="bblinks">
							<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a>: <?php echo ($configuration['p_message_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off'];?></li>
							<li><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a>: <?php echo ($configuration['p_message_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off'];?></li>
							<li><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a>: <?php echo ($configuration['o_smilies'] == '1') ? $lang_common['on'] : $lang_common['off'];?></li>
						</ul>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="submit" tabindex="2" value="<?php echo $lang_common['Submit'] ?>" accesskey="s" /></p>
		</form>
	</div>
</div>
<?php
} 
$low_prio = ($db_type == 'mysql') ? 'LOW_PRIORITY ' : '';
$db->query('UPDATE ' . $low_prio . $db->prefix . 'topics SET num_views=num_views+1 WHERE id=' . $id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
$forum_id = $cur_topic['forum_id'];
$footer_style = 'view_poll';
require FORUM_ROOT . 'footer.php';