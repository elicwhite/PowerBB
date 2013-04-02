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
$action = isset($_GET['action']) ? $_GET['action'] : null;
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Themes'];
require FORUM_ROOT.'header.php';
if ($action == 'edit')
{
}
if ($action == 'delete')
{
	$error = ''; $errnum = 0;
	if (!unlink(FORUM_ROOT.'style/'.$_GET['theme_file']. '.css'))
	{
		$error .= '<font style="color:#FF0000;">Removing <b>style/'.$_GET['theme_file'].'.css</b> ... ERROR.</font><br />';
		$errnum++;
	}
	else $error .= '<font style="color:#27b927;">Removing <b>style/'.$_GET['theme_file'].'.css</b> ... SUCCESS.</font><br />';
	if (is_dir(FORUM_ROOT.'img/'.$_GET['theme_file']))
	{
		if (!RemoveDirectory(FORUM_ROOT.'img/'.$_GET['theme_file']))
		{
			$error .= '<font style="color:#FF0000;">Removing <b>img/'.$_GET['theme_file'].'</b> ... ERROR.</font><br />';
			$errnum++;
		}
		else $error .= '<font style="color:#27b927;">Removing <b>img/'.$_GET['theme_file'].'.css</b> ... SUCCESS.</font><br />';
	}
	if (is_dir(FORUM_ROOT.'include/template/'.$_GET['theme_file']))
	{
		if (!RemoveDirectory(FORUM_ROOT.'include/template/'.$_GET['theme_file']))
		{
			$error .= '<font style="color:#FF0000;">Removing <b>include/template/'.$_GET['theme_file'].'</b> ... ERROR.</font><br />';
			$errnum++;
		}
		else $error .= '<font style="color:#27b927;">Removing <b>include/template/'.$_GET['theme_file'].'.css</b> ... SUCCESS.</font><br />';
	}
	if (is_dir(FORUM_ROOT.'include/user/'.$_GET['theme_file']))
	{
		if (!RemoveDirectory(FORUM_ROOT.'include/user/'.$_GET['theme_file']))
		{
			$error .= '<font style="color:#FF0000;">Removing <b>include/user/'.$_GET['theme_file'].'</b> ... ERROR.</font><br />';
			$errnum++;
		}
		else $error .= '<font style="color:#27b927;">Removing <b>include/user/'.$_GET['theme_file'].'.css</b> ... SUCCESS.</font><br />';
	}
	if ($errnum == 0) $error .= '<br /><b>Theme '.$_GET['theme_file']. ' deleted succesfully.</b>';
	else $error .= '<br /><b>There were errors removing theme '.$_GET['theme_file']. '. You need to remove files & folders manually!</b>';
	generate_admin_menu('themes');
?>
<div class="block">
	<form id="edit_themes" method="post" action="admin_themes.php">
	<h2>Delete theme</h2>
	<div id="adintro" class="box">
		<div class="inbox">
			<table class="aligntop" cellspacing="5">
			<tr>
				<td><?php echo $error; ?></td>
			</tr>
			</table>
			<p align="left"><input type="submit" class="b1" value="<?php echo $lang_common['Go back'] ?>"></p>
		</div>
	</div>
	</form>
</div>
<div class="clearer"></div>
</div>
<?php
}
if ($action == 'set_default')
{
	$db->query('UPDATE '.$db->prefix.'config SET conf_value='.'\''.$db->escape($_GET['theme_file']).'\''.' WHERE conf_name=\'o_default_style\'') or error('Unable to save ToDo note', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache();
	redirect(FORUM_ROOT.'admin/admin_themes.php', 'Theme <b>'. $_GET['theme_file'] . '</b> set as default. Redirecting &hellip;');
}
require FORUM_ROOT.'admin/admin_footer.php';