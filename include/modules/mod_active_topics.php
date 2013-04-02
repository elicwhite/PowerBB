<?php
/*
//-----------------------------------------------\\
\\------------------PowerBB FORUMS------------------//
//-----------------------------------------------\\
\\---------------active_topics.php---------------//
//-----------------------------------------------\\
\\ This code is (c) to TheSavior (Eli White) as  //
// of 2006. This code may NOT be reproduced, or  \\
\\ distributed by any means, unless you have     //
// explicit written permissions by TheSavior     \\
\\-----------------------------------------------//
// You may edit this file as you wish, as long   \\
\\ as it isn't redistributed after. Free support //
// is provided for all sripts on                 \\
\\ http://www.thesavior.co.nr and                //

\\-----------------------------------------------//
// I hope you enjoy this script, now get to the  \\
\\ fun part of looking at how it works! Its a    //
// joy. XD                                       \\
\\-----------------------------------------------//
*/
$result = $db->query('SELECT t.*, u.username, u.displayname FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') INNER JOIN '.$db->prefix.'users as u ON u.username=t.last_poster WHERE (fp.read_forum IS NULL OR fp.read_forum=1) ORDER BY t.last_post DESC LIMIT '.$ak_limit) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
require FORUM_ROOT.'lang/'.$forum_user['language'].'/forum.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/polls.php';
$cur_category = 0;
$cat_count = 0;
$cat_ids = (isset($_COOKIE['collapseprefs']))? $_COOKIE['collapseprefs'].',': FALSE;
if (strstr($cat_ids, $cat_count.','))
{
	$div_ido = "show";
	$div_idx = "none";
}
else
{
	$div_ido = "none";
	$div_idx = "block";
}
$exp_up = (is_file(FORUM_ROOT.'img/general/exp_up.png'))? 'general/exp_up.png': 'exp_up.png';
$exp_down = (is_file(FORUM_ROOT.'img/general/exp_down.png'))? 'general/exp_down.png': 'exp_down.png';
?>
<div id="ido<?php echo $cat_count ?>" class="blocktable" style="display:<?php echo $div_ido?>">
	<h2>
		<span style="float:right"><a href="javascript:togglecategory(<?php echo $cat_count?>, 0);"><img src="img/<?php echo $exp_down ?>" alt="Expand" /></a></span>
		<span><?php echo $lang_forum['Active Topics'] ?></span>
	</h2>
</div>
<div id="idx<?php echo $cat_count ?>" class="blocktable" style="display:<?php echo $div_idx?>">
	<h2>
		<span style="float:right"><a href="javascript:togglecategory(<?php echo $cat_count?>, 1);"><img src="img/<?php echo $exp_up?>" alt="Collapse" /></a></span>
		<span><?php echo $lang_forum['Active Topics'] ?></span>
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
if ($db->num_rows($result))
{
    while ($cur_topic = $db->fetch_assoc($result))
    {
		if ($cur_topic['displayname'] != "")
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
				if ($cur_topic['question'] != '') $last_post = '<a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'#p'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
				else $last_post = '<a href="'.makeurl("p", $cur_topic['last_post_id'], $cur_topic['subject']).'#p'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
			}
			else
			{
				if ($cur_topic['question'] != '') $last_post = '<a href="view_poll.php?pid='.$cur_topic['last_post_id'].'#p'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
				else $last_post = '<a href="view_topic.php?pid='.$cur_topic['last_post_id'].'#p'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_topic['last_poster']).'</span>';
			}
		}
		else $last_post = '&nbsp;';
		if ($configuration['o_censoring'] == '1') $cur_topic['subject'] = censor_words($cur_topic['subject']);
		if ($cur_topic['question'] != '')
		{
			if ($configuration['o_rewrite_urls'] == '1')
			{
				if ($configuration['o_censoring'] == '1') $cur_topic['question'] = censor_words($cur_topic['question']);
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': '.$lang_polls['Poll'].': <a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['subject']).'</a><br/>&nbsp;'.convert_htmlspecialchars($cur_topic['question']);
				else if ($cur_topic['closed'] == '0') $subject = $lang_polls['Poll'].': <a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
				else
				{
					$subject = $lang_polls['Poll'].': <a href="'.makeurl("l", $cur_topic['last_post_id'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}
			}
			else
			{
				if ($configuration['o_censoring'] == '1') $cur_topic['question'] = censor_words($cur_topic['question']);
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': '.$lang_polls['Poll'].': <a href="view_poll.php?id='.$cur_topic['moved_to'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a><br/>&nbsp;'.convert_htmlspecialchars($cur_topic['question']);
				else if ($cur_topic['closed'] == '0') $subject = $lang_polls['Poll'].': <a href="view_poll.php?id='.$cur_topic['id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
				else
				{
					$subject = $lang_polls['Poll'].': <a href="view_poll.php?id='.$cur_topic['id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
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
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': <a href="'.makeurl("t", $cur_topic['moved_to'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
				else if ($cur_topic['closed'] == '0') $subject = '<a href="'.makeurl("t", $cur_topic['id'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
				else
				{
					$subject = '<a href="'.makeurl("t", $cur_topic['id'], $cur_topic['subject']).'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}
			}
			else
			{
				if ($cur_topic['moved_to'] != 0) $subject = $lang_forum['Moved'].': <a href="view_topic.php?id='.$cur_topic['moved_to'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
				else if ($cur_topic['closed'] == '0') $subject = '<a href="view_topic.php?id='.$cur_topic['id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
				else
				{
					$subject = '<a href="view_topic.php?id='.$cur_topic['id'].'">'.convert_htmlspecialchars($cur_topic['subject']).'</a>';
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
		$num_pages_topic = ceil(($cur_topic['num_replies'] + 1) / $forum_user['disp_posts']);
		if ($num_pages_topic > 1)
		{
			if ($cur_topic['question'] != '')  $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'view_poll.php?id='.$cur_topic['id']).' ]';
			else $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'view_topic.php?id='.$cur_topic['id']).' ]';
		}
		else $subject_multipage = null;
		if (!empty($subject_new_posts) || !empty($subject_multipage))
		{
			$subject .= '<br/>&nbsp; '.(!empty($subject_new_posts) ? $subject_new_posts : '');
			$subject .= !empty($subject_multipage) ? ' '.$subject_multipage : '';
		}
?>
                <tr<?php if ($item_status != '') echo ' class="'.trim($item_status).'"'; ?>>
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