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
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($action == 'phpinfo' && $forum_user['g_id'] == USER_ADMIN)
{
	if (strpos(strtolower((string)@ini_get('disable_functions')), 'phpinfo') !== false) message('The PHP function phpinfo() has been disabled on this server.');
	phpinfo();
	exit;
}
if ($action == 'check_software_upgrade')
{
	if (!ini_get('allow_url_fopen')) message('Unable to check for upgrade since \'allow_url_fopen\' is disabled on this system.');
	if (!$fp = fopen('http://powerbb.net/info/powerbb_latest_version.php', 'r')) message('The update file is not on the server!');
	$latest_version = trim(@fread($fp, 16));
	@fclose($fp);
	if ($latest_version == '') message('Check for upgrade failed for unknown reasons.');
	$cur_version = str_replace(array('.', 'dev', 'beta', ' '), '', strtolower($configuration['o_cur_version']));
	$cur_version = (strlen($cur_version) == 2) ? intval($cur_version) * 10 : intval($cur_version);
	$latest_version = str_replace('.', '', strtolower($latest_version));
	$latest_version = (strlen($latest_version) == 2) ? intval($latest_version) * 10 : intval($latest_version);
	if ($cur_version >= $latest_version) message('You are running the latest version of PowerBB Forum.');
		else message('A new version of PowerBB Forum has been released. You can download the latest version at <a href="http://www.powerwd.net/forum/view_forum.php?id=12">TheSavior</a>.');
}
if (@file_exists('/proc/loadavg') && is_readable('/proc/loadavg'))
{
	$fh = @fopen('/proc/loadavg', 'r');
	$load_averages = @fread($fh, 64);
	@fclose($fh);
	$load_averages = @explode(' ', $load_averages);
	$server_load = isset($load_averages[2]) ? $load_averages[0].' '.$load_averages[1].' '.$load_averages[2] : 'Not available';
}
else if (!in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages)) $server_load = $load_averages[1].' '.$load_averages[2].' '.$load_averages[3];
else $server_load = 'Not available';
$result = $db->query('SELECT COUNT(user_id) FROM '.$db->prefix.'online WHERE idle=0') or error('Unable to fetch online count', __FILE__, __LINE__, $db->error());
$num_online = $db->result($result);
switch ($db_type)
{
	case 'sqlite':
		$db_version = 'SQLite '.sqlite_libversion();
		break;
	default:
		$result = $db->query('SELECT VERSION()') or error('Unable to fetch version info', __FILE__, __LINE__, $db->error());
		$db_version = $db->result($result);
		break;
}
if ($db_type == 'mysql' || $db_type == 'mysqli')
{
	$db_version = 'MySQL '.$db_version;
	$result = $db->query('SHOW TABLE STATUS FROM `'.$db_name.'`') or error('Unable to fetch table status', __FILE__, __LINE__, $db->error());
	$total_records = $total_size = 0;
	while ($status = $db->fetch_assoc($result))
	{
		$total_records += $status['Rows'];
		$total_size += $status['Data_length'] + $status['Index_length'];
	}
	$total_size = $total_size / 1024;
	if ($total_size > 1024) $total_size = round($total_size / 1024, 2).' MB';
	else $total_size = round($total_size, 2).' KB';
}
if (function_exists('mmcache'))
{
	$php_accelerator = '<a href="http://turck-mmcache.sourceforge.net/">Turck MMCache</a>';
}
else if (isset($_PHPA))
{
	$php_accelerator = '<a href="http://www.php-accelerator.co.uk/">ionCube PHP Accelerator</a>';
}
else if (function_exists('zend_version'))
{
	$php_accelerator = '<a href="http://www.zend.com/">Zend Optimizer</a> : '. zend_version();
}
else $php_accelerator = 'N/A';
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'users WHERE group_id > 3') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['total_reg_users'] = $db->result($result);
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'forums') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['forums'] = $db->result($result);
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'categories') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['categories'] = $db->result($result);
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'users WHERE group_id = 1') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['admin_users'] = $db->result($result);
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'users WHERE group_id = 2') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['mod_users'] = $db->result($result);
$result = $db->query('SELECT COUNT(id)-1 FROM '.$db->prefix.'users WHERE num_posts = 0') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['inactive_users'] = $db->result($result);
$result = $db->query('SELECT id, username FROM '.$db->prefix.'users ORDER BY registered DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
$stats['last_user'] = $db->fetch_assoc($result);
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'bans') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['banned_users'] = $db->result($result);
$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'messages') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['total_messages'] = $db->result($result);
$result = $db->query('SELECT COUNT(g_id) FROM '.$db->prefix.'groups') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
$stats['groups'] = $db->result($result);
$result = $db->query('SELECT SUM(num_topics), SUM(num_posts) FROM '.$db->prefix.'forums') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);
$stats['total_users'] = $stats['total_reg_users'] + $stats['admin_users'] + $stats['mod_users'];

function check_safe_mode()
{
    $sm = ini_get('safe_mode');
    return ($sm=='1');
}

function CheckDirectory($dir_to_test, $mess)
{
	$mode ='';
	if(is_readable($dir_to_test))
	{
		$mode .= 'R';
	}
	else
	{
		$mode .= '';
	}
	if(is_writable($dir_to_test))
	{
		$mode .= 'W';
		$newmess = 'You are able to ' . $mess . '.';
	}
	else
	{
		$mode .= '';
		$newmess = 'You will not be able to ' . $mess . '!';
	}
	echo '<tr><td width="15%"><strong>' . $dir_to_test .'</strong></td>';
	echo '<td width="5%"><font style="color:#27b927;"><b>'. $mode . '</b></font></td>';
	echo '<td width="80%">'.$newmess.'</td>';
	echo '</tr>';
}

function CheckMainConfigFile($file_to_test)
{
	$mode ='';
	if(is_readable($file_to_test))
	{
		$mode .= 'R';
	}
	else
	{
		$mode .= '';
	}
	if(is_writable($file_to_test))
	{
		$mode .= 'W';
		$mess = 'The file should not be writable!';
	}
	else
	{
		$mode .= '';
		$mess = 'The file is readable and not writable (This is good).';
	}
	echo '<tr><td width="15%"><strong>' . $file_to_test .'</strong></td>';
	echo '<td width="5%"><font style="color:#27b927;"><b>'. $mode . '</b></font></td>';
	echo '<td width="80%">'.$mess.'</td>';
	echo '</tr>';
}
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'];
require FORUM_ROOT.'header.php';
generate_admin_menu('index');
?>
<div class="blockform">
	<div class="tab-page" id="indexPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "indexPane" ), 1 )</script>
	<div class="tab-page" id="intro-page"><h2 class="tab"><?php echo $lang_admin['Intro']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "intro-page" ) );</script>
		<div id="adintro" class="box">
			<div class="inbox">
				<p>
					Welcome to the PowerBB Forum administration control panel. From here you can control vital aspects of the forum. Depending on whether you are an administrator or a moderator you can<br /><br />
					&nbsp;- organize categories and forums.<br />
					&nbsp;- set forum-wide options and preferences.<br />
					&nbsp;- control permissions for users and guests.<br />
					&nbsp;- view IP statistics for users.<br />
					&nbsp;- ban users.<br />
					&nbsp;- censor words.<br />
					&nbsp;- set up user ranks.<br />
					&nbsp;- prune old posts.<br />
					&nbsp;- handle post reports.
				</p>
			</div>
		</div>
		<br />
		<div id="adstats" class="box">
			<div class="inbox">
				<dl>
					<dt>
						<b>Version</b>
					</dt>
					<dd>
						PowerBB Forum <?php echo $configuration['o_cur_version'] ?><br />
						&copy; Copyright 2005 - 2006 <a href="http://www.powerwd.com/index.php">Eli White</a>. All rights reserved.<br />
						Written by Eli White
					</dd>

					<dt>
						<b>Server load</b>
					</dt>
					<dd>
						<?php echo $server_load ?> (<?php echo $num_online ?> users online)
					</dd>
					<?php if ($forum_user['g_id'] == USER_ADMIN): ?>
					<dt>
						<b>
							Environment
						</b>
					</dt>
					<dd>
						Operating system: <?php echo PHP_OS ?><br />
						Http server: <?php echo $HTTP_SERVER_VARS["SERVER_SOFTWARE"] ?><br/>
						PHP: <?php echo phpversion() ?> - <a href="admin_index.php?action=phpinfo">Show info</a><br />
						Accelerator: <?php echo $php_accelerator."\n" ?>
					</dd>
					<dt>
						<b>
							Database
						</b>
					</dt>
					<dd>
						<?php echo $db_version."\n" ?>
						<?php if (isset($total_records) && isset($total_size)): ?>
						<br />Rows: <?php echo $total_records."\n" ?>
						<br />Size: <?php echo $total_size."\n";?> <?php endif; endif; ?>
					</dd>
					<dt>
						<b>
							Compliance
						</b>
					</dt>
					<dd>
						<a href="http://jigsaw.w3.org/css-validator/validator?profile=css2&warning=2&uri=<?php echo $configuration['o_base_url']; ?>/index.php"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-css.png" title="CSS" /></a>
						<a href="http://www.php.net/"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-php.png" title="Powered by PHP" /></a>
						<a href="http://feedvalidator.org/check.cgi?url=<?php echo $configuration['o_base_url']; ?>/rss.php"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-rss10.png" title="RSS 1.0" /></a>
						<a href="http://feedvalidator.org/check.cgi?url=<?php echo $configuration['o_base_url']; ?>/rss.php"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-rss20.png" title="RSS 2.0" /></a>
						<a href="http://validator.w3.org/check?&uri=<?php echo $configuration['o_base_url']; ?>/index.php"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-xhtml.png" title="XHTML 1.0" /></a><br />
						<a href="http://www.mysql.org"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-mysql.png" title="Built on MySQL" /></a>
						<a href="http://www.mozilla.org"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-firefox.png" title="Firefox friendly" /></a>
						<a href="#"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-browser.png" title="Extended browser support" /></a>
						<a href="http://validator.opml.org/?url=<?php echo $configuration['o_base_url']; ?>/opml.php"><img border="0" src="<?php echo FORUM_ROOT?>img/general/button-opml.png" alt="OPML" /></a>
					</dd>
				</dl>
			</div>
		</div>
	</div>
	<div class="tab-page" id="statistics-page"><h2 class="tab">Statistics</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "statistics-page" ) );</script>
		<h2><span><?php echo $lang_admin['Board Statistics'] ?></span></h2>
		<div id="adintro" class="box">
			<div class="inbox">
				<table border=0>
				<tr>
					<td style="width:80%"><b><?php echo $lang_admin['stats_topics'] ?></b></td>
					<td><?php echo $stats['total_topics'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_posts'] ?></b></td>
					<td><?php echo $stats['total_posts'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_forums'] ?></b></td>
					<td><?php echo $stats['forums'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_categories'] ?></b></td>
					<td><?php echo $stats['categories'] ?></td>
				</tr>
				</table>
			</div>
		</div><br />
		<h2><span><?php echo $lang_admin['User Statistics'] ?></span></h2>
		<div id="adintro" class="box">
			<div class="inbox">
				<table border=0>
				<tr>
					<td style="width:80%"><b><?php echo $lang_admin['stats_normal_users'] ?></b></td>
					<td><?php echo $stats['total_reg_users'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_admins'] ?></b></td>
					<td><?php echo $stats['admin_users'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_mods'] ?></b></td>
					<td><?php echo $stats['mod_users'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_avg_posts'] ?></b></td>
					<td><?php echo round($stats['total_posts']/$stats['total_users'], 2); ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_avg_topics'] ?></b></td>
					<td><?php echo round($stats['total_topics']/$stats['total_users'],2); ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['inactive_users'] ?></b></td>
					<td><?php echo $stats['inactive_users'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_total_users'] ?></b></td>
					<td><?php echo $stats['total_users'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_banned_users'] ?></b></td>
					<td><?php echo $stats['banned_users'] ?></td>
				</tr>
				<tr>
					<td><b><?php echo $lang_admin['stats_groups'] ?></b></td>
					<td><?php echo $stats['groups'] ?></td>
				</tr>
				<tr>
					<td style="width:80%"><b><?php echo $lang_admin['stats_pm'] ?></b></td>
					<td><?php echo $stats['total_messages'] ?></td>
				</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="tab-page" id="config-check-page"><h2 class="tab"><?php echo $lang_admin['Config check']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "config-check-page" ) );</script>
	<div id="adintro" class="box">
		<div class="inbox">
			<table width=100% border=0 cellspacing=0 cellpadding=0>
				<tr>
					<td style="width:15%;">Safe mode</td>
					<td><?php if (check_safe_mode()) echo '<font color="#FF0000"><b>enabled</b></font>'; else echo '<font color="#27b927"><b>disabled</b></font>'; ?></td>
				</tr>
			</table>
		</div>
	</div>
		<br />
		<div id="adintro" class="box">
			<div class="inbox">
				<table width=100% border=0 cellspacing=0 cellpadding=0>
<?php
					CheckDirectory('../cache/', 'use the forum');
					CheckDirectory('../style/', 'install new themes');
					CheckDirectory('../uploads/', 'upload files');
					CheckDirectory('../backup/', 'create forum backup');
					CheckDirectory('../include/template/', 'install new themes');
					CheckDirectory('../include/user/', 'install new themes');
					CheckDirectory('../img/', 'install new themes');
					CheckDirectory('../img/avatars/', 'install new avatars');
?>
				</table>
			</div>
		</div>
		<br />
		<div id="adintro" class="box">
			<div class="inbox">
				<table width=100% border=0 cellspacing=0 cellpadding=0>
<?php
						CheckMainConfigFile('../config.php');
?>
				</table>
			</div>
		</div>
	</div>
	<div class="tab-page" id="upgrade-page"><h2 class="tab"><?php echo $lang_admin['Update']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "upgrade-page" ) );</script>
		<div id="adstats" class="box">
			<div class="inbox">
				<dl>
					<table width=50% border=0>
						<tr>
							<td class="tcr" align="right"><?php echo $lang_admin['Company']; ?></td>
							<td class="tcr"><font class=license><b><?php echo $configuration['o_lic_company'] ?></b></font></td>
						</tr>
						<tr>
							<td class="tcr" align="right"><?php echo $lang_admin['Name']; ?></td>
							<td class="tcr"><font class="license"><b><?php echo $configuration['o_lic_name'] ?></b></font></td>
						</tr>
						<tr>
							<td class="tcr" align="right"><?php echo $lang_admin['Licensed domain']; ?></td>
							<td class="tcr"><?php echo $_SERVER['SERVER_NAME'] ?></td>
						</tr>
					</table>
				</dl>
			</div>
		</div><br />
		<div id="adstats" class="box">
			<div class="inbox">
				<dl>
					<table width=100 border=0>
						<tr>
							<td class="tcr"><input type="reset" class="b1" value="<?php echo $lang_admin['CheckUpgrade'] ?>" onclick="window.location.href='admin_index.php?action=check_software_upgrade'"></td>
						</tr>
					</table>
				</dl>
			</div>
		</div>
<?php
	if (is_dir(FORUM_ROOT.'install')) 
	{
		echo '</div>
	<div class="tab-page" id="problems-page"><h2 class="tab">'.$lang_admin['Problems'].'</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "problems-page" ) );</script>
				<div id="aderror" class="box">
					<div class="inbox">
						<table border="1" style="background-color:#ff0000;"><tr>
						<td align="center" style="background-color:#ff0000;">'.$lang_admin['error_install_exists'] .'</tb></tr></table>
					</div>
				</div></div>';
	}
?>
	</div>
	<div class="clearer">
	</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>