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
	confirm_referrer('admin_categories.php');
	$new_cat_name = trim($_POST['new_cat_name']);
	if ($new_cat_name == '') message('You must enter a name for the category.');
	$db->query('INSERT INTO '.$db->prefix.'categories (cat_name) VALUES(\''.$db->escape($new_cat_name).'\')') or error('Unable to create category', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_categories.php', 'Category added. Redirecting &hellip;');
}
else if (isset($_POST['del_cat']) || isset($_POST['del_cat_comply']))
{
	confirm_referrer('admin_categories.php');
	$cat_to_delete = intval($_POST['cat_to_delete']);
	if ($cat_to_delete < 1) message($lang_common['Bad request']);
	if (isset($_POST['del_cat_comply']))
	{
		@set_time_limit(0);
		$result = $db->query('SELECT id FROM '.$db->prefix.'forums WHERE cat_id='.$cat_to_delete) or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
		$num_forums = $db->num_rows($result);
		for ($i = 0; $i < $num_forums; ++$i)
		{
			$cur_forum = $db->result($result, $i);
			prune($cur_forum, 1, -1);
			$db->query('DELETE FROM '.$db->prefix.'forums WHERE id='.$cur_forum) or error('Unable to delete forum', __FILE__, __LINE__, $db->error());
		}
		$result = $db->query('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL') or error('Unable to fetch redirect topics', __FILE__, __LINE__, $db->error());
		$num_orphans = $db->num_rows($result);
		if ($num_orphans)
		{
			for ($i = 0; $i < $num_orphans; ++$i) $orphans[] = $db->result($result, $i);
			$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $orphans).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());
		}
		$db->query('DELETE FROM '.$db->prefix.'categories WHERE id='.$cat_to_delete) or error('Unable to delete category', __FILE__, __LINE__, $db->error());
		require_once FORUM_ROOT.'include/cache.php';
		generate_quickjump_cache();
		redirect(FORUM_ROOT.'admin/admin_categories.php', 'Category deleted. Redirecting &hellip;');
	}
	else
	{
		$result = $db->query('SELECT cat_name FROM '.$db->prefix.'categories WHERE id='.$cat_to_delete) or error('Unable to fetch category info', __FILE__, __LINE__, $db->error());
		$cat_name = $db->result($result);
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Categories';
		require FORUM_ROOT.'header.php';
		generate_admin_menu('categories');
?>
	<div class="blockform">
		<h2><span><?php echo$lang_admin['Cat_delete']?></span></h2>
		<div class="box">
			<form method="post" action="admin_categories.php">
				<div class="inform">
				<input type="hidden" name="cat_to_delete" value="<?php echo $cat_to_delete ?>" />
					<fieldset>
						<legend><?php echo$lang_admin['Conf_cat_delete']?></legend>
						<div class="infldset">
							<p><?php echo$lang_admin['Conf_cat_delete_phrase']?> "<?php echo $cat_name ?>"?</p>
							<p><?php echo$lang_admin['Conf_cat_delete_warn']?></p>
						</div>
					</fieldset>
				</div>
				<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>">
					<input type="submit" name="del_cat_comply" class="b1" value="<?php echo $lang_admin['Delete'] ?>" />
				</p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php
		require FORUM_ROOT.'admin/admin_footer.php';
	}
}
else if (isset($_POST['update']))
{
	confirm_referrer('admin_categories.php');
	$cat_order = $_POST['cat_order'];
	$cat_name = $_POST['cat_name'];
	$result = $db->query('SELECT id, disp_position FROM '.$db->prefix.'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
	$num_cats = $db->num_rows($result);
	for ($i = 0; $i < $num_cats; ++$i)
	{
		if ($cat_name[$i] == '') message('You must enter a category name.');
		if (!preg_match('#^\d+$#', $cat_order[$i])) message('Position must be an integer value.');
		list($cat_id, $position) = $db->fetch_row($result);
		$db->query('UPDATE '.$db->prefix.'categories SET cat_name=\''.$db->escape($cat_name[$i]).'\', disp_position='.$cat_order[$i].' WHERE id='.$cat_id) or error('Unable to update category', __FILE__, __LINE__, $db->error());
	}
	require_once FORUM_ROOT.'include/cache.php';
	generate_quickjump_cache();
	redirect(FORUM_ROOT.'admin/admin_categories.php', 'Categories updated. Redirecting &hellip;');
}
$result = $db->query('SELECT id, cat_name, disp_position FROM '.$db->prefix.'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
$num_cats = $db->num_rows($result);
for ($i = 0; $i < $num_cats; ++$i) $cat_list[] = $db->fetch_row($result);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Categories'];
require FORUM_ROOT.'header.php';
generate_admin_menu('categories');
?>
<div class="blockform">
	<div class="tab-page" id="catPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "catPane" ), 1 )</script>
	<div class="tab-page" id="help-cat-page"><h2 class="tab"><?php echo $lang_admin['Help'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-cat-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">
									<img src=<?php echo FORUM_ROOT?>img/admin/categories.png>
								</td>
								<td>
									<span>
										<?php echo $lang_admin['help_categories']; ?>
									</span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="add-cat-page"><h2 class="tab"><?php echo $lang_admin['Add'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-cat-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_categories.php">
				<div class="inform">
					<fieldset>
						<legend>
							<?php echo $lang_admin['Add_delete_cat']; ?>
						</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Name</th>
									<td>
										<input type="text" class="textbox" name="new_cat_name" size="35" maxlength="80" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The name of the new category you want to add. You can edit the name of the category later (see below).');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="add_cat" value="<?php echo $lang_admin['Add'] ?>" tabindex="2" /></p>
			</form>
		</div>
	</div>
<?php if ($num_cats): ?>
	<div class="tab-page" id="del-cat-page"><h2 class="tab"><?php echo $lang_admin['Remove'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "del-cat-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_categories.php">
				<div class="inform">
					<fieldset>
						<legend>
							<?php echo $lang_admin['Delete_cat']; ?>
						</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Name</th>
									<td>
										<select name="cat_to_delete" tabindex="3">
											<?php
												while (list(, list($cat_id, $cat_name, ,)) = @each($cat_list)) echo "\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cat_id.'">'.convert_htmlspecialchars($cat_name).'</option>'."\n";
											?>
										</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Select the name of the category you want to delete. You will be asked to confirm your choice of category for deletion before it is deleted.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="del_cat" value="<?php echo $lang_admin['Remove'] ?>" tabindex="4" /></p>
			</form>
		</div>
	</div>
<?php endif; ?>
<?php if ($num_cats): ?>
	<div class="tab-page" id="edit-cat-page"><h2 class="tab"><?php echo $lang_admin['Edit']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-cat-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_categories.php?action=foo">
				<div class="inform">
					<fieldset>
						<legend>
							<?php echo $lang_admin['Edit_cat']; ?>
						</legend>
						<div class="infldset">
							<table id="categoryedit" cellspacing="0" >
								<thead>
									<tr>
										<th class="tcl" scope="col"><?php echo $lang_admin['Name']; ?></th>
										<th scope="col"><?php echo $lang_admin['Position']; ?></th>
										<th>&nbsp;</th>
									</tr>
								</thead>
								<tbody>
									<?php
										@reset($cat_list);
										for ($i = 0; $i < $num_cats; ++$i)
										{
											list(, list($cat_id, $cat_name, $position)) = @each($cat_list);
									?>
									<tr>
										<td>
											<input type="text" class="textbox" name="cat_name[<?php echo $i ?>]" value="<?php echo convert_htmlspecialchars($cat_name) ?>" size="35" maxlength="80" />
										</td>
										<td>
											<input type="text" class="textbox" name="cat_order[<?php echo $i ?>]" value="<?php echo $position ?>" size="3" maxlength="2" />
										</td>
									</tr>
									<?php
										}
									?>
								</tbody>
							</table>
							<div class="fsetsubmit">
								
							</div>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input class="b1" type="submit" name="update" value="<?php echo $lang_admin['Update'] ?>" /></p>
			</form>
		</div>
	</div>
<?php endif; ?>
	<div class="clearer">
	</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php';?>