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
if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';

if (isset($_POST['add_forum']))
{
confirm_referrer('admin_forums.php');
$add_to_cat = intval($_POST['add_to_cat']);
if ($add_to_cat < 1) message($lang_common['Bad request']);
$db->query('INSERT INTO '.$db->prefix.'forums (cat_id) VALUES('.$add_to_cat.')') or error('Unable to create forum', __FILE__, __LINE__, $db->error());
require_once FORUM_ROOT.'include/cache.php';
generate_quickjump_cache();
redirect(FORUM_ROOT.'admin/admin_forums.php', 'Forum added. Redirecting &hellip;');
}
else if (isset($_POST['run_merge']))
{
$forum1 = intval($_POST['forum1']);
$forum2 = intval($_POST['forum2']);
if (trim($forum1) == '') message('You never specified a forum to merge from.');
if (trim($forum2) == '') message('You never specified a forum to merge to.');
$result = $db->query("SELECT * FROM ".$db->prefix."forums WHERE id=".$forum1);
if ($db->num_rows($result) == '0') message('The forum you specified to merge from does not exist.');
$result = $db->query("SELECT * FROM ".$db->prefix."forums WHERE id=".$forum2);
if ($db->num_rows($result) == '0') message('The forum you specified to merge to does not exist.');
if ($forum1 == $forum2) message('The forums you specified are the same.');
$db->query("UPDATE ".$db->prefix."topics set forum_id=".$forum2." where forum_id=".$forum1);
$db->query("DELETE FROM ".$db->prefix."forums WHERE id = ".$forum1);
update_forum($forum2);
redirect(FORUM_ROOT.'admin/admin_forums.php', 'Forums merged succesfully. Redirecting &hellip;');
}
else if (isset($_GET['del_forum']))
{
confirm_referrer('admin_forums.php');
$forum_id = intval($_GET['del_forum']);
if ($forum_id < 1) message($lang_common['Bad request']);
if (isset($_POST['del_forum_comply']))
{
@set_time_limit(0);
prune($forum_id, 1, -1);
$result = $db->query('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL') or error('Unable to fetch redirect topics', __FILE__, __LINE__, $db->error());
$num_orphans = $db->num_rows($result);
if ($num_orphans)
{
for ($i = 0; $i < $num_orphans; ++$i) $orphans[] = $db->result($result, $i);
$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $orphans).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());
}
$db->query('DELETE FROM '.$db->prefix.'forums WHERE id='.$forum_id) or error('Unable to delete forum', __FILE__, __LINE__, $db->error());
$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE forum_id='.$forum_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());
require_once FORUM_ROOT.'include/cache.php';
generate_quickjump_cache();
redirect(FORUM_ROOT.'admin/admin_forums.php', 'Forum deleted. Redirecting &hellip;');
}
else
{
$result = $db->query('SELECT forum_name FROM '.$db->prefix.'forums WHERE id='.$forum_id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
$forum_name = convert_htmlspecialchars($db->result($result));
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Forums';
require FORUM_ROOT.'header.php';
generate_admin_menu('forums');
?><title></title>
<div class="blockform">
<h2><span><?php echo$lang_admin['Conf_del_forum']?></span></h2>
<div class="box">
<form method="post" action="admin_forums.php?del_forum=<?php echo $forum_id ?>">
<div class="inform">
<fieldset>
<legend>
<?php echo$lang_admin['Important']?>
</legend>
<div class="infldset">
<p>
<?php echo$lang_admin['Conf_delete_forum']?> "<?php echo$forum_name ?>"?
</p>
<p>
<?php echo$lang_admin['Delete_forum_warn']?>
</p>
</div>
</fieldset>
</div>
<p>
<input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>" />
<input type="submit" class="b1" name="del_forum_comply" value="<?php echo $lang_admin['Remove']; ?>" />
</p>
</form>
</div>
<div class="clearer">
</div>
</div></div>
<?php
require FORUM_ROOT.'admin/admin_footer.php';
}
}
else if (isset($_POST['update_positions']))
{
confirm_referrer('admin_forums.php');
while (list($forum_id, $disp_position) = @each($_POST['position']))
{
if (!preg_match('#^\d+$#', $disp_position)) message('Position must be a positive integer value.');
$db->query('UPDATE '.$db->prefix.'forums SET disp_position='.$disp_position.' WHERE id='.$forum_id) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
}
require_once FORUM_ROOT.'include/cache.php';
generate_quickjump_cache();
redirect(FORUM_ROOT.'admin/admin_forums.php', 'Forums updated. Redirecting &hellip;');
}
else if (isset($_GET['edit_forum']))
{
$forum_id = intval($_GET['edit_forum']);
if ($forum_id < 1) message($lang_common['Bad request']);
if (isset($_POST['save']))
{
confirm_referrer('admin_forums.php');
$forum_name = trim($_POST['forum_name']);
$forum_desc = forum_linebreaks(trim($_POST['forum_desc']));
$cat_id = intval($_POST['cat_id']);
$sort_by = intval($_POST['sort_by']);
$redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : null;
$valide = intval($_POST['valide']);
$have_password = intval($_POST['password_protected']);
$password = trim($_POST['password']);
if($have_password == 0 && !empty($password))
{
message('You must check the \'have password\' box in order to password protect a forum.');
}
$parent_forum_id = intval($_POST['parent_forum']);
if ($forum_name == '') message('You must enter a forum name.');
if ($cat_id < 1) message($lang_common['Bad request']);
$forum_desc = ($forum_desc != '') ? '\''.$db->escape($forum_desc).'\'' : 'NULL';
$redirect_url = ($redirect_url != '') ? '\''.$db->escape($redirect_url).'\'' : 'NULL';
$db->query('UPDATE '.$db->prefix.'forums SET forum_name=\''.$db->escape($forum_name).'\', forum_desc='.$forum_desc.', redirect_url='.$redirect_url.', sort_by='.$sort_by.', cat_id='.$cat_id.', parent_forum_id='.$parent_forum_id.', protected='. $have_password .', password=\''.$password.'\', valide='.$valide.' WHERE id='.$forum_id) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
if (isset($_POST['read_forum_old']))
{
$result = $db->query('SELECT g_id, g_read_board, g_post_replies, g_post_topics FROM '.$db->prefix.'groups WHERE g_id!='.USER_ADMIN) or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
while ($cur_group = $db->fetch_assoc($result))
{
$read_forum_new = ($cur_group['g_read_board'] == '1') ? isset($_POST['read_forum_new'][$cur_group['g_id']]) ? $_POST['read_forum_new'][$cur_group['g_id']] : '0' : $_POST['read_forum_old'][$cur_group['g_id']];
$post_replies_new = isset($_POST['post_replies_new'][$cur_group['g_id']]) ? $_POST['post_replies_new'][$cur_group['g_id']] : '0';
$post_topics_new = isset($_POST['post_topics_new'][$cur_group['g_id']]) ? $_POST['post_topics_new'][$cur_group['g_id']] : '0';
$image_upload_new = isset($_POST['image_upload_new'][$cur_group['g_id']]) ? $_POST['image_upload_new'][$cur_group['g_id']] : '0';
if ($read_forum_new != $_POST['read_forum_old'][$cur_group['g_id']] || $post_replies_new != $_POST['post_replies_old'][$cur_group['g_id']] || $post_topics_new != $_POST['post_topics_old'][$cur_group['g_id']] || $image_upload_new != $_POST['image_upload_old'][$cur_group['g_id']])
{
if ($read_forum_new == '1' && $post_replies_new == $cur_group['g_post_replies'] && $post_topics_new == $cur_group['g_post_topics'] && $image_upload_new == '0')
$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE group_id='.$cur_group['g_id'].' AND forum_id='.$forum_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());
else
{
$db->query('UPDATE '.$db->prefix.'forum_perms SET read_forum='.$read_forum_new.', post_replies='.$post_replies_new.', post_topics='.$post_topics_new.', image_upload='.$image_upload_new.' WHERE group_id='.$cur_group['g_id'].' AND forum_id='.$forum_id) or error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
if (!$db->affected_rows()) $db->query('INSERT INTO '.$db->prefix.'forum_perms (group_id, forum_id, read_forum, post_replies, post_topics, image_upload) VALUES('.$cur_group['g_id'].', '.$forum_id.', '.$read_forum_new.', '.$post_replies_new.', '.$post_topics_new.', '.$image_upload_new.')') or error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
}
}
}
}
require_once FORUM_ROOT.'include/cache.php';
generate_quickjump_cache();
redirect(FORUM_ROOT.'admin/admin_forums.php', 'Forum updated. Redirecting &hellip;');
}
else if (isset($_POST['revert_perms']))
{
confirm_referrer('admin_forums.php');
$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE forum_id='.$forum_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());
require_once FORUM_ROOT.'include/cache.php';
generate_quickjump_cache();
redirect(FORUM_ROOT.'admin/admin_forums.php?edit_forum='.$forum_id, 'Permissions reverted to defaults. Redirecting &hellip;');
}
$result = $db->query('SELECT id, forum_name, forum_desc, redirect_url, num_topics, sort_by, cat_id, parent_forum_id, protected, password, valide  FROM '.$db->prefix.'forums WHERE id='.$forum_id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_forum = $db->fetch_assoc($result);
$parent_forums = Array();
$result = $db->query('SELECT DISTINCT parent_forum_id FROM '.$db->prefix.'forums WHERE parent_forum_id != 0');
while($r = $db->fetch_row($result)) $parent_forums[] = $r[0];
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Forums';
require FORUM_ROOT.'header.php';
generate_admin_menu('forums');
?>
<div class="blockform">
	<div class="tab-page" id="forums1Pane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "forums1Pane" ), 1 )</script>
	<div class="tab-page" id="adv-forums-page"><h2 class="tab"><?php echo$lang_admin['Edit']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "adv-forums-page" ) );</script>
<div class="box">
<form id="edit_forum" method="post" action="admin_forums.php?edit_forum=<?php echo $forum_id ?>">
<div class="inform">
<fieldset>
<legend>
<?php echo$lang_admin['Edit_forum_details']?>
</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">
<?php echo$lang_admin['Forum_name']?>
</th>
<td>
<input type="text" class="textbox" name="forum_name" size="40" maxlength="80" value="<?php echo convert_htmlspecialchars($cur_forum['forum_name']) ?>" tabindex="1" />
</td>
</tr>
<tr>
<th scope="row">
<?php echo$lang_admin['Desc_html']?>
</th>
<td>
<textarea name="forum_desc" rows="3" cols="38" tabindex="2"><?php echo convert_htmlspecialchars($cur_forum['forum_desc']); ?></textarea>
</td>
</tr>
<tr>
<th scope="row">
Category
</th>
<td>
<select name="cat_id" tabindex="3">
<?php
$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while ($cur_cat = $db->fetch_assoc($result))
{
$selected = ($cur_cat['id'] == $cur_forum['cat_id']) ? ' selected="selected"' : '';
echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_cat['id'].'"'.$selected.'>'.convert_htmlspecialchars($cur_cat['cat_name']).'</option>'."\n";
}
?>
</select>
</td>
</tr>
<tr>
<th scope="row">
<?php echo$lang_admin['Sort_by']?>
</th>
<td>
<select name="sort_by" tabindex="4">
<option value="0"<?php if ($cur_forum['sort_by'] == '0') echo ' selected="selected"' ?>>
<?php echo$lang_admin['Last_post']?>
</option>
<option value="1"<?php if ($cur_forum['sort_by'] == '1') echo ' selected="selected"' ?>>
<?php echo$lang_admin['Topic_start']?>
</option>
</select>
</td>
</tr>
<tr>
<th scope="row">
<?php echo$lang_admin['Redir_url']?>
</th>
<td>
<?php echo ($cur_forum['num_topics']) ? 'Only available in empty forums' : '
<input type="text" class="textbox" name="redirect_url" size="40" maxlength="100" value="'.convert_htmlspecialchars($cur_forum['redirect_url']).'" tabindex="5" />'; ?>
</td>
</tr>
<tr>
									<th scope="row"><?php echo $lang_admin['Topic_validation']; ?></th>
									<td>
										<select name="valide" tabindex="5">
											<option value="0"<?php if ($cur_forum['valide'] == '0') echo ' selected="selected"' ?>>No</option>
											<option value="1"<?php if ($cur_forum['valide'] == '1') echo ' selected="selected"' ?>>Yes</option>
										</select>&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The topics must be validated by an administrator before being visible in the forum.');" onmouseout="return nd();" alt="" /></td>
								</tr>
<tr>
<th scope="row">
<?php echo$lang_admin['Pass_prot']?>
</th>
<td>
<input type="checkbox" name="password_protected" value="1" <?php if ($cur_forum['protected'] == 1) { echo "checked"; } else { echo ""; } ?>>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Check this if you want this forum protected by a password.');" onmouseout="return nd();" alt="" /></td>
</td>
</tr>
<tr>
<th scope="row">
<?php echo$lang_admin['Pass_prot_pass']?>
</th>
<td>
<input class="textbox" maxlength="20" size="25" type="text" name="password" value="<?php echo$cur_forum['password']?>">
</td>
</tr>
<tr>
<th scope="row">
Parent forum
</th>
<td>
<select name="parent_forum">
<option value="0">
No parent forum
</option>
<?php
if(!in_array($cur_forum['id'],$parent_forums))
{
$result = $db->query('SELECT id, forum_name, parent_forum_id FROM '.$db->prefix.'forums ORDER BY disp_position') or error('Unable to fetch forum lise',__FILE__,__LINE__,$db->error());
while($forum_list = $db->fetch_assoc($result))
{
$selected = ($forum_list['id'] == $cur_forum['parent_forum_id']) ? ' selected="selected"' : '';
if(!$forum_list['parent_forum_id'] && $forum_list['id'] != $cur_forum['id']) echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$forum_list['id'].'"'.$selected.'>'.convert_htmlspecialchars($forum_list['forum_name']).'</option>'."\n";
}
}
?>
</select>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>
<?php echo$lang_admin['Edit_group_perms']?>
</legend>
<div class="infldset">
<p>
<?php echo$lang_admin['Edit_group_perms_desc']?>
</p>
<table id="forumperms" cellspacing="0">
<thead>
<tr>
<th class="atcl">&nbsp;

</th>
<th>
<?php echo$lang_admin['Read_forum']?>
</th>
<th>
<?php echo$lang_admin['Post_replies']?>
</th>
<th>
<?php echo$lang_admin['Post_topic']?>
</th>
<th>
<?php echo$lang_admin['Image_upload']?>
</th>
</tr>
</thead>
<tbody>
<?php
$result = $db->query('SELECT g.g_id, g.g_title, g.g_read_board, g.g_post_replies, g.g_post_topics, fp.read_forum, fp.post_replies, fp.post_topics, fp.image_upload FROM '.$db->prefix.'groups AS g LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (g.g_id=fp.group_id AND fp.forum_id='.$forum_id.') WHERE g.g_id!='.USER_ADMIN.' ORDER BY g.g_id') or error('Unable to fetch group forum permission list', __FILE__, __LINE__, $db->error());
while ($cur_perm = $db->fetch_assoc($result))
{
$read_forum = ($cur_perm['read_forum'] != '0') ? true : false;
$post_replies = (($cur_perm['g_post_replies'] == '0' && $cur_perm['post_replies'] == '1') || ($cur_perm['g_post_replies'] == '1' && $cur_perm['post_replies'] != '0')) ? true : false;
$post_topics = (($cur_perm['g_post_topics'] == '0' && $cur_perm['post_topics'] == '1') || ($cur_perm['g_post_topics'] == '1' && $cur_perm['post_topics'] != '0')) ? true : false;
$image_upload = (isset($cur_perm['image_upload']) && $cur_perm['image_upload'] == '1' && $cur_perm['g_id'] != '3') ? true : false;
$read_forum_def = ($cur_perm['read_forum'] == '0') ? false : true;
$post_replies_def = (($post_replies && $cur_perm['g_post_replies'] == '0') || (!$post_replies && ($cur_perm['g_post_replies'] == '' || $cur_perm['g_post_replies'] == '1'))) ? false : true;
$post_topics_def = (($post_topics && $cur_perm['g_post_topics'] == '0') || (!$post_topics && ($cur_perm['g_post_topics'] == '' || $cur_perm['g_post_topics'] == '1'))) ? false : true;
?>
<tr>
<th class="atcl">
<?php echo convert_htmlspecialchars($cur_perm['g_title']) ?>
</th>
<td<?php if (!$read_forum_def) echo ' class="nodefault"'; ?>>
<input type="hidden" name="read_forum_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($read_forum) ? '1' : '0'; ?>" />
<input type="checkbox" name="read_forum_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($read_forum) ? ' checked="checked"' : ''; ?><?php echo ($cur_perm['g_read_board'] == '0') ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if (!$post_replies_def && $cur_forum['redirect_url'] == '') echo ' class="nodefault"'; ?>>
<input type="hidden" name="post_replies_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($post_replies) ? '1' : '0'; ?>" />
<input type="checkbox" name="post_replies_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($post_replies) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url'] != '') ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if (!$post_topics_def && $cur_forum['redirect_url'] == '') echo ' class="nodefault"'; ?>>
<input type="hidden" name="post_topics_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($post_topics) ? '1' : '0'; ?>" />
<input type="checkbox" name="post_topics_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($post_topics) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url'] != '') ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if ($image_upload) echo ' class="nodefault"'; ?>>
<input type="hidden" name="image_upload_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($image_upload) ? '1' : '0'; ?>" />
<input type="checkbox" name="image_upload_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($image_upload) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url'] != '' || $cur_perm['g_id'] == '3') ? ' disabled="disabled"' : ''; ?> />
</td>
</tr>
<?php
}
?>
</tbody>
</table>
</div>
</fieldset>
</div>
<p class="submitend" style="text-align:left;"><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="revert_perms" value="Revert to default" /><input type="submit" class="b1" name="save" value="Save changes" /></p>
</form>
</div>
</div>
<div class="clearer">
</div>
</div>
<?php
require FORUM_ROOT.'admin/admin_footer.php';
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Forums'];
require FORUM_ROOT.'header.php';
generate_admin_menu('forums');
?>
<div class="blockform">
	<div class="tab-page" id="forumsPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "forumsPane" ), 1 )</script>
	<div class="tab-page" id="help-page"><h2 class="tab"><?php echo $lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
							<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/categories.png></td>
							<td><span><?php echo $lang_admin['help_forums']; ?></span></td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="Add-Forum-page"><h2 class="tab"><?php echo $lang_admin['Add']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-forum-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_forums.php?action=adddel">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin['Create_forum']; ?></legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin['Add_forum_cat']; ?>
<div>
</div>
</th>
<td>
<select name="add_to_cat" tabindex="1">
<?php
$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while ($cur_cat = $db->fetch_assoc($result)) echo "\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_cat['id'].'">'.convert_htmlspecialchars($cur_cat['cat_name']).'</option>'."\n";
?>
</select>&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Select the category to which you wish to add a new forum.');" onmouseout="return nd();" alt="" />
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<p class="submitend" style="text-align:left;"><input class="b1" class="b1" type="submit" name="add_forum" value=" Add " tabindex="2" /></p>
</form>
</div>
<?php
	$toskip = array();
	$result3 = $db->query('SELECT f.id AS fid FROM '.$db->prefix.'forums AS f WHERE f.parent_forum_id != 0') or error('Cannot retrieve list of subforums', __FILE__, __LINE__, $db->error());
	while($results3 = mysql_fetch_array($result3))
	{
		$toskip[] = $results3['fid'];
	}
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'forums') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
	$numforums = $db->result($result);
	if ($numforums > 0)
	{
?>


	</div>
	<div class="tab-page" id="Edit-Forums-page"><h2 class="tab"><?php echo $lang_admin['Edit']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-forums-page" ) );</script>
	<div class="box">
		<form id="edforum" method="post" action="admin_forums.php?action=edit">
<?php
		$tabindex_count = 4;
		$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.disp_position, f.parent_forum_id FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
		$cur_category = 0;
		while ($cur_forum = $db->fetch_assoc($result))
		{
			if ($cur_forum['cid'] != $cur_category)
			{
				if ($cur_category != 0) echo "\t\t\t\t\t\t\t".'</table>'."\n\t\t\t\t\t\t".'</div>'."\n\t\t\t\t\t".'</fieldset>'."\n\t\t\t\t".'</div>'."\n";
?>
				<div class="inform">
				<fieldset>
					<legend><?php echo $lang_admin['Category']; ?> <?php echo convert_htmlspecialchars($cur_forum['cat_name']) ?></legend>
					<div class="infldset">
						<table cellspacing="0">
<?php
				$cur_category = $cur_forum['cid'];
			}
			if (!in_array($cur_forum['fid'],$toskip))
			{
?>
						<tr>
							<th><a href="admin_forums.php?edit_forum=<?php echo $cur_forum['fid'] ?>"><?php echo $lang_admin['Edit']; ?></a> - <a href="admin_forums.php?del_forum=<?php echo $cur_forum['fid'] ?>"><?php echo $lang_admin['Delete']; ?></a></th>
							<td>Position&nbsp;&nbsp;<input type="text" class="textbox" name="position[<?php echo $cur_forum['fid'] ?>]" size="3" maxlength="2" value="<?php echo $cur_forum['disp_position'] ?>" tabindex="<?php echo $tabindex_count ?>" />&nbsp;&nbsp;<strong><?php echo convert_htmlspecialchars($cur_forum['forum_name']) ?></strong></td>
						</tr>
<?php
			}

			$result2 = $db->query('SELECT f.id AS fid, f.forum_name, f.disp_position, f.parent_forum_id FROM '.$db->prefix.'forums AS f WHERE f.parent_forum_id = '.$cur_forum['fid'].'  ORDER BY f.disp_position') or error('Cannot retrieve list of forums', __FILE__, __LINE__, $db->error());
			if($db->num_rows($result2))
			{
?>


                        
<?php
				while ($cur_ss_forum = $db->fetch_assoc($result2))
				{
?>
                                                    <tr>
                                                        <th>&nbsp;&raquo;&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin_forums.php?edit_forum=<?php echo $cur_ss_forum['fid'] ?>"><?php echo $lang_admin['Edit']; ?></a> - <a href="admin_forums.php?del_forum=<?php echo $cur_ss_forum['fid'] ?>"><?php echo $lang_admin['Delete']; ?></a></th>
                                                        <td><?php echo $lang_admin['Position']; ?>&nbsp;&nbsp;<input type="text" class="textbox" name="position[<?php echo $cur_ss_forum['fid'] ?>]" size="3" maxlength="2" value="<?php echo $cur_ss_forum['disp_position'] ?>" />&nbsp;&nbsp;<strong><?php echo convert_htmlspecialchars($cur_ss_forum['forum_name']) ?></strong></td>
                                                    </tr>
<?php
				}
?>

<?php
			}




$tabindex_count += 2;
}
?>
</table>
</div>
</fieldset>
</div>
<p class="submitend" style="text-align:left;">
<input type="submit" class="b1" name="update_positions" value="Update positions" tabindex="<?php echo $tabindex_count ?>" />
</p>
</form>
</div>
<?php } ?>

	</div>
	<div class="tab-page" id="Merge-Forums-page"><h2 class="tab"><?php echo $lang_admin['Merge']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "merge-forums-page" ) );</script>
<div class="box">
<form id="merge" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
<div class="inform">
<fieldset>
<legend>
<?php echo $lang_admin['Select_forum_merge']; ?>
</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<td>
<select name="forum1">
<?php
$categories_result = $db->query("SELECT id, cat_name FROM ".$db->prefix."categories WHERE 1=1 ORDER BY id ASC");
$forums_result = $db->query("SELECT id, forum_name, cat_id FROM ".$db->prefix."forums WHERE 1=1 ORDER BY cat_id ASC");
$cat_now = 0;

while ($forums = $db->fetch_assoc($forums_result))
{
if ($forums['cat_id'] != $cat_now)
{
$categories = $db->fetch_assoc($categories_result);
echo "<option value='blargh' disabled='disabled'>".convert_htmlspecialchars($categories['cat_id'])."</option>";
$cat_now = $categories['id'];
}
echo "<option value='".convert_htmlspecialchars($forums['id'])."'>".convert_htmlspecialchars($forums['forum_name'])."</option>";
}
?>
</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_admin['Select_forum_to_merge']; ?>');" onmouseout="return nd();" alt="" />
</td>
<td>
<select name="forum2">
<?php
$categories_result = $db->query("SELECT id, cat_name FROM ".$db->prefix."categories WHERE 1=1 ORDER BY id ASC");
$forums_result = $db->query("SELECT id, forum_name, cat_id FROM ".$db->prefix."forums WHERE 1=1 ORDER BY cat_id ASC");
$cat_now = 0;
while ($forums = $db->fetch_assoc($forums_result))
{
if ($forums['cat_id'] != $cat_now)
{
$categories = $db->fetch_assoc($categories_result);
echo "<option value='blargh' disabled='disabled'>".convert_htmlspecialchars($categories['cat_id'])."</option>";
$cat_now = $categories['id'];
}
echo "<option value='".convert_htmlspecialchars($forums['id'])."'>".convert_htmlspecialchars($forums['forum_name'])."</option>";
}
?>
</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_admin['Choose_forum_to_merge']; ?>');" onmouseout="return nd();" alt="" />
</td>
</tr>
<tr>
<td colspan="2">
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<p class="submitend" style="text-align:left;"><input class="b1" type="submit" name="run_merge" value="<?php echo $lang_admin['Merge']; ?>" tabindex="3" /></p>
</form>
</div>
</div>
<div class="clearer">
</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>
