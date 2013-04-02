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
define('DISABLE_BUFFERING', 1);
define('BACKUP_PATH', FORUM_ROOT . 'backup/forum.tar');
define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';
if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);

if (isset($_POST['cleanup']))
{
	@set_time_limit(0);
	$ip = "'".implode("','", array_values(explode(' ', $_POST['ip_addys'])))."'";
	$db->query('DELETE FROM '.$db->prefix.'posts WHERE poster_ip IN('.$ip.')') or error('Could not delete posts', __FILE__, __LINE__, $db->error());
	$db->query('DELETE FROM '.$db->prefix.'users WHERE registration_ip IN('.$ip.')') or error('Could not delete users', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_posts SELECT t.forum_id, count(*) as posts FROM '.$db->prefix.'posts as p LEFT JOIN '.$db->prefix.'topics as t on p.topic_id=t.id GROUP BY t.forum_id') or error('Creating posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_posts SET num_posts=posts WHERE id=forum_id') or error('Could not update post counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_topics SELECT forum_id, count(*) as topics FROM '.$db->prefix.'topics GROUP BY forum_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_topics SET num_topics=topics WHERE id=forum_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_posts SELECT topic_id, count(*)-1 as replies FROM '.$db->prefix.'posts GROUP BY topic_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_posts SET num_replies=replies WHERE id=topic_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_last SELECT p.posted AS n_last_post, p.id AS n_last_post_id, p.poster AS n_last_poster, t.forum_id FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id ORDER BY p.posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_lastb SELECT * FROM '.$db->prefix.'forum_last WHERE forum_id > 0 GROUP BY forum_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=forum_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_last SELECT posted AS n_last_post, id AS n_last_post_id, poster AS n_last_poster, topic_id FROM '.$db->prefix.'posts ORDER BY posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_lastb SELECT * FROM '.$db->prefix.'topic_last WHERE topic_id > 0 GROUP BY topic_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=topic_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topic SELECT t.id as o_id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON p.topic_id = t.id WHERE p.id IS NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topic WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_posts SELECT p.id as o_id FROM '.$db->prefix.'posts p LEFT JOIN '.$db->prefix.'topics t ON p.topic_id=t.id WHERE t.id IS NULL') or error('Creating orphaned posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('DELETE '.$db->prefix.'posts FROM '.$db->prefix.'posts, '.$db->prefix.'orph_posts WHERE o_id=id') or error('Could not delete posts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topics SELECT t.id as o_id FROM '.$db->prefix.'topics as t LEFT JOIN '.$db->prefix.'forums as f ON t.forum_id=f.id WHERE f.id is NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topics WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_maintenance.php', 'Forums cleansed');
}
if (isset($_POST['forum_sync']))
{
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_posts SELECT t.forum_id, count(*) as posts FROM '.$db->prefix.'posts as p LEFT JOIN '.$db->prefix.'topics as t on p.topic_id=t.id GROUP BY t.forum_id') or error('Creating posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_posts SET num_posts=posts WHERE id=forum_id') or error('Could not update post counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_topics SELECT forum_id, count(*) as topics FROM '.$db->prefix.'topics GROUP BY forum_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_topics SET num_topics=topics WHERE id=forum_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_posts SELECT topic_id, count(*)-1 as replies FROM '.$db->prefix.'posts GROUP BY topic_id') or error('Creating topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_posts SET num_replies=replies WHERE id=topic_id') or error('Could not update topic counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'user_posts SELECT poster_id, count(*)as posts FROM '.$db->prefix.'posts GROUP BY poster_id') or error('Creating posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'users, '.$db->prefix.'user_posts SET num_posts=posts WHERE id=poster_id') or error('Could not update post counts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_last SELECT p.posted AS n_last_post, p.id AS n_last_post_id, p.poster AS n_last_poster, t.forum_id FROM '.$db->prefix.'posts AS p LEFT JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id ORDER BY p.posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'forum_lastb SELECT * FROM '.$db->prefix.'forum_last WHERE forum_id > 0 GROUP BY forum_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'forums, '.$db->prefix.'forum_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=forum_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_last SELECT posted AS n_last_post, id AS n_last_post_id, poster AS n_last_poster, topic_id FROM '.$db->prefix.'posts ORDER BY posted DESC') or error('Creating last posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'topic_lastb SELECT * FROM '.$db->prefix.'topic_last WHERE topic_id > 0 GROUP BY topic_id') or error('Creating last posts tableb failed', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'topics, '.$db->prefix.'topic_lastb SET last_post_id=n_last_post_id, last_post=n_last_post, last_poster=n_last_poster WHERE id=topic_id') or error('Could not update last post', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_maintenance.php', 'Forums cleansed');
}
elseif (isset($_POST['delete_orphans']))
{
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topic SELECT t.id as o_id FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON p.topic_id = t.id WHERE p.id IS NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topic WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_posts SELECT p.id as o_id FROM '.$db->prefix.'posts p LEFT JOIN '.$db->prefix.'topics t ON p.topic_id=t.id WHERE t.id IS NULL') or error('Creating orphaned posts table failed', __FILE__, __LINE__, $db->error());
	$db->query('DELETE '.$db->prefix.'posts FROM '.$db->prefix.'posts, '.$db->prefix.'orph_posts WHERE o_id=id') or error('Could not delete posts', __FILE__, __LINE__, $db->error());
	$db->query('CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->prefix.'orph_topics SELECT t.id as o_id FROM '.$db->prefix.'topics as t LEFT JOIN '.$db->prefix.'forums as f ON t.forum_id=f.id WHERE f.id is NULL') or error('Creating orphaned topics table failed', __FILE__, __LINE__, $db->error());
	$db->query('DELETE '.$db->prefix.'topics FROM '.$db->prefix.'topics, '.$db->prefix.'orph_topics WHERE o_id=id') or error('Could not delete topics', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_maintenance.php', 'Forums cleansed');
}
if (isset($_POST['backup']))
{
	if(!@is_file(BACKUP_PATH))
	{
		$tar_file = new tar();
		$filelist = '';
		backup(FORUM_ROOT, $tar_file, $filelist);
		file_put_contents('filelist', $filelist);
		$tar_file->addFile('filelist');
		$tar_file->toTar(BACKUP_PATH, false);
		unlink('filelist');
		redirect(FORUM_ROOT.'admin/admin_maintenance.php', 'Forum backup finished');
	}
	else
	{
		message('Backup file already exists, remove it first!');
	}
}

if (isset($_GET['action']) || isset($_POST['prune']) || isset($_POST['prune_comply']))
{
	if (isset($_POST['prune_comply']))
	{
		confirm_referrer('admin_prune.php');
		$prune_from = $_POST['prune_from'];
		$prune_days = intval($_POST['prune_days']);
		$prune_date = ($prune_days) ? time() - ($prune_days*86400) : -1;
		@set_time_limit(0);
		if ($prune_from == 'all')
		{
			$result = $db->query('SELECT id FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
			$num_forums = $db->num_rows($result);
			for ($i = 0; $i < $num_forums; ++$i)
			{
				$fid = $db->result($result, $i);
				prune($fid, $_POST['prune_sticky'], $prune_date);
				update_forum($fid);
			}
		}
		else
		{
			$prune_from = intval($prune_from);
			prune($prune_from, $_POST['prune_sticky'], $prune_date);
			update_forum($prune_from);
		}
		$result = $db->query('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL') or error('Unable to fetch redirect topics', __FILE__, __LINE__, $db->error());
		$num_orphans = $db->num_rows($result);
		if ($num_orphans)
		{
			for ($i = 0; $i < $num_orphans; ++$i) $orphans[] = $db->result($result, $i);
			$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $orphans).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());
		}
		redirect(FORUM_ROOT.'admin/admin_prune.php', 'Posts pruned. Redirecting &hellip;');
	}
	$prune_days = $_POST['req_prune_days'];
	if (!preg_match('#^\d+$#', $prune_days)) message('Days to prune must be a positive integer.');
	$prune_date = time() - ($prune_days*86400);
	$prune_from = $_POST['prune_from'];
	$sql = 'SELECT COUNT(id) FROM '.$db->prefix.'topics WHERE last_post<'.$prune_date.' AND moved_to IS NULL';
	if ($_POST['prune_sticky'] == '0') $sql .= ' AND sticky=\'0\'';
	if ($prune_from != 'all')
	{
		$prune_from = intval($prune_from);
		$sql .= ' AND forum_id='.$prune_from;

		$result = $db->query('SELECT forum_name FROM '.$db->prefix.'forums WHERE id='.$prune_from) or error('Unable to fetch forum name', __FILE__, __LINE__, $db->error());
		$forum = '"'.convert_htmlspecialchars($db->result($result)).'"';
	}
	else $forum = 'all forums';
	$result = $db->query($sql) or error('Unable to fetch topic prune count', __FILE__, __LINE__, $db->error());
	$num_topics = $db->result($result);
	if (!$num_topics) message('There are no topics that are '.$prune_days.' days old. Please decrease the value of "Days old" and try again.');
	require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Prune'];
	require FORUM_ROOT.'header.php';
	generate_admin_menu('prune');
?>
	<div class="blockform">
		<h2><span><?php echo $lang_admin['Prune']; ?></span></h2>
		<div class="box">
			<form method="post" action="admin_prune.php?action=foo">
				<div class="inform">
					<input type="hidden" name="prune_days" value="<?php echo $prune_days ?>" />
					<input type="hidden" name="prune_sticky" value="<?php echo $_POST['prune_sticky'] ?>" />
					<input type="hidden" name="prune_from" value="<?php echo $prune_from ?>" />
					<fieldset>
						<legend>Confirm prune posts</legend>
						<div class="infldset">
							<p>Are you sure that you want to prune all topics older than <?php echo $prune_days ?> days from <?php echo $forum ?>? (<?php echo $num_topics ?> topics)</p>
							<p>WARNING! Pruning posts deletes them permanently.</p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="button" class="b1" onClick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input class="b1" type="submit" name="prune_comply" value="<?php echo $lang_admin['Prune']; ?>" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php
	require FORUM_ROOT.'admin/admin_footer.php';
}

if (isset($_GET['i_per_page']) && isset($_GET['i_start_at']))
{
	$per_page = intval($_GET['i_per_page']);
	$start_at = intval($_GET['i_start_at']);
	if ($per_page < 1 || $start_at < 1) message($lang_common['Bad request']);
	@set_time_limit(0);
	if (isset($_GET['i_empty_index']))
	{
		confirm_referrer('admin_maintenance.php');
		$truncate_sql = ($db_type != 'sqlite') ? 'TRUNCATE TABLE ' : 'DELETE FROM ';
		$db->query($truncate_sql.$db->prefix.'search_matches') or error('Unable to empty search index match table', __FILE__, __LINE__, $db->error());
		$db->query($truncate_sql.$db->prefix.'search_words') or error('Unable to empty search index words table', __FILE__, __LINE__, $db->error());
		switch ($db_type)
		{
			case 'mysql':
			case 'mysqli':
				$result = $db->query('ALTER TABLE '.$db->prefix.'search_words auto_increment=1') or error('Unable to update table auto_increment', __FILE__, __LINE__, $db->error());
				break;
			case 'pgsql';
				$result = $db->query('SELECT setval(\'search_words_id_seq\', 1, false)') or error('Unable to update sequence', __FILE__, __LINE__, $db->error());
		}
	}
	$end_at = $start_at + $per_page;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>
			<?php echo convert_htmlspecialchars($configuration['o_board_title']) ?> / Rebuilding search index &hellip;
		</title>
		<style type="text/css">
		body {
			font: 10px Verdana, Arial, Helvetica, sans-serif;
			color: #333333;
			background-color: #FFFFFF
		}
		</style>
	</head>
	<body>
		Rebuilding index &hellip; This might be a good time to put on some coffee :-)<br /><br />
<?php
	require FORUM_ROOT.'include/search_idx.php';
	$result = $db->query('SELECT DISTINCT t.id, p.id, p.message FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id WHERE t.id>='.$start_at.' AND t.id<'.$end_at.' ORDER BY t.id') or error('Unable to fetch topic/post info', __FILE__, __LINE__, $db->error());
	$cur_topic = 0;
	while ($cur_post = $db->fetch_row($result))
	{
		if ($cur_post[0] <> $cur_topic)
		{
			$result2 = $db->query('SELECT p.id, t.subject, MIN(p.posted) AS first FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE t.id='.$cur_post[0].' GROUP BY p.id, t.subject ORDER BY first LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
			list($first_post, $subject) = $db->fetch_row($result2);
			$cur_topic = $cur_post[0];
		}
		echo 'Processing post <strong>'.$cur_post[1].'</strong> in topic <strong>'.$cur_post[0].'</strong><br />'."\n";
		if ($cur_post[1] == $first_post) update_search_index('post', $cur_post[1], $cur_post[2], $subject);
		else update_search_index('post', $cur_post[1], $cur_post[2]);
	}
	$result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE id>'.$end_at) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
	$query_str = ($db->num_rows($result)) ? '?i_per_page='.$per_page.'&i_start_at='.$end_at : '';
	$db->end_transaction();
	$db->close();
	exit('<script type="text/javascript">window.location="admin_maintenance.php'.$query_str.'"</script><br />JavaScript redirect unsuccessful. Click <a href="admin_maintenance.php'.$query_str.'">here</a> to continue.');
}
$result = $db->query('SELECT id FROM '.$db->prefix.'topics ORDER BY id LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) $first_id = $db->result($result);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Maintenance'];
$required_fields = array('req_prune_days' => 'Days old');
$focus_element = array('prune', 'req_prune_days');
require FORUM_ROOT.'header.php';
generate_admin_menu('maintenance');
?>
<div class="blockform">
	<div class="tab-page" id="maintPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "maintPane" ), 1 )</script>
	<div class="tab-page" id="help-maint-page"><h2 class="tab"><?php echo$lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-maint-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">
									<img src="<?php echo FORUM_ROOT?>img/admin/maintenance.png" alt="maintenance" />
								</td>
								<td>
									<span>
										<?php echo $lang_admin['help_maintenance']; ?>
									</span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="rebs-maint-page"><h2 class="tab"><?php echo$lang_admin['Rebuild index']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "rebs-maint-page" ) );</script>
		<div class="box">
			<form method="get" action="admin_maintenance.php">
				<div class="inform">
					<fieldset>
						<legend>
							Rebuild search index
						</legend>
						<div class="infldset">
							<p><?php echo$lang_admin['help_rebuild_index']; ?></p>
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo$lang_admin['Topics per cycle']; ?></th>
									<td>
										<input type="text" class="textbox" name="i_per_page" size="7" maxlength="7" value="100" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('<?php echo$lang_admin['help_tt1_rebuild']; ?>');" onMouseOut="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Starting Topic ID']; ?></th>
									<td>
										<input type="text" class="textbox" name="i_start_at" size="7" maxlength="7" value="<?php echo (isset($first_id)) ? $first_id : 0 ?>" tabindex="2" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The topic ID to start rebuilding at. It\'s default value is the first available ID in the database. Normally you wouldn\'t want to change this.');" onMouseOut="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo$lang_admin['Empty index']; ?></th>
									<td class="inputadmin">
										<span>
											<input type="checkbox" name="i_empty_index" value="1" tabindex="3" checked="checked" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Select this if you want the search index to be emptied before rebuilding (see below).');" onMouseOut="return nd();" alt="" />
										</span>
									</td>
								</tr>
							</table>
							<p class="topspace"><?php echo$lang_admin['help_rebuild_index_completed']; ?></p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="rebuild_index" value="<?php echo$lang_admin['Go']; ?>" tabindex="4" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="prune-maint-page"><h2 class="tab"><?php echo$lang_admin['Prune']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "prune-maint-page" ) );</script>
		<div class="box">
			<form id="prune" method="post" action="admin_maintenance.php" onSubmit="return process_form(this)">
				<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend>Prune old posts</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Days old</th>
									<td>
										<input type="text" class="textbox" name="req_prune_days" size="3" maxlength="3" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The number of days `old` a topic must be to be pruned. E.g. if you were to enter 30, every topic that didn\'t contain a post dated less than 30 days old would be deleted.');" onMouseOut="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Prune sticky topics</th>
									<td>
										<input type="radio" name="prune_sticky" value="1" tabindex="2" checked="checked" />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="prune_sticky" value="0" />&nbsp;<strong>No</strong>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('When enabled sticky topics will also be pruned.');" onMouseOut="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Prune from forum</th>
									<td>
										<select name="prune_from" tabindex="3">
											<option value="all">All forums</option>
<?php
	$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
	$cur_category = 0;
	while ($forum = $db->fetch_assoc($result))
	{
		if ($forum['cid'] != $cur_category)
		{
			if ($cur_category) echo "\t\t\t\t\t\t\t\t\t\t\t".'</optgroup>'."\n";
			echo "\t\t\t\t\t\t\t\t\t\t\t".'<optgroup label="'.convert_htmlspecialchars($forum['cat_name']).'">'."\n";
			$cur_category = $forum['cid'];
		}
		echo "\t\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$forum['fid'].'">'.convert_htmlspecialchars($forum['forum_name']).'</option>'."\n";
	}
?>
											</optgroup>
										</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('The forum from which you want to prune posts.');" onMouseOut="return nd();" alt="" />
									</td>
								</tr>
							</table>
							<p class="topspace">Use this feature with caution. Pruned posts can <strong>never</strong> be recovered. For best performance you should put the forum in maintenance mode during pruning.</p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="prune" value="<?php echo$lang_admin['Go']; ?>" tabindex="5" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="bkp-maint-page"><h2 class="tab"><?php echo$lang_admin['Backup']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "bkp-maint-page" ) );</script>
		<div class="box">
			<form method="post" action="<?php echo$_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Backup forum</legend>
						<div class="infldset">
							<p>
<?php
function check_perms($path,$perm)
{
	clearstatcache();
	$configmod = substr(sprintf('%o', fileperms($path)), -4); 
	$trcss = (($configmod != $perm) ? "background-color:#fd7a7a;" : "background-color:#91f587;");
	echo "<tr style=".$trcss.">"; 
	echo "<td style=\"border:0px;\">". $path ."</td>"; 
	echo "<td style=\"border:0px;\">$perm</td>"; 
	echo "<td style=\"border:0px;\">$configmod</td>"; 
	echo "</tr>";  
}
?>
							<table width=\"100%\"  border=\"0\" cellspacing=\"0\" cellpadding=\"3\" style=\"text-align:center;\">
							<tr>
								<td style="border:0px;"><b>Folder Name</b></th>
								<td style="border:0px;"><b>Needed Chmod</b></th>
								<td style="border:0px;"><b>Current Chmod</b></th>
							</tr>
						<?php
							check_perms("../backup","0777");
						?>
							</table>
							<br />
							This feature enables you to backup the whole forum filesystem structure to a tar archive on your server. If the table above is green, you may continue. If the table is red, then you must make sure that the folder <b>/backup/</b> is set to 0777. In order for the backup to work, you must make sure the permissions are set correctly. 
							</p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="backup" value="<?php echo$lang_admin['Go']; ?>" tabindex="4" /><?php if (file_exists(BACKUP_PATH)) echo "&nbsp;<input type=\"button\" class=\"b1\" name=\"backup\" value=\"Download Backup\" tabindex=\"4\" onclick=\"window.location = '" . BACKUP_PATH . "';\" />"; ?></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="cln-maint-page"><h2 class="tab"><?php echo$lang_admin['Spam clean']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "cln-maint-page" ) );</script>
		<div class="box">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Spam cleaning</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">IP Addresses</td>
								<td>
									<input type="text" class="textbox" name="ip_addys" size="30" maxlength="255" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onMouseOver="return overlib('Enter a list of one or more IP addresses separated by spaces to be removed from the forum (note it is also recommended you ban these IP addresses from the bans section of admin).');" onMouseOut="return nd();" alt="" />
								</td>
							</tr>
							</table>
							<p>This feature is intended to clean up the mess after a spam attack, how it works is you put in one or more IP addresses (separated by spaces) and it deletes all users and posts with that IP, then performs the rest of the cleanup operations.</p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="cleanup" value="<?php echo$lang_admin['Go']; ?>" tabindex="4" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="syn-maint-page"><h2 class="tab"><?php echo$lang_admin['Syncronize']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "syn-maint-page" ) );</script>
		<div class="box">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Syncronize forum</legend>
						<div class="infldset">
							<p>This feature works out the number of posts/topics each forum currently has and resets their post/topic counts, useful if you edited the db.</p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="forum_sync" value="Go!" tabindex="4" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="orph-maint-page"><h2 class="tab"><?php echo$lang_admin['Clear orphans']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "orph-maint-page" ) );</script>
		<div class="box">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Clear orphans</legend>
						<div class="infldset">
							<p>This feature deletes all posts whos topic has been deleted, and inversely all topics whos have no posts and all topics whos forum has been deleted, useful after database edits.</p>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="delete_orphans" value="<?php echo$lang_admin['Go']; ?>" tabindex="4" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>