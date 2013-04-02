<?php
if (!defined('IN_FORUM')) exit;

/**
 * Enter description here...
 */

function generate_admin_menu($page = '')
{
	global $configuration, $forum_user;
	$is_admin = $forum_user['g_id'] == USER_ADMIN ? true : false;
?>
<div id="adminconsole" class="block2col">
	<div id="adminmenu" class="blockmenu">
		<h2><span>Administration</span></h2>
			<div class="box">
				<div class="inbox">
					<ul>
						<li<?php if ($page == 'index') echo ' class="isactive"'; ?>>
							<a href="admin_index.php" onmouseover="return overlib('Home administration page.');" onmouseout="return nd();">
								ACP Home
							</a>
						</li>
						<li<?php if ($page == 'notes') echo ' class="isactive"'; ?>>
							<a href="admin_notes.php" onmouseover="return overlib('Click here if you need to take short notes.');" onmouseout="return nd();">
								Note pad
							</a>
						</li>
						<li<?php if ($page == 'forumroot') echo ' class="isactive"'; ?>>
							<a href="<?php echo FORUM_ROOT?>index.php" onmouseover="return overlib('Click here to go to your forum.');" onmouseout="return nd();">
								Forum Index
							</a>
						</li>
					</ul>
				</div>
			</div>
		<h2 class="block2"><span>Forum Control</span></h2>
			<div class="box">
				<div class="inbox">
					<ul>
						<?php if ($is_admin): ?>
						<li<?php if ($page == 'categories') echo ' class="isactive"'; ?>>
							<a href="admin_categories.php" onmouseover="return overlib('Here you can create, edit and remove categories.');" onmouseout="return nd();">				
								Categories
							</a>
						</li>
						<?php endif; ?><?php if ($is_admin): ?>
						<li<?php if ($page == 'forums') echo ' class="isactive"'; ?>>
							<a href="admin_forums.php" onmouseover="return overlib('Here you can create, edit and remove forums.');" onmouseout="return nd();">
								Forums
							</a>
						</li>
					</ul>
				</div>
			</div>
		<h2 class="block2"><span>Users and Groups</span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<?php endif; ?>
					<li<?php if ($page == 'users') echo ' class="isactive"'; ?>>
						<a href="admin_users.php" onmouseover="return overlib('Here you can search for users, or add new ones.');" onmouseout="return nd();">
							Users
						</a>
					</li>
					<?php if ($is_admin): ?>
					<li<?php if ($page == 'groups') echo ' class="isactive"'; ?>>
						<a href="admin_groups.php" onmouseover="return overlib('Here you can add, edit or remove user groups.');" onmouseout="return nd();">
							User groups
						</a>
					</li>
					<?php endif; ?>
					<?php if ($is_admin): ?>
					<li<?php if ($page == 'ranks') echo ' class="isactive"'; ?>>
						<a href="admin_ranks.php" onmouseover="return overlib('Configure the user ranks.');" onmouseout="return nd();">
							Ranks
						</a>
					</li>
					<?php endif; ?><?php if ($is_admin || $configuration['p_mod_ban_users'] == '1'): ?>
					<li<?php if ($page == 'bans') echo ' class="isactive"'; ?>>
						<a href="admin_bans.php" onmouseover="return overlib('Here you can set or remove bans.');" onmouseout="return nd();">
							Bans
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<h2 class="block2"><span>Board Settings</span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<?php if ($is_admin): ?>
					<li<?php if ($page == 'options') echo ' class="isactive"'; ?>>
						<a href="admin_options.php" onmouseover="return overlib('Here you can change the main PowerBB options.');" onmouseout="return nd();">
							Board configuration
						</a>
					</li>
					<?php endif; ?><?php if ($is_admin): ?>
					<li<?php if ($page == 'modules') echo ' class="isactive"'; ?>>
						<a href="admin_modules.php" onmouseover="return overlib('This is the module configuration page.');" onmouseout="return nd();">
							Modules
						</a>
					</li>
					<?php endif; ?><?php if ($is_admin): ?>
					<li<?php if ($page == 'themes') echo ' class="isactive"'; ?>>
						<a href="admin_themes.php" onmouseover="return overlib('Here you can edit and reset themes');" onmouseout="return nd();">
							Themes
						</a>
					</li>
					<?php endif; ?><?php if ($is_admin): ?>
					<li<?php if ($page == 'permissions') echo ' class="isactive"'; ?>>
						<a href="admin_permissions.php" onmouseover="return overlib('Here you can edit permission levels for user groups.');" onmouseout="return nd();">
							Permissions
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<h2 class="block2"><span>Tools</span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'censoring') echo ' class="isactive"'; ?>>
						<a href="admin_censoring.php" onmouseover="return overlib('Here you can edit censor settings for specific words.');" onmouseout="return nd();">
							Censoring
						</a>
					<?php if ($is_admin): ?>					
					<li<?php if ($page == 'maintenance') echo ' class="isactive"'; ?>>
						<a href="admin_maintenance.php" onmouseover="return overlib('Database maintenance.');" onmouseout="return nd();">
							Maintenance
						</a>
					</li>
					<?php endif; ?>
					<li<?php if ($page == 'reports') echo ' class="isactive"'; ?>>
						<a href="admin_reports.php" onmouseover="return overlib('View and zap reports.');" onmouseout="return nd();">
							Reports
						</a>
					</li>
					<li<?php if ($page == 'logs') echo ' class="isactive"'; ?>>
						<a href="admin_logs.php" onmouseover="return overlib('View logs by admins and moderators');" onmouseout="return nd();">
							Logs
						</a>
					</li>
					<li<?php if ($page == 'db') echo ' class="isactive"'; ?>>
						<a href="admin_db.php" onmouseover="return overlib('Database Managment');" onmouseout="return nd();">
							DB management
						</a>
					</li>
					<li<?php if ($page == 'email') echo ' class="isactive"'; ?>>
						<a href="admin_email.php" onmouseover="return overlib('Send Emails to registered members');" onmouseout="return nd();">
							Mass Email
						</a>
					</li>
					<li<?php if ($page == 'adverts') echo ' class="isactive"'; ?>>
						<a href="admin_advertising.php" onmouseover="return overlib('Here you can control the automatic Ad Poster');" onmouseout="return nd();">
							Advertisments
						</a>
					</li>
				</ul>
			</div>
		</div>
		<h2 class="block2"><span>Other</span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'bots') echo ' class="isactive"'; ?>>
						<a href="admin_bots.php" onmouseover="return overlib('Settings for Bot and Spider Detection');" onmouseout="return nd();">
							Bot Detection
						</a>
					</li>
				</ul>
			</div>
		</div>
<?php
	$plugins = array();
	$d = dir(FORUM_ROOT.'plugins');
	while (($entry = $d->read()) !== false)
	{
		$prefix = substr($entry, 0, strpos($entry, '_'));
		$suffix = substr($entry, strlen($entry) - 4);
		if ($suffix == '.php' && ((!$is_admin && $prefix == 'AMP') || ($is_admin && ($prefix == 'AP' || $prefix == 'AMP')))) $plugins[] = array(substr(substr($entry, strpos($entry, '_') + 1), 0, -4), $entry);
	}
	$d->close();
	if (!empty($plugins))
	{
?>
		<h2 class="block2"><span>Plugins</span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
<?php
		while (list(, $cur_plugin) = @each($plugins)) echo "\t\t\t\t\t".'<li'.(($page == $cur_plugin[1]) ? ' class="isactive"' : '').'><a href="admin_loader.php?plugin='.$cur_plugin[1].'">'.str_replace('_', ' ', $cur_plugin[0]).'</a></li>'."\n";
?>
				</ul>
			</div>
		</div>
<?php
	}
?>
	</div>
<?php
}

/*
 * Admin prune forums, topics, and posts
 */

function prune($forum_id, $prune_sticky, $prune_date)
{
	global $db;
	$extra_sql = ($prune_date != -1) ? ' AND last_post<'.$prune_date : '';
	if (!$prune_sticky) $extra_sql .= ' AND sticky=\'0\'';
	$result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id.$extra_sql, true) or error('Unable to fetch topics', __FILE__, __LINE__, $db->error());
	$topic_ids = '';
	while ($row = $db->fetch_row($result)) $topic_ids .= (($topic_ids != '') ? ',' : '').$row[0];
	if ($topic_ids != '')
	{
		$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id IN('.$topic_ids.')', true) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
		$post_ids = '';
		while ($row = $db->fetch_row($result)) $post_ids .= (($post_ids != '') ? ',' : '').$row[0];
		if ($post_ids != '')
		{
			$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.$topic_ids.')') or error('Unable to prune topics', __FILE__, __LINE__, $db->error());
			$db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE topic_id IN('.$topic_ids.')') or error('Unable to prune subscriptions', __FILE__, __LINE__, $db->error());
			$db->query('DELETE FROM '.$db->prefix.'posts WHERE id IN('.$post_ids.')') or error('Unable to prune posts', __FILE__, __LINE__, $db->error());
			require_once FORUM_ROOT.'include/search_idx.php';
			strip_search_index($post_ids);
		}
	}
}
?>