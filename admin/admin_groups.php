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
if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);

if (isset($_POST['add_group']) || isset($_GET['edit_group']))
{
	if (isset($_POST['add_group']))
	{
		$base_group = intval($_POST['base_group']);
		$result = $db->query('SELECT * FROM '.$db->prefix.'groups WHERE g_id='.$base_group) or error('Unable to fetch user group info', __FILE__, __LINE__, $db->error());
		$group = $db->fetch_assoc($result);
		$mode = 'add';
	}
	else
	{
		$group_id = intval($_GET['edit_group']);
		if ($group_id < 1) message($lang_common['Bad request']);
		$result = $db->query('SELECT * FROM '.$db->prefix.'groups WHERE g_id='.$group_id) or error('Unable to fetch user group info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		$group = $db->fetch_assoc($result);
		$mode = 'edit';
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / User groups';
	$required_fields = array('req_title' => 'Group title');
	$focus_element = array('groups2', 'req_title');
	require FORUM_ROOT.'header.php';
	generate_admin_menu('groups');
?>
<div class="blockform">
	<div class="tab-page" id="groups1Pane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "groups1Pane" ), 1 )</script>
	<div class="tab-page" id="edit-def-groups-page"><h2 class="tab"><?php echo$lang_admin['Edit']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-def-groups-page" ) );</script>
	<div class="box">
		<form name="zuzu" id="groups2" method="post" action="admin_groups.php" onsubmit="return process_form(this)">
			<div class="inform">
				<input type="hidden" name="mode" value="<?php echo $mode ?>" />
					<?php if ($mode == 'edit'): ?>
				<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
					<?php endif; if ($mode == 'add'): ?>
				<input type="hidden" name="base_group" value="<?php echo $base_group ?>" />
					<?php endif; ?>	
				<fieldset>
					<legend>
					<?php echo$lang_admin['Setup_group_perms']?>
					</legend>
					<div class="infldset">
						<p>
							<?php echo$lang_admin['Setup_group_perms_desc']?>
						</p>
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">
									<?php echo$lang_admin['Group_title']?>
									</th>
									<td>
										<input type="text" class="textbox" name="req_title" size="25" maxlength="50" value="<?php if ($mode == 'edit') echo convert_htmlspecialchars($group['g_title']); ?>" tabindex="1" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										User title
									</th>
									<td>
										<input type="text" class="textbox" name="user_title" size="25" maxlength="50" value="<?php echo convert_htmlspecialchars($group['g_user_title']) ?>" tabindex="2" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This title will override any rank users in this group have attained. Leave blank to use default title or rank.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
									<?php echo$lang_admin['Group_color']?>
									</th>
									<td>
										<input type="text" class="textbox" name="group_color" size="9" maxlength="7" value="<?php echo $group['g_color'] ?>" tabindex="25" />&nbsp;&nbsp;
										<a href="javascript:TCP.popup(document.forms['zuzu'].group_color);">
											<img width="15" height="13" border="0" alt="Click Here to select the color" src="img/general/selcol.png">
										</a>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The color that will show for users in this group in the who\'s online list, and in posts...');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<?php if ($group['g_id'] != USER_ADMIN): ?>
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Read_board']?>
									</th>
									<td>
										<input type="radio" name="read_board" value="1"<?php if ($group['g_read_board'] == '1') echo ' checked="checked"' ?> tabindex="3" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="read_board" value="0"<?php if ($group['g_read_board'] == '0') echo ' checked="checked"' ?> tabindex="4" />&nbsp;
										<strong>
											<?php echo$lang_common['No']?>
										</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to view the board. This setting applies to every aspect of the board and can therefore not be overridden by forum specific settings. If this is set to No, users in this group will only be able to login/logout and register.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Post_replies']?>
									</th>
									<td>
										<input type="radio" name="post_replies" value="1"<?php if ($group['g_post_replies'] == '1') echo ' checked="checked"' ?> tabindex="5" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="post_replies" value="0"<?php if ($group['g_post_replies'] == '0') echo ' checked="checked"' ?> tabindex="6" />&nbsp;
										<strong>
											<?php echo$lang_common['No']?>
										</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to post replies in topics.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Post_topic']?>
									</th>
									<td>
										<input type="radio" name="post_topics" value="1"<?php if ($group['g_post_topics'] == '1') echo ' checked="checked"' ?> tabindex="7" />&nbsp;
											<strong>
												<?php echo$lang_common['Yes']?>
											</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="post_topics" value="0"<?php if ($group['g_post_topics'] == '0') echo ' checked="checked"' ?> tabindex="8" />&nbsp;
											<strong>
												<?php echo$lang_common['No']?>
											</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to post new topics.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
									<?php if ($group['g_id'] != USER_GUEST): ?>	
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Edit_posts']?>
									</th>
									<td>
										<input type="radio" name="edit_posts" value="1"<?php if ($group['g_edit_posts'] == '1') echo ' checked="checked"' ?> tabindex="11" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="edit_posts" value="0"<?php if ($group['g_edit_posts'] == '0') echo ' checked="checked"' ?> tabindex="12" />&nbsp;
										<strong>
											<?php echo$lang_common['No']?>
										</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to edit their own posts.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Delete_posts']?>
									</th>
									<td>
										<input type="radio" name="delete_posts" value="1"<?php if ($group['g_delete_posts'] == '1') echo ' checked="checked"' ?> tabindex="13" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="delete_posts" value="0"<?php if ($group['g_delete_posts'] == '0') echo ' checked="checked"' ?> tabindex="14" />&nbsp;
										<strong>
											<?php echo$lang_common['No']?>
										</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to delete their own posts.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Delete_topics']?>
									</th>
									<td>
										<input type="radio" name="delete_topics" value="1"<?php if ($group['g_delete_topics'] == '1') echo ' checked="checked"' ?> tabindex="15" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="delete_topics" value="0"<?php if ($group['g_delete_topics'] == '0') echo ' checked="checked"' ?> tabindex="16" />&nbsp;
											<strong>
												<?php echo$lang_common['No']?>
											</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to delete their own topics (including any replies).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
									<?php echo$lang_admin['Set_user_title']?>
									</th>
									<td>
										<input type="radio" name="set_title" value="1"<?php if ($group['g_set_title'] == '1') echo ' checked="checked"' ?> tabindex="17" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="set_title" value="0"<?php if ($group['g_set_title'] == '0') echo ' checked="checked"' ?> tabindex="18" />&nbsp;
										<strong>
											<?php echo$lang_common['No']?>
										</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to set their own user title.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<?php endif; ?>	
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Use_search']?>
									</th>
									<td>
										<input type="radio" name="search" value="1"<?php if ($group['g_search'] == '1') echo ' checked="checked"' ?> tabindex="19" />&nbsp;
										<strong>
											<?php echo$lang_common['Yes']?>
										</strong>&nbsp;&nbsp;&nbsp;
										<input type="radio" name="search" value="0"<?php if ($group['g_search'] == '0') echo ' checked="checked"' ?> tabindex="20" />&nbsp;
										<strong>
											<?php echo$lang_common['No']?>
										</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to use the search feature.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
            						<tr>
										<th scope="row">
										<?php echo$lang_admin['View_user_list']?>
										</th>
										<td>
											<input type="radio" name="view_users" value="1"<?php if ($group['g_view_users'] == '1') echo ' checked="checked"' ?> tabindex="21" />&nbsp;
											<strong>
												<?php echo$lang_common['Yes']?>
											</strong>&nbsp;&nbsp;&nbsp;
											<input type="radio" name="view_users" value="0"<?php if ($group['g_view_users'] == '0') echo ' checked="checked"' ?> tabindex="22" />&nbsp;
											<strong>
												<?php echo$lang_common['No']?>
											</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to see the user list.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<?php echo$lang_admin['Search_user_list']?>
										</th>
										<td>
											<input type="radio" name="search_users" value="1"<?php if ($group['g_search_users'] == '1') echo ' checked="checked"' ?> tabindex="21" />&nbsp;
											<strong>
												<?php echo$lang_common['Yes']?>
											</strong>&nbsp;&nbsp;&nbsp;
											<input type="radio" name="search_users" value="0"<?php if ($group['g_search_users'] == '0') echo ' checked="checked"' ?> tabindex="22" />&nbsp;
											<strong>
												<?php echo$lang_common['No']?>
											</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Allow users in this group to freetext search for users in the user list.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<?php if ($group['g_id'] != USER_GUEST): ?>
								<tr>
									<th scope="row">
									<?php echo$lang_admin['Edit_sub_int']?>
									</th>
									<td>
										<input type="text" class="textbox" name="edit_subjects_interval" size="5" maxlength="5" value="<?php echo $group['g_edit_subjects_interval'] ?>" tabindex="23" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of seconds after post time that users in this group may edit the subject of topics they\'ve posted. Set to 0 to allow edits indefinitely.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
									<?php echo$lang_admin['Post_flo_int']?>
									</th>
									<td>
										<input type="text" class="textbox" name="post_flood" size="5" maxlength="4" value="<?php echo $group['g_post_flood'] ?>" tabindex="24" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of seconds that users in this group have to wait between posts. Set to 0 to disable.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php echo$lang_admin['Search_flo_int']?></th>
									<td>
										<input type="text" class="textbox" name="search_flood" size="5" maxlength="4" value="<?php echo $group['g_search_flood'] ?>" tabindex="25" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Number of seconds that users in this group have to wait between searches. Set to 0 to disable.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
                				<tr>
									<th scope="row">
									<?php echo$lang_admin['Num_inv']?>
									</th>
									<td>
										<input type="text" class="textbox" name="invitations" size="5" maxlength="4" value="<?php echo $group['g_invitations'] ?>" tabindex="25" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Amount of invitations that new users in this group will automatically receive.<br />If you change a user\'s group the amount above will be added to his invitations.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<?php endif; ?><?php endif; ?>
							</table>
							<?php if ($group['g_id'] == USER_MOD ): ?>	
							<p class="warntext">
							<?php echo$lang_admin['Note_mod_forum']?>
							</p>
							<?php endif; ?>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" name="add_edit_group" class="b1" value="<?php echo $lang_admin['Update']; ?>" tabindex="26" /></p>
			</form>
		</div>
	</div>
	<div class="clearer">
	</div>
</div>
<?php
	require FORUM_ROOT.'admin/admin_footer.php';
}
else if (isset($_POST['add_edit_group']))
{
	confirm_referrer('admin_groups.php');
	$is_admin_group = (isset($_POST['group_id']) && $_POST['group_id'] == USER_ADMIN) ? true : false;
	$title = trim($_POST['req_title']);
	$user_title = trim($_POST['user_title']);
	$group_color = trim($_POST['group_color']);
	$invitations = trim($_POST['invitations']);
	$read_board = isset($_POST['read_board']) ? intval($_POST['read_board']) : '1';
	$post_replies = isset($_POST['post_replies']) ? intval($_POST['post_replies']) : '1';
	$post_topics = isset($_POST['post_topics']) ? intval($_POST['post_topics']) : '1';
	$edit_posts = isset($_POST['edit_posts']) ? intval($_POST['edit_posts']) : ($is_admin_group) ? '1' : '0';
	$delete_posts = isset($_POST['delete_posts']) ? intval($_POST['delete_posts']) : ($is_admin_group) ? '1' : '0';
	$delete_topics = isset($_POST['delete_topics']) ? intval($_POST['delete_topics']) : ($is_admin_group) ? '1' : '0';
	$set_title = isset($_POST['set_title']) ? intval($_POST['set_title']) : ($is_admin_group) ? '1' : '0';
	$search = isset($_POST['search']) ? intval($_POST['search']) : '1';
	$view_users = isset($_POST['view_users']) ? intval($_POST['view_users']) : '1';
	$search_users = isset($_POST['search_users']) ? intval($_POST['search_users']) : '1';
	$edit_subjects_interval = isset($_POST['edit_subjects_interval']) ? intval($_POST['edit_subjects_interval']) : '0';
	$post_flood = isset($_POST['post_flood']) ? intval($_POST['post_flood']) : '0';
	$search_flood = isset($_POST['search_flood']) ? intval($_POST['search_flood']) : '0';
	if ($group_color != '')
	{
		if (!preg_match('{^(#){1}([a-fA-F0-9]){6}$}', $group_color)) message('Your group color is invalid. Example: #000000.');
	}

	if ($title == '') message('You must enter a group title.');
	$user_title = ($user_title != '') ? '\''.$db->escape($user_title).'\'' : 'NULL';
	if ($_POST['mode'] == 'add')
	{
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'groups WHERE g_title=\''.$db->escape($title).'\'') or error('Unable to check group title collision', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result)) message('There is already a group with the title \''.convert_htmlspecialchars($title).'\'.');
	      $db->query('INSERT INTO '.$db->prefix.'groups (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_view_users, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood, g_invitations, g_color) VALUES(\''.$db->escape($title).'\', '.$user_title.', '.$read_board.', '.$post_replies.', '.$post_topics.', '.$edit_posts.', '.$delete_posts.', '.$delete_topics.', '.$set_title.', '.$search.', '.$view_users.', '.$search_users.', '.$edit_subjects_interval.', '.$post_flood.', '.$search_flood.', '.$invitations.', '.$group_color.'\')') or error(mysql_error(), __FILE__, __LINE__, $db->error());
		$new_group_id = $db->insert_id();
		$result = $db->query('SELECT forum_id, read_forum, post_replies, post_topics FROM '.$db->prefix.'forum_perms WHERE group_id='.intval($_POST['base_group'])) or error('Unable to fetch group forum permission list', __FILE__, __LINE__, $db->error());
		while ($cur_forum_perm = $db->fetch_assoc($result)) $db->query('INSERT INTO '.$db->prefix.'forum_perms (group_id, forum_id, read_forum, post_replies, post_topics) VALUES('.$new_group_id.', '.$cur_forum_perm['forum_id'].', '.$cur_forum_perm['read_forum'].', '.$cur_forum_perm['post_replies'].', '.$cur_forum_perm['post_topics'].')') or error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
	}
	else
	{
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'groups WHERE g_title=\''.$db->escape($title).'\' AND g_id!='.intval($_POST['group_id'])) or error('Unable to check group title collision', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result)) message('There is already a group with the title \''.convert_htmlspecialchars($title).'\'.');
   		$db->query('UPDATE '.$db->prefix.'groups SET g_title=\''.$db->escape($title).'\', g_user_title='.$user_title.', g_read_board='.$read_board.', g_post_replies='.$post_replies.', g_post_topics='.$post_topics.', g_edit_posts='.$edit_posts.', g_delete_posts='.$delete_posts.', g_delete_topics='.$delete_topics.', g_set_title='.$set_title.', g_search='.$search.', g_view_users='.$view_users.', g_search_users='.$search_users.', g_edit_subjects_interval='.$edit_subjects_interval.', g_post_flood='.$post_flood.', g_search_flood='.$search_flood.', g_color=\''.$group_color.'\', g_invitations=\''.$invitations.'\' WHERE g_id='.intval($_POST['group_id'])) or error('Unable to update group', __FILE__, __LINE__, $db->error());

	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_quickjump_cache();
	redirect(FORUM_ROOT.'admin/admin_groups.php', 'Group '.(($_POST['mode'] == 'edit') ? 'edited' : 'added').'. Redirecting &hellip;');
}
else if (isset($_POST['set_default_group']))
{
	confirm_referrer('admin_groups.php');
	$group_id = intval($_POST['default_group']);
	if ($group_id < 1) message($lang_common['Bad request']);
	$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$group_id.' WHERE conf_name=\'o_default_user_group\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_groups.php', 'Default group set. Redirecting &hellip;');
}
else if (isset($_GET['del_group']))
{
	confirm_referrer('admin_groups.php');
	$group_id = intval($_GET['del_group']);
	if ($group_id < 5) message($lang_common['Bad request']);
	if ($group_id == $configuration['o_default_user_group']) message('The default group cannot be removed. In order to delete this group, you must first setup a different group as the default.');
	$result = $db->query('SELECT g.g_title, COUNT(u.id) FROM '.$db->prefix.'groups AS g INNER JOIN '.$db->prefix.'users AS u ON g.g_id=u.group_id WHERE g.g_id='.$group_id.' GROUP BY g.g_id, g_title') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result) || isset($_POST['del_group']))
	{
		if (isset($_POST['del_group']))
		{
			$move_to_group = intval($_POST['move_to_group']);
			$db->query('UPDATE '.$db->prefix.'users SET group_id='.$move_to_group.' WHERE group_id='.$group_id) or error('Unable to move users into group', __FILE__, __LINE__, $db->error());
		}
		$db->query('DELETE FROM '.$db->prefix.'groups WHERE g_id='.$group_id) or error('Unable to delete group', __FILE__, __LINE__, $db->error());
		$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE group_id='.$group_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());
		require_once FORUM_ROOT.'include/cache.php';
		generate_quickjump_cache();
		redirect(FORUM_ROOT.'admin/admin_groups.php', 'Group removed. Redirecting &hellip;');
	}
	list($group_title, $group_members) = $db->fetch_row($result);
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['UserGroups'];
	require FORUM_ROOT.'header.php';
	generate_admin_menu('groups');
?>
<div class="blockform">
	<div class="tab-page" id="groups2Pane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "groups2Pane" ), 1 )</script>
	<div class="tab-page" id="remove-groups-page"><h2 class="tab"><?php echo$lang_admin['Remove']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "remove-groups-page" ) );</script>
	<div class="box">
		<form id="groups" method="post" action="admin_groups.php?del_group=<?php echo $group_id ?>">
			<div class="inform">
				<fieldset>
					<legend>
					<?php echo$lang_admin['Move_users_group']?>
					</legend>
					<div class="infldset">
						<p>
							<?php echo$lang_admin['The_group']?>"<?php echo convert_htmlspecialchars($group_title) ?>" <?php echo$lang_admin['Currently_has']?> <?php echo $group_members ?> <?php echo$lang_admin['Members']?>. <?php echo$lang_admin['Select_group_members']?>
						</p>
						<label>
							Move users to
							<select name="move_to_group">
								<?php
									$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.USER_GUEST.' AND g_id!='.$group_id.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
									while ($cur_group = $db->fetch_assoc($result))
									{
										if ($cur_group['g_id'] == USER_MEMBER) echo "\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
										else echo "\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
									}
								?>
							</select>
							</br>
						</label>
					</div>
				</fieldset>
			</div>
			<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="del_group" value="<?php echo $lang_admin['Remove']; ?>" /></p>
		</form>
	</div>
</div>
<div class="clearer">
</div>
</div>
<?php
	require FORUM_ROOT.'admin/admin_footer.php';
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['UserGroups'];
require FORUM_ROOT.'header.php';
generate_admin_menu('groups');
?>
<div class="blockform">
	<div class="tab-page" id="groupsPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "groupsPane" ), 1 )</script>
	<div class="tab-page" id="help-groups-page"><h2 class="tab"><?php echo$lang_admin['Help']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-groups-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">
									<img src=<?php echo FORUM_ROOT?>img/admin/groups.png>
								</td>
								<td>
									<span>
										<?php echo $lang_admin['help_groups']; ?>
									</span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="add-groups-page"><h2 class="tab"><?php echo$lang_admin['Add']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-groups-page" ) );</script>
		<div class="box">
			<form id="groups" method="post" action="admin_groups.php?action=foo">
				<div class="inform">
					<fieldset>
						<legend><?php echo$lang_admin['Add_group']?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin['Base_group']?></th>
									<td>
										<select id="base_group" name="base_group" tabindex="1">
											<?php
											$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
											while ($cur_group = $db->fetch_assoc($result))
											{
												if ($cur_group['g_id'] == $configuration['o_default_user_group']) echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
												else echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
											}
											?>
										</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Select a user group from which the new group will inherit it\'s permission settings. The next page will let you fine-tune said settings.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="add_group" value=" Add " tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="def-groups-page"><h2 class="tab"><?php echo $lang_admin['Default']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "def-groups-page" ) );</script>
		<div class="box">
			<form id="groups" method="post" action="admin_groups.php">
				<div class="inform">
					<fieldset>
						<legend><?php echo$lang_admin['Default_group_set']?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo$lang_admin['Default_group']?></th>
									<td>
										<select id="default_group" name="default_group" tabindex="3">
											<?php
											$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
											while ($cur_group = $db->fetch_assoc($result))
											{
												if ($cur_group['g_id'] == $configuration['o_default_user_group']) echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
												else echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.convert_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
											}
											?>
										</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('This is the default user group, e.g. the group users are placed in when they register. For security reasons, users can\'t be placed in either the moderator or administrator user groups by default.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="set_default_group" value=" Save " tabindex="4" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="edit-groups-page"><h2 class="tab"><?php echo$lang_admin['Edit']?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-groups-page" ) );</script>
		<div class="box">
			<div class="fakeform">
				<div class="inform">
					<fieldset>
						<legend><?php echo$lang_admin['Edit_remove_group']?></legend>
						<div class="infldset">
							<p>
							<?php echo$lang_admin['Remove_group_note']?>
							</p>
							<table cellspacing="0">
								<?php
								$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
								while ($cur_group = $db->fetch_assoc($result)) echo "\t\t\t\t\t\t\t\t".'<tr><th scope="row"><a href="admin_groups.php?edit_group='.$cur_group['g_id'].'">Edit</a>'.(($cur_group['g_id'] > USER_MEMBER) ? ' - <a href="admin_groups.php?del_group='.$cur_group['g_id'].'">Remove</a>' : '').'</th><td>'.convert_htmlspecialchars($cur_group['g_title']).'</td></tr>'."\n";
								?>
							</table>
						</div>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>
