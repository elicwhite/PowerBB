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
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
if (isset($_POST['zap_id']))
{
	confirm_referrer('admin_reports.php');
	$zap_id = intval(key($_POST['zap_id']));
	$result = $db->query('SELECT zapped FROM '.$db->prefix.'reports WHERE id='.$zap_id) or error('Unable to fetch report info', __FILE__, __LINE__, $db->error());
	$zapped = $db->result($result);
	if ($zapped == '') $db->query('UPDATE '.$db->prefix.'reports SET zapped='.time().', zapped_by='.$forum_user['id'].' WHERE id='.$zap_id) or error('Unable to zap report', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_reports.php', 'Report zapped. Redirecting &hellip;');
}
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Reports'];
require FORUM_ROOT.'header.php';
generate_admin_menu('reports');
?>
<div class="blockform">
	<div class="tab-page" id="reportsPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "reportsPane" ), 1 )</script>
	<div class="tab-page" id="help-rep-page"><h2 class="tab"><?php echo $lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-rep-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
							<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/reports.png></td>
							<td>
								<span><?php echo $lang_admin['help_reports']; ?></span>
							</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="new-rep-page"><h2 class="tab"><?php echo $lang_admin['New']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "new-rep-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_reports.php?action=zap">
<?php
$result = $db->query('SELECT r.id, r.post_id, r.topic_id, r.forum_id, r.reported_by, r.created, r.message, t.subject, f.forum_name, u.username AS reporter FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id WHERE r.zapped IS NULL ORDER BY created DESC') or error('Unable to fetch report list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
	while ($cur_report = $db->fetch_assoc($result))
	{
		$reporter = ($cur_report['reporter'] != '') ? '<a href="profile.php?id='.$cur_report['reported_by'].'">'.convert_htmlspecialchars($cur_report['reporter']).'</a>' : 'Deleted user';
		$forum = ($cur_report['forum_name'] != '') ? '<a href="view_forum.php?id='.$cur_report['forum_id'].'">'.convert_htmlspecialchars($cur_report['forum_name']).'</a>' : 'Deleted';
		$topic = ($cur_report['subject'] != '') ? '<a href="view_topic.php?id='.$cur_report['topic_id'].'">'.convert_htmlspecialchars($cur_report['subject']).'</a>' : 'Deleted';
		$post = ($cur_report['post_id'] != '') ? str_replace("\n", '<br />', convert_htmlspecialchars($cur_report['message'])) : 'Deleted';
		$postid = ($cur_report['post_id'] != '') ? '<a href="view_topic.php?pid='.$cur_report['post_id'].'#p'.$cur_report['post_id'].'">Post #'.$cur_report['post_id'].'</a>' : 'Deleted';
?>
				<div class="inform">
					<fieldset>
						<legend>Reported <?php echo format_time($cur_report['created']) ?></legend>
						<div class="infldset">
							<table cellspacing="0">
								<tr>
									<th scope="row">Forum&nbsp;&raquo;&nbsp;Topic&nbsp;&raquo;&nbsp;Post</th>
									<td><?php echo $forum ?>&nbsp;&raquo;&nbsp;<?php echo $topic ?>&nbsp;&raquo;&nbsp;<?php echo $postid ?></td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin['Report by']; ?> <?php echo $reporter ?><div><input type="submit" class="b1" name="zap_id[<?php echo $cur_report['id'] ?>]" value="<?php echo $lang_admin['Zap'] ?>" /></div></th>
									<td><?php echo $post ?></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php
	}
}
else echo "\t\t\t\t".'<p>'. $lang_admin['no_new_reports'].'</p>'."\n";
?>
			</form>
		</div>
	</div>
	<div class="tab-page" id="zapped-rep-page"><h2 class="tab"><?php echo $lang_admin['Zapped']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "zapped-rep-page" ) );</script>
		<div class="box">
			<div class="fakeform">
<?php
$result = $db->query('SELECT r.id, r.post_id, r.topic_id, r.forum_id, r.reported_by, r.message, r.zapped, r.zapped_by AS zapped_by_id, t.subject, f.forum_name, u.username AS reporter, u2.username AS zapped_by FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id LEFT JOIN '.$db->prefix.'users AS u2 ON r.zapped_by=u2.id WHERE r.zapped IS NOT NULL ORDER BY zapped DESC LIMIT 10') or error('Unable to fetch report list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
	while ($cur_report = $db->fetch_assoc($result))
	{
		$reporter = ($cur_report['reporter'] != '') ? '<a href="profile.php?id='.$cur_report['reported_by'].'">'.convert_htmlspecialchars($cur_report['reporter']).'</a>' : 'Deleted user';
		$forum = ($cur_report['forum_name'] != '') ? '<a href="view_forum.php?id='.$cur_report['forum_id'].'">'.convert_htmlspecialchars($cur_report['forum_name']).'</a>' : 'Deleted';
		$topic = ($cur_report['subject'] != '') ? '<a href="view_topic.php?id='.$cur_report['topic_id'].'">'.convert_htmlspecialchars($cur_report['subject']).'</a>' : 'Deleted';
		$post = ($cur_report['post_id'] != '') ? str_replace("\n", '<br />', convert_htmlspecialchars($cur_report['message'])) : 'Post deleted';
		$post_id = ($cur_report['post_id'] != '') ? '<a href="view_topic.php?pid='.$cur_report['post_id'].'#p'.$cur_report['post_id'].'">Post #'.$cur_report['post_id'].'</a>' : 'Deleted';
		$zapped_by = ($cur_report['zapped_by'] != '') ? '<a href="profile.php?id='.$cur_report['zapped_by_id'].'">'.convert_htmlspecialchars($cur_report['zapped_by']).'</a>' : 'N/A';
?>
				<div class="inform">
					<fieldset>
						<legend>Zapped <?php echo format_time($cur_report['zapped']) ?></legend>
						<div class="infldset">
							<table cellspacing="0">
								<tr>
									<th scope="row">Forum&nbsp;&raquo;&nbsp;Topic&nbsp;&raquo;&nbsp;Post</th>
									<td><?php echo $forum ?>&nbsp;&raquo;&nbsp;<?php echo $topic ?>&nbsp;&raquo;&nbsp;<?php echo $post_id ?></td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin['Reported by']; ?> <?php echo $reporter ?><div class="topspace">Zapped by <?php echo $zapped_by ?></div></th>
									<td><?php echo $post ?></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php
	}
}
else echo "\t\t\t\t".'<p>'. $lang_admin['no_zapped_reports'].'</p>'."\n";
?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>