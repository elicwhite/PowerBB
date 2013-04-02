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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/forum.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/index.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/polls.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1) message($lang_common['Bad request']);
$result = $db->query('SELECT f.protected, f.password, f.forum_name, pf.forum_name AS parent_forum, f.forum_name, f.redirect_url, f.moderators, f.num_topics, f.sort_by, f.parent_forum_id, fp.post_topics FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') LEFT JOIN '.$db->prefix.'forums AS pf ON f.parent_forum_id=pf.id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_forum = $db->fetch_assoc($result);
if($cur_forum['protected'] != 1 && $forum_user['group_id'] != 1 || $forum_user['group_id'] != 2)
{
	if ($cur_forum['redirect_url'] != '')
	{
		header('Location: '.$cur_forum['redirect_url']);
		exit;
	}
	$mods_array = array();
	if ($cur_forum['moderators'] != '') $mods_array = unserialize($cur_forum['moderators']);
	$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
	if (($cur_forum['post_topics'] == '' && $forum_user['g_post_topics'] == '1') || $cur_forum['post_topics'] == '1' || $is_admmod) $post_link = "\t\t".'
	<p class="postlink conr">
		<a href="post.php?fid='.$id.'">
			'.$lang_forum['Post topic'].'
		</a>&nbsp;&nbsp;&nbsp;
		<a href="poll.php?fid='.$id.'">
			'.$lang_polls['New poll'].'
		</a>
	</p>'."\n";
	else $post_link = "\t\t".'<p class="postlink conr"><a href="login.php">'.$lang_forum['Post topic'].'</a>&nbsp;&nbsp;&nbsp;<a href="login.php">'.$lang_polls['New poll'].'</a></p>'."\n";
	$num_pages = ceil($cur_forum['num_topics'] / $forum_user['disp_topics']);
	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
	$start_from = $forum_user['disp_topics'] * ($p - 1);
	$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'view_forum.php?id='.$id);
	$page_title = convert_htmlspecialchars($configuration['o_board_name'].' / '.$cur_forum['forum_name']);
	define('ALLOW_INDEX', 1);
	require FORUM_ROOT.'header.php';
?>
<?php
	$subforum_result = $db->query('SELECT f.forum_desc, f.forum_name, f.id, f.last_post, f.last_post_id, f.last_poster, f.moderators, f.num_posts, f.num_topics, f.redirect_url, u.username, u.displayname FROM '.$db->prefix.'forums AS f INNER JOIN '.$db->prefix.'users AS u ON u.username=f.last_poster WHERE parent_forum_id='.$id) or error('Unable to fetch sub forum info',__FILE__,__LINE__,$db->error());
	if($db->num_rows($subforum_result))
	{
?>
		<div class="linkst">
			<div class="inbox">
				<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a>&nbsp;</li><li>&raquo;&nbsp;<?php echo convert_htmlspecialchars($cur_forum['forum_name']) ?></li></ul>
				<div class="clearer"></div>
			</div>
		</div>
		<div id="vf" class="blocktable">
			<h2><span>Sub forums</span></h2>
			<div class="box">
				<div class="inbox">
					<table cellspacing="0">
					<thead>
						<tr>
							<th class="tcl" scope="col"><?php echo $lang_common['Forum'] ?></th>
							<th class="tc2" scope="col"><?php echo $lang_index['Topics'] ?></th>
							<th class="tc3" scope="col"><?php echo $lang_common['Posts'] ?></th>
							<th class="tcr" scope="col"><?php echo $lang_common['Last post'] ?></th>
						</tr>
					</thead>
					<tbody>
<?php
		while($cur_subforum = $db->fetch_assoc($subforum_result))
		{
			if ($cur_subforum['displayname'] != "")
			{
				$cur_subforum['last_poster'] = $cur_subforum['displayname'];
			}
			$item_status = '';
			$icon_text = $lang_common['Normal icon'];
			$icon_type = 'icon';
			if (!$forum_user['is_guest'] && $cur_subforum['last_post'] > $forum_user['last_visit'])
			{
				$item_status = 'inew';
				$icon_text = $lang_common['New icon'];
				$icon_type = 'icon inew';
			}
			if ($cur_forum['redirect_url'] != '')
			{
				$forum_field = '<h3><a href="'.convert_htmlspecialchars($cur_subforum['redirect_url']).'" title="'.$lang_index['Link to'].' '.convert_htmlspecialchars($cur_subforum['redirect_url']).'">'.convert_htmlspecialchars($cur_subforum['forum_name']).'</a></h3>';
				$num_topics = $num_posts = '&nbsp;';
				$item_status = 'iredirect';
				$icon_text = $lang_common['Redirect icon'];
				$icon_type = 'icon';
			}
			else
			{
				if ($configuration['o_rewrite_urls'] == '1') $forum_field = '<h3><a href="'.makeurl("f", $cur_subforum['id'], $cur_forum['forum_name']).'">'.convert_htmlspecialchars($cur_subforum['forum_name']).'</a></h3>';
				else $forum_field = '<h3><a href="view_forum.php?id='.$cur_subforum['id'].'">'.convert_htmlspecialchars($cur_subforum['forum_name']).'</a></h3>';
				$num_topics = $cur_subforum['num_topics'];
				$num_posts = $cur_subforum['num_posts'];
			}
	if ($cur_subforum['forum_desc'] != '') $forum_field .= "\n\t\t\t\t\t\t\t\t".$cur_subforum['forum_desc'];
	if ($cur_subforum['last_post'] != '')
	{
		$queryid = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$cur_subforum['last_post_id']);
		$idm = $db->result($queryid);
		$queryid = $db->query('SELECT subject FROM '.$db->prefix.'topics WHERE id='.$idm);
		$idm = $db->result($queryid);
		if(strlen($idm) > 30)
		{
			$idmComp = str_replace('"', "''", $idm);
			$idm = substr($idm, 0, 30).'...';
		}
		else $idmComp = '';
//		$idmT = (isset($idmComp)) ? ' title="'.$idmComp.'"' : '';
		if ($cur_topic['question'] != '')
		{
			if ($configuration['o_rewrite_urls'] == '1') $last_post = '<a href="'.makeurl("l", $cur_subforum['last_post_id'], format_time($cur_subforum['last_post'])).'#p'.$cur_subforum['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_subforum['last_poster']).'<br />'.$lang_common['Topic'].': <a href="'.makeurl("l", $cur_subforum['last_post_id'], format_time($cur_subforum['last_post'])).'#p'.$cur_subforum['last_post_id'].'">'.$idm.'</a></span>';
			else $last_post = '<a href="view_poll.php?pid='.$cur_subforum['last_post_id'].'#p'.$cur_subforum['last_post_id'].'">'.format_time($cur_subforum['last_post']).'</a> <span class="byuser">'.$lang_common['by'].' '.convert_htmlspecialchars($cur_subforum['last_poster']).'<br />'.$lang_common['Topic'].': <a href="view_poll.php?pid='.$cur_subforum['last_post_id'].'#p'.$cur_subforum['last_post_id'].'">'.$idm.'</a></span>';
		}
		else
		{
			if ($configuration['o_rewrite_urls'] == '1') $last_post = '<a href="'.makeurl("p", $cur_subforum['last_post_id'], format_time($cur_subforum['last_post'])).'#p'.$cur_subforum['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_subforum['last_poster']).'<br />'.$lang_common['Topic'].': <a href="'.makeurl("p", $cur_subforum['last_post_id'], format_time($cur_subforum['last_post'])).'#p'.$cur_subforum['last_post_id'].'">'.$idm.'</a></span>';
			else $last_post = '<a href="view_topic.php?pid='.$cur_subforum['last_post_id'].'#p'.$cur_subforum['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].' '.convert_htmlspecialchars($cur_subforum['last_poster']).'<br />'.$lang_common['Topic'].': <a href="view_topic.php?pid='.$cur_subforum['last_post_id'].'#p'.$cur_subforum['last_post_id'].'">'.$idm.'</a></span>';
		}
	}
	else $last_post = '&nbsp;';
	$moderators = array();
	if ($cur_subforum['moderators'] != '')
	{
		$mods_array = unserialize($cur_subforum['moderators']);
		while (list($mod_username, $mod_id) = @each($mods_array)) $moderators[] = '[<a href="profile.php?id='.$mod_id.'">'.convert_htmlspecialchars($mod_username).'</a>]';
		$moderators = "\t\t\t\t\t\t\t\t".'<p><em>'.$lang_common['Moderated by'].'</em>: '.implode(', ', $moderators).'</p>'."\n";
	}
?>
				<tr<?php if ($item_status != '') echo ' class="'.$item_status.'"'; ?>>
					<td class="tcl">
						<div class="intd">
							<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo $icon_text ?></div></div>
							<div class="tclcon">
								<?php echo $forum_field."\n";
								if (count($moderators)>0) echo $moderators; ?>
							</div>
						</div>
					</td>
					<td class="tc2"><?php echo $num_topics ?></td>
					<td class="tc3"><?php echo $num_posts ?></td>
					<td class="tcr"><?php echo $last_post ?></td>
				</tr>
<?php
	}
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<?php
}
?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
<?php
echo $post_link;
// TODO get forum name
if ($configuration['o_rewrite_urls'] == '1')
{
	if($cur_forum['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="'.makeurl("f", $cur_forum['parent_forum_id'], $cur_forum['parent_forum']).'">'.convert_htmlspecialchars($cur_forum['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].' </a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
}
else
{
	if($cur_forum['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="view_forum.php?id='.$cur_forum['parent_forum_id'].'">'.convert_htmlspecialchars($cur_forum['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].' </a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
}
?>
		<div class="clearer"></div>
	</div>
</div>
<div id="vf1" class="blocktable">
	<h2>
		 <a class="conr" href="rss.php?fid=<?php echo $id ?>"><img src="img/general/button-feed.png" style="border:0;" alt="RSS feeds" /></a>
		 <span><?php echo convert_htmlspecialchars($cur_forum['forum_name']) ?></span>
	</h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col"><?php echo $lang_common['Topic'] ?></th>
					<th class="tc2" scope="col"><?php echo $lang_common['Replies'] ?></th>
					<th class="tc3" scope="col"><?php echo $lang_forum['Views'] ?></th>
					<th class="tcr" scope="col"><?php echo $lang_common['Last post'] ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$sql = 'SELECT p.message, t.id, t.moved_to FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id OR t.moved_to=p.topic_id WHERE t.forum_id='.$id.' GROUP BY t.id ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == '1') ? 'posted' : 'last_post').' DESC LIMIT '.$start_from.', '.$forum_user['disp_topics'];
$result = $db->query($sql) or error('Unable to fetch first posts', __FILE__, __LINE__, $db->error());
$topic_preview = array();
while ($mod_tp = $db->fetch_assoc($result))
{
	$cur_tp = preg_replace('#\[img\](.+?)\[/img\]#is','*IMAGE*',$mod_tp['message']);
	if (strlen($cur_tp)>256) $cur_tp = preg_replace('#[\s][\S]+$#','',substr($cur_tp,0,256)).'...';
	if ($configuration['o_censoring'] == '1') $cur_tp = censor_words($cur_tp);
	$cur_tp = trim(addslashes(preg_replace('#[\r\n]#','',nl2br(convert_htmlspecialchars($cur_tp)))));
	if ($mod_tp['moved_to'] != 0) $topic_preview[$mod_tp['moved_to']] = $cur_tp;
	else $topic_preview[$mod_tp['id']] = $cur_tp;
}
if ($forum_user['is_guest'] || $configuration['o_show_dot'] == '0')
{
   	$sql = 'SELECT t.id, t.poster, t.subject, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, t.question, t.icon_topic, u.username, u.displayname, us.username, us.displayname AS poster_displayname FROM '.$db->prefix.'topics as t INNER JOIN '.$db->prefix.'users as u ON u.username=t.last_poster INNER JOIN '.$db->prefix.'users AS us ON us.username=t.poster WHERE forum_id='.$id.' ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == '1') ? 'posted' : 'last_post').' DESC LIMIT '.$start_from.', '.$forum_user['disp_topics'];
}
else
{
	$sql = 'SELECT p.poster_id AS has_posted, t.id, t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, t.question, us.username, us.displayname AS poster_displayname, t.icon_topic, u.username, u.displayname FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'users AS u ON u.username=t.last_poster INNER JOIN '.$db->prefix.'users AS us ON us.username=t.poster LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id AND p.poster_id='.$forum_user['id'].' WHERE t.forum_id='.$id.' GROUP BY t.id ORDER BY sticky DESC, '.(($cur_forum['sort_by'] == '1') ? 'posted' : 'last_post').' DESC LIMIT '.$start_from.', '.$forum_user['disp_topics'];
}
$result = $db->query($sql) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
	while ($cur_topic = $db->fetch_assoc($result))
	{
		if($cur_topic['displayname'] !="")
			{
				$cur_topic['last_poster'] = $cur_topic['displayname'];
			}
		$icon_text = $lang_common['Normal icon'];
		$item_status = '';
		$icon_type = 'icon';
		if ($cur_topic['moved_to'] == null)
		{
			if ($configuration['o_rewrite_urls'] == '1')
			{
            		if ($cur_topic['question'] != '') $last_post = '<a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'#p'.$cur_topic['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
				else $last_post = '<a href="'.makeurl("p", $cur_topic['last_post_id'], $cur_topic['subject']).'#p'.$cur_topic['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
			}
			else
			{
	            	if ($cur_topic['question'] != '') $last_post = '<a href="view_poll.php?pid='.$cur_topic['last_post_id'].'#p'.$cur_topic['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
	            	else $last_post = '<a href="view_topic.php?pid='.$cur_topic['last_post_id'].'#p'.$cur_topic['last_post_id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
			}
		}
		else $last_post = '&nbsp;';
		if($cur_topic['poster_displayname'] != "")
			{
				$cur_topic['poster'] = $cur_topic['poster_displayname'];
			}
		if ($configuration['o_censoring'] == '1') $cur_topic['subject'] = censor_words($cur_topic['subject']);
		if ($cur_topic['question'] != '')
		{
			if ($configuration['o_censoring'] == '1') $cur_topic['question'] = censor_words($cur_topic['question']);
			if ($configuration['o_rewrite_urls'] == '1')
			{
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': '.$lang_polls['Poll'].': <a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['question']).'</a> <span class="byuser"> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else if ($cur_topic['closed'] == '0') $subject = $lang_polls['Poll'].': <a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['question']).'</a> <span class="byuser"> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else
				{
					$subject = $lang_polls['Poll'].': <a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['question']).'</a> <span class="byuser"> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}
			}
			else
			{
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': '.$lang_polls['Poll'].': <a href="view_poll.php?id='.$cur_topic['moved_to'].'">'.convert_htmlspecialchars($cur_topic['question']).'</a> <span class="byuser"> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else if ($cur_topic['closed'] == '0') $subject = $lang_polls['Poll'].': <a href="view_poll.php?id='.$cur_topic['id'].'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['question']).'</a> <span class="byuser"> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else
				{
					$subject = $lang_polls['Poll'].': <a href="view_poll.php?id='.$cur_topic['id'].'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['question']).'</a> <span class="byuser"> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}
			}
			if (!$forum_user['is_guest'] && topic_is_new($cur_topic['id'], $id,  $cur_topic['last_post']) && $cur_topic['moved_to'] == null)
			{
				$icon_text .= ' '.$lang_common['New icon'];
				$item_status .= ' inew';
				$icon_type = 'icon inew';
				$subject = '<strong>'.$subject.'</strong>';
				$subject_new_posts = '<span class="newtext">[&nbsp;<a href="view_poll.php?id='.$cur_topic['id'].'&amp;action=new" title="'.$lang_common['New posts info'].'">'.$lang_common['New posts'].'</a>&nbsp;]</span>';
			}
			else $subject_new_posts = null;
			if (!$forum_user['is_guest'] && $configuration['o_show_dot'] == '1')
			{
				if ($cur_topic['has_posted'] == $forum_user['id']) $subject = '<strong>&middot;</strong>&nbsp;'.$subject;
				else $subject = '&nbsp;&nbsp;'.$subject;
			}
		}
		else
		{
			if ($configuration['o_rewrite_urls'] == '1')
			{
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': <a href="'.makeurl("t", $cur_topic['moved_to'], $cur_topic['subject']).'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['moved_to']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else if ($cur_topic['closed'] == '0') $subject = '<a href="'.makeurl("t", $cur_topic['id'], $cur_topic['subject']).'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else
				{
					$subject = '<a href="'.makeurl("t", $cur_topic['id'], $cur_topic['subject']).'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}
			}
			else
			{
			if($cur_topic['poster_displayname'] != "")
			{
				$cur_topic['poster'] = $cur_topic['poster_displayname'];
			}
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': <a href="view_topic.php?id='.$cur_topic['moved_to'].'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['moved_to']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else if ($cur_topic['closed'] == '0') $subject = '<a href="view_topic.php?id='.$cur_topic['id'].'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
				else
				{
					$subject = '<a href="view_topic.php?id='.$cur_topic['id'].'" onmouseover="return overlib(\''.parse_message_preview($topic_preview[$cur_topic['id']]).'\')" onmouseout="return nd();">'.convert_htmlspecialchars($cur_topic['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['poster']).'</span>';
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}	
			}
			if (!$forum_user['is_guest'] && $cur_topic['last_post'] > $forum_user['last_visit'] && $cur_topic['moved_to'] == null)
			{
				$icon_text .= ' '.$lang_common['New icon'];
				$item_status .= ' inew';
				$icon_type = 'icon inew';
				$subject = '<strong>'.$subject.'</strong>';
				$subject_new_posts = '<span class="newtext">[&nbsp;<a href="view_topic.php?id='.$cur_topic['id'].'&amp;action=new" title="'.$lang_common['New posts info'].'">'.$lang_common['New posts'].'</a>&nbsp;]</span>';
			}
			else $subject_new_posts = null;
			if (!$forum_user['is_guest'] && $configuration['o_show_dot'] == '1')
			{
				if ($cur_topic['has_posted'] == $forum_user['id']) $subject = '<strong>&middot;</strong>&nbsp;'.$subject;
				else $subject = '&nbsp;&nbsp;'.$subject;
			}
		}
		if ($cur_topic['sticky'] == '1')
		{
			$subject = '<span class="stickytext">'.$lang_forum['Sticky'].': </span>'.$subject;
           		$item_status .= ' isticky';
			$icon_text .= ' '.$lang_forum['Sticky'];
		}
		if ($cur_topic['closed'] == '2') $subject = '<span class="stickytext">Not validated: </span>'.$subject;
		if ($cur_topic['icon_topic'] != 0)
		{
			$icon_topic = '<img src="./img/general/icons/'.$cur_topic['icon_topic'].'.gif" alt="" />';
			$subject = $icon_topic.' '.$subject;
		}
		$num_pages_topic = ceil(($cur_topic['num_replies'] + 1) / $forum_user['disp_posts']);
		if ($num_pages_topic > 1)
		{
			if ($cur_topic['question'] != '') $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'view_poll.php?id='.$cur_topic['id']).' ]';
			else $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'view_topic.php?id='.$cur_topic['id']).' ]';
		}
		else $subject_multipage = null;
		if (!empty($subject_new_posts) || !empty($subject_multipage))
		{
			$subject .= '&nbsp; '.(!empty($subject_new_posts) ? $subject_new_posts : '');
			$subject .= !empty($subject_multipage) ? ' '.$subject_multipage : '';
		}
if ($configuration['o_click_row'] == '1')
{
?>
				<tr<?php if ($item_status != '') echo ' class="'.trim($item_status).'"'; ?> onclick="window.location.href='view_topic.php?id=<?php echo $cur_topic['id']; ?>'">
<?php
}
else
{
?>
				tr<?php if ($item_status != '') echo ' class="'.trim($item_status).'"'; ?>> 
<?php
}
?>
					<td class="tcl">
						<div class="intd">
							<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo trim($icon_text) ?></div></div>
							<div class="tclcon">
								<?php echo $subject."\n" ?>
							</div>
						</div>
					</td>
					<td class="tc2"><?php echo ($cur_topic['moved_to'] == null) ? $cur_topic['num_replies'] : '&nbsp;' ?></td>
					<td class="tc3"><?php echo ($cur_topic['moved_to'] == null) ? $cur_topic['num_views'] : '&nbsp;' ?></td>
					<td class="tcr"><?php echo $last_post ?></td>
				</tr>
<?php
	}
}
else
{
?>
				<tr>
					<td class="tcl" colspan="4"><?php echo $lang_forum['Empty forum'] ?></td>
				</tr>
<?php
}
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<div class="linksb">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
<?php
echo $post_link;
if ($configuration['o_rewrite_urls'] == '1')
{
	if($cur_forum['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="'.makeurl("f", $cur_forum['parent_forum_id'], $cur_forum['parent_forum']).'">'.convert_htmlspecialchars($cur_forum['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].' </a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
}
else
{
	if($cur_forum['parent_forum']) echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].'</a>&nbsp;</li><li>&raquo;&nbsp;<a href="view_forum.php?id='.$cur_forum['parent_forum_id'].'">'.convert_htmlspecialchars($cur_forum['parent_forum']).'</a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
	else echo "\t\t".'<ul><li><a href="index.php">'.$lang_common['Index'].' </a>&nbsp;</li><li>&raquo;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</li></ul>';
}
?>
		<div class="clearer"></div>
	</div>
</div>
<?php
	}
	else
	{  
		if(isset($_POST['check_pass']))
		{
			$pass = $_POST['pass'];
			$pass = mysql_real_escape_string($pass);
			$check = $db->query("SELECT password FROM ". $db->prefix ."forums WHERE `password` = '".$pass."' AND `id` = '$id'") or error(mysql_error());
			if($db->num_rows($check) < 1)
			{
				message($lang_common['No Forum Auth']);
			}
		}
		else
		{
			echo "Protected: {$cur_forum['protected']}";
			message($lang_common['Password Protected'] .'
			 	<form method="post" action="view_forum.php?id='. $id .'">
				 Password: <input type="password" name="pass"><br /><br />
			 	<input class="b1" type="submit" name="check_pass">
			 	</form>');
		}
	}
$forum_id = $id;
$footer_style = 'view_forum';
require FORUM_ROOT.'footer.php';
?>