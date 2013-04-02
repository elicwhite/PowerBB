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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/delete.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1) message($lang_common['Bad request']);
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_post = $db->fetch_assoc($result);
$mods_array = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$cur_post['tid'].' ORDER BY posted LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
$topic_post_id = $db->result($result);
$is_topic_post = ($id == $topic_post_id) ? true : false;
if (($forum_user['g_delete_posts'] == '0' || ($forum_user['g_delete_topics'] == '0' && $is_topic_post) || $cur_post['poster_id'] != $forum_user['id'] || $cur_post['closed'] == '1') && !$is_admmod) message($lang_common['No permission']);
if (isset($_POST['delete']))
{
	if ($is_admmod) confirm_referrer('delete.php');
	require FORUM_ROOT.'include/search_idx.php';
	if ($is_topic_post)
	{
		delete_topic($cur_post['tid']);
		update_forum($cur_post['fid']);
		redirect(FORUM_ROOT.'view_forum.php?id='.$cur_post['fid'], $lang_delete['Topic del redirect']);
	}
	else
	{
		delete_post($id, $cur_post['tid']);
		update_forum($cur_post['fid']);
		redirect(FORUM_ROOT.'view_topic.php?id='.$cur_post['tid'], $lang_delete['Post del redirect']);
	}
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_delete['Delete post'];
require FORUM_ROOT.'header.php';
require FORUM_ROOT.'include/parser.php';
$cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']);
?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<a href="view_forum.php?id=<?php echo $cur_post['fid'] ?>"><?php echo convert_htmlspecialchars($cur_post['forum_name']) ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo convert_htmlspecialchars($cur_post['subject']) ?></li></ul>
	</div>
</div>
<div class="blockform">
	<h2><span><?php echo $lang_delete['Delete post'] ?></span></h2>
	<div class="box">
		<form method="post" action="delete.php?id=<?php echo $id ?>">
			<div class="inform">
				<fieldset>
					<legend class="warntext"><?php echo $lang_delete['Warning'] ?></legend>
					<div class="infldset">
						<div class="postmsg">
							<p><?php echo $lang_common['Author'] ?>: <strong><?php echo convert_htmlspecialchars($cur_post['poster']) ?></strong></p>
							<?php echo $cur_post['message'] ?>
						</div>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>" /><input class="b1" type="submit" name="delete" value="<?php echo $lang_delete['Delete'] ?>" /></p>
		</form>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>