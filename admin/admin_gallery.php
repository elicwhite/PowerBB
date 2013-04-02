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
if (isset($_POST['add_cat']))
{
	// Lazy referer check (in case base_url isn't correct)
	if (!preg_match('#/admin_loader\.php#i', $_SERVER['HTTP_REFERER']))
		message($lang_common['Bad referrer']);
	$new_cat_name = trim($_POST['new_cat_name']);
	if ($new_cat_name == '') message('You must enter a name for the category.');

	$db->query('INSERT INTO '.$db->prefix.'gallery_cat (cat_name) VALUES(\''.$db->escape($new_cat_name).'\')') or error('Unable to create category', __FILE__, __LINE__, $db->error());

	redirect(FORUM_ROOT.'admin/admin_gallery.php', 'Category added. Redirecting &hellip;');
}
else if (isset($_POST['form_sent']))
{
	if (!preg_match('#/admin_loader\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
	$form = array_map('trim', $_POST['form']);
	while (list($key, $input) = @@each($form))
	{
		if ((isset($configuration['g_'.$key])) || ($configuration['g_'.$key] == NULL))
		{
			if ($configuration['g_'.$key] != $input)
			{
				if ($input != '' || is_int($input)) $value = '\''.$db->escape($input).'\'';
				else $value = 'NULL';
				$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'g_'.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
			}
		}
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_gallery.php', 'Options updated. Redirecting &hellip;');
}
else if (isset($_POST['del_cat']) || isset($_POST['del_cat_comply']))
{
	// Lazy referer check (in case base_url isn't correct)
	if (!preg_match('#/admin_loader\.php#i', $_SERVER['HTTP_REFERER']))
		message($lang_common['Bad referrer']);

	$cat_to_delete = intval($_POST['cat_to_delete']);
	if ($cat_to_delete < 1)
		message($lang_common['Bad request']);

	if (isset($_POST['del_cat_comply']))	// Delete a category with all forums and posts
	{
		@@set_time_limit(0);

		$result = $db->query('SELECT id FROM '.$db->prefix.'gallery_img WHERE cat_id='.$cat_to_delete) or error('Unable to fetch picture list', __FILE__, __LINE__, $db->error());
		$num_img = $db->num_rows($result);

		for ($i = 0; $i < $num_img; ++$i)
		{
			$cur_img_id = $db->result($result, $i);

		  $result_img = $db->query('SELECT id, poster_id, posted FROM '.$db->prefix.'gallery_img WHERE id='.$cur_img_id) or error('Unable to select picture to delete', __FILE__, __LINE__, $db->error());
      $cur_img = $db->fetch_assoc($result_img);
      
      /* FTP Upload Server *********************************************************************/
      if($configuration['g_ftp_upload'] == 1)
      {
        // Attempt to etablish a basic connection
        $conn_id = ftp_connect($configuration['g_ftp_host']); 
    
        // Identification with username and userpass
        $login_result = ftp_login($conn_id, $configuration['g_ftp_login'], $configuration['g_ftp_pass']);  
    
        // Checking connection
        if ((!$conn_id) || (!$login_result))
    		    message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error FTP connect']);

        // Delete picture
        $del_picture = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.jpg');
        $del_picture = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.png');
        $del_picture = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.gif');

        $del_thumbs = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.jpg');
        $del_thumbs = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.png');
        $del_thumbs = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.gif');

        // Close FTP connection
        ftp_close($conn_id);
      }
      else
      {
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.jpg');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.png');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.gif');
  
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.jpg');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.png');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.gif');
      }  

			// Delete the picture
			$db->query('DELETE FROM '.$db->prefix.'gallery_img WHERE id='.$cur_img_id) or error('Unable to delete picture', __FILE__, __LINE__, $db->error());

		}

		// Delete the category
		$db->query('DELETE FROM '.$db->prefix.'gallery_cat WHERE id='.$cat_to_delete) or error('Unable to delete category', __FILE__, __LINE__, $db->error());

		// Regenerate the config cache
		require_once FORUM_ROOT.'include/cache.php';
		generate_config_cache();
		
		redirect(FORUM_ROOT.'admin/admin_gallery.php', 'Category deleted. Redirecting &hellip;');

	}
	else	// If the user hasn't comfirmed the delete
	{
		$result = $db->query('SELECT cat_name FROM '.$db->prefix.'gallery_cat WHERE id='.$cat_to_delete) or error('Unable to fetch category info', __FILE__, __LINE__, $db->error());
		$cat_name = $db->result($result);
	}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'];
require FORUM_ROOT.'header.php';
generate_admin_menu('gallery');
?>
<div class="blockform">
		<h2><span>Category delete</span></h2>
		<div class="box">
			<form method="post" action="admin_gallery.php">
				<div class="inform">
				<input type="hidden" name="cat_to_delete" value="<?php echo $cat_to_delete ?>" />
					<fieldset>
						<legend>Confirm delete category</legend>
						<div class="infldset">
							<p>Are you sure that you want to delete the category "<?php echo $cat_name ?>"?</p>
							<p>WARNING! Deleting a category will delete all pictures (if any) in that category!</p>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="del_cat_comply" value="Delete" /><a href="javascript:history.go(-1)">Go back</a></p>
			</form>
		</div>
	</div>
<?php

	}

else if (isset($_POST['update']))	// Change position and name of the categories
{
	// Lazy referer check (in case base_url isn't correct)
	if (!preg_match('#/admin_loader\.php#i', $_SERVER['HTTP_REFERER']))
		message($lang_common['Bad referrer']);

	while (list($cat_id, $disp_position) = @@each($_POST['position']))
	{
		if (!preg_match('#^\d+$#', $disp_position))
			message('Position must be a positive integer value.');

		$db->query('UPDATE '.$db->prefix.'gallery_cat SET disp_position='.$disp_position.' WHERE id='.$cat_id) or error('Unable to update category', __FILE__, __LINE__, $db->error());
	}


	// Regenerate the quickjump cache
	require_once FORUM_ROOT.'include/cache.php';
	generate_quickjump_cache();

	redirect(FORUM_ROOT.'admin/admin_gallery.php', 'Categories updated. Redirecting &hellip;');
}

else if (isset($_GET['modo_cat'])) // Edit moderation user
{

  if (isset($_POST['del_modo']))	// Dell moderator
  {

  	$modo_to_delete = trim($_POST['modo_to_delete']);
  	if ($modo_to_delete == '')
  		message('You must enter if of moderator to delete.');

  	// Get the username of the user we are processing
  	$result = $db->query('SELECT id, username FROM '.$db->prefix.'users WHERE id='.$modo_to_delete) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
  	$user = $db->fetch_assoc($result);

      if ($user['id'])
      {
      	$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'gallery_cat WHERE id ='.$_GET['modo_cat']) or error('Unable to fetch categorie info', __FILE__, __LINE__, $db->error());
        $cur_cat = $db->fetch_assoc($result);   

		    $cur_moderators = ($cur_cat['moderators'] != '') ? unserialize($cur_cat['moderators']) : array();

    		if (in_array($modo_to_delete, $cur_moderators))
    		{
    			unset($cur_moderators[$user['username']]);
    			$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';
    
    			$db->query('UPDATE '.$db->prefix.'gallery_cat SET moderators='.$cur_moderators.' WHERE id='.$_GET['modo_cat']) or error('Unable to update categorie', __FILE__, __LINE__, $db->error());
    		}
        else
    		  message('No moderator of this categorie');

      }
      else
  		  message('No match user.');

		redirect(FORUM_ROOT.'admin/admin_gallery.php&modo_cat='.$_GET['modo_cat'], 'Categorie updated. Redirecting &hellip;');
  }

  if (isset($_POST['add_modo']))	// Add new moderator
  {

  	$new_modo_name = trim($_POST['new_modo_name']);
  	if ($new_modo_name == '')
  		message('You must enter a name for the new moderator.');

    $like_command = ($db_type == 'pgsql') ? 'ILIKE' : 'LIKE';

  	// Get the username of the user we are processing
  	$result = $db->query('SELECT id, username FROM '.$db->prefix.'users WHERE username '.$like_command.' \''.$db->escape(str_replace('*', '%', $new_modo_name)).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
  	$user = $db->fetch_assoc($result);

      if ($user['id'])
      {
      	$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'gallery_cat WHERE id ='.$_GET['modo_cat']) or error('Unable to fetch categorie info', __FILE__, __LINE__, $db->error());
        $cur_cat = $db->fetch_assoc($result);   

		    $cur_moderators = ($cur_cat['moderators'] != '') ? unserialize($cur_cat['moderators']) : array();

    		if (!in_array($user['id'], $cur_moderators))
    		{
    			$cur_moderators[$user['username']] = $user['id'];
    			ksort($cur_moderators);
    
    			$db->query('UPDATE '.$db->prefix.'gallery_cat SET moderators=\''.$db->escape(serialize($cur_moderators)).'\' WHERE id='.$cur_cat['id']) or error('Unable to update categorie', __FILE__, __LINE__, $db->error());
    		}
        else
    		  message('Already moderator of this categorie');

      }
      else
  		  message('No match user.');
      
		redirect(FORUM_ROOT.'admin/admin_gallery.php&modo_cat='.$_GET['modo_cat'], 'Categorie updated. Redirecting &hellip;');
  }

	$cat_id = intval($_GET['modo_cat']);
	if ($cat_id < 1)
		message($lang_common['Bad request']);

  
  
  ?>
<div class="blockform">
	<div class="tab-page" id="galleryPane">
		<script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "galleryPane" ), 1 )</script>
			<div class="tab-page" id="help-gallery-page">
				<h2 class="tab">
					Help
				</h2>
				<script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-gallery-page" ) );</script>
				<div class="box">
					<form>
						<div class="inform">
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>
										<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/options.png></td>
										<td>
											<span>
												This plugin allows you to manage (add/remove/edit/change permission) the gallery categories.
											</span>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="tab-page" id="moderator-gallery-page">
				<h2 class="tab">
					Help
				</h2>
				<script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-gallery-page" ) );</script>
				<div class="box">
					<form>
						<div class="inform">
							<div class="infldset">
								<fieldset>
  									<legend>Add/delete moderators</legend>
									<div class="infldset">
										<table class="aligntop" cellspacing="0">
											<tr>
												<th scope="row">
													Add a new moderator
													<div>
														<input type="submit" class="b1" name="add_modo" value="Add New" tabindex="2" />
													</div>
												</th>
												<td>
													<input type="text" class="textbox" name="new_modo_name" size="35" maxlength="80" tabindex="1" />
													<span>
														The name of the new moderator you want to add.
													</span>
												</td>
											</tr>
				<?php if ($moderators): ?>  <tr>
												<th scope="row">
													Delete a moderator
													<div>
														<input type="submit" class="b1" name="del_modo" value="Delete" tabindex="4" />
													</div>
												</th>
												<td>
													<?php echo $moderators ?>
													<span>
														Select the name of the moderator you want to delete.
													</span>
												</td>
											</tr>
<?php endif; ?>							</table>
									</div>
  								</fieldset>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			<?php
}

else if (isset($_GET['edit_cat'])) // Edit categorie
{

	$cat_id = intval($_GET['edit_cat']);
	if ($cat_id < 1)
		message($lang_common['Bad request']);

	// Update group permissions for $forum_id
	if (isset($_POST['save']))
	{

		// Start with the categorie details
		$cat_name = trim($_POST['cat_name']);
		$cat_desc = forum_linebreaks(trim($_POST['cat_desc']));

		if ($cat_name == '')
			message('You must enter a categorie name.');

		$cat_desc = ($cat_desc != '') ? '\''.$db->escape($cat_desc).'\'' : 'NULL';

		$db->query('UPDATE '.$db->prefix.'gallery_cat SET cat_name=\''.$db->escape($cat_name).'\', cat_desc='.$cat_desc.' WHERE id='.$cat_id) or error('Unable to update categorie', __FILE__, __LINE__, $db->error());

		// Now let's deal with the permissions
		if (isset($_POST['read_cat_old']))
		{
			$result = $db->query('SELECT g_id, g_read_board, g_post_topics FROM '.$db->prefix.'groups WHERE g_id!='.USER_ADMIN) or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
			while ($cur_group = $db->fetch_assoc($result))
			{
				$read_cat_new = ($cur_group['g_read_board'] == '1') ? isset($_POST['read_cat_new'][$cur_group['g_id']]) ? $_POST['read_cat_new'][$cur_group['g_id']] : '0' : $_POST['read_cat_old'][$cur_group['g_id']];
				$post_cat_new = isset($_POST['post_cat_new'][$cur_group['g_id']]) ? $_POST['post_cat_new'][$cur_group['g_id']] : '0';

				// Check if the new settings differ from the old
				if ($read_cat_new != $_POST['read_cat_old'][$cur_group['g_id']] || $post_cat_new != $_POST['post_cat_old'][$cur_group['g_id']])
				{
					// If the new settings are identical to the default settings for this group, delete it's row in gallery_perms
					if ($read_cat_new == '1' && $post_cat_new == $cur_group['g_post_topics'])
						$db->query('DELETE FROM '.$db->prefix.'gallery_perms WHERE group_id='.$cur_group['g_id'].' AND cat_id='.$cat_id) or error('Unable to delete group categorie permissions', __FILE__, __LINE__, $db->error());
					else
					{
						// Run an UPDATE and see if it affected a row, if not, INSERT
						$db->query('UPDATE '.$db->prefix.'gallery_perms SET read_cat='.$read_cat_new.', post_cat='.$post_cat_new.' WHERE group_id='.$cur_group['g_id'].' AND cat_id='.$cat_id) or error('Unable to insert group categorie permissions', __FILE__, __LINE__, $db->error());
						if (!$db->affected_rows())
							$db->query('INSERT INTO '.$db->prefix.'gallery_perms (group_id, cat_id, read_cat, post_cat) VALUES('.$cur_group['g_id'].', '.$cat_id.', '.$read_cat_new.', '.$post_cat_new.')') or error('Unable to insert group categorie permissions', __FILE__, __LINE__, $db->error());
					}
				}
			}
		}

		// Regenerate the quickjump cache
		require_once FORUM_ROOT.'include/cache.php';
		generate_quickjump_cache();

		redirect(FORUM_ROOT.'admin/admin_gallery.php&edit_cat='.$cat_id, 'Categorie updated. Redirecting &hellip;');
	}
	else if (isset($_POST['revert_perms']))
	{

		$db->query('DELETE FROM '.$db->prefix.'gallery_perms WHERE cat_id='.$cat_id) or error('Unable to delete group categorie permissions', __FILE__, __LINE__, $db->error());

		// Regenerate the quickjump cache
		require_once FORUM_ROOT.'include/cache.php';
		generate_quickjump_cache();

		redirect(FORUM_ROOT.'admin/admin_gallery.php&edit_cat='.$cat_id, 'Permissions reverted to defaults. Redirecting &hellip;');
	}


	// Fetch forum info
	$result = $db->query('SELECT id, cat_name, cat_desc, num_img FROM '.$db->prefix.'gallery_cat WHERE id='.$cat_id) or error('Unable to fetch categorie info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		message($lang_common['Bad request']);

	$cur_cat = $db->fetch_assoc($result);
?>
	<div class="tab-page" id="moderator-gallery-page">
		<h2 class="tab">
			Help
		</h2>
		<script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-gallery-page" ) );</script>
			<form id="edit_cat" method="post" action="admin_gallery.php&edit_cat=<?php echo $cat_id ?>">
				<p class="submittop"><input type="submit" class="b1" name="save" value="Save changes" tabindex="6" /></p>
				<div class="inform">
					<fieldset>
						<legend>Edit categorie details</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Category name</th>
									<td><input type="text" class="textbox" name="cat_name" size="35" maxlength="80" value="<?php echo convert_htmlspecialchars($cur_cat['cat_name']) ?>" tabindex="1" /></td>
								</tr>
								<tr>
									<th scope="row">Description (HTML)</th>
									<td><textarea name="cat_desc" rows="3" cols="50" tabindex="2"><?php echo convert_htmlspecialchars($cur_cat['cat_desc']) ?></textarea></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Edit group permissions for this categorie</legend>
						<div class="infldset">
							<p>In this form, you can set the categorie specific permissions for the different user groups. If you haven't made any changes to this categories group permissions, what you see below is the default based on settings in <a href="admin_groups.php">User groups</a>. Administrators always have full permissions and are thus excluded. Permission settings that differ from the default permissions for the user group are marked red.</p>
							<table id="forumperms" cellspacing="0">
							<thead>
								<tr>
									<th class="atcl">&nbsp;</th>
									<th>Read categorie</th>
									<th>Post picture</th>
								</tr>
							</thead>
							<tbody>
<?php

	$result = $db->query('SELECT g.g_id, g.g_title, g.g_read_board, g.g_post_topics, gp.read_cat, gp.post_cat FROM '.$db->prefix.'groups AS g LEFT JOIN '.$db->prefix.'gallery_perms AS gp ON (g.g_id=gp.group_id AND gp.cat_id='.$cat_id.') WHERE g.g_id!='.USER_ADMIN.' ORDER BY g.g_id') or error('Unable to fetch group categorie permission list', __FILE__, __LINE__, $db->error());

	while ($cur_perm = $db->fetch_assoc($result))
	{
		$read_cat = ($cur_perm['read_cat'] != '0') ? true : false;
		$post_cat = (($cur_perm['g_post_topics'] == '0' && $cur_perm['post_cat'] == '1') || ($cur_perm['g_post_topics'] == '1' && $cur_perm['post_cat'] != '0')) ? true : false;

		// Determine if the current sittings differ from the default or not
		$read_cat_def = ($cur_perm['read_cat'] == '0') ? false : true;
		$post_cat_def = (($post_cat && $cur_perm['g_post_topics'] == '0') || (!$post_cat && ($cur_perm['g_post_topics'] == '' || $cur_perm['g_post_topics'] == '1'))) ? false : true;

?>
								<tr>
									<th class="atcl"><?php echo convert_htmlspecialchars($cur_perm['g_title']) ?></th>
									<td<?php if (!$read_cat_def) echo ' class="nodefault"'; ?>>
										<input type="hidden" name="read_cat_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($read_cat) ? '1' : '0'; ?>" />
										<input type="checkbox" name="read_cat_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($read_cat) ? ' checked="checked"' : ''; ?><?php echo ($cur_perm['g_read_board'] == '0') ? ' disabled="disabled"' : ''; ?> />
									</td>
									<td<?php if (!$post_cat_def) echo ' class="nodefault"'; ?>>
										<input type="hidden" name="post_cat_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($post_cat) ? '1' : '0'; ?>" />
										<input type="checkbox" name="post_cat_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($post_cat) ? ' checked="checked"' : ''; ?> />
									</td>
								</tr>
<?php

	}
}
?>
							</tbody>
							</table>
							<div class="fsetsubmit"><input type="submit" class="b1" name="revert_perms" value="Revert to default" /></div>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" class="b1" name="save" value="Save changes" /></p>
			</form>
		</div>
	</div>

	<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>