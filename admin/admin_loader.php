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
$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : '';
if (!preg_match('/^AM?P_(\w*?)\.php$/i', $plugin)) message($lang_common['Bad request']);
$prefix = substr($plugin, 0, strpos($plugin, '_'));
if ($forum_user['g_id'] == USER_MOD && $prefix == 'AP') message($lang_common['No permission']);
if (!file_exists(FORUM_ROOT.'plugins/'.$plugin)) message('There is no plugin called \''.$plugin.'\' in the plugin directory.');
if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '').'?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / '.$plugin;
require FORUM_ROOT.'header.php';
include FORUM_ROOT.'plugins/'.$plugin;
if (!defined('PLUGIN_LOADED')) message('Loading of the plugin \''.$plugin.'\' failed.');
?>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>
