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
if (isset($_POST['add_rank']))
{
	confirm_referrer('admin_ranks.php');
	$rank = trim($_POST['new_rank']);
	$min_posts = $_POST['new_min_posts'];
	if ($rank == '') message('You must enter a rank title.');
	if (!preg_match('#^\d+$#', $min_posts)) message('Minimum posts must be a positive integer value.');
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'ranks WHERE min_posts='.$min_posts) or error('Unable to fetch rank info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) message('There is already a rank with a minimun posts value of '.$min_posts.'.');
	$db->query('INSERT INTO '.$db->prefix.'ranks (rank, min_posts) VALUES(\''.$db->escape($rank).'\', '.$min_posts.')') or error('Unable to add rank', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_ranks_cache();
	redirect(FORUM_ROOT.'admin/admin_ranks.php', 'Rank added. Redirecting &hellip;');
}
else if (isset($_POST['update']))
{
	confirm_referrer('admin_ranks.php');
	$id = intval(key($_POST['update']));
	$rank = trim($_POST['rank'][$id]);
	$min_posts = trim($_POST['min_posts'][$id]);
	if ($rank == '') message('You must enter a rank title.');
	if (!preg_match('#^\d+$#', $min_posts)) message('Minimum posts must be a positive integer value.');
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'ranks WHERE id!='.$id.' && min_posts='.$min_posts) or error('Unable to fetch rank info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) message('There is already a rank with a minimun posts value of '.$min_posts.'.');
	$db->query('UPDATE '.$db->prefix.'ranks SET rank=\''.$db->escape($rank).'\', min_posts='.$min_posts.' WHERE id='.$id) or error('Unable to update rank', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_ranks_cache();
	redirect(FORUM_ROOT.'admin/admin_ranks.php', 'Rank updated. Redirecting &hellip;');
}
else if (isset($_POST['remove']))
{
	confirm_referrer('admin_ranks.php');
	$id = intval(key($_POST['remove']));
	$db->query('DELETE FROM '.$db->prefix.'ranks WHERE id='.$id) or error('Unable to delete rank', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_ranks_cache();
	redirect(FORUM_ROOT.'admin/admin_ranks.php', 'Rank removed. Redirecting &hellip;');
}
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Ranks'];
$focus_element = array('ranks', 'new_rank');
require FORUM_ROOT.'header.php';
generate_admin_menu('ranks');
?>
<div class="blockform">
	<div class="tab-page" id="rankPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "rankPane" ), 1 )</script>
	<div class="tab-page" id="help-rank-page"><h2 class="tab"><?php echo$lang_admin['Help'];?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-rank-page" ) );</script>
		<div class="box">
			<form>
				<div class="inbox">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
							<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/ranks.png></td>
							<td>
								<span><?php echo $lang_admin['help_ranks']; ?></span>
							</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="add-rank-page"><h2 class="tab"><?php echo $lang_admin['Add']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-rank-page" ) );</script>
		<div class="box">
			<form id="ranks" method="post" action="admin_ranks.php">
				<div class="inbox">
					<fieldset>
						<legend>Add rank</legend>
						<div class="infldset">
							<p><?php echo $lang_admin['help_add_ranks']; ?></p>
							<table  cellspacing="0">
							<thead>
								<tr>
									<th class="tcl" scope="col">Rank&nbsp;title</th>
									<th class="tc2" scope="col">Minimum&nbsp;posts</th>
									<th class="hidehead" scope="col">Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><input type="text" class="textbox" name="new_rank" size="24" maxlength="50" tabindex="1" /></td>
									<td><input type="text" class="textbox" name="new_min_posts" size="7" maxlength="7" tabindex="2" /></td>
									<td><input type="submit" name="add_rank" class="b1" value="<?php echo $lang_admin['Add'] ?>" tabindex="3" /></td>
								</tr>
							</tbody>
							</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="edit-rank-page"><h2 class="tab"><?php echo $lang_admin['Edit/Remove']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-rank-page" ) );</script>
		<div class="box">
			<form id="ranks" method="post" action="admin_ranks.php">
				<div class="inbox">
					<fieldset>
						<legend><?php echo $lang_admin['edit_remove_ranks']; ?></legend>
						<div class="infldset">
<?php
$result = $db->query('SELECT id, rank, min_posts FROM '.$db->prefix.'ranks ORDER BY min_posts') or error('Unable to fetch rank list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{
?>
							<table  cellspacing="0">
							<thead>
								<tr>
									<th class="tcl" scope="col"><strong><?php echo $lang_admin['Rank title']; ?></strong></th>
									<th class="tc2" scope="col"><strong><?php echo $lang_admin['Minimum posts']; ?></strong></th>
									<th class="hidehead" scope="col"><?php echo $lang_admin['Actions']; ?></th>
								</tr>
							</thead>
							<tbody>
<?php
	while ($cur_rank = $db->fetch_assoc($result)) echo "\t\t\t\t\t\t\t\t".'<tr><td><input type="text" class="textbox" name="rank['.$cur_rank['id'].']" value="'.convert_htmlspecialchars($cur_rank['rank']).'" size="24" maxlength="50" /></td><td><input type="text" class="textbox" name="min_posts['.$cur_rank['id'].']" value="'.$cur_rank['min_posts'].'" size="7" maxlength="7" /></td><td><input type="submit" class="b1" name="update['.$cur_rank['id'].']" value="'.$lang_admin['Update'] .'" />&nbsp;<input type="submit" class="b1" name="remove['.$cur_rank['id'].']" value="'.$lang_admin['Remove'] .'" /></td></tr>'."\n";
?>
							</tbody>
							</table>
<?php
}
else echo "\t\t\t\t\t\t\t".'<p>No ranks in list.</p>'."\n";
?>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>